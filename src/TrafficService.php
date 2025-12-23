<?php

require_once __DIR__ . '/Db.php';
require_once __DIR__ . '/CreditsService.php';
require_once __DIR__ . '/SettingsService.php';
require_once __DIR__ . '/JustAnotherPanelClient.php';

class TrafficService
{
    // ... (previous methods remain, will be updated or kept)

    public static function createTrafficTask(int $userId, array $params): array
    {
        $pdo = Db::conn();
        
        // Params
        $link = $params['link'] ?? null;
        $mode = $params['mode'] ?? 'single'; // 'single' or 'campaign'
        
        if (!$link) { throw new Exception('Link is required'); }

        if ($mode === 'campaign') {
            return self::createCampaign($userId, $params, $pdo);
        } else {
            return self::createSingleOrder($userId, $params, $pdo);
        }
    }

    private static function createSingleOrder(int $userId, array $params, PDO $pdo): array
    {
        $quantity = intval($params['quantity'] ?? 0);
        if ($quantity < 100) { throw new Exception('Minimum quantity is 100'); }

        $pricePer1000 = floatval(SettingsService::get('traffic_price_per_1000', 60)); 
        $cost = ceil(($quantity / 1000) * $pricePer1000);
        
        CreditsService::deduct($userId, $cost, 'traffic_order');

        $pdo->beginTransaction();
        try {
            $apiKey = SettingsService::getDecrypted('jap_api_key');
            if (!$apiKey) { throw new Exception('Traffic service not configured'); }
            $client = new JustAnotherPanelClient('https://justanotherpanel.com/api/v2', $apiKey);
            $serviceId = SettingsService::get('traffic_service_id', '9184');

            $apiParams = [
                'service' => $serviceId,
                'link' => $params['link'],
                'quantity' => $quantity,
                'country' => $params['country'] ?? null,
            ];
            // Set default device if missing
            $apiParams['device'] = $params['device'] ?? '5';
            
            // ... (add other params like device, etc)
            self::enrichApiParams($apiParams, $params);

            $response = $client->addOrder($apiParams);
            if (isset($response['error']) || isset($response['body']['error'])) {
                throw new Exception("Provider Error: " . ($response['body']['error'] ?? $response['error']));
            }
            $orderId = $response['body']['order'] ?? null;

            $metaData = json_encode(array_merge($params, ['cost' => $cost]));

            $stmt = $pdo->prepare('INSERT INTO tasks (user_id, type, title, status, provider, provider_task_id, meta_data) VALUES (?, ?, ?, ?, ?, ?, ?)');
            $stmt->execute([$userId, 'traffic', "Traffic: $quantity to {$params['link']}", 'processing', 'justanotherpanel', $orderId, $metaData]);
            $taskId = $pdo->lastInsertId();
            
            $pdo->commit();
            return ['task_id' => $taskId, 'order_id' => $orderId];
        } catch (Throwable $e) {
            $pdo->rollBack();
            CreditsService::add($userId, $cost, 'task_refund', 'traffic_failed');
            throw $e;
        }
    }

