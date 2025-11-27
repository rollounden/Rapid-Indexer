<?php
require_once __DIR__ . '/config/config.php';
session_start();

$user = null;
$success = '';
$error = '';

if (isset($_SESSION['uid'])) {
    require_once __DIR__ . '/src/Db.php';
    $pdo = Db::conn();
    $stmt = $pdo->prepare('SELECT * FROM users WHERE id = ?');
    $stmt->execute([$_SESSION['uid']]);
    $user = $stmt->fetch();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');
    
    if (empty($name) || empty($email) || empty($subject) || empty($message)) {
        $error = 'Please fill in all fields.';
    } else {
        try {
            require_once __DIR__ . '/src/Db.php';
            $pdo = Db::conn();
            
            $stmt = $pdo->prepare('INSERT INTO contact_messages (user_id, name, email, subject, message) VALUES (?, ?, ?, ?, ?)');
            $stmt->execute([
                $user['id'] ?? null,
                $name,
                $email,
                $subject,
                $message
            ]);
            
            $success = 'Your message has been sent successfully. We will get back to you soon!';
        } catch (Exception $e) {
            $error = 'Failed to send message. Please try again later.';
            // Ensure table exists
            if (strpos($e->getMessage(), "doesn't exist") !== false) {
                 $error .= ' (System update required: Run migration)';
            }
        }
    }
}

$page_title = 'Contact Support - Rapid Indexer';
$meta_description = 'Need help? Contact Rapid Indexer support team for assistance with indexing, payments, or account issues.';
$canonical_url = 'https://rapid-indexer.com/contact.php';

include __DIR__ . '/includes/header_new.php';
?>

<div class="max-w-3xl mx-auto px-6 lg:px-8 py-12">
    <div class="card rounded-xl p-8 md:p-10">
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold text-white mb-2">Contact Support</h1>
            <p class="text-gray-400">Have questions or need assistance? We're here to help.</p>
        </div>
        
        <?php if ($success): ?>
            <div class="mb-6 bg-green-500/10 border border-green-500/20 text-green-400 p-4 rounded-lg flex items-center gap-3">
                <i class="fas fa-check-circle"></i>
                <?php echo htmlspecialchars($success); ?>
            </div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="mb-6 bg-red-500/10 border border-red-500/20 text-red-400 p-4 rounded-lg flex items-center gap-3">
                <i class="fas fa-exclamation-circle"></i>
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-xs font-bold text-gray-400 uppercase mb-2">Your Name</label>
                    <input type="text" name="name" required 
                           value="<?php echo htmlspecialchars($user['email'] ?? $_POST['name'] ?? ''); ?>"
                           <?php echo $user ? 'readonly' : ''; ?>
                           class="w-full bg-[#111] border border-[#333] rounded-lg p-3 text-white focus:border-primary-500 focus:ring-1 focus:ring-primary-500 transition-colors">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-400 uppercase mb-2">Email Address</label>
                    <input type="email" name="email" required 
                           value="<?php echo htmlspecialchars($user['email'] ?? $_POST['email'] ?? ''); ?>"
                           <?php echo $user ? 'readonly' : ''; ?>
                           class="w-full bg-[#111] border border-[#333] rounded-lg p-3 text-white focus:border-primary-500 focus:ring-1 focus:ring-primary-500 transition-colors">
                </div>
            </div>
            
            <div>
                <label class="block text-xs font-bold text-gray-400 uppercase mb-2">Subject</label>
                <select name="subject" required class="w-full bg-[#111] border border-[#333] rounded-lg p-3 text-white focus:border-primary-500 focus:ring-1 focus:ring-primary-500 transition-colors">
                    <option value="">Select a topic...</option>
                    <option value="General Inquiry">General Inquiry</option>
                    <option value="Technical Support">Technical Support</option>
                    <option value="Billing & Payments">Billing & Payments</option>
                    <option value="Feature Request">Feature Request</option>
                    <option value="Other">Other</option>
                </select>
            </div>
            
            <div>
                <label class="block text-xs font-bold text-gray-400 uppercase mb-2">Message</label>
                <textarea name="message" rows="6" required placeholder="How can we help you?" class="w-full bg-[#111] border border-[#333] rounded-lg p-3 text-white focus:border-primary-500 focus:ring-1 focus:ring-primary-500 transition-colors"><?php echo htmlspecialchars($_POST['message'] ?? ''); ?></textarea>
            </div>
            
            <button type="submit" class="w-full bg-primary-600 hover:bg-primary-700 text-white font-bold py-3 px-6 rounded-lg transition-all shadow-lg shadow-primary-900/20 flex items-center justify-center gap-2">
                <i class="fas fa-paper-plane"></i> Send Message
            </button>
        </form>
        
        <div class="text-center mt-8 border-t border-white/5 pt-6">
            <p class="text-gray-500 text-sm">
                Alternatively, email us at <a href="mailto:support@rapid-indexer.com" class="text-primary-400 hover:text-primary-300">support@rapid-indexer.com</a>
            </p>
        </div>
    </div>
</div>

<?php include __DIR__ . '/includes/footer_new.php'; ?>
