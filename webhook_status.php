<?php
require_once __DIR__ . '/config/config.php';

echo "<h1>Webhook Status Check</h1>";

$webhook_url = 'https://cyan-peafowl-394593.hostingersite.com/paypal_webhook.php';

echo "<h2>Webhook URL:</h2>";
echo "<p><a href='$webhook_url' target='_blank'>$webhook_url</a></p>";

echo "<h2>Testing Webhook Accessibility:</h2>";

// Test if webhook is accessible
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $webhook_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_setopt($ch, CURLOPT_USERAGENT, 'Webhook-Test/1.0');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['test' => 'ping']));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json'
]);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

if ($error) {
    echo "<p>❌ <strong>Error:</strong> $error</p>";
} else {
    echo "<p>✅ <strong>HTTP Status:</strong> $http_code</p>";
    echo "<p><strong>Response:</strong> " . htmlspecialchars($response) . "</p>";
}

echo "<h2>Webhook Configuration:</h2>";
echo "<ul>";
echo "<li><strong>Environment:</strong> " . PAYPAL_ENV . "</li>";
echo "<li><strong>Webhook Secret:</strong> " . PAYPAL_WEBHOOK_SECRET . "</li>";
echo "<li><strong>Client ID:</strong> " . substr(PAYPAL_CLIENT_ID, 0, 20) . "...</li>";
echo "</ul>";

echo "<h2>Log Files:</h2>";
$log_dir = __DIR__ . '/storage/logs';
if (is_dir($log_dir)) {
    $files = scandir($log_dir);
    echo "<p>✅ Log directory exists</p>";
    echo "<p><strong>Files:</strong></p>";
    echo "<ul>";
    foreach ($files as $file) {
        if ($file !== '.' && $file !== '..') {
            $file_path = $log_dir . '/' . $file;
            $size = filesize($file_path);
            echo "<li>$file (" . number_format($size) . " bytes)</li>";
        }
    }
    echo "</ul>";
} else {
    echo "<p>❌ Log directory does not exist</p>";
}

echo "<h2>Recent Webhook Events:</h2>";
if (file_exists($log_dir . '/paypal_webhooks.log')) {
    $content = file_get_contents($log_dir . '/paypal_webhooks.log');
    if ($content) {
        echo "<pre>" . htmlspecialchars($content) . "</pre>";
    } else {
        echo "<p>No webhook events logged yet</p>";
    }
} else {
    echo "<p>No webhook log file found</p>";
}

echo "<h2>Test Webhook Manually:</h2>";
echo "<p><a href='test_webhook_manual.php'>Run Manual Webhook Test</a></p>";

echo "<h2>PayPal Sandbox Test:</h2>";
echo "<p><a href='sandbox_test.php'>Sandbox Testing Guide</a></p>";

echo "<h2>Next Steps:</h2>";
echo "<ol>";
echo "<li>Check if PayPal can reach your webhook URL</li>";
echo "<li>Verify webhook events are being sent</li>";
echo "<li>Check webhook logs for incoming events</li>";
echo "<li>Test with PayPal webhook simulator</li>";
echo "</ol>";
?>
