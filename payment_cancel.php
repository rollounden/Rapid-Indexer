<?php
session_start();
require_once __DIR__ . '/config/config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$token = $_GET['token'] ?? null;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Cancelled - SpeedyIndex</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <?php include 'includes/navbar.php'; ?>
    
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow">
                    <div class="card-header bg-warning text-dark">
                        <h4 class="mb-0">
                            <i class="fas fa-times-circle me-2"></i>
                            Payment Cancelled
                        </h4>
                    </div>
                    <div class="card-body text-center">
                        <div class="mb-4">
                            <i class="fas fa-times-circle text-warning" style="font-size: 4rem;"></i>
                        </div>
                        
                        <h3>Payment Cancelled</h3>
                        <p class="text-muted mb-4">
                            Your payment was cancelled and no charges were made to your account.
                        </p>
                        
                        <?php if ($token): ?>
                            <div class="alert alert-info">
                                <strong>Order ID:</strong> <?php echo htmlspecialchars($token); ?>
                            </div>
                        <?php endif; ?>
                        
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="card bg-light">
                                    <div class="card-body">
                                        <h5>What happened?</h5>
                                        <ul class="text-start">
                                            <li>You cancelled the payment</li>
                                            <li>No money was charged</li>
                                            <li>No credits were added</li>
                                            <li>You can try again anytime</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card bg-light">
                                    <div class="card-body">
                                        <h5>Need help?</h5>
                                        <ul class="text-start">
                                            <li>Check your payment method</li>
                                            <li>Ensure sufficient funds</li>
                                            <li>Try a different card</li>
                                            <li>Contact support if needed</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="d-grid gap-2 d-md-flex justify-content-md-center">
                            <a href="payments.php" class="btn btn-primary me-md-2">
                                <i class="fas fa-credit-card me-1"></i>
                                Try Payment Again
                            </a>
                            <a href="dashboard.php" class="btn btn-secondary me-md-2">
                                <i class="fas fa-home me-1"></i>
                                Go to Dashboard
                            </a>
                            <a href="tasks.php" class="btn btn-success">
                                <i class="fas fa-tasks me-1"></i>
                                View Tasks
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
