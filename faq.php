<?php
require_once __DIR__ . '/config/config.php';
session_start();

$page_title = 'Frequently Asked Questions - Rapid Indexer';
$meta_description = 'Find answers to common questions about Rapid Indexer, pricing, indexing time, and API usage.';
$canonical_url = 'https://rapid-indexer.com/faq.php';

include __DIR__ . '/includes/header_new.php';
?>

<div class="max-w-4xl mx-auto px-6 lg:px-8 py-12">
    <div class="text-center mb-12">
        <h1 class="text-4xl font-extrabold text-white mb-4">Frequently Asked Questions</h1>
        <p class="text-lg text-gray-400">Find answers to common questions about Rapid Indexer.</p>
    </div>
    
    <div class="space-y-8">
        <!-- Group 1: General -->
        <div>
            <h4 class="text-xl font-bold text-primary-500 mb-4">General</h4>
            
            <div class="space-y-4">
                <!-- Item 1 -->
                <div class="card rounded-lg overflow-hidden" x-data="{ open: true }">
                    <button @click="open = !open" class="w-full px-6 py-4 text-left flex justify-between items-center focus:outline-none bg-white/5 hover:bg-white/10 transition-colors">
                        <span class="font-bold text-white">What is Rapid Indexer?</span>
                        <i class="fas fa-chevron-down text-gray-400 transition-transform" :class="{ 'rotate-180': open }"></i>
                    </button>
                    <div x-show="open" x-collapse class="px-6 py-4 text-gray-400 border-t border-white/5">
                        Rapid Indexer is a tool that helps SEO professionals and website owners get their URLs indexed by search engines like Google faster. We use approved methods to notify search engines about your new or updated content.
                    </div>
                </div>
                
                <!-- Item 2 -->
                <div class="card rounded-lg overflow-hidden" x-data="{ open: false }">
                    <button @click="open = !open" class="w-full px-6 py-4 text-left flex justify-between items-center focus:outline-none bg-white/5 hover:bg-white/10 transition-colors">
                        <span class="font-bold text-white">How long does indexing take?</span>
                        <i class="fas fa-chevron-down text-gray-400 transition-transform" :class="{ 'rotate-180': open }"></i>
                    </button>
                    <div x-show="open" x-collapse class="px-6 py-4 text-gray-400 border-t border-white/5">
                        While we submit your URLs immediately, Google typically crawls them within 24-48 hours. Actual indexing depends on Google's algorithms and the quality of your content, but our users often see results in as little as a few hours.
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Group 2: Pricing & Credits -->
        <div>
            <h4 class="text-xl font-bold text-primary-500 mb-4">Pricing & Credits</h4>
            
            <div class="space-y-4">
                <!-- Item 3 -->
                <div class="card rounded-lg overflow-hidden" x-data="{ open: false }">
                    <button @click="open = !open" class="w-full px-6 py-4 text-left flex justify-between items-center focus:outline-none bg-white/5 hover:bg-white/10 transition-colors">
                        <span class="font-bold text-white">How much does it cost?</span>
                        <i class="fas fa-chevron-down text-gray-400 transition-transform" :class="{ 'rotate-180': open }"></i>
                    </button>
                    <div x-show="open" x-collapse class="px-6 py-4 text-gray-400 border-t border-white/5">
                        <p class="mb-2">We use a credit-based system.</p>
                        <ul class="list-disc list-inside ml-2 space-y-1 mb-2">
                            <li><strong class="text-gray-300">Indexing:</strong> 2 credits ($0.02) per URL</li>
                            <li><strong class="text-gray-300">Checking:</strong> 1 credit ($0.01) per URL</li>
                        </ul>
                        <p>Credits can be purchased via PayPal or Cryptocurrency starting at $1.00.</p>
                    </div>
                </div>
                
                <!-- Item 4 -->
                <div class="card rounded-lg overflow-hidden" x-data="{ open: false }">
                    <button @click="open = !open" class="w-full px-6 py-4 text-left flex justify-between items-center focus:outline-none bg-white/5 hover:bg-white/10 transition-colors">
                        <span class="font-bold text-white">Do credits expire?</span>
                        <i class="fas fa-chevron-down text-gray-400 transition-transform" :class="{ 'rotate-180': open }"></i>
                    </button>
                    <div x-show="open" x-collapse class="px-6 py-4 text-gray-400 border-t border-white/5">
                        No, your purchased credits never expire. You can use them whenever you need.
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Group 3: Technical -->
        <div>
            <h4 class="text-xl font-bold text-primary-500 mb-4">Technical</h4>
            
            <div class="space-y-4">
                <!-- Item 5 -->
                <div class="card rounded-lg overflow-hidden" x-data="{ open: false }">
                    <button @click="open = !open" class="w-full px-6 py-4 text-left flex justify-between items-center focus:outline-none bg-white/5 hover:bg-white/10 transition-colors">
                        <span class="font-bold text-white">Do you guarantee 100% indexing?</span>
                        <i class="fas fa-chevron-down text-gray-400 transition-transform" :class="{ 'rotate-180': open }"></i>
                    </button>
                    <div x-show="open" x-collapse class="px-6 py-4 text-gray-400 border-t border-white/5">
                        No service can guarantee 100% indexing as it ultimately depends on Google's quality standards. However, we guarantee that we will successfully submit your URLs to Google's indexing API and force a crawl. If your content is indexable, it will likely get indexed.
                    </div>
                </div>
                
                <!-- Item 6 -->
                <div class="card rounded-lg overflow-hidden" x-data="{ open: false }">
                    <button @click="open = !open" class="w-full px-6 py-4 text-left flex justify-between items-center focus:outline-none bg-white/5 hover:bg-white/10 transition-colors">
                        <span class="font-bold text-white">What is the VIP Queue?</span>
                        <i class="fas fa-chevron-down text-gray-400 transition-transform" :class="{ 'rotate-180': open }"></i>
                    </button>
                    <div x-show="open" x-collapse class="px-6 py-4 text-gray-400 border-t border-white/5">
                        The VIP Queue allows you to prioritize your tasks for faster processing. This is available for indexing tasks with up to 100 URLs and costs an additional 5 credits per link.
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="text-center mt-12">
        <p class="text-lg text-gray-400 mb-6">Still have questions?</p>
        <a href="/contact.php" class="inline-flex items-center justify-center px-8 py-4 bg-primary-600 hover:bg-primary-700 text-white font-bold rounded-lg transition-all shadow-lg shadow-primary-900/20">
            Contact Support
        </a>
    </div>
</div>

<?php include __DIR__ . '/includes/footer_new.php'; ?>
