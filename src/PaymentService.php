<?php

require_once __DIR__ . '/Db.php';
require_once __DIR__ . '/CreditsService.php';

class PaymentService
{
    public static function recordPending(int $userId, float $amount, string $currency, ?string $paypalOrderId = null): int
    {
        $pdo = Db::conn();
        $stmt = $pdo->prepare('INSERT INTO payments (user_id, amount, currency, method, paypal_order_id, status) VALUES (?, ?, ?, ?, ?, ?)');
        $stmt->execute([$userId, $amount, $currency, 'paypal', $paypalOrderId, 'pending']);
        return intval($pdo->lastInsertId());
    }

    public static function markPaid(int $paymentId, int $userId, string $paypalCaptureId, float $amount, string $currency): void
    {
        $credits = (int) floor(($amount / (float) PRICE_PER_CREDIT_USD));
        $pdo = Db::conn();
        $pdo->beginTransaction();
        try {
            $stmt = $pdo->prepare('UPDATE payments SET status = ?, paypal_capture_id = ?, amount = ?, currency = ?, credits_awarded = ? WHERE id = ?');
            $stmt->execute(['paid', $paypalCaptureId, $amount, $currency, $credits, $paymentId]);

            CreditsService::adjust($userId, $credits, 'payment', 'payments', $paymentId);

            $pdo->commit();
        } catch (Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }
}


