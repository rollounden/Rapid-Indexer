<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/src/Db.php';

// Set content type to JSON
header('Content-Type: application/json');

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

// Get the raw POST data
$payload = file_get_contents('php://input');
$headers = getallheaders();

// Verify webhook signature (for production, you should implement proper signature verification)
// For now, we'll log the webhook for debugging
$log_data = [
    'timestamp' => date('Y-m-d H:i:s'),
    'headers' => $headers,
    'payload' => $payload
];

// Log the webhook
file_put_contents(__DIR__ . '/storage/logs/paypal_webhooks.log', 
    json_encode($log_data, JSON_PRETTY_PRINT) . "\n\n", 
    FILE_APPEND | LOCK_EX);

try {
    $data = json_decode($payload, true);
    
    if (!$data) {
        throw new Exception('Invalid JSON payload');
    }
    
    // Connect to database
    $pdo = Db::conn();
    
    // Process different event types
    $event_type = $data['event_type'] ?? 'unknown';
    
    switch ($event_type) {
        case 'PAYMENT.CAPTURE.COMPLETED':
            handlePaymentCompleted($pdo, $data);
            break;
            
        case 'PAYMENT.CAPTURE.DENIED':
        case 'PAYMENT.CAPTURE.DECLINED':
            handlePaymentDenied($pdo, $data);
            break;
            
        case 'PAYMENT.CAPTURE.REFUNDED':
            handlePaymentRefunded($pdo, $data);
            break;
            
        case 'PAYMENT.CAPTURE.PENDING':
            handlePaymentPending($pdo, $data);
            break;
            
        case 'PAYMENT.CAPTURE.REVERSED':
            handlePaymentReversed($pdo, $data);
            break;
            
        case 'CHECKOUT.ORDER.APPROVED':
            handleOrderApproved($pdo, $data);
            break;
            
        default:
            // Log unknown event types
            $stmt = $pdo->prepare('INSERT INTO api_logs (endpoint, request_data, response_data, status) VALUES (?, ?, ?, ?)');
            $stmt->execute([
                'paypal_webhook',
                $payload,
                json_encode(['status' => 'unknown_event_type', 'event_type' => $event_type]),
                'info'
            ]);
            break;
    }
    
    // Return success response
    http_response_code(200);
    echo json_encode(['status' => 'success', 'message' => 'Webhook processed']);
    
} catch (Exception $e) {
    // Log error
    error_log("PayPal Webhook Error: " . $e->getMessage());
    
    // Return error response
    http_response_code(500);
    echo json_encode(['error' => 'Internal server error']);
}

function handlePaymentCompleted($pdo, $data) {
    $payment_id = $data['resource']['id'] ?? null;
    $amount = $data['resource']['amount']['value'] ?? 0;
    $currency = $data['resource']['amount']['currency_code'] ?? 'USD';
    $custom_id = $data['resource']['custom_id'] ?? null;
    
    if (!$payment_id || !$custom_id) {
        throw new Exception('Missing payment ID or custom ID');
    }
    
    // Get user ID from custom_id (assuming custom_id contains user_id)
    $user_id = $custom_id;
    
    // Calculate credits based on amount
    $credits_amount = intval($amount / PRICE_PER_CREDIT_USD);
    
    // Find the payment record by PayPal order ID or capture ID
    $stmt = $pdo->prepare('SELECT id FROM payments WHERE paypal_order_id = ? OR paypal_capture_id = ?');
    $stmt->execute([$custom_id, $payment_id]);
    $payment_record = $stmt->fetch();
    
    if ($payment_record) {
        // Update existing payment record
        $stmt = $pdo->prepare('UPDATE payments SET status = ?, paypal_capture_id = ?, credits_awarded = ?, updated_at = NOW() WHERE id = ?');
        $stmt->execute(['paid', $payment_id, $credits_amount, $payment_record['id']]);
        
        // Add credits to user account using CreditsService
        require_once __DIR__ . '/src/CreditsService.php';
        CreditsService::adjust($user_id, $credits_amount, 'payment', 'payments', $payment_record['id']);
    } else {
        // Create new payment record if not found
        $stmt = $pdo->prepare('INSERT INTO payments (user_id, amount, currency, method, paypal_capture_id, credits_awarded, status, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), NOW())');
        $stmt->execute([$user_id, $amount, $currency, 'paypal', $payment_id, $credits_amount, 'paid']);
        
        // Add credits to user account
        $stmt = $pdo->prepare('UPDATE users SET credits_balance = credits_balance + ? WHERE id = ?');
        $stmt->execute([$credits_amount, $user_id]);
        
        // Log the credit addition
        $stmt = $pdo->prepare('INSERT INTO credit_ledger (user_id, delta, reason, reference_table, reference_id) VALUES (?, ?, ?, ?, ?)');
        $stmt->execute([$user_id, $credits_amount, 'payment', 'payments', $pdo->lastInsertId()]);
    }
    
    // Log the webhook
    $stmt = $pdo->prepare('INSERT INTO api_logs (endpoint, request_data, response_data, status) VALUES (?, ?, ?, ?)');
    $stmt->execute([
        'paypal_webhook_payment_completed',
        json_encode($data),
        json_encode(['status' => 'success', 'credits_added' => $credits_amount]),
        'success'
    ]);
}

