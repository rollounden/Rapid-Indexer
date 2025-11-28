<?php

class CryptomusClient
{
    private string $merchantId;
    private string $apiKey;
    private string $baseUrl = 'https://api.cryptomus.com/v1';

    public function __construct(string $merchantId, string $apiKey)
    {
        $this->merchantId = trim($merchantId); // Trim whitespace
        $this->apiKey = trim($apiKey); // Trim whitespace
    }

    private function request(string $endpoint, array $data = [])
    {
        $url = $this->baseUrl . $endpoint;
        // Cryptomus requires JSON_UNESCAPED_UNICODE to match signature logic if we strictly follow their docs
        // The docs example says: $sign = md5(base64_encode(json_encode($data)) . $API_KEY);
        // Default json_encode escapes slashes (e.g. / -> \/).
        // We should just use standard json_encode or at least ensure we match what we send.
        // However, for unicode, docs recommend JSON_UNESCAPED_UNICODE.
        $payload = json_encode($data, JSON_UNESCAPED_UNICODE);
        
        if ($data === []) {
            $payload = '{}';
        }

        // Sign: md5(base64_encode(json_encode($data)) . $API_KEY)
        $sign = md5(base64_encode($payload) . $this->apiKey);

        $ch = curl_init($url);
        $headers = [
            'merchant: ' . $this->merchantId,
            'sign: ' . $sign,
            'Content-Type: application/json'
        ];

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        
        // Enable verbose debug output for curl if needed (optional)
        // curl_setopt($ch, CURLOPT_VERBOSE, true);

        $response = curl_exec($ch);
        $error = curl_error($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        // --- EXTREME DEBUGGING LOG ---
        // Writes to storage/logs/cryptomus_debug.log
        // Check this file after making a request!
        $logData = "----------------------------------------\n";
        $logData .= "Date: " . date('Y-m-d H:i:s') . "\n";
        $logData .= "Endpoint: $endpoint\n";
        $logData .= "Merchant ID Sent: '" . $this->merchantId . "' (Length: " . strlen($this->merchantId) . ")\n";
        $logData .= "API Key Used (First 5 chars): " . substr($this->apiKey, 0, 5) . "...\n";
        $logData .= "Payload: $payload\n";
        $logData .= "Calculated Sign: $sign\n";
        $logData .= "HTTP Code: $httpCode\n";
        $logData .= "Response Body: $response\n";
        $logData .= "Curl Error: $error\n";
        $logData .= "----------------------------------------\n";
        
        $logDir = __DIR__ . '/../storage/logs';
        if (!is_dir($logDir)) { @mkdir($logDir, 0775, true); }
        file_put_contents($logDir . '/cryptomus_debug.log', $logData, FILE_APPEND);
        // -----------------------------

        if ($error) {
            throw new Exception('Cryptomus Curl Error: ' . $error);
        }

        $result = json_decode($response, true);

        return $result;
    }


    public function createPayment(string $orderId, float $amount, string $currency, string $urlSuccess, string $urlReturn, string $urlCallback)
    {
        $data = [
            'amount' => (string)$amount,
            'currency' => $currency,
            'order_id' => $orderId,
            'url_return' => $urlReturn,
            'url_success' => $urlSuccess,
            'url_callback' => $urlCallback,
            'is_payment_multiple' => false,
            'lifetime' => 3600, // 1 hour
            'to_currency' => 'USDT' // Auto convert option if needed, or let user choose
        ];

        return $this->request('/payment', $data);
    }

    // Updated to accept array or string for flexibility (order_id or uuid)
    public function getPaymentStatus($identifier)
    {
        $data = is_array($identifier) ? $identifier : ['uuid' => $identifier];
        return $this->request('/payment/info', $data);
    }
    
    public function verifySignature(array $data): bool
    {
        if (!isset($data['sign'])) {
            return false;
        }
        
        $receivedSign = $data['sign'];
        unset($data['sign']);
        
        // Use JSON_UNESCAPED_UNICODE as per documentation for verification
        // Do NOT use JSON_UNESCAPED_SLASHES because docs say "we send a webhook data with all escaped slashes"
        $payload = json_encode($data, JSON_UNESCAPED_UNICODE);
        $calculatedSign = md5(base64_encode($payload) . $this->apiKey);
        
        return $calculatedSign === $receivedSign;
    }
}
