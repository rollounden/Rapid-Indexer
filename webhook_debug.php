<?php
require_once __DIR__ . '/config/config.php';

echo "<h1>Webhook Debug Logs</h1>";

$log_dir = __DIR__ . '/storage/logs';

echo "<h2>Available Log Files:</h2>";
if (is_dir($log_dir)) {
    $files = scandir($log_dir);
    foreach ($files as $file) {
        if ($file !== '.' && $file !== '..') {
            $file_path = $log_dir . '/' . $file;
            $size = filesize($file_path);
            echo "<h3>$file (" . number_format($size) . " bytes)</h3>";
            
            if ($size > 0) {
                $content = file_get_contents($file_path);
                echo "<pre style='background: #f5f5f5; padding: 10px; border: 1px solid #ddd; max-height: 400px; overflow-y: auto;'>";
                echo htmlspecialchars($content);
                echo "</pre>";
            } else {
                echo "<p>File is empty</p>";
            }
        }
    }
} else {
    echo "<p>Log directory does not exist</p>";
}

echo "<h2>PHP Error Log:</h2>";
$error_log = ini_get('error_log');
if ($error_log && file_exists($error_log)) {
    $content = file_get_contents($error_log);
    echo "<pre style='background: #f5f5f5; padding: 10px; border: 1px solid #ddd; max-height: 400px; overflow-y: auto;'>";
    echo htmlspecialchars($content);
    echo "</pre>";
} else {
    echo "<p>No PHP error log found</p>";
}

echo "<h2>Test Webhook with Error Handling:</h2>";
echo "<form method='POST' action='webhook_debug.php'>";
echo "<input type='hidden' name='test_webhook' value='1'>";
echo "<button type='submit'>Test Webhook with Detailed Error Info</button>";
echo "</form>";

if (isset($_POST['test_webhook'])) {
    echo "<h3>Testing Webhook...</h3>";
    
    // Enable error reporting
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    
    $test_payload = [
        'id' => 'WH-DEBUG-' . time(),
        'event_type' => 'PAYMENT.SALE.COMPLETED',
        'resource' => [
            'id' => 'debug_sale_' . time(),
            'amount' => [
                'total' => '1.00',
                'currency' => 'USD'
            ],
            'state' => 'completed'
        ]
    ];
    
    echo "<p><strong>Test Payload:</strong></p>";
    echo "<pre>" . json_encode($test_payload, JSON_PRETTY_PRINT) . "</pre>";
    
    // Test webhook directly
    $webhook_url = 'https://cyan-peafowl-394593.hostingersite.com/paypal_webhook.php';
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $webhook_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($test_payload));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'PayPal-Transmission-Id: debug-transmission-' . time(),
        'PayPal-Transmission-Sig: debug-signature',
        'PayPal-Transmission-Time: ' . date('c')
    ]);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    echo "<p><strong>HTTP Status:</strong> $http_code</p>";
    echo "<p><strong>Response:</strong> " . htmlspecialchars($response) . "</p>";
    
    if ($error) {
        echo "<p><strong>cURL Error:</strong> $error</p>";
    }
}

echo "<hr>";
echo "<p><a href='webhook_test_suite.php'>Back to Test Suite</a></p>";
?>
