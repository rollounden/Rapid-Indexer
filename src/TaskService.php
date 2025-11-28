<?php

require_once __DIR__ . '/Db.php';
require_once __DIR__ . '/CreditsService.php';
require_once __DIR__ . '/SpeedyIndexClient.php';
require_once __DIR__ . '/RalfyIndexClient.php';
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
        if ($type === 'checker') {
            $provider = 'speedyindex';
        }

        // RalfyIndex doesn't support Yandex
        if ($provider === 'ralfy' && $engine === 'yandex') {
            throw new Exception('RalfyIndex does not support Yandex. Please use Google.');
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

            if ($provider === 'ralfy') {
                $apiKey = SettingsService::getDecrypted('ralfy_api_key');
                if (!$apiKey) {
                    throw new Exception('RalfyIndex API key not configured');
                }
                $client = new RalfyIndexClient($apiKey, $userId);
                
                // Ralfy doesn't support VIP queue explicitly in the same way (or maybe it does but no endpoint), 
                // and 'engine' parameter is not used in Ralfy /project endpoint, only URLs.
                // But we'll just send URLs.
                
                $projectName = $title ? preg_replace('/[^a-zA-Z0-9 _.-]/', '', $title) : null;
                $api = $client->createProject($urls, $projectName);
                
                // Check response
                $body = json_decode($api['body'] ?? '', true) ?: [];
                
                if (($api['httpCode'] ?? 500) >= 400 || isset($body['errorCode'])) {
                    $msg = $body['message'] ?? 'Unknown RalfyIndex error';
                    throw new Exception("RalfyIndex Error: $msg");
                }

                // Success - Ralfy is fire and forget
                // Mark task as completed immediately
                $stmt = $pdo->prepare('UPDATE tasks SET status = ?, completed_at = NOW() WHERE id = ?');
                $stmt->execute(['completed', $taskId]);

                // Mark links as indexed (submitted)
                $stmt = $pdo->prepare('UPDATE task_links SET status = ?, result_data = ?, checked_at = NOW() WHERE task_id = ?');
                $stmt->execute(['indexed', json_encode(['status' => 'submitted_to_ralfy']), $taskId]);
                
                // We don't have a remote task ID to store for Ralfy.
                
                $pdo->commit();
                return ['task_id' => $taskId, 'provider' => 'ralfy'];

            } else {
                // Default: SpeedyIndex
                $client = new SpeedyIndexClient(SPEEDYINDEX_BASE_URL, SPEEDYINDEX_API_KEY, $userId);
                $api = $client->createTask($engine, $type, $urls, $title);
                $body = json_decode($api['body'] ?? '', true) ?: [];
                $siTaskId = $body['task_id'] ?? $body['taskId'] ?? null;
                if (!$siTaskId) { throw new Exception('Failed to create SpeedyIndex task'); }

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
        if (!$task['speedyindex_task_id']) { throw new Exception('No provider task id'); }

        $client = new SpeedyIndexClient(SPEEDYINDEX_BASE_URL, SPEEDYINDEX_API_KEY, $userId);
        $report = $client->fullReport($task['search_engine'], $task['type'], $task['speedyindex_task_id']);
        $payload = json_decode($report['body'] ?? '', true) ?: [];

        // Parse SpeedyIndex API response structure
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
}


