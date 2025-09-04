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
$pdo = Db::conn();

$error = '';
$success = '';

// Handle payment creation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'create_payment') {
    $amount = floatval($_POST['amount']);
    $credits = intval($amount / PRICE_PER_CREDIT_USD);
    
    if ($amount < 1) {
        $error = 'Minimum payment amount is $1.00';
    } else {
        try {
            require_once __DIR__ . '/src/PaymentService.php';
            $payment_id = PaymentService::recordPending($_SESSION['uid'], $amount, 'USD');
            
            // For now, just show success message (PayPal integration would go here)
            $success = "Payment created! You'll receive $credits credits for $$amount.";
        } catch (Exception $e) {
            $error = 'Failed to create payment: ' . $e->getMessage();
        }
    }
}

// Get user's current credits
$stmt = $pdo->prepare('SELECT credits_balance FROM users WHERE id = ?');
$stmt->execute([$_SESSION['uid']]);
$user_credits = $stmt->fetchColumn();

// Get payment history
$stmt = $pdo->prepare('
    SELECT p.*, 
           CASE 
               WHEN p.status = "completed" THEN p.credits_awarded 
               ELSE 0 
           END as credits_received
    FROM payments p 
    WHERE p.user_id = ? 
    ORDER BY p.created_at DESC 
    LIMIT 20
');
$stmt->execute([$_SESSION['uid']]);
$payments = $stmt->fetchAll();

// Get credit ledger (recent transactions)
$stmt = $pdo->prepare('
         SELECT cl.*, 
            CASE 
                WHEN cl.reason = "payment" THEN "Payment"
                WHEN cl.reason = "task_deduction" THEN "Task Creation"
                WHEN cl.reason = "task_refund" THEN "Task Refund"
                WHEN cl.reason = "admin_adjustment" THEN "Admin Adjustment"
                ELSE cl.reason
            END as transaction_label
    FROM credit_ledger cl 
    WHERE cl.user_id = ? 
    ORDER BY cl.created_at DESC 
    LIMIT 10
');
$stmt->execute([$_SESSION['uid']]);
$ledger_entries = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payments - RapidIndexer</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="/assets/css/style.css" rel="stylesheet">
</head>
<body>
    <?php include __DIR__ . '/includes/navbar.php'; ?>
    
    <div class="container mt-4">
        <div class="row">
            <div class="col-12">
                <h1 class="h3 mb-4">Payments & Credits</h1>
                
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>
                
                <?php if ($success): ?>
                    <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
                <?php endif; ?>
                
                <!-- Current Balance -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">Current Balance</h5>
                                <div class="d-flex align-items-center">
                                    <h2 class="text-primary mb-0"><?php echo number_format($user_credits); ?></h2>
                                    <span class="text-muted ms-2">credits</span>
                                </div>
                                <small class="text-muted">1 credit = $<?php echo PRICE_PER_CREDIT_USD; ?></small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">Add Credits</h5>
                                <form method="POST">
                                    <input type="hidden" name="action" value="create_payment">
                                    <div class="input-group">
                                        <span class="input-group-text">$</span>
                                        <input type="number" class="form-control" name="amount" 
                                               placeholder="Amount" min="1" step="0.01" required>
                                        <button type="submit" class="btn btn-primary">Add Credits</button>
                                    </div>
                                    <small class="text-muted">Minimum $1.00</small>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Payment History -->
                <div class="row">
                    <div class="col-md-8">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">Payment History</h5>
                            </div>
                            <div class="card-body">
                                <?php if (empty($payments)): ?>
                                    <p class="text-muted text-center py-3">No payments yet</p>
                                <?php else: ?>
                                    <div class="table-responsive">
                                        <table class="table table-hover">
                                            <thead>
                                                <tr>
                                                    <th>Date</th>
                                                    <th>Amount</th>
                                                    <th>Credits Awarded</th>
                                                    <th>Status</th>
                                                    <th>Transaction ID</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($payments as $payment): ?>
                                                    <tr>
                                                        <td><?php echo date('M j, Y g:i A', strtotime($payment['created_at'])); ?></td>
                                                        <td>$<?php echo number_format($payment['amount'], 2); ?></td>
                                                                                                                 <td><?php echo number_format($payment['credits_awarded']); ?></td>
                                                        <td>
                                                            <?php
                                                            $status_class = 'secondary';
                                                            switch ($payment['status']) {
                                                                case 'pending': $status_class = 'warning'; break;
                                                                case 'completed': $status_class = 'success'; break;
                                                                case 'failed': $status_class = 'danger'; break;
                                                            }
                                                            ?>
                                                            <span class="badge bg-<?php echo $status_class; ?>">
                                                                <?php echo htmlspecialchars(ucfirst($payment['status'])); ?>
                                                            </span>
                                                        </td>
                                                                                                                 <td>
                                                             <small class="text-muted">
                                                                 <?php echo htmlspecialchars($payment['paypal_capture_id'] ?: 'N/A'); ?>
                                                             </small>
                                                         </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Recent Credit Transactions -->
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">Recent Transactions</h5>
                            </div>
                            <div class="card-body">
                                <?php if (empty($ledger_entries)): ?>
                                    <p class="text-muted text-center py-3">No transactions yet</p>
                                <?php else: ?>
                                    <div class="list-group list-group-flush">
                                        <?php foreach ($ledger_entries as $entry): ?>
                                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                                <div>
                                                    <small class="text-muted">
                                                        <?php echo htmlspecialchars($entry['transaction_label']); ?>
                                                    </small>
                                                    <br>
                                                    <small class="text-muted">
                                                        <?php echo date('M j, g:i A', strtotime($entry['created_at'])); ?>
                                                    </small>
                                                </div>
                                                                                                 <span class="<?php echo $entry['delta'] >= 0 ? 'text-success' : 'text-danger'; ?>">
                                                     <?php echo ($entry['delta'] >= 0 ? '+' : '') . number_format($entry['delta']); ?>
                                                 </span>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
