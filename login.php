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
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Rapid Indexer</title>
    <meta name="description" content="Sign in to Rapid Indexer to manage your tasks, view reports, and boost your SEO.">
    <link rel="canonical" href="https://rapid-indexer.com/login.php">
    <meta name="robots" content="index, follow">

    <!-- Open Graph -->
    <meta property="og:title" content="Login - Rapid Indexer">
    <meta property="og:description" content="Sign in to Rapid Indexer to manage your tasks, view reports, and boost your SEO.">
    <meta property="og:type" content="website">
    <meta property="og:url" content="https://rapid-indexer.com/login.php">
    <meta property="og:image" content="https://rapid-indexer.com/assets/img/dashboard-preview.png">
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Rubik:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Rubik"', 'sans-serif'],
                    },
                    colors: {
                        primary: {
                            50: '#fff1f2',
                            100: '#ffe4e6',
                            200: '#fecdd3',
                            300: '#fda4af',
                            400: '#fb7185',
                            500: '#f43f5e',
                            600: '#e11d48', 
                            700: '#be123c', // Base primary - deep rose
                            800: '#9f1239',
                            900: '#881337', 
                            950: '#4c0519',
                        },
                        dark: {
                            850: '#262626',
                            900: '#1a1a1a',
                            950: '#111111',
                        }
                    }
                }
            }
        }
    </script>
    <style>
        body {
            background-color: #141414;
            color: #efefef;
        }
        .dark input {
            background-color: #111111;
            border-color: #333333;
            color: #e2e8f0;
        }
        .dark input:focus {
            border-color: #be123c;
            outline: none;
            box-shadow: 0 0 0 1px #be123c;
        }
    </style>
</head>
<body class="min-h-screen flex">
    <!-- Left Side: Form -->
    <div class="w-full lg:w-1/2 flex flex-col justify-center p-8 lg:p-16 bg-[#141414]">
        <div class="max-w-md mx-auto w-full">
            <a href="/" class="inline-flex items-center gap-2 mb-12 text-2xl font-bold text-white hover:text-primary-500 transition-colors">
                <i class="fas fa-rocket text-primary-500"></i>
                Rapid Indexer
            </a>
            
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-white mb-2">Welcome back</h1>
                <p class="text-gray-400">Sign in to access your dashboard.</p>
            </div>
            
            <?php if ($error): ?>
                <div class="mb-6 p-4 rounded-lg bg-red-900/20 border border-red-900/50 text-red-400 flex items-center gap-3">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" class="space-y-5">
                <div>
                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Email Address</label>
                    <input type="email" name="email" required 
                           class="dark w-full px-4 py-3 rounded-lg border border-white/10 bg-dark-950 text-white focus:border-primary-600 focus:ring-1 focus:ring-primary-600 transition-colors"
                           placeholder="name@company.com"
                           value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                </div>
                
                <div>
                    <div class="flex justify-between items-center mb-2">
                        <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider">Password</label>
                        <a href="/forgot_password.php" class="text-xs text-primary-500 hover:text-primary-400">Forgot password?</a>
                    </div>
                    <input type="password" name="password" required 
                           class="dark w-full px-4 py-3 rounded-lg border border-white/10 bg-dark-950 text-white focus:border-primary-600 focus:ring-1 focus:ring-primary-600 transition-colors"
                           placeholder="••••••••">
                </div>
                
                <button type="submit" class="w-full py-3.5 px-4 bg-primary-700 hover:bg-primary-600 text-white font-bold rounded-lg shadow-lg shadow-primary-900/20 transition-all transform active:scale-[0.98]">
                    Sign In
                </button>
                
                <div class="text-center mt-6">
                    <span class="text-gray-400">New here?</span>
                    <a href="/register.php" class="font-bold text-primary-500 hover:text-primary-400 ml-1">Create an account</a>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Right Side: Brand -->
    <div class="hidden lg:flex w-1/2 bg-[#1a1a1a] relative overflow-hidden items-center justify-center p-12">
        <!-- Abstract Pattern -->
        <div class="absolute inset-0 opacity-10" style="background-image: radial-gradient(#be123c 1px, transparent 1px); background-size: 32px 32px;"></div>
        <div class="absolute inset-0 bg-gradient-to-br from-black/50 to-transparent"></div>
        
        <div class="relative z-10 max-w-lg text-center">
            <h2 class="text-4xl font-bold text-white mb-6">Power Up Your SEO</h2>
            <div class="space-y-6 text-left">
                <div class="flex items-start gap-4 p-4 rounded-xl bg-white/5 border border-white/5 backdrop-blur-sm">
                    <div class="w-10 h-10 rounded-full bg-primary-900/30 flex items-center justify-center flex-shrink-0 text-primary-500">
                        <i class="fas fa-rocket"></i>
                    </div>
                    <div>
                        <h3 class="text-white font-bold mb-1">Rapid Processing</h3>
                        <p class="text-sm text-gray-400">Submit your links and see results fast.</p>
                    </div>
                </div>
                
                <div class="flex items-start gap-4 p-4 rounded-xl bg-white/5 border border-white/5 backdrop-blur-sm">
                    <div class="w-10 h-10 rounded-full bg-primary-900/30 flex items-center justify-center flex-shrink-0 text-primary-500">
                        <i class="fas fa-robot"></i>
                    </div>
                    <div>
                        <h3 class="text-white font-bold mb-1">Automated Checks</h3>
                        <p class="text-sm text-gray-400">We verify indexing status automatically.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>