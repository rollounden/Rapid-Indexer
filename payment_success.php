<?php
session_start();
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/src/Db.php';
require_once __DIR__ . '/src/PayPalService.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$message = '';
$error = '';

try {
    // Case 1: Cryptomus Return (order_id in GET)
    if (isset($_GET['order_id'])) {
        $order_id = $_GET['order_id'];
        
        $pdo = Db::conn();
        // Find payment by order ID
        $stmt = $pdo->prepare('SELECT * FROM payments WHERE paypal_order_id = ? LIMIT 1');
        $stmt->execute([$order_id]);
        $payment = $stmt->fetch();
        
        if ($payment) {
            if ($payment['status'] === 'paid') {
                $message = "Payment successful! Credits have been added to your account.";
                $amount = $payment['amount'];
                $credits_amount = $payment['credits_awarded'];
            } else {
                // It might not be confirmed yet by webhook
                $message = "Payment initiated. Credits will be added once the transaction is confirmed on the blockchain (this may take a few minutes).";
                $amount = $payment['amount'];
                $credits_amount = 0;
            }
        } else {
            $error = 'Order not found.';
        }
        
    } 
    // Case 2: PayPal Return (token in GET)
    elseif (isset($_GET['token'])) {
        
        $token = $_GET['token'];
        
        // Initialize PayPal service
        $paypal = new PayPalService();
        
        // Get order details
        $order = $paypal->getOrder($token);
        
        if ($order['status'] !== 'APPROVED') {
            throw new Exception('Payment not approved');
        }
        
        // Capture the payment
        $capture = $paypal->capturePayment($token);
        
        if ($capture['status'] !== 'COMPLETED') {
            throw new Exception('Payment capture failed');
        }
        
        // Get payment details
        $payment_id = $capture['purchase_units'][0]['payments']['captures'][0]['id'];
        $amount = $capture['purchase_units'][0]['payments']['captures'][0]['amount']['value'];
        $currency = $capture['purchase_units'][0]['payments']['captures'][0]['amount']['currency_code'];
        
        // Calculate credits
        $credits_amount = intval($amount / PRICE_PER_CREDIT_USD);
        
        // Connect to database
        $pdo = Db::conn();
        
                // Check if payment already exists
            $stmt = $pdo->prepare('SELECT id FROM payments WHERE paypal_capture_id = ?');
            $stmt->execute([$payment_id]);
            
            if ($stmt->fetch()) {
                $message = 'Payment already processed. Credits have been added to your account.';
            } else {
                // Insert payment record
                $stmt = $pdo->prepare('
                    INSERT INTO payments (user_id, amount, currency, method, paypal_capture_id, paypal_order_id, credits_awarded, status, created_at, updated_at) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
                ');
                $stmt->execute([
                    $user_id,
                    $amount,
                    $currency,
                    'paypal',
                    $payment_id,
                    $token,
                    $credits_amount,
                    'paid'
                ]);
                
                // Add credits to user account using CreditsService
                require_once __DIR__ . '/src/CreditsService.php';
                CreditsService::adjust($user_id, $credits_amount, 'payment', 'payments', $pdo->lastInsertId());
                
                $message = "Payment successful! $credits_amount credits have been added to your account.";
            }
    } else {
        throw new Exception('No payment reference provided');
    }
    
} catch (Exception $e) {
    $error = 'Payment processing error: ' . $e->getMessage();
}

// Get updated user info
try {
    $pdo = Db::conn();
    $stmt = $pdo->prepare('SELECT credits_balance FROM users WHERE id = ?');
    $stmt->execute([$user_id]);
    $user_credits = $stmt->fetchColumn();
} catch (Exception $e) {
    $user_credits = 0;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Success - Rapid Indexer</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <?php include 'includes/navbar.php'; ?>
    
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow">
                    <div class="card-header bg-success text-white">
                        <h4 class="mb-0">
                            <i class="fas fa-check-circle me-2"></i>
                            Payment Status
                        </h4>
                    </div>
                    <div class="card-body">
                        <?php if ($message): ?>
                            <div class="alert alert-success">
                                <i class="fas fa-check-circle me-2"></i>
                                <?php echo htmlspecialchars($message); ?>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($error): ?>
                            <div class="alert alert-danger">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                <?php echo htmlspecialchars($error); ?>
                            </div>
                        <?php endif; ?>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="card bg-light">
                                    <div class="card-body text-center">
                                        <h5>Current Credits</h5>
                                        <h2 class="text-primary"><?php echo number_format($user_credits); ?></h2>
                                        <small class="text-muted">Available for indexing</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card bg-light">
                                    <div class="card-body text-center">
                                        <h5>Payment Details</h5>
                                        <?php if (isset($amount)): ?>
                                            <h2 class="text-success">$<?php echo number_format($amount, 2); ?></h2>
                                            <small class="text-muted">Amount paid</small>
                                        <?php else: ?>
                                            <p class="text-muted">Payment details not available</p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mt-4 text-center">
                            <a href="dashboard.php" class="btn btn-primary me-2">
                                <i class="fas fa-home me-1"></i>
                                Go to Dashboard
                            </a>
                            <a href="tasks.php" class="btn btn-success me-2">
                                <i class="fas fa-tasks me-1"></i>
                                Create New Task
                            </a>
                            <a href="payments.php" class="btn btn-info">
                                <i class="fas fa-credit-card me-1"></i>
                                View Payments
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
