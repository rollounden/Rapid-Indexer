<?php
require_once __DIR__ . '/config/config.php';
session_start();

// Redirect logged-in users to dashboard
if (isset($_SESSION['uid'])) {
    header('Location: /dashboard');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en" class="dark">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Rapid Indexer - Premium SEO Link Indexing & Checking Service</title>
    <meta name="description"
        content="Boost your SEO with Rapid Indexer. We use tiered links and browser traffic to guarantee Google indexation. Monitor your backlinks with our advanced checker.">

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
                        // Custom "less intense" red/rose palette
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
                        // Lighter black / dark grey palette for background
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
                            850: '#262626', // Lighter than pure black
                            900: '#1a1a1a', // Main background
                            950: '#111111', // Deepest background
                        }
                    }
                }
            }
        }
    </script>

    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        [x-cloak] {
            display: none !important;
        }

        * {
            scroll-behavior: smooth;
        }
        
        body {
            background-color: #141414; /* Matches the reference "lighter black" */
            color: #efefef;
        }

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
        
        .feature-icon-box {
            background: linear-gradient(135deg, #be123c 0%, #881337 100%);
            box-shadow: 0 4px 20px -2px rgba(190, 18, 60, 0.4);
        }
    </style>
</head>

<body class="antialiased min-h-screen flex flex-col">
    <!-- Navigation -->
    <div class="sticky top-0 z-50 hidden lg:block">
        <div class="bg-primary-900 text-white border-b border-primary-800">
            <div class="flex w-full items-center justify-center gap-5 px-4 py-2 sm:px-6 lg:px-8">
                <p class="text-sm font-medium tracking-wide">
                    <span class="bg-white/10 px-2 py-0.5 rounded text-xs mr-2 font-bold">NEW</span>
                    Now with 70% Faster Indexing Engine
                </p>
            </div>
        </div>
    </div>

    <nav class="w-full border-b border-white/5 nav-blur sticky top-0 z-40 lg:top-[37px]">
        <div class="max-w-7xl mx-auto px-6 lg:px-8">
            <div class="flex justify-between items-center h-20">

                <!-- Styled logo -->
                <div class="flex items-center gap-3">
                    <div class="flex flex-col items-center text-primary-500">
                        <i class="fas fa-rocket text-3xl"></i>
                    </div>
                    <span class="font-bold text-2xl tracking-tight text-white">Rapid Indexer</span>
                </div>

                <!-- Navigation Links -->
                <nav class="hidden md:flex items-center gap-8">
                    <a href="#features" class="text-sm font-medium text-gray-300 hover:text-white transition-colors">Features</a>
                    <a href="#pricing" class="text-sm font-medium text-gray-300 hover:text-white transition-colors">Pricing</a>
                    <a href="/faq.php" class="text-sm font-medium text-gray-300 hover:text-white transition-colors">FAQ</a>
                </nav>

                <div class="flex items-center gap-4">
                    <a href="/login.php" class="text-sm font-medium text-gray-300 hover:text-white transition-colors">Log in</a>
                    <a href="/register.php" class="inline-flex items-center px-5 py-2.5 border border-transparent text-sm font-bold rounded-lg text-white bg-primary-700 hover:bg-primary-600 transition-all shadow-lg shadow-primary-900/20">
                        Get Started
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="relative pt-20 lg:pt-28 pb-20">
        <div class="max-w-7xl mx-auto px-6 lg:px-8 relative z-10">
            <div class="text-center max-w-4xl mx-auto">
                <h1 class="text-4xl lg:text-6xl font-extrabold tracking-tight text-white mb-8 leading-tight">
                    Backlinks have zero value <br>
                    <span class="text-primary-500">until they're indexed.</span>
                </h1>

                <p class="text-xl text-gray-400 max-w-2xl mx-auto mb-10 leading-relaxed">
                    "What can't be measured doesn't exist". Rapid Indexer uses a multi-pronged system to speed up and help indexation to power up pages with positive metrics.
                </p>

                <div class="flex flex-col sm:flex-row gap-4 justify-center items-center mb-12">
                    <a href="/register.php" class="w-full sm:w-auto inline-flex justify-center items-center px-8 py-4 bg-primary-700 text-white font-bold rounded-lg hover:bg-primary-600 transition-all shadow-lg shadow-primary-900/30 text-lg">
                        Start Indexing Free
                        <i class="fa-solid fa-arrow-right ml-3 text-sm"></i>
                    </a>
                    <a href="#how-it-works" class="w-full sm:w-auto inline-flex justify-center items-center px-8 py-4 border border-white/10 text-white font-medium rounded-lg hover:bg-white/5 transition-colors text-lg">
                        How it Works
                    </a>
                </div>
                
                <p class="text-sm text-gray-500">
                    No credit card required • 30 Free Credits on Signup
                </p>
            </div>

            <!-- Demo visualization -->
            <div class="mt-20 max-w-4xl mx-auto" x-data="{ showModal: false, urlCount: 0, demoUrls: '' }" x-init="$watch('demoUrls', () => {
                if (!demoUrls) { urlCount = 0; } else { urlCount = demoUrls.split('\n').filter(line => line.trim().length > 0).length; }
            });">
                <div class="card rounded-xl p-6 md:p-8">
                    <div class="flex items-center justify-between mb-6">
                        <div>
                            <h2 class="text-lg font-bold text-white flex items-center gap-2">
                                <i class="fa-solid fa-bolt text-primary-500"></i>
                                Quick Submit
                            </h2>
                            <p class="text-sm text-gray-400 mt-1">Enter URLs to index immediately</p>
                        </div>
                        <div class="hidden md:block text-xs font-medium text-gray-400 bg-white/5 px-3 py-1 rounded-full border border-white/5">
                            Max 10,000 URLs / batch
                        </div>
                    </div>

                    <form class="space-y-4">
                        <div class="relative">
                            <textarea class="block w-full border rounded-lg p-4 bg-black/20 text-gray-300 border-white/10 focus:ring-primary-600 focus:border-primary-600 resize-y min-h-[160px] font-mono text-sm" 
                                rows="6" 
                                x-model="demoUrls" 
                                placeholder="https://example.com/page-1&#10;https://example.com/page-2&#10;https://example.com/page-3" 
                                readonly 
                                x-on:click="showModal = true"></textarea>
                        </div>

                        <div class="flex flex-col sm:flex-row gap-4 justify-between items-center pt-2">
                            <div class="flex items-center gap-2 text-sm">
                                <span class="text-gray-400">Estimated Cost:</span>
                                <span class="px-3 py-1 rounded-md bg-primary-900/20 border border-primary-900/30 text-primary-400 font-mono font-bold" x-text="urlCount + ' Credits'">0 Credits</span>
                            </div>
                            <button type="button" class="w-full sm:w-auto px-6 py-3 text-sm font-bold rounded-lg bg-white text-black hover:bg-gray-200 transition-all" x-on:click.prevent="showModal = true">
                                Submit to Indexer
                            </button>
                        </div>
                    </form>
                </div>
                
                <!-- Modal -->
                <div x-show="showModal" x-cloak class="fixed inset-0 z-[100] flex items-center justify-center px-4 sm:px-0">
                    <div x-show="showModal" x-transition.opacity @click="showModal = false" class="absolute inset-0 bg-black/80 backdrop-blur-sm"></div>
                    <div x-show="showModal" x-transition.scale.origin.center class="relative card rounded-xl p-8 max-w-md w-full shadow-2xl border border-white/10">
                        <button @click="showModal = false" class="absolute top-4 right-4 text-gray-500 hover:text-white transition-colors">
                            <i class="fa-solid fa-times text-xl"></i>
                        </button>
                        <div class="text-center mb-8">
                            <div class="w-14 h-14 bg-primary-900/30 rounded-full flex items-center justify-center mx-auto mb-4 text-primary-500 border border-primary-900/50">
                                <i class="fa-solid fa-user-plus text-xl"></i>
                            </div>
                            <h3 class="text-2xl font-bold text-white mb-2">Create Free Account</h3>
                            <p class="text-gray-400">Sign up to submit your URLs. You'll get <span class="text-primary-400 font-bold">30 free credits</span> instantly.</p>
                        </div>
                        <div class="space-y-3">
                            <a href="/register.php" class="block w-full py-3 text-center rounded-lg bg-primary-700 hover:bg-primary-600 text-white font-bold transition-colors">Sign Up Free</a>
                            <a href="/login.php" class="block w-full py-3 text-center rounded-lg bg-white/5 hover:bg-white/10 text-white font-medium transition-colors border border-white/10">Log In</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Why Rapid Indexer Section (SEO Content) -->
    <section id="why-us" class="py-24 bg-black/20 border-y border-white/5">
        <div class="max-w-7xl mx-auto px-6 lg:px-8">
            <div class="grid md:grid-cols-2 gap-12 items-center">
                <div>
                    <h2 class="text-3xl md:text-4xl font-bold text-white mb-6">Why Most Indexing Services Fail</h2>
                    <div class="space-y-6 text-lg text-gray-400">
                        <p>
                            Many link-indexing services have died off or become irrelevant because they can't adapt. 
                            <strong class="text-primary-400">Submitting directly to Google API alone doesn't cut it anymore</strong> and has strict cap limits.
                        </p>
                        <p>
                            You need indexing services that go the extra mile to ensure you aren't just asking Google for a handout.
                        </p>
                        <p>
                            Your pages need to be powered up with positive metrics like <span class="text-white font-semibold">tiered-links</span> and <span class="text-white font-semibold">browser traffic</span>. This is where Rapid Indexer shines.
                        </p>
                    </div>
                </div>
                <div class="relative">
                    <div class="absolute -inset-4 bg-primary-900/20 rounded-full blur-3xl opacity-50"></div>
                    <div class="card p-8 rounded-xl relative border-primary-900/50">
                        <h3 class="text-xl font-bold text-white mb-4">Our Multi-Pronged Approach</h3>
                        <ul class="space-y-4">
                            <li class="flex items-start gap-3">
                                <div class="w-6 h-6 rounded-full bg-primary-900/50 flex items-center justify-center flex-shrink-0 mt-1 text-primary-500">
                                    <i class="fa-solid fa-check text-xs"></i>
                                </div>
                                <span class="text-gray-300">Direct submission signals to search engines</span>
                            </li>
                            <li class="flex items-start gap-3">
                                <div class="w-6 h-6 rounded-full bg-primary-900/50 flex items-center justify-center flex-shrink-0 mt-1 text-primary-500">
                                    <i class="fa-solid fa-check text-xs"></i>
                                </div>
                                <span class="text-gray-300">Tiered linking structure for authority</span>
                            </li>
                            <li class="flex items-start gap-3">
                                <div class="w-6 h-6 rounded-full bg-primary-900/50 flex items-center justify-center flex-shrink-0 mt-1 text-primary-500">
                                    <i class="fa-solid fa-check text-xs"></i>
                                </div>
                                <span class="text-gray-300">Real browser traffic simulation</span>
                            </li>
                            <li class="flex items-start gap-3">
                                <div class="w-6 h-6 rounded-full bg-primary-900/50 flex items-center justify-center flex-shrink-0 mt-1 text-primary-500">
                                    <i class="fa-solid fa-check text-xs"></i>
                                </div>
                                <span class="text-gray-300">Detailed index status verification</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Stats Strip -->
    <div class="border-b border-white/5 bg-black/20 backdrop-blur-sm">
        <div class="max-w-7xl mx-auto px-6 lg:px-8 py-12">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-8 text-center">
                <div>
                    <div class="text-3xl md:text-4xl font-bold text-white mb-1">1.2M+</div>
                    <div class="text-sm text-gray-500 uppercase tracking-wider font-bold">Pages Indexed</div>
                </div>
                <div>
                    <div class="text-3xl md:text-4xl font-bold text-white mb-1">98%</div>
                    <div class="text-sm text-gray-500 uppercase tracking-wider font-bold">Success Rate</div>
                </div>
                <div>
                    <div class="text-3xl md:text-4xl font-bold text-white mb-1">9m</div>
                    <div class="text-sm text-gray-500 uppercase tracking-wider font-bold">Crawler Time</div>
                </div>
                <div>
                    <div class="text-3xl md:text-4xl font-bold text-white mb-1">24/7</div>
                    <div class="text-sm text-gray-500 uppercase tracking-wider font-bold">System Uptime</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Features Grid -->
    <section id="features" class="py-24 relative">
        <div class="max-w-7xl mx-auto px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl md:text-4xl font-bold text-white mb-4">Built for SEO Performance</h2>
                <p class="text-lg text-gray-400 max-w-2xl mx-auto">Everything you need to get your content ranked faster.</p>
            </div>

            <div class="grid md:grid-cols-3 gap-6">
                <!-- Card 1 -->
                <div class="p-8 rounded-xl card hover:border-primary-700/50 transition-colors group">
                    <div class="w-12 h-12 bg-primary-900/20 rounded-lg flex items-center justify-center mb-6 group-hover:bg-primary-900/30 transition-colors border border-primary-900/30">
                        <i class="fa-solid fa-rocket text-primary-500 text-xl"></i>
                    </div>
                    <h3 class="text-xl font-bold text-white mb-3">Instant Discovery</h3>
                    <p class="text-gray-400">Direct submission signals to search engines force them to crawl your URLs immediately.</p>
                </div>

                <!-- Card 2 -->
                <div class="p-8 rounded-xl card hover:border-primary-700/50 transition-colors group">
                    <div class="w-12 h-12 bg-primary-900/20 rounded-lg flex items-center justify-center mb-6 group-hover:bg-primary-900/30 transition-colors border border-primary-900/30">
                        <i class="fa-solid fa-check-double text-primary-500 text-xl"></i>
                    </div>
                    <h3 class="text-xl font-bold text-white mb-3">Verification</h3>
                    <p class="text-gray-400">We don't just submit; we check if Google actually indexed your page and report the status.</p>
                </div>

                <!-- Card 3 -->
                <div class="p-8 rounded-xl card hover:border-primary-700/50 transition-colors group">
                    <div class="w-12 h-12 bg-primary-900/20 rounded-lg flex items-center justify-center mb-6 group-hover:bg-primary-900/30 transition-colors border border-primary-900/30">
                        <i class="fa-solid fa-layer-group text-primary-500 text-xl"></i>
                    </div>
                    <h3 class="text-xl font-bold text-white mb-3">Bulk Processing</h3>
                    <p class="text-gray-400">Submit up to 10,000 URLs in a single batch. Perfect for large programmatic SEO sites.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Pricing -->
    <section id="pricing" class="py-24 bg-black/20">
        <div class="max-w-7xl mx-auto px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl md:text-4xl font-bold text-white mb-4">Simple Pay-As-You-Go</h2>
                <p class="text-lg text-gray-400">Get started with our premium indexing service today, for as little as $0.02 per indexed page!</p>
            </div>

            <div class="grid md:grid-cols-2 gap-8 max-w-4xl mx-auto">
                <!-- Standard -->
                <div class="rounded-xl p-8 card flex flex-col">
                    <h3 class="text-lg font-bold text-white mb-2 uppercase tracking-wide">Link Checking</h3>
                    <div class="flex items-baseline gap-1 mb-6">
                        <span class="text-4xl font-extrabold text-white">$0.01</span>
                        <span class="text-gray-500">/ URL</span>
                    </div>
                    <ul class="space-y-4 mb-8 text-gray-300 flex-1">
                        <li class="flex items-center gap-3"><i class="fa-solid fa-check text-primary-600 text-sm"></i> Check Index Status</li>
                        <li class="flex items-center gap-3"><i class="fa-solid fa-check text-primary-600 text-sm"></i> Real-time Verification</li>
                        <li class="flex items-center gap-3"><i class="fa-solid fa-check text-primary-600 text-sm"></i> Export Reports</li>
                    </ul>
                    <a href="/register.php" class="block w-full py-3 px-6 rounded-lg border border-white/10 bg-white/5 hover:bg-white/10 text-white font-bold text-center transition-colors">Get Started</a>
                </div>

                <!-- Premium -->
                <div class="rounded-xl p-8 card border-primary-700 relative overflow-hidden flex flex-col">
                    <div class="absolute top-0 right-0 bg-primary-700 text-white text-xs font-bold px-3 py-1 rounded-bl-lg">RECOMMENDED</div>
                    <h3 class="text-lg font-bold text-white mb-2 uppercase tracking-wide">Premium Indexing</h3>
                    <div class="flex items-baseline gap-1 mb-6">
                        <span class="text-4xl font-extrabold text-primary-500">$0.02</span>
                        <span class="text-gray-500">/ URL</span>
                    </div>
                    <ul class="space-y-4 mb-8 text-gray-300 flex-1">
                        <li class="flex items-center gap-3"><i class="fa-solid fa-check text-primary-500 text-sm"></i> Force Google Crawl</li>
                        <li class="flex items-center gap-3"><i class="fa-solid fa-check text-primary-500 text-sm"></i> Tiered Links & Traffic</li>
                        <li class="flex items-center gap-3"><i class="fa-solid fa-check text-primary-500 text-sm"></i> 98% Success Rate</li>
                    </ul>
                    <a href="/register.php" class="block w-full py-3 px-6 rounded-lg bg-primary-700 hover:bg-primary-600 text-white font-bold text-center transition-colors shadow-lg shadow-primary-900/20">Start Indexing</a>
                </div>
            </div>

            <!-- Payment Methods -->
            <div class="mt-16 text-center">
                <p class="text-sm text-gray-500 mb-6 uppercase tracking-wider font-semibold">Secure Crypto Payments</p>
                <div class="flex flex-wrap justify-center items-center gap-6">
                    <!-- Crypto Images -->
                    <div class="flex items-center gap-6 opacity-80 hover:opacity-100 transition-opacity">
                        <img src="/assets/img/bitcoin-btc-logo.png" alt="Bitcoin" class="h-8 w-auto object-contain">
                        <img src="/assets/img/ethereum-eth-logo.png" alt="Ethereum" class="h-8 w-auto object-contain">
                        <img src="/assets/img/Zcash-Yellow.png" alt="Zcash" class="h-8 w-auto object-contain">
                        
                        <span class="text-white font-bold flex items-center gap-2 border border-white/10 px-4 py-2 rounded-full text-sm bg-white/5">
                            <i class="fa-solid fa-coins text-primary-500"></i>
                            Crypto Only
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <footer class="border-t border-white/5 bg-black/20 py-12 mt-auto">
        <div class="max-w-7xl mx-auto px-6 lg:px-8 flex flex-col md:flex-row justify-between items-center gap-6">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 bg-primary-700 rounded flex items-center justify-center text-white font-bold">
                    R
                </div>
                <span class="text-white font-bold text-lg">Rapid Indexer</span>
            </div>
            <div class="text-gray-500 text-sm">
                © <?php echo date('Y'); ?> Rapid Indexer. All rights reserved.
            </div>
            <div class="flex gap-6 text-sm font-medium text-gray-400">
                <a href="/privacy.php" class="hover:text-white transition-colors">Privacy</a>
                <a href="/terms.php" class="hover:text-white transition-colors">Terms</a>
                <a href="/contact.php" class="hover:text-white transition-colors">Contact</a>
            </div>
        </div>
    </footer>
</body>
</html>
