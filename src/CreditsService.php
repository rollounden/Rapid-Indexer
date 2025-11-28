<?php

require_once __DIR__ . '/Db.php';
require_once __DIR__ . '/SettingsService.php';

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
        $inTransaction = $pdo->inTransaction();
        
        if (!$inTransaction) {
            $pdo->beginTransaction();
        }
        
        try {
            $stmt = $pdo->prepare('UPDATE users SET credits_balance = credits_balance + ? WHERE id = ?');
            $stmt->execute([$delta, $userId]);

            $stmt = $pdo->prepare('INSERT INTO credit_ledger (user_id, delta, reason, reference_table, reference_id) VALUES (?, ?, ?, ?, ?)');
            $stmt->execute([$userId, $delta, $reason, $referenceTable, $referenceId]);

            if (!$inTransaction) {
                $pdo->commit();
            }
        } catch (Throwable $e) {
            if (!$inTransaction) {
                $pdo->rollBack();
            }
            throw $e;
        }
    }

    public static function reserveForTask(int $userId, int $numUrls, bool $vip, string $type = 'indexer'): void
    {
        // Determine cost based on type
        $cost_indexing = (int)SettingsService::get('cost_indexing', (string)DEFAULT_COST_INDEXING);
        $cost_checking = (int)SettingsService::get('cost_checking', (string)DEFAULT_COST_CHECKING);
        $cost_vip_extra = (int)SettingsService::get('cost_vip', (string)DEFAULT_COST_VIP_EXTRA);

        $baseCost = ($type === 'checker') ? $cost_checking : $cost_indexing;
        $vipCost = $vip ? $cost_vip_extra : 0;
        
        $totalPerUrl = $baseCost + $vipCost;
        $required = $totalPerUrl * $numUrls;

        $pdo = Db::conn();
        $inTransaction = $pdo->inTransaction();

        if (!$inTransaction) {
            $pdo->beginTransaction();
        }

        try {
            $stmt = $pdo->prepare('SELECT credits_balance FROM users WHERE id = ? FOR UPDATE');
            $stmt->execute([$userId]);
            $row = $stmt->fetch();
            $balance = intval($row['credits_balance'] ?? 0);
            if ($balance < $required) {
                throw new Exception("Insufficient credits. Required: $required, Available: $balance");
            }
            $stmt = $pdo->prepare('UPDATE users SET credits_balance = credits_balance - ? WHERE id = ?');
            $stmt->execute([$required, $userId]);

            $stmt = $pdo->prepare('INSERT INTO credit_ledger (user_id, delta, reason) VALUES (?, ?, ?)');
            $stmt->execute([$userId, -$required, 'task_deduction']);

            if (!$inTransaction) {
                $pdo->commit();
            }
        } catch (Throwable $e) {
            if (!$inTransaction) {
                $pdo->rollBack();
            }
            throw $e;
        }
    }
}


