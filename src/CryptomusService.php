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
            return [
                'payment_url' => $response['result']['url'],
                'uuid' => $response['result']['uuid']
            ];
        }

        throw new Exception('Failed to create Cryptomus payment: ' . json_encode($response));
    }
    
    public function handleWebhook(array $data)
    {
        // Verify signature
        // Note: Cryptomus sends the sign in the post body usually
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

        if ($status === 'paid' || $status === 'paid_over') {
            $this->processSuccess($orderId, $amount, $currency, $uuid);
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
}
