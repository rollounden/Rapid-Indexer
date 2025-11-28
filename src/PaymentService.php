<?php
require_once __DIR__ . '/Db.php';
require_once __DIR__ . '/CreditsService.php';
require_once __DIR__ . '/SettingsService.php';
require_once __DIR__ . '/DiscountService.php';

class PaymentService
{
    public static function recordPending(int $userId, float $amount, string $currency, ?string $paypalOrderId = null, ?int $discountCodeId = null, ?float $originalAmount = null, ?float $discountAmount = 0): int
    {
        $pdo = Db::conn();
        // Check if columns exist (defensive, though migration should have run)
        // Using simplified query assuming columns exist
        $stmt = $pdo->prepare('INSERT INTO payments (user_id, amount, currency, method, paypal_order_id, status, discount_code_id, original_amount, discount_amount) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)');
        $stmt->execute([$userId, $amount, $currency, 'paypal', $paypalOrderId, 'pending', $discountCodeId, $originalAmount, $discountAmount]);
        return intval($pdo->lastInsertId());
    }

    public static function markPaid(int $paymentId, int $userId, string $paypalCaptureId, float $amount, string $currency): void
    {
        $pdo = Db::conn();
        
        // Fetch payment to check for discount
        $stmt = $pdo->prepare('SELECT * FROM payments WHERE id = ?');
        $stmt->execute([$paymentId]);
        $payment = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$payment) throw new Exception("Payment not found: $paymentId");
        if ($payment['status'] === 'paid') return; // Idempotency

        $price_per_credit = (float)SettingsService::get('price_per_credit', (string)DEFAULT_PRICE_PER_CREDIT_USD);
        
        // Determine base amount for credit calculation
        // If original_amount is set, it means a discount was applied, so we award credits based on the pre-discount value
        $baseAmount = !empty($payment['original_amount']) ? (float)$payment['original_amount'] : (float)$amount;
        
        // Recalculate credits based on base amount (value)
        $credits = (int) floor(($baseAmount / $price_per_credit));
        
        $pdo->beginTransaction();
        try {
            // Update payment status
            // We record the ACTUAL paid amount ($amount passed in from webhook/capture)
            // Note: Verify that the paid amount matches what we expect? 
            // For now, trust the provider amount, but we might want to check against payment['amount']
            
            $stmt = $pdo->prepare('UPDATE payments SET status = ?, paypal_capture_id = ?, amount = ?, currency = ?, credits_awarded = ? WHERE id = ?');
            $stmt->execute(['paid', $paypalCaptureId, $amount, $currency, $credits, $paymentId]);

            CreditsService::adjust($userId, $credits, 'payment', 'payments', $paymentId);

            // If discount was used, record usage
            if (!empty($payment['discount_code_id'])) {
                $saved = 0;
                if (!empty($payment['discount_amount'])) {
                    $saved = (float)$payment['discount_amount'];
                } elseif (!empty($payment['original_amount'])) {
                     $saved = (float)$payment['original_amount'] - $amount;
                }
                
                if ($saved > 0) {
                    DiscountService::recordUsage($payment['discount_code_id'], $userId, $paymentId, $saved);
                }
            }

            $pdo->commit();
        } catch (Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }
}
