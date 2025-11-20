<?php
require_once __DIR__ . '/config/config.php';
session_start();

// Redirect if already logged in
if (isset($_SESSION['uid'])) {
    header('Location: /');
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        require_once __DIR__ . '/src/Db.php';
        
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        
        if (empty($email) || empty($password)) {
            $error = 'Please fill in all fields.';
        } elseif ($password !== $confirm_password) {
            $error = 'Passwords do not match.';
        } elseif (strlen($password) < 6) {
            $error = 'Password must be at least 6 characters long.';
        } else {
            $pdo = Db::conn();
            
            // Check if email already exists
            $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                $error = 'Email address already registered.';
            } else {
                $hash = password_hash($password, PASSWORD_BCRYPT);
                $stmt = $pdo->prepare('INSERT INTO users (email, password_hash, credits_balance) VALUES (?, ?, 0)');
                $stmt->execute([$email, $hash]);
                
                $success = 'Account created successfully! You can now sign in.';
            }
        }
    } catch (Exception $e) {
        $error = 'Registration failed. Please try again.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Rapid Indexer</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="/assets/css/style.css" rel="stylesheet">
</head>
<body>
    <div class="auth-wrapper">
        <div class="text-center">
            <a href="/" class="auth-logo">
                <i class="fas fa-rocket me-2"></i>Rapid Indexer
            </a>
        </div>
        
        <div class="auth-box">
            <div class="text-center mb-4">
                <h4 class="fw-bold">Create Account</h4>
                <p class="text-muted">Start indexing your links today</p>
            </div>
            
            <?php if ($error): ?>
                <div class="alert alert-danger py-2 text-center"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success py-2 text-center"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="mb-3">
                    <label class="form-label">Email address</label>
                    <input type="email" class="form-control form-control-lg" name="email" required 
                           placeholder="name@company.com"
                           value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Password</label>
                    <input type="password" class="form-control form-control-lg" name="password" required 
                           minlength="6" placeholder="••••••••">
                    <div class="form-text">At least 6 characters</div>
                </div>
                
                <div class="mb-4">
                    <label class="form-label">Confirm Password</label>
                    <input type="password" class="form-control form-control-lg" name="confirm_password" required placeholder="••••••••">
                </div>
                
                <div class="d-grid">
                    <button type="submit" class="btn btn-primary btn-lg">Create Account</button>
                </div>
            </form>
            
            <div class="text-center mt-4">
                <p class="mb-0 text-muted">Already have an account? <a href="/login.php" class="fw-bold text-primary text-decoration-none">Sign in</a></p>
            </div>
        </div>
        
        <div class="text-center mt-4 text-muted small">
            &copy; <?php echo date('Y'); ?> Rapid Indexer. All rights reserved.
        </div>
    </div>
    
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
</body>
</html>
