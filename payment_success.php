<?php
session_start();
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/src/Db.php';
require_once __DIR__ . '/src/SettingsService.php';
require_once __DIR__ . '/src/PayPalService.php';
require_once __DIR__ . '/src/CryptomusService.php'; // Added include

// Check if user is logged in
if (!isset($_SESSION['uid'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['uid'];
$message = '';
$error = '';
$status = ''; // Track status for UI logic

try {
    // Case 1: Cryptomus Return (order_id in GET)
    if (isset($_GET['order_id'])) {
        $order_id = $_GET['order_id'];
        
        // Handle manual check action
        if (isset($_GET['action']) && $_GET['action'] === 'check_status') {
            try {
                $cryptoService = new CryptomusService();
                $newStatus = $cryptoService->checkStatus($order_id);
                
                if ($newStatus === 'paid') {
                    // Redirect to self without action to show success
                    header("Location: payment_success.php?order_id=$order_id");
                    exit;
                } elseif ($newStatus === 'failed' || $newStatus === 'cancel') {
                    header("Location: payment_cancel.php?token=$order_id");
                    exit;
                }
            } catch (Exception $e) {
                $error = 'Failed to check status: ' . $e->getMessage();
            }
        }
        
        $pdo = Db::conn();
        // Find payment by order ID
        $stmt = $pdo->prepare('SELECT * FROM payments WHERE paypal_order_id = ? LIMIT 1');
        $stmt->execute([$order_id]);
        $payment = $stmt->fetch();
        
        if ($payment) {
            $status = $payment['status'];
            
            if ($payment['status'] === 'paid') {
                $message = "Payment successful! Credits have been added to your account.";
                $amount = $payment['amount'];
                $credits_amount = $payment['credits_awarded'];
            } elseif ($payment['status'] === 'failed') {
                 // Redirect to cancel page if we detect it's failed
                 header("Location: payment_cancel.php?token=$order_id");
                 exit;
            } else {
                // It might not be confirmed yet by webhook
                $message = "Payment initiated. Status: " . ucfirst($payment['status']);
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
        $price_per_credit = (float)SettingsService::get('price_per_credit', (string)DEFAULT_PRICE_PER_CREDIT_USD);
        $credits_amount = intval($amount / $price_per_credit);
        
        // Connect to database
        $pdo = Db::conn();
        
        // Check if payment already exists
        $stmt = $pdo->prepare('SELECT id FROM payments WHERE paypal_capture_id = ?');
        $stmt->execute([$payment_id]);
            
        if ($stmt->fetch()) {
            $message = 'Payment already processed. Credits have been added to your account.';
            $status = 'paid';
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
            $status = 'paid';
        }
    } else {
        // Optional: redirect to payments if no params
        // header('Location: payments.php');
        // exit;
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
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Success - Rapid Indexer</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Rubik"', 'sans-serif'],
                    },
                    colors: {
                        primary: {
                            50: '#fff1f2',
                            100: '#ffe4e6',
                            200: '#fecdd3',
                            300: '#fda4af',
                            400: '#fb7185',
                            500: '#f43f5e',
                            600: '#e11d48', 
                            700: '#be123c', // Base primary - deep rose
                            800: '#9f1239',
                            900: '#881337', 
                            950: '#4c0519',
                        }
                    }
                }
            }
        }
    </script>
    <link href="https://fonts.googleapis.com/css2?family=Rubik:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background-color: #141414; color: #efefef; }
    </style>
</head>
<body class="min-h-screen flex flex-col">
    <?php include __DIR__ . '/includes/navbar.php'; ?>
    
    <div class="flex-grow container mx-auto px-4 py-12">
        <div class="max-w-2xl mx-auto">
            <div class="bg-[#1c1c1c] border border-white/10 rounded-xl shadow-2xl overflow-hidden">
                <div class="<?php echo $status === 'paid' ? 'bg-green-600' : 'bg-yellow-600'; ?> px-6 py-4">
                    <h4 class="text-xl font-bold text-white flex items-center gap-2">
                        <i class="fas <?php echo $status === 'paid' ? 'fa-check-circle' : 'fa-clock'; ?>"></i>
                        <?php echo $status === 'paid' ? 'Payment Successful' : 'Payment Processing'; ?>
                    </h4>
                </div>
                
                <div class="p-8">
                    <?php if ($message): ?>
                        <div class="<?php echo $status === 'paid' ? 'bg-green-500/10 border-green-500/20 text-green-400' : 'bg-yellow-500/10 border-yellow-500/20 text-yellow-400'; ?> border p-4 rounded-lg mb-6 flex items-start gap-3">
                            <i class="fas <?php echo $status === 'paid' ? 'fa-check' : 'fa-info-circle'; ?> mt-1"></i>
                            <div>
                                <?php echo htmlspecialchars($message); ?>
                                <?php if ($status === 'pending' && isset($order_id)): ?>
                                    <div class="mt-2 text-sm opacity-80">
                                        Credits will be added automatically once confirmed.
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($error): ?>
                        <div class="bg-red-500/10 border border-red-500/20 text-red-400 p-4 rounded-lg mb-6 flex items-center gap-3">
                            <i class="fas fa-exclamation-triangle"></i>
                            <?php echo htmlspecialchars($error); ?>
                        </div>
                    <?php endif; ?>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                        <div class="bg-white/5 rounded-lg p-6 text-center border border-white/5">
                            <h5 class="text-sm font-bold text-gray-400 uppercase mb-2">Current Balance</h5>
                            <h2 class="text-3xl font-bold text-primary-500"><?php echo number_format($user_credits); ?></h2>
                            <p class="text-xs text-gray-500 mt-1">Credits available</p>
                        </div>
                        
                        <div class="bg-white/5 rounded-lg p-6 text-center border border-white/5">
                            <h5 class="text-sm font-bold text-gray-400 uppercase mb-2">Transaction Amount</h5>
                            <?php if (isset($amount)): ?>
                                <h2 class="text-3xl font-bold text-green-400">$<?php echo number_format($amount, 2); ?></h2>
                                <p class="text-xs text-gray-500 mt-1">Amount paid</p>
                            <?php else: ?>
                                <p class="text-gray-500">Details unavailable</p>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="flex flex-col sm:flex-row justify-center gap-4">
                        <a href="dashboard.php" class="px-6 py-3 rounded-lg bg-white/10 hover:bg-white/20 text-white font-medium transition-colors text-center">
                            <i class="fas fa-home mr-2"></i> Dashboard
                        </a>
                        
                        <?php if ($status === 'pending' && isset($order_id)): ?>
                            <a href="payment_success.php?order_id=<?php echo htmlspecialchars($order_id); ?>&action=check_status" class="px-6 py-3 rounded-lg bg-primary-600 hover:bg-primary-700 text-white font-bold transition-all shadow-lg shadow-primary-900/20 text-center">
                                <i class="fas fa-sync-alt mr-2"></i> Check Status Again
                            </a>
                        <?php endif; ?>
                        
                        <a href="tasks.php" class="px-6 py-3 rounded-lg bg-primary-600 hover:bg-primary-700 text-white font-bold transition-all shadow-lg shadow-primary-900/20 text-center">
                            <i class="fas fa-rocket mr-2"></i> New Task
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <?php include __DIR__ . '/includes/footer_new.php'; ?>
</body>
</html>
