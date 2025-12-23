<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/src/Db.php';
require_once __DIR__ . '/src/EmailService.php';

session_start();
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } else {
        try {
            $pdo = Db::conn();
            // Check if user exists
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if ($user) {
                // Generate token
                $token = bin2hex(random_bytes(32));
                $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));

                // Save to DB
                $stmt = $pdo->prepare("INSERT INTO password_resets (email, token, expires_at) VALUES (?, ?, ?)");
                $stmt->execute([$email, $token, $expires]);

                // Send Email
                $mailer = new EmailService();
                $mailer->sendPasswordReset($email, $token);
            }
            
            // Always show success to prevent email enumeration scanning
            $message = 'If an account exists for that email, we have sent a password reset link.';
            
        } catch (Exception $e) {
            $error = 'System error. Please try again later.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <title>Forgot Password - Rapid Indexer</title>
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
        <h2 class="text-2xl font-bold mb-6 text-center text-white">Reset Password</h2>
        
        <?php if ($message): ?>
            <div class="bg-green-900/20 text-green-400 p-4 rounded mb-6 border border-green-900/50">
                <?= htmlspecialchars($message) ?>
            </div>
            <div class="text-center">
                <a href="/login" class="text-rose-500 hover:underline">Back to Login</a>
            </div>
        <?php else: ?>
        
            <?php if ($error): ?>
                <div class="bg-red-900/20 text-red-400 p-4 rounded mb-6 border border-red-900/50">
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <form method="POST" class="space-y-4">
                <div>
                    <label class="block text-sm text-gray-400 mb-2">Email Address</label>
                    <input type="email" name="email" required class="w-full bg-[#111] border border-[#333] rounded-lg p-3 text-white focus:border-rose-600 focus:ring-1 focus:ring-rose-600 outline-none transition-colors">
                </div>
                <button type="submit" class="w-full bg-rose-600 hover:bg-rose-700 text-white font-bold py-3 rounded-lg transition-all shadow-lg shadow-rose-900/20">
                    Send Reset Link
                </button>
            </form>
            <div class="text-center mt-6">
                <a href="/login" class="text-gray-500 hover:text-gray-300 text-sm">Back to Login</a>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>

