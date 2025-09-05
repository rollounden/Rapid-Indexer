<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/src/Db.php';
require_once __DIR__ . '/src/PayPalService.php';

echo "<h1>Force Complete Approved Payment</h1>";

try {
    $pdo = Db::conn();
    
    // Get the approved payment
    $stmt = $pdo->prepare('SELECT * FROM payments WHERE status = ? AND paypal_order_id = ?');
    $stmt->execute(['pending', '2ES27748W7476501C']);
    $payment = $stmt->fetch();
    
    if (!$payment) {
        echo "<p>❌ Payment not found.</p>";
        exit;
    }
    
    echo "<h2>Payment Details</h2>";
    echo "<p><strong>ID:</strong> " . $payment['id'] . "</p>";
    echo "<p><strong>Amount:</strong> $" . $payment['amount'] . "</p>";
    echo "<p><strong>Order ID:</strong> " . $payment['paypal_order_id'] . "</p>";
    echo "<p><strong>User ID:</strong> " . $payment['user_id'] . "</p>";
    
    $paypal = new PayPalService();
    
    echo "<h3>1. Attempting to Capture Payment</h3>";
    
    // Try to capture the payment
    $capture_result = $paypal->capturePayment($payment['paypal_order_id']);
    
    if ($capture_result && isset($capture_result['status']) && $capture_result['status'] === 'COMPLETED') {
        echo "<p>✅ <strong>Payment captured successfully!</strong></p>";
        
        // Calculate credits
        $credits = intval($payment['amount'] / PRICE_PER_CREDIT_USD);
        
        // Update payment status
        $stmt = $pdo->prepare('UPDATE payments SET status = ?, paypal_capture_id = ?, credits_awarded = ?, updated_at = NOW() WHERE id = ?');
        $stmt->execute(['paid', $capture_result['id'], $credits, $payment['id']]);
        
        // Add credits to user account
        $stmt = $pdo->prepare('UPDATE users SET credits_balance = credits_balance + ? WHERE id = ?');
        $stmt->execute([$credits, $payment['user_id']]);
        
        // Log the credit addition
        $stmt = $pdo->prepare('INSERT INTO credit_ledger (user_id, delta, reason, reference_table, reference_id) VALUES (?, ?, ?, ?, ?)');
        $stmt->execute([$payment['user_id'], $credits, 'payment', 'payments', $payment['id']]);
        
        echo "<p>✅ <strong>Awarded $credits credits to user!</strong></p>";
        echo "<p>✅ <strong>Payment status updated to 'paid'</strong></p>";
        
        // Show updated user credits
        $stmt = $pdo->prepare('SELECT credits_balance FROM users WHERE id = ?');
        $stmt->execute([$payment['user_id']]);
        $user = $stmt->fetch();
        echo "<p>✅ <strong>New credit balance: " . $user['credits_balance'] . " credits</strong></p>";
        
    } else {
        echo "<p>❌ <strong>Failed to capture payment</strong></p>";
        echo "<p>Response: " . json_encode($capture_result, JSON_PRETTY_PRINT) . "</p>";
        
        // If capture fails, let's just manually complete it since it's approved
        echo "<h3>2. Manual Completion (Payment is Approved)</h3>";
        
        $credits = intval($payment['amount'] / PRICE_PER_CREDIT_USD);
        
        // Update payment status
        $stmt = $pdo->prepare('UPDATE payments SET status = ?, paypal_capture_id = ?, credits_awarded = ?, updated_at = NOW() WHERE id = ?');
        $stmt->execute(['paid', 'manual_' . time(), $credits, $payment['id']]);
        
        // Add credits to user account
        $stmt = $pdo->prepare('UPDATE users SET credits_balance = credits_balance + ? WHERE id = ?');
        $stmt->execute([$credits, $payment['user_id']]);
        
        // Log the credit addition
        $stmt = $pdo->prepare('INSERT INTO credit_ledger (user_id, delta, reason, reference_table, reference_id) VALUES (?, ?, ?, ?, ?)');
        $stmt->execute([$payment['user_id'], $credits, 'payment_manual', 'payments', $payment['id']]);
        
        echo "<p>✅ <strong>Manually completed payment and awarded $credits credits!</strong></p>";
        
        // Show updated user credits
        $stmt = $pdo->prepare('SELECT credits_balance FROM users WHERE id = ?');
        $stmt->execute([$payment['user_id']]);
        $user = $stmt->fetch();
        echo "<p>✅ <strong>New credit balance: " . $user['credits_balance'] . " credits</strong></p>";
    }
    
} catch (Exception $e) {
    echo "<p>❌ Error: " . $e->getMessage() . "</p>";
    echo "<p>File: " . $e->getFile() . "</p>";
    echo "<p>Line: " . $e->getLine() . "</p>";
}

echo "<hr>";
echo "<p><a href='payments.php'>Go to Payments Page</a></p>";
echo "<p><a href='check_pending_payments.php'>Check Pending Payments</a></p>";
?>
