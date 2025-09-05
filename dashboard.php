<?php
require_once __DIR__ . '/config/config.php';
session_start();

// Redirect if not logged in
if (!isset($_SESSION['uid'])) {
    header('Location: /login.php');
    exit;
}

require_once __DIR__ . '/src/Db.php';
require_once __DIR__ . '/src/CreditsService.php';
require_once __DIR__ . '/src/TaskService.php';

$userId = $_SESSION['uid'];
$error = '';
$success = '';

// Handle task creation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'create_task') {
    try {
        $engine = $_POST['engine'] ?? 'google';
        $type = $_POST['type'] ?? 'indexer';
        $title = $_POST['title'] ?? null;
        $urlsRaw = $_POST['urls'] ?? '';
        $urls = array_values(array_filter(array_map('trim', preg_split('/\r?\n/', $urlsRaw))));
        $vip = isset($_POST['vip']) && $_POST['vip'] === '1';
        
        if (empty($urls)) {
            $error = 'Please enter at least one URL.';
        } else {
            $created = TaskService::createTask($userId, $engine, $type, $urls, $title, $vip);
            $success = 'Task created successfully!';
        }
    } catch (Exception $e) {
        $error = 'Error creating task: ' . $e->getMessage();
    }
}

// Get user data and statistics
$pdo = Db::conn();
$credits = CreditsService::getBalance($userId);

// Get recent tasks
$stmt = $pdo->prepare('SELECT * FROM tasks WHERE user_id = ? ORDER BY created_at DESC LIMIT 5');
$stmt->execute([$userId]);
$recentTasks = $stmt->fetchAll();

// Get task statistics
$stmt = $pdo->prepare('SELECT COUNT(*) as total, SUM(CASE WHEN status = "completed" THEN 1 ELSE 0 END) as completed FROM tasks WHERE user_id = ?');
$stmt->execute([$userId]);
$taskStats = $stmt->fetch();

