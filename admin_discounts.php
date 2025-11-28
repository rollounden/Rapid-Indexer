<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/config/config.php';
session_start();

if (!isset($_SESSION['uid']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header('Location: /login.php');
    exit;
}

require_once __DIR__ . '/src/Db.php';
require_once __DIR__ . '/src/DiscountService.php';
$pdo = Db::conn();

$error = '';
$success = '';

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (isset($_POST['action'])) {
            switch ($_POST['action']) {
                case 'create_discount':
                    DiscountService::create([
                        'code' => $_POST['code'],
                        'type' => $_POST['type'],
                        'value' => floatval($_POST['value']),
                        'min_spend' => !empty($_POST['min_spend']) ? floatval($_POST['min_spend']) : null,
                        'max_uses' => !empty($_POST['max_uses']) ? intval($_POST['max_uses']) : null,
                        'expires_at' => !empty($_POST['expires_at']) ? $_POST['expires_at'] : null,
                        'affiliate_user_id' => !empty($_POST['affiliate_user_id']) ? intval($_POST['affiliate_user_id']) : null
                    ]);
                    $success = 'Discount code created successfully.';
                    break;
                    
                case 'delete_discount':
                    DiscountService::delete($_POST['id']);
                    $success = 'Discount code deleted.';
                    break;
            }
        }
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

$discounts = DiscountService::getAll();

include __DIR__ . '/includes/header_new.php';
?>

<div class="max-w-7xl mx-auto px-6 lg:px-8 py-10" x-data="{ showModal: false }">
    <div class="flex flex-col md:flex-row justify-between items-center mb-8 gap-4">
        <div>
            <h1 class="text-3xl font-bold text-white">Discount Codes</h1>
            <p class="text-gray-400 mt-2">Manage discounts and affiliate codes.</p>
        </div>
        <div class="flex gap-2">
            <a href="/admin.php" class="px-4 py-2 rounded-lg border border-white/10 text-gray-300 hover:bg-white/5 transition-all">
                Back to Dashboard
            </a>
            <button @click="showModal = true" class="px-4 py-2 rounded-lg bg-primary-600 text-white hover:bg-primary-700 transition-all flex items-center gap-2">
                <i class="fas fa-plus"></i> New Code
            </button>
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
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-400 uppercase">Code</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-400 uppercase">Value</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-400 uppercase">Usage</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-400 uppercase">Expires</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-400 uppercase">Affiliate</th>
                        <th class="px-6 py-3 text-right text-xs font-bold text-gray-400 uppercase">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5">
                    <?php if (empty($discounts)): ?>
                        <tr><td colspan="6" class="px-6 py-8 text-center text-gray-500">No discount codes found</td></tr>
                    <?php else: ?>
                        <?php foreach ($discounts as $d): ?>
                            <tr class="hover:bg-white/5 transition-colors">
                                <td class="px-6 py-4 text-sm font-mono font-bold text-white"><?php echo htmlspecialchars($d['code']); ?></td>
                                <td class="px-6 py-4 text-sm text-gray-300">
                                    <?php echo $d['type'] === 'percent' ? number_format($d['value']) . '%' : '$' . number_format($d['value'], 2); ?>
                                    <?php if ($d['min_spend']): ?>
                                        <div class="text-xs text-gray-500">Min spend: $<?php echo $d['min_spend']; ?></div>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-300">
                                    <?php echo $d['used_count']; ?> / <?php echo $d['max_uses'] ?? '∞'; ?>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-300">
                                    <?php echo $d['expires_at'] ? date('M j, Y', strtotime($d['expires_at'])) : 'Never'; ?>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-300">
                                    <?php echo $d['affiliate_email'] ? htmlspecialchars($d['affiliate_email']) : '-'; ?>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <form method="POST" onsubmit="return confirm('Are you sure?');">
                                        <input type="hidden" name="action" value="delete_discount">
                                        <input type="hidden" name="id" value="<?php echo $d['id']; ?>">
                                        <button type="submit" class="text-red-400 hover:text-red-300 transition-colors">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Create Modal -->
    <div x-show="showModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center px-4 sm:px-0">
        <div x-show="showModal" x-transition.opacity @click="showModal = false" class="absolute inset-0 bg-black/80 backdrop-blur-sm"></div>
        <div x-show="showModal" x-transition.scale.origin.center class="relative card rounded-xl p-6 max-w-md w-full shadow-2xl border border-white/10">
            <h3 class="text-xl font-bold text-white mb-4">Create Discount Code</h3>
            
            <form method="POST">
                <input type="hidden" name="action" value="create_discount">
                
                <div class="mb-4">
                    <label class="block text-xs font-bold text-gray-400 uppercase mb-2">Code</label>
                    <input type="text" name="code" required class="w-full bg-[#111] border border-[#333] rounded-lg p-3 text-white focus:border-primary-500 focus:ring-1 focus:ring-primary-500 uppercase" placeholder="SUMMER20">
                </div>
                
                <div class="grid grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-400 uppercase mb-2">Type</label>
                        <select name="type" class="w-full bg-[#111] border border-[#333] rounded-lg p-3 text-white focus:border-primary-500 focus:ring-1 focus:ring-primary-500">
                            <option value="percent">Percentage (%)</option>
                            <option value="fixed">Fixed Amount ($)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-400 uppercase mb-2">Value</label>
                        <input type="number" name="value" required step="0.01" class="w-full bg-[#111] border border-[#333] rounded-lg p-3 text-white focus:border-primary-500 focus:ring-1 focus:ring-primary-500" placeholder="20">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-400 uppercase mb-2">Min Spend ($)</label>
                        <input type="number" name="min_spend" step="0.01" class="w-full bg-[#111] border border-[#333] rounded-lg p-3 text-white focus:border-primary-500 focus:ring-1 focus:ring-primary-500" placeholder="Optional">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-400 uppercase mb-2">Max Uses</label>
                        <input type="number" name="max_uses" step="1" class="w-full bg-[#111] border border-[#333] rounded-lg p-3 text-white focus:border-primary-500 focus:ring-1 focus:ring-primary-500" placeholder="Optional">
                    </div>
                </div>

                <div class="mb-4">
                    <label class="block text-xs font-bold text-gray-400 uppercase mb-2">Expires At</label>
                    <input type="datetime-local" name="expires_at" class="w-full bg-[#111] border border-[#333] rounded-lg p-3 text-white focus:border-primary-500 focus:ring-1 focus:ring-primary-500">
                </div>

                <div class="mb-6">
                    <label class="block text-xs font-bold text-gray-400 uppercase mb-2">Affiliate User ID</label>
                    <input type="number" name="affiliate_user_id" class="w-full bg-[#111] border border-[#333] rounded-lg p-3 text-white focus:border-primary-500 focus:ring-1 focus:ring-primary-500" placeholder="Optional - for tracking">
                </div>
                
                <div class="flex justify-end gap-3">
                    <button type="button" @click="showModal = false" class="px-4 py-2 rounded-lg border border-white/10 text-gray-300 hover:bg-white/5 hover:text-white transition-colors">Cancel</button>
                    <button type="submit" class="px-4 py-2 rounded-lg bg-primary-600 text-white hover:bg-primary-700 transition-colors font-bold">Create Code</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include __DIR__ . '/includes/footer_new.php'; ?>

