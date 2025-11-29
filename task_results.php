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
// If traffic campaign, we might not have individual links in task_links for every visitor
// but we might have schedule items.
if ($task['type'] === 'traffic_campaign') {
    // Fetch schedule
    $stmt = $pdo->prepare('SELECT * FROM traffic_schedule WHERE task_id = ? ORDER BY scheduled_at ASC');
    $stmt->execute([$task_id]);
    $schedule = $stmt->fetchAll();
    
    // Transform to link-like structure for display or handle separately
    // Let's display schedule instead of links table if it's a campaign
} else {
    $stmt = $pdo->prepare('SELECT * FROM task_links WHERE task_id = ? ORDER BY id');
    $stmt->execute([$task_id]);
    $links = $stmt->fetchAll();
}

// Handle updates to drip schedule date
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_drip_date') {
    try {
        if (!$task['is_drip_feed']) {
            throw new Exception("This is not a drip feed task.");
        }
        
        $newDate = $_POST['next_run_at'] ?? '';
        // Validate date format (simple check)
        if (!strtotime($newDate)) {
            throw new Exception("Invalid date format.");
        }
        
        // Convert to UTC for database storage (assuming input is local/browser time, but for MVP treating as UTC or server time)
        // Ideally we should handle timezone conversion, but sticking to server time for now.
        $formattedDate = date('Y-m-d H:i:s', strtotime($newDate));
        
        $stmt = $pdo->prepare("UPDATE tasks SET next_run_at = ? WHERE id = ?");
        $stmt->execute([$formattedDate, $task_id]);
        
        $success = "Next batch schedule updated to $formattedDate";
        
        // Refresh task details
        $stmt = $pdo->prepare('SELECT * FROM tasks WHERE id = ? AND user_id = ?');
        $stmt->execute([$task_id, $_SESSION['uid']]);
        $task = $stmt->fetch();
        
    } catch (Exception $e) {
        $error = "Failed to update schedule: " . $e->getMessage();
    }
}

// Handle manual drip force send
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'force_drip_send') {
    try {
        if (!$task['is_drip_feed']) {
            throw new Exception("This is not a drip feed task.");
        }
        
        require_once __DIR__ . '/src/TaskService.php';
        $result = TaskService::processDripFeedBatch($task_id);
        
        if (isset($result['status']) && $result['status'] === 'completed') {
            $success = "Drip feed completed! All links submitted.";
        } else {
            $count = $result['count'] ?? 0;
            $success = "Successfully forced batch of $count links.";
        }
        
        // Refresh task details
        $stmt = $pdo->prepare('SELECT * FROM tasks WHERE id = ? AND user_id = ?');
        $stmt->execute([$task_id, $_SESSION['uid']]);
        $task = $stmt->fetch();
        
        // Refresh links
        if ($task['type'] !== 'traffic_campaign') {
            $stmt = $pdo->prepare('SELECT * FROM task_links WHERE task_id = ? ORDER BY id');
            $stmt->execute([$task_id]);
            $links = $stmt->fetchAll();
        }
        
    } catch (Exception $e) {
        $error = "Failed to force send: " . $e->getMessage();
    }
}

// Calculate stats
if ($task['type'] === 'traffic_campaign') {
    $total = array_sum(array_column($schedule, 'quantity'));
    $completedQty = 0;
    $pendingQty = 0;
    $failedQty = 0;
    
    foreach ($schedule as $run) {
        if ($run['status'] === 'completed') $completedQty += $run['quantity'];
        elseif ($run['status'] === 'failed') $failedQty += $run['quantity'];
        else $pendingQty += $run['quantity'];
    }
    
    // Map to boxes
    // Total Links -> Total Visitors
    // Indexed -> Delivered
    // Unindexed -> Failed
    // Pending -> Pending
    $box1_label = 'Total Visitors'; $box1_val = $total;
    $box2_label = 'Delivered'; $box2_val = $completedQty;
    $box3_label = 'Failed'; $box3_val = $failedQty;
    $box4_label = 'Pending'; $box4_val = $pendingQty;
    
    $progress = $total > 0 ? round(($completedQty / $total) * 100) : 0;
    
} else {
    // Standard logic
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
    
    $box1_label = 'Total Links'; $box1_val = $total;
    $box2_label = 'Indexed'; $box2_val = $indexed;
    $box3_label = 'Unindexed'; $box3_val = $unindexed;
    $box4_label = 'Pending'; $box4_val = $pending;
    
    $progress = $total > 0 ? round((($indexed + $unindexed) / $total) * 100) : 0;
}

