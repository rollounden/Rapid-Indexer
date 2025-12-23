<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/src/Db.php';

$token = $_GET['token'] ?? '';
$error = '';
$success = '';

if (empty($token)) {
    die('Invalid request.');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    if (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters.';
    } elseif ($password !== $confirm) {
        $error = 'Passwords do not match.';
    } else {
        try {
            $pdo = Db::conn();
            
            // Find valid token
            $stmt = $pdo->prepare("SELECT email FROM password_resets WHERE token = ? AND expires_at > NOW() LIMIT 1");
            $stmt->execute([$token]);
            $reset = $stmt->fetch();

            if ($reset) {
                // Update User Password
                $hash = password_hash($password, PASSWORD_BCRYPT);
                $stmt = $pdo->prepare("UPDATE users SET password_hash = ? WHERE email = ?");
                $stmt->execute([$hash, $reset['email']]);

                // Delete used token
                $stmt = $pdo->prepare("DELETE FROM password_resets WHERE email = ?");
                $stmt->execute([$reset['email']]);

                $success = 'Password has been reset successfully!';
            } else {
                $error = 'Invalid or expired reset link.';
            }
        } catch (Exception $e) {
            $error = 'Error resetting password.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <title>Set New Password - Rapid Indexer</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>tailwind.config = { darkMode: 'class' }</script>
    <link href="https://fonts.googleapis.com/css2?family=Rubik:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { background-color: #141414; color: #fff; font-family: 'Rubik', sans-serif; }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-4">
    <div class="max-w-md w-full bg-[#1a1a1a] p-8 rounded-xl border border-white/10 shadow-2xl">
        <h2 class="text-2xl font-bold mb-6 text-center text-white">New Password</h2>
        
        <?php if ($success): ?>
            <div class="bg-green-900/20 text-green-400 p-4 rounded mb-6 border border-green-900/50">
                <?= htmlspecialchars($success) ?>
            </div>
            <div class="text-center">
                <a href="/login" class="bg-rose-600 hover:bg-rose-700 text-white font-bold py-3 px-6 rounded-lg inline-block transition-all shadow-lg shadow-rose-900/20">Login Now</a>
            </div>
        <?php else: ?>
            <?php if ($error): ?>
                <div class="bg-red-900/20 text-red-400 p-4 rounded mb-6 border border-red-900/50">
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <form method="POST" class="space-y-4">
                <div>
                    <label class="block text-sm text-gray-400 mb-2">New Password</label>
                    <input type="password" name="password" required class="w-full bg-[#111] border border-[#333] rounded-lg p-3 text-white focus:border-rose-600 focus:ring-1 focus:ring-rose-600 outline-none transition-colors">
                </div>
                <div>
                    <label class="block text-sm text-gray-400 mb-2">Confirm Password</label>
                    <input type="password" name="confirm_password" required class="w-full bg-[#111] border border-[#333] rounded-lg p-3 text-white focus:border-rose-600 focus:ring-1 focus:ring-rose-600 outline-none transition-colors">
                </div>
                <button type="submit" class="w-full bg-rose-600 hover:bg-rose-700 text-white font-bold py-3 rounded-lg transition-all shadow-lg shadow-rose-900/20">
                    Reset Password
                </button>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>

