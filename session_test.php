<?php
session_start();

echo "<h2>Session Test</h2>";

echo "<h3>Current Session Data:</h3>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

echo "<h3>Session ID:</h3>";
echo "<p>Session ID: " . session_id() . "</p>";

echo "<h3>Test Login:</h3>";
if (isset($_POST['email']) && isset($_POST['password'])) {
    require_once __DIR__ . '/config/config.php';
    require_once __DIR__ . '/src/Db.php';
    
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    
    try {
        $pdo = Db::conn();
        $stmt = $pdo->prepare('SELECT * FROM users WHERE email = ? LIMIT 1');
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password_hash'])) {
            $_SESSION['uid'] = $user['id'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['role'] = $user['role'];
            
            echo "<p style='color: green;'>✅ Login successful! Session updated.</p>";
            echo "<p>User ID: " . $user['id'] . "</p>";
            echo "<p>Email: " . $user['email'] . "</p>";
            echo "<p>Role: " . $user['role'] . "</p>";
        } else {
            echo "<p style='color: red;'>❌ Invalid credentials</p>";
        }
    } catch (Exception $e) {
        echo "<p style='color: red;'>❌ Error: " . htmlspecialchars($e->getMessage()) . "</p>";
    }
}

echo "<h3>Login Form:</h3>";
?>
<form method="POST">
    <div>
        <label>Email: <input type="email" name="email" required></label>
    </div>
    <div>
        <label>Password: <input type="password" name="password" required></label>
    </div>
    <button type="submit">Test Login</button>
</form>

<hr>
<p><a href="/dashboard.php">Test Dashboard</a> | <a href="/tasks.php">Test Tasks</a> | <a href="/payments.php">Test Payments</a> | <a href="/admin.php">Test Admin</a></p>
