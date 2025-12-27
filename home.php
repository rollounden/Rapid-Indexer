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

    <title>Rapid Indexer - The Fastest Google Indexer & Link Checker</title>
    <meta name="description" content="Stop losing revenue to invisible pages. Rapid Indexer gets your links indexed in minutes (VIP Queue) and verifies their status. Fix 'Crawled - Currently Not Indexed' errors today.">
    <meta name="keywords" content="google indexer, backlink indexer, link indexing service, google index checker, crawled not indexed, website authority, rapid indexer">
    <link rel="canonical" href="https://rapid-indexer.com/">
    <meta name="robots" content="index, follow">
    
    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="Rapid Indexer">
    <meta property="og:url" content="https://rapid-indexer.com/">
    <meta property="og:title" content="Rapid Indexer - Get Your Pages Found on Google Fast">
    <meta property="og:description" content="The fastest way to get indexed. Fix visibility issues and verify your links with our advanced infrastructure.">
    <meta property="og:image" content="https://rapid-indexer.com/assets/img/dashboard-preview.png">

    <meta property="twitter:card" content="summary_large_image">
    <meta property="twitter:url" content="https://rapid-indexer.com/">
    <meta property="twitter:title" content="Rapid Indexer - Get Your Pages Found on Google Fast">
    <meta property="twitter:description" content="The fastest way to get indexed. Fix visibility issues and verify your links with our advanced infrastructure.">
    <meta property="twitter:image" content="https://rapid-indexer.com/assets/img/dashboard-preview.png">

    <link rel="icon" href="/favicon.ico" sizes="any">
    <link rel="icon" href="/favicon.svg" type="image/svg+xml">
    <link rel="apple-touch-icon" href="/apple-touch-icon.png">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Rubik:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <!-- Schema.org JSON-LD -->
    <script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@graph": [
        {
          "@type": "Organization",
          "@id": "https://rapid-indexer.com/#org",
          "name": "Rapid Indexer",
          "url": "https://rapid-indexer.com/",
          "logo": "https://rapid-indexer.com/assets/img/rocket-icon.png",
          "sameAs": [
            "https://x.com/rapid_indexer",
            "https://www.linkedin.com/company/rapid-indexer/",
            "https://facebook.com/rapidindexer",
            "https://www.f6s.com/company/rapid-indexer"
          ]
        },
        {
          "@type": "SoftwareApplication",
          "@id": "https://rapid-indexer.com/#app",
          "name": "Rapid Indexer",
          "url": "https://rapid-indexer.com/",
          "applicationCategory": "BusinessApplication",
          "operatingSystem": "Web",
          "publisher": { "@id": "https://rapid-indexer.com/#org" },
          "description": "Visibility infrastructure for SEOs and businesses. Bulk URL submission, indexing status verification, and automated discovery.",
          "featureList": [
            "Bulk submissions up to 10,000 URLs",
            "VIP queue option (Under 2 min)",
            "Google Index Checker",
            "API Access"
          ]
        },
        {
          "@type": "FAQPage",
          "mainEntity": [
            {
              "@type": "Question",
              "name": "What does 'Crawled - Currently Not Indexed' mean?",
              "acceptedAnswer": {
                "@type": "Answer",
                "text": "It means Google has visited your page but decided not to add it to the search index. This is often due to low perceived value, lack of backlinks, or crawl budget issues. Rapid Indexer helps fix this by signaling authority and forcing a re-crawl."
              }
            },
            {
              "@type": "Question",
              "name": "How fast is the VIP Indexing Queue?",
              "acceptedAnswer": {
                "@type": "Answer",
                "text": "Our VIP Queue is designed for speed. Internal tests show Googlebot visits in as little as 35 seconds to 2 minutes after submission."
              }
            }
          ]
        }
      ]
    }
    </script>

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
        .nav-blur { background: rgba(20, 20, 20, 0.9); backdrop-filter: blur(12px); }
        .card { background: #1c1c1c; border: 1px solid #2a2a2a; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.3); }
        .prose p { margin-bottom: 1.5rem; line-height: 1.8; color: #d1d5db; }
        .prose h3 { color: white; font-size: 1.5rem; font-weight: 700; margin-top: 2rem; margin-bottom: 1rem; }
        .prose ul { list-style-type: disc; padding-left: 1.5rem; color: #d1d5db; margin-bottom: 1.5rem; }
        .prose li { margin-bottom: 0.5rem; }
    </style>
</head>

<body class="antialiased min-h-screen flex flex-col">
    <!-- Navigation -->
    <div class="sticky top-0 z-50 hidden lg:block">
        <div class="bg-primary-900 text-white border-b border-primary-800">
            <div class="flex w-full items-center justify-center gap-5 px-4 py-2 sm:px-6 lg:px-8">
                <p class="text-sm font-medium tracking-wide">
                    <span class="bg-white/10 px-2 py-0.5 rounded text-xs mr-2 font-bold">VIP QUEUE</span>
                    Internal tests show discovery in as little as 35 seconds!
                </p>
            </div>
        </div>
    </div>

    <nav class="w-full border-b border-white/5 nav-blur sticky top-0 z-40 lg:top-[37px]">
        <div class="max-w-7xl mx-auto px-6 lg:px-8">
            <div class="flex justify-between items-center h-20">
                <a href="/" class="flex items-center gap-3">
                    <div class="flex flex-col items-center text-primary-500">
                        <i class="fas fa-rocket text-3xl"></i>
                    </div>
                    <span class="font-bold text-2xl tracking-tight text-white">Rapid Indexer</span>
                </a>

                <nav class="hidden md:flex items-center gap-8">
                    <a href="#problem" class="text-sm font-medium text-gray-300 hover:text-white transition-colors">The Problem</a>
                    <a href="#how-it-works" class="text-sm font-medium text-gray-300 hover:text-white transition-colors">How it Works</a>
                    <a href="#use-cases" class="text-sm font-medium text-gray-300 hover:text-white transition-colors">Use Cases</a>
                    <a href="#pricing" class="text-sm font-medium text-gray-300 hover:text-white transition-colors">Pricing</a>
                    <!-- Tools Dropdown -->
                     <div class="relative group" x-data="{ open: false }">
                        <button @click="open = !open" @click.outside="open = false" class="text-sm font-medium text-gray-300 hover:text-white transition-colors flex items-center gap-1">
                            Tools <i class="fas fa-chevron-down text-xs ml-1 transition-transform" :class="{ 'rotate-180': open }"></i>
                        </button>
                        <div x-show="open" x-transition.opacity.duration.200ms class="absolute top-full right-0 mt-2 w-56 bg-[#1a1a1a] border border-white/10 rounded-xl shadow-xl py-2 z-50" style="display: none;">
                            <a href="/chrome-extension" class="block px-4 py-3 hover:bg-white/5 transition-colors">
                                <div class="text-white font-bold text-sm"><i class="fab fa-chrome mr-2 text-primary-400"></i>Chrome Extension</div>
                            </a>
                             <a href="/viral-blast" class="block px-4 py-3 hover:bg-white/5 transition-colors">
                                <div class="text-white font-bold text-sm"><i class="fas fa-fire mr-2 text-red-500"></i>Traffic Simulator</div>
                                <div class="text-xs text-gray-500 mt-1">For Advanced SEOs</div>
                            </a>
                        </div>
                    </div>
                </nav>

                <div class="flex items-center gap-4">
                    <a href="/login" class="text-sm font-medium text-gray-300 hover:text-white transition-colors">Log in</a>
                    <a href="/register" class="inline-flex items-center px-5 py-2.5 border border-transparent text-sm font-bold rounded-lg text-white bg-primary-700 hover:bg-primary-600 transition-all shadow-lg shadow-primary-900/20">
                        Start Indexing Free
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- 1. Hero Section (The "Money" Hook) -->
    <section class="relative pt-20 lg:pt-28 pb-20">
        <div class="max-w-7xl mx-auto px-6 lg:px-8 relative z-10">
            <div class="text-center max-w-4xl mx-auto">
                <h1 class="text-sm md:text-base font-bold text-primary-500 uppercase tracking-widest mb-4">The Visibility Infrastructure</h1>
                <h2 class="text-4xl lg:text-6xl font-extrabold tracking-tight text-white mb-8 leading-tight">
                    Boost Your Rankings <span class="text-primary-500">Today</span><br>
                    by Indexing Links You Already Have.
                </h2>

                <p class="text-xl text-gray-400 max-w-2xl mx-auto mb-10 leading-relaxed">
                    Don't let your hard work go to waste. <strong>Rapid Indexer</strong> forces Google to crawl your backlinks, blog posts, and product pages in minutes—not weeks.
                    <br><span class="text-primary-400 text-sm font-bold block mt-2"><i class="fas fa-bolt"></i> VIP Queue: Discovery in 35s - 2 minutes.</span>
                </p>

                <div class="flex flex-col sm:flex-row gap-4 justify-center items-center mb-12">
                    <a href="/register" class="w-full sm:w-auto inline-flex justify-center items-center px-8 py-4 bg-primary-700 text-white font-bold rounded-lg hover:bg-primary-600 transition-all shadow-lg shadow-primary-900/30 text-lg">
                        Start Indexing Now
                        <i class="fa-solid fa-arrow-right ml-3 text-sm"></i>
                    </a>
                    <a href="#check-links" class="w-full sm:w-auto inline-flex justify-center items-center px-8 py-4 border border-white/10 text-white font-medium rounded-lg hover:bg-white/5 transition-colors text-lg">
                        Check Index Status
                    </a>
                </div>
                
                <p class="text-sm text-gray-500 flex items-center justify-center gap-2">
                    <i class="fas fa-check-circle text-green-500"></i> No credit card required 
                    <?php if ($free_credits > 0): ?>
                        • <?php echo $free_credits; ?> Free Credits on Signup
                    <?php endif; ?>
                </p>
                
                <div class="mt-8 flex flex-wrap justify-center gap-6 text-sm text-gray-500 opacity-60">
                    <span>Used by:</span>
                    <span class="font-bold text-gray-400"><i class="fas fa-building mr-1"></i> Agencies</span>
                    <span class="font-bold text-gray-400"><i class="fas fa-link mr-1"></i> Link Builders</span>
                    <span class="font-bold text-gray-400"><i class="fas fa-pen-fancy mr-1"></i> Content Creators</span>
                    <span class="font-bold text-gray-400"><i class="fas fa-search mr-1"></i> SEOs</span>
                    <span class="font-bold text-gray-400"><i class="fab fa-amazon mr-1"></i> Amazon Sellers</span>
                    <span class="font-bold text-gray-400"><i class="fas fa-shopping-bag mr-1"></i> Marketplace Sellers</span>
                    <span class="font-bold text-gray-400"><i class="fas fa-robot mr-1"></i> Automation Specialists</span>
                    <span class="font-bold text-gray-400"><i class="fas fa-map-marker-alt mr-1"></i> Local Businesses</span>
                </div>
                
                <p class="mt-4 text-xs text-gray-600 italic">
                    ...and anyone who wants more traffic with search-based marketing.
                </p>
            </div>
        </div>
    </section>

    <!-- 2. The Visibility Problem (Deep Dive) -->
    <section id="problem" class="py-24 bg-black/20 border-y border-white/5">
        <div class="max-w-7xl mx-auto px-6 lg:px-8">
            <div class="max-w-3xl mx-auto text-center mb-16">
                <h2 class="text-3xl font-bold text-white mb-6">The "Invisible Internet" Epidemic</h2>
                <p class="text-lg text-gray-400">
                    Why 90% of new content and backlinks are failing to produce results in 2025.
                </p>
            </div>

            <div class="grid lg:grid-cols-2 gap-16 items-start">
                <div class="prose max-w-none">
                    <h3 class="text-white">Publishing Content ≠ Ranking Content</h3>
                    <p>
                        The modern web is oversaturated. Every day, millions of new pages, blog posts, and backlinks are created. Google's "Crawl Budget" (the resources it dedicates to discovering new content) simply cannot keep up with this exponential growth. This bottleneck has created a massive gap between content creation and content discovery.
                    </p>
                    <p>
                        This has led to a silent crisis in SEO: <strong>Indexation Failure</strong>. You might have the best content, the perfect keywords, and high-authority backlinks, but if Google's bots don't crawl and index them, <strong>they do not exist</strong> in the eyes of the search engine. Your investment in content and links is effectively wasted until this bridge is crossed.
                    </p>
                    
                    <h3 class="text-white">The "Crawled - Currently Not Indexed" Nightmare</h3>
                    <p>
                        This is the most frustrating error message in Google Search Console. It means Google knows your page exists, visited it, but <em>decided it wasn't worth adding to the index</em>. This often happens due to:
                    </p>
                    <ul>
                        <li><strong>Lack of Authority Signals:</strong> The page has no incoming internal or external link signals to validate its importance.</li>
                        <li><strong>Duplicate Content Flags:</strong> Google thinks it's too similar to other pages already in the index.</li>
                        <li><strong>Crawl Budget Exhaustion:</strong> The bot left your site before finishing the job because your site is too large or slow.</li>
                    </ul>

                    <h3 class="text-red-400 text-2xl border-l-4 border-red-500 pl-4 mt-8 italic">
                        "Unindexed pages generate $0 revenue and zero SEO value."
                    </h3>
                    <p>
                        Without intervention, these pages can sit in limbo for months. Our tools are built specifically to resolve these status codes and unlock the value of your work.
                    </p>

                    <h3 class="text-white">The "Discovered - Currently Not Indexed" Trap</h3>
                    <p>
                        Even worse is when Google knows a URL exists (likely found via a sitemap) but hasn't even bothered to crawl it yet. This is a massive missed opportunity, especially for time-sensitive content like news, seasonal products, or viral posts where speed to market is everything.
                    </p>
                </div>

                <div class="space-y-8">
                    <div class="bg-[#1a1a1a] border border-white/10 rounded-xl p-8 shadow-2xl">
                        <div class="flex items-center gap-4 mb-6">
                            <div class="w-12 h-12 bg-red-500/20 rounded-full flex items-center justify-center text-red-500">
                                <i class="fas fa-unlink text-xl"></i>
                            </div>
                            <div>
                                <h4 class="text-xl font-bold text-white">The Backlink Black Hole</h4>
                                <p class="text-sm text-gray-500">Why your link building isn't working</p>
                            </div>
                        </div>
                        <p class="text-gray-400 mb-4">
                            You pay for a guest post or build a tiered link structure. The link goes live. You wait. And wait.
                        </p>
                        <p class="text-gray-400">
                            <strong>If that backlink page isn't indexed, no PageRank flows to your site.</strong>
                        </p>
                        <div class="bg-red-900/10 border-l-4 border-red-500 p-4 mt-4">
                            <p class="text-red-300 text-sm font-medium">
                                "I built 50 links this month but my rankings didn't move." <br>
                                <span class="text-white">Reality check: Only 5 of them were actually indexed.</span>
                            </p>
                        </div>
                    </div>

                    <div class="bg-[#1a1a1a] border border-white/10 rounded-xl p-8 shadow-2xl">
                        <div class="flex items-center gap-4 mb-6">
                            <div class="w-12 h-12 bg-blue-500/20 rounded-full flex items-center justify-center text-blue-500">
                                <i class="fas fa-search-dollar text-xl"></i>
                            </div>
                            <div>
                                <h4 class="text-xl font-bold text-white">GSC Is Not Enough</h4>
                                <p class="text-sm text-gray-500">Why you need an external checker</p>
                            </div>
                        </div>
                        <p class="text-gray-400">
                            Google Search Console data is often delayed by 3-5 days and uses sampling. It might say a URL is indexed when it has actually dropped out, or vice versa. Relying solely on GSC leaves you blind to the real-time status of your pages.
                        </p>
                        <p class="text-gray-400 mt-2">
                            <strong>Rapid Indexer's Checker</strong> queries the live SERPs (Search Engine Results Pages) in real-time. We give you the binary truth: <span class="text-green-400">Yes</span> or <span class="text-red-400">No</span>.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- 3. Our Solution (Technical) -->
    <section id="how-it-works" class="py-24 relative overflow-hidden">
        <!-- Background Glow -->
        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[800px] h-[800px] bg-primary-900/10 rounded-full blur-[100px] -z-10"></div>

        <div class="max-w-7xl mx-auto px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl md:text-4xl font-bold text-white mb-4">The Solution: Rapid Indexing Infrastructure</h2>
                <p class="text-lg text-gray-400 max-w-2xl mx-auto">
                    We don't just "ping" Google. We use a multi-layered infrastructure to force discovery and validate visibility.
                </p>
            </div>

            <div class="grid md:grid-cols-2 gap-8">
                <!-- Service A: Indexer -->
                <div class="card p-8 rounded-xl border-primary-600/30 bg-primary-900/5 relative overflow-hidden group hover:border-primary-500/50 transition-colors">
                    <div class="absolute top-0 right-0 bg-primary-600 text-white text-[10px] font-bold px-2 py-1 rounded-bl-lg uppercase">Core Engine</div>
                    <div class="w-14 h-14 bg-primary-600/20 rounded-lg flex items-center justify-center mb-6 border border-primary-600/30 text-primary-500 text-2xl">
                        <i class="fas fa-bolt"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-white mb-3">Rapid URL Indexer</h3>
                    <p class="text-gray-400 mb-6 leading-relaxed">
                        Our proprietary indexing engine uses a combination of direct cloud API signaling and high-authority feed injection to force Googlebot to crawl your URLs immediately. This isn't just a ping; it's a priority request.
                    </p>
                    
                    <div class="space-y-4 mb-8">
                        <div class="flex items-start gap-3">
                            <div class="mt-1 w-5 h-5 rounded-full bg-primary-500/20 flex items-center justify-center text-primary-500 text-xs"><i class="fas fa-check"></i></div>
                            <div>
                                <strong class="text-white block">Standard Indexing</strong>
                                <span class="text-sm text-gray-500">Best for bulk backlinks, citations, and Tier 2/3 links. Typically processes within 24-48 hours using our safe, drip-fed authority network. This mimics natural discovery patterns for large batches of links.</span>
                            </div>
                        </div>
                        <div class="flex items-start gap-3">
                            <div class="mt-1 w-5 h-5 rounded-full bg-yellow-500/20 flex items-center justify-center text-yellow-500 text-xs"><i class="fas fa-bolt"></i></div>
                            <div>
                                <strong class="text-white block">VIP Priority Queue</strong>
                                <span class="text-sm text-gray-500">For "Money Pages", new blog posts, and time-sensitive press releases. Internal tests show discovery in <strong class="text-white">35 seconds to 2 minutes</strong>. This is the fastest indexing available on the market, bypassing the standard crawl queue.</span>
                            </div>
                        </div>
                    </div>
                    <a href="/register" class="inline-flex items-center justify-center w-full py-3 rounded-lg bg-primary-700 hover:bg-primary-600 text-white font-bold transition-all">Start Indexing Now</a>
                </div>

                <!-- Service B: Checker -->
                <div id="check-links" class="card p-8 rounded-xl border-white/10 relative overflow-hidden group hover:border-white/20 transition-colors">
                    <div class="absolute top-0 right-0 bg-blue-600 text-white text-[10px] font-bold px-2 py-1 rounded-bl-lg uppercase">Verification</div>
                    <div class="w-14 h-14 bg-blue-600/20 rounded-lg flex items-center justify-center mb-6 border border-blue-600/30 text-blue-500 text-2xl">
                        <i class="fas fa-search"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-white mb-3">Index Status Checker</h3>
                    <p class="text-gray-400 mb-6 leading-relaxed">
                        Stop relying on outdated caches. Our checker tool performs live queries to see if your URL is actually returning in search results for your target market. Accuracy is paramount for reporting.
                    </p>
                    
                    <div class="space-y-4 mb-8">
                        <div class="flex items-start gap-3">
                            <div class="mt-1 w-5 h-5 rounded-full bg-blue-500/20 flex items-center justify-center text-blue-500 text-xs"><i class="fas fa-globe"></i></div>
                            <div>
                                <strong class="text-white block">Real-Time SERP Verification</strong>
                                <span class="text-sm text-gray-500">We don't check a cache; we check the live Google results page to ensure 100% accuracy. If we say it's indexed, it's visible to the world.</span>
                            </div>
                        </div>
                        <div class="flex items-start gap-3">
                            <div class="mt-1 w-5 h-5 rounded-full bg-blue-500/20 flex items-center justify-center text-blue-500 text-xs"><i class="fas fa-file-csv"></i></div>
                            <div>
                                <strong class="text-white block">Bulk Reporting</strong>
                                <span class="text-sm text-gray-500">Paste up to 10,000 URLs. Get a clean CSV report showing "Indexed", "Not Indexed", or "Error". Perfect for client audits and monthly reporting.</span>
                            </div>
                        </div>
                    </div>
                    <a href="/register" class="inline-flex items-center justify-center w-full py-3 rounded-lg border border-white/10 bg-white/5 hover:bg-white/10 text-white font-bold transition-all">Verify Your Links</a>
                </div>
            </div>
        </div>
    </section>

    <!-- 4. Use Cases (Deep Dive) -->
    <section id="use-cases" class="py-24 bg-[#111111] border-y border-white/5">
        <div class="max-w-7xl mx-auto px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl font-bold text-white mb-4">What Can You Index? (Almost Anything)</h2>
                <p class="text-gray-400">If it has a URL and is publicly accessible, we can help Google find it.</p>
            </div>

            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
                <!-- Backlinks -->
                <div class="bg-white/5 p-6 rounded-xl border border-white/5 hover:border-primary-500/30 transition-all group">
                    <div class="w-12 h-12 bg-black/40 rounded-lg flex items-center justify-center mb-4 text-primary-500 text-2xl group-hover:scale-110 transition-transform">
                        <i class="fas fa-link"></i>
                    </div>
                    <h4 class="text-xl font-bold text-white mb-2">Backlinks & Guest Posts</h4>
                    <p class="text-gray-400 text-sm mb-4">
                        You built the link, but Google hasn't counted it. <br>
                        Index <strong>Guest Posts, PBNs, Niche Edits</strong>, and Web 2.0s to ensure "link juice" actually passes to your money site.
                    </p>
                    <span class="text-xs font-mono text-primary-400 bg-primary-900/20 px-2 py-1 rounded">Target: Higher DA/PA</span>
                </div>

                <!-- Parasite SEO -->
                <div class="bg-white/5 p-6 rounded-xl border border-white/5 hover:border-blue-500/30 transition-all group">
                    <div class="w-12 h-12 bg-black/40 rounded-lg flex items-center justify-center mb-4 text-blue-500 text-2xl group-hover:scale-110 transition-transform">
                        <i class="fas fa-rocket"></i>
                    </div>
                    <h4 class="text-xl font-bold text-white mb-2">Parasite SEO</h4>
                    <p class="text-gray-400 text-sm mb-4">
                        Rank high-authority pages in hours. <br>
                        <strong>LinkedIn Articles, Medium, Outlook, Quora, Reddit</strong>. Get these indexed instantly to rank for competitive keywords using their domain authority.
                    </p>
                    <span class="text-xs font-mono text-blue-400 bg-blue-900/20 px-2 py-1 rounded">Target: Fast Rankings</span>
                </div>

                <!-- Marketplaces -->
                <div class="bg-white/5 p-6 rounded-xl border border-white/5 hover:border-yellow-500/30 transition-all group">
                    <div class="w-12 h-12 bg-black/40 rounded-lg flex items-center justify-center mb-4 text-yellow-500 text-2xl group-hover:scale-110 transition-transform">
                        <i class="fas fa-shopping-bag"></i>
                    </div>
                    <h4 class="text-xl font-bold text-white mb-2">Marketplace Listings</h4>
                    <p class="text-gray-400 text-sm mb-4">
                        Get your products found on Google, not just the app.<br>
                        <strong>Amazon Products, Etsy Listings, eBay Auctions, Shopify Pages</strong>. Capture organic search traffic for your specific product SKUs.
                    </p>
                    <span class="text-xs font-mono text-yellow-400 bg-yellow-900/20 px-2 py-1 rounded">Target: Product Sales</span>
                </div>

                <!-- Press Releases -->
                <div class="bg-white/5 p-6 rounded-xl border border-white/5 hover:border-gray-400/30 transition-all group">
                    <div class="w-12 h-12 bg-black/40 rounded-lg flex items-center justify-center mb-4 text-gray-300 text-2xl group-hover:scale-110 transition-transform">
                        <i class="fas fa-newspaper"></i>
                    </div>
                    <h4 class="text-xl font-bold text-white mb-2">Press & News</h4>
                    <p class="text-gray-400 text-sm mb-4">
                        News is worthless if it isn't fresh.<br>
                        <strong>Press Releases, Syndicated News, Brand Announcements</strong>. Index them immediately to hit the "Top Stories" carousel and control your brand narrative.
                    </p>
                    <span class="text-xs font-mono text-gray-400 bg-gray-700/20 px-2 py-1 rounded">Target: Brand Authority</span>
                </div>

                <!-- Local Citations -->
                <div class="bg-white/5 p-6 rounded-xl border border-white/5 hover:border-green-500/30 transition-all group">
                    <div class="w-12 h-12 bg-black/40 rounded-lg flex items-center justify-center mb-4 text-green-500 text-2xl group-hover:scale-110 transition-transform">
                        <i class="fas fa-map-marked-alt"></i>
                    </div>
                    <h4 class="text-xl font-bold text-white mb-2">Local Citations</h4>
                    <p class="text-gray-400 text-sm mb-4">
                        Dominate the "Map Pack".<br>
                        <strong>Yelp, YellowPages, BBB, Chamber of Commerce</strong>. Indexing these directory links validates your Name, Address, and Phone (NAP) data for Google Maps.
                    </p>
                    <span class="text-xs font-mono text-green-400 bg-green-900/20 px-2 py-1 rounded">Target: Local Maps</span>
                </div>

                <!-- Social & UGC -->
                <div class="bg-white/5 p-6 rounded-xl border border-white/5 hover:border-pink-500/30 transition-all group">
                    <div class="w-12 h-12 bg-black/40 rounded-lg flex items-center justify-center mb-4 text-pink-500 text-2xl group-hover:scale-110 transition-transform">
                        <i class="fas fa-users"></i>
                    </div>
                    <h4 class="text-xl font-bold text-white mb-2">Social & UGC</h4>
                    <p class="text-gray-400 text-sm mb-4">
                        Rank for specific questions.<br>
                        <strong>Twitter Threads, Instagram Posts, TikTok Videos, Forum Comments</strong>. Get specific posts indexed to rank for long-tail questions your audience is asking.
                    </p>
                    <span class="text-xs font-mono text-pink-400 bg-pink-900/20 px-2 py-1 rounded">Target: Long-tail Traffic</span>
                </div>
            </div>

            <div class="text-center mt-16">
                <a href="/register" class="inline-flex items-center px-8 py-4 bg-white text-black font-bold rounded-lg hover:bg-gray-200 transition-all text-lg shadow-xl hover:shadow-2xl hover:-translate-y-1">
                    Start Indexing Your Links
                    <i class="fas fa-arrow-right ml-3"></i>
                </a>
                <p class="text-sm text-gray-500 mt-4">
                    Join 5,000+ SEOs boosting their visibility today.
                </p>
            </div>
        </div>
    </section>
            <div class="text-center mt-16">
                <a href="/register" class="inline-flex items-center px-8 py-4 bg-white text-black font-bold rounded-lg hover:bg-gray-200 transition-all text-lg shadow-xl hover:shadow-2xl hover:-translate-y-1">
                    Start Indexing Your Links
                    <i class="fas fa-arrow-right ml-3"></i>
                </a>
                <p class="text-sm text-gray-500 mt-4">
                    Join 5,000+ SEOs boosting their visibility today.
                </p>
            </div>
        </div>
    </section>

    <!-- 5. Advanced Section: Traffic (The "Turbo") -->
    <section class="py-24 relative overflow-hidden">
        <div class="max-w-7xl mx-auto px-6 lg:px-8 relative z-10">
            <div class="card bg-[#1a1a1a] border border-white/10 rounded-2xl p-8 md:p-12 flex flex-col md:flex-row items-center gap-12">
                <div class="md:w-2/3">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded bg-red-500/10 text-red-500 text-xs font-bold uppercase mb-4 border border-red-500/20">
                        <i class="fas fa-exclamation-triangle"></i> Advanced Strategy
                    </div>
                    <h2 class="text-3xl font-bold text-white mb-4">Indexing is Step 1. Ranking Requires Signals.</h2>
                    <p class="text-gray-400 mb-6 text-lg">
                        Google's algorithm has evolved. It now looks for <strong class="text-white">User Signals</strong> (Click-Through Rate, Dwell Time, Traffic Sources) to validate if a newly indexed page is actually relevant.
                    </p>
                    <p class="text-gray-400 mb-6 text-lg">
                        Our <strong>Viral Traffic Blast</strong> technology simulates real interest by sending high-quality, residential traffic from social referrers to your pages. This validates the indexation and helps sticky rankings.
                    </p>
                    <p class="text-sm text-gray-500 italic mb-6">
                        ⚠️ Not recommended for standard business websites. Designed for experienced SEOs, parasite campaigns, and experimental projects.
                    </p>
                    <a href="/viral-blast" class="inline-flex items-center text-white font-bold border-b border-red-500 pb-0.5 hover:text-red-400 transition-colors">
                        Explore Viral Traffic Blast <i class="fas fa-arrow-right ml-2 text-xs"></i>
                    </a>
                </div>
                <div class="md:w-1/3 w-full flex justify-center">
                    <div class="relative w-full max-w-xs">
                        <div class="absolute -inset-4 bg-red-600/20 rounded-full blur-2xl"></div>
                        <div class="relative bg-black/40 p-6 rounded-xl border border-white/10 backdrop-blur-sm">
                            <div class="flex items-center gap-4 mb-4">
                                <div class="w-10 h-10 rounded-full bg-red-500/20 flex items-center justify-center text-red-500">
                                    <i class="fas fa-fire"></i>
                                </div>
                                <div>
                                    <div class="text-white font-bold">Social Signal</div>
                                    <div class="text-xs text-gray-500">Twitter • Reddit • FB</div>
                                </div>
                            </div>
                            <div class="h-2 bg-white/10 rounded-full overflow-hidden">
                                <div class="h-full bg-red-500 w-3/4"></div>
                            </div>
                            <div class="mt-2 text-right text-xs text-red-400 font-mono">+1,420 Visitors</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- 6. Audience Specifics & Integrations -->
    <section class="py-24 bg-black/20">
        <div class="max-w-7xl mx-auto px-6 lg:px-8">
            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
                <!-- Agencies -->
                <div class="p-8 border border-white/5 rounded-xl hover:bg-white/5 transition-colors">
                    <h3 class="text-xl font-bold text-white mb-3 flex items-center"><i class="fas fa-briefcase text-primary-500 mr-3"></i> For Agencies</h3>
                    <p class="text-gray-400 text-sm mb-6">
                        You handle hundreds of links for clients. Manually checking them is impossible. Use our infrastructure to automate the delivery of your link building campaigns.
                    </p>
                    <ul class="text-sm text-gray-500 space-y-3">
                        <li class="flex items-center"><i class="fas fa-check mr-2 text-primary-500"></i> Bulk Processing (10k+ links)</li>
                        <li class="flex items-center"><i class="fas fa-check mr-2 text-primary-500"></i> Developer API Access</li>
                        <li class="flex items-center"><i class="fas fa-check mr-2 text-primary-500"></i> White-label CSV Reports</li>
                    </ul>
                </div>
                
                <!-- Businesses -->
                <div class="p-8 border border-white/5 rounded-xl hover:bg-white/5 transition-colors">
                    <h3 class="text-xl font-bold text-white mb-3 flex items-center"><i class="fas fa-store text-primary-500 mr-3"></i> For Business Owners</h3>
                    <p class="text-gray-400 text-sm mb-6">
                        You don't need to be an SEO expert. You just want your site to appear on Google. Our simple dashboard makes it easy to submit your new pages and forget about it.
                    </p>
                    <ul class="text-sm text-gray-500 space-y-3">
                        <li class="flex items-center"><i class="fas fa-check mr-2 text-primary-500"></i> Simple Dashboard</li>
                        <li class="flex items-center"><i class="fas fa-check mr-2 text-primary-500"></i> No Monthly Subscription</li>
                        <li class="flex items-center"><i class="fas fa-check mr-2 text-primary-500"></i> Auto-Refunds for failures</li>
                    </ul>
                </div>

                <!-- Automation -->
                <div class="p-8 border border-white/5 rounded-xl hover:bg-white/5 transition-colors">
                    <h3 class="text-xl font-bold text-white mb-3 flex items-center"><i class="fas fa-robot text-primary-500 mr-3"></i> For Automation</h3>
                    <p class="text-gray-400 text-sm mb-6">
                        Integrate indexing into your existing workflow. Connect your tools and let us handle the signaling in the background.
                    </p>
                    <div class="space-y-4">
                        <div class="flex items-center justify-between p-2 bg-black/20 rounded border border-white/10">
                            <span class="text-sm text-gray-300 font-bold"><i class="fab fa-chrome mr-2"></i> Chrome Extension</span>
                            <span class="text-xs text-green-400">Available</span>
                        </div>
                        <div class="flex items-center justify-between p-2 bg-black/20 rounded border border-white/10">
                            <span class="text-sm text-gray-300 font-bold"><i class="fas fa-bolt mr-2"></i> Zapier / Make / n8n</span>
                            <span class="text-xs text-green-400">Via API</span>
                        </div>
                        <div class="flex items-center justify-between p-2 bg-black/20 rounded border border-white/10">
                            <span class="text-sm text-gray-300 font-bold"><i class="fab fa-wordpress mr-2"></i> WordPress Plugin</span>
                            <span class="text-xs text-green-400">Easy Setup</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- 7. Pricing -->
    <section id="pricing" class="py-24 border-t border-white/5">
        <div class="max-w-7xl mx-auto px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl md:text-4xl font-bold text-white mb-4">Simple Pay-As-You-Go</h2>
                <p class="text-lg text-gray-400">Credits never expire. Pay only for what you use.</p>
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
                        <li class="flex items-center gap-3"><i class="fa-solid fa-check text-primary-600 text-sm"></i> 100% Accuracy Guarantee</li>
                    </ul>
                    <a href="/register" class="block w-full py-3 px-6 rounded-lg border border-white/10 bg-white/5 hover:bg-white/10 text-white font-bold text-center transition-colors">Get Started</a>
                </div>

                <!-- Premium -->
                <div class="rounded-xl p-8 card border-primary-700 relative overflow-hidden flex flex-col">
                    <div class="absolute top-0 right-0 bg-primary-600 text-white text-[10px] font-bold px-2 py-1 rounded-bl-lg uppercase">Recommended</div>
                    <h3 class="text-lg font-bold text-white mb-2 uppercase tracking-wide">Rapid Indexing</h3>
                    <div class="flex items-baseline gap-1 mb-6">
                        <span class="text-4xl font-extrabold text-primary-500">$0.02</span>
                        <span class="text-gray-500">/ URL</span>
                    </div>
                    <ul class="space-y-4 mb-8 text-gray-300 flex-1">
                        <li class="flex items-center gap-3"><i class="fa-solid fa-check text-primary-500 text-sm"></i> <strong>VIP Queue</strong> Included (Under 2 min)</li>
                        <li class="flex items-center gap-3"><i class="fa-solid fa-check text-primary-500 text-sm"></i> Drip-Feed Included</li>
                        <li class="flex items-center gap-3"><i class="fa-solid fa-check text-primary-500 text-sm"></i> API Access Included</li>
                        <li class="flex items-center gap-3"><i class="fa-solid fa-check text-primary-500 text-sm"></i> Auto-Refund if not indexed</li>
                    </ul>
                    <a href="/register" class="block w-full py-3 px-6 rounded-lg bg-primary-700 hover:bg-primary-600 text-white font-bold text-center transition-colors shadow-lg shadow-primary-900/20">Start Indexing</a>
                </div>
            </div>
             <!-- Payment Methods -->
             <div class="mt-16 text-center">
                <p class="text-sm text-gray-500 mb-6 uppercase tracking-wider font-semibold">Secure Crypto Payments</p>
                <div class="flex flex-wrap justify-center items-center gap-6 opacity-60">
                    <img src="/assets/img/bitcoin-btc-logo.png" alt="Bitcoin" class="h-6 w-auto object-contain">
                    <img src="/assets/img/ethereum-eth-logo.png" alt="Ethereum" class="h-6 w-auto object-contain">
                    <img src="/assets/img/Zcash-Yellow.png" alt="Zcash" class="h-6 w-auto object-contain">
                    <span class="text-xs text-gray-500 border border-white/10 px-2 py-1 rounded">Crypto Only</span>
                </div>
            </div>
        </div>
    </section>

    <!-- 8. Frequently Asked Questions (New Section) -->
    <section class="py-24 bg-[#111111] border-t border-white/5">
        <div class="max-w-4xl mx-auto px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl font-bold text-white mb-4">Frequently Asked Questions</h2>
                <p class="text-gray-400">Everything you need to know about indexing and verification.</p>
            </div>

            <div class="space-y-6" x-data="{ active: null }">
                <!-- FAQ 1 -->
                <div class="bg-white/5 rounded-xl border border-white/5 overflow-hidden">
                    <button @click="active = (active === 1 ? null : 1)" class="flex justify-between items-center w-full p-6 text-left hover:bg-white/5 transition-colors">
                        <span class="text-white font-bold">How fast is the VIP Indexing Queue?</span>
                        <i class="fas fa-chevron-down text-gray-500 transition-transform duration-300" :class="{ 'rotate-180': active === 1 }"></i>
                    </button>
                    <div x-show="active === 1" x-collapse class="px-6 pb-6 text-gray-400 text-sm leading-relaxed">
                        Our VIP Priority Queue is engineered for speed. Internal tests consistently show Googlebot crawling submitted URLs in as little as 35 seconds to 2 minutes. While we force the crawl almost instantly, the actual time for Google to update its public index can vary slightly, but discovery is typically immediate.
                    </div>
                </div>

                <!-- FAQ 2 -->
                <div class="bg-white/5 rounded-xl border border-white/5 overflow-hidden">
                    <button @click="active = (active === 2 ? null : 2)" class="flex justify-between items-center w-full p-6 text-left hover:bg-white/5 transition-colors">
                        <span class="text-white font-bold">Does this work for "Crawled - Currently Not Indexed"?</span>
                        <i class="fas fa-chevron-down text-gray-500 transition-transform duration-300" :class="{ 'rotate-180': active === 2 }"></i>
                    </button>
                    <div x-show="active === 2" x-collapse class="px-6 pb-6 text-gray-400 text-sm leading-relaxed">
                        Yes. This error usually happens when Google visits a page but deems it not authoritative enough to index. By using Rapid Indexer, you are sending a strong signal of importance and authority, which often forces Google to reconsider and index the page. For stubborn pages, we recommend combining indexing with a small amount of social traffic (via Viral Blast) to validate user interest.
                    </div>
                </div>

                <!-- FAQ 3 -->
                <div class="bg-white/5 rounded-xl border border-white/5 overflow-hidden">
                    <button @click="active = (active === 3 ? null : 3)" class="flex justify-between items-center w-full p-6 text-left hover:bg-white/5 transition-colors">
                        <span class="text-white font-bold">Is this safe for my money site?</span>
                        <i class="fas fa-chevron-down text-gray-500 transition-transform duration-300" :class="{ 'rotate-180': active === 3 }"></i>
                    </button>
                    <div x-show="active === 3" x-collapse class="px-6 pb-6 text-gray-400 text-sm leading-relaxed">
                        Absolutely. We use 100% white-hat signaling methods for our Standard and VIP indexing services. We do not build spammy links to your site; we simply notify Google's infrastructure that your content exists and is ready for crawling. It is as safe as using Google Search Console, but automated and faster.
                    </div>
                </div>

                <!-- FAQ 4 -->
                <div class="bg-white/5 rounded-xl border border-white/5 overflow-hidden">
                    <button @click="active = (active === 4 ? null : 4)" class="flex justify-between items-center w-full p-6 text-left hover:bg-white/5 transition-colors">
                        <span class="text-white font-bold">What is the success rate?</span>
                        <i class="fas fa-chevron-down text-gray-500 transition-transform duration-300" :class="{ 'rotate-180': active === 4 }"></i>
                    </button>
                    <div x-show="active === 4" x-collapse class="px-6 pb-6 text-gray-400 text-sm leading-relaxed">
                        We see an average success rate of 98% for valid, crawlable pages. However, no tool can force Google to index "noindex" pages, broken links (404s), or content that violates Google's core policies. If a page fails to index, it is usually an issue with the content quality or site configuration, not the signaling.
                    </div>
                </div>

                <!-- FAQ 5 -->
                <div class="bg-white/5 rounded-xl border border-white/5 overflow-hidden">
                    <button @click="active = (active === 5 ? null : 5)" class="flex justify-between items-center w-full p-6 text-left hover:bg-white/5 transition-colors">
                        <span class="text-white font-bold">Do you offer refunds?</span>
                        <i class="fas fa-chevron-down text-gray-500 transition-transform duration-300" :class="{ 'rotate-180': active === 5 }"></i>
                    </button>
                    <div x-show="active === 5" x-collapse class="px-6 pb-6 text-gray-400 text-sm leading-relaxed">
                        Yes. We operate on a credit system. If you submit a URL for indexing and we cannot verify that Google crawled it, the credits are automatically refunded to your account. You only pay for successful submissions.
                    </div>
                </div>

                <!-- FAQ 6 -->
                <div class="bg-white/5 rounded-xl border border-white/5 overflow-hidden">
                    <button @click="active = (active === 6 ? null : 6)" class="flex justify-between items-center w-full p-6 text-left hover:bg-white/5 transition-colors">
                        <span class="text-white font-bold">Can I integrate this with my software?</span>
                        <i class="fas fa-chevron-down text-gray-500 transition-transform duration-300" :class="{ 'rotate-180': active === 6 }"></i>
                    </button>
                    <div x-show="active === 6" x-collapse class="px-6 pb-6 text-gray-400 text-sm leading-relaxed">
                        Yes! We have a full Developer API available for all users. You can integrate Rapid Indexer into your own SEO tools, dashboards, or internal scripts. We also support integration via Zapier and Make.com for no-code automation.
                    </div>
                </div>
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
                <div class="flex gap-4 ml-4 pl-4 border-l border-white/10">
                    <a href="https://x.com/rapid_indexer" target="_blank" class="text-gray-400 hover:text-white transition-colors"><i class="fab fa-twitter"></i></a>
                    <a href="https://www.linkedin.com/company/rapid-indexer/" target="_blank" class="text-gray-400 hover:text-white transition-colors"><i class="fab fa-linkedin"></i></a>
                </div>
            </div>
            <div class="text-gray-500 text-sm">
                © <?php echo date('Y'); ?> Rapid Indexer. All rights reserved.
            </div>
            <div class="flex gap-6 text-sm font-medium text-gray-400">
                <a href="/privacy" class="hover:text-white transition-colors">Privacy</a>
                <a href="/terms" class="hover:text-white transition-colors">Terms</a>
                <a href="/contact" class="hover:text-white transition-colors">Contact</a>
            </div>
        </div>
    </footer>
</body>
</html>
