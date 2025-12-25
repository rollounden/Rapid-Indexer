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
            require_once __DIR__ . '/dashboard.php';
        } else {
            require_once __DIR__ . '/home.php';
        }
        break;

    case '/dashboard':
        require_once __DIR__ . '/dashboard.php';
        break;

    case '/login':
    case '/auth':
        require_once __DIR__ . '/login.php';
        break;

    case '/register':
        require_once __DIR__ . '/register.php';
        break;

    case '/forgot-password':
    case '/forgot_password':
        require_once __DIR__ . '/forgot_password.php';
        break;

    case '/reset-password':
    case '/reset_password':
        require_once __DIR__ . '/reset_password.php';
        break;

    case '/logout':
        require_once __DIR__ . '/logout.php';
        break;

    case '/tasks':
        require_once __DIR__ . '/tasks.php';
        break;

    case '/task_details':
        require_once __DIR__ . '/task_details.php';
        break;

    case '/task_results':
        require_once __DIR__ . '/task_results.php';
        break;

    case '/traffic':
        require_once __DIR__ . '/traffic.php';
        break;

    case '/payments':
        require_once __DIR__ . '/payments.php';
        break;

    case '/admin':
        require_once __DIR__ . '/admin.php';
        break;

    case '/faq':
        require_once __DIR__ . '/faq.php';
        break;

    case '/contact':
        require_once __DIR__ . '/contact.php';
        break;

    case '/terms':
        require_once __DIR__ . '/terms.php';
        break;

    case '/privacy':
        require_once __DIR__ . '/privacy.php';
        break;
        
    case '/api_access':
        require_once __DIR__ . '/api_access.php';
        break;
        
    case '/viral-blast':
        require_once __DIR__ . '/viral-blast.php';
        break;

    case '/chrome-extension':
        require_once __DIR__ . '/chrome-extension.php';
        break;

    case '/refund':
        require_once __DIR__ . '/refund.php';
        break;

    case '/payment_success':
        require_once __DIR__ . '/payment_success.php';
        break;

    case '/payment_cancel':
        require_once __DIR__ . '/payment_cancel.php';
        break;

    // Admin Pages
    case '/admin_users':
        require_once __DIR__ . '/admin_users.php';
        break;
    case '/admin_payments':
        require_once __DIR__ . '/admin_payments.php';
        break;
    case '/admin_messages':
        require_once __DIR__ . '/admin_messages.php';
        break;
    case '/admin_email':
        require_once __DIR__ . '/admin_email.php';
        break;
    case '/admin_discounts':
        require_once __DIR__ . '/admin_discounts.php';
        break;
    case '/admin_traffic':
        require_once __DIR__ . '/admin_traffic.php';
        break;
    case '/admin_settings':
        require_once __DIR__ . '/admin_settings.php';
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
        // Try to serve static assets or 404
        if (file_exists(__DIR__ . $path) && !is_dir(__DIR__ . $path)) {
             // Let the web server handle it? 
             // If we are here, it means the request was rewritten to index.php or handled by it.
             // But usually static files are served directly by nginx/apache.
             // If this is a PHP CLI server or similar configuration:
             return false; 
        }
        
        http_response_code(404);
        require_once __DIR__ . '/404.php';
        break;
}
