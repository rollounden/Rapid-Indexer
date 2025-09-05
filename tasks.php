<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/config/config.php';
session_start();

// Check if user is logged in
if (!isset($_SESSION['uid'])) {
    header('Location: /login.php');
    exit;
}

require_once __DIR__ . '/src/Db.php';
require_once __DIR__ . '/src/TaskService.php';
$pdo = Db::conn();

$error = '';
$success = '';

// Handle task actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'sync_status':
                $task_id = $_POST['task_id'];
                try {
                    TaskService::syncTaskStatus($_SESSION['uid'], $task_id);
                    $success = 'Task status updated successfully.';
                } catch (Exception $e) {
                    $error = 'Failed to sync task status: ' . $e->getMessage();
                }
                break;
                
            case 'export_csv':
                $task_id = $_POST['task_id'];
                try {
                    $csv_data = TaskService::exportTaskCsv($_SESSION['uid'], $task_id);
                    
                    header('Content-Type: text/csv');
                    header('Content-Disposition: attachment; filename="task_' . $task_id . '_results.csv"');
                    echo $csv_data;
                    exit;
                } catch (Exception $e) {
                    $error = 'Failed to export results: ' . $e->getMessage();
                }
                break;
                
            case 'vip_queue':
                $task_id = $_POST['task_id'];
                try {
                    // Get task details
                    $stmt = $pdo->prepare('SELECT * FROM tasks WHERE id = ? AND user_id = ? AND type = ?');
                    $stmt->execute([$task_id, $_SESSION['uid'], 'indexer']);
                    $task = $stmt->fetch();
                    
                    if (!$task) {
                        throw new Exception('Task not found or not an indexer task');
                    }
                    
                    // Check if task has links
                    $stmt = $pdo->prepare('SELECT COUNT(*) FROM task_links WHERE task_id = ?');
                    $stmt->execute([$task_id]);
                    $link_count = $stmt->fetchColumn();
                    
                    if ($link_count > 100) {
                        throw new Exception('VIP queue is only available for tasks with ≤ 100 links');
                    }
                    
                    // Add VIP queue request to SpeedyIndex
                    require_once __DIR__ . '/src/SpeedyIndexClient.php';
                    $client = new SpeedyIndexClient(SPEEDYINDEX_BASE_URL, SPEEDYINDEX_API_KEY, $_SESSION['uid']);
                    $result = $client->request('POST', '/v2/task/google/indexer/vip', ['task_id' => $task['speedyindex_task_id']]);
                    
                    if ($result['httpCode'] === 200) {
                        $success = 'VIP queue request submitted successfully.';
                    } else {
                        throw new Exception('Failed to submit VIP queue request');
                    }
                } catch (Exception $e) {
                    $error = 'Failed to submit VIP queue: ' . $e->getMessage();
                }
                break;
        }
    }
}

// Get user's tasks with pagination
$page = max(1, intval($_GET['page'] ?? 1));
$per_page = 10;
$offset = ($page - 1) * $per_page;

$stmt = $pdo->prepare('
    SELECT t.*, 
           COUNT(tl.id) as total_links,
           SUM(CASE WHEN tl.status = "indexed" THEN 1 ELSE 0 END) as indexed_links,
           SUM(CASE WHEN tl.status = "unindexed" THEN 1 ELSE 0 END) as unindexed_links,
           SUM(CASE WHEN tl.status = "pending" THEN 1 ELSE 0 END) as pending_links,
           SUM(CASE WHEN tl.status = "error" THEN 1 ELSE 0 END) as error_links
    FROM tasks t 
    LEFT JOIN task_links tl ON t.id = tl.task_id 
    WHERE t.user_id = ? 
    GROUP BY t.id 
    ORDER BY t.created_at DESC 
    LIMIT ' . (int)$per_page . ' OFFSET ' . (int)$offset
);
$stmt->execute([$_SESSION['uid']]);
$tasks = $stmt->fetchAll();

