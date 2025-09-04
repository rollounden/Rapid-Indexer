<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/src/PayPalService.php';

echo "<h1>PayPal Integration Test</h1>";

try {
    echo "<h2>1. Testing PayPal Service Initialization</h2>";
    $paypal = new PayPalService();
    echo "✅ PayPal service initialized successfully<br>";
    
    echo "<h2>2. Testing Access Token</h2>";
    $access_token = $paypal->getAccessToken();
    echo "✅ Access token obtained: " . substr($access_token, 0, 20) . "...<br>";
    
    echo "<h2>3. Testing Order Creation (Test Mode)</h2>";
    $order = $paypal->createOrder(1.00, 'USD', 'test_user_123', 'Test Order - 100 credits');
    echo "✅ Order created successfully<br>";
    echo "Order ID: " . $order['id'] . "<br>";
    echo "Status: " . $order['status'] . "<br>";
    
    echo "<h2>4. Testing Order Retrieval</h2>";
    $retrieved_order = $paypal->getOrder($order['id']);
    echo "✅ Order retrieved successfully<br>";
    echo "Retrieved Order ID: " . $retrieved_order['id'] . "<br>";
    echo "Retrieved Status: " . $retrieved_order['status'] . "<br>";
    
    echo "<h2>5. PayPal Configuration Summary</h2>";
    echo "Environment: " . PAYPAL_ENV . "<br>";
    echo "Client ID: " . substr(PAYPAL_CLIENT_ID, 0, 20) . "...<br>";
    echo "BN Code: " . PAYPAL_BN_CODE . "<br>";
    echo "Base URL: " . (PAYPAL_ENV === 'live' ? 'https://api-m.paypal.com' : 'https://api-m.sandbox.paypal.com') . "<br>";
    
    echo "<h2>6. Webhook URL</h2>";
    $webhook_url = 'https://' . $_SERVER['HTTP_HOST'] . '/paypal_webhook.php';
    echo "Webhook URL: <a href='$webhook_url' target='_blank'>$webhook_url</a><br>";
    echo "Make sure to add this URL to your PayPal webhook configuration<br>";
    
    echo "<h2>7. Test Links</h2>";
    echo "<a href='payments.php' class='btn btn-primary'>Test Payment Page</a><br>";
    echo "<a href='payment_success.php?token=test' class='btn btn-success'>Test Success Page</a><br>";
    echo "<a href='payment_cancel.php?token=test' class='btn btn-warning'>Test Cancel Page</a><br>";
    
} catch (Exception $e) {
    echo "<h2>❌ Error</h2>";
    echo "Error: " . $e->getMessage() . "<br>";
    echo "File: " . $e->getFile() . "<br>";
    echo "Line: " . $e->getLine() . "<br>";
}

echo "<hr>";
echo "<h2>Next Steps:</h2>";
echo "<ol>";
echo "<li>Add the webhook URL to your PayPal sandbox configuration</li>";
echo "<li>Test a payment flow from the payments page</li>";
echo "<li>Check webhook logs in storage/logs/paypal_webhooks.log</li>";
echo "<li>Verify credits are added correctly</li>";
echo "</ol>";
?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; }
.btn { display: inline-block; padding: 10px 20px; margin: 5px; text-decoration: none; border-radius: 5px; }
.btn-primary { background: #007bff; color: white; }
.btn-success { background: #28a745; color: white; }
.btn-warning { background: #ffc107; color: black; }
</style>