include __DIR__ . '/includes/header_new.php';
?>

<div class="max-w-7xl mx-auto px-6 lg:px-8 py-10" x-data="{ showDetails: false, detailsContent: '' }">
    <div class="flex flex-col md:flex-row justify-between items-center mb-8 gap-4">
        <?php if (isset($success)): ?>
            <div class="mb-6 bg-green-500/10 border border-green-500/20 text-green-400 p-4 rounded-lg flex items-center gap-3">
                <i class="fas fa-check-circle"></i>
                <?php echo htmlspecialchars($success); ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($error)): ?>
            <div class="mb-6 bg-red-500/10 border border-red-500/20 text-red-400 p-4 rounded-lg flex items-center gap-3">
                <i class="fas fa-exclamation-circle"></i>
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <div>
            <nav class="flex items-center text-sm text-gray-400 mb-2">
                <a href="/tasks.php" class="hover:text-white transition-colors">Tasks</a>
                <span class="mx-2">/</span>
                <span class="text-white">#<?php echo $task_id; ?></span>
            </nav>
            <h1 class="text-3xl font-bold text-white flex items-center gap-3">
                <?php echo htmlspecialchars($task['title'] ?: 'Untitled Task'); ?>
                <?php if (!empty($task['is_drip_feed'])): ?>
                    <span class="px-3 py-1 rounded-lg text-sm bg-blue-500/20 text-blue-400 border border-blue-500/30 font-bold flex items-center gap-2">
                        <i class="fas fa-tint"></i> Drip Feed
                    </span>
                <?php endif; ?>
            </h1>
        </div>
        <div>
            <span class="px-3 py-1 rounded-full text-sm font-bold uppercase <?php echo $task['status'] === 'completed' ? 'bg-green-500/10 text-green-400 border border-green-500/20' : ($task['status'] === 'processing' ? 'bg-blue-500/10 text-blue-400 border border-blue-500/20' : 'bg-yellow-500/10 text-yellow-400 border border-yellow-500/20'); ?>">
                <?php echo ucfirst($task['status']); ?>
            </span>

            <?php if (!empty($task['is_drip_feed']) && $task['status'] !== 'completed'): ?>
                <form method="POST" class="inline-block ml-2" onsubmit="return confirm('Are you sure you want to force the next batch?');">
                    <input type="hidden" name="action" value="force_drip_send">
                    <button type="submit" class="px-3 py-1 rounded-lg bg-primary-600 text-white hover:bg-primary-700 text-sm font-bold transition-colors flex items-center gap-2">
                        <i class="fas fa-paper-plane"></i> Force Send Batch
                    </button>
                </form>
            <?php endif; ?>
        </div>
    </div>
    
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
        <div class="card rounded-xl p-4 text-center">
            <h3 class="text-3xl font-bold text-primary-500"><?php echo $box1_val; ?></h3>
            <div class="text-xs text-gray-500 uppercase font-bold mt-1"><?php echo $box1_label; ?></div>
        </div>
        <div class="card rounded-xl p-4 text-center">
            <h3 class="text-3xl font-bold text-green-400"><?php echo $box2_val; ?></h3>
            <div class="text-xs text-gray-500 uppercase font-bold mt-1"><?php echo !empty($task['is_drip_feed']) ? 'Submitted' : $box2_label; ?></div>
        </div>
        <div class="card rounded-xl p-4 text-center">
            <h3 class="text-3xl font-bold text-yellow-400"><?php echo $box3_val; ?></h3>
            <div class="text-xs text-gray-500 uppercase font-bold mt-1"><?php echo $box3_label; ?></div>
        </div>
        <div class="card rounded-xl p-4 text-center">
            <h3 class="text-3xl font-bold text-gray-400"><?php echo $box4_val; ?></h3>
            <div class="text-xs text-gray-500 uppercase font-bold mt-1"><?php echo $box4_label; ?></div>
        </div>
    </div>
    
    <?php if (!empty($task['is_drip_feed'])): ?>
        <div class="mb-8 card rounded-xl p-6 border-blue-500/20 bg-blue-500/5 flex items-center gap-4">
            <div class="w-12 h-12 rounded-full bg-blue-500/10 flex items-center justify-center text-blue-400 shrink-0">
                <i class="fas fa-info-circle text-xl"></i>
            </div>
            <div class="flex justify-between items-center">
                <div>
                    <h4 class="text-white font-bold mb-1">Drip Feed Active</h4>
                    <p class="text-sm text-gray-400">
                        This task is being drip-fed over time. 
                        <?php if ($task['next_run_at']): ?>
                            Next batch scheduled for: <span class="text-white font-mono"><?php echo date('M j, H:i', strtotime($task['next_run_at'])); ?></span>
                        <?php endif; ?>
                    </p>
                </div>
                
                <?php if ($task['status'] !== 'completed'): ?>
                    <div x-data="{ editing: false, newDate: '<?php echo $task['next_run_at'] ? date('Y-m-d\TH:i', strtotime($task['next_run_at'])) : ''; ?>' }">
                        <button @click="editing = !editing" x-show="!editing" class="text-xs text-blue-400 hover:text-blue-300 underline ml-2">
                            Change Schedule
                        </button>
                        
                        <form method="POST" x-show="editing" class="flex items-center gap-2 mt-2" x-cloak>
                            <input type="hidden" name="action" value="update_drip_date">
                            <input type="datetime-local" name="next_run_at" x-model="newDate" class="bg-black/30 border border-blue-500/30 rounded px-2 py-1 text-xs text-white focus:outline-none focus:border-blue-500">
                            <button type="submit" class="px-2 py-1 rounded bg-blue-500 text-white text-xs hover:bg-blue-600 transition-colors">Save</button>
                            <button type="button" @click="editing = false" class="px-2 py-1 rounded border border-white/10 text-gray-400 text-xs hover:bg-white/10 transition-colors">Cancel</button>
                        </form>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
    
    <?php if ($task['type'] === 'traffic_campaign'): 
        $meta = json_decode($task['meta_data'] ?? '{}', true);
    ?>
        <!-- Campaign Info -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
             <div class="card rounded-xl p-4 flex items-center gap-4">
                <div class="w-12 h-12 rounded-full bg-blue-500/10 flex items-center justify-center text-blue-400">
                    <i class="fas fa-globe text-xl"></i>
                </div>
                <div>
                    <div class="text-xs text-gray-500 uppercase font-bold">Target Geo</div>
                    <div class="text-white font-bold text-lg"><?php echo htmlspecialchars($meta['country'] ?? 'Worldwide'); ?></div>
                </div>
             </div>
             <div class="card rounded-xl p-4 flex items-center gap-4">
                <div class="w-12 h-12 rounded-full bg-purple-500/10 flex items-center justify-center text-purple-400">
                    <i class="fas fa-link text-xl"></i>
                </div>
                <div class="overflow-hidden">
                    <div class="text-xs text-gray-500 uppercase font-bold">Traffic Source</div>
                    <div class="text-white font-bold truncate" title="<?php echo htmlspecialchars($meta['referring_url'] ?? 'Direct'); ?>">
                        <?php 
                        if (($meta['type_of_traffic'] ?? '') == '1') echo 'Google: ' . ($meta['google_keyword'] ?? '-');
                        elseif (($meta['type_of_traffic'] ?? '') == '2') echo 'Ref: ' . ($meta['referring_url'] ?? '-');
                        else echo 'Direct / None';
                        ?>
                    </div>
                </div>
             </div>
             <div class="card rounded-xl p-4 flex items-center gap-4">
                <div class="w-12 h-12 rounded-full bg-pink-500/10 flex items-center justify-center text-pink-400">
                    <i class="fas fa-clock text-xl"></i>
                </div>
                <div>
                    <div class="text-xs text-gray-500 uppercase font-bold">Duration</div>
                    <div class="text-white font-bold text-lg"><?php echo htmlspecialchars($meta['days'] ?? '1'); ?> Days</div>
                </div>
             </div>
        </div>

        <div class="card rounded-xl overflow-hidden">
            <div class="px-6 py-4 border-b border-white/5 bg-white/5 flex justify-between items-center">
                <h5 class="font-bold text-white">Traffic Delivery (Viral Wave)</h5>
                <div class="flex gap-3 text-xs">
                    <div class="flex items-center gap-1"><div class="w-2 h-2 rounded-full bg-green-500"></div> Delivered</div>
                    <div class="flex items-center gap-1"><div class="w-2 h-2 rounded-full bg-blue-500"></div> Processing</div>
                    <div class="flex items-center gap-1"><div class="w-2 h-2 rounded-full bg-primary-600"></div> Pending</div>
                </div>
            </div>
            <div class="p-6">
                <!-- Chart Visualization -->
                <div class="h-64 flex items-end gap-1 w-full">
                    <?php 
                    $maxQty = 0;
                    foreach ($schedule as $run) {
                        if ($run['quantity'] > $maxQty) $maxQty = $run['quantity'];
                    }
                    if ($maxQty == 0) $maxQty = 1; // Prevent division by zero
                    
                    foreach ($schedule as $run): 
                        $heightPercent = ($run['quantity'] / $maxQty) * 100;
                        // Ensure minimum visible height
                        if ($heightPercent < 5) $heightPercent = 5;
                        
                        $barColor = 'bg-primary-600'; // Default (pending)
                        if ($run['status'] === 'completed') $barColor = 'bg-green-500 shadow-[0_0_10px_rgba(34,197,94,0.5)]';
                        if ($run['status'] === 'processing') $barColor = 'bg-blue-500 shadow-[0_0_10px_rgba(59,130,246,0.5)]';
                        if ($run['status'] === 'failed') $barColor = 'bg-red-500';
                    ?>
                        <div class="flex-1 flex flex-col items-center group relative h-full justify-end">
                            <!-- Tooltip -->
                            <div class="absolute bottom-full mb-2 opacity-0 group-hover:opacity-100 transition-opacity bg-gray-900 text-white text-xs p-2 rounded whitespace-nowrap z-20 border border-white/10 shadow-xl pointer-events-none">
                                <div class="font-bold text-primary-400"><?php echo $run['quantity']; ?> visitors</div>
                                <div class="text-gray-400"><?php echo date('M j, H:i', strtotime($run['scheduled_at'])); ?></div>
                                <div class="uppercase text-[10px] mt-1 font-bold"><?php echo $run['status']; ?></div>
                            </div>
                            
                            <div class="<?php echo $barColor; ?> w-full rounded-t opacity-90 hover:opacity-100 transition-all duration-300 relative group-hover:scale-y-105 origin-bottom" style="height: <?php echo $heightPercent; ?>%"></div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <div class="flex justify-between text-xs text-gray-500 mt-4 font-mono border-t border-white/5 pt-2">
                    <span>Start: <?php echo !empty($schedule) ? date('M j', strtotime($schedule[0]['scheduled_at'])) : '-'; ?></span>
                    <span>End: <?php echo !empty($schedule) ? date('M j', strtotime(end($schedule)['scheduled_at'])) : '-'; ?></span>
                </div>
            </div>
            
            <div class="px-6 py-4 border-t border-white/5 bg-white/5">
                <h5 class="font-bold text-white mb-4">Detailed Schedule</h5>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="bg-white/5 border-b border-white/5">
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-400 uppercase tracking-wider">Scheduled Time</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-400 uppercase tracking-wider">Visitors</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-400 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-400 uppercase tracking-wider">Details</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/5">
                        <?php foreach ($schedule as $run): ?>
                            <tr class="hover:bg-white/5 transition-colors">
                                <td class="px-6 py-4 text-sm text-gray-300">
                                    <?php echo date('M j, H:i', strtotime($run['scheduled_at'])); ?>
                                </td>
                                <td class="px-6 py-4 text-sm text-white font-mono">
                                    <?php echo $run['quantity']; ?>
                                </td>
                                <td class="px-6 py-4">
                                    <?php
                                    $badge_class = 'bg-gray-500/10 text-gray-400 border-gray-500/20';
                                    if ($run['status'] === 'completed') $badge_class = 'bg-green-500/10 text-green-400 border-green-500/20';
                                    if ($run['status'] === 'failed') $badge_class = 'bg-red-500/10 text-red-400 border-red-500/20';
                                    if ($run['status'] === 'processing') $badge_class = 'bg-blue-500/10 text-blue-400 border-blue-500/20';
                                    ?>
                                    <span class="px-2 py-0.5 rounded text-[10px] uppercase font-bold border <?php echo $badge_class; ?>">
                                        <?php echo ucfirst($run['status']); ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-xs text-gray-500">
                                    <?php echo $run['provider_order_id'] ? 'Order #' . $run['provider_order_id'] : '-'; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php else: ?>
        <!-- Standard Links View -->
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
    <?php endif; ?>
    
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
