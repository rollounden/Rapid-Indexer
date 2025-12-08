<?php
require_once __DIR__ . '/config/config.php';
session_start();

// Redirect if not logged in
if (!isset($_SESSION['uid'])) {
    header('Location: /login.php');
    exit;
}

require_once __DIR__ . '/src/Db.php';
require_once __DIR__ . '/src/CreditsService.php';
require_once __DIR__ . '/src/TaskService.php';
require_once __DIR__ . '/src/SettingsService.php';

$userId = $_SESSION['uid'];
$enable_vip_queue = SettingsService::get('enable_vip_queue', '1');
$cost_indexing = (int)SettingsService::get('cost_indexing', (string)DEFAULT_COST_INDEXING);
$cost_checking = (int)SettingsService::get('cost_checking', (string)DEFAULT_COST_CHECKING);
$cost_vip = (int)SettingsService::get('cost_vip', (string)DEFAULT_COST_VIP_EXTRA);
$error = '';
$success = '';

// Handle task creation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'create_task') {
    try {
        $engine = $_POST['engine'] ?? 'google';
        $type = $_POST['type'] ?? 'indexer';
        $title = $_POST['title'] ?? null;
        $urlsRaw = $_POST['urls'] ?? '';
        $urls = array_values(array_filter(array_map('trim', preg_split('/\r?\n/', $urlsRaw))));
        $vip = isset($_POST['vip']) && $_POST['vip'] === '1' && $enable_vip_queue === '1';
        
        if (empty($urls)) {
            $error = 'Please enter at least one URL.';
        } else {
            $dripFeed = isset($_POST['drip_feed']) && $_POST['drip_feed'] === '1';
            $dripDuration = isset($_POST['drip_duration']) ? (int)$_POST['drip_duration'] : 3;
            $dripConfig = $dripFeed ? ['duration_days' => $dripDuration] : null;
            
            $created = TaskService::createTask($userId, $engine, $type, $urls, $title, $vip, $dripConfig);
            $success = 'Task created successfully!';
        }
    } catch (Exception $e) {
        $error = 'Error creating task: ' . $e->getMessage();
    }
}

// Get user data and statistics
$pdo = Db::conn();
$credits = CreditsService::getBalance($userId);

// Get recent tasks
$stmt = $pdo->prepare('SELECT * FROM tasks WHERE user_id = ? ORDER BY created_at DESC LIMIT 5');
$stmt->execute([$userId]);
$recentTasks = $stmt->fetchAll();

// Get task statistics
$stmt = $pdo->prepare('SELECT COUNT(*) as total, SUM(CASE WHEN status = "completed" THEN 1 ELSE 0 END) as completed FROM tasks WHERE user_id = ?');
$stmt->execute([$userId]);
$taskStats = $stmt->fetch();

// Get payment statistics
$stmt = $pdo->prepare('SELECT COUNT(*) as total_payments, SUM(amount) as total_spent FROM payments WHERE user_id = ? AND status = "paid"');
$stmt->execute([$userId]);
$paymentStats = $stmt->fetch();

// Check user VIP flag
$userVip = false;
try {
    $stmt = $pdo->prepare('SELECT vip_enabled FROM users WHERE id = ?');
    $stmt->execute([$userId]);
    $userVip = (bool)$stmt->fetchColumn();
} catch (Exception $e) {
    // Column might not exist yet
}

$requirePayment = SettingsService::get('vip_require_payment', '1') === '1';
$isAdmin = isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
$hasSpent = ($paymentStats['total_spent'] ?? 0) > 0;

$isPaidUser = $isAdmin || $userVip || !$requirePayment || $hasSpent;

$currentProvider = SettingsService::get('indexing_provider', 'speedyindex');

include __DIR__ . '/includes/header_new.php';
?>

