<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/config/config.php';
session_start();

// Check if user is logged in and is admin
if (!isset($_SESSION['uid']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header('Location: /login.php');
    exit;
}

require_once __DIR__ . '/src/Db.php';
$pdo = Db::conn();

$error = '';
$success = '';

// Handle admin actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'adjust_credits':
                $user_id = $_POST['user_id'];
                $amount = intval($_POST['amount']);
                $reason = $_POST['reason'];
                
                try {
                    require_once __DIR__ . '/src/CreditsService.php';
                    CreditsService::adjust($user_id, $amount, 'admin_adjustment', 'admin_actions', null);
                    $success = "Credits adjusted successfully for user #$user_id";
                } catch (Exception $e) {
                    $error = 'Failed to adjust credits: ' . $e->getMessage();
                }
                break;
                
            case 'toggle_user_status':
                $user_id = $_POST['user_id'];
                $new_status = $_POST['new_status'];
                
                try {
                    $stmt = $pdo->prepare('UPDATE users SET status = ? WHERE id = ?');
                    $stmt->execute([$new_status, $user_id]);
                    $success = "User #$user_id status updated to $new_status";
                } catch (Exception $e) {
                    $error = 'Failed to update user status: ' . $e->getMessage();
                }
                break;
        }
    }
}

// Get system statistics
$stats = [];

// Total users
$stmt = $pdo->query('SELECT COUNT(*) FROM users');
$stats['total_users'] = $stmt->fetchColumn();

// Active users (last 30 days)
$stmt = $pdo->query('SELECT COUNT(*) FROM users WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)');
$stats['active_users'] = $stmt->fetchColumn();

// Total tasks
$stmt = $pdo->query('SELECT COUNT(*) FROM tasks');
$stats['total_tasks'] = $stmt->fetchColumn();

// Completed tasks
$stmt = $pdo->query('SELECT COUNT(*) FROM tasks WHERE status = "completed"');
$stats['completed_tasks'] = $stmt->fetchColumn();

// Total payments
$stmt = $pdo->query('SELECT COUNT(*) FROM payments WHERE status = "completed"');
$stats['total_payments'] = $stmt->fetchColumn();

// Total revenue
$stmt = $pdo->query('SELECT SUM(amount) FROM payments WHERE status = "completed"');
$stats['total_revenue'] = $stmt->fetchColumn() ?: 0;

