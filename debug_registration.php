<?php
require_once __DIR__ . '/config/config.php';

echo "<h2>Registration Debug</h2>";

// Test database connection first
try {
    require_once __DIR__ . '/src/Db.php';
    $pdo = Db::conn();
    echo "<p style='color: green;'>✅ Database connection successful</p>";
    
    // Check if users table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'users'");
    if ($stmt->rowCount() > 0) {
        echo "<p style='color: green;'>✅ Users table exists</p>";
    } else {
        echo "<p style='color: red;'>❌ Users table does not exist</p>";
        exit;
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Database error: " . htmlspecialchars($e->getMessage()) . "</p>";
    exit;
}

// Test registration process
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    echo "<h3>Testing Registration Process:</h3>";
    
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    echo "<p><strong>Email:</strong> " . htmlspecialchars($email) . "</p>";
    echo "<p><strong>Password length:</strong> " . strlen($password) . "</p>";
    echo "<p><strong>Confirm password length:</strong> " . strlen($confirm_password) . "</p>";
    
    // Validation checks
    if (empty($email) || empty($password)) {
        echo "<p style='color: red;'>❌ Email and password are required</p>";
    } elseif ($password !== $confirm_password) {
        echo "<p style='color: red;'>❌ Passwords do not match</p>";
    } elseif (strlen($password) < 6) {
        echo "<p style='color: red;'>❌ Password must be at least 6 characters</p>";
    } else {
        echo "<p style='color: green;'>✅ Validation passed</p>";
        
        // Check if email already exists
        try {
            $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                echo "<p style='color: red;'>❌ Email already registered</p>";
            } else {
                echo "<p style='color: green;'>✅ Email is available</p>";
                
                // Try to create user
                try {
                    $hash = password_hash($password, PASSWORD_BCRYPT);
                    $stmt = $pdo->prepare('INSERT INTO users (email, password_hash, credits_balance) VALUES (?, ?, 0)');
                    $stmt->execute([$email, $hash]);
                    
                    echo "<p style='color: green;'>✅ User created successfully!</p>";
                    echo "<p>User ID: " . $pdo->lastInsertId() . "</p>";
                    
                } catch (Exception $e) {
                    echo "<p style='color: red;'>❌ Error creating user: " . htmlspecialchars($e->getMessage()) . "</p>";
                }
            }
        } catch (Exception $e) {
            echo "<p style='color: red;'>❌ Error checking email: " . htmlspecialchars($e->getMessage()) . "</p>";
        }
    }
}

// Show current users
echo "<h3>Current Users:</h3>";
try {
    $stmt = $pdo->query('SELECT id, email, role, credits_balance, created_at FROM users ORDER BY created_at DESC');
    $users = $stmt->fetchAll();
    
    if (empty($users)) {
        echo "<p>No users found</p>";
    } else {
        echo "<table border='1' style='border-collapse: collapse;'>";
        echo "<tr><th>ID</th><th>Email</th><th>Role</th><th>Credits</th><th>Created</th></tr>";
        foreach ($users as $user) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($user['id']) . "</td>";
            echo "<td>" . htmlspecialchars($user['email']) . "</td>";
            echo "<td>" . htmlspecialchars($user['role']) . "</td>";
            echo "<td>" . htmlspecialchars($user['credits_balance']) . "</td>";
            echo "<td>" . htmlspecialchars($user['created_at']) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error fetching users: " . htmlspecialchars($e->getMessage()) . "</p>";
}
?>

<h3>Test Registration Form:</h3>
<form method="POST">
    <div>
        <label>Email: <input type="email" name="email" required></label>
    </div>
    <div>
        <label>Password: <input type="password" name="password" required minlength="6"></label>
    </div>
    <div>
        <label>Confirm Password: <input type="password" name="confirm_password" required></label>
    </div>
    <button type="submit">Test Registration</button>
</form>

<hr>
<p><a href="/register.php">Go to Real Registration Page</a> | <a href="/login.php">Go to Login Page</a></p>
