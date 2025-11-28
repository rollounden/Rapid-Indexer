<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/src/SettingsService.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$free_credits = SettingsService::get('free_credits_on_signup', '30');

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
    <meta name="description" content="Boost your SEO with Rapid Indexer. We use tiered links and browser traffic to guarantee Google indexation. Monitor your backlinks with our advanced checker.">
    <meta name="keywords" content="seo link indexing, google indexer, backlink checker, rapid indexer, seo tools, fast indexing, url submission">
    <link rel="canonical" href="https://rapid-indexer.com/">
    <meta name="robots" content="index, follow">
    
    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="https://rapid-indexer.com/">
    <meta property="og:title" content="Rapid Indexer - Premium SEO Link Indexing & Checking Service">
    <meta property="og:description" content="Boost your SEO with Rapid Indexer. We use tiered links and browser traffic to guarantee Google indexation. Monitor your backlinks with our advanced checker.">
    <meta property="og:image" content="https://rapid-indexer.com/assets/img/dashboard-preview.png">

    <meta property="twitter:card" content="summary_large_image">
    <meta property="twitter:url" content="https://rapid-indexer.com/">
    <meta property="twitter:title" content="Rapid Indexer - Premium SEO Link Indexing & Checking Service">
    <meta property="twitter:description" content="Boost your SEO with Rapid Indexer. We use tiered links and browser traffic to guarantee Google indexation. Monitor your backlinks with our advanced checker.">
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
                    <a href="/viral-blast.php" class="text-sm font-medium text-primary-400 hover:text-white transition-colors flex items-center gap-1"><i class="fas fa-fire"></i> Viral Blast</a>
                    <div class="relative group" x-data="{ open: false }">
                        <button @click="open = !open" @click.outside="open = false" class="text-sm font-medium text-gray-300 hover:text-white transition-colors flex items-center gap-1">
                            How it Works <i class="fas fa-chevron-down text-xs ml-1 transition-transform" :class="{ 'rotate-180': open }"></i>
                        </button>
                        <div x-show="open" x-transition.opacity.duration.200ms class="absolute top-full left-0 mt-2 w-64 bg-[#1a1a1a] border border-white/10 rounded-xl shadow-xl py-2 z-50" style="display: none;">
                            <a href="#submit-links" class="block px-4 py-3 hover:bg-white/5 transition-colors">
                                <div class="text-white font-bold text-sm">Submitting Links</div>
                                <div class="text-xs text-gray-500">How to start an indexing campaign</div>
                            </a>
                            <a href="#check-links" class="block px-4 py-3 hover:bg-white/5 transition-colors">
                                <div class="text-white font-bold text-sm">Checking Links</div>
                                <div class="text-xs text-gray-500">Verify indexing status</div>
                            </a>
                            <a href="#buy-credits" class="block px-4 py-3 hover:bg-white/5 transition-colors">
                                <div class="text-white font-bold text-sm">Buying Credits</div>
                                <div class="text-xs text-gray-500">Purchase via Cryptomus</div>
                            </a>
                        </div>
                    </div>
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
                <h1 class="text-sm md:text-base font-bold text-primary-500 uppercase tracking-widest mb-4">SEO Signals Platform</h1>
                <h2 class="text-4xl lg:text-6xl font-extrabold tracking-tight text-white mb-8 leading-tight">
                    Get Indexed. <span class="text-primary-500">Go Viral.</span><br>
                    Rank Higher.
                </h2>

                <p class="text-xl text-gray-400 max-w-2xl mx-auto mb-10 leading-relaxed">
                    Don't just build links. Power them up. Rapid Indexer forces Google to crawl your backlinks and simulates viral social traffic to boost your rankings.
                </p>

                <div class="flex flex-col sm:flex-row gap-4 justify-center items-center mb-12">
                    <a href="/register.php" class="w-full sm:w-auto inline-flex justify-center items-center px-8 py-4 bg-primary-700 text-white font-bold rounded-lg hover:bg-primary-600 transition-all shadow-lg shadow-primary-900/30 text-lg">
                        Start Boosting Free
                        <i class="fa-solid fa-arrow-right ml-3 text-sm"></i>
                    </a>
                    <a href="#how-it-works" class="w-full sm:w-auto inline-flex justify-center items-center px-8 py-4 border border-white/10 text-white font-medium rounded-lg hover:bg-white/5 transition-colors text-lg">
                        How it Works
                    </a>
                </div>
                
                <p class="text-sm text-gray-500">
                    No credit card required • <?php echo $free_credits; ?> Free Credits on Signup
                </p>
            </div>

            <div class="mt-20 grid md:grid-cols-2 gap-8 max-w-6xl mx-auto">
                <!-- Indexer Demo -->
                <div class="card rounded-xl p-6 md:p-8 border-white/10 hover:border-primary-500/30 transition-colors" x-data="{ showModal: false, urlCount: 0, demoUrls: '' }" x-init="$watch('demoUrls', () => {
                    if (!demoUrls) { urlCount = 0; } else { urlCount = demoUrls.split('\n').filter(line => line.trim().length > 0).length; }
                });">
                    <div class="flex items-center justify-between mb-6">
                        <div>
                            <h2 class="text-lg font-bold text-white flex items-center gap-2">
                                <i class="fa-solid fa-bolt text-primary-500"></i>
                                Quick Indexer
                            </h2>
                            <p class="text-sm text-gray-400 mt-1">Submit URLs to index immediately</p>
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
                                <p class="text-gray-400">Sign up to submit your URLs. You'll get <span class="text-primary-400 font-bold"><?php echo $free_credits; ?> free credits</span> instantly.</p>
                            </div>
                            <div class="space-y-3">
                                <a href="/register.php" class="block w-full py-3 text-center rounded-lg bg-primary-700 hover:bg-primary-600 text-white font-bold transition-colors">Sign Up Free</a>
                                <a href="/login.php" class="block w-full py-3 text-center rounded-lg bg-white/5 hover:bg-white/10 text-white font-medium transition-colors border border-white/10">Log In</a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Traffic Demo (New) -->
                <div class="card rounded-xl p-6 md:p-8 border-primary-600/30 bg-primary-900/5 hover:border-primary-500/50 transition-all relative overflow-hidden" x-data="{ showTrafficModal: false, trafficQty: 4000 }">
                    <div class="absolute top-0 right-0 bg-primary-600 text-white text-[10px] font-bold px-2 py-1 rounded-bl-lg uppercase">New Feature</div>
                    
                    <div class="flex items-center justify-between mb-6">
                        <div>
                            <h2 class="text-lg font-bold text-white flex items-center gap-2">
                                <i class="fa-solid fa-fire-flame-curved text-primary-500"></i>
                                Viral Traffic Blast
                            </h2>
                            <p class="text-sm text-gray-400 mt-1">Simulate viral social traffic spikes</p>
                        </div>
                    </div>

                    <form class="space-y-4">
                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1 uppercase">Target URL</label>
                            <div class="relative">
                                <input type="text" class="block w-full border rounded-lg p-3 bg-black/20 text-gray-300 border-white/10" 
                                    placeholder="https://example.com/viral-post" readonly x-on:click="showTrafficModal = true">
                                <div class="absolute right-3 top-3 text-gray-500"><i class="fas fa-link"></i></div>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-medium text-gray-500 mb-1 uppercase">Visitors</label>
                                <select class="block w-full border rounded-lg p-3 bg-black/20 text-gray-300 border-white/10 appearance-none" x-model="trafficQty" x-on:click.prevent="showTrafficModal = true">
                                    <option value="1000">1,000</option>
                                    <option value="4000" selected>4,000</option>
                                    <option value="10000">10,000</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-500 mb-1 uppercase">Duration</label>
                                <div class="block w-full border rounded-lg p-3 bg-black/20 text-gray-300 border-white/10">3 Days</div>
                            </div>
                        </div>

                        <div class="flex flex-col sm:flex-row gap-4 justify-between items-center pt-2">
                            <div class="flex items-center gap-2 text-sm">
                                <span class="text-gray-400">Estimated Cost:</span>
                                <span class="px-3 py-1 rounded-md bg-primary-900/20 border border-primary-900/30 text-primary-400 font-mono font-bold" x-text="Math.ceil((trafficQty / 1000) * 60) + ' Credits'">240 Credits</span>
                            </div>
                            <button type="button" class="w-full sm:w-auto px-6 py-3 text-sm font-bold rounded-lg bg-gradient-to-r from-primary-600 to-primary-500 text-white hover:from-primary-500 hover:to-primary-400 transition-all shadow-lg shadow-primary-900/20" x-on:click.prevent="showTrafficModal = true">
                                Launch Blast <i class="fas fa-rocket ml-1"></i>
                            </button>
                        </div>
                    </form>

                    <!-- Traffic Modal -->
                    <div x-show="showTrafficModal" x-cloak class="fixed inset-0 z-[100] flex items-center justify-center px-4 sm:px-0">
                        <div x-show="showTrafficModal" x-transition.opacity @click="showTrafficModal = false" class="absolute inset-0 bg-black/80 backdrop-blur-sm"></div>
                        <div x-show="showTrafficModal" x-transition.scale.origin.center class="relative card rounded-xl p-8 max-w-md w-full shadow-2xl border border-white/10">
                            <button @click="showTrafficModal = false" class="absolute top-4 right-4 text-gray-500 hover:text-white transition-colors">
                                <i class="fa-solid fa-times text-xl"></i>
                            </button>
                            <div class="text-center mb-8">
                                <div class="w-14 h-14 bg-primary-900/30 rounded-full flex items-center justify-center mx-auto mb-4 text-primary-500 border border-primary-900/50">
                                    <i class="fa-solid fa-fire text-xl"></i>
                                </div>
                                <h3 class="text-2xl font-bold text-white mb-2">Launch Viral Campaign</h3>
                                <p class="text-gray-400">Sign up to access our <span class="text-white font-bold">Viral Blast</span> technology. Drip-feed traffic from social sources.</p>
                            </div>
                            <div class="space-y-3">
                                <a href="/register.php" class="block w-full py-3 text-center rounded-lg bg-gradient-to-r from-primary-600 to-primary-500 hover:from-primary-500 hover:to-primary-400 text-white font-bold transition-all shadow-lg shadow-primary-900/20">Create Account</a>
                                <a href="/login.php" class="block w-full py-3 text-center rounded-lg bg-white/5 hover:bg-white/10 text-white font-medium transition-colors border border-white/10">Log In</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- How It Works Section -->
    <section id="how-it-works" class="py-24 bg-[#111111] border-t border-white/5 scroll-mt-20">
        <div class="max-w-7xl mx-auto px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl md:text-4xl font-bold text-white mb-4">How It Works</h2>
                <p class="text-lg text-gray-400">Simple steps to boost your SEO performance.</p>
            </div>

            <div class="space-y-24">
                <!-- 1. Submit Links -->
                <div id="submit-links" class="grid md:grid-cols-2 gap-12 items-center scroll-mt-32">
                    <div class="order-2 md:order-1">
                        <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-primary-900/30 text-primary-500 font-bold text-xl border border-primary-900/50 mb-6">1</div>
                        <h3 class="text-2xl font-bold text-white mb-4">Submit Your Links</h3>
                        <p class="text-gray-400 text-lg mb-6">
                            Paste your URLs into our dashboard. We accept bulk submissions up to 10,000 links at once.
                        </p>
                        <ul class="space-y-3 text-gray-300">
                            <li class="flex items-start gap-3">
                                <i class="fas fa-check mt-1 text-primary-500"></i>
                                <span>Instant submission to Google & Yandex</span>
                            </li>
                            <li class="flex items-start gap-3">
                                <i class="fas fa-check mt-1 text-primary-500"></i>
                                <span>Select <strong>Drip Feed</strong> to spread submissions over days</span>
                            </li>
                            <li class="flex items-start gap-3">
                                <i class="fas fa-check mt-1 text-primary-500"></i>
                                <span>Optional <strong>VIP Queue</strong> for priority processing</span>
                            </li>
                        </ul>
                    </div>
                    <div class="order-1 md:order-2 relative group">
                        <div class="absolute -inset-2 bg-primary-600/20 rounded-xl blur-xl opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
                        <div class="relative bg-[#1c1c1c] border border-white/10 rounded-xl p-6 shadow-2xl">
                            <!-- Mock UI -->
                            <div class="flex gap-4 mb-4">
                                <div class="w-1/2 h-10 bg-white/5 rounded-lg border border-white/5"></div>
                                <div class="w-1/2 h-10 bg-white/5 rounded-lg border border-white/5"></div>
                            </div>
                            <div class="space-y-2 mb-4">
                                <div class="h-4 bg-white/5 rounded w-3/4"></div>
                                <div class="h-32 bg-white/5 rounded-lg border border-white/5 p-3 font-mono text-xs text-gray-500">
                                    https://example.com/page-1<br>
                                    https://example.com/page-2<br>
                                    https://example.com/page-3
                                </div>
                            </div>
                            <div class="flex justify-between items-center">
                                <div class="h-4 w-20 bg-white/5 rounded"></div>
                                <div class="h-10 w-32 bg-primary-600 rounded-lg"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 2. Check Links -->
                <div id="check-links" class="grid md:grid-cols-2 gap-12 items-center scroll-mt-32">
                    <div class="relative group">
                        <div class="absolute -inset-2 bg-blue-600/20 rounded-xl blur-xl opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
                        <div class="relative bg-[#1c1c1c] border border-white/10 rounded-xl p-6 shadow-2xl">
                            <div class="flex justify-between items-center mb-6 border-b border-white/5 pb-4">
                                <div class="h-6 w-32 bg-white/5 rounded"></div>
                                <div class="h-6 w-20 bg-green-500/20 rounded-full"></div>
                            </div>
                            <div class="grid grid-cols-3 gap-4 mb-6">
                                <div class="bg-white/5 rounded-lg p-3 text-center">
                                    <div class="h-6 w-8 mx-auto bg-primary-500/20 rounded mb-2"></div>
                                    <div class="h-3 w-12 mx-auto bg-white/10 rounded"></div>
                                </div>
                                <div class="bg-white/5 rounded-lg p-3 text-center border border-green-500/30">
                                    <div class="h-6 w-8 mx-auto bg-green-500 rounded mb-2"></div>
                                    <div class="h-3 w-12 mx-auto bg-white/10 rounded"></div>
                                </div>
                                <div class="bg-white/5 rounded-lg p-3 text-center">
                                    <div class="h-6 w-8 mx-auto bg-yellow-500/20 rounded mb-2"></div>
                                    <div class="h-3 w-12 mx-auto bg-white/10 rounded"></div>
                                </div>
                            </div>
                            <div class="space-y-3">
                                <div class="flex justify-between items-center p-2 bg-white/5 rounded border border-green-500/20">
                                    <div class="h-3 w-48 bg-white/10 rounded"></div>
                                    <div class="h-4 w-16 bg-green-500/20 rounded"></div>
                                </div>
                                <div class="flex justify-between items-center p-2 bg-white/5 rounded">
                                    <div class="h-3 w-40 bg-white/10 rounded"></div>
                                    <div class="h-4 w-16 bg-yellow-500/20 rounded"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div>
                        <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-blue-900/30 text-blue-500 font-bold text-xl border border-blue-900/50 mb-6">2</div>
                        <h3 class="text-2xl font-bold text-white mb-4">Verify Indexing Status</h3>
                        <p class="text-gray-400 text-lg mb-6">
                            Don't guess. Know. Our checker tool verifies if your URLs are actually indexed in Google's database.
                        </p>
                        <ul class="space-y-3 text-gray-300">
                            <li class="flex items-start gap-3">
                                <i class="fas fa-check mt-1 text-blue-500"></i>
                                <span>Real-time verification against live SERPs</span>
                            </li>
                            <li class="flex items-start gap-3">
                                <i class="fas fa-check mt-1 text-blue-500"></i>
                                <span>Detailed reports: Indexed, Unindexed, or Error</span>
                            </li>
                            <li class="flex items-start gap-3">
                                <i class="fas fa-check mt-1 text-blue-500"></i>
                                <span>Export results to CSV for client reporting</span>
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- 3. Buy Credits -->
                <div id="buy-credits" class="grid md:grid-cols-2 gap-12 items-center scroll-mt-32">
                    <div class="order-2 md:order-1">
                        <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-purple-900/30 text-purple-500 font-bold text-xl border border-purple-900/50 mb-6">3</div>
                        <h3 class="text-2xl font-bold text-white mb-4">Secure Crypto Payments</h3>
                        <p class="text-gray-400 text-lg mb-6">
                            Top up your account instantly using Cryptomus. We support major cryptocurrencies for privacy and speed.
                        </p>
                        <ul class="space-y-3 text-gray-300">
                            <li class="flex items-start gap-3">
                                <i class="fas fa-check mt-1 text-purple-500"></i>
                                <span>Pay with Bitcoin, Ethereum, USDT, LTC, and more</span>
                            </li>
                            <li class="flex items-start gap-3">
                                <i class="fas fa-check mt-1 text-purple-500"></i>
                                <span>Credits added automatically after 1 confirmation</span>
                            </li>
                            <li class="flex items-start gap-3">
                                <i class="fas fa-check mt-1 text-purple-500"></i>
                                <span>No recurring subscriptions - pay as you go</span>
                            </li>
                        </ul>
                        <div class="mt-8 flex gap-4 opacity-70 grayscale hover:grayscale-0 transition-all duration-500">
                            <img src="/assets/img/bitcoin-btc-logo.png" class="h-8 object-contain">
                            <img src="/assets/img/ethereum-eth-logo.png" class="h-8 object-contain">
                            <img src="/assets/img/Zcash-Yellow.png" class="h-8 object-contain">
                        </div>
                    </div>
                    <div class="order-1 md:order-2 relative group">
                        <div class="absolute -inset-2 bg-purple-600/20 rounded-xl blur-xl opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
                        <div class="relative bg-[#1c1c1c] border border-white/10 rounded-xl p-6 shadow-2xl text-center">
                            <h4 class="text-gray-400 uppercase text-xs font-bold mb-4">Payment Gateway</h4>
                            <div class="bg-black/40 rounded-lg p-6 border border-white/5 mb-6">
                                <div class="text-3xl font-bold text-white mb-1">$50.00</div>
                                <div class="text-sm text-gray-500">5,000 Credits</div>
                            </div>
                            <button class="w-full py-3 rounded-lg bg-purple-600 text-white font-bold flex items-center justify-center gap-2 shadow-lg shadow-purple-900/20">
                                <i class="fas fa-lock"></i> Pay with Cryptomus
                            </button>
                            <div class="mt-4 text-xs text-gray-500 flex justify-center gap-4">
                                <span class="flex items-center gap-1"><i class="fab fa-bitcoin"></i> BTC</span>
                                <span class="flex items-center gap-1"><i class="fab fa-ethereum"></i> ETH</span>
                                <span class="flex items-center gap-1"><i class="fas fa-dollar-sign"></i> USDT</span>
                            </div>
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
                    <h2 class="text-3xl md:text-4xl font-bold text-white mb-6">The Ranking Formula Has Changed</h2>
                    <div class="space-y-6 text-lg text-gray-400">
                        <p>
                            <strong class="text-primary-400">Indexing is step one. Virality is step two.</strong> 
                            Google's modern algorithm prioritizes "User Signals" (CTR, Dwell Time, Traffic Sources) alongside backlinks.
                        </p>
                        <p>
                            If you build 100 backlinks to a page but it gets 0 visitors, it looks suspicious (unnatural).
                        </p>
                        <p>
                            Rapid Indexer solves this by combining <span class="text-white font-semibold">Premium Indexing</span> with <span class="text-white font-semibold">Viral Traffic Simulation</span>. We prove to Google that your content is not only real but trending.
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
                <!-- Viral Blast Card (New) -->
                <div class="p-8 rounded-xl card border border-primary-600/50 bg-primary-900/10 hover:bg-primary-900/20 transition-colors group relative overflow-hidden">
                    <div class="absolute top-0 right-0 bg-primary-600 text-white text-[10px] font-bold px-2 py-1 rounded-bl-lg uppercase">New</div>
                    <div class="w-12 h-12 bg-primary-600/20 rounded-lg flex items-center justify-center mb-6 group-hover:bg-primary-600/30 transition-colors border border-primary-600/30">
                        <i class="fa-solid fa-fire-flame-curved text-primary-500 text-xl"></i>
                    </div>
                    <h3 class="text-xl font-bold text-white mb-3">Viral Blast</h3>
                    <p class="text-gray-400 mb-4">Simulate viral social traffic with our tested drip-feed formula. Boost "User Behavior" signals.</p>
                    <a href="/viral-blast.php" class="text-primary-400 font-bold text-sm hover:text-white transition-colors">Learn the Formula <i class="fas fa-arrow-right ml-1"></i></a>
                </div>

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
                        <li class="flex items-center gap-3"><i class="fa-solid fa-coins text-primary-500 text-sm"></i> 2 Credits per Link</li>
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
                <div class="flex flex-col items-center text-primary-500">
                    <i class="fas fa-rocket text-xl"></i>
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
