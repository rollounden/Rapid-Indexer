<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/src/Db.php';

// Force include Db class if not already loaded
if (!class_exists('Db')) {
    require_once __DIR__ . '/src/Db.php';
}

$pdo = Db::conn();

// Debug Time
echo "Current PHP Time: " . date('Y-m-d H:i:s') . "\n";
echo "Current DB Time (NOW()): " . $pdo->query("SELECT NOW()")->fetchColumn() . "\n";

// Debug Task
$stmt = $pdo->prepare("SELECT id, status, next_run_at, is_drip_feed, drip_percentage FROM tasks WHERE id = 39");
$stmt->execute();
$task = $stmt->fetch(PDO::FETCH_ASSOC);

if ($task) {
    echo "Task Found:\n";
    print_r($task);
    
    if ($task['next_run_at']) {
        $nextRun = strtotime($task['next_run_at']);
        $now = time();
        $diff = $nextRun - $now;
        echo "Time until next run: " . $diff . " seconds\n";
        if ($diff <= 0) {
            echo "Task IS READY to run.\n";
        } else {
            echo "Task is NOT ready to run yet.\n";
        }
    }
} else {
    echo "Task #39 not found.\n";
}
?>
