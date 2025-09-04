<?php
// Simple webhook test script
echo "<h1>PayPal Webhook Test</h1>";

// Test webhook URL
$webhook_url = 'https://' . $_SERVER['HTTP_HOST'] . '/paypal_webhook.php';
echo "<p><strong>Webhook URL:</strong> <a href='$webhook_url' target='_blank'>$webhook_url</a></p>";

// Test webhook with sample data
$test_data = [
    'event_type' => 'PAYMENT.CAPTURE.COMPLETED',
    'resource' => [
        'id' => 'test_capture_id_123',
        'amount' => [
            'value' => '1.00',
            'currency_code' => 'USD'
        ],
        'custom_id' => '1', // user_id
        'status' => 'COMPLETED'
    ]
];

echo "<h2>Test Webhook with Sample Data</h2>";
echo "<pre>" . json_encode($test_data, JSON_PRETTY_PRINT) . "</pre>";

echo "<h2>Instructions:</h2>";
echo "<ol>";
echo "<li>Copy the webhook URL above</li>";
echo "<li>Go to your PayPal Developer Dashboard</li>";
echo "<li>Add the webhook URL to your sandbox configuration</li>";
echo "<li>Select these events:</li>";
echo "<ul>";
echo "<li><strong>PAYMENT.CAPTURE.COMPLETED</strong> - When payment is successful</li>";
echo "<li><strong>PAYMENT.CAPTURE.DENIED</strong> - When payment is denied</li>";
echo "<li><strong>PAYMENT.CAPTURE.DECLINED</strong> - When payment is declined</li>";
echo "<li><strong>PAYMENT.CAPTURE.REFUNDED</strong> - When payment is refunded</li>";
echo "<li><strong>PAYMENT.CAPTURE.PENDING</strong> - When payment is pending</li>";
echo "<li><strong>PAYMENT.CAPTURE.REVERSED</strong> - When payment is reversed</li>";
echo "</ul>";
echo "<li>Test a payment from your payments page</li>";
echo "<li>Check the webhook logs at: storage/logs/paypal_webhooks.log</li>";
echo "</ol>";

echo "<h2>Current Configuration:</h2>";
echo "<ul>";
echo "<li>Environment: " . (defined('PAYPAL_ENV') ? PAYPAL_ENV : 'Not set') . "</li>";
echo "<li>Client ID: " . (defined('PAYPAL_CLIENT_ID') ? substr(PAYPAL_CLIENT_ID, 0, 20) . '...' : 'Not set') . "</li>";
echo "<li>Price per credit: $" . (defined('PRICE_PER_CREDIT_USD') ? PRICE_PER_CREDIT_USD : 'Not set') . "</li>";
echo "</ul>";
?>
