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
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="/assets/css/style.css" rel="stylesheet">
    <style>
        body, html {
            height: 100%;
            margin: 0;
            background-color: #f8fafc;
        }
        .split-screen {
            display: flex;
            height: 100vh;
            overflow: hidden;
        }
        .auth-side {
            width: 40%;
            min-width: 450px;
            background: #fff;
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 3rem;
            position: relative;
            z-index: 10;
            box-shadow: 5px 0 30px rgba(0,0,0,0.05);
            overflow-y: auto;
        }
        .brand-side {
            flex: 1;
            background: linear-gradient(135deg, #2563eb 0%, #1e40af 100%);
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            padding: 3rem;
        }
        .brand-pattern {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-image: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.05'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
        }
        .auth-logo {
            font-size: 1.75rem;
            font-weight: 800;
            color: #2563eb;
            text-decoration: none;
            display: flex;
            align-items: center;
            margin-bottom: 3rem;
        }
        .form-control {
            padding: 0.75rem 1rem;
            border-radius: 0.5rem;
            border-color: #e2e8f0;
            font-size: 1rem;
        }
        .form-control:focus {
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }
        .btn-primary {
            padding: 0.75rem;
            font-weight: 600;
            font-size: 1rem;
            border-radius: 0.5rem;
        }
        @media (max-width: 992px) {
            .brand-side {
                display: none;
            }
            .auth-side {
                width: 100%;
                min-width: 0;
            }
        }
    </style>
</head>
<body>
    <div class="split-screen">
        <div class="auth-side">
            <a href="/" class="auth-logo">
                <i class="fas fa-rocket me-2"></i>Rapid Indexer
            </a>
            
            <div class="mb-4">
                <h2 class="fw-bold mb-2 text-slate-900">Create an account</h2>
                <p class="text-muted">Start indexing your links in minutes.</p>
            </div>
            
            <?php if ($error): ?>
                <div class="alert alert-danger border-0 bg-red-50 text-red-700 d-flex align-items-center">
                    <i class="fas fa-exclamation-circle me-2"></i> <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success border-0 bg-green-50 text-green-700 d-flex align-items-center">
                    <i class="fas fa-check-circle me-2"></i> <?php echo htmlspecialchars($success); ?>
                </div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="mb-3">
                    <label class="form-label fw-semibold text-secondary small">EMAIL ADDRESS</label>
                    <input type="email" class="form-control" name="email" required 
                           placeholder="name@company.com"
                           value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                </div>
                
                <div class="mb-3">
                    <label class="form-label fw-semibold text-secondary small">PASSWORD</label>
                    <input type="password" class="form-control" name="password" required minlength="6" placeholder="••••••••">
                    <div class="form-text">Must be at least 6 characters</div>
                </div>
                
                <div class="mb-4">
                    <label class="form-label fw-semibold text-secondary small">CONFIRM PASSWORD</label>
                    <input type="password" class="form-control" name="confirm_password" required placeholder="••••••••">
                </div>
                
                <div class="d-grid mb-4">
                    <button type="submit" class="btn btn-primary btn-lg shadow-sm">Create Account</button>
                </div>
                
                <div class="text-center">
                    <span class="text-muted">Already have an account?</span>
                    <a href="/login.php" class="fw-bold text-primary text-decoration-none ms-1">Sign in</a>
                </div>
            </form>
            
            <div class="mt-auto pt-5 text-center text-muted small">
                By registering, you agree to our <a href="/terms.php" class="text-decoration-none">Terms</a> and <a href="/privacy.php" class="text-decoration-none">Privacy Policy</a>.
            </div>
        </div>
        
        <div class="brand-side">
            <div class="brand-pattern"></div>
            <div class="text-center position-relative p-5">
                <h1 class="display-5 fw-bold mb-4">Join 10,000+ SEO Professionals</h1>
                <div class="row g-4 text-start mt-4">
                    <div class="col-12">
                        <div class="d-flex align-items-center mb-3">
                            <div class="rounded-circle bg-white bg-opacity-25 p-2 me-3">
                                <i class="fas fa-bolt text-white"></i>
                            </div>
                            <div>
                                <h5 class="mb-0">Instant Indexing</h5>
                                <small class="text-white-50">Get crawled in minutes</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="d-flex align-items-center mb-3">
                            <div class="rounded-circle bg-white bg-opacity-25 p-2 me-3">
                                <i class="fas fa-chart-line text-white"></i>
                            </div>
                            <div>
                                <h5 class="mb-0">Detailed Reporting</h5>
                                <small class="text-white-50">Track every link status</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="d-flex align-items-center">
                            <div class="rounded-circle bg-white bg-opacity-25 p-2 me-3">
                                <i class="fas fa-shield-alt text-white"></i>
                            </div>
                            <div>
                                <h5 class="mb-0">100% Safe</h5>
                                <small class="text-white-50">Google-compliant methods</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
