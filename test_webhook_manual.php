<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/src/Db.php';

echo "<h1>Manual Webhook Test</h1>";

// Test webhook data
$test_payload = [
    'id' => 'WH-TEST-123',
    'event_type' => 'PAYMENT.CAPTURE.COMPLETED',
    'resource' => [
        'id' => 'test_capture_123',
        'amount' => [
            'value' => '1.00',
            'currency_code' => 'USD'
        ],
        'custom_id' => '1', // user_id
        'status' => 'COMPLETED'
    ]
];

echo "<h2>Test Payload:</h2>";
echo "<pre>" . json_encode($test_payload, JSON_PRETTY_PRINT) . "</pre>";

try {
    // Connect to database
    $pdo = Db::conn();
    
    // Simulate webhook processing
    echo "<h2>Processing Webhook...</h2>";
    
    $data = $test_payload;
    $event_type = $data['event_type'] ?? 'unknown';
    
    echo "<p>Event Type: $event_type</p>";
    
    switch ($event_type) {
        case 'PAYMENT.CAPTURE.COMPLETED':
            echo "<p>Processing PAYMENT.CAPTURE.COMPLETED...</p>";
            
            $payment_id = $data['resource']['id'] ?? null;
            $amount = $data['resource']['amount']['value'] ?? 0;
            $currency = $data['resource']['amount']['currency_code'] ?? 'USD';
            $custom_id = $data['resource']['custom_id'] ?? null;
            
            echo "<p>Payment ID: $payment_id</p>";
            echo "<p>Amount: $amount $currency</p>";
            echo "<p>Custom ID: $custom_id</p>";
            
            if (!$payment_id) {
                throw new Exception('Missing payment ID');
            }
            
            $user_id = $custom_id;
            $credits_amount = intval($amount / PRICE_PER_CREDIT_USD);
            
            echo "<p>User ID: $user_id</p>";
            echo "<p>Credits to award: $credits_amount</p>";
            
            // Check if payment already exists
            $stmt = $pdo->prepare('SELECT id FROM payments WHERE paypal_capture_id = ?');
            $stmt->execute([$payment_id]);
            $existing_payment = $stmt->fetch();
            
            if ($existing_payment) {
                echo "<p>✅ Payment already exists, updating...</p>";
                $stmt = $pdo->prepare('UPDATE payments SET status = ?, paypal_capture_id = ?, credits_awarded = ?, updated_at = NOW() WHERE id = ?');
                $stmt->execute(['paid', $payment_id, $credits_amount, $existing_payment['id']]);
            } else {
                echo "<p>✅ Creating new payment record...</p>";
                $stmt = $pdo->prepare('INSERT INTO payments (user_id, amount, currency, method, paypal_capture_id, credits_awarded, status, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), NOW())');
                $stmt->execute([$user_id, $amount, $currency, 'paypal', $payment_id, $credits_amount, 'paid']);
            }
            
            // Add credits to user account
            echo "<p>✅ Adding credits to user account...</p>";
            $stmt = $pdo->prepare('UPDATE users SET credits_balance = credits_balance + ? WHERE id = ?');
            $stmt->execute([$credits_amount, $user_id]);
            
            // Log the credit addition
            echo "<p>✅ Logging credit addition...</p>";
            $stmt = $pdo->prepare('INSERT INTO credit_ledger (user_id, delta, reason, reference_table, reference_id) VALUES (?, ?, ?, ?, ?)');
            $stmt->execute([$user_id, $credits_amount, 'payment', 'payments', $payment_id]);
            
            echo "<p><strong>✅ Webhook processed successfully!</strong></p>";
            break;
            
        default:
            echo "<p>❌ Unknown event type: $event_type</p>";
            break;
    }
    
    // Check current user credits
    $stmt = $pdo->prepare('SELECT credits_balance FROM users WHERE id = ?');
    $stmt->execute([$user_id]);
    $current_credits = $stmt->fetchColumn();
    
    echo "<h2>Current User Credits: $current_credits</h2>";
    
    // Check payment history
    $stmt = $pdo->prepare('SELECT * FROM payments WHERE user_id = ? ORDER BY created_at DESC LIMIT 5');
    $stmt->execute([$user_id]);
    $payments = $stmt->fetchAll();
    
    echo "<h2>Recent Payments:</h2>";
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
    echo "<h2>❌ Error:</h2>";
    echo "<p>" . $e->getMessage() . "</p>";
    echo "<p>File: " . $e->getFile() . "</p>";
    echo "<p>Line: " . $e->getLine() . "</p>";
}

echo "<hr>";
echo "<p><a href='payments.php'>Go to Payments Page</a></p>";
echo "<p><a href='test_webhook.php'>Go to Webhook Test</a></p>";
?>
