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

$error = '';
$success = '';

// Handle task actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'sync_status':
                $task_id = $_POST['task_id'];
                try {
                    // Check task type first
                    $stmt = $pdo->prepare('SELECT type FROM tasks WHERE id = ? AND user_id = ?');
                    $stmt->execute([$task_id, $_SESSION['uid']]);
                    $t = $stmt->fetch();
                    
                    if ($t && $t['type'] === 'traffic') {
                        require_once __DIR__ . '/src/TrafficService.php';
                        TrafficService::syncStatus($task_id);
                    } else {
                        TaskService::syncTaskStatus($_SESSION['uid'], $task_id);
                    }
                    $success = 'Task status updated successfully.';
                } catch (Exception $e) {
                    $error = 'Failed to sync task status: ' . $e->getMessage();
                }
                break;
                
            case 'export_csv':
                $task_id = $_POST['task_id'];
                try {
                    $csv_data = TaskService::exportTaskCsv($_SESSION['uid'], $task_id);
                    
                    header('Content-Type: text/csv');
                    header('Content-Disposition: attachment; filename="task_' . $task_id . '_results.csv"');
                    echo $csv_data;
                    exit;
                } catch (Exception $e) {
                    $error = 'Failed to export results: ' . $e->getMessage();
                }
                break;
                
            case 'vip_queue':
                $task_id = $_POST['task_id'];
                try {
                    // Get task details
                    $stmt = $pdo->prepare('SELECT * FROM tasks WHERE id = ? AND user_id = ? AND type = ?');
                    $stmt->execute([$task_id, $_SESSION['uid'], 'indexer']);
                    $task = $stmt->fetch();
                    
                    if (!$task) {
                        throw new Exception('Task not found or not an indexer task');
                    }
                    
                    // Check if task has links
                    $stmt = $pdo->prepare('SELECT COUNT(*) FROM task_links WHERE task_id = ?');
                    $stmt->execute([$task_id]);
                    $link_count = $stmt->fetchColumn();
                    
                    if ($link_count > 100) {
                        throw new Exception('VIP queue is only available for tasks with ≤ 100 links');
                    }
                    
                    // Add VIP queue request to SpeedyIndex
                    require_once __DIR__ . '/src/SpeedyIndexClient.php';
                    $client = new SpeedyIndexClient(SPEEDYINDEX_BASE_URL, SPEEDYINDEX_API_KEY, $_SESSION['uid']);
                    $result = $client->request('POST', '/v2/task/google/indexer/vip', ['task_id' => $task['speedyindex_task_id']]);
                    
                    if ($result['httpCode'] === 200) {
                        $success = 'VIP queue request submitted successfully.';
                    } else {
                        throw new Exception('Failed to submit VIP queue request');
                    }
                } catch (Exception $e) {
                    $error = 'Failed to submit VIP queue: ' . $e->getMessage();
                }
                break;

            case 'check_indexing':
                $task_id = $_POST['task_id'];
                try {
                    // Fetch original task details
                    $stmt = $pdo->prepare('SELECT * FROM tasks WHERE id = ? AND user_id = ?');
                    $stmt->execute([$task_id, $_SESSION['uid']]);
                    $originalTask = $stmt->fetch();

                    if (!$originalTask) {
                        throw new Exception('Task not found');
                    }

                    // Fetch URLs
                    $stmt = $pdo->prepare('SELECT url FROM task_links WHERE task_id = ?');
                    $stmt->execute([$task_id]);
                    $urls = $stmt->fetchAll(PDO::FETCH_COLUMN);

                    if (empty($urls)) {
                        throw new Exception('No URLs found in this task');
                    }

                    // Create new checker task
                    $engine = 'google'; 
                    $title = 'Check: ' . ($originalTask['title'] ?: 'Task #' . $originalTask['id']);
                    
                    $result = TaskService::createTask($_SESSION['uid'], $engine, 'checker', $urls, $title, false);
                    
                    $newTaskId = $result['task_id'];
                    header("Location: /task_results?id=$newTaskId");
                    exit;

                } catch (Exception $e) {
                    $error = 'Failed to create checking task: ' . $e->getMessage();
                }
                break;
        }
    }
}

// Get user's tasks with pagination
$page = max(1, intval($_GET['page'] ?? 1));
$per_page = 10;
$offset = ($page - 1) * $per_page;
$filter = $_GET['filter'] ?? null;

$whereClause = 'WHERE t.user_id = ?';
$params = [$_SESSION['uid']];

