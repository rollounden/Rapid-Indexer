<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Error Check</h2>";

// Test basic includes
echo "<h3>Testing Basic Includes:</h3>";

try {
    require_once __DIR__ . '/config/config.php';
    echo "<p style='color: green;'>✅ config.php loaded</p>";
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ config.php error: " . htmlspecialchars($e->getMessage()) . "</p>";
}

try {
    require_once __DIR__ . '/src/Db.php';
    echo "<p style='color: green;'>✅ Db.php loaded</p>";
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Db.php error: " . htmlspecialchars($e->getMessage()) . "</p>";
}

// Test service classes
echo "<h3>Testing Service Classes:</h3>";

try {
    require_once __DIR__ . '/src/TaskService.php';
    echo "<p style='color: green;'>✅ TaskService.php loaded</p>";
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ TaskService.php error: " . htmlspecialchars($e->getMessage()) . "</p>";
}

try {
    require_once __DIR__ . '/src/PaymentService.php';
    echo "<p style='color: green;'>✅ PaymentService.php loaded</p>";
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ PaymentService.php error: " . htmlspecialchars($e->getMessage()) . "</p>";
}

try {
    require_once __DIR__ . '/src/CreditsService.php';
    echo "<p style='color: green;'>✅ CreditsService.php loaded</p>";
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ CreditsService.php error: " . htmlspecialchars($e->getMessage()) . "</p>";
}

// Test database connection
echo "<h3>Testing Database Connection:</h3>";
try {
    $pdo = Db::conn();
    echo "<p style='color: green;'>✅ Database connection successful</p>";
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Database error: " . htmlspecialchars($e->getMessage()) . "</p>";
}

// Test navbar include
echo "<h3>Testing Navbar Include:</h3>";
try {
    ob_start();
    include __DIR__ . '/includes/navbar.php';
    $navbar_output = ob_get_clean();
    echo "<p style='color: green;'>✅ navbar.php loaded</p>";
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ navbar.php error: " . htmlspecialchars($e->getMessage()) . "</p>";
}

// Test session
echo "<h3>Testing Session:</h3>";
try {
    session_start();
    echo "<p style='color: green;'>✅ Session started</p>";
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Session error: " . htmlspecialchars($e->getMessage()) . "</p>";
}

// Test specific page includes
echo "<h3>Testing Page-Specific Code:</h3>";

// Test tasks.php specific code
try {
    $page = max(1, intval(1));
    $per_page = 10;
    $offset = ($page - 1) * $per_page;
    
    $stmt = $pdo->prepare('
        SELECT t.*, COUNT(tl.id) as total_links, 
               SUM(CASE WHEN tl.status = "completed" THEN 1 ELSE 0 END) as completed_links,
               SUM(CASE WHEN tl.status = "failed" THEN 1 ELSE 0 END) as failed_links
        FROM tasks t 
        LEFT JOIN task_links tl ON t.id = tl.task_id 
        WHERE t.user_id = ? 
        GROUP BY t.id 
        ORDER BY t.created_at DESC 
        LIMIT 10 OFFSET 0
    ');
    echo "<p style='color: green;'>✅ Tasks query prepared</p>";
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Tasks query error: " . htmlspecialchars($e->getMessage()) . "</p>";
}

// Test payments.php specific code
try {
    $stmt = $pdo->prepare('
        SELECT p.*, 
               CASE 
                   WHEN p.status = "completed" THEN p.credits_amount 
                   ELSE 0 
               END as credits_received
        FROM payments p 
        WHERE p.user_id = ? 
        ORDER BY p.created_at DESC 
        LIMIT 20
    ');
    echo "<p style='color: green;'>✅ Payments query prepared</p>";
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Payments query error: " . htmlspecialchars($e->getMessage()) . "</p>";
}

echo "<hr>";
echo "<p><a href='/dashboard.php'>Test Dashboard</a> | <a href='/tasks.php'>Test Tasks</a> | <a href='/payments.php'>Test Payments</a> | <a href='/admin.php'>Test Admin</a></p>";
?>