// Get total count for pagination
$stmt = $pdo->prepare('SELECT COUNT(*) FROM tasks WHERE user_id = ?');
$stmt->execute([$_SESSION['uid']]);
$total_tasks = $stmt->fetchColumn();
$total_pages = ceil($total_tasks / $per_page);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tasks - Rapid Indexer</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="/assets/css/style.css" rel="stylesheet">
</head>
<body>
    <?php include __DIR__ . '/includes/navbar.php'; ?>
    
    <div class="container mt-4">
        <div class="row">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h1 class="h3 mb-0">My Tasks</h1>
                    <a href="/dashboard.php" class="btn btn-primary">Create New Task</a>
                </div>
                
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>
                
                <?php if ($success): ?>
                    <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
                <?php endif; ?>
                
                <?php if (empty($tasks)): ?>
                    <div class="card">
                        <div class="card-body text-center py-5">
                            <h5 class="text-muted">No tasks found</h5>
                            <p class="text-muted">Create your first indexing task to get started.</p>
                            <a href="/dashboard.php" class="btn btn-primary">Create Task</a>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="card">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Task ID</th>
                                            <th>Type</th>
                                            <th>Status</th>
                                            <th>Progress</th>
                                            <th>Created</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($tasks as $task): ?>
                                            <tr>
                                                <td>
                                                    <strong>#<?php echo htmlspecialchars($task['id']); ?></strong>
                                                                                                         <?php if ($task['vip']): ?>
                                                         <span class="badge bg-warning">VIP</span>
                                                     <?php endif; ?>
                                                </td>
                                                <td><?php echo htmlspecialchars(ucfirst($task['type'])); ?></td>
                                                <td>
                                                    <?php
                                                    $status_class = 'secondary';
                                                    switch ($task['status']) {
                                                        case 'pending': $status_class = 'warning'; break;
                                                        case 'processing': $status_class = 'info'; break;
                                                        case 'completed': $status_class = 'success'; break;
                                                        case 'failed': $status_class = 'danger'; break;
                                                    }
                                                    ?>
                                                    <span class="badge bg-<?php echo $status_class; ?>">
                                                        <?php echo htmlspecialchars(ucfirst($task['status'])); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <?php if ($task['total_links'] > 0): ?>
                                                        <div class="progress" style="height: 20px;">
                                                            <?php 
                                                            $completed = $task['indexed_links'] + $task['unindexed_links'];
                                                            $percentage = ($completed / $task['total_links']) * 100;
                                                            ?>
                                                            <div class="progress-bar" style="width: <?php echo $percentage; ?>%">
                                                                <?php echo $completed; ?>/<?php echo $task['total_links']; ?>
                                                            </div>
                                                        </div>
                                                    <?php else: ?>
                                                        <span class="text-muted">No links</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?php echo date('M j, Y g:i A', strtotime($task['created_at'])); ?></td>
                                                <td>
                                                    <div class="btn-group btn-group-sm">
                                                        <form method="POST" style="display: inline;">
                                                            <input type="hidden" name="action" value="sync_status">
                                                            <input type="hidden" name="task_id" value="<?php echo $task['id']; ?>">
                                                            <button type="submit" class="btn btn-outline-primary" 
                                                                    <?php echo $task['status'] === 'completed' ? 'disabled' : ''; ?>
                                                                    title="<?php echo $task['type'] === 'checker' ? 'Auto-syncs every 15 seconds' : 'Auto-syncs every 2 minutes'; ?>">
                                                                <i class="fas fa-sync"></i> Sync
                                                                <?php if ($task['type'] === 'checker'): ?>
                                                                    <small class="text-muted">(15s)</small>
                                                                <?php else: ?>
                                                                    <small class="text-muted">(2m)</small>
                                                                <?php endif; ?>
                                                            </button>
                                                        </form>
                                                        
                                                        <?php if ($task['status'] === 'completed'): ?>
                                                            <form method="POST" style="display: inline;">
                                                                <input type="hidden" name="action" value="export_csv">
                                                                <input type="hidden" name="task_id" value="<?php echo $task['id']; ?>">
                                                                <button type="submit" class="btn btn-outline-success">
                                                                    Export
                                                                </button>
                                                            </form>
                                                        <?php endif; ?>
                                                        
                                                        <a href="/task_results.php?id=<?php echo $task['id']; ?>" class="btn btn-outline-primary">
                                                            <i class="fas fa-list"></i> View Results
                                                        </a>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            
                            <?php if ($total_pages > 1): ?>
                                <nav aria-label="Task pagination">
                                    <ul class="pagination justify-content-center">
                                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                            <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                                                <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                                            </li>
                                        <?php endfor; ?>
                                    </ul>
                                </nav>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