if ($filter === 'traffic') {
    $whereClause .= " AND (t.type = 'traffic' OR t.type = 'traffic_campaign')";
} elseif ($filter) {
    $whereClause .= " AND t.type = ?";
    $params[] = $filter;
}

$stmt = $pdo->prepare("
    SELECT t.*, 
           COUNT(tl.id) as total_links,
           SUM(CASE WHEN tl.status = 'indexed' THEN 1 ELSE 0 END) as indexed_links,
           SUM(CASE WHEN tl.status = 'unindexed' THEN 1 ELSE 0 END) as unindexed_links,
           SUM(CASE WHEN tl.status = 'pending' THEN 1 ELSE 0 END) as pending_links,
           SUM(CASE WHEN tl.status = 'error' THEN 1 ELSE 0 END) as error_links
    FROM tasks t 
    LEFT JOIN task_links tl ON t.id = tl.task_id 
    $whereClause
    GROUP BY t.id 
    ORDER BY t.created_at DESC 
    LIMIT " . (int)$per_page . " OFFSET " . (int)$offset
);
$stmt->execute($params);
$tasks = $stmt->fetchAll();

// Get total count for pagination
$stmt = $pdo->prepare("SELECT COUNT(*) FROM tasks t $whereClause");
$stmt->execute($params);
$total_tasks = $stmt->fetchColumn();
$total_pages = ceil($total_tasks / $per_page);

include __DIR__ . '/includes/header_new.php';
?>

