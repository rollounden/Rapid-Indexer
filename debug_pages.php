<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Page Diagnostics</h1>";

// Test 1: Basic includes
echo "<h2>1. Testing Basic Includes</h2>";
try {
    require_once __DIR__ . '/config/config.php';
    echo "✅ config.php loaded<br>";
} catch (Exception $e) {
    echo "❌ config.php failed: " . $e->getMessage() . "<br>";
}

try {
    require_once __DIR__ . '/src/Db.php';
    echo "✅ Db.php loaded<br>";
} catch (Exception $e) {
    echo "❌ Db.php failed: " . $e->getMessage() . "<br>";
}

// Test 2: Database connection
echo "<h2>2. Testing Database Connection</h2>";
try {
    $pdo = Db::conn();
    echo "✅ Database connection successful<br>";
} catch (Exception $e) {
    echo "❌ Database connection failed: " . $e->getMessage() . "<br>";
}

// Test 3: Service classes
echo "<h2>3. Testing Service Classes</h2>";
try {
    require_once __DIR__ . '/src/TaskService.php';
    echo "✅ TaskService.php loaded<br>";
} catch (Exception $e) {
    echo "❌ TaskService.php failed: " . $e->getMessage() . "<br>";
}

try {
    require_once __DIR__ . '/src/PaymentService.php';
    echo "✅ PaymentService.php loaded<br>";
} catch (Exception $e) {
    echo "❌ PaymentService.php failed: " . $e->getMessage() . "<br>";
}

try {
    require_once __DIR__ . '/src/CreditsService.php';
    echo "✅ CreditsService.php loaded<br>";
} catch (Exception $e) {
    echo "❌ CreditsService.php failed: " . $e->getMessage() . "<br>";
}

// Test 4: Constants
echo "<h2>4. Testing Constants</h2>";
$constants = ['PRICE_PER_CREDIT_USD', 'SPEEDYINDEX_BASE_URL', 'SPEEDYINDEX_API_KEY'];
foreach ($constants as $const) {
    if (defined($const)) {
        echo "✅ $const = " . constant($const) . "<br>";
    } else {
        echo "❌ $const not defined<br>";
    }
}

// Test 5: Navbar include
echo "<h2>5. Testing Navbar Include</h2>";
try {
    ob_start();
    include __DIR__ . '/includes/navbar.php';
    $navbar_output = ob_get_clean();
    echo "✅ navbar.php included successfully<br>";
} catch (Exception $e) {
    echo "❌ navbar.php failed: " . $e->getMessage() . "<br>";
}

// Test 6: Simulate tasks.php queries
echo "<h2>6. Testing Tasks Page Queries</h2>";
try {
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
    $stmt->execute([1]);
    $tasks = $stmt->fetchAll();
    echo "✅ Tasks query successful (found " . count($tasks) . " tasks)<br>";
} catch (Exception $e) {
    echo "❌ Tasks query failed: " . $e->getMessage() . "<br>";
}

// Test 7: Simulate payments.php queries
echo "<h2>7. Testing Payments Page Queries</h2>";
try {
    $stmt = $pdo->prepare('SELECT credits_balance FROM users WHERE id = ?');
    $stmt->execute([1]);
    $user_credits = $stmt->fetchColumn();
    echo "✅ User credits query successful (balance: $user_credits)<br>";
} catch (Exception $e) {
    echo "❌ User credits query failed: " . $e->getMessage() . "<br>";
}

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
    $stmt->execute([1]);
    $payments = $stmt->fetchAll();
    echo "✅ Payments query successful (found " . count($payments) . " payments)<br>";
} catch (Exception $e) {
    echo "❌ Payments query failed: " . $e->getMessage() . "<br>";
}

// Test 8: Simulate admin.php queries
echo "<h2>8. Testing Admin Page Queries</h2>";
try {
    $stmt = $pdo->query('SELECT COUNT(*) FROM users');
    $total_users = $stmt->fetchColumn();
    echo "✅ Total users query successful (count: $total_users)<br>";
} catch (Exception $e) {
    echo "❌ Total users query failed: " . $e->getMessage() . "<br>";
}

try {
    $stmt = $pdo->query('SELECT COUNT(*) FROM tasks');
    $total_tasks = $stmt->fetchColumn();
    echo "✅ Total tasks query successful (count: $total_tasks)<br>";
} catch (Exception $e) {
    echo "❌ Total tasks query failed: " . $e->getMessage() . "<br>";
}

// Test 9: Check table structure
echo "<h2>9. Checking Table Structure</h2>";
$tables = ['users', 'tasks', 'task_links', 'payments', 'credit_ledger', 'api_logs'];
foreach ($tables as $table) {
    try {
        $stmt = $pdo->query("DESCRIBE $table");
        $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
        echo "✅ Table $table exists with " . count($columns) . " columns<br>";
    } catch (Exception $e) {
        echo "❌ Table $table failed: " . $e->getMessage() . "<br>";
    }
}

echo "<hr>";
echo "<h2>Summary</h2>";
echo "<p>If you see any ❌ errors above, those are the exact issues causing the 500 errors.</p>";
echo "<p><a href='/tasks.php'>Test Tasks Page</a> | <a href='/payments.php'>Test Payments Page</a> | <a href='/admin.php'>Test Admin Page</a></p>";
?>