    private static function createCampaign(int $userId, array $params, PDO $pdo): array
    {
        // Campaign Logic
        // Days: User defined
        // Range: 100-600 visitors per run (random)
        // Interval: 20m - 6h (random)
        
        $days = intval($params['days'] ?? 1);
        if ($days < 1) { throw new Exception('Minimum duration is 1 day'); }
        
        // Minimum recommended order size
        // User input quantity should be treated as minimum total
        // But we'll stick to the quantity provided by user for simplicity, 
        // or we can enforce a minimum in UI (already done: min 100).
        
        // Generate Schedule
        $schedule = [];
        $currentTime = time();
        $endTime = $currentTime + ($days * 24 * 3600);
        $totalQuantity = 0;
        $requestedQuantity = intval($params['quantity'] ?? 0);
        
        // We want to distribute requestedQuantity over the days in small bursts
        // Instead of random amount loops, let's work backwards from total quantity
        // But we also want "random intervals" and "random amounts".
        
        // Approach: 
        // 1. Calculate average runs needed based on avg burst size (~350)
        // 2. Distribute these runs over the time period
        
        $avgBurst = 350; // (100+600)/2
        $estimatedRuns = ceil($requestedQuantity / $avgBurst);
        
        // Ensure we have enough time
        $totalSeconds = $days * 24 * 3600;
        $avgInterval = $totalSeconds / $estimatedRuns;
        
        // Start first run almost immediately (30-90s delay)
        $nextRun = $currentTime + rand(30, 90); 
        $remainingQty = $requestedQuantity;
        
        while ($remainingQty > 0 && $nextRun < $endTime) {
            // Calculate burst size
            // Last run takes remaining
            if ($remainingQty < 600) {
                $qty = $remainingQty;
            } else {
                $qty = rand(100, 600);
            }
            
            // Safety check: don't go over remaining
            if ($qty > $remainingQty) $qty = $remainingQty;
            
            $schedule[] = [
                'time' => $nextRun,
                'qty' => $qty
            ];
            
            $remainingQty -= $qty;
            $totalQuantity += $qty; // This should equal requestedQuantity at end
            
            // Calculate next interval
            // We want some randomness around the average interval
            // Variation +/- 30%
            $variation = $avgInterval * 0.3;
            $interval = $avgInterval + rand(-$variation, $variation);
            if ($interval < 1200) $interval = 1200; // Minimum 20 mins
            
            $nextRun += $interval;
        }
        
        // If we ran out of time but still have quantity, dump it in the last slot or add a slot
        if ($remainingQty > 0) {
            if (!empty($schedule)) {
                $schedule[count($schedule)-1]['qty'] += $remainingQty;
                $totalQuantity += $remainingQty;
            } else {
                $schedule[] = ['time' => $currentTime + 600, 'qty' => $remainingQty];
                $totalQuantity += $remainingQty;
            }
        }
        
        // Calculate Cost
        $pricePer1000 = floatval(SettingsService::get('traffic_price_per_1000', 60)); 
        $cost = ceil(($totalQuantity / 1000) * $pricePer1000);

        CreditsService::deduct($userId, $cost, 'traffic_campaign');

        $pdo->beginTransaction();
        try {
            $metaData = json_encode(array_merge($params, [
                'cost' => $cost, 
                'total_quantity' => $totalQuantity, 
                'days' => $days,
                'runs_count' => count($schedule)
            ]));
            
            // Use user provided title or auto-generate
            if (!empty($params['task_name'])) {
                $title = $params['task_name'];
            } else {
                $title = "Viral Traffic: $totalQuantity visitors over $days days";
            }
            
            $stmt = $pdo->prepare('INSERT INTO tasks (user_id, type, title, status, provider, meta_data) VALUES (?, ?, ?, ?, ?, ?)');
            $stmt->execute([$userId, 'traffic_campaign', $title, 'processing', 'justanotherpanel', $metaData]);
            $taskId = $pdo->lastInsertId();

            $stmtSchedule = $pdo->prepare('INSERT INTO traffic_schedule (task_id, scheduled_at, quantity, status) VALUES (?, ?, ?, ?)');
            
            foreach ($schedule as $run) {
                $dateStr = date('Y-m-d H:i:s', $run['time']);
                $stmtSchedule->execute([$taskId, $dateStr, $run['qty'], 'pending']);
            }

            $pdo->commit();
            return ['task_id' => $taskId, 'total_quantity' => $totalQuantity, 'runs' => count($schedule)];
            
        } catch (Throwable $e) {
            $pdo->rollBack();
            CreditsService::add($userId, $cost, 'task_refund', 'campaign_failed');
            throw $e;
        }
    }

    private static function enrichApiParams(array &$apiParams, array $sourceParams)
    {
        if (!empty($sourceParams['device'])) {
            $apiParams['device'] = $sourceParams['device'];
        }
        // Ensure device is set if not already (redundant but safe)
        if (!isset($apiParams['device'])) {
            $apiParams['device'] = '5';
        }

        if (!empty($sourceParams['type_of_traffic'])) {
            $apiParams['type_of_traffic'] = $sourceParams['type_of_traffic'];
            
            if ($sourceParams['type_of_traffic'] == 1 && !empty($sourceParams['google_keyword'])) {
                $apiParams['google_keyword'] = $sourceParams['google_keyword'];
            }
            if ($sourceParams['type_of_traffic'] == 2 && !empty($sourceParams['referring_url'])) {
                $apiParams['referring_url'] = $sourceParams['referring_url'];
            }
        }
    }

