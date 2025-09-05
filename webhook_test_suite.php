<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/src/Db.php';

echo "<h1>PayPal Webhook Test Suite</h1>";

// Test 1: Check webhook URL accessibility
echo "<h2>1. Webhook URL Test</h2>";
$webhook_url = 'https://cyan-peafowl-394593.hostingersite.com/paypal_webhook.php';
echo "<p><strong>Webhook URL:</strong> <a href='$webhook_url' target='_blank'>$webhook_url</a></p>";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $webhook_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['test' => 'connectivity']));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

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

// Test 2: Check log files
echo "<h2>2. Log Files Check</h2>";
$log_dir = __DIR__ . '/storage/logs';
if (is_dir($log_dir)) {
    echo "<p>✅ Log directory exists</p>";
    $files = scandir($log_dir);
    foreach ($files as $file) {
        if ($file !== '.' && $file !== '..') {
            $file_path = $log_dir . '/' . $file;
            $size = filesize($file_path);
            echo "<p><strong>$file:</strong> " . number_format($size) . " bytes</p>";
            
            if ($size > 0) {
                echo "<details><summary>View content</summary><pre>" . htmlspecialchars(file_get_contents($file_path)) . "</pre></details>";
            }
        }
    }
} else {
    echo "<p>❌ Log directory does not exist</p>";
}

// Test 3: Database webhook events
echo "<h2>3. Database Webhook Events</h2>";
try {
    $pdo = Db::conn();
    $stmt = $pdo->query('SELECT COUNT(*) FROM webhook_events');
    $count = $stmt->fetchColumn();
    echo "<p><strong>Webhook Events in DB:</strong> $count</p>";
    
    if ($count > 0) {
        $stmt = $pdo->query('SELECT * FROM webhook_events ORDER BY created_at DESC LIMIT 5');
        $events = $stmt->fetchAll();
        echo "<table border='1'>";
        echo "<tr><th>ID</th><th>Provider</th><th>Event Type</th><th>Status</th><th>Created</th></tr>";
        foreach ($events as $event) {
            echo "<tr>";
            echo "<td>" . $event['id'] . "</td>";
            echo "<td>" . $event['provider'] . "</td>";
            echo "<td>" . $event['event_type'] . "</td>";
            echo "<td>" . $event['status'] . "</td>";
            echo "<td>" . $event['created_at'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
} catch (Exception $e) {
    echo "<p>❌ Database error: " . $e->getMessage() . "</p>";
}

// Test 4: Simulate webhook event
echo "<h2>4. Simulate Webhook Event</h2>";
$test_payload = [
    'id' => 'WH-TEST-' . time(),
    'event_type' => 'PAYMENT.SALE.COMPLETED',
    'resource' => [
        'id' => 'test_sale_' . time(),
        'amount' => [
            'total' => '1.00',
            'currency' => 'USD'
        ],
        'state' => 'completed'
    ]
];

echo "<p><strong>Test Payload:</strong></p>";
echo "<pre>" . json_encode($test_payload, JSON_PRETTY_PRINT) . "</pre>";

// Send test webhook
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $webhook_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($test_payload));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'PayPal-Transmission-Id: test-transmission-' . time(),
    'PayPal-Transmission-Sig: test-signature',
    'PayPal-Transmission-Time: ' . date('c')
]);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "<p><strong>Test Response:</strong> HTTP $http_code</p>";
echo "<p><strong>Response Body:</strong> " . htmlspecialchars($response) . "</p>";

// Test 5: Check if webhook was processed
echo "<h2>5. Check Webhook Processing</h2>";
sleep(2); // Wait a moment for processing

try {
    $stmt = $pdo->query('SELECT COUNT(*) FROM webhook_events WHERE external_event_id LIKE "WH-TEST-%"');
    $test_count = $stmt->fetchColumn();
    echo "<p><strong>Test Webhook Events:</strong> $test_count</p>";
    
    if ($test_count > 0) {
        echo "<p>✅ Test webhook was processed!</p>";
    } else {
        echo "<p>❌ Test webhook was not processed</p>";
    }
} catch (Exception $e) {
    echo "<p>❌ Error checking test webhook: " . $e->getMessage() . "</p>";
}

// Test 6: PayPal Configuration
echo "<h2>6. PayPal Configuration</h2>";
echo "<ul>";
echo "<li><strong>Environment:</strong> " . PAYPAL_ENV . "</li>";
echo "<li><strong>Client ID:</strong> " . substr(PAYPAL_CLIENT_ID, 0, 20) . "...</li>";
echo "<li><strong>Webhook Secret:</strong> " . PAYPAL_WEBHOOK_SECRET . "</li>";
echo "<li><strong>BN Code:</strong> " . PAYPAL_BN_CODE . "</li>";
echo "</ul>";

echo "<h2>Next Steps:</h2>";
echo "<ol>";
echo "<li>Update PayPal webhook URL to point to /paypal_webhook.php</li>";
echo "<li>Test webhook simulator again</li>";
echo "<li>Check logs for incoming events</li>";
echo "<li>Verify webhook processing</li>";
echo "</ol>";

echo "<hr>";
echo "<p><a href='webhook_status.php'>Webhook Status</a> | <a href='test_webhook_manual.php'>Manual Test</a> | <a href='payments.php'>Payments</a></p>";
?>
