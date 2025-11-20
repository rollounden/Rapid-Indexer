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
        
        if (empty($email) || empty($password)) {
            $error = 'Please enter both email and password.';
        } else {
            $pdo = Db::conn();
            $stmt = $pdo->prepare('SELECT * FROM users WHERE email = ? LIMIT 1');
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            
            if ($user && password_verify($password, $user['password_hash'])) {
                $_SESSION['uid'] = $user['id'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['role'] = $user['role'];
                header('Location: /');
                exit;
            } else {
                $error = 'Invalid email or password.';
            }
        }
    } catch (Exception $e) {
        $error = 'Login failed. Please try again.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Rapid Indexer</title>
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
                <h4 class="fw-bold">Welcome Back</h4>
                <p class="text-muted">Sign in to continue to your dashboard</p>
            </div>
            
            <?php if ($error): ?>
                <div class="alert alert-danger py-2 text-center"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="mb-3">
                    <label class="form-label">Email address</label>
                    <input type="email" class="form-control form-control-lg" name="email" required 
                           placeholder="name@company.com"
                           value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                </div>
                
                <div class="mb-4">
                    <div class="d-flex justify-content-between">
                        <label class="form-label">Password</label>
                        <a href="#" class="text-decoration-none small">Forgot password?</a>
                    </div>
                    <input type="password" class="form-control form-control-lg" name="password" required placeholder="••••••••">
                </div>
                
                <div class="d-grid">
                    <button type="submit" class="btn btn-primary btn-lg">Sign In</button>
                </div>
            </form>
            
            <div class="text-center mt-4">
                <p class="mb-0 text-muted">Don't have an account? <a href="/register.php" class="fw-bold text-primary text-decoration-none">Sign up</a></p>
            </div>
        </div>
        
        <div class="text-center mt-4 text-muted small">
            &copy; <?php echo date('Y'); ?> Rapid Indexer. All rights reserved.
        </div>
    </div>
    
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
</body>
</html>
