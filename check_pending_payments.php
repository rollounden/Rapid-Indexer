<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/src/Db.php';

echo "<h1>PayPal Payment Completion Checker</h1>";

try {
    $pdo = Db::conn();
    
    echo "<h2>1. Checking Pending Payments</h2>";
    
    // Get all pending payments with PayPal order IDs
    $stmt = $pdo->prepare('
        SELECT * FROM payments 
        WHERE status = ? AND paypal_order_id IS NOT NULL 
        ORDER BY created_at DESC
    ');
    $stmt->execute(['pending']);
    $pending_payments = $stmt->fetchAll();
    
    if (empty($pending_payments)) {
        echo "<p>No pending payments found.</p>";
    } else {
        echo "<p>Found " . count($pending_payments) . " pending payments:</p>";
        
        require_once __DIR__ . '/src/PayPalService.php';
        $paypal = new PayPalService();
        
        foreach ($pending_payments as $payment) {
            echo "<h3>Payment #" . $payment['id'] . " - $" . $payment['amount'] . "</h3>";
            echo "<p><strong>Order ID:</strong> " . $payment['paypal_order_id'] . "</p>";
            echo "<p><strong>Created:</strong> " . $payment['created_at'] . "</p>";
            
            try {
                // Check order status with PayPal
                $order = $paypal->getOrder($payment['paypal_order_id']);
                echo "<p><strong>PayPal Order Status:</strong> " . $order['status'] . "</p>";
                
                if ($order['status'] === 'COMPLETED') {
                    // Order is completed, check if it has captures
                    if (isset($order['purchase_units'][0]['payments']['captures'])) {
                        $captures = $order['purchase_units'][0]['payments']['captures'];
                        if (!empty($captures)) {
                            $capture = $captures[0];
                            echo "<p><strong>Capture Status:</strong> " . $capture['status'] . "</p>";
                            
                            if ($capture['status'] === 'COMPLETED') {
                                // Payment is completed, update our database
                                $credits = intval($payment['amount'] / PRICE_PER_CREDIT_USD);
                                
                                // Update payment status
                                $stmt = $pdo->prepare('UPDATE payments SET status = ?, paypal_capture_id = ?, credits_awarded = ?, updated_at = NOW() WHERE id = ?');
                                $stmt->execute(['paid', $capture['id'], $credits, $payment['id']]);
                                
                                // Add credits to user account
                                $stmt = $pdo->prepare('UPDATE users SET credits_balance = credits_balance + ? WHERE id = ?');
                                $stmt->execute([$credits, $payment['user_id']]);
                                
                                // Log the credit addition
                                $stmt = $pdo->prepare('INSERT INTO credit_ledger (user_id, delta, reason, reference_table, reference_id) VALUES (?, ?, ?, ?, ?)');
                                $stmt->execute([$payment['user_id'], $credits, 'payment', 'payments', $payment['id']]);
                                
                                echo "<p>✅ <strong>Payment completed! Awarded $credits credits.</strong></p>";
                            } else {
                                echo "<p>⏳ Capture not completed yet: " . $capture['status'] . "</p>";
                            }
                        } else {
                            echo "<p>⏳ No captures found yet</p>";
                        }
                    } else {
                        echo "<p>⏳ No payments found in order yet</p>";
                    }
                } else {
                    echo "<p>⏳ Order not completed yet: " . $order['status'] . "</p>";
                }
                
            } catch (Exception $e) {
                echo "<p>❌ Error checking order: " . $e->getMessage() . "</p>";
            }
            
            echo "<hr>";
        }
    }
    
    echo "<h2>2. Current User Credits</h2>";
    $stmt = $pdo->query('SELECT id, email, credits_balance FROM users');
    $users = $stmt->fetchAll();
    
    echo "<table border='1'>";
    echo "<tr><th>User ID</th><th>Email</th><th>Credits</th></tr>";
    foreach ($users as $user) {
        echo "<tr>";
        echo "<td>" . $user['id'] . "</td>";
        echo "<td>" . $user['email'] . "</td>";
        echo "<td>" . $user['credits_balance'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    echo "<h2>3. Recent Payments</h2>";
    $stmt = $pdo->query('SELECT * FROM payments ORDER BY created_at DESC LIMIT 10');
    $payments = $stmt->fetchAll();
    
    echo "<table border='1'>";
    echo "<tr><th>ID</th><th>Amount</th><th>Status</th><th>Credits</th><th>Created</th></tr>";
    foreach ($payments as $payment) {
        echo "<tr>";
        echo "<td>" . $payment['id'] . "</td>";
        echo "<td>$" . $payment['amount'] . "</td>";
        echo "<td>" . $payment['status'] . "</td>";
        echo "<td>" . $payment['credits_awarded'] . "</td>";
        echo "<td>" . $payment['created_at'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
} catch (Exception $e) {
    echo "<p>❌ Error: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<p><a href='payments.php'>Go to Payments Page</a></p>";
echo "<p><a href='update_pending_payments.php'>Update Pending Payments</a></p>";
?>
