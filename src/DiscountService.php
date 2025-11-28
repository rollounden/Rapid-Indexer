<?php
require_once __DIR__ . '/Db.php';
require_once __DIR__ . '/SettingsService.php';

class DiscountService {
    // Indexing cost $0.002. ROI 4x = $0.008 revenue required per link.
    // 1 link = 2 credits. So 2 credits must cost >= $0.008.
    // 1 credit must cost >= $0.004.
    const FLOOR_PRICE_PER_CREDIT = 0.004; 

    public static function create($data) {
        $pdo = Db::conn();
        // Validation
        if (empty($data['code'])) throw new Exception("Code is required");
        if (empty($data['value'])) throw new Exception("Value is required");
        
        // Basic ROI Check for Percentage (Static check)
        if ($data['type'] === 'percent') {
            $currentPrice = (float)SettingsService::get('price_per_credit', '0.01');
            $discountedPrice = $currentPrice * (1 - ($data['value'] / 100));
            if ($discountedPrice < self::FLOOR_PRICE_PER_CREDIT) {
                 // We allow creating it but warn? Or block? User said "maintain atleast a 4x roi".
                 // I'll block it to be safe, or clamp it during usage. Blocking is safer.
                 $maxPercent = (1 - (self::FLOOR_PRICE_PER_CREDIT / $currentPrice)) * 100;
                 throw new Exception("Discount too high. Max allowed percentage is " . floor($maxPercent) . "% to maintain 4x ROI floor.");
            }
        }
        
        // Insert
        $sql = "INSERT INTO discount_codes (code, type, value, min_spend, max_uses, expires_at, affiliate_user_id, is_active) 
                VALUES (?, ?, ?, ?, ?, ?, ?, 1)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            strtoupper(trim($data['code'])),
            $data['type'],
            $data['value'],
            !empty($data['min_spend']) ? $data['min_spend'] : null,
            !empty($data['max_uses']) ? $data['max_uses'] : null,
            !empty($data['expires_at']) ? $data['expires_at'] : null,
            !empty($data['affiliate_user_id']) ? $data['affiliate_user_id'] : null
        ]);
        return $pdo->lastInsertId();
    }

    public static function findActive($code) {
        $pdo = Db::conn();
        $stmt = $pdo->prepare("SELECT * FROM discount_codes WHERE code = ? AND is_active = 1");
        $stmt->execute([strtoupper(trim($code))]);
        $discount = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$discount) return null;
        
        // Check Expiry
        if ($discount['expires_at'] && strtotime($discount['expires_at']) < time()) {
            return null;
        }
        
        // Check Usage Limit
        if ($discount['max_uses'] !== null && $discount['used_count'] >= $discount['max_uses']) {
            return null;
        }
        
        return $discount;
    }

    public static function calculate($discount, $amount) {
        // Check Min Spend
        if ($discount['min_spend'] && $amount < $discount['min_spend']) {
            throw new Exception("Minimum spend of $" . number_format($discount['min_spend'], 2) . " required.");
        }
        
        $discountAmount = 0;
        if ($discount['type'] === 'percent') {
            $discountAmount = $amount * ($discount['value'] / 100);
        } else {
            $discountAmount = $discount['value'];
        }
        
        // Clamp discount to not exceed amount (free is max)
        if ($discountAmount > $amount) {
            $discountAmount = $amount;
        }

        // ROI FLOOR ENFORCEMENT
        // Effective Price Check
        $finalAmount = $amount - $discountAmount;
        $pricePerCredit = (float)SettingsService::get('price_per_credit', '0.01');
        
        // Credits User Gets = Amount / PricePerCredit (They get the FULL value of what they entered)
        // e.g. User selects $100 package. Gets 10,000 credits.
        $credits = $amount / $pricePerCredit;
        
        if ($credits > 0) {
            $effectivePrice = $finalAmount / $credits;
            if ($effectivePrice < self::FLOOR_PRICE_PER_CREDIT) {
                 // Reduce discount to meet floor
                 // Target Final Amount = Credits * Floor
                 $targetFinal = $credits * self::FLOOR_PRICE_PER_CREDIT;
                 // Discount = Amount - TargetFinal
                 $maxDiscount = $amount - $targetFinal;
                 
                 if ($discountAmount > $maxDiscount) {
                     $discountAmount = $maxDiscount;
                 }
            }
        }
        
        return round($discountAmount, 2);
    }
    
    public static function recordUsage($discountId, $userId, $paymentId, $amountSaved) {
        $pdo = Db::conn();
        $stmt = $pdo->prepare("INSERT INTO discount_usage (discount_code_id, user_id, payment_id, discount_amount) VALUES (?, ?, ?, ?)");
        $stmt->execute([$discountId, $userId, $paymentId, $amountSaved]);
        
        $stmt = $pdo->prepare("UPDATE discount_codes SET used_count = used_count + 1 WHERE id = ?");
        $stmt->execute([$discountId]);
    }
    
    public static function getAll() {
        $pdo = Db::conn();
        $stmt = $pdo->query("SELECT d.*, u.email as affiliate_email FROM discount_codes d LEFT JOIN users u ON d.affiliate_user_id = u.id ORDER BY d.created_at DESC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public static function delete($id) {
        $pdo = Db::conn();
        $stmt = $pdo->prepare("DELETE FROM discount_codes WHERE id = ?");
        return $stmt->execute([$id]);
    }
}

