<?php

require_once __DIR__ . '/Db.php';
require_once __DIR__ . '/CryptomusClient.php';
require_once __DIR__ . '/SettingsService.php';
require_once __DIR__ . '/PaymentService.php';

class CryptomusService
{
    private $client;

    public function __construct()
    {
        // Try to get from SettingsService first (DB), then fallback to constants (Config/.env)
        $merchantId = SettingsService::getDecrypted('cryptomus_merchant_id');
        $apiKey = SettingsService::getDecrypted('cryptomus_api_key');

        if (!$merchantId && defined('CRYPTOMUS_MERCHANT_ID')) {
            $merchantId = CRYPTOMUS_MERCHANT_ID;
        }
        
        if (!$apiKey && defined('CRYPTOMUS_PAYMENT_KEY')) {
            $apiKey = CRYPTOMUS_PAYMENT_KEY;
        }

        if (!$merchantId || !$apiKey) {
            throw new Exception('Cryptomus is not configured');
        }

        $this->client = new CryptomusClient($merchantId, $apiKey);
    }


    public function createPayment(int $userId, float $amount, ?int $discountCodeId = null, ?float $originalAmount = null, ?float $discountAmount = 0)
    {
        $currency = 'USD';
        
        // Record pending payment first to get an ID
        $paymentId = PaymentService::recordPending($userId, $amount, $currency, null, $discountCodeId, $originalAmount, $discountAmount);
        $orderId = 'ORD-' . $paymentId . '-' . time();

        // Update payment with generated order ID (using paypal_order_id column for now, or we can add external_id)
        $pdo = Db::conn();
        $stmt = $pdo->prepare('UPDATE payments SET paypal_order_id = ?, method = ? WHERE id = ?');
        $stmt->execute([$orderId, 'cryptomus', $paymentId]);

        $callbackUrl = 'https://' . $_SERVER['HTTP_HOST'] . '/cryptomus_webhook.php';
        $successUrl = 'https://' . $_SERVER['HTTP_HOST'] . '/payment_success.php?order_id=' . $orderId;
        $cancelUrl = 'https://' . $_SERVER['HTTP_HOST'] . '/payment_cancel.php?token=' . $orderId; // payment_cancel.php uses 'token' for order ID display

        $response = $this->client->createPayment($orderId, $amount, $currency, $successUrl, $cancelUrl, $callbackUrl);

        if (isset($response['result']['url'])) {
            // Update with UUID if available
            if (isset($response['result']['uuid'])) {
                $stmt = $pdo->prepare('UPDATE payments SET paypal_capture_id = ? WHERE id = ?');
                $stmt->execute([$response['result']['uuid'], $paymentId]);
            }
            
            return [
                'payment_url' => $response['result']['url'],
                'uuid' => $response['result']['uuid'] ?? null
            ];
        }

        throw new Exception('Failed to create Cryptomus payment: ' . json_encode($response));
    }
    
    public function handleWebhook(array $data)
    {
        // Verify signature
        $sign = $data['sign'] ?? '';
        unset($data['sign']);
        
        $apiKey = SettingsService::getDecrypted('cryptomus_api_key');
        
        if (!$apiKey && defined('CRYPTOMUS_PAYMENT_KEY')) {
            $apiKey = CRYPTOMUS_PAYMENT_KEY;
        }

        $hash = md5(base64_encode(json_encode($data, JSON_UNESCAPED_UNICODE)) . $apiKey);

        if ($hash !== $sign) {
            throw new Exception('Invalid signature');
        }

        $status = $data['status'] ?? '';
        $orderId = $data['order_id'] ?? '';
        $amount = $data['amount'] ?? 0;
        $currency = $data['currency'] ?? 'USD';
        $uuid = $data['uuid'] ?? '';

        // Updated status list based on docs
        if ($status === 'paid' || $status === 'paid_over' || $status === 'confirm_check') {
            $this->processSuccess($orderId, $amount, $currency, $uuid);
        } elseif ($status === 'cancel' || $status === 'fail' || $status === 'system_fail') {
            $this->processFailure($orderId, $status);
        }
    }

    private function processSuccess($orderId, $amount, $currency, $uuid)
    {
        $pdo = Db::conn();
        // Find payment by order ID
        $stmt = $pdo->prepare('SELECT * FROM payments WHERE paypal_order_id = ? LIMIT 1');
        $stmt->execute([$orderId]);
        $payment = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$payment) {
            throw new Exception('Payment not found');
        }

        if ($payment['status'] === 'paid') {
            return; // Already processed
        }

        PaymentService::markPaid($payment['id'], $payment['user_id'], $uuid, $amount, $currency);
    }
    
    private function processFailure($orderId, $status)
    {
        $pdo = Db::conn();
        // Only fail if it's currently pending
        $stmt = $pdo->prepare('UPDATE payments SET status = ? WHERE paypal_order_id = ? AND status = "pending"');
        $stmt->execute(['failed', $orderId]);
    }
    
    public function checkStatus($orderId)
    {
        $pdo = Db::conn();
        $stmt = $pdo->prepare('SELECT * FROM payments WHERE paypal_order_id = ? LIMIT 1');
        $stmt->execute([$orderId]);
        $payment = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$payment) {
            throw new Exception('Payment not found');
        }
        
        // Identify if we have UUID or should use order_id
        $identifier = [];
        if (!empty($payment['paypal_capture_id']) && strpos($payment['paypal_capture_id'], '-') !== false) {
            $identifier = ['uuid' => $payment['paypal_capture_id']];
        } else {
            $identifier = ['order_id' => $orderId];
        }

        try {
            $response = $this->client->getPaymentStatus($identifier);
            
            // Log this check for debugging
            $logFile = __DIR__ . '/../storage/logs/manual_check_debug.log';
            file_put_contents($logFile, date('Y-m-d H:i:s') . " Checking ID: " . json_encode($identifier) . " Response: " . json_encode($response) . "\n", FILE_APPEND);
            
            if (isset($response['result']['status'])) {
                $status = $response['result']['status'];
                $uuid = $response['result']['uuid'] ?? $payment['paypal_capture_id'] ?? '';
                
                // Status mapping based on Cryptomus Docs
                // paid, paid_over -> Completed
                // confirm_check -> Confirmed on blockchain (safe to credit usually)
                // process, check -> Waiting
                // wrong_amount -> Underpaid (might need manual handling, or credit partial?)
                
                if ($status === 'paid' || $status === 'paid_over' || $status === 'confirm_check') {
                    $amount = $response['result']['amount'] ?? $payment['amount'];
                    $currency = $response['result']['currency'] ?? $payment['currency'];
                    $this->processSuccess($orderId, $amount, $currency, $uuid);
                    return 'paid';
                } elseif ($status === 'cancel' || $status === 'fail' || $status === 'system_fail') {
                    $this->processFailure($orderId, $status);
                    return 'failed';
                } elseif ($status === 'check' || $status === 'process' || $status === 'wrong_amount_waiting') {
                     return 'processing';
                } elseif ($status === 'wrong_amount') {
                     // Special case: underpaid. Maybe mark as failed or specialized status?
                     // For now, treat as failed/attention needed
                     return 'failed'; 
                } else {
                    return $status;
                }
            }
        } catch (Exception $e) {
            $msg = $e->getMessage();
            if (strpos(strtolower($msg), 'active transaction') !== false) {
                return 'processing';
            }
            throw $e;
        }
        
        return $payment['status'];
    }
}
