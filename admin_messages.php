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

// Ensure table exists (temporary auto-migration for convenience)
try {
    $pdo->query("SELECT 1 FROM contact_messages LIMIT 1");
} catch (Exception $e) {
    // Table likely doesn't exist
    // This is a fallback if the user didn't run migration manually
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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Support Messages - Rapid Indexer Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="/assets/css/style.css" rel="stylesheet">
</head>
<body>
    <?php include __DIR__ . '/includes/navbar.php'; ?>
    
    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-0">Support Messages</h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="/admin.php">Dashboard</a></li>
                        <li class="breadcrumb-item active">Messages</li>
                    </ol>
                </nav>
            </div>
            
            <div class="btn-group">
                <a href="?status=all" class="btn btn-outline-secondary <?php echo $status_filter === 'all' ? 'active' : ''; ?>">All</a>
                <a href="?status=new" class="btn btn-outline-primary <?php echo $status_filter === 'new' ? 'active' : ''; ?>">New</a>
                <a href="?status=read" class="btn btn-outline-info <?php echo $status_filter === 'read' ? 'active' : ''; ?>">Read</a>
                <a href="?status=replied" class="btn btn-outline-success <?php echo $status_filter === 'replied' ? 'active' : ''; ?>">Replied</a>
            </div>
        </div>
        
        <div class="card">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th>Status</th>
                                <th>Date</th>
                                <th>User / Email</th>
                                <th>Subject</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($messages)): ?>
                                <tr>
                                    <td colspan="5" class="text-center py-5 text-muted">
                                        <i class="far fa-envelope fa-3x mb-3"></i>
                                        <p class="mb-0">No messages found.</p>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($messages as $msg): ?>
                                    <tr class="<?php echo $msg['status'] === 'new' ? 'fw-bold bg-light' : ''; ?>">
                                        <td>
                                            <?php
                                            $badge = 'secondary';
                                            if ($msg['status'] === 'new') $badge = 'primary';
                                            if ($msg['status'] === 'read') $badge = 'info';
                                            if ($msg['status'] === 'replied') $badge = 'success';
                                            ?>
                                            <span class="badge bg-<?php echo $badge; ?>">
                                                <?php echo ucfirst($msg['status']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="small"><?php echo date('M j', strtotime($msg['created_at'])); ?></div>
                                            <div class="small text-muted"><?php echo date('H:i', strtotime($msg['created_at'])); ?></div>
                                        </td>
                                        <td>
                                            <div><?php echo htmlspecialchars($msg['name']); ?></div>
                                            <div class="small text-muted">
                                                <a href="mailto:<?php echo htmlspecialchars($msg['email']); ?>" class="text-decoration-none">
                                                    <?php echo htmlspecialchars($msg['email']); ?>
                                                </a>
                                            </div>
                                            <?php if ($msg['user_id']): ?>
                                                <div class="small text-primary">User #<?php echo $msg['user_id']; ?></div>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo htmlspecialchars($msg['subject']); ?></td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-primary" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#messageModal<?php echo $msg['id']; ?>">
                                                View
                                            </button>
                                        </td>
                                    </tr>
                                    
                                    <!-- View Modal -->
                                    <div class="modal fade" id="messageModal<?php echo $msg['id']; ?>" tabindex="-1">
                                        <div class="modal-dialog modal-lg">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title"><?php echo htmlspecialchars($msg['subject']); ?></h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="mb-3">
                                                        <div class="row">
                                                            <div class="col-md-6">
                                                                <strong>From:</strong> <?php echo htmlspecialchars($msg['name']); ?> 
                                                                &lt;<?php echo htmlspecialchars($msg['email']); ?>&gt;
                                                            </div>
                                                            <div class="col-md-6 text-end text-muted">
                                                                <?php echo date('F j, Y g:i A', strtotime($msg['created_at'])); ?>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="p-3 bg-light rounded border mb-3">
                                                        <?php echo nl2br(htmlspecialchars($msg['message'])); ?>
                                                    </div>
                                                    
                                                    <div class="d-flex justify-content-end gap-2">
                                                        <a href="mailto:<?php echo htmlspecialchars($msg['email']); ?>?subject=Re: <?php echo urlencode($msg['subject']); ?>" 
                                                           class="btn btn-primary">
                                                            <i class="fas fa-reply me-1"></i> Reply via Email
                                                        </a>
                                                        
                                                        <form method="POST">
                                                            <input type="hidden" name="action" value="update_status">
                                                            <input type="hidden" name="id" value="<?php echo $msg['id']; ?>">
                                                            <?php if ($msg['status'] !== 'read' && $msg['status'] !== 'replied'): ?>
                                                                <button type="submit" name="status" value="read" class="btn btn-info text-white">Mark Read</button>
                                                            <?php endif; ?>
                                                            <?php if ($msg['status'] !== 'replied'): ?>
                                                                <button type="submit" name="status" value="replied" class="btn btn-success">Mark Replied</button>
                                                            <?php endif; ?>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

