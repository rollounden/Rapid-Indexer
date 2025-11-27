<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/config/config.php';
session_start();

// Check if user is logged in
if (!isset($_SESSION['uid'])) {
    header('Location: /login.php');
    exit;
}

require_once __DIR__ . '/src/Db.php';
require_once __DIR__ . '/src/TaskService.php';
$pdo = Db::conn();

// Get task details
$task_id = intval($_GET['id'] ?? 0);
if (!$task_id) {
    die('Invalid task ID');
}

// Fetch task
$stmt = $pdo->prepare('SELECT * FROM tasks WHERE id = ? AND user_id = ?');
$stmt->execute([$task_id, $_SESSION['uid']]);
$task = $stmt->fetch();

if (!$task) {
    die('Task not found or access denied');
}

// Get task links
$stmt = $pdo->prepare('SELECT * FROM task_links WHERE task_id = ? ORDER BY id');
$stmt->execute([$task_id]);
$links = $stmt->fetchAll();

// Calculate stats
$total = count($links);
$indexed = 0;
$unindexed = 0;
$pending = 0;
$error = 0;

foreach ($links as $link) {
    switch ($link['status']) {
        case 'indexed': $indexed++; break;
        case 'unindexed': $unindexed++; break;
        case 'pending': $pending++; break;
        case 'error': $error++; break;
    }
}

$progress = $total > 0 ? round((($indexed + $unindexed) / $total) * 100) : 0;

include __DIR__ . '/includes/header_new.php';
?>

<div class="max-w-7xl mx-auto px-6 lg:px-8 py-10" x-data="{ showDetails: false, detailsContent: '' }">
    <div class="flex flex-col md:flex-row justify-between items-center mb-8 gap-4">
        <div>
            <nav class="flex items-center text-sm text-gray-400 mb-2">
                <a href="/tasks.php" class="hover:text-white transition-colors">Tasks</a>
                <span class="mx-2">/</span>
                <span class="text-white">#<?php echo $task_id; ?></span>
            </nav>
            <h1 class="text-3xl font-bold text-white"><?php echo htmlspecialchars($task['title'] ?: 'Untitled Task'); ?></h1>
        </div>
        <div>
            <span class="px-3 py-1 rounded-full text-sm font-bold uppercase <?php echo $task['status'] === 'completed' ? 'bg-green-500/10 text-green-400 border border-green-500/20' : ($task['status'] === 'processing' ? 'bg-blue-500/10 text-blue-400 border border-blue-500/20' : 'bg-yellow-500/10 text-yellow-400 border border-yellow-500/20'); ?>">
                <?php echo ucfirst($task['status']); ?>
            </span>
        </div>
    </div>
    
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
        <div class="card rounded-xl p-4 text-center">
            <h3 class="text-3xl font-bold text-primary-500"><?php echo $total; ?></h3>
            <div class="text-xs text-gray-500 uppercase font-bold mt-1">Total Links</div>
        </div>
        <div class="card rounded-xl p-4 text-center">
            <h3 class="text-3xl font-bold text-green-400"><?php echo $indexed; ?></h3>
            <div class="text-xs text-gray-500 uppercase font-bold mt-1">Indexed</div>
        </div>
        <div class="card rounded-xl p-4 text-center">
            <h3 class="text-3xl font-bold text-yellow-400"><?php echo $unindexed; ?></h3>
            <div class="text-xs text-gray-500 uppercase font-bold mt-1">Unindexed</div>
        </div>
        <div class="card rounded-xl p-4 text-center">
            <h3 class="text-3xl font-bold text-gray-400"><?php echo $pending; ?></h3>
            <div class="text-xs text-gray-500 uppercase font-bold mt-1">Pending</div>
        </div>
    </div>
    
    <div class="card rounded-xl overflow-hidden">
        <div class="px-6 py-4 border-b border-white/5 bg-white/5 flex justify-between items-center">
            <h5 class="font-bold text-white">Links</h5>
            <?php if ($task['status'] === 'completed'): ?>
                <form method="POST" action="/tasks.php">
                    <input type="hidden" name="action" value="export_csv">
                    <input type="hidden" name="task_id" value="<?php echo $task_id; ?>">
                    <button type="submit" class="px-3 py-1.5 rounded-lg border border-green-500/30 text-green-400 hover:bg-green-500/10 text-sm font-bold transition-colors">
                        <i class="fas fa-download mr-1"></i> Export CSV
                    </button>
                </form>
            <?php endif; ?>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="bg-white/5 border-b border-white/5">
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-400 uppercase tracking-wider">URL</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-400 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-400 uppercase tracking-wider">Checked At</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-400 uppercase tracking-wider">Result</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5">
                    <?php foreach ($links as $link): ?>
                        <tr class="hover:bg-white/5 transition-colors">
                            <td class="px-6 py-4">
                                <div class="truncate max-w-xs md:max-w-md">
                                    <a href="<?php echo htmlspecialchars($link['url']); ?>" target="_blank" class="text-primary-400 hover:text-primary-300 hover:underline transition-colors text-sm">
                                        <?php echo htmlspecialchars($link['url']); ?>
                                    </a>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <?php
                                $badge_class = 'bg-gray-500/10 text-gray-400 border-gray-500/20';
                                switch ($link['status']) {
                                    case 'indexed': $badge_class = 'bg-green-500/10 text-green-400 border-green-500/20'; break;
                                    case 'unindexed': $badge_class = 'bg-yellow-500/10 text-yellow-400 border-yellow-500/20'; break;
                                    case 'error': $badge_class = 'bg-red-500/10 text-red-400 border-red-500/20'; break;
                                }
                                ?>
                                <span class="px-2 py-0.5 rounded text-[10px] uppercase font-bold border <?php echo $badge_class; ?>">
                                    <?php echo ucfirst($link['status']); ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-400">
                                <?php echo $link['checked_at'] ? date('M j, H:i', strtotime($link['checked_at'])) : '-'; ?>
                            </td>
                            <td class="px-6 py-4">
                                <?php if ($link['result_data']): ?>
                                    <button @click="showDetails = true; detailsContent = JSON.stringify(<?php echo htmlspecialchars($link['result_data']); ?>, null, 2)" 
                                            class="text-xs px-2 py-1 rounded border border-blue-500/30 text-blue-400 hover:bg-blue-500/10 transition-colors">
                                        Details
                                    </button>
                                <?php else: ?>
                                    <span class="text-gray-600">-</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <!-- Details Modal -->
    <div x-show="showDetails" x-cloak class="fixed inset-0 z-50 flex items-center justify-center px-4 sm:px-0">
        <div x-show="showDetails" x-transition.opacity @click="showDetails = false" class="absolute inset-0 bg-black/80 backdrop-blur-sm"></div>
        <div x-show="showDetails" x-transition.scale.origin.center class="relative card rounded-xl p-6 max-w-2xl w-full shadow-2xl border border-white/10 max-h-[80vh] flex flex-col">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-xl font-bold text-white">Result Details</h3>
                <button @click="showDetails = false" class="text-gray-500 hover:text-white transition-colors">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            <div class="overflow-auto bg-black/30 p-4 rounded-lg border border-white/5 flex-1">
                <pre class="text-xs text-green-400 font-mono whitespace-pre-wrap" x-text="detailsContent"></pre>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/includes/footer_new.php'; ?>
