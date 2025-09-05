<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/src/Db.php';
require_once __DIR__ . '/src/TaskService.php';

echo "<h1>Test Fixed Task Sync</h1>";

try {
    $pdo = Db::conn();
    
    // Get the latest task
    $stmt = $pdo->prepare('SELECT * FROM tasks WHERE user_id = ? ORDER BY created_at DESC LIMIT 1');
    $stmt->execute([1]); // User ID 1
    $task = $stmt->fetch();
    
    if (!$task) {
        echo "<p>No tasks found.</p>";
        exit;
    }
    
    echo "<h2>Task Details</h2>";
    echo "<p><strong>ID:</strong> " . $task['id'] . "</p>";
    echo "<p><strong>Type:</strong> " . $task['type'] . "</p>";
    echo "<p><strong>Engine:</strong> " . $task['search_engine'] . "</p>";
    echo "<p><strong>Status:</strong> " . $task['status'] . "</p>";
    echo "<p><strong>SpeedyIndex Task ID:</strong> " . $task['speedyindex_task_id'] . "</p>";
    
    echo "<h2>Before Sync - Task Links</h2>";
    $stmt = $pdo->prepare('SELECT * FROM task_links WHERE task_id = ?');
    $stmt->execute([$task['id']]);
    $links = $stmt->fetchAll();
    
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>URL</th><th>Status</th><th>Error Code</th><th>Checked At</th></tr>";
    foreach ($links as $link) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($link['url']) . "</td>";
        echo "<td>" . $link['status'] . "</td>";
        echo "<td>" . ($link['error_code'] ?: 'N/A') . "</td>";
        echo "<td>" . ($link['checked_at'] ?: 'Not checked') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    echo "<h2>Syncing Task Status...</h2>";
    
    // Sync the task
    $result = TaskService::syncTaskStatus(1, $task['id']);
    
    echo "<p>✅ <strong>Sync completed!</strong></p>";
    echo "<p><strong>Updated:</strong> " . $result['updated'] . " links</p>";
    
    echo "<h2>After Sync - Task Links</h2>";
    $stmt = $pdo->prepare('SELECT * FROM task_links WHERE task_id = ?');
    $stmt->execute([$task['id']]);
    $links = $stmt->fetchAll();
    
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>URL</th><th>Status</th><th>Error Code</th><th>Checked At</th><th>Result Data</th></tr>";
    foreach ($links as $link) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($link['url']) . "</td>";
        echo "<td style='color: " . ($link['status'] === 'indexed' ? 'green' : ($link['status'] === 'unindexed' ? 'orange' : 'red')) . ";'>" . $link['status'] . "</td>";
        echo "<td>" . ($link['error_code'] ?: 'N/A') . "</td>";
        echo "<td>" . ($link['checked_at'] ?: 'Not checked') . "</td>";
        echo "<td>" . ($link['result_data'] ? 'Yes' : 'No') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Show updated task status
    $stmt = $pdo->prepare('SELECT status, completed_at FROM tasks WHERE id = ?');
    $stmt->execute([$task['id']]);
    $updated_task = $stmt->fetch();
    
    echo "<h2>Updated Task Status</h2>";
    echo "<p><strong>Status:</strong> " . $updated_task['status'] . "</p>";
    echo "<p><strong>Completed At:</strong> " . ($updated_task['completed_at'] ?: 'Not completed') . "</p>";
    
    // Show result data details
    if (!empty($links)) {
        echo "<h2>Result Data Details</h2>";
        foreach ($links as $link) {
            if ($link['result_data']) {
                $result_data = json_decode($link['result_data'], true);
                echo "<h3>URL: " . htmlspecialchars($link['url']) . "</h3>";
                echo "<pre style='background: #f5f5f5; padding: 10px; border: 1px solid #ddd;'>";
                echo htmlspecialchars(json_encode($result_data, JSON_PRETTY_PRINT));
                echo "</pre>";
            }
        }
    }
    
} catch (Exception $e) {
    echo "<p>❌ <strong>Error:</strong> " . $e->getMessage() . "</p>";
    echo "<p><strong>File:</strong> " . $e->getFile() . "</p>";
    echo "<p><strong>Line:</strong> " . $e->getLine() . "</p>";
}

echo "<hr>";
echo "<p><a href='tasks.php'>Back to Tasks</a></p>";
?>
