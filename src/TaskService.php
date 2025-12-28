<?php

require_once __DIR__ . '/Db.php';
require_once __DIR__ . '/CreditsService.php';
require_once __DIR__ . '/SpeedyIndexClient.php';
require_once __DIR__ . '/RalfyIndexClient.php';
require_once __DIR__ . '/RocketIndexerClient.php';
require_once __DIR__ . '/SettingsService.php';

class TaskService
{
    public static function createTask(int $userId, string $engine, string $type, array $urls, ?string $title, bool $vip, ?array $dripConfig = null): array
    {
        if (count($urls) === 0) { throw new Exception('No URLs provided'); }
        if (!in_array($engine, ['google','yandex'], true)) { throw new Exception('Invalid engine'); }
        if (!in_array($type, ['indexer','checker'], true)) { throw new Exception('Invalid type'); }

        // Determine provider
        $provider = SettingsService::get('indexing_provider', 'speedyindex');
        
        // Check for VIP override
        $useRocketVip = SettingsService::get('use_rocket_for_vip', '0') === '1';
        if ($vip && $useRocketVip && $type === 'indexer') {
            $provider = 'rocketindexer';
        }

        if ($type === 'checker') {
            $provider = 'speedyindex';
        }

        // Secondary provider doesn't support Yandex
        if ($provider === 'ralfy' && $engine === 'yandex') {
            throw new Exception('Yandex is not supported for this operation. Please use Google.');
        }

        CreditsService::reserveForTask($userId, count($urls), $vip, $type);

        $pdo = Db::conn();
        $pdo->beginTransaction();
        try {
            // Drip Feed Logic
            $isDrip = ($dripConfig !== null && isset($dripConfig['duration_days']));
            $dripPercentage = 100;
            $dripInterval = 1440;
            $nextRun = null;
            $status = 'pending';

            if ($isDrip && $type === 'indexer') { // Only indexer supports drip
                $durationDays = (int)$dripConfig['duration_days'];
                if ($durationDays < 1) $durationDays = 1;
                
                // Strategy: 12 batches per day (Every 2 hours)
                $batchesPerDay = 12;
                $totalBatches = $durationDays * $batchesPerDay;
                $dripPercentage = (int)ceil(100 / $totalBatches);
                $dripInterval = 120; // 2 hours
                $nextRun = date('Y-m-d H:i:s'); // Start immediately
            } else {
                $isDrip = false; 
            }

            // Insert task with provider and drip options
            $stmt = $pdo->prepare('INSERT INTO tasks (user_id, type, search_engine, title, vip, status, provider, is_drip_feed, drip_percentage, drip_interval_minutes, next_run_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
            $stmt->execute([
                $userId, 
                $type, 
                $engine, 
                $title, 
                $vip ? 1 : 0, 
                $status, 
                $provider,
                $isDrip ? 1 : 0,
                $isDrip ? $dripPercentage : null,
                $isDrip ? $dripInterval : null,
                $nextRun
            ]);
            $taskId = intval($pdo->lastInsertId());

            $insertLink = $pdo->prepare('INSERT INTO task_links (task_id, url, status) VALUES (?, ?, ?)');
            foreach ($urls as $url) {
                $insertLink->execute([$taskId, $url, 'pending']);
            }

            // If Drip Feed, we stop here. The background worker will pick it up.
            if ($isDrip) {
                $pdo->commit();
                return ['task_id' => $taskId, 'is_drip_feed' => true, 'provider' => $provider];
            }

            if ($provider === 'rocketindexer') {
                $apiKey = SettingsService::getDecrypted('rocket_api_key');
                if (!$apiKey) {
                    throw new Exception('Service configuration error (API key missing)');
                }
                $client = new RocketIndexerClient($apiKey, $userId);

                // Process URLs
                // We'll mark the task as "processing" and let a background worker handle status checks,
                // OR just submit them all now if fast.
                
                foreach ($urls as $url) {
                    $res = $client->submitUrl($url);
                    $body = json_decode($res['body'] ?? '', true);
                    
                    // Store tracking ID for this specific URL in task_links
                    // API returns tracking_ids array in data object
                    $trackingId = null;
                    if (isset($body['data']['tracking_ids']) && is_array($body['data']['tracking_ids']) && !empty($body['data']['tracking_ids'])) {
                        $trackingId = $body['data']['tracking_ids'][0];
                    }

                    $success = $body['success'] ?? false;
                    $status = $success ? 'indexed' : 'error';
                    $error = $body['message'] ?? null;
                    
                    $linkStmt = $pdo->prepare('UPDATE task_links SET status = ?, result_data = ?, error_code = ? WHERE task_id = ? AND url = ?');
                    $resultData = $trackingId ? json_encode(['tracking_id' => $trackingId, 'provider' => 'rocketindexer']) : null;
                    $linkStmt->execute([$status, $resultData, $error ? 0 : null, $taskId, $url]);
                }

                // Mark task as completed since we submitted everything
                $stmt = $pdo->prepare('UPDATE tasks SET status = "completed", completed_at = NOW(), provider = "rocketindexer" WHERE id = ?');
                $stmt->execute([$taskId]);

                $pdo->commit();
                return ['task_id' => $taskId, 'provider' => 'rocketindexer'];

            } elseif ($provider === 'ralfy') {
                $apiKey = SettingsService::getDecrypted('ralfy_api_key');
                if (!$apiKey) {
                    throw new Exception('Service configuration error (API key missing)');
                }
                $client = new RalfyIndexClient($apiKey, $userId);
                
                // Some providers don't support VIP queue explicitly in the same way 
                // and 'engine' parameter might not be used in project endpoint, only URLs.
                // We'll just send URLs.
                
                $projectName = $title ? preg_replace('/[^a-zA-Z0-9 _.-]/', '', $title) : null;
                $api = $client->createProject($urls, $projectName);
                
                // Check response
                $body = json_decode($api['body'] ?? '', true) ?: [];
                
                if (($api['httpCode'] ?? 500) >= 400 || isset($body['errorCode'])) {
                    $msg = $body['message'] ?? 'Unknown error';
                    throw new Exception("Indexing Error: $msg");
                }

                // Success - fire and forget
                // Mark task as completed immediately
                $stmt = $pdo->prepare('UPDATE tasks SET status = ?, completed_at = NOW() WHERE id = ?');
                $stmt->execute(['completed', $taskId]);

                // Mark links as indexed (submitted)
                $stmt = $pdo->prepare('UPDATE task_links SET status = ?, result_data = ?, checked_at = NOW() WHERE task_id = ?');
                $stmt->execute(['indexed', json_encode(['status' => 'submitted_to_provider']), $taskId]);
                
                // We don't have a remote task ID to store for this provider.
                
                $pdo->commit();
                return ['task_id' => $taskId, 'provider' => 'ralfy'];

            } else {
                // Default Provider
                $client = new SpeedyIndexClient(SPEEDYINDEX_BASE_URL, SPEEDYINDEX_API_KEY, $userId);
                $api = $client->createTask($engine, $type, $urls, $title);
                $body = json_decode($api['body'] ?? '', true) ?: [];
                $siTaskId = $body['task_id'] ?? $body['taskId'] ?? null;
                
                // Handle provider error if task_id is missing
                if (!$siTaskId) { 
                    $errorMsg = $body['message'] ?? 'Unknown error';
                    // Check for common error patterns and genericize if needed
                    // Usually "Invalid URL", "Not enough credits" (on provider side)
                    // If provider side balance issue, we should not show that to user if possible, or show "Service temporarily unavailable".
                    
                    if (stripos($errorMsg, 'balance') !== false || stripos($errorMsg, 'funds') !== false) {
                        $errorMsg = 'Service temporarily unavailable';
                    }
                    
                    // Log the full error internally
                    if (class_exists('ApiLogger')) {
                        // Assuming ApiLogger has a static method for generic logging or we rely on the client log
                        // But client log only logs requests. Let's log to error log.
                        error_log("Task Creation Failed: " . json_encode($body));
                    }
                    
                    throw new Exception("Indexing Error: $errorMsg");
                }

                $stmt = $pdo->prepare('UPDATE tasks SET speedyindex_task_id = ?, provider_task_id = ?, status = ? WHERE id = ?');
                $stmt->execute([$siTaskId, $siTaskId, 'processing', $taskId]);

                $pdo->commit();
                return ['task_id' => $taskId, 'speedyindex_task_id' => $siTaskId, 'provider' => 'speedyindex'];
            }
        } catch (Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    public static function syncTaskStatus(int $userId, int $taskId): array
    {
        $pdo = Db::conn();
        $stmt = $pdo->prepare('SELECT * FROM tasks WHERE id = ? AND user_id = ? LIMIT 1');
        $stmt->execute([$taskId, $userId]);
        $task = $stmt->fetch();
        if (!$task) { throw new Exception('Task not found'); }
        
        // Handle Drip Feed Tasks
        if (!empty($task['is_drip_feed'])) {
            // Drip feed tasks don't have a single provider ID and status is managed by auto_drip_feed.php
            // However, we can update the progress based on link status
            $pendingCountStmt = $pdo->prepare('SELECT COUNT(*) FROM task_links WHERE task_id = ? AND status = ?');
            $pendingCountStmt->execute([$taskId, 'pending']);
            $pendingCount = (int) $pendingCountStmt->fetchColumn();
            
            // If no pending links, mark as completed if not already
            if ($pendingCount === 0 && $task['status'] !== 'completed') {
                 $pdo->prepare('UPDATE tasks SET status = "completed", completed_at = NOW() WHERE id = ?')
                    ->execute([$taskId]);
                 return ['ok' => true, 'updated' => 0, 'pending' => 0, 'status' => 'completed', 'msg' => 'Drip feed complete'];
            }
            
            // If still pending, just return status
            return ['ok' => true, 'updated' => 0, 'pending' => $pendingCount, 'status' => $task['status'], 'msg' => 'Drip feed active'];
        }

        if (!$task['speedyindex_task_id']) { throw new Exception('Remote task ID missing'); }

        $client = new SpeedyIndexClient(SPEEDYINDEX_BASE_URL, SPEEDYINDEX_API_KEY, $userId);
        $report = $client->fullReport($task['search_engine'], $task['type'], $task['speedyindex_task_id']);
        $payload = json_decode($report['body'] ?? '', true) ?: [];

        // Parse Provider API response structure
        $result = $payload['result'] ?? [];
        $indexed_links = $result['indexed_links'] ?? [];
        $unindexed_links = $result['unindexed_links'] ?? [];
        
        $update = $pdo->prepare('UPDATE task_links SET status = ?, result_data = ?, checked_at = NOW(), error_code = ? WHERE task_id = ? AND url = ?');
        
        // Process indexed links
        foreach ($indexed_links as $item) {
            $url = $item['url'] ?? null;
            if (!$url) { continue; }
            $update->execute(['indexed', json_encode($item), null, $taskId, $url]);
        }
        
        // Process unindexed links
        foreach ($unindexed_links as $item) {
            $url = $item['url'] ?? null;
            if (!$url) { continue; }
            $error_code = isset($item['error_code']) ? intval($item['error_code']) : null;
            $update->execute(['unindexed', json_encode($item), $error_code, $taskId, $url]);
        }
        
        $total_processed = count($indexed_links) + count($unindexed_links);

        // Determine if there are any pending links left for this task
        $pendingCountStmt = $pdo->prepare('SELECT COUNT(*) FROM task_links WHERE task_id = ? AND status = ?');
        $pendingCountStmt->execute([$taskId, 'pending']);
        $pendingCount = (int) $pendingCountStmt->fetchColumn();

        $newStatus = $pendingCount === 0 ? 'completed' : 'processing';
        $pdo->prepare('UPDATE tasks SET status = ?, completed_at = CASE WHEN ? = "completed" THEN NOW() ELSE completed_at END WHERE id = ?')
            ->execute([$newStatus, $newStatus, $taskId]);

        return ['ok' => true, 'updated' => $total_processed, 'pending' => $pendingCount, 'status' => $newStatus];
    }

    public static function exportTaskCsv(int $userId, int $taskId): string
    {
        $pdo = Db::conn();
        $stmt = $pdo->prepare('SELECT id FROM tasks WHERE id = ? AND user_id = ? LIMIT 1');
        $stmt->execute([$taskId, $userId]);
        if (!$stmt->fetch()) { throw new Exception('Task not found'); }

        $q = $pdo->prepare('SELECT url, status, error_code FROM task_links WHERE task_id = ? ORDER BY id');
        $q->execute([$taskId]);
        $rows = $q->fetchAll();

        $fh = fopen('php://temp', 'w+');
        fputcsv($fh, ['url', 'status', 'error_code']);
        foreach ($rows as $r) {
            fputcsv($fh, [$r['url'], $r['status'], $r['error_code']]);
        }
        rewind($fh);
        $csv = stream_get_contents($fh);
        fclose($fh);
        return $csv;
    }

    public static function processDripFeedBatch(int $taskId): array
    {
        $pdo = Db::conn();
        $stmt = $pdo->prepare("SELECT * FROM tasks WHERE id = ?");
        $stmt->execute([$taskId]);
        $task = $stmt->fetch();

        if (!$task) throw new Exception("Task not found");
        if (!$task['is_drip_feed']) throw new Exception("Not a drip feed task");

        // Get total links count
        $countStmt = $pdo->prepare("SELECT COUNT(*) FROM task_links WHERE task_id = ?");
        $countStmt->execute([$taskId]);
        $totalLinks = (int)$countStmt->fetchColumn();
        
        // Get pending links
        $pendingStmt = $pdo->prepare("SELECT id, url FROM task_links WHERE task_id = ? AND status = 'pending' LIMIT 10000");
        $pendingStmt->execute([$taskId]);
        $pendingLinks = $pendingStmt->fetchAll();
        $pendingCount = count($pendingLinks);
        
        if ($pendingCount === 0) {
            $pdo->prepare("UPDATE tasks SET status = 'completed', completed_at = NOW(), next_run_at = NULL WHERE id = ?")->execute([$taskId]);
            return ['status' => 'completed', 'message' => 'No pending links. Task marked completed.'];
        }
        
        // Calculate batch size
        $percentage = $task['drip_percentage'] ?: 10;
        if ($percentage <= 0) $percentage = 10;
        
        $batchSize = (int)ceil($totalLinks * ($percentage / 100));
        if ($batchSize < 1) $batchSize = 1;
        
        // Take the batch
        $batchLinks = array_slice($pendingLinks, 0, $batchSize);
        $batchUrls = array_column($batchLinks, 'url');
        $batchLinkIds = array_column($batchLinks, 'id');
        
        // Submit to provider
        $provider = $task['provider'];
        $providerTaskId = null;
        $providerBatchId = null;
        
        if ($provider === 'ralfy') {
            $apiKey = SettingsService::getDecrypted('ralfy_api_key');
            if (!$apiKey) throw new Exception('Service configuration error (API key missing)');
            
            $client = new RalfyIndexClient($apiKey, $task['user_id']);
            $projectName = $task['title'] ? preg_replace('/[^a-zA-Z0-9 _.-]/', '', $task['title']) . " (Batch)" : null;
            
            $api = $client->createProject($batchUrls, $projectName);
            $body = json_decode($api['body'] ?? '', true) ?: [];
             
            if (($api['httpCode'] ?? 500) >= 400 || isset($body['errorCode'])) {
                throw new Exception("Indexing Error: " . ($body['message'] ?? 'Unknown'));
            }
        } else {
            // Default Provider
            $client = new SpeedyIndexClient(SPEEDYINDEX_BASE_URL, SPEEDYINDEX_API_KEY, $task['user_id']);
            $api = $client->createTask($task['search_engine'], $task['type'], $batchUrls, $task['title'] . " (Batch)");
            $body = json_decode($api['body'] ?? '', true) ?: [];
            
            $providerTaskId = $body['task_id'] ?? $body['taskId'] ?? null;
            if (!$providerTaskId) {
                $errorMsg = $body['message'] ?? 'Unknown error';
                if (stripos($errorMsg, 'balance') !== false || stripos($errorMsg, 'funds') !== false) {
                    $errorMsg = 'Service temporarily unavailable';
                }
                throw new Exception("Indexing Error: $errorMsg");
            }
            
            $providerBatchId = $providerTaskId;
        }
        
        // Transaction to update DB
        $pdo->beginTransaction();
        
        try {
            // Create Batch Record
            $batchStmt = $pdo->prepare("INSERT INTO task_batches (task_id, provider_batch_id, link_count, status) VALUES (?, ?, ?, 'submitted')");
            $batchStmt->execute([$taskId, $providerBatchId, count($batchUrls)]);
            $batchId = $pdo->lastInsertId();
            
            // Update Links
            $linkIdsStr = implode(',', array_map('intval', $batchLinkIds));
            $pdo->exec("UPDATE task_links SET status = 'indexed', batch_id = $batchId, checked_at = NOW() WHERE id IN ($linkIdsStr)");
            
            // Update Task Next Run
            $interval = $task['drip_interval_minutes'] ?: 1440;
            $pdo->prepare("UPDATE tasks SET status = 'processing', next_run_at = DATE_ADD(NOW(), INTERVAL ? MINUTE) WHERE id = ?")
                ->execute([$interval, $taskId]);
                
            $pdo->commit();
            return ['status' => 'success', 'count' => count($batchUrls), 'batch_id' => $batchId];
            
        } catch (Exception $e) {
            $pdo->rollBack();
            throw $e;
        }
    }
}