// Get payment statistics
$stmt = $pdo->prepare('SELECT COUNT(*) as total_payments, SUM(amount) as total_spent FROM payments WHERE user_id = ? AND status = "paid"');
$stmt->execute([$userId]);
$paymentStats = $stmt->fetch();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Dashboard - RapidIndexer</title>
    <meta name="theme-color" content="#667eea">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="/assets/css/style.css" rel="stylesheet">
    <style>
        /* Mobile Optimizations */
        @media (max-width: 768px) {
            .btn-lg {
                padding: 0.75rem 1.5rem;
                font-size: 1rem;
            }
            .form-control-lg {
                padding: 0.75rem 1rem;
                font-size: 1rem;
            }
            .table-responsive {
                font-size: 0.9rem;
            }
            .card-body {
                padding: 1rem;
            }
            .btn-group-mobile {
                flex-direction: column;
                gap: 0.5rem;
            }
            .btn-group-mobile .btn {
                width: 100%;
            }
        }
        
        @media (max-width: 576px) {
            .container {
                padding-left: 1rem;
                padding-right: 1rem;
            }
            .btn-lg {
                padding: 0.6rem 1.2rem;
                font-size: 0.95rem;
            }
            .display-4 {
                font-size: 2rem !important;
            }
        }
        
        /* Touch-friendly buttons */
        .btn {
            min-height: 44px;
            min-width: 44px;
        }
        
        /* Prevent zoom on input focus */
        input, textarea, select {
            font-size: 16px;
        }
        
        /* Mobile Navigation Enhancements */
        @media (max-width: 991px) {
            .navbar-collapse {
                background-color: #fff;
                border-top: 1px solid #dee2e6;
                margin-top: 0.5rem;
                padding-top: 1rem;
            }
            .navbar-nav .nav-link {
                padding: 0.75rem 1rem;
                border-radius: 0.375rem;
                margin: 0.25rem 0;
            }
            .navbar-nav .nav-link:hover {
                background-color: #f8f9fa;
            }
            .navbar-nav .nav-link.active {
                background-color: #667eea;
                color: white !important;
            }
            .dropdown-menu {
                border: none;
                box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
            }
        }
        
        /* Touch-friendly navbar toggler */
        .navbar-toggler {
            padding: 0.5rem;
            border: none;
            background: transparent;
        }
        
        .navbar-toggler:focus {
            box-shadow: none;
        }
        
        /* Brand icon animation */
        .navbar-brand i {
            transition: transform 0.3s ease;
        }
        
        .navbar-brand:hover i {
            transform: rotate(15deg);
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg">
        <div class="container">
            <a class="navbar-brand" href="/dashboard.php">Rapid Indexer</a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="/tasks.php">Tasks</a>
                <a class="nav-link" href="/payments.php">Payments</a>
                <?php if (($_SESSION['role'] ?? '') === 'admin'): ?>
                <a class="nav-link" href="/admin.php">Admin</a>
                <?php endif; ?>
                <a class="nav-link" href="/logout.php">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container py-4">
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card stats-card">
                    <div class="card-body text-center">
                        <div class="stats-number"><?php echo number_format($credits); ?></div>
                        <div class="stats-label">Available Credits</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stats-card">
                    <div class="card-body text-center">
                        <div class="stats-number"><?php echo number_format($taskStats['total'] ?? 0); ?></div>
                        <div class="stats-label">Total Tasks</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stats-card">
                    <div class="card-body text-center">
                        <div class="stats-number"><?php echo number_format($taskStats['completed'] ?? 0); ?></div>
                        <div class="stats-label">Completed Tasks</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stats-card">
                    <div class="card-body text-center">
                        <div class="stats-number">$<?php echo number_format($paymentStats['total_spent'] ?? 0, 2); ?></div>
                        <div class="stats-label">Total Spent</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Create Task Form -->
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Create New Task</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <input type="hidden" name="action" value="create_task" />
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Search Engine</label>
                                        <select name="engine" class="form-select">
                                            <option value="google">Google</option>
                                            <option value="yandex">Yandex</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Task Type</label>
                                        <select name="type" class="form-select">
                                            <option value="indexer">Indexer</option>
                                            <option value="checker">Checker</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Task Title (Optional)</label>
                                <input type="text" class="form-control" name="title" placeholder="Enter task title">
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">URLs (One per line, max 10,000)</label>
                                <textarea class="form-control" rows="6" name="urls" placeholder="https://example.com/page1&#10;https://example.com/page2" required></textarea>
                            </div>
                            
                            <div class="mb-3" id="vipSection" style="display: none;">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="vip" value="1" id="vipCheck">
                                    <label class="form-check-label" for="vipCheck">
                                        VIP Queue (extra credits per URL) - Indexing only
                                    </label>
                                </div>
                            </div>
                            
                            <button type="submit" class="btn btn-primary">Create Task</button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Recent Tasks -->
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Recent Tasks</h5>
                        <a href="/tasks.php" class="btn btn-sm btn-outline-primary">View All</a>
                    </div>
                    <div class="card-body p-0">
                        <?php if (empty($recentTasks)): ?>
                            <div class="p-3 text-center text-muted">
                                <p class="mb-0">No tasks yet</p>
                            </div>
                        <?php else: ?>
                            <div class="list-group list-group-flush">
                                <?php foreach ($recentTasks as $task): ?>
                                    <div class="list-group-item">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div>
                                                <h6 class="mb-1"><?php echo htmlspecialchars($task['title'] ?: 'Untitled Task'); ?></h6>
                                                <small class="text-muted">
                                                    <?php echo htmlspecialchars(ucfirst($task['search_engine'])); ?> • 
                                                    <?php echo htmlspecialchars(ucfirst($task['type'])); ?>
                                                </small>
                                            </div>
                                            <span class="badge badge-<?php echo $task['status'] === 'completed' ? 'success' : ($task['status'] === 'processing' ? 'warning' : 'secondary'); ?>">
                                                <?php echo htmlspecialchars(ucfirst($task['status'])); ?>
                                            </span>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Show/hide VIP section based on task type
        document.addEventListener('DOMContentLoaded', function() {
            const typeSelect = document.querySelector('select[name="type"]');
            const vipSection = document.getElementById('vipSection');
            
            function toggleVipSection() {
                if (typeSelect.value === 'indexer') {
                    vipSection.style.display = 'block';
                } else {
                    vipSection.style.display = 'none';
                    document.getElementById('vipCheck').checked = false;
                }
            }
            
            typeSelect.addEventListener('change', toggleVipSection);
            toggleVipSection(); // Initial call
        });
    </script>
</body>
</html>
