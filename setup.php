<?php
require_once __DIR__ . '/config/config.php';

echo "<h2>RapidIndexer Database Setup</h2>";

try {
    require_once __DIR__ . '/src/Db.php';
    $pdo = Db::conn();
    
    echo "<p>✅ Database connection successful!</p>";
    
    // Read and execute the SQL schema
    $sql = file_get_contents(__DIR__ . '/db/init.sql');
    $pdo->exec($sql);
    
    echo "<p>✅ Database tables created successfully!</p>";
    
    // Create an admin user
    $adminEmail = 'admin@rapidindexer.com';
    $adminPassword = 'admin123'; // Change this!
    $adminHash = password_hash($adminPassword, PASSWORD_BCRYPT);
    
    $stmt = $pdo->prepare('INSERT INTO users (email, password_hash, role, credits_balance) VALUES (?, ?, ?, ?)');
    $stmt->execute([$adminEmail, $adminHash, 'admin', 1000]);
    
    echo "<p>✅ Admin user created!</p>";
    echo "<p><strong>Email:</strong> admin@rapidindexer.com</p>";
    echo "<p><strong>Password:</strong> admin123</p>";
    echo "<p><strong>⚠️ IMPORTANT:</strong> Change this password immediately after first login!</p>";
    
    echo "<p><a href='/login.php'>Go to Login Page</a></p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p>Please check your database configuration in config/config.php</p>";
}
?>
