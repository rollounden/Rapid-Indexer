<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/src/CryptomusService.php';

// Read raw POST data
$rawInput = file_get_contents('php://input');
$data = json_decode($rawInput, true);

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
    $logFile = __DIR__ . '/storage/logs/cryptomus_webhook.log';
    $entry = date('Y-m-d H:i:s') . " Error: " . $e->getMessage() . "\nPayload: " . $rawInput . "\n";
    file_put_contents($logFile, $entry, FILE_APPEND);
    
    http_response_code(400);
    echo 'Error: ' . $e->getMessage();
}

