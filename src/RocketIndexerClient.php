<?php

class RocketIndexerClient
{
    private string $baseUrl = 'https://rocketindexer.com/api/index.php';
    private string $apiKey;
    private ?int $userId;

    public function __construct(string $apiKey, ?int $userId = null)
    {
        $this->apiKey = $apiKey;
        $this->userId = $userId;
    }

    private function request(string $method, string $endpointName, array $data = [])
    {
        // Remove leading slash from endpoint if present (e.g. '/balance' -> 'balance')
        $endpointName = ltrim($endpointName, '/');

        // Build URL with query parameters for token and endpoint
        $queryParams = [
            'token' => $this->apiKey,
            'endpoint' => $endpointName
        ];

        $ch = curl_init();

        if ($method === 'GET') {
            // For GET, add data to query params
            $queryParams = array_merge($queryParams, $data);
            $url = $this->baseUrl . '?' . http_build_query($queryParams);
        } else {
            // For POST, token and endpoint are in URL, data is in body
            $url = $this->baseUrl . '?' . http_build_query($queryParams);
            
            curl_setopt($ch, CURLOPT_POST, true);
            // Data is sent as JSON body
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
            ApiLogger::log($this->userId, $endpointName, $data, $response, $httpCode, $error ?: null, $durationMs);
        }

        return [
            'httpCode' => $httpCode,
            'body' => $response,
            'error' => $error,
        ];
    }

    public function getBalance()
    {
        return $this->request('GET', 'balance');
    }

    public function submitUrl(string $url)
    {
        // API expects "urls" array
        return $this->request('POST', 'submit', ['urls' => [$url]]);
    }

    public function getStatus(string $trackingId)
    {
        // API expects "ids" (comma separated) and "limit"
        // Adjusting to match the new API signature based on the user provided info
        // User said: endpoint=status&limit=10&ids=123,456
        return $this->request('GET', 'status', ['ids' => $trackingId]);
    }
}
