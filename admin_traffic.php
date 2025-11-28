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
require_once __DIR__ . '/src/SettingsService.php';
$pdo = Db::conn();

$error = '';
$success = '';

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'cancel_schedule':
                $schedule_id = $_POST['schedule_id'];
                $stmt = $pdo->prepare("UPDATE traffic_schedule SET status = 'failed', execution_log = 'Cancelled by Admin' WHERE id = ? AND status = 'pending'");
                $stmt->execute([$schedule_id]);
                $success = "Schedule #$schedule_id cancelled.";
                break;
        }
    }
}

// Pagination
$page = max(1, intval($_GET['page'] ?? 1));
$per_page = 20;
$offset = ($page - 1) * $per_page;

// Get traffic tasks
$stmt = $pdo->prepare('
    SELECT t.*, u.email,
           (SELECT COUNT(*) FROM traffic_schedule s WHERE s.task_id = t.id) as total_runs,
           (SELECT COUNT(*) FROM traffic_schedule s WHERE s.task_id = t.id AND s.status = "completed") as completed_runs,
           (SELECT COUNT(*) FROM traffic_schedule s WHERE s.task_id = t.id AND s.status = "pending") as pending_runs
    FROM tasks t
    JOIN users u ON t.user_id = u.id
    WHERE t.type IN ("traffic", "traffic_campaign")
    ORDER BY t.created_at DESC 
    LIMIT ' . (int)$per_page . ' OFFSET ' . (int)$offset
);
$stmt->execute();
$tasks = $stmt->fetchAll();

// Get total count
$stmt = $pdo->query('SELECT COUNT(*) FROM tasks WHERE type IN ("traffic", "traffic_campaign")');
$total_tasks = $stmt->fetchColumn();
$total_pages = ceil($total_tasks / $per_page);

