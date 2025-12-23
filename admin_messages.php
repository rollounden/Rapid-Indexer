<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/config/config.php';
if (session_status() === PHP_SESSION_NONE) { session_start(); }

// Check if user is logged in and is admin
if (!isset($_SESSION['uid']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header('Location: /login');
    exit;
}

require_once __DIR__ . '/src/Db.php';
$pdo = Db::conn();

// Ensure table exists (auto-migration)
try {
    $pdo->query("SELECT 1 FROM contact_messages LIMIT 1");
} catch (Exception $e) {
    // Table doesn't exist, create it
    $sql = "
    CREATE TABLE IF NOT EXISTS contact_messages (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        user_id BIGINT UNSIGNED NULL,
        name VARCHAR(255) NOT NULL,
        email VARCHAR(255) NOT NULL,
        subject VARCHAR(255) NOT NULL,
        message TEXT NOT NULL,
        status ENUM('new', 'read', 'replied') DEFAULT 'new',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ";
    try {
        $pdo->exec($sql);
    } catch (Exception $ex) {
        die("Database initialization error: " . $ex->getMessage());
    }
}

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'update_status') {
        $id = intval($_POST['id']);
        $status = $_POST['status'];
        
        $stmt = $pdo->prepare("UPDATE contact_messages SET status = ? WHERE id = ?");
        $stmt->execute([$status, $id]);
    }
}

// Get messages
$status_filter = $_GET['status'] ?? 'all';
$sql = "SELECT m.*, u.email as user_email FROM contact_messages m LEFT JOIN users u ON m.user_id = u.id";
$params = [];

if ($status_filter !== 'all') {
    $sql .= " WHERE m.status = ?";
    $params[] = $status_filter;
}

$sql .= " ORDER BY m.created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$messages = $stmt->fetchAll();

include __DIR__ . '/includes/header_new.php';
?>

