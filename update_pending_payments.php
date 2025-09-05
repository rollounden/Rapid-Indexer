<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/src/Db.php';

echo "<h1>Update Pending Payments</h1>";

try {
    $pdo = Db::conn();
    
    // Get all pending payments
    $stmt = $pdo->prepare('SELECT * FROM payments WHERE status = ? ORDER BY created_at DESC');
    $stmt->execute(['pending']);
    $pending_payments = $stmt->fetchAll();
    
    echo "<h2>Pending Payments Found: " . count($pending_payments) . "</h2>";
    
    if (empty($pending_payments)) {
        echo "<p>No pending payments found.</p>";
    } else {
        echo "<table border='1'>";
        echo "<tr><th>ID</th><th>User ID</th><th>Amount</th><th>Created</th><th>Action</th></tr>";
        
        foreach ($pending_payments as $payment) {
            echo "<tr>";
            echo "<td>" . $payment['id'] . "</td>";
            echo "<td>" . $payment['user_id'] . "</td>";
            echo "<td>$" . $payment['amount'] . "</td>";
            echo "<td>" . $payment['created_at'] . "</td>";
            echo "<td>";
            
            // Calculate credits
            $credits = intval($payment['amount'] / PRICE_PER_CREDIT_USD);
            
            // Check if payment is older than 30 minutes (likely abandoned)
            $created_time = strtotime($payment['created_at']);
            $current_time = time();
            $age_minutes = ($current_time - $created_time) / 60;
            
            if ($age_minutes > 30) {
                echo "<a href='?action=cancel&id=" . $payment['id'] . "'>Cancel</a> ";
                echo "<small>(" . round($age_minutes) . " min old)</small>";
            } else {
                echo "<small>Too recent (" . round($age_minutes) . " min old)</small>";
            }
            
            echo "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    // Handle actions
    if (isset($_GET['action']) && isset($_GET['id'])) {
        $action = $_GET['action'];
        $payment_id = $_GET['id'];
        
        if ($action === 'cancel') {
            $stmt = $pdo->prepare('UPDATE payments SET status = ?, updated_at = NOW() WHERE id = ?');
            $stmt->execute(['cancelled', $payment_id]);
            echo "<p>✅ Payment #$payment_id cancelled</p>";
            echo "<script>setTimeout(function(){ window.location.href = 'update_pending_payments.php'; }, 2000);</script>";
        }
    }
    
    echo "<h2>Current User Credits:</h2>";
    $stmt = $pdo->prepare('SELECT id, username, credits_balance FROM users ORDER BY id');
    $stmt->execute();
    $users = $stmt->fetchAll();
    
    echo "<table border='1'>";
    echo "<tr><th>User ID</th><th>Username</th><th>Credits</th></tr>";
    foreach ($users as $user) {
        echo "<tr>";
        echo "<td>" . $user['id'] . "</td>";
        echo "<td>" . $user['username'] . "</td>";
        echo "<td>" . $user['credits_balance'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
} catch (Exception $e) {
    echo "<p>❌ Error: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<p><a href='payments.php'>Go to Payments Page</a></p>";
echo "<p><a href='webhook_status.php'>Check Webhook Status</a></p>";
?>
