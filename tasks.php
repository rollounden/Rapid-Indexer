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
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="/assets/css/style.css" rel="stylesheet">
    <style>
        /* Enhanced Button Styling */
        .btn {
            border-radius: 0.5rem;
            font-weight: 500;
            transition: all 0.2s ease;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.15);
        }
        
        .btn:active {
            transform: translateY(0);
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
        }
        
        .btn-success {
            background: linear-gradient(135deg, #56ab2f 0%, #a8e6cf 100%);
            border: none;
        }
        
        .btn-warning {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            border: none;
            color: white;
        }
        
        .btn-warning:hover {
            color: white;
        }
        
        /* Mobile Button Optimizations */
        @media (max-width: 768px) {
            .d-flex.gap-2 {
                flex-direction: column;
                gap: 0.5rem !important;
            }
            
            .d-flex.gap-2 .btn {
                width: 100%;
                justify-content: center;
            }
            
            .btn-lg {
                padding: 0.75rem 1.5rem;
                font-size: 1rem;
            }
        }
        
        /* Table Action Buttons */
        .table td .d-flex {
            min-width: 200px;
        }
        
        @media (max-width: 576px) {
            .table td .d-flex {
                min-width: auto;
            }
        }
        
        /* Status Badge Improvements */
        .badge {
            font-size: 0.75rem;
            padding: 0.5rem 0.75rem;
            border-radius: 0.375rem;
        }
        
        /* Progress Bar Improvements */
        .progress {
            height: 0.5rem;
            border-radius: 0.25rem;
        }
        
        .progress-bar {
            border-radius: 0.25rem;
        }
    </style>
</head>
<body>
    <?php include __DIR__ . '/includes/navbar.php'; ?>
    
    <div class="container mt-4">
        <div class="row">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h1 class="h3 mb-0">My Tasks</h1>
                    <a href="/dashboard" class="btn btn-primary btn-lg">
                        <i class="fas fa-plus me-2"></i>Create New Task
                    </a>
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
                            <i class="fas fa-tasks text-muted mb-3" style="font-size: 3rem;"></i>
                            <h5 class="text-muted mb-3">No tasks found</h5>
                            <p class="text-muted mb-4">Create your first indexing task to get started.</p>
                            <a href="/dashboard" class="btn btn-primary btn-lg">
                                <i class="fas fa-plus me-2"></i>Create Your First Task
                            </a>
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
                                                    <?php if (in_array($task['status'], ['pending', 'processing'])): ?>
                                                        <div class="text-muted small mt-1">
                                                            <?php if ($task['type'] === 'checker'): ?>
                                                                Typically completes within 1-2 minutes. Use Resync to refresh.
                                                            <?php else: ?>
                                                                Typically completes within 2-10 minutes. Use Resync to refresh.
                                                            <?php endif; ?>
                                                        </div>
                                                    <?php endif; ?>
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
                                                    <div class="d-flex gap-2 flex-wrap">
                                                        <a href="/task_results?id=<?php echo $task['id']; ?>" 
                                                           class="btn btn-primary btn-sm">
                                                            <i class="fas fa-eye me-1"></i>View Results
                                                        </a>
                                                        
                                                        <?php if ($task['status'] === 'completed'): ?>
                                                            <form method="POST" style="display: inline;">
                                                                <input type="hidden" name="action" value="export_csv">
                                                                <input type="hidden" name="task_id" value="<?php echo $task['id']; ?>">
                                                                <button type="submit" class="btn btn-success btn-sm">
                                                                    <i class="fas fa-download me-1"></i>Export CSV
                                                                </button>
                                                            </form>
                                                        <?php endif; ?>
                                                        
                                                        <?php if ($task['status'] === 'pending' && $task['type'] === 'indexer'): ?>
                                                            <form method="POST" style="display: inline;">
                                                                <input type="hidden" name="action" value="vip_queue">
                                                                <input type="hidden" name="task_id" value="<?php echo $task['id']; ?>">
                                                                <button type="submit" class="btn btn-warning btn-sm">
                                                                    <i class="fas fa-star me-1"></i>VIP Queue
                                                                </button>
                                                            </form>
                                                        <?php endif; ?>

                                                        <?php if (in_array($task['status'], ['pending', 'processing'])): ?>
                                                            <form method="POST" style="display: inline;">
                                                                <input type="hidden" name="action" value="sync_status">
                                                                <input type="hidden" name="task_id" value="<?php echo $task['id']; ?>">
                                                                <button type="submit" class="btn btn-outline-secondary btn-sm">
                                                                    <i class="fas fa-arrows-rotate me-1"></i>Resync
                                                                </button>
                                                            </form>
                                                        <?php endif; ?>
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