<div class="max-w-7xl mx-auto px-6 lg:px-8 py-10">
    
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-white">Dashboard</h1>
        <p class="text-gray-400 mt-2">Welcome back to your indexing command center.</p>
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
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <!-- Credits -->
        <div class="card rounded-xl p-6 border-l-4 border-l-primary-600">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-sm text-gray-400 uppercase font-bold tracking-wider">Credits</p>
                    <h3 class="text-3xl font-bold text-white mt-1"><?php echo number_format($credits); ?></h3>
                </div>
                <div class="p-3 bg-primary-900/20 rounded-lg text-primary-500">
                    <i class="fas fa-coins text-xl"></i>
                </div>
            </div>
        </div>

        <!-- Total Tasks -->
        <div class="card rounded-xl p-6">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-sm text-gray-400 uppercase font-bold tracking-wider">Total Tasks</p>
                    <h3 class="text-3xl font-bold text-white mt-1"><?php echo number_format($taskStats['total'] ?? 0); ?></h3>
                </div>
                <div class="p-3 bg-blue-900/20 rounded-lg text-blue-500">
                    <i class="fas fa-list text-xl"></i>
                </div>
            </div>
        </div>

        <!-- Completed -->
        <div class="card rounded-xl p-6">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-sm text-gray-400 uppercase font-bold tracking-wider">Completed</p>
                    <h3 class="text-3xl font-bold text-white mt-1"><?php echo number_format($taskStats['completed'] ?? 0); ?></h3>
                </div>
                <div class="p-3 bg-green-900/20 rounded-lg text-green-500">
                    <i class="fas fa-check-double text-xl"></i>
                </div>
            </div>
        </div>

        <!-- Total Spent -->
        <div class="card rounded-xl p-6">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-sm text-gray-400 uppercase font-bold tracking-wider">Total Spent</p>
                    <h3 class="text-3xl font-bold text-white mt-1">$<?php echo number_format($paymentStats['total_spent'] ?? 0, 2); ?></h3>
                </div>
                <div class="p-3 bg-purple-900/20 rounded-lg text-purple-500">
                    <i class="fas fa-dollar-sign text-xl"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Create Task Form -->
        <div class="lg:col-span-2">
            <div class="card rounded-xl overflow-hidden">
                <div class="px-6 py-4 border-b border-white/5 bg-white/5">
                    <h3 class="text-lg font-bold text-white flex items-center gap-2">
                        <i class="fas fa-plus-circle text-primary-500"></i> Create New Task
                    </h3>
                </div>
                <div class="p-6">
                    <form method="POST">
                        <input type="hidden" name="action" value="create_task" />
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                            <div>
                                <label class="block text-xs font-bold text-gray-400 uppercase mb-2">Search Engine</label>
                                <select name="engine" class="w-full bg-[#111] border border-[#333] rounded-lg p-3 text-white focus:border-primary-500 focus:ring-1 focus:ring-primary-500 transition-colors">
                                    <option value="google">Google</option>
                                    <?php if ($currentProvider !== 'ralfy'): ?>
                                    <option value="yandex">Yandex</option>
                                    <?php endif; ?>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-400 uppercase mb-2">Task Type</label>
                                <select name="type" id="typeSelect" class="w-full bg-[#111] border border-[#333] rounded-lg p-3 text-white focus:border-primary-500 focus:ring-1 focus:ring-primary-500 transition-colors">
                                    <option value="indexer">Indexer</option>
                                    <option value="checker">Checker</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="mb-6">
                            <label class="block text-xs font-bold text-gray-400 uppercase mb-2">Task Title (Optional)</label>
                            <input type="text" name="title" class="w-full bg-[#111] border border-[#333] rounded-lg p-3 text-white focus:border-primary-500 focus:ring-1 focus:ring-primary-500 transition-colors" placeholder="e.g., My New Blog Posts">
                        </div>
                        
                        <div class="mb-6">
                            <label class="block text-xs font-bold text-gray-400 uppercase mb-2">URLs (One per line, max 10,000)</label>
                            <textarea name="urls" id="urlsInput" rows="6" class="w-full bg-[#111] border border-[#333] rounded-lg p-3 text-white font-mono text-sm focus:border-primary-500 focus:ring-1 focus:ring-primary-500 transition-colors" placeholder="https://example.com/page1&#10;https://example.com/page2" required></textarea>
                        </div>
                        
                        <div class="mb-6" id="vipSection" style="display: none;">
                            <?php if ($isPaidUser): ?>
                            <div class="flex flex-col gap-3 p-4 bg-yellow-900/10 border border-yellow-900/20 rounded-lg">
                                <div class="flex items-center gap-3">
                                    <div class="relative inline-block w-12 mr-2 align-middle select-none transition duration-200 ease-in">
                                        <input type="checkbox" name="vip" id="vipQueue" value="1" class="toggle-checkbox absolute block w-6 h-6 rounded-full bg-white border-4 appearance-none cursor-pointer peer"/>
                                        <label for="vipQueue" class="toggle-label block overflow-hidden h-6 rounded-full bg-gray-700 cursor-pointer peer-checked:bg-yellow-500 peer-checked:border-yellow-500"></label>
                                    </div>
                                    <div>
                                        <label for="vipQueue" class="font-bold text-white cursor-pointer flex items-center gap-2">
                                            VIP Priority Queue <i class="fas fa-bolt text-yellow-400"></i>
                                        </label>
                                        <p class="text-xs text-yellow-500 font-bold">+<?php echo $cost_vip; ?> credits/link</p>
                                    </div>
                                </div>
                                <div class="ml-14 text-xs text-gray-400 space-y-1">
                                    <p class="text-yellow-200/80"><i class="fas fa-check mr-1"></i> <strong>Under 2 minute indexing</strong> - Ultra-fast processing</p>
                                    <p class="text-yellow-200/80"><i class="fas fa-check mr-1"></i> Best for <strong>Tier 1 / Money Sites</strong> requiring premium handling</p>
                                    <p class="text-yellow-200/80"><i class="fas fa-check mr-1"></i> Enhanced system reliability and success rates</p>
                                </div>
                            </div>
                            <?php else: ?>
                            <div class="flex flex-col gap-3 p-4 bg-gray-800/50 border border-gray-700 rounded-lg relative overflow-hidden group">
                                <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/5 to-transparent translate-x-[-100%] group-hover:translate-x-[100%] transition-transform duration-1000"></div>
                                <div class="flex items-center gap-3">
                                     <div class="w-12 h-8 flex items-center justify-center bg-gray-700/50 rounded-full text-gray-400 border border-gray-600">
                                         <i class="fas fa-lock text-sm"></i>
                                     </div>
                                     <div>
                                         <strong class="text-gray-300 block flex items-center gap-2">
                                             VIP Priority Queue <span class="text-[10px] uppercase bg-yellow-500/10 text-yellow-500 px-1.5 py-0.5 rounded border border-yellow-500/20">Locked</span>
                                         </strong>
                                         <p class="text-xs text-gray-500">Purchase credits to unlock <strong class="text-gray-300">Under 2 Minute Indexing</strong></p>
                                     </div>
                                     <a href="/payments.php" class="ml-auto text-xs bg-primary-600 hover:bg-primary-500 text-white px-3 py-1.5 rounded transition-colors font-bold z-10 shadow-lg shadow-primary-900/20">
                                         Unlock Now
                                     </a>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>

                        <!-- Standard Queue Info (Visible when VIP is not checked) -->
                        <div id="standardQueueInfo" class="mb-6 p-4 bg-blue-900/10 border border-blue-900/20 rounded-lg">
                             <div class="flex items-start gap-3">
                                <i class="fas fa-clock text-blue-400 mt-1"></i>
                                <div>
                                    <strong class="text-blue-400 text-sm block mb-1">Standard Indexing Timeline</strong>
                                    <p class="text-xs text-gray-400">
                                        Submission to search engines starts in <strong>~2 hours</strong>. 
                                        Full indexing cycle may take up to <strong>48 hours</strong>.
                                    </p>
                                </div>
                             </div>
                        </div>

                        <div id="dripSection" style="display: none;">
                            <!-- Drip feed removed temporarily -->
                        </div>
                        
                        <div class="bg-black/20 rounded-lg p-4 mb-6 border border-white/5 flex justify-between items-center">
                            <div>
                                <span class="text-gray-400 text-sm block">Estimated Cost</span>
                                <div class="text-xs text-gray-500 mt-1">
                                    Index: <span class="text-gray-300"><?php echo $cost_indexing; ?></span> | 
                                    Check: <span class="text-gray-300"><?php echo $cost_checking; ?></span>
                                </div>
                            </div>
                            <div class="text-right">
                                <span id="estimatedCost" class="text-2xl font-bold text-white">0</span>
                                <span class="text-gray-400 text-sm ml-1">credits</span>
                            </div>
                        </div>

                        <button type="submit" class="w-full bg-primary-600 hover:bg-primary-700 text-white font-bold py-3 px-6 rounded-lg transition-all shadow-lg shadow-primary-900/20 flex items-center justify-center gap-2">
                            <i class="fas fa-rocket"></i> Submit Task
                        </button>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- Recent Tasks -->
        <div class="lg:col-span-1">
            <div class="card rounded-xl overflow-hidden h-full">
                <div class="px-6 py-4 border-b border-white/5 bg-white/5 flex justify-between items-center">
                    <h3 class="text-lg font-bold text-white">Recent Tasks</h3>
                    <a href="/tasks.php" class="text-xs font-bold text-primary-400 hover:text-primary-300 uppercase">View All</a>
                </div>
                <div class="divide-y divide-white/5">
                    <?php if (empty($recentTasks)): ?>
                        <div class="p-8 text-center text-gray-500">
                            <i class="fas fa-clipboard-list text-3xl mb-3 opacity-20"></i>
                            <p>No tasks yet</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($recentTasks as $task): ?>
                            <div class="p-4 hover:bg-white/5 transition-colors">
                                <div class="flex justify-between items-start mb-1">
                                    <h4 class="text-sm font-bold text-white truncate pr-2" title="<?php echo htmlspecialchars($task['title'] ?: 'Untitled Task'); ?>">
                                        <?php echo htmlspecialchars($task['title'] ?: 'Untitled Task'); ?>
                                    </h4>
                                    <?php
                                    $statusColors = [
                                        'completed' => 'text-green-400 bg-green-400/10',
                                        'processing' => 'text-yellow-400 bg-yellow-400/10',
                                        'pending' => 'text-gray-400 bg-gray-400/10',
                                        'error' => 'text-red-400 bg-red-400/10',
                                    ];
                                    $statusColor = $statusColors[$task['status']] ?? 'text-gray-400 bg-gray-400/10';
                                    ?>
                                    <span class="text-[10px] uppercase font-bold px-2 py-0.5 rounded <?php echo $statusColor; ?>">
                                        <?php echo htmlspecialchars($task['status']); ?>
                                    </span>
                                </div>
                                <div class="flex justify-between items-center text-xs text-gray-500">
                                    <span>
                                        <i class="<?php echo $task['search_engine'] === 'google' ? 'fab fa-google' : 'fab fa-yandex'; ?> mr-1"></i>
                                        <?php echo ucfirst($task['type']); ?>
                                    </span>
                                    <span><?php echo date('M j', strtotime($task['created_at'])); ?></span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const urlsInput = document.getElementById('urlsInput');
        const typeSelect = document.getElementById('typeSelect');
        const vipSection = document.getElementById('vipSection');
        const vipCheckbox = document.getElementById('vipQueue');
        
        const dripSection = document.getElementById('dripSection');
        const dripCheckbox = document.getElementById('dripFeed');
        const dripOptions = document.getElementById('dripOptions');

        const costDisplay = document.getElementById('estimatedCost');
        
        const COST_INDEXING = <?php echo $cost_indexing; ?>;
        const COST_CHECKING = <?php echo $cost_checking; ?>;
        const COST_VIP = <?php echo $cost_vip; ?>;
        const ENABLE_VIP = true; // Always enable UI if code present
        
        function updateCost() {
            const urls = urlsInput.value.trim().split('\n').filter(line => line.trim() !== '');
            const count = urls.length;
            const type = typeSelect.value;
            const isVip = vipCheckbox && vipCheckbox.checked;
            
            let baseCost = (type === 'checker') ? COST_CHECKING : COST_INDEXING;
            let extra = (isVip && type === 'indexer') ? COST_VIP : 0;
            
            let total = count * (baseCost + extra);
            costDisplay.textContent = new Intl.NumberFormat().format(total);
            
            // VIP and Drip logic
            if (type === 'indexer') {
                if (ENABLE_VIP) vipSection.style.display = 'block';
                dripSection.style.display = 'block';
                
                // Toggle standard info based on VIP (only if VIP checkbox exists)
                const standardInfo = document.getElementById('standardQueueInfo');
                if (standardInfo) {
                    standardInfo.style.display = (isVip) ? 'none' : 'block';
                }
            } else {
                vipSection.style.display = 'none';
                dripSection.style.display = 'none';
                if (vipCheckbox) vipCheckbox.checked = false;
                if (dripCheckbox) {
                    dripCheckbox.checked = false;
                    dripOptions.style.display = 'none';
                }
            }
        }
        
        if (dripCheckbox) {
            dripCheckbox.addEventListener('change', function() {
                dripOptions.style.display = this.checked ? 'block' : 'none';
            });
        }

        urlsInput.addEventListener('input', updateCost);
        typeSelect.addEventListener('change', updateCost);
        if (vipCheckbox) vipCheckbox.addEventListener('change', updateCost);
        
        // Initial check
        updateCost();
    });
</script>

<?php include __DIR__ . '/includes/footer_new.php'; ?>
