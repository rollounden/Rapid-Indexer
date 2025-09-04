<?php

class ApiLogger
{
	public static function log(?int $userId, string $endpoint, $requestPayload, $responsePayload, ?int $statusCode, ?string $errorMessage, ?int $durationMs = null): void
	{
		try {
			$pdo = Db::conn();
			$stmt = $pdo->prepare('INSERT INTO api_logs (user_id, endpoint, request_payload, response_payload, status_code, error_message, duration_ms) VALUES (?, ?, ?, ?, ?, ?, ?)');
			$stmt->execute([
				$userId,
				$endpoint,
				is_string($requestPayload) ? $requestPayload : json_encode($requestPayload),
				is_string($responsePayload) ? $responsePayload : json_encode($responsePayload),
				$statusCode,
				$errorMessage,
				$durationMs,
			]);
		} catch (Throwable $e) {
			// ignore logging failures in MVP
		}
	}
}
