<?php

class SpeedyIndexClient
{
	private string $baseUrl;
	private string $apiKey;
	private ?int $userId;

	public function __construct(string $baseUrl, string $apiKey, ?int $userId = null)
	{
		$this->baseUrl = rtrim($baseUrl, '/');
		$this->apiKey = $apiKey;
		$this->userId = $userId;
	}

	private function request(string $method, string $path, array $body = null)
	{
		$url = $this->baseUrl . $path;
		$ch = curl_init($url);
		$headers = [
			'Authorization: ' . $this->apiKey,
			'Accept: application/json',
		];
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, strtoupper($method));
		if ($body !== null) {
			$payload = json_encode($body);
			$headers[] = 'Content-Type: application/json';
			curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
		}
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
			require_once __DIR__ . '/Db.php';
		}
		ApiLogger::log($this->userId, $path, $body, $response, $httpCode, $error ?: null, $durationMs);

		return [
			'httpCode' => $httpCode,
			'body' => $response,
			'error' => $error,
		];
	}

	public function getAccount()
	{
		return $this->request('GET', '/v2/account');
	}

	public function createTask(string $searchEngine, string $type, array $urls, ?string $title = null)
	{
		$body = [ 'urls' => array_values($urls) ];
		if ($title !== null) { $body['title'] = $title; }
		$path = '/v2/task/' . $searchEngine . '/' . $type . '/create';
		return $this->request('POST', $path, $body);
	}

	public function listTasks(string $searchEngine, int $page)
	{
		return $this->request('GET', '/v2/task/' . $searchEngine . '/list/' . $page);
	}

	public function statusTasks(string $searchEngine, string $type, array $taskIds)
	{
		$path = '/v2/task/' . $searchEngine . '/' . $type . '/status';
		return $this->request('POST', $path, ['task_ids' => array_values($taskIds)]);
	}

	public function fullReport(string $searchEngine, string $type, string $taskId)
	{
		$path = '/v2/task/' . $searchEngine . '/' . $type . '/fullreport';
		return $this->request('POST', $path, ['task_id' => $taskId]);
	}

	public function vipQueue(string $taskId)
	{
		return $this->request('POST', '/v2/task/google/indexer/vip', ['task_id' => $taskId]);
	}

	public function singleUrl(string $searchEngine, string $url)
	{
		return $this->request('POST', '/v2/' . $searchEngine . '/url', ['url' => $url]);
	}
}
