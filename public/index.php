<?php
require_once __DIR__ . '/../config.php';
session_start();
require_once __DIR__ . '/../src/Db.php';
require_once __DIR__ . '/../src/SpeedyIndexClient.php';
require_once __DIR__ . '/../src/ApiLogger.php';
require_once __DIR__ . '/../src/CreditsService.php';
require_once __DIR__ . '/../src/TaskService.php';

function log_message(string $msg): void {
	$dir = dirname(LOG_FILE);
	if (!is_dir($dir)) { @mkdir($dir, 0777, true); }
	file_put_contents(LOG_FILE, date('c') . ' ' . $msg . PHP_EOL, FILE_APPEND);
}

$userId = $_SESSION['uid'] ?? null;
$action = $_POST['action'] ?? $_GET['action'] ?? null;
$result = null;
$credits = $userId ? CreditsService::getBalance(intval($userId)) : null;

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
		<form class="d-inline" method="post" action="/auth.php">
			<input type="hidden" name="action" value="logout" />
			<button class="btn btn-sm btn-outline-danger">Logout</button>
		</form>
		<?php else: ?>
		<form class="row g-2" method="post" action="/auth.php">
			<input type="hidden" name="action" value="login" />
			<div class="col-auto"><input class="form-control" name="email" placeholder="Email" /></div>
			<div class="col-auto"><input class="form-control" type="password" name="password" placeholder="Password" /></div>
			<div class="col-auto"><button class="btn btn-primary">Login</button></div>
		</form>
		<form class="row g-2 mt-2" method="post" action="/auth.php">
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
						<button class="btn btn-success">Create Task</button>
					</form>
				</div>
			</div>
		</div>

		<div class="col-md-12">
			<div class="card">
				<div class="card-header">List Tasks</div>
				<div class="card-body">
					<form method="post" class="row g-2 align-items-end">
						<input type="hidden" name="action" value="list_tasks" />
						<div class="col-sm-3">
							<label class="form-label">Search Engine</label>
							<select name="engine" class="form-select">
								<option value="google">Google</option>
								<option value="yandex">Yandex</option>
							</select>
						</div>
						<div class="col-sm-2">
							<label class="form-label">Page</label>
							<input type="number" name="page" class="form-control" value="0" />
						</div>
						<div class="col-sm-3">
							<button class="btn btn-secondary">Load</button>
						</div>
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
