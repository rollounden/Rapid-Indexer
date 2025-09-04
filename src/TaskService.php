<?php

require_once __DIR__ . '/Db.php';
require_once __DIR__ . '/CreditsService.php';
require_once __DIR__ . '/SpeedyIndexClient.php';

class TaskService
{
    public static function createTask(int $userId, string $engine, string $type, array $urls, ?string $title, bool $vip): array
    {
        if (count($urls) === 0) { throw new Exception('No URLs provided'); }
        if (!in_array($engine, ['google','yandex'], true)) { throw new Exception('Invalid engine'); }
        if (!in_array($type, ['indexer','checker'], true)) { throw new Exception('Invalid type'); }

        CreditsService::reserveForTask($userId, count($urls), $vip);

        $pdo = Db::conn();
        $pdo->beginTransaction();
        try {
            $stmt = $pdo->prepare('INSERT INTO tasks (user_id, type, search_engine, title, vip, status) VALUES (?, ?, ?, ?, ?, ?)');
            $stmt->execute([$userId, $type, $engine, $title, $vip ? 1 : 0, 'pending']);
            $taskId = intval($pdo->lastInsertId());

            $insertLink = $pdo->prepare('INSERT INTO task_links (task_id, url, status) VALUES (?, ?, ?)');
            foreach ($urls as $url) {
                $insertLink->execute([$taskId, $url, 'pending']);
            }

            $client = new SpeedyIndexClient(SPEEDYINDEX_BASE_URL, SPEEDYINDEX_API_KEY, $userId);
            $api = $client->createTask($engine, $type, $urls, $title);
            $body = json_decode($api['body'] ?? '', true) ?: [];
            $siTaskId = $body['task_id'] ?? $body['taskId'] ?? null;
            if (!$siTaskId) { throw new Exception('Failed to create SpeedyIndex task'); }

            $stmt = $pdo->prepare('UPDATE tasks SET speedyindex_task_id = ?, status = ? WHERE id = ?');
            $stmt->execute([$siTaskId, 'processing', $taskId]);

            $pdo->commit();
            return ['task_id' => $taskId, 'speedyindex_task_id' => $siTaskId];
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

        // Expecting payload structure to include results per URL. This may need adaptation.
        $links = $payload['links'] ?? $payload['data']['links'] ?? [];
        $update = $pdo->prepare('UPDATE task_links SET status = ?, result_data = ?, checked_at = NOW(), error_code = ? WHERE task_id = ? AND url = ?');
        foreach ($links as $item) {
            $url = $item['url'] ?? null;
            if (!$url) { continue; }
            $status = $item['status'] ?? ($item['indexed'] ? 'indexed' : 'unindexed');
            $error = isset($item['error_code']) ? intval($item['error_code']) : null;
            $update->execute([$status, json_encode($item), $error, $taskId, $url]);
        }

        $pdo->prepare('UPDATE tasks SET status = ?, completed_at = CASE WHEN ? = "completed" THEN NOW() ELSE completed_at END WHERE id = ?')
            ->execute(['completed', 'completed', $taskId]);

        return ['ok' => true, 'updated' => count($links)];
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


