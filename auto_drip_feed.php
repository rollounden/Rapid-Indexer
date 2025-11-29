<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/src/Db.php';
require_once __DIR__ . '/src/TaskService.php';

// Force include Db class if not already loaded
if (!class_exists('Db')) {
    require_once __DIR__ . '/src/Db.php';
}

// Set time limit to avoid timeouts
set_time_limit(600);

echo "Starting Drip Feed Worker...\n";

$pdo = Db::conn();

// Get tasks due for processing
$stmt = $pdo->prepare("
    SELECT id FROM tasks 
    WHERE is_drip_feed = 1 
    AND status IN ('pending', 'processing') 
    AND (next_run_at IS NULL OR next_run_at <= NOW())
    LIMIT 10
");
$stmt->execute();
$tasks = $stmt->fetchAll();

echo "Found " . count($tasks) . " tasks to process.\n";

foreach ($tasks as $task) {
    echo "Processing Task #{$task['id']}...\n";
    try {
        $result = TaskService::processDripFeedBatch((int)$task['id']);
        if (isset($result['count'])) {
             echo "  Submitted batch of {$result['count']} links.\n";
        } else {
             echo "  {$result['message']}\n";
        }
    } catch (Exception $e) {
        echo "  Error processing task: " . $e->getMessage() . "\n";
    }
}

echo "Done.\n";
echo "Done.\n";

