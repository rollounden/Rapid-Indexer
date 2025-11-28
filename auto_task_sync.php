<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/src/Db.php';
require_once __DIR__ . '/src/TaskService.php';

// Auto-sync script - can be run via cron job every 2-3 minutes
// This will automatically sync all pending/processing tasks

try {
    // Ensure log directory exists
    $logDir = __DIR__ . '/storage/logs';
    if (!is_dir($logDir)) { @mkdir($logDir, 0775, true); }
    $logFile = $logDir . '/cron_task_sync.log';
    $pdo = Db::conn();
    
    // Get all tasks that need syncing
    // For checker tasks: sync if processing or older than 15 seconds
    // For indexer tasks: sync if processing or older than 2 minutes
    // For traffic tasks: sync if processing or older than 5 minutes (handled by TrafficService)
    $stmt = $pdo->prepare('
        SELECT * FROM tasks 
        WHERE status IN (?, ?) 
        AND (
            status = ? OR 
            (type = ? AND created_at < DATE_SUB(NOW(), INTERVAL 15 SECOND)) OR
            (type = ? AND created_at < DATE_SUB(NOW(), INTERVAL 2 MINUTE)) OR
            (type = ? AND created_at < DATE_SUB(NOW(), INTERVAL 5 MINUTE)) OR
            (type = ? AND created_at < DATE_SUB(NOW(), INTERVAL 5 MINUTE))
        )
        ORDER BY 
            CASE WHEN type = ? THEN 0 ELSE 1 END,
            created_at ASC
    ');
    $stmt->execute([
        'processing', 'pending', // status IN
        'processing',            // status =
        'checker',               // type =
        'indexer',               // type =
        'traffic',               // type =
        'traffic_campaign',      // type =
        'checker'                // ORDER BY
    ]);
    $tasks = $stmt->fetchAll();
    
    if (empty($tasks)) {
        // No tasks to sync
        $line = '[' . gmdate('Y-m-d H:i:s') . " UTC] auto_task_sync: 0 tasks\n";
        @file_put_contents($logFile, $line, FILE_APPEND);
        echo "No tasks to sync.\n";
        exit;
    }
    
    $synced_count = 0;
    $error_count = 0;
    
    foreach ($tasks as $task) {
        try {
            echo "Syncing task #{$task['id']} ({$task['type']})...\n";
            
            if ($task['type'] === 'traffic' || $task['type'] === 'traffic_campaign') {
                require_once __DIR__ . '/src/TrafficService.php';
                TrafficService::syncStatus($task['id']);
                // Traffic sync doesn't return a detailed result array like TaskService yet,
                // so we assume success if no exception
                 echo "✅ Task #{$task['id']} synced (Traffic)\n";
                 $synced_count++;
            } else {
                // Existing logic for indexer/checker
                if (empty($task['speedyindex_task_id'])) {
                     echo "⚠️ Task #{$task['id']} skipped (no provider ID)\n";
                     continue;
                }
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
