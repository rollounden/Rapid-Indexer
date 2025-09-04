<?php
require_once __DIR__ . '/config/config.php';
session_start();

// Check if user is logged in and is admin
if (!isset($_SESSION['uid']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header('Location: /login.php');
    exit;
}

require_once __DIR__ . '/src/Db.php';
$pdo = Db::conn();

$error = '';
$success = '';

// Handle admin actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'adjust_credits':
                $user_id = $_POST['user_id'];
                $amount = intval($_POST['amount']);
                $reason = $_POST['reason'];
                
                try {
                    require_once __DIR__ . '/src/CreditsService.php';
                    CreditsService::adjust($user_id, $amount, 'admin_adjustment', 'admin_actions', null);
                    $success = "Credits adjusted successfully for user #$user_id";
                } catch (Exception $e) {
                    $error = 'Failed to adjust credits: ' . $e->getMessage();
                }
                break;
                
            case 'toggle_user_status':
                $user_id = $_POST['user_id'];
                $new_status = $_POST['new_status'];
                
                try {
                    $stmt = $pdo->prepare('UPDATE users SET status = ? WHERE id = ?');
                    $stmt->execute([$new_status, $user_id]);
                    $success = "User #$user_id status updated to $new_status";
                } catch (Exception $e) {
                    $error = 'Failed to update user status: ' . $e->getMessage();
                }
                break;
        }
    }
}

// Get system statistics
$stats = [];

// Total users
$stmt = $pdo->query('SELECT COUNT(*) FROM users');
$stats['total_users'] = $stmt->fetchColumn();

// Active users (last 30 days)
$stmt = $pdo->query('SELECT COUNT(*) FROM users WHERE last_login >= DATE_SUB(NOW(), INTERVAL 30 DAY)');
$stats['active_users'] = $stmt->fetchColumn();

// Total tasks
$stmt = $pdo->query('SELECT COUNT(*) FROM tasks');
$stats['total_tasks'] = $stmt->fetchColumn();

// Completed tasks
$stmt = $pdo->query('SELECT COUNT(*) FROM tasks WHERE status = "completed"');
$stats['completed_tasks'] = $stmt->fetchColumn();

// Total payments
$stmt = $pdo->query('SELECT COUNT(*) FROM payments WHERE status = "completed"');
$stats['total_payments'] = $stmt->fetchColumn();

// Total revenue
$stmt = $pdo->query('SELECT SUM(amount) FROM payments WHERE status = "completed"');
$stats['total_revenue'] = $stmt->fetchColumn() ?: 0;

