<?php
require_once __DIR__ . '/../config.php';
session_start();
require_once __DIR__ . '/../src/Db.php';

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
