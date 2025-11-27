<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/src/SettingsService.php';
session_start();

$free_credits = SettingsService::get('free_credits_on_signup', '30');

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
        require_once __DIR__ . '/src/SettingsService.php';
        
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        
        $free_credits = SettingsService::get('free_credits_on_signup', '30');
        
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
                // Give free credits on signup
                $stmt = $pdo->prepare('INSERT INTO users (email, password_hash, credits_balance) VALUES (?, ?, ?)');
                $stmt->execute([$email, $hash, $free_credits]);
                
                $success = 'Account created successfully! You can now sign in.';
            }
        }
    } catch (Exception $e) {
        $error = 'Registration failed. Please try again.';
    }
}
?>
<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Account - Rapid Indexer</title>
    <meta name="description" content="Join Rapid Indexer today. Get <?php echo $free_credits; ?> free credits to start indexing your backlinks immediately.">
    <link rel="canonical" href="https://rapid-indexer.com/register.php">
    <meta name="robots" content="index, follow">
    
    <!-- Open Graph -->
    <meta property="og:title" content="Create Account - Rapid Indexer">
    <meta property="og:description" content="Join Rapid Indexer today. Get <?php echo $free_credits; ?> free credits to start indexing your backlinks immediately.">
    <meta property="og:type" content="website">
    <meta property="og:url" content="https://rapid-indexer.com/register.php">
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
                <h1 class="text-3xl font-bold text-white mb-2">Create an account</h1>
                <p class="text-gray-400">Start indexing your links in minutes. Get <span class="text-primary-500 font-bold"><?php echo $free_credits; ?> free credits</span> instantly.</p>
            </div>
            
            <?php if ($error): ?>
                <div class="mb-6 p-4 rounded-lg bg-red-900/20 border border-red-900/50 text-red-400 flex items-center gap-3">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="mb-6 p-4 rounded-lg bg-green-900/20 border border-green-900/50 text-green-400 flex items-center gap-3">
                    <i class="fas fa-check-circle"></i>
                    <?php echo htmlspecialchars($success); ?>
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
                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Password</label>
                    <input type="password" name="password" required minlength="6" 
                           class="dark w-full px-4 py-3 rounded-lg border border-white/10 bg-dark-950 text-white focus:border-primary-600 focus:ring-1 focus:ring-primary-600 transition-colors"
                           placeholder="••••••••">
                    <p class="mt-1 text-xs text-gray-500">Must be at least 6 characters</p>
                </div>
                
                <div>
                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Confirm Password</label>
                    <input type="password" name="confirm_password" required 
                           class="dark w-full px-4 py-3 rounded-lg border border-white/10 bg-dark-950 text-white focus:border-primary-600 focus:ring-1 focus:ring-primary-600 transition-colors"
                           placeholder="••••••••">
                </div>
                
                <button type="submit" class="w-full py-3.5 px-4 bg-primary-700 hover:bg-primary-600 text-white font-bold rounded-lg shadow-lg shadow-primary-900/20 transition-all transform active:scale-[0.98]">
                    Create Account
                </button>
                
                <div class="text-center mt-6">
                    <span class="text-gray-400">Already have an account?</span>
                    <a href="/login.php" class="font-bold text-primary-500 hover:text-primary-400 ml-1">Sign in</a>
                </div>
            </form>
            
            <div class="mt-12 text-center text-xs text-gray-500">
                By registering, you agree to our <a href="/terms.php" class="underline hover:text-gray-300">Terms</a> and <a href="/privacy.php" class="underline hover:text-gray-300">Privacy Policy</a>.
            </div>
        </div>
    </div>
    
    <!-- Right Side: Brand -->
    <div class="hidden lg:flex w-1/2 bg-[#1a1a1a] relative overflow-hidden items-center justify-center p-12">
        <!-- Abstract Pattern -->
        <div class="absolute inset-0 opacity-10" style="background-image: radial-gradient(#be123c 1px, transparent 1px); background-size: 32px 32px;"></div>
        <div class="absolute inset-0 bg-gradient-to-br from-black/50 to-transparent"></div>
        
        <div class="relative z-10 max-w-lg text-center">
            <h2 class="text-4xl font-bold text-white mb-6">Join 10,000+ SEO Professionals</h2>
            <div class="space-y-6 text-left">
                <div class="flex items-start gap-4 p-4 rounded-xl bg-white/5 border border-white/5 backdrop-blur-sm">
                    <div class="w-10 h-10 rounded-full bg-primary-900/30 flex items-center justify-center flex-shrink-0 text-primary-500">
                        <i class="fas fa-bolt"></i>
                    </div>
                    <div>
                        <h3 class="text-white font-bold mb-1">Instant Indexing</h3>
                        <p class="text-sm text-gray-400">Get crawled in minutes, not weeks.</p>
                    </div>
                </div>
                
                <div class="flex items-start gap-4 p-4 rounded-xl bg-white/5 border border-white/5 backdrop-blur-sm">
                    <div class="w-10 h-10 rounded-full bg-primary-900/30 flex items-center justify-center flex-shrink-0 text-primary-500">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <div>
                        <h3 class="text-white font-bold mb-1">Detailed Reporting</h3>
                        <p class="text-sm text-gray-400">Track every link status in real-time.</p>
                    </div>
                </div>
                
                <div class="flex items-start gap-4 p-4 rounded-xl bg-white/5 border border-white/5 backdrop-blur-sm">
                    <div class="w-10 h-10 rounded-full bg-primary-900/30 flex items-center justify-center flex-shrink-0 text-primary-500">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <div>
                        <h3 class="text-white font-bold mb-1">100% Safe</h3>
                        <p class="text-sm text-gray-400">Google-compliant methods only.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
