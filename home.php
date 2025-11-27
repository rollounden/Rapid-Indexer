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

    <title>Rapid Indexer - Fast Google Indexing Service</title>
    <meta name="description"
        content="Check if your URLs are indexed by Google and submit URLs to speed up indexing. Fast, accurate batch processing with real-time results. Perfect for SEO professionals and website owners.">

    <link rel="icon" href="/favicon.ico" sizes="any">
    <link rel="icon" href="/favicon.svg" type="image/svg+xml">
    <link rel="apple-touch-icon" href="/apple-touch-icon.png">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        emerald: {
                            50: '#ecfdf5',
                            100: '#d1fae5',
                            200: '#a7f3d0',
                            300: '#6ee7b7',
                            400: '#34d399',
                            500: '#10b981',
                            600: '#059669',
                            700: '#047857',
                            800: '#065f46',
                            900: '#064e3b',
                            950: '#022c22',
                        },
                        zinc: {
                            850: '#1f1f22', // Custom darker zinc
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
        
        /* Custom gradient background */
        .radial-gradient-bg {
            background: radial-gradient(circle at top center, #1f2937 0%, #09090b 100%);
        }
        
        /* Form styles */
        .dark input, .dark textarea, .dark select {
            background-color: rgba(255, 255, 255, 0.05);
            border-color: rgba(255, 255, 255, 0.1);
            color: #e4e4e7;
        }
        
        .dark input:focus, .dark textarea:focus, .dark select:focus {
            border-color: #10b981;
            outline: none;
            box-shadow: 0 0 0 1px #10b981;
        }
    </style>
</head>

<body class="radial-gradient-bg text-zinc-900 dark:text-zinc-100 antialiased min-h-screen">
    <!-- Navigation -->
<div class="sticky top-0 z-50 hidden lg:block">
        <div class="bg-emerald-600 text-white">
            <div class="flex w-full items-center justify-center gap-5 px-4 py-2 sm:px-6 lg:px-8">
                <p class="text-xl font-bold leading-snug">
                    Now with 70% Faster Indexing!
                </p>
                <a href="/register.php"
                        class="inline-flex items-center gap-2 rounded-md bg-amber-400 text-black px-3 py-2 text-lg font-bold hover:bg-amber-300 transition">
                        Start Free
                        <svg class="shrink-0 h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
  <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 19.5 15-15m0 0H8.25m11.25 0v11.25"/>
</svg>

                            </a>
                            </div>
        </div>
    </div>

<nav
    class="w-full border-b border-zinc-200 dark:border-zinc-800 bg-white/80 dark:bg-zinc-900/80 backdrop-blur-sm sticky top-0 z-40 lg:top-[52px]"> <!-- adjusted top for subheader -->
    <div class="max-w-7xl mx-auto px-6 lg:px-8">
        <div class="flex justify-between items-center h-16">

            <!-- Styled logo -->
            <div class="inline-block py-1.5">
                <a href="/"
                    class="mb-0.5 truncate leading-tight font-bold text-2xl bg-gradient-to-r from-emerald-400 via-emerald-500 to-emerald-600 bg-clip-text text-transparent tracking-wide uppercase flex items-center gap-2">
                    <span class="border-2 rounded-full px-1 border-emerald-400 text-emerald-400">&#10003;</span> Rapid Indexer
                </a>
            </div>

            <!-- Navigation Links -->
            <nav class="hidden md:flex items-center gap-6">
                <a href="#learnmore"
                    class="text-sm font-medium text-zinc-600 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-zinc-100 transition-colors ">
                    Features
                </a>
                <a href="#pricing"
                    class="text-sm font-medium text-zinc-600 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-zinc-100 transition-colors ">
                    Pricing
                </a>
                <a href="/faq.php"
                    class="text-sm font-medium text-zinc-600 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-zinc-100 transition-colors ">
                    FAQ
                </a>
            </nav>


            <div class="flex items-center gap-4">
                <a href="/login.php"
                    class="text-sm font-medium text-zinc-600 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-zinc-100 transition-colors">
                    Log in
                </a>
                <a href="/register.php"
                    class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-emerald-600 hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500 transition-colors">
                    Sign up
                </a>
            </div>
        </div>
    </div>
</nav>

    <!-- Hero Section -->
    <section class="relative overflow-hidden">
        <div class="max-w-7xl mx-auto px-6 lg:px-8 mt-20 lg:mt-32">
            <div class="text-center">
                <h1 class="text-4xl lg:text-6xl font-semibold tracking-tight text-zinc-900 dark:text-zinc-100 mb-6">
                    Force Google to Index<br>Your Links Instantly
                </h1>

                <p class="text-xl text-zinc-600 dark:text-zinc-400 max-w-3xl mx-auto mb-6 leading-relaxed">
                    Stop waiting weeks for search engines to crawl your site. Our premium indexing API ensures your backlinks and pages get discovered within hours.
                </p>
                <p
                    class="text-xl font-semibold text-emerald-600 dark:text-emerald-400 max-w-2xl mx-auto mb-8 leading-relaxed">
                    🎉 No monthly fees - Pay as you go - Credits never expire
                </p>

                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                                            <a href="/register.php"
                            class="inline-flex items-center px-8 py-4 border border-emerald-300 dark:border-emerald-600 text-zinc-100 dark:text-zinc-100 rounded-xl hover:bg-emerald-500 transition-colors bg-emerald-600 font-black text-xl">
                            Start Indexing Free
                        </a>
                                        <a href="#learnmore"
                        class="inline-flex items-center px-8 py-4 border border-zinc-300 dark:border-zinc-700 text-zinc-900 dark:text-zinc-100 text-xl font-medium rounded-xl hover:bg-zinc-100 dark:hover:bg-zinc-800 transition-colors">
                        Learn More
                    </a>
                </div>
            </div>

            <!-- Demo visualization -->
            <div class="mt-16 max-w-5xl mx-auto space-y-8" x-data="{ showModal: false, urlCount: 0, demoUrls: '' }" x-init="$watch('demoUrls', () => {
                if (!demoUrls) {
                    urlCount = 0;
                } else {
                    urlCount = demoUrls.split('\n').filter(line => line.trim().length > 0).length;
                }
            });">
                <!-- Input Form Demo -->
                <div class="w-full">
                    <div
                        class="rounded-xl border border-neutral-200 bg-white p-6 dark:border-neutral-700 dark:bg-zinc-900">
                        <div class="mb-6">
                            <h2 class="text-xl font-semibold text-neutral-900 dark:text-neutral-100">
                                Google Indexer
                            </h2>
                            <p class="mt-2 text-neutral-600 dark:text-neutral-400">
                                Submit URLs to Google for crawling and indexing.
                            </p>
                            <ul class="list-disc text-sm text-neutral-600 dark:text-neutral-400 pl-6 mt-2">
                                <li>Enter one URL per line.</li>
                                <li>Maximum 10,000 URLs per batch.</li>
                            </ul>
                        </div>

                        <form class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-zinc-800 dark:text-zinc-200 mb-2">
                                    Job Name (Optional)
                                </label>
                                <div class="relative">
                                    <input type="text" class="w-full border rounded-lg block py-2 px-3 bg-white dark:bg-white/10 text-zinc-700 dark:text-zinc-300 border-zinc-200 dark:border-white/10 focus:ring-emerald-500 focus:border-emerald-500" placeholder="e.g., New Product Pages" readonly x-on:click="showModal = true">
                                </div>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-zinc-800 dark:text-zinc-200 mb-2">
                                    URLs to Submit for Indexing
                                </label>
                                <textarea class="block w-full border rounded-lg p-3 bg-white dark:bg-white/10 text-zinc-700 dark:text-zinc-300 border-zinc-200 dark:border-white/10 focus:ring-emerald-500 focus:border-emerald-500 resize-y" rows="6" x-model="demoUrls" placeholder="https://example.com&#10;https://example.com/page&#10;https://another-site.com" readonly x-on:click="showModal = true"></textarea>
                            </div>

                            <div class="flex flex-wrap gap-3 justify-between items-center">
                                <div class="px-3 py-2 border rounded-md text-sm font-medium"
                                    :class="urlCount > 0 ?
                                        'border-yellow-300 bg-yellow-50 dark:bg-yellow-900/20 dark:border-yellow-600 text-yellow-800 dark:text-yellow-400' :
                                        'border-neutral-300 bg-neutral-50 dark:bg-neutral-800 dark:border-neutral-600 text-neutral-600 dark:text-neutral-400'">
                                    Will use <span x-text="urlCount">0</span> Credits
                                </div>
                                <div class="flex gap-3">
                                    <button type="button" class="px-4 py-2 text-sm font-medium rounded-lg bg-white hover:bg-zinc-50 dark:bg-zinc-700 dark:hover:bg-zinc-600 text-zinc-800 dark:text-white border border-zinc-200 dark:border-zinc-600" x-on:click.prevent="showModal = true">
                                        Clear
                                    </button>
                                    <button type="button" class="px-4 py-2 text-sm font-medium rounded-lg bg-emerald-600 hover:bg-emerald-500 text-white shadow-sm" x-on:click.prevent="showModal = true">
                                        Submit For Indexing
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Index Check History (demo) -->
                <div class="w-full max-w-5xl mx-auto">
                    <div class="rounded-xl border border-neutral-200 bg-white dark:border-neutral-700 dark:bg-zinc-900 overflow-hidden">
                        <div class="border-b border-neutral-200 px-6 py-4 dark:border-neutral-700">
                            <h3 class="text-lg font-semibold text-neutral-900 dark:text-neutral-100">Recent Index Check Jobs</h3>
                            <p class="mt-1 text-sm text-neutral-600 dark:text-neutral-400">View and manage your recent index checks</p>
                        </div>

                        <div class="border-b border-neutral-200 dark:border-neutral-700 bg-blue-50 dark:bg-blue-900/10">
                            <div class="px-6 py-4">
                                <div class="flex items-center gap-2 mb-3">
                                    <div class="flex-shrink-0">
                                        <div class="w-2 h-2 bg-blue-500 rounded-full animate-pulse"></div>
                                    </div>
                                    <h4 class="text-sm font-medium text-blue-900 dark:text-blue-100">Active Jobs (1)</h4>
                                </div>
                                <div class="flex items-center justify-between">
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center gap-3 mb-1">
                                            <span class="text-sm font-medium text-blue-900 dark:text-blue-100 truncate">Category Page Crawl</span>
                                            <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900/20 dark:text-blue-400">Processing</span>
                                        </div>
                                        <div class="flex items-center gap-3">
                                            <div class="flex-1 bg-neutral-200 rounded-full h-1.5 dark:bg-neutral-700">
                                                <div class="bg-blue-500 h-1.5 rounded-full transition-all duration-500" style="width: 40%"></div>
                                            </div>
                                            <span class="text-xs text-neutral-600 dark:text-neutral-400 min-w-fit">40/100</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-neutral-200 dark:divide-neutral-700">
                                <thead class="bg-neutral-50 dark:bg-neutral-800">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase tracking-wider">Job Name</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase tracking-wider">Status</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase tracking-wider">URLs</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase tracking-wider">Results</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase tracking-wider">Indexed</th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-zinc-900 divide-y divide-neutral-200 dark:divide-neutral-700">
                                    <tr class="hover:bg-neutral-50 dark:hover:bg-neutral-800 transition-colors">
                                        <td class="px-6 py-4 whitespace-nowrap font-medium text-neutral-900 dark:text-neutral-100">Product Pages</td>
                                        <td class="px-6 py-4 whitespace-nowrap"><span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium bg-emerald-100 text-emerald-800 dark:bg-emerald-900/20 dark:text-emerald-400">Complete</span></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-neutral-900 dark:text-neutral-100">25</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm"><span class="text-emerald-600 dark:text-emerald-400 font-medium">18</span> <span class="text-neutral-400">/</span> <span class="text-red-600 dark:text-red-400 font-medium">7</span></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm"><span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium bg-emerald-100 text-emerald-800 dark:bg-emerald-900/20 dark:text-emerald-400">72%</span></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium"><a href="#" class="text-emerald-600 hover:text-emerald-500 dark:text-emerald-400" x-on:click.prevent="showModal = true">Details</a></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Modal -->
                <div x-show="showModal" x-cloak class="fixed inset-0 z-[100] flex items-center justify-center px-4 sm:px-0">
                    <!-- Overlay -->
                    <div x-show="showModal" x-transition.opacity @click="showModal = false" class="absolute inset-0 bg-black/60 backdrop-blur-sm"></div>

                    <!-- Modal Content -->
                    <div x-show="showModal" x-transition.scale.origin.center
                        class="relative bg-white dark:bg-zinc-800 rounded-xl p-8 max-w-md w-full border border-zinc-200 dark:border-zinc-700 shadow-2xl">
                        <!-- Close button -->
                        <button @click="showModal = false"
                            class="absolute top-4 right-4 text-zinc-500 hover:text-zinc-700 dark:text-zinc-400 dark:hover:text-zinc-200">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>

                        <!-- Modal header -->
                        <div class="mb-6">
                            <h3 class="text-2xl font-semibold text-zinc-900 dark:text-zinc-100 mb-2">Start Using Rapid Indexer</h3>
                            <p class="text-zinc-600 dark:text-zinc-400">Create an account to submit your URLs. No credit card required.</p>
                        </div>

                        <!-- Modal content -->
                        <div class="space-y-4">
                            <div class="bg-emerald-50 dark:bg-emerald-900/20 rounded-lg p-4 border border-emerald-200 dark:border-emerald-700">
                                <div class="flex items-center">
                                    <svg class="w-5 h-5 text-emerald-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                    </svg>
                                    <span class="text-sm text-emerald-700 dark:text-emerald-300"><strong>Free credits</strong> available on signup</span>
                                </div>
                            </div>
                            <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-4 border border-blue-200 dark:border-blue-700">
                                <div class="flex items-center">
                                    <svg class="w-5 h-5 text-blue-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                    </svg>
                                    <span class="text-sm text-blue-700 dark:text-blue-300">Check & submit URLs instantly</span>
                                </div>
                            </div>
                        </div>

                        <!-- Modal buttons -->
                        <div class="flex gap-3 mt-6">
                            <a href="/login.php"
                                class="flex-1 text-center px-6 py-3 border border-zinc-300 dark:border-zinc-600 text-zinc-700 dark:text-zinc-300 hover:bg-zinc-50 dark:hover:bg-zinc-700 font-medium rounded-lg transition-colors">
                                Log In
                            </a>
                            <a href="/register.php"
                                class="flex-1 text-center px-6 py-3 bg-emerald-600 text-white hover:bg-emerald-700 font-medium rounded-lg transition-colors">
                                Sign Up
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="pt-40" id="learnmore">
        <div class="max-w-7xl mx-auto px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl lg:text-4xl font-semibold text-zinc-900 dark:text-zinc-100 mb-4">
                    Why Choose Rapid Indexer?
                </h2>
                <p class="text-xl text-zinc-600 dark:text-zinc-400 max-w-2xl mx-auto">
                    Built for SEO professionals who need fast, accurate index checking AND fast indexing.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <!-- Feature 1 -->
                <div
                    class="text-center p-8 rounded-2xl bg-zinc-50 dark:bg-zinc-800/50 border border-zinc-200 dark:border-zinc-700">
                    <div
                        class="w-12 h-12 bg-blue-100 dark:bg-blue-900/50 rounded-xl flex items-center justify-center mx-auto mb-6">
                        <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-zinc-900 dark:text-zinc-100 mb-4">Index Checking</h3>
                    <p class="text-zinc-600 dark:text-zinc-400">
                        Check up to 10,000 URLs at once to see if they're indexed by Google. Real-time SERP results.
                    </p>
                </div>

                <!-- Feature 2 -->
                <div
                    class="text-center p-8 rounded-2xl bg-zinc-50 dark:bg-zinc-800/50 border border-zinc-200 dark:border-zinc-700">
                    <div
                        class="w-12 h-12 bg-emerald-100 dark:bg-emerald-900/50 rounded-xl flex items-center justify-center mx-auto mb-6">
                        <svg class="w-6 h-6 text-emerald-600 dark:text-emerald-400" fill="none"
                            stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10">
                            </path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-zinc-900 dark:text-zinc-100 mb-4">Direct Submission</h3>
                    <p class="text-zinc-600 dark:text-zinc-400">
                        Submit URLs directly for fast crawling and indexing. Skip the wait
                        and get indexed.
                    </p>
                </div>

                <!-- Feature 3 -->
                <div
                    class="text-center p-8 rounded-2xl bg-zinc-50 dark:bg-zinc-800/50 border border-zinc-200 dark:border-zinc-700">
                    <div
                        class="w-12 h-12 bg-purple-100 dark:bg-purple-900/50 rounded-xl flex items-center justify-center mx-auto mb-6">
                        <svg class="w-6 h-6 text-purple-600 dark:text-purple-400" fill="none"
                            stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-zinc-900 dark:text-zinc-100 mb-4">Lightning Fast</h3>
                    <p class="text-zinc-600 dark:text-zinc-400">
                        Batch process hundreds of URLs in seconds. Optimized performance gets you results faster.
                    </p>
                </div>

                <!-- Feature 4 -->
                <div
                    class="text-center p-8 rounded-2xl bg-zinc-50 dark:bg-zinc-800/50 border border-zinc-200 dark:border-zinc-700">
                    <div
                        class="w-12 h-12 bg-orange-100 dark:bg-orange-900/50 rounded-xl flex items-center justify-center mx-auto mb-6">
                        <svg class="w-6 h-6 text-orange-600 dark:text-orange-400" fill="none"
                            stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z">
                            </path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-zinc-900 dark:text-zinc-100 mb-4">Export & Track</h3>
                    <p class="text-zinc-600 dark:text-zinc-400">
                        Download reports as CSV, track history, and monitor your indexing progress over time.
                    </p>
                </div>

                <!-- Feature 5 -->
                <div
                    class="text-center p-8 rounded-2xl bg-zinc-50 dark:bg-zinc-800/50 border border-zinc-200 dark:border-zinc-700">
                    <div
                        class="w-12 h-12 bg-indigo-100 dark:bg-indigo-900/50 rounded-xl flex items-center justify-center mx-auto mb-6">
                        <svg class="w-6 h-6 text-indigo-600 dark:text-indigo-400" fill="none"
                            stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z">
                            </path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-zinc-900 dark:text-zinc-100 mb-4">Fast Turnaround</h3>
                    <p class="text-zinc-600 dark:text-zinc-400">
                        Our proprietary indexing method gets you noticed by Google within hours.
                    </p>
                </div>

                <!-- Feature 6 -->
                <div
                    class="text-center p-8 rounded-2xl bg-zinc-50 dark:bg-zinc-800/50 border border-zinc-200 dark:border-zinc-700">
                    <div
                        class="w-12 h-12 bg-pink-100 dark:bg-pink-900/50 rounded-xl flex items-center justify-center mx-auto mb-6">
                        <svg class="w-6 h-6 text-pink-600 dark:text-pink-400" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1">
                            </path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-zinc-900 dark:text-zinc-100 mb-4">Simple Pricing</h3>
                    <p class="text-zinc-600 dark:text-zinc-400">
                        Simple system where 1 credit = 1 url, checking or indexing. No subscriptions needed.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- How It Works -->
    <section class="mt-40">
        <div class="max-w-7xl mx-auto px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl lg:text-4xl font-semibold text-zinc-900 dark:text-zinc-100 mb-4">
                    How Rapid Indexer Works
                </h2>
                <p class="text-xl text-zinc-600 dark:text-zinc-400">
                    Two powerful workflows to manage your Google indexing: Check status and submit for indexing
                </p>
            </div>

            <div class="space-y-16">
                <!-- Index Checking Workflow -->
                <div class="text-center mb-12">
                    <div
                        class="inline-flex items-center px-4 py-2 bg-blue-100 dark:bg-blue-900/30 rounded-full text-blue-800 dark:text-blue-200 text-sm font-semibold mb-8">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        Check Index Status
                    </div>
                    <h3 class="text-2xl font-semibold text-zinc-900 dark:text-zinc-100 mb-4">Verify Your URLs are Indexed</h3>
                    <p class="text-lg text-zinc-600 dark:text-zinc-400 mb-8">Check if your URLs appear in Google's search results</p>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                        <!-- Step 1 -->
                        <div class="text-center">
                            <div class="relative">
                                <div
                                    class="w-16 h-16 bg-blue-600 text-white rounded-2xl flex items-center justify-center mx-auto mb-6 text-2xl font-bold shadow-lg shadow-blue-500/20">
                                    1
                                </div>
                            </div>
                            <h4 class="text-xl font-semibold text-zinc-900 dark:text-zinc-100 mb-4">Enter Your URLs</h4>
                            <p class="text-zinc-600 dark:text-zinc-400">
                                Paste your URLs into our checker. One URL per line, up to 10,000 at once.
                            </p>
                        </div>

                        <!-- Step 2 -->
                        <div class="text-center">
                            <div class="relative">
                                <div
                                    class="w-16 h-16 bg-blue-600 text-white rounded-2xl flex items-center justify-center mx-auto mb-6 text-2xl font-bold shadow-lg shadow-blue-500/20">
                                    2
                                </div>
                            </div>
                            <h4 class="text-xl font-semibold text-zinc-900 dark:text-zinc-100 mb-4">Run the Check</h4>
                            <p class="text-zinc-600 dark:text-zinc-400">
                                Our system queries Google's SERPs in real-time for the most accurate results.
                            </p>
                        </div>

                        <!-- Step 3 -->
                        <div class="text-center">
                            <div
                                class="w-16 h-16 bg-blue-600 text-white rounded-2xl flex items-center justify-center mx-auto mb-6 text-2xl font-bold shadow-lg shadow-blue-500/20">
                                3
                            </div>
                            <h4 class="text-xl font-semibold text-zinc-900 dark:text-zinc-100 mb-4">View Results</h4>
                            <p class="text-zinc-600 dark:text-zinc-400">
                                See indexed/not indexed status for each URL. Export results and track changes over time.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Index Submission Workflow -->
                <div class="text-center mt-36">
                    <div
                        class="inline-flex items-center px-4 py-2 bg-emerald-100 dark:bg-emerald-900/30 rounded-full text-emerald-800 dark:text-emerald-200 text-sm font-semibold mb-8">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10">
                            </path>
                        </svg>
                        Submit for Indexing
                    </div>
                    <h3 class="text-2xl font-semibold text-zinc-900 dark:text-zinc-100 mb-4">Fast-Track Your URLs to Google</h3>
                    <p class="text-lg text-zinc-600 dark:text-zinc-400 mb-8">Our indexer gets your URLs crawled by Google as fast as possible.</p>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                        <!-- Step 1 -->
                        <div class="text-center">
                            <div class="relative">
                                <div
                                    class="w-16 h-16 bg-emerald-600 text-white rounded-2xl flex items-center justify-center mx-auto mb-6 text-2xl font-bold shadow-lg shadow-emerald-500/20">
                                    1
                                </div>
                            </div>
                            <h4 class="text-xl font-semibold text-zinc-900 dark:text-zinc-100 mb-4">Prepare URLs</h4>
                            <p class="text-zinc-600 dark:text-zinc-400">
                                Enter the URLs you want Google to crawl and index. Perfect for new pages or updates.
                            </p>
                        </div>

                        <!-- Step 2 -->
                        <div class="text-center">
                            <div class="relative">
                                <div
                                    class="w-16 h-16 bg-emerald-600 text-white rounded-2xl flex items-center justify-center mx-auto mb-6 text-2xl font-bold shadow-lg shadow-emerald-500/20">
                                    2
                                </div>
                            </div>
                            <h4 class="text-xl font-semibold text-zinc-900 dark:text-zinc-100 mb-4">Submit to our Indexer</h4>
                            <p class="text-zinc-600 dark:text-zinc-400">
                                We get Googlebot's eyes on your URLs in record time.
                            </p>
                        </div>

                        <!-- Step 3 -->
                        <div class="text-center">
                            <div
                                class="w-16 h-16 bg-emerald-600 text-white rounded-2xl flex items-center justify-center mx-auto mb-6 text-2xl font-bold shadow-lg shadow-emerald-500/20">
                                3
                            </div>
                            <h4 class="text-xl font-semibold text-zinc-900 dark:text-zinc-100 mb-4">Track Progress</h4>
                            <p class="text-zinc-600 dark:text-zinc-400">
                                Monitor submission status and track when Google acknowledges your indexing requests.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Pricing Section -->
    <section id="pricing" class="py-40 bg-white dark:bg-zinc-900/50">
        <div class="max-w-7xl mx-auto px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl lg:text-4xl font-semibold text-zinc-900 dark:text-zinc-100 mb-4">
                    Simple, Transparent Pricing
                </h2>
                <p class="text-xl text-zinc-600 dark:text-zinc-400">Pay as you go. Credits never expire.</p>
            </div>
            
            <div class="grid md:grid-cols-2 gap-8 max-w-4xl mx-auto">
                <!-- Link Checking -->
                <div class="rounded-2xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800/50 p-8 text-center">
                    <h3 class="text-xl font-bold text-zinc-900 dark:text-zinc-100 mb-2">LINK CHECKING</h3>
                    <div class="flex items-baseline justify-center my-4">
                        <span class="text-5xl font-extrabold text-zinc-900 dark:text-zinc-100">$0.01</span>
                        <span class="text-zinc-500 dark:text-zinc-400 ml-2">/ URL check</span>
                    </div>
                    <ul class="text-zinc-600 dark:text-zinc-400 space-y-4 mb-8 text-left max-w-xs mx-auto">
                        <li class="flex items-center"><svg class="w-5 h-5 text-emerald-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg> Check Google Index Status</li>
                        <li class="flex items-center"><svg class="w-5 h-5 text-emerald-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg> Real-time Verification</li>
                        <li class="flex items-center"><svg class="w-5 h-5 text-emerald-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg> Bulk Processing</li>
                    </ul>
                    <a href="/register.php" class="block w-full py-3 px-6 border border-zinc-300 dark:border-zinc-600 rounded-xl text-zinc-700 dark:text-zinc-200 hover:bg-zinc-50 dark:hover:bg-zinc-700 font-bold transition-colors">
                        Get Started
                    </a>
                </div>

                <!-- Premium Indexing -->
                <div class="rounded-2xl border-2 border-emerald-500 bg-white dark:bg-zinc-800/50 p-8 text-center relative overflow-hidden">
                    <div class="absolute top-0 right-0 left-0 bg-emerald-500 text-white text-xs font-bold py-1">RECOMMENDED</div>
                    <h3 class="text-xl font-bold text-zinc-900 dark:text-zinc-100 mb-2 mt-2">PREMIUM INDEXING</h3>
                    <div class="flex items-baseline justify-center my-4">
                        <span class="text-5xl font-extrabold text-emerald-600 dark:text-emerald-400">$0.03</span>
                        <span class="text-zinc-500 dark:text-zinc-400 ml-2">/ URL submission</span>
                    </div>
                    <ul class="text-zinc-600 dark:text-zinc-400 space-y-4 mb-8 text-left max-w-xs mx-auto">
                        <li class="flex items-center"><svg class="w-5 h-5 text-emerald-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg> Force Google Crawl</li>
                        <li class="flex items-center"><svg class="w-5 h-5 text-emerald-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg> Includes Index Check</li>
                        <li class="flex items-center"><svg class="w-5 h-5 text-emerald-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg> 98% Index Rate</li>
                        <li class="flex items-center"><svg class="w-5 h-5 text-amber-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"></path></svg> VIP Queue Available (+$0.05)</li>
                    </ul>
                    <a href="/register.php" class="block w-full py-3 px-6 bg-emerald-600 hover:bg-emerald-500 rounded-xl text-white font-bold transition-colors">
                        Start Indexing
                    </a>
                </div>
            </div>
            
            <div class="mt-12 text-center text-zinc-500 dark:text-zinc-400">
                <p>Secure payments via PayPal & Crypto</p>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="my-40">
        <div class="max-w-4xl mx-auto text-center px-6 lg:px-8">
            <h2 class="text-3xl lg:text-4xl font-semibold text-zinc-900 dark:text-zinc-100 mb-6">
                Ready to Master Your Google Indexing?
            </h2>
            <p class="text-xl text-zinc-600 dark:text-zinc-200 mb-8">
                Check index status AND index your URLs. Sign up for free and get credits to start. No
                credit card required.
            </p>

            <div class="mb-8">
                <div
                    class="inline-flex items-center px-4 py-2 bg-zinc-100 dark:bg-zinc-800 rounded-full text-sm text-zinc-600 dark:text-zinc-400">
                    <svg class="w-4 h-4 mr-2 text-emerald-500" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                            d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                            clip-rule="evenodd"></path>
                    </svg>
                    1 credit = 1 URL check
                </div>
            </div>

            <a href="/register.php"
                    class="inline-flex items-center px-8 py-4 border border-emerald-300 dark:border-emerald-600 text-zinc-100 dark:text-zinc-100 rounded-xl hover:bg-emerald-500 transition-colors bg-emerald-600 font-black text-xl">
                    Log In / Sign Up
                </a>
        </div>
    </section>

    <footer class="border-t border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900">
    <div class="max-w-7xl mx-auto px-6 lg:px-8 py-8">
        <div class="flex flex-col md:flex-row justify-between items-center gap-4">
            <p class="text-sm text-zinc-500 dark:text-zinc-400">
                © <?php echo date('Y'); ?> Rapid Indexer
            </p>
            <div class="flex items-center gap-6">
                <a href="/privacy.php"
                    class="text-sm text-zinc-500 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-zinc-100 ">
                    Privacy
                </a>
                <a href="/terms.php"
                    class="text-sm text-zinc-500 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-zinc-100 ">
                    Terms
                </a>
            </div>
        </div>
    </div>
</footer>
</body>

</html>
