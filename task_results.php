<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/config/config.php';
session_start();

// Check if user is logged in
if (!isset($_SESSION['uid'])) {
    header('Location: /login');
    exit;
}

require_once __DIR__ . '/src/Db.php';
require_once __DIR__ . '/src/TaskService.php';
$pdo = Db::conn();

// Get task details
$task_id = intval($_GET['id'] ?? 0);

// If no ID provided, redirect to tasks list instead of showing error
if (!$task_id) {
    header('Location: /tasks');
    exit;
}

// Fetch task
$stmt = $pdo->prepare('SELECT * FROM tasks WHERE id = ? AND user_id = ?');
$stmt->execute([$task_id, $_SESSION['uid']]);
$task = $stmt->fetch();

if (!$task) {
    // User might have clicked a valid link but lacks permission or task deleted
    // Redirecting to tasks with an error message is better UX than dying
    $_SESSION['flash_error'] = 'Task not found or access denied.';
    header('Location: /tasks');
    exit;
}

// Get task links
$stmt = $pdo->prepare('SELECT * FROM task_links WHERE task_id = ? ORDER BY id');
$stmt->execute([$task_id]);
$links = $stmt->fetchAll();

// Calculate stats
$total = count($links);
$indexed = 0;
$unindexed = 0;
$pending = 0;
$error = 0;

foreach ($links as $link) {
    switch ($link['status']) {
        case 'indexed': $indexed++; break;
        case 'unindexed': $unindexed++; break;
        case 'pending': $pending++; break;
        case 'error': $error++; break;
    }
}

$progress = $total > 0 ? round((($indexed + $unindexed) / $total) * 100) : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Task Details - Rapid Indexer</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="/assets/css/style.css" rel="stylesheet">
</head>
<body>
    <?php include __DIR__ . '/includes/navbar.php'; ?>
    
    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-1">
                        <li class="breadcrumb-item"><a href="/tasks">Tasks</a></li>
                        <li class="breadcrumb-item active" aria-current="page">#<?php echo $task_id; ?></li>
                    </ol>
                </nav>
                <h1 class="h3 mb-0"><?php echo htmlspecialchars($task['title'] ?: 'Untitled Task'); ?></h1>
            </div>
            <div>
                <span class="badge bg-<?php echo $task['status'] === 'completed' ? 'success' : ($task['status'] === 'processing' ? 'info' : 'warning'); ?> fs-6">
                    <?php echo ucfirst($task['status']); ?>
                </span>
            </div>
        </div>
        
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body text-center">
                        <h3 class="text-primary"><?php echo $total; ?></h3>
                        <div class="text-muted small">Total Links</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body text-center">
                        <h3 class="text-success"><?php echo $indexed; ?></h3>
                        <div class="text-muted small">Indexed</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body text-center">
                        <h3 class="text-warning"><?php echo $unindexed; ?></h3>
                        <div class="text-muted small">Unindexed</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body text-center">
                        <h3 class="text-secondary"><?php echo $pending; ?></h3>
                        <div class="text-muted small">Pending</div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Links</h5>
                <?php if ($task['status'] === 'completed'): ?>
                    <div class="btn-group">
                        <?php if ($task['type'] === 'indexer'): ?>
                            <form method="POST" action="/tasks">
                                <input type="hidden" name="action" value="check_indexing">
                                <input type="hidden" name="task_id" value="<?php echo $task_id; ?>">
                                <button type="submit" class="btn btn-sm btn-info text-white">
                                    <i class="fas fa-search me-1"></i>Check Indexing
                                </button>
                            </form>
                        <?php endif; ?>
                        <form method="POST" action="/tasks" class="ms-2">
                            <input type="hidden" name="action" value="export_csv">
                            <input type="hidden" name="task_id" value="<?php echo $task_id; ?>">
                            <button type="submit" class="btn btn-sm btn-outline-success">Export CSV</button>
                        </form>
                    </div>
                <?php endif; ?>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped mb-0">
                        <thead>
                            <tr>
                                <th>URL</th>
                                <th>Status</th>
                                <th>Checked At</th>
                                <th>Result</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($links as $link): ?>
                                <tr>
                                    <td class="text-truncate" style="max-width: 300px;" title="<?php echo htmlspecialchars($link['url']); ?>">
                                        <a href="<?php echo htmlspecialchars($link['url']); ?>" target="_blank" class="text-decoration-none">
                                            <?php echo htmlspecialchars($link['url']); ?>
                                        </a>
                                    </td>
                                    <td>
                                        <?php
                                        $badge_class = 'secondary';
                                        switch ($link['status']) {
                                            case 'indexed': $badge_class = 'success'; break;
                                            case 'unindexed': $badge_class = 'warning'; break;
                                            case 'error': $badge_class = 'danger'; break;
                                        }
                                        ?>
                                        <span class="badge bg-<?php echo $badge_class; ?>">
                                            <?php echo ucfirst($link['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php echo $link['checked_at'] ? date('M j, H:i', strtotime($link['checked_at'])) : '-'; ?>
                                    </td>
                                    <td>
                                        <?php if ($link['result_data']): ?>
                                            <button class="btn btn-xs btn-outline-info" onclick='showDetails(<?php echo json_encode($link["result_data"]); ?>)'>
                                                Details
                                            </button>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Details Modal -->
    <div class="modal fade" id="detailsModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Result Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <pre id="detailsContent" class="bg-light p-3 rounded"></pre>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function showDetails(data) {
            try {
                const content = typeof data === 'string' ? JSON.parse(data) : data;
                document.getElementById('detailsContent').textContent = JSON.stringify(content, null, 2);
                new bootstrap.Modal(document.getElementById('detailsModal')).show();
            } catch (e) {
                console.error('Error parsing details', e);
            }
        }
    </script>
    <?php include __DIR__ . '/includes/footer.php'; ?>
</body>
</html>