    public static function processScheduledRuns()
    {
        $pdo = Db::conn();
        // Find pending runs that are due
        $stmt = $pdo->prepare("
            SELECT s.*, t.user_id, t.meta_data 
            FROM traffic_schedule s
            JOIN tasks t ON s.task_id = t.id
            WHERE s.status = 'pending' AND s.scheduled_at <= NOW()
            LIMIT 10
        ");
        $stmt->execute();
        $runs = $stmt->fetchAll();

        if (empty($runs)) return;

        $apiKey = SettingsService::getDecrypted('jap_api_key');
        if (!$apiKey) { return; } // Log error?
        
        $client = new JustAnotherPanelClient('https://justanotherpanel.com/api/v2', $apiKey);
        $serviceId = SettingsService::get('traffic_service_id', '9184');

        foreach ($runs as $run) {
            try {
                // Prepare params
                $meta = json_decode($run['meta_data'], true) ?? [];
                
                // Make sure we default quantity if something weird happened
                $quantity = (int)($run['quantity'] ?? 100);
                if ($quantity < 100) $quantity = 100; // JAP minimum usually

                $apiParams = [
                    'service' => $serviceId,
                    'link' => $meta['link'] ?? '', // Should be in meta
                    'quantity' => $quantity,
                    'country' => $meta['country'] ?? null,
                ];
                
                // Ensure run parameter is set if needed (though normally 1)
                // JAP "runs" usually means repeats, but here we are scheduling them ourselves.
                // However, the "runs" param might be mandatory or we might want to set it to 1 explicitly.
                // $apiParams['runs'] = 1; 
                // $apiParams['interval'] = 0;

                self::enrichApiParams($apiParams, $meta);

                if (empty($apiParams['link'])) {
                    throw new Exception("Missing link in task metadata");
                }

                $response = $client->addOrder($apiParams);
                
                if (isset($response['error']) || isset($response['body']['error'])) {
                    $msg = $response['body']['error'] ?? $response['error'];
                    throw new Exception($msg);
                }
                
                $orderId = $response['body']['order'] ?? null;
                if (!$orderId) throw new Exception("No order ID returned");

                // Update schedule item
                $update = $pdo->prepare("UPDATE traffic_schedule SET status = 'completed', provider_order_id = ?, execution_log = 'Success', updated_at = NOW() WHERE id = ?");
                $update->execute([$orderId, $run['id']]);

            } catch (Exception $e) {
                $log = "Error: " . $e->getMessage();
                $update = $pdo->prepare("UPDATE traffic_schedule SET status = 'failed', execution_log = ?, updated_at = NOW() WHERE id = ?");
                $update->execute([$log, $run['id']]);
            }
        }
        
        // Check for completed campaigns
        // A campaign is completed when all its schedule items are not pending
        // We can run a separate cleanup or check here. 
        // For simplicity, let's just leave the campaign as 'processing' until all are done.
        // We could update task status to 'completed' if all items are done.
    }

    public static function syncStatus(int $taskId)
    {
        // For campaigns, status is derived from schedule? 
        // Or we just leave it as processing until end date.
        // For single tasks, we check provider.
        
        $pdo = Db::conn();
        $stmt = $pdo->prepare('SELECT type, provider_task_id, status FROM tasks WHERE id = ?');
        $stmt->execute([$taskId]);
        $task = $stmt->fetch();

        if ($task['type'] === 'traffic' && $task['provider_task_id']) {
            // Same as before for single tasks
             $apiKey = SettingsService::getDecrypted('jap_api_key');
            if (!$apiKey) { return; }
            
            $client = new JustAnotherPanelClient('https://justanotherpanel.com/api/v2', $apiKey);
            $response = $client->getOrderStatus((int)$task['provider_task_id']);
            $body = $response['body'] ?? [];
            
             if (isset($body['status'])) {
                $statusMap = [
                    'Completed' => 'completed',
                    'Processing' => 'processing',
                    'In progress' => 'processing',
                    'Pending' => 'pending',
                    'Partial' => 'completed',
                    'Canceled' => 'failed',
                    'Refunded' => 'failed'
                ];
                $newStatus = $statusMap[$body['status']] ?? 'processing';
                if ($newStatus !== $task['status']) {
                    $pdo->prepare('UPDATE tasks SET status = ?, completed_at = CASE WHEN ? = "completed" THEN NOW() ELSE completed_at END WHERE id = ?')
                        ->execute([$newStatus, $newStatus, $taskId]);
                }
             }
        } elseif ($task['type'] === 'traffic_campaign') {
            // Check if all schedules are done
            $countStmt = $pdo->prepare("SELECT COUNT(*) FROM traffic_schedule WHERE task_id = ? AND status = 'pending'");
            $countStmt->execute([$taskId]);
            $pending = $countStmt->fetchColumn();
            
            if ($pending == 0 && $task['status'] !== 'completed') {
                 $pdo->prepare('UPDATE tasks SET status = "completed", completed_at = NOW() WHERE id = ?')
                    ->execute([$taskId]);
            }
        }
    }
}