<div class="max-w-7xl mx-auto px-6 lg:px-8 py-10" x-data="{ viewModal: false, msg: {} }">
    <div class="flex flex-col md:flex-row justify-between items-center mb-8 gap-4">
        <div>
            <h1 class="text-3xl font-bold text-white">Support Messages</h1>
            <nav class="flex items-center text-sm text-gray-400 mt-1">
                <a href="/admin" class="hover:text-white transition-colors">Dashboard</a>
                <span class="mx-2">/</span>
                <span class="text-white">Messages</span>
            </nav>
        </div>
        
        <div class="flex bg-black/20 p-1 rounded-lg border border-white/5">
            <a href="?status=all" class="px-4 py-2 rounded-md text-sm font-bold transition-all <?php echo $status_filter === 'all' ? 'bg-white/10 text-white' : 'text-gray-400 hover:text-white hover:bg-white/5'; ?>">All</a>
            <a href="?status=new" class="px-4 py-2 rounded-md text-sm font-bold transition-all <?php echo $status_filter === 'new' ? 'bg-primary-600 text-white' : 'text-gray-400 hover:text-white hover:bg-white/5'; ?>">New</a>
            <a href="?status=read" class="px-4 py-2 rounded-md text-sm font-bold transition-all <?php echo $status_filter === 'read' ? 'bg-blue-500/20 text-blue-400' : 'text-gray-400 hover:text-white hover:bg-white/5'; ?>">Read</a>
            <a href="?status=replied" class="px-4 py-2 rounded-md text-sm font-bold transition-all <?php echo $status_filter === 'replied' ? 'bg-green-500/20 text-green-400' : 'text-gray-400 hover:text-white hover:bg-white/5'; ?>">Replied</a>
        </div>
    </div>
    
    <div class="card rounded-xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="bg-white/5 border-b border-white/5">
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-400 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-400 uppercase tracking-wider">Date</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-400 uppercase tracking-wider">User / Email</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-400 uppercase tracking-wider">Subject</th>
                        <th class="px-6 py-4 text-right text-xs font-bold text-gray-400 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5">
                    <?php if (empty($messages)): ?>
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-gray-500">
                                <i class="far fa-envelope text-4xl mb-4 block opacity-50"></i>
                                No messages found.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($messages as $msg): ?>
                            <tr class="hover:bg-white/5 transition-colors <?php echo $msg['status'] === 'new' ? 'bg-white/[0.02]' : ''; ?>">
                                <td class="px-6 py-4">
                                    <?php
                                    $badge = 'bg-gray-500/10 text-gray-400 border-gray-500/20';
                                    if ($msg['status'] === 'new') $badge = 'bg-primary-600 text-white shadow-lg shadow-primary-900/20';
                                    if ($msg['status'] === 'read') $badge = 'bg-blue-500/10 text-blue-400 border-blue-500/20';
                                    if ($msg['status'] === 'replied') $badge = 'bg-green-500/10 text-green-400 border-green-500/20';
                                    ?>
                                    <span class="px-2 py-1 rounded text-[10px] uppercase font-bold <?php echo $badge; ?>">
                                        <?php echo ucfirst($msg['status']); ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm text-gray-300"><?php echo date('M j', strtotime($msg['created_at'])); ?></div>
                                    <div class="text-xs text-gray-500"><?php echo date('H:i', strtotime($msg['created_at'])); ?></div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm font-bold text-white"><?php echo htmlspecialchars($msg['name']); ?></div>
                                    <a href="mailto:<?php echo htmlspecialchars($msg['email']); ?>" class="text-xs text-gray-400 hover:text-white transition-colors">
                                        <?php echo htmlspecialchars($msg['email']); ?>
                                    </a>
                                    <?php if ($msg['user_id']): ?>
                                        <div class="text-[10px] text-primary-400 mt-0.5">User #<?php echo $msg['user_id']; ?></div>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-300 truncate max-w-[200px]">
                                    <?php echo htmlspecialchars($msg['subject']); ?>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <button @click="viewModal = true; msg = <?php echo htmlspecialchars(json_encode($msg)); ?>" 
                                            class="p-2 rounded-lg bg-white/5 text-primary-400 hover:bg-primary-600 hover:text-white transition-all" title="View Message">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <!-- View Modal -->
    <div x-show="viewModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center px-4 sm:px-0">
        <div x-show="viewModal" x-transition.opacity @click="viewModal = false" class="absolute inset-0 bg-black/80 backdrop-blur-sm"></div>
        <div x-show="viewModal" x-transition.scale.origin.center class="relative card rounded-xl p-6 max-w-2xl w-full shadow-2xl border border-white/10 max-h-[90vh] overflow-y-auto">
            <div class="flex justify-between items-start mb-6">
                <div>
                    <h3 class="text-xl font-bold text-white mb-1" x-text="msg.subject"></h3>
                    <div class="text-sm text-gray-400">
                        From: <span class="text-white font-bold" x-text="msg.name"></span> &lt;<span x-text="msg.email"></span>&gt;
                    </div>
                </div>
                <button @click="viewModal = false" class="text-gray-500 hover:text-white transition-colors">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            
            <div class="p-4 bg-white/5 rounded-lg border border-white/5 text-gray-300 mb-6 whitespace-pre-wrap leading-relaxed" x-text="msg.message"></div>
            
            <div class="flex flex-wrap justify-end gap-3 pt-4 border-t border-white/10">
                <a :href="'mailto:' + msg.email + '?subject=Re: ' + encodeURIComponent(msg.subject)" 
                   class="px-4 py-2 rounded-lg bg-primary-600 text-white hover:bg-primary-700 transition-colors font-bold flex items-center gap-2">
                    <i class="fas fa-reply"></i> Reply via Email
                </a>
                
                <form method="POST" class="flex gap-2">
                    <input type="hidden" name="action" value="update_status">
                    <input type="hidden" name="id" :value="msg.id">
                    
                    <button type="submit" name="status" value="read" 
                            x-show="msg.status !== 'read' && msg.status !== 'replied'"
                            class="px-4 py-2 rounded-lg border border-white/10 text-gray-300 hover:bg-white/5 hover:text-white transition-colors font-bold">
                        Mark Read
                    </button>
                    
                    <button type="submit" name="status" value="replied" 
                            x-show="msg.status !== 'replied'"
                            class="px-4 py-2 rounded-lg bg-green-600 text-white hover:bg-green-700 transition-colors font-bold">
                        Mark Replied
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/includes/footer_new.php'; ?>
