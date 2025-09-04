<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../src/Db.php';
require_once __DIR__ . '/../src/PaymentService.php';

header('Content-Type: application/json');
session_start();

try {
    $userId = $_SESSION['uid'] ?? null;
    if (!$userId) { throw new Exception('Unauthorized'); }

    $amount = floatval($_POST['amount'] ?? $_GET['amount'] ?? 0);
    $currency = $_POST['currency'] ?? $_GET['currency'] ?? 'USD';
    if ($amount <= 0) { throw new Exception('Invalid amount'); }

    $paymentId = PaymentService::recordPending(intval($userId), $amount, $currency, null);

    // Return a payload the frontend can use with PayPal SDK
    // custom_id will be attached to the order capture after approval on the client
    echo json_encode([
        'ok' => true,
        'payment_id' => $paymentId,
        'custom_id' => $paymentId . ':' . intval($userId),
        'amount' => $amount,
        'currency' => $currency,
    ]);
} catch (Throwable $e) {
    http_response_code(400);
    echo json_encode(['error' => $e->getMessage()]);
}


