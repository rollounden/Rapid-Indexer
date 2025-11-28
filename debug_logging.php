<?php
require_once __DIR__ . '/src/Db.php';
require_once __DIR__ . '/src/CryptomusService.php';

echo "Starting Manual Debug...\n";

// 1. Check if logs directory is writable
$logDir = __DIR__ . '/storage/logs';
if (!is_dir($logDir)) {
    mkdir($logDir, 0777, true);
    echo "Created logs directory.\n";
} else {
    echo "Logs directory exists. Permissions: " . substr(sprintf('%o', fileperms($logDir)), -4) . "\n";
    chmod($logDir, 0777); // Try to force writable
}

// 2. Try to fetch one pending payment
$pdo = Db::conn();
$stmt = $pdo->prepare("SELECT * FROM payments WHERE method = 'cryptomus' AND status = 'pending' LIMIT 1");
$stmt->execute();
$payment = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$payment) {
    die("No pending Cryptomus payments found in DB to test.\n");
}

echo "Found Pending Payment ID: " . $payment['id'] . " (Order: " . $payment['paypal_order_id'] . ")\n";

// 3. Try to check status
try {
    $service = new CryptomusService();
    echo "Checking status via API...\n";
    $status = $service->checkStatus($payment['paypal_order_id']);
    echo "API returned status: " . $status . "\n";
} catch (Exception $e) {
    echo "ERROR checking status: " . $e->getMessage() . "\n";
}

// 4. Try listing payments
try {
    $service = new CryptomusService();
    echo "Fetching Payment List from API...\n";
    // Use reflection to access private client if needed, or just trust the service wrapper
    // We'll just use the service sync logic but manually
    $count = $service->syncPendingPayments();
    echo "Sync function returned count: $count\n";
} catch (Exception $e) {
    echo "ERROR syncing: " . $e->getMessage() . "\n";
}

// 5. Read back the log if it exists now
if (file_exists($logDir . '/sync_debug.log')) {
    echo "\n--- CONTENT OF sync_debug.log ---\n";
    echo file_get_contents($logDir . '/sync_debug.log');
} else {
    echo "\nSync debug log was NOT created.\n";
}

if (file_exists($logDir . '/manual_check_debug.log')) {
    echo "\n--- CONTENT OF manual_check_debug.log ---\n";
    echo file_get_contents($logDir . '/manual_check_debug.log');
}

