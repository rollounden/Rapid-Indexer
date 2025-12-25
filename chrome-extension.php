<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/src/SettingsService.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$free_credits = SettingsService::get('free_credits_on_signup', '30');
?>
<!DOCTYPE html>
<html lang="en" class="dark">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Official Rapid Indexer Chrome Extension - Boost SEO Workflow</title>
    <meta name="description" content="Instantly submit URLs to Google and Yandex, manage viral traffic campaigns, and check your credit balance directly from your browser with the Rapid Indexer Chrome Extension.">
    <meta name="keywords" content="rapid indexer chrome extension, seo extension, google indexing tool, viral traffic generator, seo workflow">
    <link rel="canonical" href="https://rapid-indexer.com/chrome-extension">
    <meta name="robots" content="index, follow">
    
    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="https://rapid-indexer.com/chrome-extension">
    <meta property="og:title" content="Rapid Indexer Chrome Extension">
    <meta property="og:description" content="Integrate Rapid Indexer seamlessly into your browsing experience. Instant indexing, traffic generation, and more.">
    <meta property="og:image" content="https://rapid-indexer.com/assets/img/dashboard-preview.png">

    <meta property="twitter:card" content="summary_large_image">
    <meta property="twitter:url" content="https://rapid-indexer.com/chrome-extension">
    <meta property="twitter:title" content="Rapid Indexer Chrome Extension">
    <meta property="twitter:description" content="Integrate Rapid Indexer seamlessly into your browsing experience. Instant indexing, traffic generation, and more.">
    <meta property="twitter:image" content="https://rapid-indexer.com/assets/img/dashboard-preview.png">

    <link rel="icon" href="/favicon.ico" sizes="any">
    <link rel="icon" href="/favicon.svg" type="image/svg+xml">
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
        
        .card {
            background: #1c1c1c;
            border: 1px solid #2a2a2a;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.3);
        }
        
        .nav-blur {
            background: rgba(20, 20, 20, 0.9);
            backdrop-filter: blur(12px);
        }
    </style>
</head>

