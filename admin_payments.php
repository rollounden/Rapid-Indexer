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

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'delete_payment') {
            $paymentId = intval($_POST['payment_id']);
            try {
                // Check if payment exists
                $stmt = $pdo->prepare('SELECT * FROM payments WHERE id = ?');
                $stmt->execute([$paymentId]);
                $payment = $stmt->fetch();
                
                if (!$payment) {
                    throw new Exception("Payment not found.");
                }
                
                $stmt = $pdo->prepare('DELETE FROM payments WHERE id = ?');
                $stmt->execute([$paymentId]);
                $success = "Payment #$paymentId deleted successfully.";
            } catch (Exception $e) {
                $error = "Failed to delete payment: " . $e->getMessage();
            }
        }
    }
}

// Pagination
$page = max(1, intval($_GET['page'] ?? 1));
$per_page = 20;
$offset = ($page - 1) * $per_page;

// Get total payments
$stmt = $pdo->query("SELECT COUNT(*) FROM payments");
$total_items = $stmt->fetchColumn();
$total_pages = ceil($total_items / $per_page);

// Get payments list
$stmt = $pdo->prepare("
    SELECT p.*, u.email as user_email 
    FROM payments p 
    LEFT JOIN users u ON p.user_id = u.id 
    ORDER BY p.created_at DESC 
    LIMIT :limit OFFSET :offset
");
$stmt->bindValue(':limit', $per_page, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$payments = $stmt->fetchAll();

include __DIR__ . '/includes/header_new.php';
?>

<div class="max-w-7xl mx-auto px-6 lg:px-8 py-10" x-data="{ deleteModal: false, deletePaymentId: '' }">
    <div class="flex flex-col md:flex-row justify-between items-center mb-8 gap-4">
        <div>
            <h1 class="text-3xl font-bold text-white">Manage Payments</h1>
            <nav class="flex items-center text-sm text-gray-400 mt-1">
                <a href="/admin.php" class="hover:text-white transition-colors">Dashboard</a>
                <span class="mx-2">/</span>
                <span class="text-white">Payments</span>
            </nav>
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
    
    <div class="card rounded-xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="bg-white/5 border-b border-white/5">
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-400 uppercase tracking-wider">User Details</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-400 uppercase tracking-wider">Amount</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-400 uppercase tracking-wider">Method</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-400 uppercase tracking-wider">Credits</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-400 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-400 uppercase tracking-wider">Date</th>
                        <th class="px-6 py-4 text-right text-xs font-bold text-gray-400 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5">
                    <?php if (empty($payments)): ?>
                        <tr>
                            <td colspan="7" class="px-6 py-8 text-center text-gray-500">
                                No payments found.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($payments as $payment): ?>
                            <tr class="hover:bg-white/5 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="text-sm font-bold text-white"><?php echo htmlspecialchars($payment['user_email'] ?? 'Unknown User'); ?></div>
                                    <div class="text-xs text-gray-500">User #<?php echo $payment['user_id']; ?> • ID #<?php echo $payment['id']; ?></div>
                                </td>
                                <td class="px-6 py-4 text-sm font-bold text-white">$<?php echo number_format($payment['amount'], 2); ?></td>
                                <td class="px-6 py-4 text-sm text-gray-300"><?php echo htmlspecialchars(ucfirst($payment['method'])); ?></td>
                                <td class="px-6 py-4 text-sm text-gray-300"><?php echo number_format($payment['credits_awarded']); ?></td>
                                <td class="px-6 py-4">
                                    <span class="px-2 py-0.5 rounded text-[10px] uppercase font-bold <?php echo $payment['status'] === 'paid' || $payment['status'] === 'completed' ? 'bg-green-500/10 text-green-400' : ($payment['status'] === 'pending' ? 'bg-yellow-500/10 text-yellow-400' : 'bg-red-500/10 text-red-400'); ?>">
                                        <?php echo ucfirst($payment['status']); ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm text-gray-300"><?php echo date('M j, Y', strtotime($payment['created_at'])); ?></div>
                                    <div class="text-xs text-gray-500"><?php echo date('H:i', strtotime($payment['created_at'])); ?></div>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <button @click="deleteModal = true; deletePaymentId = '<?php echo $payment['id']; ?>'" 
                                            class="p-2 rounded-lg bg-white/5 text-red-400 hover:bg-red-500 hover:text-white transition-all" title="Delete Payment">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <?php if ($total_pages > 1): ?>
            <div class="px-6 py-4 border-t border-white/5 bg-white/5 flex justify-center">
                <nav class="flex items-center gap-2">
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <a href="?page=<?php echo $i; ?>" 
                           class="w-10 h-10 flex items-center justify-center rounded-lg text-sm font-bold transition-colors <?php echo $i === $page ? 'bg-primary-600 text-white' : 'bg-white/5 text-gray-400 hover:bg-white/10 hover:text-white'; ?>">
                            <?php echo $i; ?>
                        </a>
                    <?php endfor; ?>
                </nav>
            </div>
        <?php endif; ?>
    </div>
    
    <!-- Delete Confirmation Modal -->
    <div x-show="deleteModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center px-4 sm:px-0">
        <div x-show="deleteModal" x-transition.opacity @click="deleteModal = false" class="absolute inset-0 bg-black/80 backdrop-blur-sm"></div>
        <div x-show="deleteModal" x-transition.scale.origin.center class="relative card rounded-xl p-6 max-w-md w-full shadow-2xl border border-white/10">
            <div class="text-center mb-6">
                <div class="w-12 h-12 bg-red-500/20 rounded-full flex items-center justify-center mx-auto mb-4 text-red-500">
                    <i class="fas fa-exclamation-triangle text-xl"></i>
                </div>
                <h3 class="text-xl font-bold text-white mb-2">Confirm Deletion</h3>
                <p class="text-gray-400">Are you sure you want to delete payment #<strong class="text-white" x-text="deletePaymentId"></strong>?</p>
                <p class="text-red-400 text-sm mt-2">This action cannot be undone.</p>
            </div>
            
            <form method="POST" class="flex gap-3 justify-center">
                <input type="hidden" name="action" value="delete_payment">
                <input type="hidden" name="payment_id" :value="deletePaymentId">
                
                <button type="button" @click="deleteModal = false" class="px-6 py-2 rounded-lg border border-white/10 text-gray-300 hover:bg-white/5 hover:text-white transition-colors">Cancel</button>
                <button type="submit" class="px-6 py-2 rounded-lg bg-red-600 text-white hover:bg-red-700 transition-colors font-bold">Delete Payment</button>
            </form>
        </div>
    </div>
</div>

<?php include __DIR__ . '/includes/footer_new.php'; ?>
