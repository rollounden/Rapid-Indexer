<?php
require_once __DIR__ . '/config/config.php';
session_start();

// Simple routing for backward compatibility
$path = $_SERVER['REQUEST_URI'] ?? '/';
$path = parse_url($path, PHP_URL_PATH);
$path = rtrim($path, '/');
if (empty($path)) $path = '/';

// Route handling
switch ($path) {
    case '/':
        // Serve dashboard if logged in, otherwise serve home page
        if (isset($_SESSION['uid'])) {
            header('Location: /dashboard');
            exit;
        } else {
            // Include home page directly
            require_once __DIR__ . '/home.php';
            exit;
        }
        break;

    case '/auth':
        // Redirect to login for backward compatibility
        header('Location: /login.php');
        exit;
        break;

    case '/tasks':
        // Redirect to new tasks page
        header('Location: /tasks.php');
        exit;
        break;

    case '/payments':
        // Redirect to new payments page
        header('Location: /payments.php');
        exit;
        break;

    case '/admin':
        // Redirect to new admin page
        header('Location: /admin.php');
        exit;
        break;

    case '/webhook_paypal':
        // Keep webhook endpoint as is
require_once __DIR__ . '/src/Db.php';
        require_once __DIR__ . '/src/PaymentService.php';
        
        // Read raw body and headers
        $raw = file_get_contents('php://input');
        $headers = function_exists('getallheaders') ? getallheaders() : [];

        http_response_code(200);

        try {
            $event = json_decode($raw, true);
            if (!$event) { throw new Exception('Invalid JSON'); }

            $pdo = Db::conn();
            // Idempotency by event ID
            $externalId = $event['id'] ?? null;
            if (!$externalId) { throw new Exception('Missing event id'); }

            $stmt = $pdo->prepare('SELECT id, status FROM webhook_events WHERE external_event_id = ?');
            $stmt->execute([$externalId]);
            $row = $stmt->fetch();
            if ($row && in_array($row['status'], ['processed','ignored'], true)) {
                echo 'ok';
                exit;
            }

            if (!$row) {
                $stmt = $pdo->prepare('INSERT INTO webhook_events (provider, external_event_id, event_type, payload, status) VALUES (?, ?, ?, ?, ?)');
                $stmt->execute(['paypal', $externalId, $event['event_type'] ?? null, json_encode($event), 'received']);
            }

            // Basic event handling for capture completed
            $type = $event['event_type'] ?? '';
            if ($type === 'PAYMENT.CAPTURE.COMPLETED') {
                $resource = $event['resource'] ?? [];
                $captureId = $resource['id'] ?? null;
                $amount = isset($resource['amount']['value']) ? floatval($resource['amount']['value']) : 0.0;
                $currency = $resource['amount']['currency_code'] ?? 'USD';

                // Expect we stored paymentId and userId somewhere (custom_id or metadata)
                $customId = $resource['custom_id'] ?? null; // format: paymentId:userId
                if ($customId && strpos($customId, ':') !== false) {
                    [$paymentId, $userId] = array_map('intval', explode(':', $customId, 2));
                    PaymentService::markPaid($paymentId, $userId, $captureId, $amount, $currency);
                }

                $pdo->prepare('UPDATE webhook_events SET status = ?, processed_at = NOW() WHERE external_event_id = ?')->execute(['processed', $externalId]);
                echo 'ok';
                exit;
            }

            // Other events ignored for now
            $pdo->prepare('UPDATE webhook_events SET status = ?, processed_at = NOW() WHERE external_event_id = ?')->execute(['ignored', $externalId]);
            echo 'ok';
        } catch (Throwable $e) {
            try {
                $pdo = Db::conn();
                $pdo->prepare('UPDATE webhook_events SET status = ?, last_error = ?, delivery_attempts = delivery_attempts + 1 WHERE external_event_id = ?')->execute(['error', $e->getMessage(), $externalId ?? '']);
            } catch (Throwable $e2) {
                // swallow
            }
            http_response_code(500);
            echo 'error';
        }
        break;

    default:
        http_response_code(404);
        echo '404 Not Found';
        break;
}
