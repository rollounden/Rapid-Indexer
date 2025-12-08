<?php

class RocketIndexerClient
{
    private string $baseUrl = 'https://api.rocketindexer.com';
    private string $apiKey;
    private ?int $userId;

    public function __construct(string $apiKey, ?int $userId = null)
    {
        $this->apiKey = $apiKey;
        $this->userId = $userId;
    }

    private function request(string $method, string $endpoint, array $data = [])
    {
        $url = $this->baseUrl . $endpoint;
        
        $ch = curl_init();
        
        if ($method === 'GET') {
            $data['token'] = $this->apiKey;
            $url .= '?' . http_build_query($data);
        } else {
            // POST
            $data['token'] = $this->apiKey;
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $headers = [
            'Content-Type: application/json',
            'Accept: application/json'
        ];
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $start = microtime(true);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        $durationMs = (int) round((microtime(true) - $start) * 1000);

        // Log using existing infrastructure
        if (class_exists('ApiLogger')) {
            ApiLogger::log($this->userId, $endpoint, $data, $response, $httpCode, $error ?: null, $durationMs);
        }

        return [
            'httpCode' => $httpCode,
            'body' => $response,
            'error' => $error,
        ];
    }

    public function getBalance()
    {
        return $this->request('GET', '/balance');
    }

    public function submitUrl(string $url)
    {
        return $this->request('POST', '/index', ['url' => $url]);
    }

    public function getStatus(string $trackingId)
    {
        return $this->request('GET', '/status', ['id' => $trackingId]);
    }
}