<div class="max-w-7xl mx-auto px-6 lg:px-8 py-10">
    <div class="flex flex-col md:flex-row justify-between items-center mb-8 gap-4">
        <div>
            <h1 class="text-3xl font-bold text-white">My Tasks</h1>
            <p class="text-gray-400 mt-1">Manage and monitor your indexing campaigns.</p>
        </div>
        <div class="flex gap-2">
            <!-- Tab Filters -->
            <div class="bg-white/5 p-1 rounded-lg flex">
                <a href="/tasks.php" class="px-4 py-2 rounded-md text-sm font-bold transition-all <?php echo !isset($_GET['filter']) ? 'bg-primary-600 text-white shadow-lg' : 'text-gray-400 hover:text-white'; ?>">All</a>
                <a href="/tasks.php?filter=indexer" class="px-4 py-2 rounded-md text-sm font-bold transition-all <?php echo (isset($_GET['filter']) && $_GET['filter'] === 'indexer') ? 'bg-primary-600 text-white shadow-lg' : 'text-gray-400 hover:text-white'; ?>">Indexer</a>
                <a href="/tasks.php?filter=checker" class="px-4 py-2 rounded-md text-sm font-bold transition-all <?php echo (isset($_GET['filter']) && $_GET['filter'] === 'checker') ? 'bg-primary-600 text-white shadow-lg' : 'text-gray-400 hover:text-white'; ?>">Checker</a>
                <a href="/tasks.php?filter=traffic" class="px-4 py-2 rounded-md text-sm font-bold transition-all <?php echo (isset($_GET['filter']) && $_GET['filter'] === 'traffic') ? 'bg-primary-600 text-white shadow-lg' : 'text-gray-400 hover:text-white'; ?>">Traffic</a>
            </div>
            
            <a href="/dashboard" class="bg-white/10 hover:bg-white/20 text-white font-bold py-2 px-4 rounded-lg transition-all flex items-center gap-2">
                <i class="fas fa-plus"></i> New
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

    <?php if (empty($tasks)): ?>
        <div class="card rounded-xl p-12 text-center">
            <div class="w-20 h-20 bg-white/5 rounded-full flex items-center justify-center mx-auto mb-6 text-gray-500">
                <i class="fas fa-tasks text-4xl"></i>
            </div>
            <h3 class="text-xl font-bold text-white mb-2">No tasks found</h3>
            <p class="text-gray-400 mb-6">Create your first indexing task to get started.</p>
            <a href="/dashboard" class="inline-flex items-center justify-center px-6 py-3 border border-transparent text-base font-medium rounded-lg text-white bg-primary-600 hover:bg-primary-700 transition-colors">
                <i class="fas fa-plus mr-2"></i> Create Your First Task
            </a>
        </div>
    <?php else: ?>
        <div class="card rounded-xl overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="bg-white/5 border-b border-white/5">
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-400 uppercase tracking-wider">Task Details</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-400 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-400 uppercase tracking-wider w-1/4">Progress</th>
                            <th class="px-6 py-4 text-right text-xs font-bold text-gray-400 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/5">
                        <?php foreach ($tasks as $task): ?>
                            <tr class="hover:bg-white/5 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="flex-shrink-0">
                                            <div class="w-10 h-10 rounded-lg bg-white/5 flex items-center justify-center text-gray-400">
                                                <?php if ($task['type'] === 'traffic'): ?>
                                                    <i class="fas fa-users"></i>
                                                <?php else: ?>
                                                    <i class="<?php echo $task['search_engine'] === 'google' ? 'fab fa-google' : 'fab fa-yandex'; ?>"></i>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        <div>
                                            <div class="font-bold text-white flex items-center gap-2">
                                                <?php 
                                                if ($task['type'] === 'traffic' || $task['type'] === 'traffic_campaign') {
                                                    $meta = json_decode($task['meta_data'] ?? '{}', true);
                                                    // Try to get title from task first, fallback to auto-generated
                                                    echo htmlspecialchars($task['title'] ?: 'Traffic Campaign');
                                                } else {
                                                    echo htmlspecialchars(ucfirst($task['type'])) . ' Task #' . $task['id'];
                                                }
                                                ?>
                                                <?php if ($task['vip']): ?>
                                                    <span class="px-2 py-0.5 rounded text-[10px] bg-yellow-500/20 text-yellow-400 border border-yellow-500/30 font-bold">VIP</span>
                                                <?php endif; ?>
                                                <?php if (!empty($task['is_drip_feed'])): ?>
                                                    <span class="px-2 py-0.5 rounded text-[10px] bg-blue-500/20 text-blue-400 border border-blue-500/30 font-bold" title="Drip Feed Active">
                                                        <i class="fas fa-tint mr-1"></i> DRIP
                                                    </span>
                                                <?php endif; ?>
                                            </div>
                                            <div class="text-sm text-gray-500">
                                                <?php echo date('M j, Y g:i A', strtotime($task['created_at'])); ?>
                                            </div>
                                            <?php if ($task['title'] && $task['type'] !== 'traffic' && $task['type'] !== 'traffic_campaign'): ?>
                                                <div class="text-xs text-gray-400 mt-1 italic">
                                                    "<?php echo htmlspecialchars($task['title']); ?>"
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <?php
                                    $statusClasses = [
                                        'completed' => 'bg-green-500/10 text-green-400 border-green-500/20',
                                        'processing' => 'bg-blue-500/10 text-blue-400 border-blue-500/20',
                                        'pending' => 'bg-yellow-500/10 text-yellow-400 border-yellow-500/20',
                                        'failed' => 'bg-red-500/10 text-red-400 border-red-500/20',
                                        'error' => 'bg-red-500/10 text-red-400 border-red-500/20',
                                    ];
                                    $class = $statusClasses[$task['status']] ?? 'bg-gray-500/10 text-gray-400 border-gray-500/20';
                                    ?>
                                    <span class="px-3 py-1 rounded-full text-xs font-bold border <?php echo $class; ?> uppercase tracking-wide">
                                        <?php echo htmlspecialchars($task['status']); ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <?php if ($task['type'] === 'traffic_campaign'): ?>
                                        <?php
                                            $stmt = $pdo->prepare("SELECT SUM(quantity) as total, SUM(CASE WHEN status = 'completed' THEN quantity ELSE 0 END) as delivered FROM traffic_schedule WHERE task_id = ?");
                                            $stmt->execute([$task['id']]);
                                            $tStats = $stmt->fetch();
                                            $totalVisitors = $tStats['total'] ?? 0;
                                            $deliveredVisitors = $tStats['delivered'] ?? 0;
                                            $percent = $totalVisitors > 0 ? ($deliveredVisitors / $totalVisitors) * 100 : 0;
                                        ?>
                                        <div class="w-full bg-white/10 rounded-full h-2 mb-2">
                                            <div class="bg-primary-600 h-2 rounded-full transition-all duration-500" style="width: <?php echo $percent; ?>%"></div>
                                        </div>
                                        <div class="flex justify-between text-xs text-gray-500">
                                            <span><?php echo round($percent); ?>% Done</span>
                                            <span><?php echo number_format($deliveredVisitors); ?>/<?php echo number_format($totalVisitors); ?> Visitors</span>
                                        </div>
                                    <?php elseif ($task['type'] === 'traffic'): ?>
                                        <?php
                                            $meta = json_decode($task['meta_data'] ?? '{}', true);
                                            $qty = $meta['quantity'] ?? 0;
                                        ?>
                                        <div class="text-sm text-gray-400">
                                            <span class="text-white font-bold"><?php echo number_format($qty); ?></span> Visitors
                                            <div class="text-xs text-gray-500 mt-1">Single Blast</div>
                                        </div>
                                    <?php elseif ($task['total_links'] > 0): ?>
                                        <?php 
                                        $completed = $task['indexed_links'] + $task['unindexed_links'];
                                        // For Drip Feed, 'indexed' means submitted.
                                        $percentage = ($completed / $task['total_links']) * 100;
                                        ?>
                                        <div class="w-full bg-white/10 rounded-full h-2 mb-2">
                                            <div class="bg-primary-600 h-2 rounded-full transition-all duration-500" style="width: <?php echo $percentage; ?>%"></div>
                                        </div>
                                        <div class="flex justify-between text-xs text-gray-500">
                                            <span><?php echo round($percentage); ?>% <?php echo !empty($task['is_drip_feed']) ? 'Submitted' : 'Complete'; ?></span>
                                            <span><?php echo $completed; ?>/<?php echo $task['total_links']; ?> Links</span>
                                        </div>
                                        <?php if (!empty($task['is_drip_feed']) && $task['status'] === 'processing'): ?>
                                            <div class="text-[10px] text-blue-400 mt-1 flex items-center gap-1">
                                                <i class="fas fa-clock"></i> Drip Active
                                            </div>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="text-gray-500 text-sm">No links</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="/task_results.php?id=<?php echo $task['id']; ?>" 
                                           class="p-2 rounded-lg bg-white/5 text-gray-300 hover:bg-primary-600 hover:text-white transition-all" title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        
                                        <?php if ($task['status'] === 'completed'): ?>
                                            <?php if ($task['type'] === 'indexer'): ?>
                                                <form method="POST" class="inline-block">
                                                    <input type="hidden" name="action" value="check_indexing">
                                                    <input type="hidden" name="task_id" value="<?php echo $task['id']; ?>">
                                                    <button type="submit" class="p-2 rounded-lg bg-white/5 text-blue-400 hover:bg-blue-600 hover:text-white transition-all" title="Check Indexing">
                                                        <i class="fas fa-search"></i>
                                                    </button>
                                                </form>
                                            <?php endif; ?>
                                            <form method="POST" class="inline-block">
                                                <input type="hidden" name="action" value="export_csv">
                                                <input type="hidden" name="task_id" value="<?php echo $task['id']; ?>">
                                                <button type="submit" class="p-2 rounded-lg bg-white/5 text-green-400 hover:bg-green-600 hover:text-white transition-all" title="Download CSV">
                                                    <i class="fas fa-download"></i>
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                        
                                        <?php if ($task['status'] === 'pending' && $task['type'] === 'indexer'): ?>
                                            <form method="POST" class="inline-block">
                                                <input type="hidden" name="action" value="vip_queue">
                                                <input type="hidden" name="task_id" value="<?php echo $task['id']; ?>">
                                                <button type="submit" class="p-2 rounded-lg bg-white/5 text-yellow-400 hover:bg-yellow-500 hover:text-black transition-all" title="Upgrade to VIP">
                                                    <i class="fas fa-star"></i>
                                                </button>
                                            </form>
                                        <?php endif; ?>

                                        <?php if (in_array($task['status'], ['pending', 'processing']) && $task['type'] !== 'traffic' && $task['type'] !== 'traffic_campaign'): ?>
                                            <form method="POST" class="inline-block">
                                                <input type="hidden" name="action" value="sync_status">
                                                <input type="hidden" name="task_id" value="<?php echo $task['id']; ?>">
                                                <button type="submit" class="p-2 rounded-lg bg-white/5 text-gray-400 hover:bg-white/10 hover:text-white transition-all" title="Sync Status">
                                                    <i class="fas fa-sync-alt"></i>
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
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
                            <a href="?page=<?php echo $i; ?>" 
                               class="w-10 h-10 flex items-center justify-center rounded-lg text-sm font-bold transition-colors <?php echo $i === $page ? 'bg-primary-600 text-white' : 'bg-white/5 text-gray-400 hover:bg-white/10 hover:text-white'; ?>">
                                <?php echo $i; ?>
                            </a>
                        <?php endfor; ?>
                    </nav>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/includes/footer_new.php'; ?>
