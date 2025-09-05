<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/src/Db.php';

echo "<h1>API Response Structure Analysis</h1>";

try {
    $pdo = Db::conn();
    
    // Get the latest fullreport API log
    $stmt = $pdo->prepare('SELECT * FROM api_logs WHERE endpoint LIKE ? ORDER BY created_at DESC LIMIT 1');
    $stmt->execute(['%fullreport%']);
    $log = $stmt->fetch();
    
    if (!$log) {
        echo "<p>No fullreport API logs found.</p>";
        exit;
    }
    
    echo "<h2>Latest Full Report Response</h2>";
    echo "<p><strong>Task ID:</strong> " . $log['request_payload'] . "</p>";
    echo "<p><strong>Status Code:</strong> " . $log['status_code'] . "</p>";
    echo "<p><strong>Created:</strong> " . $log['created_at'] . "</p>";
    
    $response = json_decode($log['response_payload'], true);
    
    echo "<h3>Full Response Structure:</h3>";
    echo "<pre style='background: #f5f5f5; padding: 10px; border: 1px solid #ddd; max-height: 600px; overflow-y: auto;'>";
    echo htmlspecialchars(json_encode($response, JSON_PRETTY_PRINT));
    echo "</pre>";
    
    echo "<h3>Response Analysis:</h3>";
    echo "<p><strong>Code:</strong> " . ($response['code'] ?? 'N/A') . "</p>";
    
    if (isset($response['result'])) {
        $result = $response['result'];
        echo "<p><strong>Result ID:</strong> " . ($result['id'] ?? 'N/A') . "</p>";
        
        // Check for different possible structures
        $possible_keys = ['indexed_links', 'unindexed_links', 'links', 'data', 'results'];
        foreach ($possible_keys as $key) {
            if (isset($result[$key])) {
                echo "<p><strong>Found key '$key':</strong> " . (is_array($result[$key]) ? count($result[$key]) . ' items' : gettype($result[$key])) . "</p>";
            }
        }
        
        // Show all keys in result
        echo "<p><strong>All keys in result:</strong> " . implode(', ', array_keys($result)) . "</p>";
    }
    
} catch (Exception $e) {
    echo "<p>❌ Error: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<p><a href='manual_task_sync.php'>Manual Task Sync</a> | <a href='tasks.php'>Tasks</a></p>";
?>
