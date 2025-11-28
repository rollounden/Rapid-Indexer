<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/src/CryptomusService.php';

// Force log errors
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/storage/logs/cryptomus_webhook_debug.log');

// Read raw POST data
$rawInput = file_get_contents('php://input');
$data = json_decode($rawInput, true);

// Log EVERYTHING for debugging
$logFile = __DIR__ . '/storage/logs/cryptomus_webhook_debug.log';
$logEntry = date('Y-m-d H:i:s') . " WEBHOOK RECEIVED:\nHeaders: " . json_encode(getallheaders()) . "\nPayload: " . $rawInput . "\n-------------------\n";
file_put_contents($logFile, $logEntry, FILE_APPEND);

if (!$data) {
    http_response_code(400);
    exit('Invalid JSON');
}

try {
    $service = new CryptomusService();
    $service->handleWebhook($data);
    echo 'OK';
} catch (Exception $e) {
    // Log error
    $entry = date('Y-m-d H:i:s') . " EXCEPTION: " . $e->getMessage() . "\n";
    file_put_contents($logFile, $entry, FILE_APPEND);
    
    http_response_code(400);
    echo 'Error: ' . $e->getMessage();
}
