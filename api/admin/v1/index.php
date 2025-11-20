<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../../config/config.php';
require_once __DIR__ . '/../../../src/Db.php';
require_once __DIR__ . '/../../../src/UserService.php';
require_once __DIR__ . '/../../../src/CreditsService.php';

// 1. Authentication
$headers = getallheaders();
$apiKey = $headers['X-Admin-Key'] ?? $_SERVER['HTTP_X_ADMIN_KEY'] ?? $_GET['api_key'] ?? null;

// Check against environment variable or config constant
// Force reload from .env just in case config.php loaded it before it was populated in some environments
if (!isset($_ENV['ADMIN_API_KEY']) && file_exists(__DIR__ . '/../../../.env')) {
    $lines = file(__DIR__ . '/../../../.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos($line, 'ADMIN_API_KEY=') === 0) {
            $_ENV['ADMIN_API_KEY'] = trim(substr($line, 14));
            break;
        }
    }
}

$validKey = $_ENV['ADMIN_API_KEY'] ?? (defined('ADMIN_API_KEY') ? ADMIN_API_KEY : null);

// DEBUG: Uncomment to debug key issues (DO NOT LEAVE IN PRODUCTION)
// error_log("Received Key: " . ($apiKey ? 'YES' : 'NO'));
// error_log("Valid Key Configured: " . ($validKey ? 'YES' : 'NO'));

if (!$validKey || $apiKey !== $validKey) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized. Invalid or missing X-Admin-Key.']);
    exit;
}

// 2. Router
$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? null;

// Read JSON input
$input = json_decode(file_get_contents('php://input'), true) ?? [];

try {
    switch ($action) {
        case 'create_user':
            if ($method !== 'POST') throw new Exception('Method not allowed', 405);
            
            $email = $input['email'] ?? null;
            $password = $input['password'] ?? null;
            
            $user = UserService::create($email, $password);
            echo json_encode(['success' => true, 'user' => $user]);
            break;

        case 'get_user':
            if ($method !== 'GET') throw new Exception('Method not allowed', 405);
            
            $email = $_GET['email'] ?? null;
            $id = $_GET['id'] ?? null;
            
            if ($id) {
                $user = UserService::getById((int)$id);
            } elseif ($email) {
                $user = UserService::getByEmail($email);
            } else {
                throw new Exception('Missing email or id parameter');
            }
            
            if (!$user) {
                http_response_code(404);
                echo json_encode(['error' => 'User not found']);
            } else {
                echo json_encode(['success' => true, 'user' => $user]);
            }
            break;

        case 'get_recent_payments':
            if ($method !== 'GET') throw new Exception('Method not allowed', 405);
            
            $limit = (int)($_GET['limit'] ?? 50);
            $payments = UserService::getRecentPayments($limit);
            echo json_encode(['success' => true, 'payments' => $payments]);
            break;

        case 'update_credits':
            if ($method !== 'POST') throw new Exception('Method not allowed', 405);
            
            $userId = $input['user_id'] ?? null;
            $delta = $input['delta'] ?? null; // Can be positive or negative
            $reason = $input['reason'] ?? 'admin_api_adjustment';
            
            if (!$userId || !is_numeric($delta)) {
                throw new Exception('Missing user_id or delta');
            }
            
            CreditsService::adjust((int)$userId, (int)$delta, $reason);
            $newBalance = CreditsService::getBalance((int)$userId);
            
            echo json_encode([
                'success' => true, 
                'message' => 'Credits updated', 
                'new_balance' => $newBalance
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
    echo json_encode(['error' => $e->getMessage()]);
}

