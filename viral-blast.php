<?php
require_once __DIR__ . '/config/config.php';
session_start();

// Public sales page - no redirect required
$is_logged_in = isset($_SESSION['uid']);
$cta_link = $is_logged_in ? '/traffic.php' : '/register.php';
$cta_text = $is_logged_in ? 'Launch Viral Campaign' : 'Start Viral Campaign Free';
?>
<!DOCTYPE html>
<html lang="en" class="dark">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Viral Blast - Simulate Virality & Boost Rankings | Rapid Indexer</title>
    <meta name="description" content="Simulate viral social media traffic to boost your webpage rankings. Our tested drip-feed formula delivers real browser hits from high-authority referrers.">
    <meta name="keywords" content="viral traffic, seo traffic, rank boost, social signals, drip feed traffic, real visitors">
    <link rel="canonical" href="https://rapid-indexer.com/viral-blast">
    <meta name="robots" content="index, follow">
    
    <meta property="og:type" content="website">
    <meta property="og:url" content="https://rapid-indexer.com/viral-blast.php">
    <meta property="og:title" content="Viral Blast - The Secret to Instant Ranking Boosts">
    <meta property="og:description" content="Don't just build links. Simulate the viral activity Google loves. Get 1000s of real visitors from social referrers today.">
    <meta property="og:image" content="https://rapid-indexer.com/assets/img/dashboard-preview.png">

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
        body { background-color: #141414; color: #efefef; }
        .nav-blur { background: rgba(20, 20, 20, 0.9); backdrop-filter: blur(12px); }
        .card { background: #1c1c1c; border: 1px solid #2a2a2a; }
        .gradient-text {
            background: linear-gradient(to right, #fb7185, #be123c);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .viral-glow {
            box-shadow: 0 0 40px -10px rgba(190, 18, 60, 0.3);
        }
    </style>
</head>

<body class="antialiased min-h-screen flex flex-col">
    
    <!-- Nav -->
    <nav class="w-full border-b border-white/5 nav-blur sticky top-0 z-40">
        <div class="max-w-7xl mx-auto px-6 lg:px-8">
            <div class="flex justify-between items-center h-20">
                <a href="/" class="flex items-center gap-3 hover:opacity-90 transition-opacity">
                    <div class="flex flex-col items-center text-primary-500">
                        <i class="fas fa-rocket text-2xl"></i>
                    </div>
                    <span class="font-bold text-xl tracking-tight text-white">Rapid Indexer</span>
                </a>
                <div class="flex items-center gap-4">
                    <?php if ($is_logged_in): ?>
                        <a href="/dashboard" class="text-sm font-medium text-gray-300 hover:text-white transition-colors">Dashboard</a>
                    <?php else: ?>
                        <a href="/login.php" class="text-sm font-medium text-gray-300 hover:text-white transition-colors">Log in</a>
                        <a href="/register.php" class="px-5 py-2.5 text-sm font-bold rounded-lg text-white bg-primary-700 hover:bg-primary-600 transition-all">Get Started</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero -->
    <section class="relative pt-20 pb-24 overflow-hidden">
        <div class="absolute top-0 left-1/2 -translate-x-1/2 w-[1000px] h-[500px] bg-primary-900/20 rounded-full blur-[120px] -z-10"></div>
        
        <div class="max-w-7xl mx-auto px-6 lg:px-8 relative z-10">
            <div class="grid lg:grid-cols-2 gap-12 items-center">
                <div class="text-center lg:text-left">
                    <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-primary-900/20 border border-primary-500/30 text-primary-400 text-sm font-bold mb-8 viral-glow">
                        <i class="fas fa-fire-flame-curved"></i> NEW: Viral Simulation Engine
                    </div>
                    
                    <h1 class="text-5xl md:text-6xl font-extrabold text-white mb-6 leading-tight">
                        Simulate <span class="gradient-text">Virality</span>.<br>
                        Dominante Search.
                    </h1>
                    
                    <p class="text-xl text-gray-400 mb-10 leading-relaxed">
                        Google's algorithm has evolved. It demands user engagement.
                        <strong class="text-white">Viral Blast</strong> injects randomized, high-retention traffic from social referrers to prove your content is trending.
                    </p>
                    
                    <div class="flex flex-col sm:flex-row gap-4 justify-center lg:justify-start items-center">
                        <?php if ($is_logged_in): ?>
                            <a href="/traffic.php" class="w-full sm:w-auto px-8 py-4 bg-gradient-to-r from-primary-600 to-primary-500 text-white font-bold rounded-xl hover:from-primary-500 hover:to-primary-400 transition-all shadow-lg shadow-primary-900/40 text-lg flex items-center justify-center gap-2">
                                <i class="fas fa-rocket"></i> Launch Viral Campaign
                            </a>
                        <?php else: ?>
                            <a href="/register.php" class="w-full sm:w-auto px-8 py-4 bg-gradient-to-r from-primary-600 to-primary-500 text-white font-bold rounded-xl hover:from-primary-500 hover:to-primary-400 transition-all shadow-lg shadow-primary-900/40 text-lg flex items-center justify-center gap-2">
                                <i class="fas fa-rocket"></i> Start Viral Campaign Free
                            </a>
                        <?php endif; ?>
                        <a href="#formula" class="w-full sm:w-auto px-8 py-4 border border-white/10 text-white font-medium rounded-xl hover:bg-white/5 transition-colors text-lg flex items-center justify-center">
                            See The Formula
                        </a>
                    </div>
                    
                    <div class="mt-8 flex items-center justify-center lg:justify-start gap-4 text-sm text-gray-500">
                        <div class="flex items-center gap-2"><i class="fas fa-check text-primary-500"></i> Residential IPs</div>
                        <div class="flex items-center gap-2"><i class="fas fa-check text-primary-500"></i> Social Signals</div>
                        <div class="flex items-center gap-2"><i class="fas fa-check text-primary-500"></i> Drip-Feed</div>
                    </div>
                </div>

                <!-- Interactive Demo Card -->
                <div class="relative mx-auto max-w-md w-full" x-data="{ trafficQty: 4000 }">
                    <div class="absolute -inset-1 bg-gradient-to-r from-primary-600 to-purple-600 rounded-2xl blur opacity-30 animate-pulse"></div>
                    <div class="card rounded-xl p-6 md:p-8 border-primary-600/30 bg-[#1a1a1a] relative overflow-hidden">
                        <div class="absolute top-0 right-0 bg-primary-600 text-white text-[10px] font-bold px-2 py-1 rounded-bl-lg uppercase">Live Demo</div>
                        
                        <div class="flex items-center justify-between mb-6">
                            <div>
                                <h2 class="text-lg font-bold text-white flex items-center gap-2">
                                    <i class="fa-solid fa-fire-flame-curved text-primary-500"></i>
                                    Viral Traffic Blast
                                </h2>
                                <p class="text-sm text-gray-400 mt-1">Simulate viral social traffic spikes</p>
                            </div>
                        </div>

                        <form class="space-y-4" action="<?php echo $cta_link; ?>">
                            <div>
                                <label class="block text-xs font-medium text-gray-500 mb-1 uppercase">Target URL</label>
                                <div class="relative">
                                    <input type="text" class="block w-full border rounded-lg p-3 bg-black/20 text-gray-300 border-white/10 focus:border-primary-500 transition-colors" 
                                        placeholder="https://example.com/viral-post">
                                    <div class="absolute right-3 top-3 text-gray-500"><i class="fas fa-link"></i></div>
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-medium text-gray-500 mb-1 uppercase">Visitors</label>
                                    <select class="block w-full border rounded-lg p-3 bg-black/20 text-gray-300 border-white/10 appearance-none cursor-pointer hover:border-primary-500 transition-colors" x-model="trafficQty">
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
                                <a href="<?php echo $cta_link; ?>" class="w-full sm:w-auto px-6 py-3 text-sm font-bold rounded-lg bg-gradient-to-r from-primary-600 to-primary-500 text-white hover:from-primary-500 hover:to-primary-400 transition-all shadow-lg shadow-primary-900/20 flex items-center justify-center">
                                    Launch Blast <i class="fas fa-rocket ml-1"></i>
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- The Formula -->
    <section id="formula" class="py-24 bg-black/30 border-y border-white/5">
        <div class="max-w-7xl mx-auto px-6 lg:px-8">
            <div class="grid md:grid-cols-2 gap-16 items-center">
                <div>
                    <h2 class="text-3xl font-bold text-white mb-6">The "Tested Formula" for SEO Success</h2>
                    <div class="space-y-6 text-lg text-gray-400">
                        <p>
                            Backlinks alone are suspicious. Why would a page have 100 links but 0 visitors? That's a footprint.
                        </p>
                        <p>
                            Our <strong>Viral Blast</strong> mimics the exact pattern of a viral content piece:
                        </p>
                        <ul class="space-y-4 mt-6">
                            <li class="flex items-start gap-4">
                                <div class="w-10 h-10 rounded-lg bg-green-500/10 flex items-center justify-center text-green-500 flex-shrink-0 border border-green-500/20">
                                    <i class="fas fa-random"></i>
                                </div>
                                <div>
                                    <h4 class="text-white font-bold">Randomized Drip-Feed</h4>
                                    <p class="text-sm mt-1">Traffic doesn't come in flat lines. We use chaotic, randomized burst intervals (20m - 6h) to simulate real sharing.</p>
                                </div>
                            </li>
                            <li class="flex items-start gap-4">
                                <div class="w-10 h-10 rounded-lg bg-blue-500/10 flex items-center justify-center text-blue-500 flex-shrink-0 border border-blue-500/20">
                                    <i class="fab fa-twitter"></i>
                                </div>
                                <div>
                                    <h4 class="text-white font-bold">Social Referrers</h4>
                                    <p class="text-sm mt-1">We spoof headers from trusted platforms like Twitter, Reddit, and Facebook. Google sees "Social Signal".</p>
                                </div>
                            </li>
                            <li class="flex items-start gap-4">
                                <div class="w-10 h-10 rounded-lg bg-purple-500/10 flex items-center justify-center text-purple-500 flex-shrink-0 border border-purple-500/20">
                                    <i class="fas fa-globe"></i>
                                </div>
                                <div>
                                    <h4 class="text-white font-bold">Residential IPs</h4>
                                    <p class="text-sm mt-1">Real user behavior requires real IPs. No datacenter footprints that get flagged instantly.</p>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>
                <div class="relative">
                    <div class="absolute -inset-1 bg-gradient-to-r from-primary-600 to-purple-600 rounded-2xl blur opacity-20"></div>
                    <div class="card p-8 rounded-xl relative">
                        <div class="flex items-center justify-between mb-8 border-b border-white/5 pb-4">
                            <h3 class="font-bold text-white">Campaign Simulation</h3>
                            <span class="text-xs bg-green-500/20 text-green-400 px-2 py-1 rounded">Active</span>
                        </div>
                        
                        <!-- Chart bars simulation -->
                        <div class="flex items-end justify-between h-48 gap-2 mb-4">
                            <div class="w-full bg-primary-900/20 rounded-t h-[20%] relative group"></div>
                            <div class="w-full bg-primary-900/40 rounded-t h-[45%] relative group"></div>
                            <div class="w-full bg-primary-600 rounded-t h-[80%] relative group shadow-[0_0_15px_rgba(225,29,72,0.5)]"></div>
                            <div class="w-full bg-primary-900/50 rounded-t h-[60%] relative group"></div>
                            <div class="w-full bg-primary-900/30 rounded-t h-[30%] relative group"></div>
                            <div class="w-full bg-primary-900/60 rounded-t h-[50%] relative group"></div>
                            <div class="w-full bg-primary-900/20 rounded-t h-[25%] relative group"></div>
                        </div>
                        <div class="flex justify-between text-xs text-gray-500 font-mono">
                            <span>Day 1</span>
                            <span>Day 2</span>
                            <span>Day 3</span>
                        </div>
                        
                        <div class="mt-8 p-4 bg-black/40 rounded-lg border border-white/5">
                            <div class="flex items-center gap-3 text-sm text-gray-300">
                                <i class="fas fa-check-circle text-green-500"></i>
                                <span>Rankings increased for <strong>"best seo tools"</strong></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA -->
    <section class="py-24">
            <div class="max-w-4xl mx-auto px-6 lg:px-8 text-center">
            <h2 class="text-4xl font-bold text-white mb-6">Ready to go Viral?</h2>
            <p class="text-xl text-gray-400 mb-10">
                Stop hoping for traffic. Create it. <br>
                Join 5,000+ SEOs using Rapid Indexer to dominate the SERPs.
            </p>
            <?php if ($is_logged_in): ?>
                <a href="/traffic.php" class="inline-flex items-center px-8 py-4 bg-white text-black font-bold rounded-lg hover:bg-gray-200 transition-all text-lg">
                    Launch Viral Campaign
                </a>
            <?php else: ?>
                <a href="/register.php" class="inline-flex items-center px-8 py-4 bg-white text-black font-bold rounded-lg hover:bg-gray-200 transition-all text-lg">
                    Start Viral Campaign Free
                </a>
            <?php endif; ?>
        </div>
    </section>

    <footer class="border-t border-white/5 bg-black/20 py-12 mt-auto">
        <div class="max-w-7xl mx-auto px-6 lg:px-8 flex flex-col md:flex-row justify-between items-center gap-6">
            <div class="text-gray-500 text-sm">
                © <?php echo date('Y'); ?> Rapid Indexer.
            </div>
            <div class="flex gap-6 text-sm font-medium text-gray-400">
                <a href="/" class="hover:text-white">Home</a>
                <a href="/privacy.php" class="hover:text-white">Privacy</a>
                <a href="/terms.php" class="hover:text-white">Terms</a>
            </div>
        </div>
    </footer>

</body>
</html>

