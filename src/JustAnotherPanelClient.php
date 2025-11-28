<?php

class JustAnotherPanelClient
{
    private string $apiUrl;
    private string $apiKey;

    public function __construct(string $apiUrl, string $apiKey)
    {
        $this->apiUrl = $apiUrl;
        $this->apiKey = $apiKey;
    }

    private function request(array $params): array
    {
        $params['key'] = $this->apiKey;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->apiUrl);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // In production, this should be true
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            return ['error' => $error, 'http_code' => $httpCode];
        }

        $decoded = json_decode($response, true);
        return ['body' => $decoded, 'http_code' => $httpCode, 'raw' => $response];
    }

    public function getServices(): array
    {
        return $this->request([
            'action' => 'services'
        ]);
    }

    public function addOrder(array $orderData): array
    {
        // orderData should contain: service, link, quantity, etc.
        $params = array_merge(['action' => 'add'], $orderData);
        return $this->request($params);
    }

    public function getOrderStatus(int $orderId): array
    {
        return $this->request([
            'action' => 'status',
            'order' => $orderId
        ]);
    }

    public function getBalance(): array
    {
        return $this->request([
            'action' => 'balance'
        ]);
    }
}

