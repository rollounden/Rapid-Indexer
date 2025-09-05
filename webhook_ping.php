<?php
// Simple webhook ping test
header('Content-Type: application/json');

$log_data = [
    'timestamp' => date('Y-m-d H:i:s'),
    'method' => $_SERVER['REQUEST_METHOD'],
    'headers' => getallheaders(),
    'payload' => file_get_contents('php://input'),
    'get_params' => $_GET,
    'post_params' => $_POST
];

// Ensure logs directory exists
$log_dir = __DIR__ . '/storage/logs';
if (!is_dir($log_dir)) {
    mkdir($log_dir, 0755, true);
}

// Log the ping
file_put_contents($log_dir . '/webhook_ping.log', 
    json_encode($log_data, JSON_PRETTY_PRINT) . "\n\n", 
    FILE_APPEND | LOCK_EX);

// Return success response
http_response_code(200);
echo json_encode([
    'status' => 'success',
    'message' => 'Webhook ping received',
    'timestamp' => date('Y-m-d H:i:s'),
    'method' => $_SERVER['REQUEST_METHOD']
]);
?>
