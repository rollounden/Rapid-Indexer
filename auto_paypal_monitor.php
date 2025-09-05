<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/src/Db.php';
require_once __DIR__ . '/src/PayPalService.php';

// This script can be run automatically via cron job
// Run every 5 minutes to check for completed payments

try {
    $pdo = Db::conn();
    $paypal = new PayPalService();
    
    // Get pending payments older than 2 minutes (to avoid checking too recent ones)
    $stmt = $pdo->prepare('SELECT * FROM payments WHERE status = ? AND paypal_order_id IS NOT NULL AND created_at < DATE_SUB(NOW(), INTERVAL 2 MINUTE) ORDER BY created_at DESC');
    $stmt->execute(['pending']);
    $pending_payments = $stmt->fetchAll();
    
    if (empty($pending_payments)) {
        // No pending payments to check
        exit;
    }
    
    foreach ($pending_payments as $payment) {
        try {
            // Get order details from PayPal
            $order = $paypal->getOrder($payment['paypal_order_id']);
            
            if ($order && $order['status'] === 'COMPLETED') {
                // Check for completed captures
                if (isset($order['purchase_units'][0]['payments']['captures'])) {
                    $captures = $order['purchase_units'][0]['payments']['captures'];
                    
                    foreach ($captures as $capture) {
                        if ($capture['status'] === 'COMPLETED') {
                            // Calculate credits
                            $credits = intval($payment['amount'] / PRICE_PER_CREDIT_USD);
                            
                            // Update payment status
                            $stmt = $pdo->prepare('UPDATE payments SET status = ?, paypal_capture_id = ?, credits_awarded = ?, updated_at = NOW() WHERE id = ?');
                            $stmt->execute(['paid', $capture['id'], $credits, $payment['id']]);
                            
                            // Add credits to user account
                            $stmt = $pdo->prepare('UPDATE users SET credits_balance = credits_balance + ? WHERE id = ?');
                            $stmt->execute([$credits, $payment['user_id']]);
                            
                            // Log the credit addition
                            $stmt = $pdo->prepare('INSERT INTO credit_ledger (user_id, delta, reason, reference_table, reference_id) VALUES (?, ?, ?, ?, ?)');
                            $stmt->execute([$payment['user_id'], $credits, 'payment_auto', 'payments', $payment['id']]);
                            
                            // Log the successful processing
                            error_log("Auto-processed payment #{$payment['id']} - Awarded {$credits} credits to user #{$payment['user_id']}");
                            
                            break; // Only process the first completed capture
                        }
                    }
                }
            }
            
        } catch (Exception $e) {
            error_log("Error processing payment #{$payment['id']}: " . $e->getMessage());
        }
    }
    
} catch (Exception $e) {
    error_log("PayPal monitor error: " . $e->getMessage());
}
?>
