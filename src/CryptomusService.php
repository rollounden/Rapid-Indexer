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
            // Could be checking by UUID if order ID didn't match? 
            // For safety, we stick to order_id which is our reference
            return; 
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
            $logDir = __DIR__ . '/../storage/logs';
            if (!is_dir($logDir)) { @mkdir($logDir, 0775, true); }
            $logFile = $logDir . '/manual_check_debug.log';
            
            file_put_contents($logFile, date('Y-m-d H:i:s') . " Checking ID: " . json_encode($identifier) . " Response: " . json_encode($response) . "\n", FILE_APPEND);
            
            // Check if API returned an error in 'state' or 'message' even if HTTP 200
            if (isset($response['state']) && $response['state'] !== 0) {
                $msg = $response['message'] ?? json_encode($response);
                // If it says active transaction, treat as processing
                if (strpos(strtolower($msg), 'active transaction') !== false) {
                    return 'processing';
                }
                // Otherwise throw so it's caught below
                throw new Exception("API Error: " . $msg);
            }

            if (isset($response['result']['status'])) {
                $status = $response['result']['status'];
                $uuid = $response['result']['uuid'] ?? $payment['paypal_capture_id'] ?? '';
                
                // Status mapping based on Cryptomus Docs
                // paid, paid_over -> Completed
                // confirm_check -> Confirmed on blockchain (safe to credit usually)
                // process, check -> Waiting
                // wrong_amount -> Underpaid (might need manual handling, or credit partial?)
                
                if ($status === 'paid' || $status === 'paid_over' || $status === 'confirm_check' || $status === 'check' || $status === 'process') {
                    $amount = $response['result']['amount'] ?? $payment['amount'];
                    $currency = $response['result']['currency'] ?? $payment['currency'];
                    $this->processSuccess($orderId, $amount, $currency, $uuid);
                    return 'paid';
                } elseif ($status === 'cancel' || $status === 'fail' || $status === 'system_fail') {
                    $this->processFailure($orderId, $status);
                    return 'failed';
                } elseif ($status === 'wrong_amount_waiting') {
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
            // Don't swallow other errors, throw them so admin knows
            throw $e;
        }
        
        return $payment['status'];
    }

    // New method to sync all pending payments via List API
    public function syncPendingPayments()
    {
        $pdo = Db::conn();
        // Get all pending Cryptomus payments from last 24h
        $stmt = $pdo->prepare("
            SELECT * FROM payments 
            WHERE method = 'cryptomus' AND status = 'pending' 
            AND created_at > DATE_SUB(NOW(), INTERVAL 24 HOUR)
        ");
        $stmt->execute();
        // Use fetchAll without group/unique to get a simple array of arrays
        $pendingPayments = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($pendingPayments)) {
            return 0;
        }
        
        // Map by Order ID for quick lookup
        $paymentsByOrder = [];
        foreach ($pendingPayments as $p) {
            if (!empty($p['paypal_order_id'])) {
                $paymentsByOrder[$p['paypal_order_id']] = $p;
            }
        }
        
        $updatedCount = 0;
        $logDir = __DIR__ . '/../storage/logs';
        if (!is_dir($logDir)) { @mkdir($logDir, 0775, true); }
        $logFile = $logDir . '/sync_debug.log';
        
        file_put_contents($logFile, date('Y-m-d H:i:s') . " Starting Sync for " . count($pendingPayments) . " payments\n", FILE_APPEND);

        // Try to fetch list from API to bulk update
        try {
            // Fetch last 50 payments from API to cover recent pending ones
            // API doesn't support filtering by status in list, so we get recent history
            $response = $this->client->request('/payment/list', []); // Default gets recent
            
            if (isset($response['result']['items']) && is_array($response['result']['items'])) {
                foreach ($response['result']['items'] as $item) {
                    $remoteOrderId = $item['order_id'] ?? '';
                    $remoteStatus = $item['status'] ?? '';
                    
                    if ($remoteOrderId && isset($paymentsByOrder[$remoteOrderId])) {
                        $localPayment = $paymentsByOrder[$remoteOrderId];
                        
                        // Check if status changed
                        // API Statuses: paid, paid_over, confirm_check, check, process, cancel, fail
                        
                        if (in_array($remoteStatus, ['paid', 'paid_over', 'confirm_check'])) {
                             // It's paid!
                             $this->processSuccess($remoteOrderId, $item['amount'], $item['currency'], $item['uuid']);
                             $updatedCount++;
                             file_put_contents($logFile, "  -> Matched & Updated PAID: $remoteOrderId\n", FILE_APPEND);
                        } elseif (in_array($remoteStatus, ['cancel', 'fail', 'system_fail']) && $localPayment['status'] !== 'failed' && $localPayment['status'] !== 'cancelled') {
                             // It's failed/cancelled
                             $this->processFailure($remoteOrderId, $remoteStatus);
                             $updatedCount++;
                             file_put_contents($logFile, "  -> Matched & Updated FAILED: $remoteOrderId (Status: $remoteStatus)\n", FILE_APPEND);
                        }
                        
                        // Remove from list so we don't check individually later (optimization)
                        unset($paymentsByOrder[$remoteOrderId]);
                    }
                }
            }
        } catch (Exception $e) {
            file_put_contents($logFile, "  ! Bulk List API Error: " . $e->getMessage() . "\n", FILE_APPEND);
        }
        
        // For any remaining pending payments that weren't in the bulk list (maybe older?), check individually
        foreach ($paymentsByOrder as $orderId => $payment) {
            try {
                file_put_contents($logFile, "Checking Individual Payment ID " . $payment['id'] . " (Order: " . $orderId . ")\n", FILE_APPEND);
                
                $status = $this->checkStatus($orderId);
                
                file_put_contents($logFile, "Result Status: $status\n", FILE_APPEND);
                
                if ($status === 'paid' || $status === 'failed') {
                    $updatedCount++;
                }
            } catch (Exception $e) {
                file_put_contents($logFile, "Error syncing payment " . $payment['id'] . ": " . $e->getMessage() . "\n", FILE_APPEND);
            }
        }
        
        return $updatedCount;
    }

    public function triggerTestWebhook($orderId, $status = 'paid')
    {
        $pdo = Db::conn();
        $stmt = $pdo->prepare('SELECT * FROM payments WHERE paypal_order_id = ? LIMIT 1');
        $stmt->execute([$orderId]);
        $payment = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$payment) {
            throw new Exception("Payment not found for order ID: $orderId");
        }
        
        // IMPORTANT: We try to call the remote test-webhook API first.
        // If it fails (e.g. because of "Payment service not found" or firewall),
        // we fallback to internal simulation so the Admin action "Force Paid" still works.
        
        $callbackUrl = 'https://' . $_SERVER['HTTP_HOST'] . '/cryptomus_webhook.php';
        
        // Basic payload for remote call
        $payload = [
            'url_callback' => $callbackUrl,
            'currency' => $payment['currency'] ?? 'USD',
            'network' => 'ETH', // Use uppercase, usually reliable
            'order_id' => $orderId,
            'status' => $status
        ];
        
        // "Payment service not found" usually means the currency/network combo is invalid.
        // If we recorded it as USD, we need to fake a crypto currency for the test webhook
        // because real webhooks send the COIN currency (e.g. USDT, LTCT).
        if ($payload['currency'] === 'USD') {
            $payload['currency'] = 'USDT';
            $payload['network'] = 'tron'; 
        }

        // If we have a valid UUID, use it too.
        if (!empty($payment['paypal_capture_id']) && strpos($payment['paypal_capture_id'], '-') !== false) {
             $payload['uuid'] = $payment['paypal_capture_id'];
        }
        
        try {
             $res = $this->client->testWebhookPayment($payload);
             // If successful state 0, return it
             if (isset($res['state']) && $res['state'] === 0) {
                 return $res;
             }
             // If not successful, we continue to fallback below...
             // But maybe log why
             $logDir = __DIR__ . '/../storage/logs';
             if (!is_dir($logDir)) { @mkdir($logDir, 0775, true); }
             file_put_contents($logDir . '/cryptomus_webhook_debug.log', date('Y-m-d H:i:s') . " Remote test webhook failed, falling back to local simulation. Response: " . json_encode($res) . "\n", FILE_APPEND);
             
        } catch (Exception $e) {
             // Log error and continue to fallback
             $logDir = __DIR__ . '/../storage/logs';
             if (!is_dir($logDir)) { @mkdir($logDir, 0775, true); }
             file_put_contents($logDir . '/cryptomus_webhook_debug.log', date('Y-m-d H:i:s') . " Remote test webhook exception: " . $e->getMessage() . "\n", FILE_APPEND);
        }

        // FALLBACK: Internal Simulation
        // This ensures that even if Cryptomus API rejects the test request (e.g. invalid currency pair or invoice not found),
        // we still process the "Paid" status internally for the Admin.
        
         $fakePayload = [
            'type' => 'payment',
            'uuid' => $payment['paypal_capture_id'] ?? 'test-uuid-' . time(),
            'order_id' => $orderId,
            'amount' => (string)$payment['amount'],
            'payment_amount' => (string)$payment['amount'],
            'payment_amount_usd' => (string)$payment['amount'],
            'merchant_amount' => (string)$payment['amount'],
            'commission' => '0',
            'is_final' => true,
            'status' => $status,
            'from' => 'test_wallet',
            'wallet_address_uuid' => null,
            'network' => 'tron',
            'currency' => 'USDT',
            'payer_currency' => 'USDT',
            'additional_data' => null,
            'convert' => [
                'to_currency' => 'USDT',
                'commission' => null,
                'rate' => '1.000000'
            ],
            'txid' => 'test_txid_' . time(),
            'sign' => '' // Will be calculated below
         ];
         
         // Manually sign it so handleWebhook accepts it
         $apiKey = SettingsService::getDecrypted('cryptomus_api_key');
         if (!$apiKey && defined('CRYPTOMUS_PAYMENT_KEY')) {
            $apiKey = CRYPTOMUS_PAYMENT_KEY;
         }
         
         $json = json_encode($fakePayload, JSON_UNESCAPED_UNICODE);
         $sign = md5(base64_encode($json) . $apiKey);
         $fakePayload['sign'] = $sign;
         
         // Call handleWebhook directly
         
         // Log the simulated webhook for debugging consistency
         $logDir = __DIR__ . '/../storage/logs';
         if (!is_dir($logDir)) { @mkdir($logDir, 0775, true); }
         file_put_contents($logDir . '/cryptomus_webhook_debug.log', date('Y-m-d H:i:s') . " SIMULATED WEBHOOK:\nPayload: " . json_encode($fakePayload) . "\n-------------------\n", FILE_APPEND);

         $this->handleWebhook($fakePayload);
         
         return ['state' => 0, 'message' => 'Simulated webhook (Remote call failed or skipped)'];
    }
}
