<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/src/PayPalService.php';

echo "<h1>PayPal Payment Flow Diagnostic</h1>";

echo "<h2>1. PayPal Configuration Check</h2>";
echo "<ul>";
echo "<li><strong>Environment:</strong> " . PAYPAL_ENV . "</li>";
echo "<li><strong>Client ID:</strong> " . substr(PAYPAL_CLIENT_ID, 0, 20) . "...</li>";
echo "<li><strong>Webhook Secret:</strong> " . PAYPAL_WEBHOOK_SECRET . "</li>";
echo "<li><strong>BN Code:</strong> " . PAYPAL_BN_CODE . "</li>";
echo "</ul>";

echo "<h2>2. Test PayPal API Connection</h2>";
try {
    $paypal = new PayPalService();
    $access_token = $paypal->getAccessToken();
    echo "<p>✅ PayPal API connection successful</p>";
    echo "<p><strong>Access Token:</strong> " . substr($access_token, 0, 20) . "...</p>";
} catch (Exception $e) {
    echo "<p>❌ PayPal API connection failed: " . $e->getMessage() . "</p>";
}

echo "<h2>3. Test Order Creation</h2>";
try {
    $paypal = new PayPalService();
    $order = $paypal->createOrder(1.00, 'USD', 1, 'Test Order - 100 credits');
    echo "<p>✅ Order creation successful</p>";
    echo "<p><strong>Order ID:</strong> " . $order['id'] . "</p>";
    echo "<p><strong>Status:</strong> " . $order['status'] . "</p>";
    
    // Show approval URL
    foreach ($order['links'] as $link) {
        if ($link['rel'] === 'approve') {
            echo "<p><strong>Approval URL:</strong> <a href='" . $link['href'] . "' target='_blank'>" . $link['href'] . "</a></p>";
            break;
        }
    }
} catch (Exception $e) {
    echo "<p>❌ Order creation failed: " . $e->getMessage() . "</p>";
}

echo "<h2>4. Webhook URL Check</h2>";
$webhook_url = 'https://cyan-peafowl-394593.hostingersite.com/paypal_webhook.php';
echo "<p><strong>Webhook URL:</strong> <a href='$webhook_url' target='_blank'>$webhook_url</a></p>";

// Test webhook accessibility
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $webhook_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['test' => 'ping']));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

if ($error) {
    echo "<p>❌ <strong>Webhook Error:</strong> $error</p>";
} else {
    echo "<p>✅ <strong>Webhook HTTP Status:</strong> $http_code</p>";
    echo "<p><strong>Response:</strong> " . htmlspecialchars($response) . "</p>";
}

echo "<h2>5. Recent Webhook Events</h2>";
$log_dir = __DIR__ . '/storage/logs';
if (is_dir($log_dir)) {
    $files = scandir($log_dir);
    foreach ($files as $file) {
        if ($file !== '.' && $file !== '..' && strpos($file, 'webhook') !== false) {
            $file_path = $log_dir . '/' . $file;
            $size = filesize($file_path);
            echo "<h3>$file (" . number_format($size) . " bytes)</h3>";
            
            if ($size > 0) {
                $content = file_get_contents($file_path);
                echo "<pre style='background: #f5f5f5; padding: 10px; border: 1px solid #ddd; max-height: 300px; overflow-y: auto;'>";
                echo htmlspecialchars($content);
                echo "</pre>";
            }
        }
    }
}

echo "<h2>6. PayPal Sandbox Settings to Check</h2>";
echo "<div style='background: #fff3cd; padding: 15px; border: 1px solid #ffeaa7; border-radius: 5px;'>";
echo "<h3>⚠️ Important PayPal Sandbox Settings:</h3>";
echo "<ol>";
echo "<li><strong>Payment Review:</strong> Should be DISABLED for testing</li>";
echo "<li><strong>Webhook URL:</strong> Must be exactly: <code>https://cyan-peafowl-394593.hostingersite.com/paypal_webhook.php</code></li>";
echo "<li><strong>Webhook Events:</strong> Must include <code>PAYMENT.CAPTURE.COMPLETED</code> and <code>PAYMENT.SALE.COMPLETED</code></li>";
echo "<li><strong>Sandbox Account:</strong> Use <code>sb-j2gdy45737228@business.example.com</code></li>";
echo "</ol>";
echo "</div>";

echo "<h2>7. Test Payment Flow</h2>";
echo "<p><a href='payments.php' class='btn btn-primary'>Try a Real Payment</a></p>";
echo "<p><a href='webhook_test_suite.php' class='btn btn-info'>Test Webhook Suite</a></p>";

echo "<h2>8. Common Issues & Solutions</h2>";
echo "<div style='background: #d1ecf1; padding: 15px; border: 1px solid #bee5eb; border-radius: 5px;'>";
echo "<h3>🔧 Troubleshooting:</h3>";
echo "<ul>";
echo "<li><strong>Payment Review Enabled:</strong> Disable in PayPal sandbox settings</li>";
echo "<li><strong>Wrong Webhook URL:</strong> Update to point to /paypal_webhook.php</li>";
echo "<li><strong>Missing Events:</strong> Add PAYMENT.CAPTURE.COMPLETED and PAYMENT.SALE.COMPLETED</li>";
echo "<li><strong>HTTPS Required:</strong> PayPal requires HTTPS for webhooks</li>";
echo "<li><strong>Sandbox vs Live:</strong> Make sure you're testing in sandbox mode</li>";
echo "</ul>";
echo "</div>";

echo "<style>";
echo ".btn { display: inline-block; padding: 10px 20px; margin: 5px; text-decoration: none; border-radius: 5px; }";
echo ".btn-primary { background: #007bff; color: white; }";
echo ".btn-info { background: #17a2b8; color: white; }";
echo "</style>";
?>
