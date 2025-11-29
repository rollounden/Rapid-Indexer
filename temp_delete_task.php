<?php
// temp_delete_task.php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/src/Db.php';

if (!isset($_GET['key']) || $_GET['key'] !== 'secure_delete_39') {
    die('Unauthorized');
}

try {
    $pdo = Db::conn();
    $pdo->beginTransaction();
    
    // Delete related records first
    $pdo->prepare('DELETE FROM task_links WHERE task_id = ?')->execute([39]);
    $pdo->prepare('DELETE FROM task_batches WHERE task_id = ?')->execute([39]);
    
    // Delete task
    $pdo->prepare('DELETE FROM tasks WHERE id = ?')->execute([39]);
    
    $pdo->commit();
    echo "Task #39 deleted successfully.";
} catch (Exception $e) {
    $pdo->rollBack();
    echo "Error: " . $e->getMessage();
}
?>
