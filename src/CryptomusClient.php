<?php

class CryptomusClient
{
    private string $merchantId;
    private string $apiKey;
    private string $baseUrl = 'https://api.cryptomus.com/v1';

    public function __construct(string $merchantId, string $apiKey)
    {
        $this->merchantId = $merchantId;
        $this->apiKey = $apiKey;
    }

    private function request(string $endpoint, array $data = [])
    {
        $url = $this->baseUrl . $endpoint;
        $payload = json_encode($data);
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

        $response = curl_exec($ch);
        $error = curl_error($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($error) {
            throw new Exception('Cryptomus Curl Error: ' . $error);
        }

        $result = json_decode($response, true);
        
        // Log API call if needed (optional)
        // file_put_contents(__DIR__ . '/../storage/logs/cryptomus.log', date('Y-m-d H:i:s') . " $endpoint $httpCode " . json_encode($result) . "\n", FILE_APPEND);

        return $result;
    }

    public function createPayment(string $orderId, float $amount, string $currency, string $urlReturn, string $urlCallback)
    {
        $data = [
            'amount' => (string)$amount,
            'currency' => $currency,
            'order_id' => $orderId,
            'url_return' => $urlReturn,
            'url_callback' => $urlCallback,
            'is_payment_multiple' => false,
            'lifetime' => 3600, // 1 hour
            'to_currency' => 'USDT' // Auto convert option if needed, or let user choose
        ];

        return $this->request('/payment', $data);
    }

    public function getPaymentStatus(string $uuid)
    {
        return $this->request('/payment/info', ['uuid' => $uuid]);
    }
    
    public function verifySignature(array $data): bool
    {
        if (!isset($data['sign'])) {
            return false;
        }
        
        $receivedSign = $data['sign'];
        unset($data['sign']);
        
        $payload = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $calculatedSign = md5(base64_encode($payload) . $this->apiKey);
        
        return $calculatedSign === $receivedSign;
    }
}

