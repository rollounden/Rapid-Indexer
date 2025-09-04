<?php
require_once __DIR__ . '/../config.php';
session_start();
require_once __DIR__ . '/../src/Db.php';

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