<body class="antialiased min-h-screen flex flex-col">
    <!-- Navigation -->
    <nav class="w-full border-b border-white/5 nav-blur sticky top-0 z-40">
        <div class="max-w-7xl mx-auto px-6 lg:px-8">
            <div class="flex justify-between items-center h-20">
                <!-- Logo -->
                <a href="/" class="flex items-center gap-3">
                    <div class="flex flex-col items-center text-primary-500">
                        <i class="fas fa-rocket text-3xl"></i>
                    </div>
                    <span class="font-bold text-2xl tracking-tight text-white">Rapid Indexer</span>
                </a>

                <!-- Navigation Links -->
                <nav class="hidden md:flex items-center gap-8">
                    <a href="/viral-blast" class="text-sm font-medium text-primary-400 hover:text-white transition-colors flex items-center gap-1"><i class="fas fa-fire"></i> Viral Blast</a>
                    <a href="/#features" class="text-sm font-medium text-gray-300 hover:text-white transition-colors">Features</a>
                    <a href="/#pricing" class="text-sm font-medium text-gray-300 hover:text-white transition-colors">Pricing</a>
                    <a href="/faq" class="text-sm font-medium text-gray-300 hover:text-white transition-colors">FAQ</a>
                </nav>

                <div class="flex items-center gap-4">
                    <a href="/login" class="text-sm font-medium text-gray-300 hover:text-white transition-colors">Log in</a>
                    <a href="/register" class="inline-flex items-center px-5 py-2.5 border border-transparent text-sm font-bold rounded-lg text-white bg-primary-700 hover:bg-primary-600 transition-all shadow-lg shadow-primary-900/20">
                        Get Started
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="relative pt-20 lg:pt-28 pb-20">
        <div class="max-w-7xl mx-auto px-6 lg:px-8 relative z-10">
            <div class="grid lg:grid-cols-2 gap-12 items-center">
                <div>
                    <h1 class="text-sm md:text-base font-bold text-primary-500 uppercase tracking-widest mb-4">Official Chrome Extension</h1>
                    <h2 class="text-4xl lg:text-5xl font-extrabold tracking-tight text-white mb-6 leading-tight">
                        Boost Your SEO Workflow <br>
                        <span class="text-primary-500">Directly from Browser</span>
                    </h2>

                    <p class="text-lg text-gray-400 mb-8 leading-relaxed">
                        Stop switching tabs to copy-paste links. Instantly submit URLs to Google and Yandex, manage viral traffic campaigns, and check your credit balance directly from your browser.
                    </p>

                    <div class="flex flex-col sm:flex-row gap-4">
                        <a href="https://github.com/backlinkz-io/Fastest-Website-Indexer" target="_blank" class="inline-flex justify-center items-center px-8 py-4 bg-primary-700 text-white font-bold rounded-lg hover:bg-primary-600 transition-all shadow-lg shadow-primary-900/30 text-lg">
                            <i class="fab fa-github mr-3 text-xl"></i>
                            Download from GitHub
                        </a>
                         <button disabled class="inline-flex justify-center items-center px-8 py-4 bg-gray-800 text-gray-500 font-bold rounded-lg border border-gray-700 cursor-not-allowed text-lg">
                            <i class="fab fa-chrome mr-3 text-xl"></i>
                            Store (Coming Soon)
                        </button>
                    </div>
                </div>
                <div class="relative">
                    <div class="absolute -inset-4 bg-primary-900/20 rounded-full blur-3xl opacity-50"></div>
                    <img src="/assets/img/dashboard-preview.png" alt="Rapid Indexer Extension Preview" class="relative rounded-xl border border-white/10 shadow-2xl">
                </div>
            </div>
        </div>
    </section>

    <!-- Key Features -->
    <section id="features" class="py-24 bg-[#111111] border-t border-white/5">
        <div class="max-w-7xl mx-auto px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl md:text-4xl font-bold text-white mb-4">Powerful Features at Your Fingertips</h2>
                <p class="text-lg text-gray-400">Whether you are doing a site audit, building backlinks, or publishing new content.</p>
            </div>

            <div class="grid md:grid-cols-3 gap-8">
                <!-- Feature 1 -->
                <div class="p-8 rounded-xl card hover:border-primary-700/50 transition-colors">
                    <div class="w-12 h-12 bg-primary-900/20 rounded-lg flex items-center justify-center mb-6 text-primary-500 border border-primary-900/30">
                        <i class="fas fa-bolt text-xl"></i>
                    </div>
                    <h3 class="text-xl font-bold text-white mb-3">Instant Indexing</h3>
                    <p class="text-gray-400">Submit the page you are currently viewing with a single click. Never miss indexing a new post again.</p>
                </div>

                <!-- Feature 2 -->
                <div class="p-8 rounded-xl card hover:border-primary-700/50 transition-colors">
                    <div class="w-12 h-12 bg-primary-900/20 rounded-lg flex items-center justify-center mb-6 text-primary-500 border border-primary-900/30">
                        <i class="fas fa-mouse-pointer text-xl"></i>
                    </div>
                    <h3 class="text-xl font-bold text-white mb-3">Context Menu Support</h3>
                    <p class="text-gray-400">Right-click any link on any webpage and select "Rapid Indexer" to index it immediately.</p>
                </div>

                <!-- Feature 3 -->
                <div class="p-8 rounded-xl card hover:border-primary-700/50 transition-colors">
                    <div class="w-12 h-12 bg-primary-900/20 rounded-lg flex items-center justify-center mb-6 text-primary-500 border border-primary-900/30">
                        <i class="fas fa-layer-group text-xl"></i>
                    </div>
                    <h3 class="text-xl font-bold text-white mb-3">Bulk Submission</h3>
                    <p class="text-gray-400">Paste lists of URLs to process multiple pages at once directly from the extension interface.</p>
                </div>

                <!-- Feature 4 -->
                <div class="p-8 rounded-xl card hover:border-primary-700/50 transition-colors">
                    <div class="w-12 h-12 bg-primary-900/20 rounded-lg flex items-center justify-center mb-6 text-primary-500 border border-primary-900/30">
                        <i class="fas fa-crown text-xl"></i>
                    </div>
                    <h3 class="text-xl font-bold text-white mb-3">VIP Queue</h3>
                    <p class="text-gray-400">Toggle the VIP option for instant indexing results in under 1 minute for critical pages.</p>
                </div>

                <!-- Feature 5 -->
                <div class="p-8 rounded-xl card hover:border-primary-700/50 transition-colors">
                    <div class="w-12 h-12 bg-primary-900/20 rounded-lg flex items-center justify-center mb-6 text-primary-500 border border-primary-900/30">
                        <i class="fas fa-clock text-xl"></i>
                    </div>
                    <h3 class="text-xl font-bold text-white mb-3">Drip Feed</h3>
                    <p class="text-gray-400">Schedule your submissions to be spread out over days for natural growth pattern.</p>
                </div>

                <!-- Feature 6 -->
                <div class="p-8 rounded-xl card hover:border-primary-700/50 transition-colors">
                    <div class="w-12 h-12 bg-primary-900/20 rounded-lg flex items-center justify-center mb-6 text-primary-500 border border-primary-900/30">
                        <i class="fas fa-calculator text-xl"></i>
                    </div>
                    <h3 class="text-xl font-bold text-white mb-3">Cost Calculator</h3>
                    <p class="text-gray-400">Automatically see the estimated credit cost for indexing or traffic tasks before you submit.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Viral Traffic Generator -->
    <section class="py-24 bg-black/20 relative overflow-hidden">
        <div class="absolute inset-0 bg-primary-900/5 pointer-events-none"></div>
        <div class="max-w-7xl mx-auto px-6 lg:px-8 relative">
            <div class="grid lg:grid-cols-2 gap-16 items-center">
                <div class="order-2 lg:order-1">
                    <h2 class="text-3xl md:text-4xl font-bold text-white mb-6">📈 Viral Traffic Generator</h2>
                    <p class="text-lg text-gray-400 mb-8">Drive real traffic to your URLs directly from the extension. Perfect for signaling popularity to search algorithms.</p>
                    
                    <ul class="space-y-6">
                        <li class="flex items-start gap-4">
                            <div class="w-8 h-8 rounded-full bg-primary-900/50 flex items-center justify-center flex-shrink-0 text-primary-500 mt-1">
                                <i class="fas fa-check text-sm"></i>
                            </div>
                            <div>
                                <h4 class="text-white font-bold mb-1">Precision Targeting</h4>
                                <p class="text-gray-400 text-sm">Select visitor count, geo-location (Country), and device type (Mobile/Desktop).</p>
                            </div>
                        </li>
                        <li class="flex items-start gap-4">
                            <div class="w-8 h-8 rounded-full bg-primary-900/50 flex items-center justify-center flex-shrink-0 text-primary-500 mt-1">
                                <i class="fas fa-check text-sm"></i>
                            </div>
                            <div>
                                <h4 class="text-white font-bold mb-1">Traffic Sources</h4>
                                <p class="text-gray-400 text-sm">Simulate traffic from Social Media, Google Keywords, or Direct visits.</p>
                            </div>
                        </li>
                        <li class="flex items-start gap-4">
                            <div class="w-8 h-8 rounded-full bg-primary-900/50 flex items-center justify-center flex-shrink-0 text-primary-500 mt-1">
                                <i class="fas fa-check text-sm"></i>
                            </div>
                            <div>
                                <h4 class="text-white font-bold mb-1">Smart Referrers</h4>
                                <p class="text-gray-400 text-sm">Use the "Current Page" feature to easily set referring URLs (e.g., simulate traffic coming from a specific tweet or article).</p>
                            </div>
                        </li>
                    </ul>
                </div>
                <div class="order-1 lg:order-2">
                     <div class="card p-2 rounded-xl border border-white/10 bg-[#1c1c1c] shadow-2xl transform rotate-1 hover:rotate-0 transition-transform duration-500">
                        <img src="/assets/img/viral-website-traffic.png" onerror="this.src='/assets/img/dashboard-preview.png'" alt="Viral Traffic Dashboard" class="rounded-lg w-full">
                     </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Getting Started -->
    <section class="py-24 border-t border-white/5">
        <div class="max-w-4xl mx-auto px-6 lg:px-8 text-center">
            <h2 class="text-3xl md:text-4xl font-bold text-white mb-16">Get Started in 3 Steps</h2>
            
            <div class="grid md:grid-cols-3 gap-8 relative">
                <!-- Connector Line -->
                <div class="hidden md:block absolute top-12 left-0 right-0 h-0.5 bg-gradient-to-r from-transparent via-primary-900/50 to-transparent -z-10"></div>

                <div class="relative">
                    <div class="w-24 h-24 mx-auto bg-[#1c1c1c] rounded-full border border-primary-500/30 flex items-center justify-center mb-6 shadow-lg shadow-primary-900/20 z-10">
                        <span class="text-4xl font-bold text-primary-500">1</span>
                    </div>
                    <h3 class="text-xl font-bold text-white mb-2">Install Extension</h3>
                    <p class="text-gray-400 text-sm">Download the extension from GitHub and load it unpacked into Chrome.</p>
                </div>

                <div class="relative">
                    <div class="w-24 h-24 mx-auto bg-[#1c1c1c] rounded-full border border-primary-500/30 flex items-center justify-center mb-6 shadow-lg shadow-primary-900/20 z-10">
                        <span class="text-4xl font-bold text-primary-500">2</span>
                    </div>
                    <h3 class="text-xl font-bold text-white mb-2">Enter API Key</h3>
                    <p class="text-gray-400 text-sm">Get your API key from the Rapid Indexer dashboard and paste it in.</p>
                </div>

                <div class="relative">
                    <div class="w-24 h-24 mx-auto bg-[#1c1c1c] rounded-full border border-primary-500/30 flex items-center justify-center mb-6 shadow-lg shadow-primary-900/20 z-10">
                        <span class="text-4xl font-bold text-primary-500">3</span>
                    </div>
                    <h3 class="text-xl font-bold text-white mb-2">Start Indexing</h3>
                    <p class="text-gray-400 text-sm">Navigate to any page and start indexing or driving traffic instantly.</p>
                </div>
            </div>

            <div class="mt-16 flex flex-col items-center gap-4">
                <a href="https://github.com/backlinkz-io/Fastest-Website-Indexer" target="_blank" class="inline-flex justify-center items-center px-10 py-5 bg-gradient-to-r from-primary-600 to-primary-700 text-white font-bold rounded-xl hover:from-primary-500 hover:to-primary-600 transition-all shadow-xl shadow-primary-900/30 text-xl transform hover:-translate-y-1">
                    <i class="fab fa-github mr-3"></i>
                    Download from GitHub
                </a>
                <p class="text-gray-400 font-medium"><i class="fab fa-chrome mr-2"></i> Chrome Web Store version coming soon</p>
                <p class="mt-2 text-gray-500 text-sm">Requires a Rapid Indexer account. <a href="/register" class="text-primary-400 hover:text-white">Sign up free</a>.</p>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="border-t border-white/5 bg-black/20 py-12 mt-auto">
        <div class="max-w-7xl mx-auto px-6 lg:px-8 flex flex-col md:flex-row justify-between items-center gap-6">
            <div class="flex items-center gap-3">
                <div class="flex flex-col items-center text-primary-500">
                    <i class="fas fa-rocket text-xl"></i>
                </div>
                <span class="text-white font-bold text-lg">Rapid Indexer</span>
            </div>
            <div class="text-gray-500 text-sm">
                © <?php echo date('Y'); ?> Rapid Indexer. All rights reserved.
            </div>
            <div class="flex gap-6 text-sm font-medium text-gray-400">
                <a href="/privacy" class="hover:text-white transition-colors">Privacy</a>
                <a href="/terms" class="hover:text-white transition-colors">Terms</a>
                <a href="/contact" class="hover:text-white transition-colors">Contact</a>
                <a href="/chrome-extension" class="hover:text-white transition-colors text-primary-500">Chrome Extension</a>
            </div>
        </div>
    </footer>
</body>
</html>

