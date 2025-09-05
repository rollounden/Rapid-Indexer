<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/src/Db.php';
require_once __DIR__ . '/src/PayPalService.php';

echo "<h1>PayPal Order Status Check</h1>";

try {
    $pdo = Db::conn();
    
    // Get the most recent pending payment
    $stmt = $pdo->prepare('SELECT * FROM payments WHERE status = ? AND paypal_order_id IS NOT NULL ORDER BY created_at DESC LIMIT 1');
    $stmt->execute(['pending']);
    $payment = $stmt->fetch();
    
    if (!$payment) {
        echo "<p>No pending payments found.</p>";
        exit;
    }
    
    echo "<h2>Checking Payment #" . $payment['id'] . "</h2>";
    echo "<p><strong>Amount:</strong> $" . $payment['amount'] . "</p>";
    echo "<p><strong>Order ID:</strong> " . $payment['paypal_order_id'] . "</p>";
    echo "<p><strong>Created:</strong> " . $payment['created_at'] . "</p>";
    
    $paypal = new PayPalService();
    
    echo "<h3>1. Getting Order Details from PayPal</h3>";
    $order = $paypal->getOrder($payment['paypal_order_id']);
    
    echo "<p><strong>Order Status:</strong> " . $order['status'] . "</p>";
    echo "<p><strong>Order ID:</strong> " . $order['id'] . "</p>";
    
    if (isset($order['purchase_units'][0]['payments'])) {
        $payments = $order['purchase_units'][0]['payments'];
        echo "<h3>2. Payment Details</h3>";
        
        if (isset($payments['captures'])) {
            echo "<p><strong>Captures Found:</strong> " . count($payments['captures']) . "</p>";
            foreach ($payments['captures'] as $capture) {
                echo "<p><strong>Capture ID:</strong> " . $capture['id'] . "</p>";
                echo "<p><strong>Capture Status:</strong> " . $capture['status'] . "</p>";
                echo "<p><strong>Amount:</strong> $" . $capture['amount']['value'] . " " . $capture['amount']['currency_code'] . "</p>";
                
                if ($capture['status'] === 'COMPLETED') {
                    echo "<p>✅ <strong>Payment is completed!</strong></p>";
                    
                    // Update payment status
                    $credits = intval($payment['amount'] / PRICE_PER_CREDIT_USD);
                    
                    $stmt = $pdo->prepare('UPDATE payments SET status = ?, paypal_capture_id = ?, credits_awarded = ?, updated_at = NOW() WHERE id = ?');
                    $stmt->execute(['paid', $capture['id'], $credits, $payment['id']]);
                    
                    // Add credits to user account
                    $stmt = $pdo->prepare('UPDATE users SET credits_balance = credits_balance + ? WHERE id = ?');
                    $stmt->execute([$credits, $payment['user_id']]);
                    
                    // Log the credit addition
                    $stmt = $pdo->prepare('INSERT INTO credit_ledger (user_id, delta, reason, reference_table, reference_id) VALUES (?, ?, ?, ?, ?)');
                    $stmt->execute([$payment['user_id'], $credits, 'payment', 'payments', $payment['id']]);
                    
                    echo "<p>✅ <strong>Awarded $credits credits to user!</strong></p>";
                }
            }
        } else {
            echo "<p>❌ No captures found</p>";
        }
        
        if (isset($payments['authorizations'])) {
            echo "<p><strong>Authorizations Found:</strong> " . count($payments['authorizations']) . "</p>";
            foreach ($payments['authorizations'] as $auth) {
                echo "<p><strong>Auth ID:</strong> " . $auth['id'] . "</p>";
                echo "<p><strong>Auth Status:</strong> " . $auth['status'] . "</p>";
            }
        }
    } else {
        echo "<p>❌ No payment information found in order</p>";
    }
    
    echo "<h3>3. Full Order Response</h3>";
    echo "<pre style='background: #f5f5f5; padding: 10px; border: 1px solid #ddd; max-height: 400px; overflow-y: auto;'>";
    echo htmlspecialchars(json_encode($order, JSON_PRETTY_PRINT));
    echo "</pre>";
    
} catch (Exception $e) {
    echo "<p>❌ Error: " . $e->getMessage() . "</p>";
    echo "<p>File: " . $e->getFile() . "</p>";
    echo "<p>Line: " . $e->getLine() . "</p>";
}

echo "<hr>";
echo "<p><a href='payments.php'>Go to Payments Page</a></p>";
?>
