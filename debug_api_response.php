<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/src/Db.php';

echo "<h1>API Response Debug</h1>";

try {
    $pdo = Db::conn();
    
    // Get the latest API log entries
    $stmt = $pdo->prepare('SELECT * FROM api_logs WHERE endpoint LIKE ? ORDER BY created_at DESC LIMIT 5');
    $stmt->execute(['%fullreport%']);
    $logs = $stmt->fetchAll();
    
    if (empty($logs)) {
        echo "<p>No fullreport API logs found.</p>";
        exit;
    }
    
    foreach ($logs as $log) {
        echo "<h2>API Log #" . $log['id'] . "</h2>";
        echo "<p><strong>Endpoint:</strong> " . $log['endpoint'] . "</p>";
        echo "<p><strong>Status:</strong> " . $log['status_code'] . "</p>";
        echo "<p><strong>Created:</strong> " . $log['created_at'] . "</p>";
        
        echo "<h3>Request Payload:</h3>";
        echo "<pre style='background: #f5f5f5; padding: 10px; border: 1px solid #ddd; max-height: 200px; overflow-y: auto;'>";
        echo htmlspecialchars(json_encode(json_decode($log['request_payload']), JSON_PRETTY_PRINT));
        echo "</pre>";
        
        echo "<h3>Response Payload:</h3>";
        echo "<pre style='background: #f5f5f5; padding: 10px; border: 1px solid #ddd; max-height: 400px; overflow-y: auto;'>";
        echo htmlspecialchars(json_encode(json_decode($log['response_payload']), JSON_PRETTY_PRINT));
        echo "</pre>";
        
        echo "<hr>";
    }
    
} catch (Exception $e) {
    echo "<p>❌ Error: " . $e->getMessage() . "</p>";
}

echo "<p><a href='tasks.php'>Back to Tasks</a></p>";
?>
