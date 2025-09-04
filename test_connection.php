<?php
require_once __DIR__ . '/config/config.php';

echo "<h2>MySQL Connection Test (No Database)</h2>";

echo "<h3>Testing MySQL Connection:</h3>";

try {
    // First try connecting without specifying a database
    $dsn = "mysql:host=" . DB_HOST . ";charset=utf8mb4";
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    
    echo "<p style='color: green;'>✅ MySQL connection successful!</p>";
    
    // List available databases
    $stmt = $pdo->query("SHOW DATABASES");
    $databases = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "<h3>Available Databases:</h3>";
    echo "<ul>";
    foreach ($databases as $db) {
        $selected = ($db === DB_NAME) ? " <strong>(SELECTED)</strong>" : "";
        echo "<li>" . htmlspecialchars($db) . $selected . "</li>";
    }
    echo "</ul>";
    
    // Now try connecting to the specific database
    echo "<h3>Testing Specific Database Connection:</h3>";
    try {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
        
        echo "<p style='color: green;'>✅ Database '" . DB_NAME . "' connection successful!</p>";
        
        // Check if tables exist
        $stmt = $pdo->query("SHOW TABLES");
        $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        if (empty($tables)) {
            echo "<p style='color: orange;'>⚠️ Database exists but has no tables. Run setup.php</p>";
        } else {
            echo "<p style='color: green;'>✅ Database has " . count($tables) . " tables</p>";
        }
        
    } catch (PDOException $e) {
        echo "<p style='color: red;'>❌ Cannot connect to database '" . DB_NAME . "'</p>";
        echo "<p><strong>Error:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
    }
    
} catch (PDOException $e) {
    echo "<p style='color: red;'>❌ MySQL connection failed!</p>";
    echo "<p><strong>Error:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
    
    echo "<h3>Possible Issues:</h3>";
    echo "<ul>";
    echo "<li><strong>Wrong Password:</strong> The password for user '" . DB_USER . "' is incorrect</li>";
    echo "<li><strong>Wrong Username:</strong> The username '" . DB_USER . "' doesn't exist</li>";
    echo "<li><strong>Host Issue:</strong> MySQL might not be running on '" . DB_HOST . "'</li>";
    echo "</ul>";
    
    echo "<h3>How to Fix:</h3>";
    echo "<ol>";
    echo "<li>Go to your Hostinger control panel</li>";
    echo "<li>Navigate to Databases > MySQL Databases</li>";
    echo "<li>Find the user '" . DB_USER . "'</li>";
    echo "<li>Click 'Change Password' and set a new password</li>";
    echo "<li>Update the password in config/config.php</li>";
    echo "</ol>";
}

echo "<hr>";
echo "<p><a href='/test_db.php'>Test Full Database</a> | <a href='/setup.php'>Run Setup</a> | <a href='/login.php'>Login</a></p>";
?>
