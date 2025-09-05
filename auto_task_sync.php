<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/src/Db.php';
require_once __DIR__ . '/src/TaskService.php';

// Auto-sync script - can be run via cron job every 2-3 minutes
// This will automatically sync all pending/processing tasks

try {
    $pdo = Db::conn();
    
    // Get all tasks that need syncing
    // For checker tasks: sync if processing or older than 30 seconds
    // For indexer tasks: sync if processing or older than 2 minutes
    $stmt = $pdo->prepare('
        SELECT * FROM tasks 
        WHERE status IN (?, ?) 
        AND speedyindex_task_id IS NOT NULL 
        AND (
            status = ? OR 
            (type = ? AND created_at < DATE_SUB(NOW(), INTERVAL 30 SECOND)) OR
            (type = ? AND created_at < DATE_SUB(NOW(), INTERVAL 2 MINUTE))
        )
        ORDER BY 
            CASE WHEN type = ? THEN 0 ELSE 1 END,
            created_at ASC
    ');
    $stmt->execute(['processing', 'pending', 'processing', 'checker', 'indexer', 'checker']);
    $tasks = $stmt->fetchAll();
    
    if (empty($tasks)) {
        // No tasks to sync
        exit;
    }
    
    $synced_count = 0;
    $error_count = 0;
    
    foreach ($tasks as $task) {
        try {
            echo "Syncing task #{$task['id']} ({$task['type']})...\n";
            
            $result = TaskService::syncTaskStatus($task['user_id'], $task['id']);
            
            if ($result['updated'] > 0) {
                echo "✅ Task #{$task['id']} synced - {$result['updated']} links updated\n";
                $synced_count++;
            } else {
                echo "⏳ Task #{$task['id']} still processing\n";
                
                // For checker tasks that are still pending, add a small delay before next check
                if ($task['type'] === 'checker' && $task['status'] === 'pending') {
                    echo "🔄 Checker task still pending, will retry in next cycle\n";
                }
            }
            
        } catch (Exception $e) {
            echo "❌ Error syncing task #{$task['id']}: " . $e->getMessage() . "\n";
            $error_count++;
        }
    }
    
    echo "\nSync Summary:\n";
    echo "✅ Synced: $synced_count tasks\n";
    echo "❌ Errors: $error_count tasks\n";
    echo "📊 Total processed: " . count($tasks) . " tasks\n";
    
} catch (Exception $e) {
    echo "❌ Auto-sync error: " . $e->getMessage() . "\n";
}
?>
