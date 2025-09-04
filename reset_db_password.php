<?php
echo "<h2>Database Password Reset Helper</h2>";

// Common test passwords to try
$test_passwords = [
    'test123456',
    'password123',
    'admin123',
    '123456789',
    'testpassword',
    'hostinger123',
    'database123'
];

$host = 'localhost';
$dbname = 'u906310247_KEKRd';
$username = 'u906310247_FBapb';

echo "<p><strong>Testing connection for:</strong></p>";
echo "<p>Host: $host</p>";
echo "<p>Database: $dbname</p>";
echo "<p>Username: $username</p>";

echo "<h3>Testing Passwords:</h3>";

foreach ($test_passwords as $password) {
    try {
        $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Test a simple query
        $stmt = $pdo->query("SELECT COUNT(*) FROM users");
        $count = $stmt->fetchColumn();
        
        echo "<p style='color: green;'>✅ <strong>SUCCESS!</strong> Password: <code>$password</code></p>";
        echo "<p>Users in database: $count</p>";
        
        // Update config file
        $config_content = file_get_contents(__DIR__ . '/config/config.php');
        $new_config = preg_replace(
            "/const DB_PASS = '[^']*';/",
            "const DB_PASS = '$password';",
            $config_content
        );
        file_put_contents(__DIR__ . '/config/config.php', $new_config);
        
        echo "<p style='color: green;'>✅ Config file updated with correct password!</p>";
        echo "<p><a href='/debug_registration.php'>Test Registration Now</a></p>";
        break;
        
    } catch (PDOException $e) {
        echo "<p style='color: red;'>❌ Password '$password' failed: " . htmlspecialchars($e->getMessage()) . "</p>";
    }
}

echo "<hr>";
echo "<h3>If no password worked:</h3>";
echo "<p>1. Go to Hostinger control panel</p>";
echo "<p>2. Navigate to Databases > MySQL Databases</p>";
echo "<p>3. Find user 'u906310247_FBapb'</p>";
echo "<p>4. Click 'Change Password'</p>";
echo "<p>5. Set a new password</p>";
echo "<p>6. Update config/config.php with the new password</p>";
?>
