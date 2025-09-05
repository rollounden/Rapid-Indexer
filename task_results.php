<?php
require_once __DIR__ . '/config/config.php';
session_start();

// Check if user is logged in
if (!isset($_SESSION['uid'])) {
    header('Location: /login.php');
    exit;
}

$task_id = intval($_GET['id'] ?? 0);
if (!$task_id) {
    header('Location: /tasks.php');
    exit;
}

require_once __DIR__ . '/src/Db.php';
$pdo = Db::conn();

try {
    // Get task details
    $stmt = $pdo->prepare('SELECT * FROM tasks WHERE id = ? AND user_id = ?');
    $stmt->execute([$task_id, $_SESSION['uid']]);
    $task = $stmt->fetch();
    
    if (!$task) {
        header('Location: /tasks.php');
        exit;
    }
    
    // Get task links
    $stmt = $pdo->prepare('SELECT * FROM task_links WHERE task_id = ? ORDER BY id');
    $stmt->execute([$task_id]);
    $links = $stmt->fetchAll();
    
} catch (Exception $e) {
    header('Location: /tasks.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Task Results - RapidIndexer</title>
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
                    <h1 class="h3 mb-0">Task Results - #<?php echo $task['id']; ?></h1>
                    <a href="/tasks.php" class="btn btn-outline-secondary">Back to Tasks</a>
                </div>
                
                <!-- Task Summary -->
                <div class="card mb-4">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6>Task Information</h6>
                                <table class="table table-sm">
                                    <tr><td><strong>ID:</strong></td><td><?php echo $task['id']; ?></td></tr>
                                    <tr><td><strong>Type:</strong></td><td><?php echo ucfirst($task['type']); ?></td></tr>
                                    <tr><td><strong>Engine:</strong></td><td><?php echo ucfirst($task['search_engine']); ?></td></tr>
                                    <tr><td><strong>Status:</strong></td><td><span class="badge bg-success"><?php echo ucfirst($task['status']); ?></span></td></tr>
                                    <tr><td><strong>VIP:</strong></td><td><?php echo $task['vip'] ? '<span class="badge bg-warning">Yes</span>' : 'No'; ?></td></tr>
                                    <tr><td><strong>Created:</strong></td><td><?php echo date('M j, Y g:i A', strtotime($task['created_at'])); ?></td></tr>
                                    <?php if ($task['completed_at']): ?>
                                        <tr><td><strong>Completed:</strong></td><td><?php echo date('M j, Y g:i A', strtotime($task['completed_at'])); ?></td></tr>
                                    <?php endif; ?>
                                    <?php if ($task['title']): ?>
                                        <tr><td><strong>Title:</strong></td><td><?php echo htmlspecialchars($task['title']); ?></td></tr>
                                    <?php endif; ?>
                                </table>
                            </div>
                            
                            <div class="col-md-6">
                                <h6>Statistics</h6>
                                <table class="table table-sm">
                                    <tr><td><strong>Total Links:</strong></td><td><?php echo count($links); ?></td></tr>
                                    
                                    <?php
                                    $indexed = array_filter($links, fn($l) => $l['status'] === 'indexed');
                                    $unindexed = array_filter($links, fn($l) => $l['status'] === 'unindexed');
                                    $pending = array_filter($links, fn($l) => $l['status'] === 'pending');
                                    $error = array_filter($links, fn($l) => $l['status'] === 'error');
                                    ?>
                                    
                                    <tr><td><strong>Indexed:</strong></td><td><span class="text-success"><?php echo count($indexed); ?></span></td></tr>
                                    <tr><td><strong>Unindexed:</strong></td><td><span class="text-warning"><?php echo count($unindexed); ?></span></td></tr>
                                    <tr><td><strong>Pending:</strong></td><td><span class="text-info"><?php echo count($pending); ?></span></td></tr>
                                    <?php if (count($error) > 0): ?>
                                        <tr><td><strong>Errors:</strong></td><td><span class="text-danger"><?php echo count($error); ?></span></td></tr>
                                    <?php endif; ?>
                                    
                                    <?php
                                    $progress = count($links) > 0 ? round(((count($indexed) + count($unindexed)) / count($links)) * 100) : 0;
                                    ?>
                                    <tr><td><strong>Progress:</strong></td><td><?php echo $progress; ?>%</td></tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Link Results -->
                <?php if (!empty($links)): ?>
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Link Results</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>URL</th>
                                            <th>Status</th>
                                            <th>Error Code</th>
                                            <th>Checked At</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($links as $link): ?>
                                            <?php
                                            $status_class = '';
                                            $status_text = $link['status'];
                                            
                                            switch ($link['status']) {
                                                case 'indexed':
                                                    $status_class = 'success';
                                                    break;
                                                case 'unindexed':
                                                    $status_class = 'warning';
                                                    break;
                                                case 'pending':
                                                    $status_class = 'info';
                                                    break;
                                                case 'error':
                                                    $status_class = 'danger';
                                                    break;
                                            }
                                            ?>
                                            <tr>
                                                <td style="max-width: 400px; word-break: break-all;">
                                                    <a href="<?php echo htmlspecialchars($link['url']); ?>" target="_blank" class="text-decoration-none">
                                                        <?php echo htmlspecialchars($link['url']); ?>
                                                    </a>
                                                </td>
                                                <td>
                                                    <span class="badge bg-<?php echo $status_class; ?>">
                                                        <?php echo ucfirst($status_text); ?>
                                                    </span>
                                                </td>
                                                <td><?php echo $link['error_code'] ?: 'N/A'; ?></td>
                                                <td><?php echo $link['checked_at'] ? date('M j, g:i A', strtotime($link['checked_at'])) : 'Not checked'; ?></td>
                                                <td>
                                                    <?php if ($link['result_data']): ?>
                                                        <button class="btn btn-sm btn-outline-info" onclick="showResultData('<?php echo htmlspecialchars(json_encode(json_decode($link['result_data'], true))); ?>')">
                                                            View Data
                                                        </button>
                                                    <?php else: ?>
                                                        <span class="text-muted">No data</span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Result Data Modal -->
    <div class="modal fade" id="resultDataModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Result Data</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="resultDataContent">
                    <!-- Content will be loaded here -->
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function showResultData(data) {
            const modal = new bootstrap.Modal(document.getElementById('resultDataModal'));
            document.getElementById('resultDataContent').innerHTML = '<pre>' + JSON.stringify(JSON.parse(data), null, 2) + '</pre>';
            modal.show();
        }
    </script>
</body>
</html>
