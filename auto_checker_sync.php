<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/src/Db.php';
require_once __DIR__ . '/src/TaskService.php';

// Checker-specific sync script - runs every 30 seconds
// This focuses on checker tasks which complete faster

try {
    $pdo = Db::conn();
    
    // Get checker tasks that need syncing (processing or older than 30 seconds)
    $stmt = $pdo->prepare('
        SELECT * FROM tasks 
        WHERE type = ? 
        AND status IN (?, ?) 
        AND speedyindex_task_id IS NOT NULL 
        AND (status = ? OR created_at < DATE_SUB(NOW(), INTERVAL 30 SECOND))
        ORDER BY created_at ASC
    ');
    $stmt->execute(['checker', 'processing', 'pending', 'processing']);
    $checker_tasks = $stmt->fetchAll();
    
    if (empty($checker_tasks)) {
        // No checker tasks to sync
        exit;
    }
    
    $synced_count = 0;
    $error_count = 0;
    
    foreach ($checker_tasks as $task) {
        try {
            echo "Syncing checker task #{$task['id']}...\n";
            
            $result = TaskService::syncTaskStatus($task['user_id'], $task['id']);
            
            if ($result['updated'] > 0) {
                echo "✅ Checker task #{$task['id']} synced - {$result['updated']} links updated\n";
                $synced_count++;
            } else {
                echo "⏳ Checker task #{$task['id']} still processing\n";
            }
            
        } catch (Exception $e) {
            echo "❌ Error syncing checker task #{$task['id']}: " . $e->getMessage() . "\n";
            $error_count++;
        }
    }
    
    echo "\nChecker Sync Summary:\n";
    echo "✅ Synced: $synced_count checker tasks\n";
    echo "❌ Errors: $error_count checker tasks\n";
    echo "📊 Total processed: " . count($checker_tasks) . " checker tasks\n";
    
} catch (Exception $e) {
    echo "❌ Checker sync error: " . $e->getMessage() . "\n";
}
?>
