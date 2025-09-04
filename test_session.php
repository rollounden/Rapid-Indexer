<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Session Test</h1>";

// Check session configuration
echo "<h2>Session Configuration</h2>";
echo "Session save path: " . session_save_path() . "<br>";
echo "Session name: " . session_name() . "<br>";
echo "Session status: " . (session_status() === PHP_SESSION_ACTIVE ? 'Active' : 'Not Active') . "<br>";

// Start session
session_start();

echo "<h2>Current Session Data</h2>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

echo "<h2>Session ID</h2>";
echo "Session ID: " . session_id() . "<br>";

// Test setting session data
if (isset($_POST['test_login'])) {
    $_SESSION['uid'] = 1;
    $_SESSION['email'] = 'test@example.com';
    $_SESSION['role'] = 'user';
    echo "<p style='color: green;'>✅ Session data set!</p>";
    echo "<p>Refresh the page to see if session persists.</p>";
}

if (isset($_POST['clear_session'])) {
    session_destroy();
    echo "<p style='color: red;'>❌ Session cleared!</p>";
}

echo "<h2>Test Actions</h2>";
?>
<form method="POST">
    <button type="submit" name="test_login">Test Login (Set Session)</button>
    <button type="submit" name="clear_session">Clear Session</button>
</form>

<hr>
<p><a href="/tasks.php">Test Tasks Page</a> | <a href="/payments.php">Test Payments Page</a> | <a href="/admin.php">Test Admin Page</a></p>
