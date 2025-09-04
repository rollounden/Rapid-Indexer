<?php
require_once __DIR__ . '/config/config.php';
session_start();

// Redirect if not logged in
if (!isset($_SESSION['uid'])) {
    header('Location: /login.php');
    exit;
}

require_once __DIR__ . '/src/Db.php';
require_once __DIR__ . '/src/TaskService.php';

$userId = $_SESSION['uid'];
$pdo = Db::conn();
$msg = '';
$msgType = '';

// Handle task synchronization
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'sync') {
    try {
        TaskService::syncTaskStatus($userId, intval($_POST['task_id']));
        $msg = 'Task synchronized successfully!';
        $msgType = 'success';
    } catch (Exception $e) {
        $msg = 'Error: ' . $e->getMessage();
        $msgType = 'danger';
    }
}

// Handle CSV export
if (isset($_GET['action']) && $_GET['action'] === 'export' && isset($_GET['task_id'])) {
    try {
        $csv = TaskService::exportTaskCsv($userId, intval($_GET['task_id']));
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="task_' . intval($_GET['task_id']) . '.csv"');
        echo $csv;
        exit;
    } catch (Exception $e) {
        $msg = 'Export error: ' . $e->getMessage();
        $msgType = 'danger';
    }
}

// Get tasks with pagination
$page = max(1, intval($_GET['page'] ?? 1));
$limit = 20;
$offset = ($page - 1) * $limit;

$stmt = $pdo->prepare('SELECT * FROM tasks WHERE user_id = ? ORDER BY created_at DESC LIMIT ? OFFSET ?');
$stmt->execute([$userId, $limit, $offset]);
$tasks = $stmt->fetchAll();

// Get total count for pagination
$stmt = $pdo->prepare('SELECT COUNT(*) FROM tasks WHERE user_id = ?');
$stmt->execute([$userId]);
$totalTasks = $stmt->fetchColumn();
$totalPages = ceil($totalTasks / $limit);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tasks - RapidIndexer</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="/assets/css/style.css" rel="stylesheet">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg">
        <div class="container">
            <a class="navbar-brand" href="/dashboard.php">RapidIndexer</a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link active" href="/tasks.php">Tasks</a>
                <a class="nav-link" href="/payments.php">Payments</a>
                <?php if (($_SESSION['role'] ?? '') === 'admin'): ?>
                <a class="nav-link" href="/admin.php">Admin</a>
                <?php endif; ?>
                <a class="nav-link" href="/logout.php">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 mb-0">My Tasks</h1>
            <a href="/dashboard.php" class="btn btn-outline-primary">Create New Task</a>
        </div>

        <?php if ($msg): ?>
            <div class="alert alert-<?php echo $msgType; ?>"><?php echo htmlspecialchars($msg); ?></div>
        <?php endif; ?>

        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Task History</h5>
            </div>
            <div class="card-body p-0">
                <?php if (empty($tasks)): ?>
                    <div class="p-4 text-center text-muted">
                        <h5>No tasks yet</h5>
                        <p>Create your first task to get started!</p>
                        <a href="/dashboard.php" class="btn btn-primary">Create Task</a>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Title</th>
                                    <th>Engine</th>
                                    <th>Type</th>
                                    <th>Status</th>
                                    <th>VIP</th>
                                    <th>Created</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($tasks as $task): ?>
                                    <tr>
                                        <td>
                                            <span class="badge bg-secondary">#<?php echo htmlspecialchars(strval($task['id'])); ?></span>
                                        </td>
                                        <td>
                                            <strong><?php echo htmlspecialchars($task['title'] ?: 'Untitled Task'); ?></strong>
                                        </td>
                                        <td>
                                            <span class="badge bg-info"><?php echo htmlspecialchars(ucfirst($task['search_engine'])); ?></span>
                                        </td>
                                        <td>
                                            <span class="badge bg-primary"><?php echo htmlspecialchars(ucfirst($task['type'])); ?></span>
                                        </td>
                                        <td>
                                            <?php
                                            $statusClass = 'bg-secondary';
                                            if ($task['status'] === 'completed') $statusClass = 'bg-success';
                                            elseif ($task['status'] === 'processing') $statusClass = 'bg-warning';
                                            elseif ($task['status'] === 'failed') $statusClass = 'bg-danger';
                                            ?>
                                            <span class="badge <?php echo $statusClass; ?>">
                                                <?php echo htmlspecialchars(ucfirst($task['status'])); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if ($task['vip']): ?>
                                                <span class="badge bg-warning">VIP</span>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <small class="text-muted">
                                                <?php echo date('M j, Y g:i A', strtotime($task['created_at'])); ?>
                                            </small>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <form method="post" class="d-inline">
                                                    <input type="hidden" name="action" value="sync" />
                                                    <input type="hidden" name="task_id" value="<?php echo htmlspecialchars(strval($task['id'])); ?>" />
                                                    <button type="submit" class="btn btn-outline-primary" title="Sync Status">
                                                        <i class="bi bi-arrow-clockwise"></i> Sync
                                                    </button>
                                                </form>
                                                <a href="/tasks.php?action=export&task_id=<?php echo htmlspecialchars(strval($task['id'])); ?>" 
                                                   class="btn btn-outline-secondary" title="Export CSV">
                                                    <i class="bi bi-download"></i> Export
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
            <nav class="mt-4">
                <ul class="pagination justify-content-center">
                    <?php if ($page > 1): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?php echo $page - 1; ?>">Previous</a>
                        </li>
                    <?php endif; ?>
                    
                    <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                        <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                        </li>
                    <?php endfor; ?>
                    
                    <?php if ($page < $totalPages): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?php echo $page + 1; ?>">Next</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </nav>
        <?php endif; ?>
    </div>
</body>
</html>