// Get next upcoming runs
$stmt = $pdo->query('
    SELECT s.*, t.title, u.email 
    FROM traffic_schedule s
    JOIN tasks t ON s.task_id = t.id
    JOIN users u ON t.user_id = u.id
    WHERE s.status = "pending"
    ORDER BY s.scheduled_at ASC
    LIMIT 10
');
$upcoming_runs = $stmt->fetchAll();

include __DIR__ . '/includes/header_new.php';
?>

<div class="max-w-7xl mx-auto px-6 lg:px-8 py-10">
    <div class="flex flex-col md:flex-row justify-between items-center mb-8 gap-4">
        <div>
            <h1 class="text-3xl font-bold text-white">Traffic Management</h1>
            <nav class="flex items-center text-sm text-gray-400 mt-1">
                <a href="/admin.php" class="hover:text-white transition-colors">Dashboard</a>
                <span class="mx-2">/</span>
                <span class="text-white">Traffic</span>
            </nav>
        </div>
        <a href="/admin.php" class="bg-white/5 hover:bg-white/10 text-white font-medium py-2 px-4 rounded-lg transition-colors border border-white/10">
            <i class="fas fa-arrow-left mr-2"></i> Back to Dashboard
        </a>
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

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8">
        <!-- Tasks List -->
        <div class="lg:col-span-2 card rounded-xl overflow-hidden">
            <div class="px-6 py-4 border-b border-white/5 bg-white/5">
                <h3 class="text-lg font-bold text-white">Recent Traffic Campaigns</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="bg-white/5 border-b border-white/5">
                            <th class="px-4 py-3 text-left text-xs font-bold text-gray-400 uppercase">User</th>
                            <th class="px-4 py-3 text-left text-xs font-bold text-gray-400 uppercase">Details</th>
                            <th class="px-4 py-3 text-left text-xs font-bold text-gray-400 uppercase">Progress</th>
                            <th class="px-4 py-3 text-left text-xs font-bold text-gray-400 uppercase">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/5">
                        <?php foreach ($tasks as $task): ?>
                            <tr class="hover:bg-white/5 transition-colors">
                                <td class="px-4 py-3">
                                    <div class="text-sm text-white font-medium"><?php echo htmlspecialchars($task['email']); ?></div>
                                    <div class="text-xs text-gray-500"><?php echo date('M j, g:i a', strtotime($task['created_at'])); ?></div>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="text-sm text-gray-300 truncate max-w-xs" title="<?php echo htmlspecialchars($task['title']); ?>">
                                        <?php echo htmlspecialchars($task['title']); ?>
                                    </div>
                                    <div class="text-xs text-primary-400 mt-1">
                                        <?php 
                                        $meta = json_decode($task['meta_data'] ?? '{}', true);
                                        echo $task['type'] === 'traffic_campaign' ? 'Viral Blast (' . ($meta['days'] ?? '?') . ' days)' : 'Quick Boost';
                                        ?>
                                    </div>
                                </td>
                                <td class="px-4 py-3">
                                    <?php if ($task['type'] === 'traffic_campaign'): ?>
                                        <div class="flex items-center gap-2 text-xs text-gray-400">
                                            <div class="w-20 bg-white/10 rounded-full h-1.5">
                                                <div class="bg-primary-600 h-1.5 rounded-full" style="width: <?php echo ($task['total_runs'] > 0 ? ($task['completed_runs'] / $task['total_runs']) * 100 : 0); ?>%"></div>
                                            </div>
                                            <span><?php echo $task['completed_runs']; ?>/<?php echo $task['total_runs']; ?></span>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-xs text-gray-500">N/A</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="px-2 py-0.5 rounded text-[10px] uppercase font-bold <?php echo $task['status'] === 'completed' ? 'bg-green-500/10 text-green-400' : ($task['status'] === 'processing' ? 'bg-blue-500/10 text-blue-400' : 'bg-red-500/10 text-red-400'); ?>">
                                        <?php echo $task['status']; ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php if ($total_pages > 1): ?>
                <div class="px-6 py-4 border-t border-white/5 bg-white/5 flex justify-center">
                    <nav class="flex items-center gap-2">
                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                            <a href="?page=<?php echo $i; ?>" class="w-8 h-8 flex items-center justify-center rounded-lg text-xs font-bold <?php echo $i === $page ? 'bg-primary-600 text-white' : 'bg-white/5 text-gray-400 hover:bg-white/10'; ?>">
                                <?php echo $i; ?>
                            </a>
                        <?php endfor; ?>
                    </nav>
                </div>
            <?php endif; ?>
        </div>

        <!-- Upcoming Schedule -->
        <div class="card rounded-xl overflow-hidden h-fit">
            <div class="px-6 py-4 border-b border-white/5 bg-white/5">
                <h3 class="text-lg font-bold text-white">Next Scheduled Runs</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <tbody class="divide-y divide-white/5">
                        <?php if (empty($upcoming_runs)): ?>
                            <tr><td class="px-6 py-8 text-center text-gray-500 text-sm">No upcoming runs scheduled.</td></tr>
                        <?php else: ?>
                            <?php foreach ($upcoming_runs as $run): ?>
                                <tr class="hover:bg-white/5 transition-colors">
                                    <td class="px-4 py-3">
                                        <div class="text-xs text-primary-400 font-bold mb-1">
                                            <?php 
                                            $diff = strtotime($run['scheduled_at']) - time();
                                            if ($diff < 0) echo 'Due Now';
                                            elseif ($diff < 60) echo 'In < 1 min';
                                            elseif ($diff < 3600) echo 'In ' . round($diff/60) . ' mins';
                                            else echo 'In ' . round($diff/3600) . ' hours';
                                            ?>
                                        </div>
                                        <div class="text-sm text-white font-mono"><?php echo $run['quantity']; ?> Visitors</div>
                                        <div class="text-xs text-gray-500 mt-1 truncate max-w-[150px]"><?php echo htmlspecialchars($run['email']); ?></div>
                                    </td>
                                    <td class="px-4 py-3 text-right">
                                        <form method="POST" onsubmit="return confirm('Cancel this scheduled run?');">
                                            <input type="hidden" name="action" value="cancel_schedule">
                                            <input type="hidden" name="schedule_id" value="<?php echo $run['id']; ?>">
                                            <button type="submit" class="text-xs text-red-400 hover:text-red-300 border border-red-500/30 px-2 py-1 rounded hover:bg-red-500/10 transition-colors">
                                                Cancel
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
    </div>
</div>

<?php include __DIR__ . '/includes/footer_new.php'; ?>

