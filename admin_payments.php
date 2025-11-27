<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

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

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'delete_payment') {
            $paymentId = intval($_POST['payment_id']);
            try {
                // Check if payment exists
                $stmt = $pdo->prepare('SELECT * FROM payments WHERE id = ?');
                $stmt->execute([$paymentId]);
                $payment = $stmt->fetch();
                
                if (!$payment) {
                    throw new Exception("Payment not found.");
                }
                
                // We generally shouldn't delete payments for accounting reasons, 
                // but this is a requested feature.
                // Maybe better to mark as 'deleted' or 'hidden'? 
                // But user asked to "delete".
                
                $stmt = $pdo->prepare('DELETE FROM payments WHERE id = ?');
                $stmt->execute([$paymentId]);
                $success = "Payment #$paymentId deleted successfully.";
            } catch (Exception $e) {
                $error = "Failed to delete payment: " . $e->getMessage();
            }
        }
    }
}

// Pagination
$page = max(1, intval($_GET['page'] ?? 1));
$per_page = 20;
$offset = ($page - 1) * $per_page;

// Get total payments
$stmt = $pdo->query("SELECT COUNT(*) FROM payments");
$total_items = $stmt->fetchColumn();
$total_pages = ceil($total_items / $per_page);

// Get payments list
$stmt = $pdo->prepare("
    SELECT p.*, u.email as user_email 
    FROM payments p 
    LEFT JOIN users u ON p.user_id = u.id 
    ORDER BY p.created_at DESC 
    LIMIT :limit OFFSET :offset
");
$stmt->bindValue(':limit', $per_page, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$payments = $stmt->fetchAll();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Payments - Rapid Indexer Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="/assets/css/style.css" rel="stylesheet">
</head>
<body>
    <?php include __DIR__ . '/includes/navbar.php'; ?>
    
    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-0">Manage Payments</h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="/admin.php">Dashboard</a></li>
                        <li class="breadcrumb-item active">Payments</li>
                    </ol>
                </nav>
            </div>
        </div>
        
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>
        
        <div class="card">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th>ID</th>
                                <th>User</th>
                                <th>Amount</th>
                                <th>Method</th>
                                <th>Credits</th>
                                <th>Status</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($payments)): ?>
                                <tr>
                                    <td colspan="8" class="text-center py-4 text-muted">No payments found.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($payments as $payment): ?>
                                    <tr>
                                        <td>#<?php echo $payment['id']; ?></td>
                                        <td>
                                            <div class="fw-bold"><?php echo htmlspecialchars($payment['user_email'] ?? 'Unknown User'); ?></div>
                                            <div class="small text-muted">User #<?php echo $payment['user_id']; ?></div>
                                        </td>
                                        <td>$<?php echo number_format($payment['amount'], 2); ?></td>
                                        <td><?php echo htmlspecialchars(ucfirst($payment['method'])); ?></td>
                                        <td><?php echo number_format($payment['credits_awarded']); ?></td>
                                        <td>
                                            <span class="badge bg-<?php echo $payment['status'] === 'paid' || $payment['status'] === 'completed' ? 'success' : ($payment['status'] === 'pending' ? 'warning' : 'danger'); ?>">
                                                <?php echo ucfirst($payment['status']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div><?php echo date('M j, Y', strtotime($payment['created_at'])); ?></div>
                                            <div class="small text-muted"><?php echo date('H:i', strtotime($payment['created_at'])); ?></div>
                                        </td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-outline-danger" 
                                                    onclick="confirmDelete(<?php echo $payment['id']; ?>)">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <?php if ($total_pages > 1): ?>
                <div class="card-footer">
                    <nav aria-label="Payment pagination">
                        <ul class="pagination justify-content-center mb-0">
                            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                                </li>
                            <?php endfor; ?>
                        </ul>
                    </nav>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deletePaymentModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirm Delete</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete payment #<strong id="deletePaymentId"></strong>?</p>
                    <p class="text-danger small">This action cannot be undone.</p>
                </div>
                <div class="modal-footer">
                    <form method="POST">
                        <input type="hidden" name="action" value="delete_payment">
                        <input type="hidden" name="payment_id" id="deletePaymentIdInput">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">Delete Payment</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function confirmDelete(id) {
            document.getElementById('deletePaymentId').textContent = id;
            document.getElementById('deletePaymentIdInput').value = id;
            new bootstrap.Modal(document.getElementById('deletePaymentModal')).show();
        }
    </script>
</body>
</html>
