<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/src/Db.php';

echo "<h1>Webhook Events Test</h1>";

try {
    $pdo = Db::conn();
    
    // Test inserting a webhook event manually
    echo "<h2>1. Testing Manual Webhook Event Insert</h2>";
    
    $test_data = [
        'provider' => 'paypal',
        'external_event_id' => 'test_' . time(),
        'event_type' => 'PAYMENT.CAPTURE.COMPLETED',
        'signature' => 'test_signature',
        'payload' => json_encode(['test' => 'data']),
        'status' => 'received'
    ];
    
    $stmt = $pdo->prepare('INSERT INTO webhook_events (provider, external_event_id, event_type, signature, payload, status, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())');
    $result = $stmt->execute([
        $test_data['provider'],
        $test_data['external_event_id'],
        $test_data['event_type'],
        $test_data['signature'],
        $test_data['payload'],
        $test_data['status']
    ]);
    
    if ($result) {
        echo "<p>✅ <strong>Manual insert successful!</strong></p>";
        echo "<p>Inserted event ID: " . $test_data['external_event_id'] . "</p>";
    } else {
        echo "<p>❌ <strong>Manual insert failed!</strong></p>";
    }
    
    // Check current webhook events
    echo "<h2>2. Current Webhook Events</h2>";
    $stmt = $pdo->prepare('SELECT * FROM webhook_events ORDER BY created_at DESC LIMIT 5');
    $stmt->execute();
    $events = $stmt->fetchAll();
    
    if (empty($events)) {
        echo "<p>❌ <strong>No webhook events found</strong></p>";
    } else {
        echo "<p>✅ <strong>Found " . count($events) . " webhook events:</strong></p>";
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>ID</th><th>Provider</th><th>Event ID</th><th>Event Type</th><th>Status</th><th>Created</th></tr>";
        foreach ($events as $event) {
            echo "<tr>";
            echo "<td>" . $event['id'] . "</td>";
            echo "<td>" . $event['provider'] . "</td>";
            echo "<td>" . $event['external_event_id'] . "</td>";
            echo "<td>" . $event['event_type'] . "</td>";
            echo "<td>" . $event['status'] . "</td>";
            echo "<td>" . $event['created_at'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    // Test webhook table structure
    echo "<h2>3. Webhook Events Table Structure</h2>";
    $stmt = $pdo->prepare('DESCRIBE webhook_events');
    $stmt->execute();
    $columns = $stmt->fetchAll();
    
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    foreach ($columns as $column) {
        echo "<tr>";
        echo "<td>" . $column['Field'] . "</td>";
        echo "<td>" . $column['Type'] . "</td>";
        echo "<td>" . $column['Null'] . "</td>";
        echo "<td>" . $column['Key'] . "</td>";
        echo "<td>" . $column['Default'] . "</td>";
        echo "<td>" . $column['Extra'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
} catch (Exception $e) {
    echo "<p>❌ <strong>Error:</strong> " . $e->getMessage() . "</p>";
    echo "<p><strong>File:</strong> " . $e->getFile() . "</p>";
    echo "<p><strong>Line:</strong> " . $e->getLine() . "</p>";
}

echo "<hr>";
echo "<p><a href='test_webhook.php'>Test Webhook</a> | <a href='payments.php'>Payments</a></p>";
?>
