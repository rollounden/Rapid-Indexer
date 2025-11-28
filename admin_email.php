<?php
require_once __DIR__ . '/config/config.php';
session_start();

// Admin Check
if (!isset($_SESSION['uid']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header('Location: /login.php');
    exit;
}

require_once __DIR__ . '/src/Db.php';
require_once __DIR__ . '/src/EmailService.php';

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $subject = trim($_POST['subject'] ?? '');
    $body = trim($_POST['body'] ?? '');
    
    if (empty($subject) || empty($body)) {
        $error = 'Please fill in all fields.';
    } else {
        try {
            // 1. Get all active users
            $pdo = Db::conn();
            $stmt = $pdo->query("SELECT email FROM users WHERE status = 'active'");
            $users = $stmt->fetchAll();
            
            // 2. Send emails
            // Note: For large lists (>100), you should use a batch/queue system.
            // For now, we just loop through them.
            $mailer = new EmailService();
            $count = 0;
            
            // Increase time limit for bulk sending
            set_time_limit(300); 
            
            foreach ($users as $user) {
                if ($mailer->sendPromo($user['email'], $subject, $body)) {
                    $count++;
                }
            }
            
            $message = "Success! Sent emails to $count users.";
            
        } catch (Exception $e) {
            $error = 'Error sending emails: ' . $e->getMessage();
        }
    }
}

include __DIR__ . '/includes/header_new.php';
?>

<div class="max-w-4xl mx-auto px-6 py-12">
    <div class="flex justify-between items-center mb-8">
        <h1 class="text-3xl font-bold text-white">📢 Send Promo Email</h1>
        <a href="/admin.php" class="text-gray-400 hover:text-white">Back to Dashboard</a>
    </div>

    <?php if ($message): ?>
        <div class="bg-green-900/20 text-green-400 p-4 rounded-lg mb-6 border border-green-900/50">
            <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>
    
    <?php if ($error): ?>
        <div class="bg-red-900/20 text-red-400 p-4 rounded-lg mb-6 border border-red-900/50">
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <div class="bg-[#1a1a1a] rounded-xl border border-white/10 p-6">
        <form method="POST" class="space-y-6">
            <div>
                <label class="block text-sm font-bold text-gray-400 mb-2">Subject Line</label>
                <input type="text" name="subject" required class="w-full bg-[#111] border border-[#333] rounded-lg p-3 text-white focus:border-primary-500 outline-none">
            </div>
            
            <div>
                <label class="block text-sm font-bold text-gray-400 mb-2">Email Body (HTML allowed)</label>
                <p class="text-xs text-gray-500 mb-2">Tip: Use &lt;br&gt; for new lines, &lt;b&gt; for bold.</p>
                <textarea name="body" rows="10" required class="w-full bg-[#111] border border-[#333] rounded-lg p-3 text-white focus:border-primary-500 outline-none font-mono"></textarea>
            </div>
            
            <div class="p-4 bg-yellow-900/20 border border-yellow-900/50 rounded-lg text-yellow-200 text-sm">
                <i class="fas fa-exclamation-triangle mr-2"></i>
                Warning: This will send an email to <strong>ALL</strong> active users immediately.
            </div>
            
            <button type="submit" class="bg-primary-600 hover:bg-primary-700 text-white font-bold py-3 px-8 rounded-lg transition shadow-lg shadow-primary-900/20">
                <i class="fas fa-paper-plane mr-2"></i> Send Broadcast
            </button>
        </form>
    </div>
</div>

<?php include __DIR__ . '/includes/footer_new.php'; ?>

