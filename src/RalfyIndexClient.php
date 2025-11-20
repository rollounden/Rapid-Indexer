<?php

class RalfyIndexClient
{
	private string $baseUrl = 'https://api.ralfyindex.com';
	private string $apiKey;
	private ?int $userId;

	public function __construct(string $apiKey, ?int $userId = null)
	{
		$this->apiKey = $apiKey;
		$this->userId = $userId;
	}

	private function request(string $endpoint, array $data = [])
	{
		$url = $this->baseUrl . $endpoint;
		
        // Add API key to data
        $data['apikey'] = $this->apiKey;
        
        $payload = json_encode($data);

		$ch = curl_init($url);
		$headers = [
			'Content-Type: application/json',
            'Accept: application/json'
		];
        
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        
		$start = microtime(true);
		$response = curl_exec($ch);
		$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		$error = curl_error($ch);
		curl_close($ch);
		$durationMs = (int) round((microtime(true) - $start) * 1000);

		// Log
		if (!class_exists('ApiLogger')) {
			require_once __DIR__ . '/ApiLogger.php';
            if (!class_exists('Db')) {
			    require_once __DIR__ . '/Db.php';
            }
		}
		ApiLogger::log($this->userId, $endpoint, $data, $response, $httpCode, $error ?: null, $durationMs);

		return [
			'httpCode' => $httpCode,
			'body' => $response,
			'error' => $error,
		];
	}

	public function getStatus()
	{
		return $this->request('/status');
	}

	public function getBalance()
	{
		return $this->request('/balance');
	}

	public function createProject(array $urls, ?string $projectName = null)
	{
        $data = [
            'urls' => array_values($urls)
        ];
        if ($projectName) {
            $data['projectName'] = $projectName;
        }
		return $this->request('/project', $data);
	}
}