// Get recent users
$stmt = $pdo->query('
    SELECT id, email, role, credits_balance, status, created_at
    FROM users 
    ORDER BY created_at DESC 
    LIMIT 10
');
$recent_users = $stmt->fetchAll();

// Get recent payments
$stmt = $pdo->query('
    SELECT p.*, u.email 
    FROM payments p 
    JOIN users u ON p.user_id = u.id 
    ORDER BY p.created_at DESC 
    LIMIT 10
');
$recent_payments = $stmt->fetchAll();

// Get recent API errors
$stmt = $pdo->query('
    SELECT * FROM api_logs 
    WHERE status_code >= 400 
    ORDER BY created_at DESC 
    LIMIT 10
');
$recent_errors = $stmt->fetchAll();

include __DIR__ . '/includes/header_new.php';
?>

<div class="max-w-7xl mx-auto px-6 lg:px-8 py-10" x-data="{ showModal: false, userId: '', userEmail: '' }">
    <div class="flex flex-col md:flex-row justify-between items-center mb-8 gap-4">
        <h1 class="text-3xl font-bold text-white">Admin Dashboard</h1>
        <div class="flex flex-wrap gap-2">
            <a href="/admin_users.php" class="px-4 py-2 rounded-lg border border-primary-600 text-primary-400 hover:bg-primary-600 hover:text-white transition-all flex items-center gap-2">
                <i class="fas fa-users"></i> Users
            </a>
            <a href="/admin_payments.php" class="px-4 py-2 rounded-lg border border-primary-600 text-primary-400 hover:bg-primary-600 hover:text-white transition-all flex items-center gap-2">
                <i class="fas fa-credit-card"></i> Payments
            </a>
            <a href="/admin_messages.php" class="px-4 py-2 rounded-lg border border-primary-600 text-primary-400 hover:bg-primary-600 hover:text-white transition-all flex items-center gap-2">
                <i class="fas fa-envelope"></i> Messages
            </a>
            <a href="/admin_discounts.php" class="px-4 py-2 rounded-lg border border-primary-600 text-primary-400 hover:bg-primary-600 hover:text-white transition-all flex items-center gap-2">
                <i class="fas fa-tags"></i> Discounts
            </a>
            <a href="/admin_settings.php" class="px-4 py-2 rounded-lg bg-primary-600 text-white hover:bg-primary-700 transition-all flex items-center gap-2">
                <i class="fas fa-cog"></i> Settings
            </a>
        </div>
    </div>

    <?php if ($error): ?>
        <div class="mb-6 bg-red-500/10 border border-red-500/20 text-red-400 p-4 rounded-lg flex items-center gap-3">
            <i class="fas fa-exclamation-circle"></i>
            <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>
    
    <?php if ($success): ?>
        <div class="mb-6 bg-green-500/10 border border-green-500/20 text-green-400 p-4 rounded-lg flex items-center gap-3">
            <i class="fas fa-check-circle"></i>
            <?php echo htmlspecialchars($success); ?>
        </div>
    <?php endif; ?>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4 mb-8">
        <div class="card rounded-xl p-4 text-center">
            <h5 class="text-2xl font-bold text-primary-500"><?php echo number_format($stats['total_users']); ?></h5>
            <p class="text-xs text-gray-500 uppercase font-bold mt-1">Users</p>
        </div>
        <div class="card rounded-xl p-4 text-center">
            <h5 class="text-2xl font-bold text-green-400"><?php echo number_format($stats['active_users']); ?></h5>
            <p class="text-xs text-gray-500 uppercase font-bold mt-1">Active</p>
        </div>
        <div class="card rounded-xl p-4 text-center">
            <h5 class="text-2xl font-bold text-blue-400"><?php echo number_format($stats['total_tasks']); ?></h5>
            <p class="text-xs text-gray-500 uppercase font-bold mt-1">Tasks</p>
        </div>
        <div class="card rounded-xl p-4 text-center">
            <h5 class="text-2xl font-bold text-yellow-400"><?php echo number_format($stats['completed_tasks']); ?></h5>
            <p class="text-xs text-gray-500 uppercase font-bold mt-1">Completed</p>
        </div>
        <div class="card rounded-xl p-4 text-center">
            <h5 class="text-2xl font-bold text-purple-400"><?php echo number_format($stats['total_payments']); ?></h5>
            <p class="text-xs text-gray-500 uppercase font-bold mt-1">Payments</p>
        </div>
        <div class="card rounded-xl p-4 text-center">
            <h5 class="text-2xl font-bold text-green-500">$<?php echo number_format($stats['total_revenue'], 2); ?></h5>
            <p class="text-xs text-gray-500 uppercase font-bold mt-1">Revenue</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
        <!-- Recent Users -->
        <div class="card rounded-xl overflow-hidden">
            <div class="px-6 py-4 border-b border-white/5 bg-white/5">
                <h3 class="text-lg font-bold text-white">Recent Users</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="bg-white/5 border-b border-white/5">
                            <th class="px-4 py-3 text-left text-xs font-bold text-gray-400 uppercase">User</th>
                            <th class="px-4 py-3 text-left text-xs font-bold text-gray-400 uppercase">Credits</th>
                            <th class="px-4 py-3 text-left text-xs font-bold text-gray-400 uppercase">Status</th>
                            <th class="px-4 py-3 text-right text-xs font-bold text-gray-400 uppercase">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/5">
                        <?php foreach ($recent_users as $user): ?>
                            <tr class="hover:bg-white/5 transition-colors">
                                <td class="px-4 py-3">
                                    <div class="text-sm text-white font-medium"><?php echo htmlspecialchars($user['email']); ?></div>
                                    <div class="text-xs text-gray-500">#<?php echo $user['id']; ?> • <?php echo $user['role']; ?></div>
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-300 font-mono"><?php echo number_format($user['credits_balance']); ?></td>
                                <td class="px-4 py-3">
                                    <span class="px-2 py-0.5 rounded text-[10px] uppercase font-bold <?php echo $user['status'] === 'active' ? 'bg-green-500/10 text-green-400 border border-green-500/20' : 'bg-red-500/10 text-red-400 border border-red-500/20'; ?>">
                                        <?php echo htmlspecialchars($user['status']); ?>
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <button @click="showModal = true; userId = '<?php echo $user['id']; ?>'; userEmail = '<?php echo htmlspecialchars($user['email']); ?>'" 
                                            class="text-xs px-2 py-1 rounded border border-primary-600 text-primary-400 hover:bg-primary-600 hover:text-white transition-colors">
                                        Adjust
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Recent Payments -->
        <div class="card rounded-xl overflow-hidden">
            <div class="px-6 py-4 border-b border-white/5 bg-white/5">
                <h3 class="text-lg font-bold text-white">Recent Payments</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="bg-white/5 border-b border-white/5">
                            <th class="px-4 py-3 text-left text-xs font-bold text-gray-400 uppercase">User</th>
                            <th class="px-4 py-3 text-left text-xs font-bold text-gray-400 uppercase">Amount</th>
                            <th class="px-4 py-3 text-left text-xs font-bold text-gray-400 uppercase">Status</th>
                            <th class="px-4 py-3 text-left text-xs font-bold text-gray-400 uppercase">Date</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/5">
                        <?php foreach ($recent_payments as $payment): ?>
                            <tr class="hover:bg-white/5 transition-colors">
                                <td class="px-4 py-3 text-sm text-gray-300 truncate max-w-[150px]" title="<?php echo htmlspecialchars($payment['email']); ?>">
                                    <?php echo htmlspecialchars($payment['email']); ?>
                                </td>
                                <td class="px-4 py-3 text-sm font-bold text-white">$<?php echo number_format($payment['amount'], 2); ?></td>
                                <td class="px-4 py-3">
                                    <span class="px-2 py-0.5 rounded text-[10px] uppercase font-bold <?php echo $payment['status'] === 'completed' ? 'bg-green-500/10 text-green-400' : 'bg-yellow-500/10 text-yellow-400'; ?>">
                                        <?php echo htmlspecialchars($payment['status']); ?>
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-xs text-gray-500"><?php echo date('M j', strtotime($payment['created_at'])); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- API Errors -->
    <?php if (!empty($recent_errors)): ?>
    <div class="card rounded-xl overflow-hidden mb-8">
        <div class="px-6 py-4 border-b border-white/5 bg-white/5">
            <h3 class="text-lg font-bold text-white">Recent API Errors</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="bg-white/5 border-b border-white/5">
                        <th class="px-4 py-3 text-left text-xs font-bold text-gray-400 uppercase">Time</th>
                        <th class="px-4 py-3 text-left text-xs font-bold text-gray-400 uppercase">Endpoint</th>
                        <th class="px-4 py-3 text-left text-xs font-bold text-gray-400 uppercase">Status</th>
                        <th class="px-4 py-3 text-left text-xs font-bold text-gray-400 uppercase">Error</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5">
                    <?php foreach ($recent_errors as $error): ?>
                        <tr class="hover:bg-white/5 transition-colors">
                            <td class="px-4 py-3 text-xs text-gray-400"><?php echo date('M j g:i A', strtotime($error['created_at'])); ?></td>
                            <td class="px-4 py-3 text-sm text-gray-300 font-mono"><?php echo htmlspecialchars($error['endpoint']); ?></td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-red-500/10 text-red-400 border border-red-500/20">
                                    <?php echo $error['status_code']; ?>
                                </span>
                            </td>
                            <td class="px-4 py-3 text-sm text-red-300 truncate max-w-md">
                                <?php echo htmlspecialchars(substr($error['error_message'] ?? '', 0, 100)); ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>
    
    <!-- Adjust Credits Modal -->
    <div x-show="showModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center px-4 sm:px-0">
        <div x-show="showModal" x-transition.opacity @click="showModal = false" class="absolute inset-0 bg-black/80 backdrop-blur-sm"></div>
        <div x-show="showModal" x-transition.scale.origin.center class="relative card rounded-xl p-6 max-w-md w-full shadow-2xl border border-white/10">
            <h3 class="text-xl font-bold text-white mb-4">Adjust User Credits</h3>
            
            <form method="POST">
                <input type="hidden" name="action" value="adjust_credits">
                <input type="hidden" name="user_id" :value="userId">
                
                <div class="mb-4">
                    <label class="block text-xs font-bold text-gray-400 uppercase mb-2">User Email</label>
                    <input type="text" :value="userEmail" readonly class="w-full bg-black/20 border border-[#333] rounded-lg p-3 text-gray-400 cursor-not-allowed">
                </div>
                
                <div class="mb-4">
                    <label class="block text-xs font-bold text-gray-400 uppercase mb-2">Credit Adjustment</label>
                    <input type="number" name="amount" required placeholder="Positive (add) or negative (remove)" class="w-full bg-[#111] border border-[#333] rounded-lg p-3 text-white focus:border-primary-500 focus:ring-1 focus:ring-primary-500">
                </div>
                
                <div class="mb-6">
                    <label class="block text-xs font-bold text-gray-400 uppercase mb-2">Reason</label>
                    <textarea name="reason" required placeholder="Why are you adjusting credits?" class="w-full bg-[#111] border border-[#333] rounded-lg p-3 text-white focus:border-primary-500 focus:ring-1 focus:ring-primary-500" rows="3"></textarea>
                </div>
                
                <div class="flex justify-end gap-3">
                    <button type="button" @click="showModal = false" class="px-4 py-2 rounded-lg border border-white/10 text-gray-300 hover:bg-white/5 hover:text-white transition-colors">Cancel</button>
                    <button type="submit" class="px-4 py-2 rounded-lg bg-primary-600 text-white hover:bg-primary-700 transition-colors font-bold">Confirm</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include __DIR__ . '/includes/footer_new.php'; ?>
