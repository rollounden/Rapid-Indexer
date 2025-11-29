<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/src/Db.php';
require_once __DIR__ . '/src/SpeedyIndexClient.php';
require_once __DIR__ . '/src/RalfyIndexClient.php';
require_once __DIR__ . '/src/SettingsService.php';

// Force include Db class if not already loaded
if (!class_exists('Db')) {
    require_once __DIR__ . '/src/Db.php';
}

// Set time limit to avoid timeouts
set_time_limit(600);

echo "Starting Drip Feed Worker...\n";

$pdo = Db::conn();

// Get tasks due for processing
$stmt = $pdo->prepare("
    SELECT * FROM tasks 
    WHERE is_drip_feed = 1 
    AND status IN ('pending', 'processing') 
    AND (next_run_at IS NULL OR next_run_at <= NOW())
    LIMIT 10
");
$stmt->execute();
$tasks = $stmt->fetchAll();

echo "Found " . count($tasks) . " tasks to process.\n";

foreach ($tasks as $task) {
    echo "Processing Task #{$task['id']}...\n";
    
    try {
        // Get total links count
        $countStmt = $pdo->prepare("SELECT COUNT(*) FROM task_links WHERE task_id = ?");
        $countStmt->execute([$task['id']]);
        $totalLinks = (int)$countStmt->fetchColumn();
        
        // Get pending links
        $pendingStmt = $pdo->prepare("SELECT id, url FROM task_links WHERE task_id = ? AND status = 'pending' LIMIT 10000");
        $pendingStmt->execute([$task['id']]);
        $pendingLinks = $pendingStmt->fetchAll();
        $pendingCount = count($pendingLinks);
        
        if ($pendingCount === 0) {
            echo "  No pending links. Marking completed.\n";
            $pdo->prepare("UPDATE tasks SET status = 'completed', completed_at = NOW(), next_run_at = NULL WHERE id = ?")->execute([$task['id']]);
            continue;
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
        
        echo "  Submitting batch of " . count($batchUrls) . " links.\n";
        
        // Submit to provider
        $provider = $task['provider'];
        $providerTaskId = null;
        $providerBatchId = null; // Ralfy doesn't return batch IDs usually, SpeedyIndex returns Task ID
        
        if ($provider === 'ralfy') {
            $apiKey = SettingsService::getDecrypted('ralfy_api_key');
            if (!$apiKey) throw new Exception('RalfyIndex API key missing');
            
            $client = new RalfyIndexClient($apiKey, $task['user_id']);
            $projectName = $task['title'] ? preg_replace('/[^a-zA-Z0-9 _.-]/', '', $task['title']) . " (Batch)" : null;
            
            $api = $client->createProject($batchUrls, $projectName);
            $body = json_decode($api['body'] ?? '', true) ?: [];
             
            if (($api['httpCode'] ?? 500) >= 400 || isset($body['errorCode'])) {
                throw new Exception("RalfyIndex Error: " . ($body['message'] ?? 'Unknown'));
            }
            // Ralfy doesn't return a specific ID we track, it's fire & forget
            
        } else {
            // SpeedyIndex
            $client = new SpeedyIndexClient(SPEEDYINDEX_BASE_URL, SPEEDYINDEX_API_KEY, $task['user_id']);
            $api = $client->createTask($task['search_engine'], $task['type'], $batchUrls, $task['title'] . " (Batch)");
            $body = json_decode($api['body'] ?? '', true) ?: [];
            
            $providerTaskId = $body['task_id'] ?? $body['taskId'] ?? null;
            if (!$providerTaskId) throw new Exception('Failed to create SpeedyIndex task');
            
            $providerBatchId = $providerTaskId;
        }
        
        // Transaction to update DB
        $pdo->beginTransaction();
        
        try {
            // Create Batch Record
            $batchStmt = $pdo->prepare("INSERT INTO task_batches (task_id, provider_batch_id, link_count, status) VALUES (?, ?, ?, 'submitted')");
            $batchStmt->execute([$task['id'], $providerBatchId, count($batchUrls)]);
            $batchId = $pdo->lastInsertId();
            
            // Update Links
            $linkIdsStr = implode(',', array_map('intval', $batchLinkIds));
            // Note: In a real app, use a loop or properly bind for safety, but ids are ints here.
            // Using status='indexed' to mean "submitted to indexer" as per TaskService convention
            $pdo->exec("UPDATE task_links SET status = 'indexed', batch_id = $batchId, checked_at = NOW() WHERE id IN ($linkIdsStr)");
            
            // Update Task Next Run
            $interval = $task['drip_interval_minutes'] ?: 1440;
            $pdo->prepare("UPDATE tasks SET status = 'processing', next_run_at = DATE_ADD(NOW(), INTERVAL ? MINUTE) WHERE id = ?")
                ->execute([$interval, $task['id']]);
                
            $pdo->commit();
            echo "  Batch submitted successfully.\n";
            
        } catch (Exception $e) {
            $pdo->rollBack();
            throw $e;
        }
        
    } catch (Exception $e) {
        echo "  Error processing task: " . $e->getMessage() . "\n";
        // Log error?
    }
}

echo "Done.\n";

