<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/src/Db.php';

$task_id = intval($_GET['id'] ?? 0);
if (!$task_id) {
    echo '<div class="alert alert-danger">Invalid task ID</div>';
    exit;
}

try {
    $pdo = Db::conn();
    
    // Get task details
    $stmt = $pdo->prepare('SELECT * FROM tasks WHERE id = ? AND user_id = ?');
    $stmt->execute([$task_id, $_SESSION['uid']]);
    $task = $stmt->fetch();
    
    if (!$task) {
        echo '<div class="alert alert-danger">Task not found</div>';
        exit;
    }
    
    // Get task links
    $stmt = $pdo->prepare('SELECT * FROM task_links WHERE task_id = ? ORDER BY id');
    $stmt->execute([$task_id]);
    $links = $stmt->fetchAll();
    
    echo '<div class="row">';
    echo '<div class="col-md-6">';
    echo '<h6>Task Information</h6>';
    echo '<table class="table table-sm">';
    echo '<tr><td><strong>ID:</strong></td><td>' . $task['id'] . '</td></tr>';
    echo '<tr><td><strong>Type:</strong></td><td>' . ucfirst($task['type']) . '</td></tr>';
    echo '<tr><td><strong>Engine:</strong></td><td>' . ucfirst($task['search_engine']) . '</td></tr>';
    echo '<tr><td><strong>Status:</strong></td><td><span class="badge bg-' . ($task['status'] === 'completed' ? 'success' : ($task['status'] === 'processing' ? 'primary' : 'warning')) . '">' . ucfirst($task['status']) . '</span></td></tr>';
    echo '<tr><td><strong>VIP:</strong></td><td>' . ($task['vip'] ? '<span class="badge bg-warning">Yes</span>' : 'No') . '</td></tr>';
    echo '<tr><td><strong>Created:</strong></td><td>' . date('M j, Y g:i A', strtotime($task['created_at'])) . '</td></tr>';
    if ($task['completed_at']) {
        echo '<tr><td><strong>Completed:</strong></td><td>' . date('M j, Y g:i A', strtotime($task['completed_at'])) . '</td></tr>';
    }
    if ($task['title']) {
        echo '<tr><td><strong>Title:</strong></td><td>' . htmlspecialchars($task['title']) . '</td></tr>';
    }
    echo '</table>';
    echo '</div>';
    
    echo '<div class="col-md-6">';
    echo '<h6>Statistics</h6>';
    echo '<table class="table table-sm">';
    echo '<tr><td><strong>Total Links:</strong></td><td>' . count($links) . '</td></tr>';
    
    $indexed = array_filter($links, fn($l) => $l['status'] === 'indexed');
    $unindexed = array_filter($links, fn($l) => $l['status'] === 'unindexed');
    $pending = array_filter($links, fn($l) => $l['status'] === 'pending');
    $error = array_filter($links, fn($l) => $l['status'] === 'error');
    
    echo '<tr><td><strong>Indexed:</strong></td><td><span class="text-success">' . count($indexed) . '</span></td></tr>';
    echo '<tr><td><strong>Unindexed:</strong></td><td><span class="text-warning">' . count($unindexed) . '</span></td></tr>';
    echo '<tr><td><strong>Pending:</strong></td><td><span class="text-info">' . count($pending) . '</span></td></tr>';
    if (count($error) > 0) {
        echo '<tr><td><strong>Errors:</strong></td><td><span class="text-danger">' . count($error) . '</span></td></tr>';
    }
    
    $progress = count($links) > 0 ? round(((count($indexed) + count($unindexed)) / count($links)) * 100) : 0;
    echo '<tr><td><strong>Progress:</strong></td><td>' . $progress . '%</td></tr>';
    echo '</table>';
    echo '</div>';
    echo '</div>';
    
    if (!empty($links)) {
        echo '<hr>';
        echo '<h6>Link Details</h6>';
        echo '<div class="table-responsive" style="max-height: 400px; overflow-y: auto;">';
        echo '<table class="table table-sm table-striped">';
        echo '<thead class="sticky-top bg-light">';
        echo '<tr><th>URL</th><th>Status</th><th>Error Code</th><th>Checked At</th><th>Result Data</th></tr>';
        echo '</thead>';
        echo '<tbody>';
        
        foreach ($links as $link) {
            $status_class = '';
            $status_text = $link['status'];
            
            switch ($link['status']) {
                case 'indexed':
                    $status_class = 'text-success';
                    break;
                case 'unindexed':
                    $status_class = 'text-warning';
                    break;
                case 'pending':
                    $status_class = 'text-info';
                    break;
                case 'error':
                    $status_class = 'text-danger';
                    break;
            }
            
            echo '<tr>';
            echo '<td style="max-width: 300px; word-break: break-all;">' . htmlspecialchars($link['url']) . '</td>';
            echo '<td><span class="' . $status_class . '">' . ucfirst($status_text) . '</span></td>';
            echo '<td>' . ($link['error_code'] ?: 'N/A') . '</td>';
            echo '<td>' . ($link['checked_at'] ? date('M j, g:i A', strtotime($link['checked_at'])) : 'Not checked') . '</td>';
            echo '<td>';
            if ($link['result_data']) {
                $result_data = json_decode($link['result_data'], true);
                echo '<button class="btn btn-sm btn-outline-info" onclick="showResultData(\'' . htmlspecialchars(json_encode($result_data)) . '\')">View</button>';
            } else {
                echo 'N/A';
            }
            echo '</td>';
            echo '</tr>';
        }
        
        echo '</tbody>';
        echo '</table>';
        echo '</div>';
    }
    
} catch (Exception $e) {
    echo '<div class="alert alert-danger">Error: ' . htmlspecialchars($e->getMessage()) . '</div>';
}
?>

<script>
function showResultData(data) {
    const modal = new bootstrap.Modal(document.getElementById('resultDataModal'));
    document.getElementById('resultDataContent').innerHTML = '<pre>' + JSON.stringify(JSON.parse(data), null, 2) + '</pre>';
    modal.show();
}
</script>

<!-- Result Data Modal -->
<div class="modal fade" id="resultDataModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Result Data</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="resultDataContent">
                <!-- Content will be loaded here -->
            </div>
        </div>
    </div>
</div>
