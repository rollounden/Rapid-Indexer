<?php
require_once __DIR__ . '/../config.php';
session_start();
require_once __DIR__ . '/../src/Db.php';
require_once __DIR__ . '/../src/CreditsService.php';

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


