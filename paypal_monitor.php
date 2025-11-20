<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/src/Db.php';
require_once __DIR__ . '/src/PayPalService.php';

echo "<h1>PayPal Payment Monitor</h1>";
echo "<p><strong>This script checks for completed PayPal payments and processes them automatically.</strong></p>";

try {
    $pdo = Db::conn();
    $paypal = new PayPalService();
    
    // Get all pending payments
    $stmt = $pdo->prepare('SELECT * FROM payments WHERE status = ? AND paypal_order_id IS NOT NULL ORDER BY created_at DESC');
    $stmt->execute(['pending']);
    $pending_payments = $stmt->fetchAll();
    
    if (empty($pending_payments)) {
        echo "<p>✅ <strong>No pending payments found.</strong></p>";
        exit;
    }
    
    echo "<h2>Found " . count($pending_payments) . " Pending Payments</h2>";
    
    $processed = 0;
    $errors = 0;
    
    foreach ($pending_payments as $payment) {
        echo "<h3>Checking Payment #" . $payment['id'] . " - $" . $payment['amount'] . "</h3>";
        echo "<p><strong>Order ID:</strong> " . $payment['paypal_order_id'] . "</p>";
        echo "<p><strong>Created:</strong> " . $payment['created_at'] . "</p>";
        
        try {
            // Get order details from PayPal
            $order = $paypal->getOrder($payment['paypal_order_id']);
            
            if (!$order) {
                echo "<p>❌ <strong>Failed to get order details</strong></p>";
                $errors++;
                continue;
            }
            
            echo "<p><strong>PayPal Order Status:</strong> " . $order['status'] . "</p>";
            
            // Check if order is completed
            if ($order['status'] === 'COMPLETED') {
                echo "<p>✅ <strong>Order is completed!</strong></p>";
                
                // Get capture details
                if (isset($order['purchase_units'][0]['payments']['captures'])) {
                    $captures = $order['purchase_units'][0]['payments']['captures'];
                    $completed_capture = null;
                    
                    foreach ($captures as $capture) {
                        if ($capture['status'] === 'COMPLETED') {
                            $completed_capture = $capture;
                            break;
                        }
                    }
                    
                    if ($completed_capture) {
                        echo "<p>✅ <strong>Found completed capture: " . $completed_capture['id'] . "</strong></p>";
                        
                        // Calculate credits
                        $credits = intval($payment['amount'] / PRICE_PER_CREDIT_USD);
                        
                        // Update payment status
                        $stmt = $pdo->prepare('UPDATE payments SET status = ?, paypal_capture_id = ?, credits_awarded = ?, updated_at = NOW() WHERE id = ?');
                        $stmt->execute(['paid', $completed_capture['id'], $credits, $payment['id']]);
                        
                        // Add credits to user account
                        $stmt = $pdo->prepare('UPDATE users SET credits_balance = credits_balance + ? WHERE id = ?');
                        $stmt->execute([$credits, $payment['user_id']]);
                        
                        // Log the credit addition
                        $stmt = $pdo->prepare('INSERT INTO credit_ledger (user_id, delta, reason, reference_table, reference_id) VALUES (?, ?, ?, ?, ?)');
                        $stmt->execute([$payment['user_id'], $credits, 'payment_auto', 'payments', $payment['id']]);
                        
                        echo "<p>✅ <strong>Awarded $credits credits to user!</strong></p>";
                        echo "<p>✅ <strong>Payment status updated to 'paid'</strong></p>";
                        
                        $processed++;
                        
                        // Get updated user credits
                        $stmt = $pdo->prepare('SELECT credits_balance FROM users WHERE id = ?');
                        $stmt->execute([$payment['user_id']]);
                        $user = $stmt->fetch();
                        echo "<p>✅ <strong>New credit balance: " . $user['credits_balance'] . " credits</strong></p>";
                        
                    } else {
                        echo "<p>❌ <strong>No completed capture found</strong></p>";
                        $errors++;
                    }
                } else {
                    echo "<p>❌ <strong>No captures found in order</strong></p>";
                    $errors++;
                }
                
            } else {
                echo "<p>⏳ <strong>Order status: " . $order['status'] . " (not completed yet)</strong></p>";
            }
            
        } catch (Exception $e) {
            echo "<p>❌ <strong>Error processing payment:</strong> " . $e->getMessage() . "</p>";
            $errors++;
        }
        
        echo "<hr>";
    }
    
    echo "<h2>Summary</h2>";
    echo "<p>✅ <strong>Processed:</strong> $processed payments</p>";
    echo "<p>❌ <strong>Errors:</strong> $errors payments</p>";
    echo "<p>⏳ <strong>Still pending:</strong> " . (count($pending_payments) - $processed) . " payments</p>";
    
} catch (Exception $e) {
    echo "<p>❌ <strong>Error:</strong> " . $e->getMessage() . "</p>";
    echo "<p><strong>File:</strong> " . $e->getFile() . "</p>";
    echo "<p><strong>Line:</strong> " . $e->getLine() . "</p>";
}

echo "<hr>";
echo "<p><a href='payments.php'>Go to Payments Page</a></p>";
echo "<p><a href='test_webhook_events.php'>Check Webhook Events</a></p>";
?>
