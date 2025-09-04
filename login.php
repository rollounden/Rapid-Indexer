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
                $_SESSION['role'] = $user['role'];
                $pdo->prepare('UPDATE users SET last_login_at = NOW() WHERE id = ?')->execute([$user['id']]);
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
    <title>Login - RapidIndexer</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="/assets/css/style.css" rel="stylesheet">
</head>
<body>
    <div class="auth-container">
        <div class="auth-card">
            <div class="card">
                <div class="card-body p-4">
                    <div class="text-center mb-4">
                        <h2 class="fw-bold text-primary">RapidIndexer</h2>
                        <p class="text-muted">Sign in to your account</p>
                    </div>
                    
                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                    <?php endif; ?>
                    
                    <?php if ($success): ?>
                        <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
                    <?php endif; ?>
                    
                    <form method="POST">
                        <div class="mb-3">
                            <label for="email" class="form-label">Email address</label>
                            <input type="email" class="form-control" id="email" name="email" required 
                                   value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                        </div>
                        
                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                        
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">Sign In</button>
                        </div>
                    </form>
                    
                    <div class="text-center mt-4">
                        <p class="mb-0">Don't have an account? <a href="/register.php" class="text-primary">Sign up</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