function handlePaymentDenied($pdo, $data) {
    $payment_id = $data['resource']['id'] ?? null;
    
    if ($payment_id) {
        // Update payment status
        $stmt = $pdo->prepare('UPDATE payments SET status = ?, updated_at = NOW() WHERE paypal_capture_id = ?');
        $stmt->execute(['failed', $payment_id]);
        
        // Log the webhook
        $stmt = $pdo->prepare('INSERT INTO api_logs (endpoint, request_data, response_data, status) VALUES (?, ?, ?, ?)');
        $stmt->execute([
            'paypal_webhook_payment_denied',
            json_encode($data),
            json_encode(['status' => 'denied']),
            'error'
        ]);
    }
}

function handlePaymentRefunded($pdo, $data) {
    $payment_id = $data['resource']['id'] ?? null;
    $refund_amount = $data['resource']['amount']['value'] ?? 0;
    
    if ($payment_id) {
        // Update payment status
        $stmt = $pdo->prepare('UPDATE payments SET status = ?, updated_at = NOW() WHERE paypal_capture_id = ?');
        $stmt->execute(['refunded', $payment_id]);
        
        // Get the original payment to calculate credits to deduct
        $stmt = $pdo->prepare('SELECT user_id, credits_awarded FROM payments WHERE paypal_capture_id = ?');
        $stmt->execute([$payment_id]);
        $payment = $stmt->fetch();
        
        if ($payment) {
            // Calculate credits to deduct based on refund amount
            $credits_to_deduct = intval($refund_amount / PRICE_PER_CREDIT_USD);
            
            // Deduct credits from user account
            $stmt = $pdo->prepare('UPDATE users SET credits_balance = GREATEST(0, credits_balance - ?) WHERE id = ?');
            $stmt->execute([$credits_to_deduct, $payment['user_id']]);
            
            // Log the credit deduction
            $stmt = $pdo->prepare('INSERT INTO credit_ledger (user_id, delta, reason, reference_table, reference_id) VALUES (?, ?, ?, ?, ?)');
            $stmt->execute([$payment['user_id'], -$credits_to_deduct, 'payment', 'payments', $payment_id]);
        }
        
        // Log the webhook
        $stmt = $pdo->prepare('INSERT INTO api_logs (endpoint, request_data, response_data, status) VALUES (?, ?, ?, ?)');
        $stmt->execute([
            'paypal_webhook_payment_refunded',
            json_encode($data),
            json_encode(['status' => 'refunded', 'credits_deducted' => $credits_to_deduct ?? 0]),
            'info'
        ]);
    }
}

function handlePaymentPending($pdo, $data) {
    $payment_id = $data['resource']['id'] ?? null;
    
    if ($payment_id) {
        // Update payment status to pending
        $stmt = $pdo->prepare('UPDATE payments SET status = ?, updated_at = NOW() WHERE paypal_capture_id = ?');
        $stmt->execute(['pending', $payment_id]);
        
        // Log the webhook
        $stmt = $pdo->prepare('INSERT INTO api_logs (endpoint, request_data, response_data, status) VALUES (?, ?, ?, ?)');
        $stmt->execute([
            'paypal_webhook_payment_pending',
            json_encode($data),
            json_encode(['status' => 'pending']),
            'info'
        ]);
    }
}

function handlePaymentReversed($pdo, $data) {
    $payment_id = $data['resource']['id'] ?? null;
    
    if ($payment_id) {
        // Update payment status to reversed
        $stmt = $pdo->prepare('UPDATE payments SET status = ?, updated_at = NOW() WHERE paypal_capture_id = ?');
        $stmt->execute(['reversed', $payment_id]);
        
        // Get the original payment to calculate credits to deduct
        $stmt = $pdo->prepare('SELECT user_id, credits_awarded FROM payments WHERE paypal_capture_id = ?');
        $stmt->execute([$payment_id]);
        $payment = $stmt->fetch();
        
        if ($payment && $payment['credits_awarded'] > 0) {
            // Deduct credits from user account
            $stmt = $pdo->prepare('UPDATE users SET credits_balance = GREATEST(0, credits_balance - ?) WHERE id = ?');
            $stmt->execute([$payment['credits_awarded'], $payment['user_id']]);
            
            // Log the credit deduction
            $stmt = $pdo->prepare('INSERT INTO credit_ledger (user_id, delta, reason, reference_table, reference_id) VALUES (?, ?, ?, ?, ?)');
            $stmt->execute([$payment['user_id'], -$payment['credits_awarded'], 'payment', 'payments', $payment_id]);
        }
        
        // Log the webhook
        $stmt = $pdo->prepare('INSERT INTO api_logs (endpoint, request_data, response_data, status) VALUES (?, ?, ?, ?)');
        $stmt->execute([
            'paypal_webhook_payment_reversed',
            json_encode($data),
            json_encode(['status' => 'reversed', 'credits_deducted' => $payment['credits_awarded'] ?? 0]),
            'error'
        ]);
    }
}

function handleOrderApproved($pdo, $data) {
    $order_id = $data['resource']['id'] ?? null;
    
    if ($order_id) {
        // Log the order approval
        $stmt = $pdo->prepare('INSERT INTO api_logs (endpoint, request_data, response_data, status) VALUES (?, ?, ?, ?)');
        $stmt->execute([
            'paypal_webhook_order_approved',
            json_encode($data),
            json_encode(['status' => 'approved']),
            'info'
        ]);
    }
}
?>
