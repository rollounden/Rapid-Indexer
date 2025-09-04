<?php
require_once __DIR__ . '/../config.php';
session_start();
require_once __DIR__ . '/../src/Db.php';
require_once __DIR__ . '/../src/TaskService.php';

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
								<a class="btn btn-sm btn-outline-secondary" href="/tasks.php?action=export&task_id=<?php echo htmlspecialchars(strval($t['id'])); ?>">Export CSV</a>
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