// Get recent users
$stmt = $pdo->query('
    SELECT id, email, role, credits_balance, status, created_at, last_login 
    FROM users 
    ORDER BY created_at DESC 
    LIMIT 10
');
$recent_users = $stmt->fetchAll();

// Get recent payments
$stmt = $pdo->query('
    SELECT p.*, u.email 
    FROM payments p 
    JOIN users u ON p.user_id = u.id 
    ORDER BY p.created_at DESC 
    LIMIT 10
');
$recent_payments = $stmt->fetchAll();

// Get recent API errors
$stmt = $pdo->query('
    SELECT * FROM api_logs 
    WHERE status_code >= 400 
    ORDER BY created_at DESC 
    LIMIT 10
');
$recent_errors = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - RapidIndexer</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="/assets/css/style.css" rel="stylesheet">
</head>
<body>
    <?php include __DIR__ . '/includes/navbar.php'; ?>
    
    <div class="container mt-4">
        <div class="row">
            <div class="col-12">
                <h1 class="h3 mb-4">Admin Dashboard</h1>
                
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>
                
                <?php if ($success): ?>
                    <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
                <?php endif; ?>
                
                <!-- Statistics Cards -->
                <div class="row mb-4">
                    <div class="col-md-2">
                        <div class="card text-center">
                            <div class="card-body">
                                <h5 class="card-title text-primary"><?php echo number_format($stats['total_users']); ?></h5>
                                <small class="text-muted">Total Users</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="card text-center">
                            <div class="card-body">
                                <h5 class="card-title text-success"><?php echo number_format($stats['active_users']); ?></h5>
                                <small class="text-muted">Active Users</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="card text-center">
                            <div class="card-body">
                                <h5 class="card-title text-info"><?php echo number_format($stats['total_tasks']); ?></h5>
                                <small class="text-muted">Total Tasks</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="card text-center">
                            <div class="card-body">
                                <h5 class="card-title text-warning"><?php echo number_format($stats['completed_tasks']); ?></h5>
                                <small class="text-muted">Completed</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="card text-center">
                            <div class="card-body">
                                <h5 class="card-title text-primary"><?php echo number_format($stats['total_payments']); ?></h5>
                                <small class="text-muted">Payments</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="card text-center">
                            <div class="card-body">
                                <h5 class="card-title text-success">$<?php echo number_format($stats['total_revenue'], 2); ?></h5>
                                <small class="text-muted">Revenue</small>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Main Content -->
                <div class="row">
                    <!-- Recent Users -->
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">Recent Users</h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>Email</th>
                                                <th>Credits</th>
                                                <th>Status</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($recent_users as $user): ?>
                                                <tr>
                                                    <td>#<?php echo $user['id']; ?></td>
                                                    <td>
                                                        <?php echo htmlspecialchars($user['email']); ?>
                                                        <?php if ($user['role'] === 'admin'): ?>
                                                            <span class="badge bg-danger">Admin</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td><?php echo number_format($user['credits_balance']); ?></td>
                                                    <td>
                                                        <span class="badge bg-<?php echo $user['status'] === 'active' ? 'success' : 'danger'; ?>">
                                                            <?php echo htmlspecialchars(ucfirst($user['status'])); ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <button class="btn btn-sm btn-outline-primary" 
                                                                data-bs-toggle="modal" 
                                                                data-bs-target="#adjustCreditsModal"
                                                                data-user-id="<?php echo $user['id']; ?>"
                                                                data-user-email="<?php echo htmlspecialchars($user['email']); ?>">
                                                            Adjust
                                                        </button>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Recent Payments -->
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">Recent Payments</h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>User</th>
                                                <th>Amount</th>
                                                <th>Credits</th>
                                                <th>Status</th>
                                                <th>Date</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($recent_payments as $payment): ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($payment['email']); ?></td>
                                                    <td>$<?php echo number_format($payment['amount'], 2); ?></td>
                                                                                                             <td><?php echo number_format($payment['credits_awarded']); ?></td>
                                                    <td>
                                                        <span class="badge bg-<?php echo $payment['status'] === 'completed' ? 'success' : 'warning'; ?>">
                                                            <?php echo htmlspecialchars(ucfirst($payment['status'])); ?>
                                                        </span>
                                                    </td>
                                                    <td><?php echo date('M j', strtotime($payment['created_at'])); ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- API Errors -->
                <?php if (!empty($recent_errors)): ?>
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">Recent API Errors</h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>Time</th>
                                                <th>Endpoint</th>
                                                <th>Status</th>
                                                <th>Error</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($recent_errors as $error): ?>
                                                <tr>
                                                    <td><?php echo date('M j g:i A', strtotime($error['created_at'])); ?></td>
                                                    <td><?php echo htmlspecialchars($error['endpoint']); ?></td>
                                                    <td>
                                                        <span class="badge bg-danger"><?php echo $error['status_code']; ?></span>
                                                    </td>
                                                    <td>
                                                        <small class="text-muted">
                                                            <?php echo htmlspecialchars(substr($error['error_message'] ?? '', 0, 50)); ?>...
                                                        </small>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Adjust Credits Modal -->
    <div class="modal fade" id="adjustCreditsModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Adjust User Credits</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="adjust_credits">
                        <input type="hidden" name="user_id" id="adjustUserId">
                        
                        <div class="mb-3">
                            <label class="form-label">User Email</label>
                            <input type="text" class="form-control" id="adjustUserEmail" readonly>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Credit Adjustment</label>
                            <input type="number" class="form-control" name="amount" required 
                                   placeholder="Positive for credit, negative for debit">
                            <small class="text-muted">Use positive numbers to add credits, negative to remove</small>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Reason</label>
                            <textarea class="form-control" name="reason" required 
                                      placeholder="Reason for adjustment"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Adjust Credits</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Handle modal data
        document.addEventListener('DOMContentLoaded', function() {
            const modal = document.getElementById('adjustCreditsModal');
            modal.addEventListener('show.bs.modal', function(event) {
                const button = event.relatedTarget;
                const userId = button.getAttribute('data-user-id');
                const userEmail = button.getAttribute('data-user-email');
                
                document.getElementById('adjustUserId').value = userId;
                document.getElementById('adjustUserEmail').value = userEmail;
            });
        });
    </script>
</body>
</html>
