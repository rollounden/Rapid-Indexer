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
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f3f4f6;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-card {
            background: white;
            border-radius: 1rem;
            box-shadow: 0 10px 25px rgba(0,0,0,0.05);
            overflow: hidden;
            width: 100%;
            max-width: 450px;
            border: 1px solid #e5e7eb;
        }
        .card-header {
            background: white;
            padding: 2rem 2rem 1rem;
            border-bottom: none;
            text-align: center;
        }
        .card-body {
            padding: 1rem 2rem 2rem;
        }
        .btn-primary {
            background-color: #2563eb;
            border-color: #2563eb;
            padding: 0.75rem;
            font-weight: 600;
        }
        .btn-primary:hover {
            background-color: #1d4ed8;
            border-color: #1d4ed8;
        }
        .form-control {
            padding: 0.75rem;
            border-color: #d1d5db;
        }
        .form-control:focus {
            border-color: #2563eb;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }
        .logo-icon {
            color: #2563eb;
            font-size: 2.5rem;
            margin-bottom: 1rem;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5">
                <div class="login-card">
                    <div class="card-header">
                        <i class="fas fa-rocket logo-icon"></i>
                        <h2 class="h4 fw-bold mb-1">Welcome Back</h2>
                        <p class="text-muted small mb-0">Sign in to Rapid Indexer</p>
                    </div>
                    <div class="card-body">
                        <?php if ($error): ?>
                            <div class="alert alert-danger py-2 small"><?php echo htmlspecialchars($error); ?></div>
                        <?php endif; ?>
                        
                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label small fw-bold text-muted">EMAIL ADDRESS</label>
                                <input type="email" class="form-control" name="email" required value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                            </div>
                            
                            <div class="mb-4">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <label class="form-label small fw-bold text-muted mb-0">PASSWORD</label>
                                    <a href="#" class="text-decoration-none small">Forgot?</a>
                                </div>
                                <input type="password" class="form-control" name="password" required>
                            </div>
                            
                            <div class="d-grid mb-4">
                                <button type="submit" class="btn btn-primary">Sign In</button>
                            </div>
                            
                            <div class="text-center small">
                                <span class="text-muted">New here?</span>
                                <a href="/register" class="fw-bold text-decoration-none ms-1">Create account</a>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="text-center mt-4 text-muted small">
                    &copy; <?php echo date('Y'); ?> Rapid Indexer. All rights reserved.
                </div>
            </div>
        </div>
    </div>
</body>
</html>
