<?php

require_once __DIR__ . '/Db.php';

class CreditsService
{
    public static function getBalance(int $userId): int
    {
        $pdo = Db::conn();
        $stmt = $pdo->prepare('SELECT credits_balance FROM users WHERE id = ?');
        $stmt->execute([$userId]);
        $row = $stmt->fetch();
        return intval($row['credits_balance'] ?? 0);
    }

    public static function adjust(int $userId, int $delta, string $reason, ?string $referenceTable = null, ?int $referenceId = null): void
    {
        $pdo = Db::conn();
        $pdo->beginTransaction();
        try {
            $stmt = $pdo->prepare('UPDATE users SET credits_balance = credits_balance + ? WHERE id = ?');
            $stmt->execute([$delta, $userId]);

            $stmt = $pdo->prepare('INSERT INTO credit_ledger (user_id, delta, reason, reference_table, reference_id) VALUES (?, ?, ?, ?, ?)');
            $stmt->execute([$userId, $delta, $reason, $referenceTable, $referenceId]);

            $pdo->commit();
        } catch (Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    public static function reserveForTask(int $userId, int $numUrls, bool $vip): void
    {
        $required = (CREDITS_PER_URL * $numUrls) + ($vip ? VIP_EXTRA_CREDITS_PER_URL * $numUrls : 0);
        $pdo = Db::conn();
        $pdo->beginTransaction();
        try {
            $stmt = $pdo->prepare('SELECT credits_balance FROM users WHERE id = ? FOR UPDATE');
            $stmt->execute([$userId]);
            $row = $stmt->fetch();
            $balance = intval($row['credits_balance'] ?? 0);
            if ($balance < $required) {
                throw new Exception('Insufficient credits');
            }
            $stmt = $pdo->prepare('UPDATE users SET credits_balance = credits_balance - ? WHERE id = ?');
            $stmt->execute([$required, $userId]);

            $stmt = $pdo->prepare('INSERT INTO credit_ledger (user_id, delta, reason) VALUES (?, ?, ?)');
            $stmt->execute([$userId, -$required, 'task_deduction']);

            $pdo->commit();
        } catch (Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }
}


