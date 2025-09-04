<?php
require_once __DIR__ . '/config/config.php';

echo "<h2>Database Connection Test</h2>";

echo "<h3>Configuration:</h3>";
echo "<p><strong>Host:</strong> " . DB_HOST . "</p>";
echo "<p><strong>Database:</strong> " . DB_NAME . "</p>";
echo "<p><strong>User:</strong> " . DB_USER . "</p>";
echo "<p><strong>Password:</strong> " . (strlen(DB_PASS) > 0 ? "***" . substr(DB_PASS, -3) : "EMPTY") . "</p>";

echo "<h3>Testing Connection:</h3>";

try {
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
    
    echo "<p style='color: green;'>✅ Database connection successful!</p>";
    
    // Test if tables exist
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "<h3>Existing Tables:</h3>";
    if (empty($tables)) {
        echo "<p style='color: orange;'>⚠️ No tables found. You need to run setup.php</p>";
    } else {
        echo "<ul>";
        foreach ($tables as $table) {
            echo "<li>" . htmlspecialchars($table) . "</li>";
        }
        echo "</ul>";
    }
    
    // Test users table specifically
    if (in_array('users', $tables)) {
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM users");
        $result = $stmt->fetch();
        echo "<p><strong>Users in database:</strong> " . $result['count'] . "</p>";
    }
    
} catch (PDOException $e) {
    echo "<p style='color: red;'>❌ Database connection failed!</p>";
    echo "<p><strong>Error:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
    
    // Common error solutions
    echo "<h3>Common Solutions:</h3>";
    echo "<ul>";
    echo "<li>Check if the database password is correct in config/config.php</li>";
    echo "<li>Make sure the database name and username are correct</li>";
    echo "<li>Verify the database exists in your Hostinger control panel</li>";
    echo "<li>Check if the database user has proper permissions</li>";
    echo "</ul>";
}

echo "<hr>";
echo "<p><a href='/setup.php'>Run Database Setup</a> | <a href='/login.php'>Go to Login</a></p>";
?>
