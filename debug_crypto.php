<?php
// Allow direct access for debugging, bypass session check for this script ONLY
define('DEBUG_MODE', true);

// Error Reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>Rapid Indexer - Cryptomus Debugger</h1>";

// 1. Check Environment
echo "<h2>1. Environment Check</h2>";
echo "PHP Version: " . phpversion() . "<br>";
echo "CURL Extension: " . (extension_loaded('curl') ? '<span style="color:green">INSTALLED</span>' : '<span style="color:red">MISSING</span>') . "<br>";
echo "JSON Extension: " . (extension_loaded('json') ? '<span style="color:green">INSTALLED</span>' : '<span style="color:red">MISSING</span>') . "<br>";

// 2. Check Config/Credentials
echo "<h2>2. Configuration Check</h2>";
require_once __DIR__ . '/src/SettingsService.php';
require_once __DIR__ . '/src/Db.php';
require_once __DIR__ . '/config/config.php'; // Ensure constants are loaded

try {
    $pdo = Db::conn();
    echo "Database Connection: <span style='color:green'>SUCCESS</span><br>";
} catch (Exception $e) {
    die("Database Connection: <span style='color:red'>FAILED</span> - " . $e->getMessage());
}

$merchantId = SettingsService::getDecrypted('cryptomus_merchant_id');
if (!$merchantId && defined('CRYPTOMUS_MERCHANT_ID')) $merchantId = CRYPTOMUS_MERCHANT_ID;

$apiKey = SettingsService::getDecrypted('cryptomus_api_key');
if (!$apiKey && defined('CRYPTOMUS_PAYMENT_KEY')) $apiKey = CRYPTOMUS_PAYMENT_KEY;

echo "Merchant ID: " . ($merchantId ? "Found (" . substr($merchantId, 0, 4) . "...)" : "<span style='color:red'>MISSING</span>") . "<br>";
echo "API Key: " . ($apiKey ? "Found (Length: " . strlen($apiKey) . ")" : "<span style='color:red'>MISSING</span>") . "<br>";

if (!$merchantId || !$apiKey) {
    die("<h3 style='color:red'>STOPPING: Missing Credentials</h3>");
}

// 3. Check Pending Payments
echo "<h2>3. Pending Payments</h2>";
$stmt = $pdo->prepare("SELECT * FROM payments WHERE method = 'cryptomus' AND status = 'pending' ORDER BY created_at DESC LIMIT 5");
$stmt->execute();
$pending = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($pending)) {
    echo "No pending Cryptomus payments found in database.<br>";
} else {
    echo "<table border='1' cellpadding='5'><tr><th>ID</th><th>Amount</th><th>Order ID</th><th>Date</th><th>Action</th></tr>";
    foreach ($pending as $p) {
        echo "<tr>";
        echo "<td>" . $p['id'] . "</td>";
        echo "<td>$" . $p['amount'] . "</td>";
        echo "<td>" . $p['paypal_order_id'] . "</td>";
        echo "<td>" . $p['created_at'] . "</td>";
        echo "<td><form method='POST' style='display:inline'><input type='hidden' name='check_id' value='".$p['paypal_order_id']."'><button type='submit'>Check Status</button></form></td>";
        echo "</tr>";
    }
    echo "</table>";
}

// 4. Test API Connectivity
echo "<h2>4. API Connectivity Test</h2>";
require_once __DIR__ . '/src/CryptomusClient.php';

$client = new CryptomusClient($merchantId, $apiKey);

// If a check was requested
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['check_id'])) {
    echo "<h3>Checking Order: " . htmlspecialchars($_POST['check_id']) . "</h3>";
    try {
        $status = $client->getPaymentStatus(['order_id' => $_POST['check_id']]);
        echo "<pre>" . json_encode($status, JSON_PRETTY_PRINT) . "</pre>";
    } catch (Exception $e) {
        echo "<div style='color:red; border:1px solid red; padding:10px'>API Error: " . $e->getMessage() . "</div>";
    }
} else {
    // Just try to get service list or payment list to verify auth
    echo "Attempting to fetch Payment List (Top 1)...<br>";
    try {
        // We use getPaymentList but with empty cursor, assuming it works
        // Or use services list if available, but payment list is what we use in sync
        $response = $client->getPaymentList([]); 
        echo "API Response HTTP Status: <span style='color:green'>OK</span><br>";
        echo "Response Data Sample:<br>";
        echo "<textarea rows='10' cols='80'>" . json_encode($response, JSON_PRETTY_PRINT) . "</textarea>";
    } catch (Exception $e) {
        echo "<div style='color:red; border:1px solid red; padding:10px'>
        <strong>API Connection Failed:</strong> " . $e->getMessage() . "<br>
        Check your Merchant ID and API Key.
        </div>";
    }
}
?>

