<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../src/Db.php';
require_once __DIR__ . '/../../src/UserService.php';
require_once __DIR__ . '/../../src/TaskService.php';
require_once __DIR__ . '/../../src/CreditsService.php';
require_once __DIR__ . '/../../src/SettingsService.php';

// 1. Authentication
$headers = getallheaders();
$apiKey = $headers['X-API-Key'] ?? $_SERVER['HTTP_X_API_KEY'] ?? $_GET['api_key'] ?? null;

if (!$apiKey) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized. Missing X-API-Key header.']);
    exit;
}

$user = UserService::getByApiKey($apiKey);

if (!$user) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized. Invalid API Key.']);
    exit;
}

if ($user['status'] !== 'active') {
    http_response_code(403);
    echo json_encode(['error' => 'Account suspended.']);
    exit;
}

$userId = (int)$user['id'];

// 2. Router
$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? null;
// Support PATH_INFO-like routing if action is not set? 
// For now, stick to ?action= for consistency with Admin API.

// Read JSON input
$input = json_decode(file_get_contents('php://input'), true) ?? [];

try {
    switch ($action) {
        case 'me':
            if ($method !== 'GET') throw new Exception('Method not allowed', 405);
            
            // Refresh user data to get latest credits
            $user = UserService::getById($userId);
            
            echo json_encode([
                'success' => true,
                'user' => [
                    'id' => $user['id'],
                    'email' => $user['email'],
                    'credits_balance' => (int)$user['credits_balance'],
                    'created_at' => $user['created_at']
                ]
            ]);
            break;

        case 'create_task':
            if ($method !== 'POST') throw new Exception('Method not allowed', 405);
            
            $type = $input['type'] ?? 'indexer';

            if ($type === 'traffic') {
                require_once __DIR__ . '/../../src/TrafficService.php';

                // Map inputs
                $link = $input['link'] ?? $input['url'] ?? null;
                // If user sent "urls" array, take first
                if (!$link && !empty($input['urls']) && is_array($input['urls'])) {
                    $link = $input['urls'][0] ?? null;
                }

                if (!$link) throw new Exception('Missing "link" or "url" parameter for traffic task');

                $params = [
                    'link' => $link,
                    'task_name' => $input['title'] ?? $input['task_name'] ?? null,
                    'quantity' => $input['quantity'] ?? 0,
                    'mode' => $input['mode'] ?? 'single',
                    'days' => $input['days'] ?? 1,
                    'country' => $input['country'] ?? 'WW',
                    'device' => $input['device'] ?? '',
                    'type_of_traffic' => $input['type_of_traffic'] ?? '',
                    'google_keyword' => $input['google_keyword'] ?? '',
                    'referring_url' => $input['referring_url'] ?? '',
                ];

                $result = TrafficService::createTrafficTask($userId, $params);

                echo json_encode([
                    'success' => true,
                    'message' => 'Traffic task created successfully',
                    'task_id' => $result['task_id'],
                    'provider_order_id' => $result['order_id'] ?? null,
                    'total_quantity' => $result['total_quantity'] ?? $params['quantity'],
                    'runs' => $result['runs'] ?? 1
                ]);

            } else {
                // Indexer / Checker
                $urls = $input['urls'] ?? [];
                if (is_string($urls)) {
                    // Allow newline separated string too
                    $urls = array_values(array_filter(array_map('trim', preg_split('/\r?\n/', $urls))));
                }
                
                if (empty($urls) || !is_array($urls)) {
                    throw new Exception('No URLs provided. Send "urls" as array or newline-separated string.');
                }
                
                $engine = $input['engine'] ?? 'google';
                // Type is already read above
                $title = $input['title'] ?? null;
                $vip = filter_var($input['vip'] ?? false, FILTER_VALIDATE_BOOLEAN);
                
                // Drip Feed
                $dripFeed = filter_var($input['drip_feed'] ?? false, FILTER_VALIDATE_BOOLEAN);
                $dripDuration = isset($input['drip_duration_days']) ? (int)$input['drip_duration_days'] : 3;
                $dripConfig = $dripFeed ? ['duration_days' => $dripDuration] : null;
                
                // Check VIP setting
                $enable_vip_queue = SettingsService::get('enable_vip_queue', '1') === '1';
                if ($vip && !$enable_vip_queue) {
                    $vip = false;
                }
                
                $result = TaskService::createTask($userId, $engine, $type, $urls, $title, $vip, $dripConfig);
                
                echo json_encode([
                    'success' => true,
                    'message' => 'Task created successfully',
                    'task_id' => $result['task_id'],
                    'provider' => $result['provider'] ?? 'unknown',
                    'is_drip_feed' => $result['is_drip_feed'] ?? false
                ]);
            }
            break;

        case 'get_task':
            if ($method !== 'GET') throw new Exception('Method not allowed', 405);
            
            $taskId = $input['task_id'] ?? $_GET['task_id'] ?? null;
            if (!$taskId) throw new Exception('Missing task_id');
            
            // Sync status first (updates DB from provider)
            // Note: syncTaskStatus throws exception if task not found or not owned by user
            $syncResult = TaskService::syncTaskStatus($userId, (int)$taskId);
            
            // Fetch fresh details from DB including links if requested?
            // For now, return what sync returns plus basic info
            
             $pdo = Db::conn();
             $stmt = $pdo->prepare('SELECT * FROM tasks WHERE id = ? AND user_id = ? LIMIT 1');
             $stmt->execute([(int)$taskId, $userId]);
             $task = $stmt->fetch(PDO::FETCH_ASSOC);
             
             // Optional: Include links summary or details
             // For a simple API, maybe just summary stats
            
            echo json_encode([
                'success' => true,
                'task' => [
                    'id' => $task['id'],
                    'title' => $task['title'],
                    'type' => $task['type'],
                    'engine' => $task['search_engine'],
                    'status' => $task['status'],
                    'vip' => (bool)$task['vip'],
                    'progress' => [
                         'updated' => $syncResult['updated'],
                         'pending' => $syncResult['pending']
                    ],
                    'created_at' => $task['created_at'],
                    'completed_at' => $task['completed_at']
                ]
            ]);
            break;
            
        case 'get_task_links':
             if ($method !== 'GET') throw new Exception('Method not allowed', 405);
             
             $taskId = $input['task_id'] ?? $_GET['task_id'] ?? null;
             if (!$taskId) throw new Exception('Missing task_id');
             
             // Ensure ownership
             $pdo = Db::conn();
             $stmt = $pdo->prepare('SELECT id FROM tasks WHERE id = ? AND user_id = ?');
             $stmt->execute([(int)$taskId, $userId]);
             if (!$stmt->fetch()) throw new Exception('Task not found');
             
             $q = $pdo->prepare('SELECT url, status, error_code, checked_at FROM task_links WHERE task_id = ?');
             $q->execute([(int)$taskId]);
             $links = $q->fetchAll(PDO::FETCH_ASSOC);
             
             echo json_encode([
                 'success' => true,
                 'links' => $links
             ]);
             break;

        case 'health':
            echo json_encode(['status' => 'ok', 'version' => '1.0']);
            break;

        default:
            throw new Exception('Invalid action', 400);
    }

} catch (Exception $e) {
    $code = $e->getCode();
    if ($code < 100 || $code > 599) $code = 400;
    http_response_code($code);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}

