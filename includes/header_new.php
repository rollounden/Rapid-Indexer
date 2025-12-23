<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$current_page = basename($_SERVER['PHP_SELF']);

// Default Meta Data
$page_title = $page_title ?? 'Rapid Indexer - Premium SEO Link Indexing';
$meta_description = $meta_description ?? 'Boost your SEO with Rapid Indexer. We use tiered links and browser traffic to guarantee Google indexation. Monitor your backlinks with our advanced checker.';
$meta_keywords = $meta_keywords ?? 'seo link indexing, google indexer, backlink checker, rapid indexer, seo tools';
$canonical_url = $canonical_url ?? 'https://rapid-indexer.com' . $_SERVER['REQUEST_URI'];
$robots_tag = $robots_tag ?? 'index, follow';
$og_image = $og_image ?? 'https://rapid-indexer.com/assets/img/dashboard-preview.png';
?>
<!DOCTYPE html>
<html lang="en" class="dark">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title><?php echo htmlspecialchars($page_title); ?></title>
    <meta name="description" content="<?php echo htmlspecialchars($meta_description); ?>">
    <meta name="keywords" content="<?php echo htmlspecialchars($meta_keywords); ?>">
    <link rel="canonical" href="<?php echo htmlspecialchars($canonical_url); ?>">
    <meta name="robots" content="<?php echo htmlspecialchars($robots_tag); ?>">

    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?php echo htmlspecialchars($canonical_url); ?>">
    <meta property="og:title" content="<?php echo htmlspecialchars($page_title); ?>">
    <meta property="og:description" content="<?php echo htmlspecialchars($meta_description); ?>">
    <meta property="og:image" content="<?php echo htmlspecialchars($og_image); ?>">

    <!-- Twitter -->
    <meta property="twitter:card" content="summary_large_image">
    <meta property="twitter:url" content="<?php echo htmlspecialchars($canonical_url); ?>">
    <meta property="twitter:title" content="<?php echo htmlspecialchars($page_title); ?>">
    <meta property="twitter:description" content="<?php echo htmlspecialchars($meta_description); ?>">
    <meta property="twitter:image" content="<?php echo htmlspecialchars($og_image); ?>">

    <link rel="icon" href="/favicon.ico" sizes="any">
    <link rel="apple-touch-icon" href="/apple-touch-icon.png">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Rubik:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

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
                            700: '#be123c',
                            800: '#9f1239',
                            900: '#881337', 
                            950: '#4c0519',
                        },
                        dark: {
                            50: '#f6f6f6',
                            100: '#e7e7e7',
                            200: '#d1d1d1',
                            300: '#b0b0b0',
                            400: '#888888',
                            500: '#6d6d6d',
                            600: '#5d5d5d',
                            700: '#4f4f4f',
                            800: '#454545',
                            850: '#262626',
                            900: '#1a1a1a',
                            950: '#111111',
                        }
                    }
                }
            }
        }
    </script>

    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        [x-cloak] { display: none !important; }
        * { scroll-behavior: smooth; }
        body { background-color: #141414; color: #efefef; }
        
        /* Custom inputs */
        .dark input, .dark textarea, .dark select {
            background-color: #111111;
            border-color: #333333;
            color: #e2e8f0;
        }
        .dark input:focus, .dark textarea:focus, .dark select:focus {
            border-color: #be123c;
            outline: none;
            box-shadow: 0 0 0 1px #be123c;
        }

        .card {
            background: #1c1c1c;
            border: 1px solid #2a2a2a;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.3);
        }
        
        .nav-blur {
            background: rgba(20, 20, 20, 0.9);
            backdrop-filter: blur(12px);
        }

        /* Table Styles */
        table { width: 100%; border-collapse: separate; border-spacing: 0; }
        th { text-align: left; font-weight: 600; color: #a1a1aa; padding: 1rem; border-bottom: 1px solid #333; }
        td { padding: 1rem; border-bottom: 1px solid #2a2a2a; color: #e2e8f0; }
        tr:last-child td { border-bottom: none; }
    </style>
</head>

<body class="antialiased min-h-screen flex flex-col">
    <!-- Navigation -->
    <nav class="w-full border-b border-white/5 nav-blur sticky top-0 z-40" x-data="{ mobileMenuOpen: false }">
        <div class="max-w-7xl mx-auto px-6 lg:px-8">
            <div class="flex justify-between items-center h-20">

                <!-- Logo -->
                <a href="/dashboard" class="flex items-center gap-3 hover:opacity-90 transition-opacity">
                    <div class="flex flex-col items-center text-primary-500">
                        <i class="fas fa-rocket text-2xl"></i>
                    </div>
                    <span class="font-bold text-xl tracking-tight text-white">Rapid Indexer</span>
                </a>

                <!-- Desktop Menu -->
                <div class="hidden md:flex items-center gap-8">
                    <a href="/dashboard" class="text-sm font-medium <?php echo $current_page === 'dashboard.php' ? 'text-primary-500' : 'text-gray-300 hover:text-white'; ?> transition-colors">Dashboard</a>
                    <a href="/tasks" class="text-sm font-medium <?php echo $current_page === 'tasks.php' ? 'text-primary-500' : 'text-gray-300 hover:text-white'; ?> transition-colors">Tasks</a>
                    <a href="/traffic" class="text-sm font-medium <?php echo $current_page === 'traffic.php' ? 'text-primary-500' : 'text-gray-300 hover:text-white'; ?> transition-colors">Traffic</a>
                    <a href="/payments" class="text-sm font-medium <?php echo $current_page === 'payments.php' ? 'text-primary-500' : 'text-gray-300 hover:text-white'; ?> transition-colors">Payments</a>
                    
                    <!-- Support Dropdown -->
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open" @click.away="open = false" class="flex items-center gap-1 text-sm font-medium text-gray-300 hover:text-white transition-colors">
                            Support <i class="fas fa-chevron-down text-xs ml-1"></i>
                        </button>
                        <div x-show="open" x-cloak class="absolute right-0 mt-2 w-48 bg-[#1c1c1c] border border-[#333] rounded-lg shadow-xl py-1 z-50">
                            <a href="/faq" class="block px-4 py-2 text-sm text-gray-300 hover:bg-white/5 hover:text-white">FAQ</a>
                            <a href="/contact" class="block px-4 py-2 text-sm text-gray-300 hover:bg-white/5 hover:text-white">Contact Us</a>
                        </div>
                    </div>

                    <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                    <a href="/admin" class="text-sm font-medium <?php echo strpos($current_page, 'admin') === 0 ? 'text-primary-500' : 'text-gray-300 hover:text-white'; ?> transition-colors">Admin</a>
                    <?php endif; ?>
                </div>

                <!-- User Menu -->
                <div class="hidden md:flex items-center gap-4">
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open" @click.away="open = false" class="flex items-center gap-2 text-sm font-medium text-gray-300 hover:text-white transition-colors">
                            <div class="w-8 h-8 rounded-full bg-primary-900/50 flex items-center justify-center text-primary-500 border border-primary-900/30">
                                <i class="fas fa-user text-xs"></i>
                            </div>
                            <span class="max-w-[150px] truncate"><?php echo htmlspecialchars($_SESSION['email'] ?? 'User'); ?></span>
                            <i class="fas fa-chevron-down text-xs"></i>
                        </button>
                        <div x-show="open" x-cloak class="absolute right-0 mt-2 w-48 bg-[#1c1c1c] border border-[#333] rounded-lg shadow-xl py-1 z-50">
                            <div class="px-4 py-2 border-b border-white/5">
                                <p class="text-xs text-gray-500 uppercase">Signed in as</p>
                                <p class="text-sm font-bold text-white truncate"><?php echo htmlspecialchars($_SESSION['email'] ?? 'User'); ?></p>
                            </div>
                            <a href="/api_access" class="block px-4 py-2 text-sm text-gray-300 hover:bg-white/5 hover:text-white">API Access</a>
                            <a href="/logout" class="block px-4 py-2 text-sm text-red-400 hover:bg-red-500/10 hover:text-red-300">Log out</a>
                        </div>
                    </div>
                </div>

                <!-- Mobile Menu Button -->
                <button @click="mobileMenuOpen = !mobileMenuOpen" class="md:hidden text-gray-300 hover:text-white">
                    <i class="fas fa-bars text-xl"></i>
                </button>
            </div>
        </div>

        <!-- Mobile Menu -->
        <div x-show="mobileMenuOpen" x-cloak class="md:hidden bg-[#1c1c1c] border-b border-white/5">
            <div class="px-4 pt-2 pb-4 space-y-1">
                <a href="/dashboard" class="block px-3 py-2 rounded-md text-base font-medium <?php echo $current_page === 'dashboard.php' ? 'bg-primary-900/20 text-primary-500' : 'text-gray-300 hover:bg-white/5 hover:text-white'; ?>">Dashboard</a>
                <a href="/tasks" class="block px-3 py-2 rounded-md text-base font-medium <?php echo $current_page === 'tasks.php' ? 'bg-primary-900/20 text-primary-500' : 'text-gray-300 hover:bg-white/5 hover:text-white'; ?>">Tasks</a>
                <a href="/traffic" class="block px-3 py-2 rounded-md text-base font-medium <?php echo $current_page === 'traffic.php' ? 'bg-primary-900/20 text-primary-500' : 'text-gray-300 hover:bg-white/5 hover:text-white'; ?>">Traffic</a>
                <a href="/payments" class="block px-3 py-2 rounded-md text-base font-medium <?php echo $current_page === 'payments.php' ? 'bg-primary-900/20 text-primary-500' : 'text-gray-300 hover:bg-white/5 hover:text-white'; ?>">Payments</a>
                <a href="/faq" class="block px-3 py-2 rounded-md text-base font-medium text-gray-300 hover:bg-white/5 hover:text-white">FAQ</a>
                <a href="/contact" class="block px-3 py-2 rounded-md text-base font-medium text-gray-300 hover:bg-white/5 hover:text-white">Contact Us</a>
                <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                <a href="/admin" class="block px-3 py-2 rounded-md text-base font-medium <?php echo strpos($current_page, 'admin') === 0 ? 'bg-primary-900/20 text-primary-500' : 'text-gray-300 hover:bg-white/5 hover:text-white'; ?>">Admin</a>
                <?php endif; ?>
                <a href="/api_access" class="block px-3 py-2 rounded-md text-base font-medium <?php echo $current_page === 'api_access.php' ? 'bg-primary-900/20 text-primary-500' : 'text-gray-300 hover:bg-white/5 hover:text-white'; ?>">API Access</a>
                <div class="border-t border-white/10 my-2"></div>
                <a href="/logout" class="block px-3 py-2 rounded-md text-base font-medium text-red-400 hover:bg-red-500/10">Log out</a>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="flex-grow">

