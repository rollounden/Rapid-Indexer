<?php
require_once __DIR__ . '/config/config.php';
session_start();

// Simple routing
$path = $_SERVER['REQUEST_URI'] ?? '/';
$path = parse_url($path, PHP_URL_PATH);
$path = rtrim($path, '/');
if (empty($path)) $path = '/';

// Route handling
switch ($path) {
    case '/':
        require_once __DIR__ . '/src/Db.php';
        require_once __DIR__ . '/src/SpeedyIndexClient.php';
        require_once __DIR__ . '/src/ApiLogger.php';
        require_once __DIR__ . '/src/CreditsService.php';
        require_once __DIR__ . '/src/TaskService.php';
        
        $userId = $_SESSION['uid'] ?? null;
        $action = $_POST['action'] ?? $_GET['action'] ?? null;
        $result = null;
        $credits = $userId ? CreditsService::getBalance(intval($userId)) : null;

        function log_message(string $msg): void {
            $dir = dirname(LOG_FILE);
            if (!is_dir($dir)) { @mkdir($dir, 0777, true); }
            file_put_contents(LOG_FILE, date('c') . ' ' . $msg . PHP_EOL, FILE_APPEND);
        }

        try {
            if ($action === 'create_task') {
                $engine = $_POST['engine'] ?? 'google';
                $type = $_POST['type'] ?? 'indexer';
                $title = $_POST['title'] ?? null;
                $urlsRaw = $_POST['urls'] ?? '';
                $urls = array_values(array_filter(array_map('trim', preg_split('/\r?\n/', $urlsRaw))));
                $vip = isset($_POST['vip']) && $_POST['vip'] === '1';
                $created = TaskService::createTask(intval($userId), $engine, $type, $urls, $title, $vip);
                $result = ['httpCode' => 200, 'body' => json_encode(['ok' => true, 'task' => $created])];
                log_message('Task created: ' . ($title ?? 'no-title') . ' with ' . count($urls) . ' urls');
            } elseif ($action === 'list_tasks') {
                $engine = $_POST['engine'] ?? 'google';
                $page = intval($_POST['page'] ?? 0);
                $client = new SpeedyIndexClient(SPEEDYINDEX_BASE_URL, SPEEDYINDEX_API_KEY, $userId);
                $result = $client->listTasks($engine, $page);
                log_message("List tasks page=$page");
            }
        } catch (Throwable $e) {
            log_message('Error: ' . $e->getMessage());
            $result = ['httpCode' => 500, 'body' => json_encode(['error' => $e->getMessage()])];
        }
        
        // Dashboard HTML
        ?>
        <!doctype html>
        <html>
        <head>
            <meta charset="utf-8" />
            <title>Rapid-Indexer</title>
            <meta name="viewport" content="width=device-width, initial-scale=1" />
            <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" />
        </head>
        <body class="bg-light">
        <div class="container py-4">
            <h1 class="mb-4">Rapid-Indexer (MVP)</h1>

            <div class="mb-3 p-3 bg-white border rounded">
                <?php if ($userId): ?>
                <span class="me-2">Signed in (User ID: <?php echo htmlspecialchars(strval($userId)); ?>)</span>
                <form class="d-inline" method="post" action="/auth">
                    <input type="hidden" name="action" value="logout" />
                    <button class="btn btn-sm btn-outline-danger">Logout</button>
                </form>
                <div class="mt-2">
                    <a href="/tasks" class="btn btn-sm btn-outline-primary">My Tasks</a>
                    <a href="/payments" class="btn btn-sm btn-outline-secondary">Payments</a>
                    <?php if (($_SESSION['role'] ?? '') === 'admin'): ?>
                    <a href="/admin" class="btn btn-sm btn-outline-warning">Admin</a>
                    <?php endif; ?>
                </div>
                <?php else: ?>
                <form class="row g-2" method="post" action="/auth">
                    <input type="hidden" name="action" value="login" />
                    <div class="col-auto"><input class="form-control" name="email" placeholder="Email" /></div>
                    <div class="col-auto"><input class="form-control" type="password" name="password" placeholder="Password" /></div>
                    <div class="col-auto"><button class="btn btn-primary">Login</button></div>
                </form>
                <form class="row g-2 mt-2" method="post" action="/auth">
                    <input type="hidden" name="action" value="register" />
                    <div class="col-auto"><input class="form-control" name="email" placeholder="New Email" /></div>
                    <div class="col-auto"><input class="form-control" type="password" name="password" placeholder="New Password" /></div>
                    <div class="col-auto"><button class="btn btn-outline-secondary">Register</button></div>
                </form>
                <?php endif; ?>
            </div>

            <div class="row g-4">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">Internal Credits</div>
                        <div class="card-body">
                            <?php if ($userId): ?>
                            <p class="mb-2">Current balance: <strong><?php echo htmlspecialchars(strval($credits ?? 0)); ?></strong> credits</p>
                            <?php else: ?>
                            <p class="text-muted">Login to view your credits.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">Create Task</div>
                        <div class="card-body">
                            <form method="post">
                                <input type="hidden" name="action" value="create_task" />
                                <div class="mb-2">
                                    <label class="form-label">Search Engine</label>
                                    <select name="engine" class="form-select">
                                        <option value="google">Google</option>
                                        <option value="yandex">Yandex</option>
                                    </select>
                                </div>
                                <div class="mb-2">
                                    <label class="form-label">Type</label>
                                    <select name="type" class="form-select">
                                        <option value="indexer">Indexer</option>
                                        <option value="checker">Checker</option>
                                    </select>
                                </div>
                                <div class="mb-2">
                                    <label class="form-label">Title (optional)</label>
                                    <input class="form-control" name="title" />
                                </div>
                                <div class="mb-2">
                                    <label class="form-label">URLs (one per line, max 10,000)</label>
                                    <textarea class="form-control" rows="6" name="urls" placeholder="https://example.com/page1
https://example.com/page2"></textarea>
                                </div>
                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="checkbox" name="vip" value="1" id="vipCheck" />
                                    <label class="form-check-label" for="vipCheck">VIP queue (extra credits per URL)</label>
                                </div>
                                <button class="btn btn-success">Create Task</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <?php if ($result !== null): ?>
            <div class="mt-4">
                <h5>Result (HTTP <?php echo htmlspecialchars(strval($result['httpCode'] ?? 0)); ?>)</h5>
                <pre class="bg-dark text-light p-3" style="white-space: pre-wrap;">
<?php echo htmlspecialchars($result['body'] ?? (is_string($result) ? $result : json_encode($result))); ?>
                </pre>
            </div>
            <?php endif; ?>
        </div>
        </body>
        </html>
        <?php
        break;

    case '/auth':
        require_once __DIR__ . '/src/Db.php';
        
        $action = $_POST['action'] ?? $_GET['action'] ?? null;

        function json_response($data) {
            header('Content-Type: application/json');
            echo json_encode($data);
            exit;
        }

        try {
            if ($action === 'register') {
                $email = trim($_POST['email'] ?? '');
                $pass = $_POST['password'] ?? '';
                if ($email === '' || $pass === '') { throw new Exception('Email and password required'); }
                $hash = password_hash($pass, PASSWORD_BCRYPT);
                $pdo = Db::conn();
                $stmt = $pdo->prepare('INSERT INTO users (email, password_hash) VALUES (?, ?)');
                $stmt->execute([$email, $hash]);
                json_response(['ok' => true]);
            } elseif ($action === 'login') {
                $email = trim($_POST['email'] ?? '');
                $pass = $_POST['password'] ?? '';
                $pdo = Db::conn();
                $stmt = $pdo->prepare('SELECT * FROM users WHERE email = ? LIMIT 1');
                $stmt->execute([$email]);
                $user = $stmt->fetch();
                if (!$user || !password_verify($pass, $user['password_hash'])) { throw new Exception('Invalid credentials'); }
                $_SESSION['uid'] = $user['id'];
                $_SESSION['role'] = $user['role'];
                $pdo->prepare('UPDATE users SET last_login_at = NOW() WHERE id = ?')->execute([$user['id']]);
                json_response(['ok' => true]);
            } elseif ($action === 'logout') {
                session_destroy();
                json_response(['ok' => true]);
            } else {
                json_response(['error' => 'Unsupported action']);
            }
        } catch (Throwable $e) {
            json_response(['error' => $e->getMessage()]);
        }
        break;

    case '/tasks':
        require_once __DIR__ . '/src/Db.php';
        require_once __DIR__ . '/src/TaskService.php';
        
        $userId = $_SESSION['uid'] ?? null;
        if (!$userId) { header('Location: /'); exit; }

        $pdo = Db::conn();
        $msg = null;

        if (($_POST['action'] ?? null) === 'sync' && isset($_POST['task_id'])) {
            try {
                TaskService::syncTaskStatus(intval($userId), intval($_POST['task_id']));
                $msg = 'Task synchronized.';
            } catch (Throwable $e) {
                $msg = 'Error: ' . $e->getMessage();
            }
        }

        if (($_GET['action'] ?? null) === 'export' && isset($_GET['task_id'])) {
            try {
                $csv = TaskService::exportTaskCsv(intval($userId), intval($_GET['task_id']));
                header('Content-Type: text/csv');
                header('Content-Disposition: attachment; filename="task_' . intval($_GET['task_id']) . '.csv"');
                echo $csv;
                exit;
            } catch (Throwable $e) {
                $msg = 'Export error: ' . $e->getMessage();
            }
        }

        $stmt = $pdo->prepare('SELECT * FROM tasks WHERE user_id = ? ORDER BY created_at DESC LIMIT 100');
        $stmt->execute([intval($userId)]);
        $tasks = $stmt->fetchAll();
        ?>
        <!doctype html>
        <html>
        <head>
            <meta charset="utf-8" />
            <title>My Tasks</title>
            <meta name="viewport" content="width=device-width, initial-scale=1" />
            <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" />
        </head>
        <body class="bg-light">
        <div class="container py-4">
            <h1 class="mb-4">My Tasks</h1>
            <a class="btn btn-link" href="/">← Back</a>

            <?php if ($msg): ?>
            <div class="alert alert-info mt-3"><?php echo htmlspecialchars($msg); ?></div>
            <?php endif; ?>

            <div class="card mt-3">
                <div class="card-header">Recent Tasks</div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm mb-0">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Engine</th>
                                    <th>Type</th>
                                    <th>Title</th>
                                    <th>Status</th>
                                    <th>VIP</th>
                                    <th>Created</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($tasks as $t): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars(strval($t['id'])); ?></td>
                                    <td><?php echo htmlspecialchars($t['search_engine']); ?></td>
                                    <td><?php echo htmlspecialchars($t['type']); ?></td>
                                    <td><?php echo htmlspecialchars((string)($t['title'] ?? '')); ?></td>
                                    <td><?php echo htmlspecialchars($t['status']); ?></td>
                                    <td><?php echo $t['vip'] ? 'Yes' : 'No'; ?></td>
                                    <td><?php echo htmlspecialchars($t['created_at']); ?></td>
                                    <td>
                                        <form method="post" class="d-inline">
                                            <input type="hidden" name="action" value="sync" />
                                            <input type="hidden" name="task_id" value="<?php echo htmlspecialchars(strval($t['id'])); ?>" />
                                            <button class="btn btn-sm btn-outline-primary">Sync</button>
                                        </form>
                                        <a class="btn btn-sm btn-outline-secondary" href="/tasks?action=export&task_id=<?php echo htmlspecialchars(strval($t['id'])); ?>">Export CSV</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        </body>
        </html>
        <?php
        break;

    case '/payments':
        require_once __DIR__ . '/src/Db.php';
        
        $userId = $_SESSION['uid'] ?? null;
        if (!$userId) { header('Location: /'); exit; }

        $pdo = Db::conn();
        $stmt = $pdo->prepare('SELECT * FROM payments WHERE user_id = ? ORDER BY created_at DESC LIMIT 100');
        $stmt->execute([intval($userId)]);
        $payments = $stmt->fetchAll();
        ?>
        <!doctype html>
        <html>
        <head>
            <meta charset="utf-8" />
            <title>Payments</title>
            <meta name="viewport" content="width=device-width, initial-scale=1" />
            <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" />
        </head>
        <body class="bg-light">
        <div class="container py-4">
            <h1 class="mb-4">Payments</h1>
            <a class="btn btn-link" href="/">← Back</a>

            <div class="card mt-3">
                <div class="card-header">Recent Payments</div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm mb-0">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Status</th>
                                    <th>Amount</th>
                                    <th>Currency</th>
                                    <th>Credits</th>
                                    <th>PayPal Capture ID</th>
                                    <th>Created</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($payments as $p): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars(strval($p['id'])); ?></td>
                                    <td><?php echo htmlspecialchars($p['status']); ?></td>
                                    <td><?php echo htmlspecialchars(strval($p['amount'])); ?></td>
                                    <td><?php echo htmlspecialchars($p['currency']); ?></td>
                                    <td><?php echo htmlspecialchars(strval($p['credits_awarded'])); ?></td>
                                    <td style="max-width:200px; word-break:break-all;"><?php echo htmlspecialchars((string)($p['paypal_capture_id'] ?? '')); ?></td>
                                    <td><?php echo htmlspecialchars($p['created_at']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        </body>
        </html>
        <?php
        break;

    case '/admin':
        require_once __DIR__ . '/src/Db.php';
        require_once __DIR__ . '/src/CreditsService.php';
        
        if (($_SESSION['role'] ?? '') !== 'admin') { header('Location: /'); exit; }

        $pdo = Db::conn();
        $msg = null;

        if (($_POST['action'] ?? null) === 'adjust_credits') {
            try {
                $targetId = intval($_POST['user_id'] ?? 0);
                $delta = intval($_POST['delta'] ?? 0);
                CreditsService::adjust($targetId, $delta, 'admin_adjustment', 'users', $targetId);
                $msg = 'Adjusted credits.';
            } catch (Throwable $e) {
                $msg = 'Error: ' . $e->getMessage();
            }
        }

        $users = $pdo->query('SELECT id, email, role, status, credits_balance, created_at FROM users ORDER BY created_at DESC LIMIT 100')->fetchAll();
        $payments = $pdo->query('SELECT p.*, u.email FROM payments p JOIN users u ON u.id = p.user_id ORDER BY p.created_at DESC LIMIT 50')->fetchAll();
        $tasks = $pdo->query('SELECT t.*, u.email FROM tasks t JOIN users u ON u.id = t.user_id ORDER BY t.created_at DESC LIMIT 50')->fetchAll();
        ?>
        <!doctype html>
        <html>
        <head>
            <meta charset="utf-8" />
            <title>Admin</title>
            <meta name="viewport" content="width=device-width, initial-scale=1" />
            <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" />
        </head>
        <body class="bg-light">
        <div class="container py-4">
            <h1 class="mb-4">Admin</h1>
            <a class="btn btn-link" href="/">← Back</a>

            <?php if ($msg): ?>
            <div class="alert alert-info mt-3"><?php echo htmlspecialchars($msg); ?></div>
            <?php endif; ?>

            <div class="row g-4">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">Users</div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-sm mb-0">
                                    <thead><tr><th>ID</th><th>Email</th><th>Role</th><th>Status</th><th>Credits</th><th>Adjust</th></tr></thead>
                                    <tbody>
                                    <?php foreach ($users as $u): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars(strval($u['id'])); ?></td>
                                        <td><?php echo htmlspecialchars($u['email']); ?></td>
                                        <td><?php echo htmlspecialchars($u['role']); ?></td>
                                        <td><?php echo htmlspecialchars($u['status']); ?></td>
                                        <td><?php echo htmlspecialchars(strval($u['credits_balance'])); ?></td>
                                        <td>
                                            <form method="post" class="d-flex gap-2">
                                                <input type="hidden" name="action" value="adjust_credits" />
                                                <input type="hidden" name="user_id" value="<?php echo htmlspecialchars(strval($u['id'])); ?>" />
                                                <input type="number" name="delta" class="form-control form-control-sm" placeholder="e.g. 100" />
                                                <button class="btn btn-sm btn-outline-primary">Apply</button>
                                            </form>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="card mb-4">
                        <div class="card-header">Recent Payments</div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-sm mb-0">
                                    <thead><tr><th>ID</th><th>User</th><th>Status</th><th>Amount</th><th>Credits</th><th>Created</th></tr></thead>
                                    <tbody>
                                    <?php foreach ($payments as $p): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars(strval($p['id'])); ?></td>
                                        <td><?php echo htmlspecialchars($p['email']); ?></td>
                                        <td><?php echo htmlspecialchars($p['status']); ?></td>
                                        <td><?php echo htmlspecialchars(strval($p['amount'])); ?> <?php echo htmlspecialchars($p['currency']); ?></td>
                                        <td><?php echo htmlspecialchars(strval($p['credits_awarded'])); ?></td>
                                        <td><?php echo htmlspecialchars($p['created_at']); ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header">Recent Tasks</div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-sm mb-0">
                                    <thead><tr><th>ID</th><th>User</th><th>Engine</th><th>Type</th><th>Status</th><th>VIP</th><th>Created</th></tr></thead>
                                    <tbody>
                                    <?php foreach ($tasks as $t): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars(strval($t['id'])); ?></td>
                                        <td><?php echo htmlspecialchars($t['email']); ?></td>
                                        <td><?php echo htmlspecialchars($t['search_engine']); ?></td>
                                        <td><?php echo htmlspecialchars($t['type']); ?></td>
                                        <td><?php echo htmlspecialchars($t['status']); ?></td>
                                        <td><?php echo $t['vip'] ? 'Yes' : 'No'; ?></td>
                                        <td><?php echo htmlspecialchars($t['created_at']); ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        </body>
        </html>
        <?php
        break;

    case '/webhook_paypal':
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
