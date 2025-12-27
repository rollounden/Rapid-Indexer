<?php
// Start session if not already started (handled in header_new.php usually, but good practice)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$page_title = 'API Documentation - Rapid Indexer';
$meta_description = 'Rapid Indexer User API Reference. Integrate indexing capabilities directly into your applications.';
$current_page = 'api-docs.php'; // For potential nav highlighting if added

include 'includes/header_new.php';
?>

<div class="max-w-7xl mx-auto px-6 lg:px-8 py-12">
    <!-- Header Section -->
    <div class="mb-12">
        <h1 class="text-4xl font-bold text-white mb-4">User API Reference</h1>
        <p class="text-xl text-gray-400 max-w-3xl">
            The Rapid Indexer User API allows you to integrate indexing capabilities directly into your applications, scripts, or WordPress sites.
        </p>
    </div>

    <!-- Find API Key Section -->
    <div class="mb-12 bg-primary-900/10 border border-primary-500/20 rounded-xl p-6 lg:p-8">
        <h2 class="text-2xl font-bold text-white mb-4 flex items-center gap-2">
            <i class="fas fa-key text-primary-500"></i> How to find your API Key
        </h2>
        <div class="space-y-4 text-gray-300">
            <p>To access the API, you need your unique API key. Follow these steps to find it:</p>
            <ol class="list-decimal list-inside space-y-2 ml-2">
                <li><a href="/login" class="text-primary-400 hover:text-primary-300 underline">Log in</a> to your Rapid Indexer account.</li>
                <li>Click on your <strong>user account</strong> (email address) in the top right side of the header.</li>
                <li>Click on <strong>API Access</strong> in the dropdown menu (located just above the logout button).</li>
            </ol>
            <p class="text-sm text-gray-400 mt-4">
                <i class="fas fa-info-circle mr-1"></i> Keep your API key secret. Do not share it publicly.
            </p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
        <!-- Sidebar Navigation (Optional, for now just content) -->
        <div class="hidden lg:block lg:col-span-1">
            <div class="sticky top-24 space-y-2">
                <p class="font-semibold text-white mb-4 uppercase text-sm tracking-wider">Contents</p>
                <a href="#base-url" class="block text-gray-400 hover:text-white transition-colors">Base URL</a>
                <a href="#authentication" class="block text-gray-400 hover:text-white transition-colors">Authentication</a>
                <a href="#get-profile" class="block text-gray-400 hover:text-white transition-colors">Get User Profile</a>
                <a href="#create-task" class="block text-gray-400 hover:text-white transition-colors">Create Task</a>
                <a href="#get-task" class="block text-gray-400 hover:text-white transition-colors">Get Task Details</a>
                <a href="#get-links" class="block text-gray-400 hover:text-white transition-colors">Get Task Links</a>
                <a href="#traffic-api" class="block text-gray-400 hover:text-white transition-colors">Traffic API</a>
                <a href="#errors" class="block text-gray-400 hover:text-white transition-colors">Error Handling</a>
            </div>
        </div>

        <!-- Main Content -->
        <div class="lg:col-span-3 space-y-12">
            
            <!-- Base URL -->
            <section id="base-url" class="card rounded-xl p-6">
                <h2 class="text-2xl font-bold text-white mb-4">Base URL</h2>
                <div class="bg-black/50 rounded-lg p-4 border border-white/10 font-mono text-sm text-primary-400">
                    https://rapid-indexer.com/api/v1/index.php
                </div>
            </section>

            <!-- Authentication -->
            <section id="authentication" class="card rounded-xl p-6">
                <h2 class="text-2xl font-bold text-white mb-4">Authentication</h2>
                <p class="text-gray-300 mb-4">Authentication is handled via the <code class="text-primary-400 bg-black/30 px-1 py-0.5 rounded">X-API-Key</code> header.</p>
                
                <h3 class="text-lg font-semibold text-white mb-2">Header Example</h3>
                <div class="bg-black/50 rounded-lg p-4 border border-white/10 font-mono text-sm text-gray-300 mb-6">
                    X-API-Key: 5f4dcc3b5aa765d61d8327deb882cf99
                </div>

                <h3 class="text-lg font-semibold text-white mb-2">Query Parameter Alternative</h3>
                <div class="bg-black/50 rounded-lg p-4 border border-white/10 font-mono text-sm text-gray-300">
                    ?api_key=5f4dcc3b5aa765d61d8327deb882cf99
                </div>
            </section>

            <!-- Get Profile -->
            <section id="get-profile" class="card rounded-xl p-6">
                <h2 class="text-2xl font-bold text-white mb-2">1. Get User Profile</h2>
                <p class="text-gray-400 mb-6">Check your account status and credit balance.</p>
                
                <div class="grid md:grid-cols-2 gap-4 mb-6">
                    <div>
                        <span class="text-xs font-bold text-gray-500 uppercase tracking-wider">Method</span>
                        <div class="font-mono text-green-400">GET</div>
                    </div>
                    <div>
                        <span class="text-xs font-bold text-gray-500 uppercase tracking-wider">URL Parameter</span>
                        <div class="font-mono text-white">?action=me</div>
                    </div>
                </div>

                <h3 class="text-lg font-semibold text-white mb-2">Response Example</h3>
                <pre class="bg-black/50 rounded-lg p-4 border border-white/10 overflow-x-auto text-sm text-gray-300"><code>{
  "success": true,
  "user": {
    "id": 123,
    "email": "user@example.com",
    "credits_balance": 500,
    "created_at": "2023-10-27 10:00:00"
  }
}</code></pre>
            </section>

            <!-- Create Task -->
            <section id="create-task" class="card rounded-xl p-6">
                <h2 class="text-2xl font-bold text-white mb-2">2. Create Task</h2>
                <p class="text-gray-400 mb-6">Submit URLs for indexing or checking.</p>
                
                <div class="grid md:grid-cols-2 gap-4 mb-6">
                    <div>
                        <span class="text-xs font-bold text-gray-500 uppercase tracking-wider">Method</span>
                        <div class="font-mono text-blue-400">POST</div>
                    </div>
                    <div>
                        <span class="text-xs font-bold text-gray-500 uppercase tracking-wider">URL Parameter</span>
                        <div class="font-mono text-white">?action=create_task</div>
                    </div>
                </div>

                <div class="overflow-x-auto mb-6">
                    <table class="w-full text-left">
                        <thead>
                            <tr class="border-b border-white/10">
                                <th class="py-2 text-sm font-semibold text-gray-300">Parameter</th>
                                <th class="py-2 text-sm font-semibold text-gray-300">Type</th>
                                <th class="py-2 text-sm font-semibold text-gray-300">Required</th>
                                <th class="py-2 text-sm font-semibold text-gray-300">Description</th>
                            </tr>
                        </thead>
                        <tbody class="text-sm text-gray-400">
                            <tr class="border-b border-white/5">
                                <td class="py-2 font-mono text-primary-400">urls</td>
                                <td class="py-2">array/string</td>
                                <td class="py-2 text-white">Yes</td>
                                <td class="py-2">Array of URLs or newline-separated string</td>
                            </tr>
                            <tr class="border-b border-white/5">
                                <td class="py-2 font-mono text-primary-400">type</td>
                                <td class="py-2">string</td>
                                <td class="py-2">No</td>
                                <td class="py-2"><code>indexer</code> (default) or <code>checker</code></td>
                            </tr>
                            <tr class="border-b border-white/5">
                                <td class="py-2 font-mono text-primary-400">engine</td>
                                <td class="py-2">string</td>
                                <td class="py-2">No</td>
                                <td class="py-2"><code>google</code> (default) or <code>yandex</code></td>
                            </tr>
                            <tr class="border-b border-white/5">
                                <td class="py-2 font-mono text-primary-400">title</td>
                                <td class="py-2">string</td>
                                <td class="py-2">No</td>
                                <td class="py-2">Optional reference title</td>
                            </tr>
                            <tr class="border-b border-white/5">
                                <td class="py-2 font-mono text-primary-400">vip</td>
                                <td class="py-2">bool</td>
                                <td class="py-2">No</td>
                                <td class="py-2">Enable VIP Queue (costs extra)</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <h3 class="text-lg font-semibold text-white mb-2">Request Example</h3>
                <pre class="bg-black/50 rounded-lg p-4 border border-white/10 overflow-x-auto text-sm text-gray-300 mb-4"><code>{
  "urls": [
    "https://example.com/page1",
    "https://example.com/page2"
  ],
  "type": "indexer",
  "engine": "google",
  "title": "My Blog Posts",
  "vip": true
}</code></pre>
            </section>

            <!-- Get Task Details -->
            <section id="get-task" class="card rounded-xl p-6">
                <h2 class="text-2xl font-bold text-white mb-2">3. Get Task Details</h2>
                <p class="text-gray-400 mb-6">Check the status of a specific task.</p>
                
                <div class="grid md:grid-cols-2 gap-4 mb-6">
                    <div>
                        <span class="text-xs font-bold text-gray-500 uppercase tracking-wider">Method</span>
                        <div class="font-mono text-green-400">GET</div>
                    </div>
                    <div>
                        <span class="text-xs font-bold text-gray-500 uppercase tracking-wider">URL Parameter</span>
                        <div class="font-mono text-white">?action=get_task&task_id={id}</div>
                    </div>
                </div>

                <h3 class="text-lg font-semibold text-white mb-2">Response Example</h3>
                <pre class="bg-black/50 rounded-lg p-4 border border-white/10 overflow-x-auto text-sm text-gray-300"><code>{
  "success": true,
  "task": {
    "id": 456,
    "title": "My Blog Posts",
    "type": "indexer",
    "status": "processing",
    "progress": {
      "updated": 10,
      "pending": 5
    }
  }
}</code></pre>
            </section>

            <!-- Get Task Links -->
            <section id="get-links" class="card rounded-xl p-6">
                <h2 class="text-2xl font-bold text-white mb-2">4. Get Task Links</h2>
                <p class="text-gray-400 mb-6">Get detailed status for each link in a task.</p>
                
                <div class="grid md:grid-cols-2 gap-4 mb-6">
                    <div>
                        <span class="text-xs font-bold text-gray-500 uppercase tracking-wider">Method</span>
                        <div class="font-mono text-green-400">GET</div>
                    </div>
                    <div>
                        <span class="text-xs font-bold text-gray-500 uppercase tracking-wider">URL Parameter</span>
                        <div class="font-mono text-white">?action=get_task_links&task_id={id}</div>
                    </div>
                </div>

                <h3 class="text-lg font-semibold text-white mb-2">Response Example</h3>
                <pre class="bg-black/50 rounded-lg p-4 border border-white/10 overflow-x-auto text-sm text-gray-300"><code>{
  "success": true,
  "links": [
    {
      "url": "https://example.com/page1",
      "status": "indexed",
      "checked_at": "2023-12-23 14:05:00"
    }
  ]
}</code></pre>
            </section>

            <!-- Traffic API -->
            <section id="traffic-api" class="card rounded-xl p-6">
                <h2 class="text-2xl font-bold text-white mb-2">5. Create Traffic Task</h2>
                <p class="text-gray-400 mb-6">Simulate viral traffic to your URLs.</p>
                
                <div class="grid md:grid-cols-2 gap-4 mb-6">
                    <div>
                        <span class="text-xs font-bold text-gray-500 uppercase tracking-wider">Method</span>
                        <div class="font-mono text-blue-400">POST</div>
                    </div>
                    <div>
                        <span class="text-xs font-bold text-gray-500 uppercase tracking-wider">URL Parameter</span>
                        <div class="font-mono text-white">?action=create_task</div>
                    </div>
                </div>

                <div class="overflow-x-auto mb-6">
                    <table class="w-full text-left">
                        <thead>
                            <tr class="border-b border-white/10">
                                <th class="py-2 text-sm font-semibold text-gray-300">Parameter</th>
                                <th class="py-2 text-sm font-semibold text-gray-300">Required</th>
                                <th class="py-2 text-sm font-semibold text-gray-300">Description</th>
                            </tr>
                        </thead>
                        <tbody class="text-sm text-gray-400">
                            <tr class="border-b border-white/5">
                                <td class="py-2 font-mono text-primary-400">type</td>
                                <td class="py-2 text-white">Yes</td>
                                <td class="py-2">Must be <code>traffic</code></td>
                            </tr>
                            <tr class="border-b border-white/5">
                                <td class="py-2 font-mono text-primary-400">link</td>
                                <td class="py-2 text-white">Yes</td>
                                <td class="py-2">Target URL to boost</td>
                            </tr>
                            <tr class="border-b border-white/5">
                                <td class="py-2 font-mono text-primary-400">quantity</td>
                                <td class="py-2 text-white">Yes</td>
                                <td class="py-2">Total visitors (Min: 100)</td>
                            </tr>
                            <tr class="border-b border-white/5">
                                <td class="py-2 font-mono text-primary-400">country</td>
                                <td class="py-2">No</td>
                                <td class="py-2">2-letter code (e.g. US, DE). Default: WW</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <h3 class="text-lg font-semibold text-white mb-2">Request Example</h3>
                <pre class="bg-black/50 rounded-lg p-4 border border-white/10 overflow-x-auto text-sm text-gray-300"><code>{
  "type": "traffic",
  "link": "https://example.com/viral-post",
  "quantity": 5000,
  "mode": "campaign",
  "country": "US"
}</code></pre>
            </section>

            <!-- Error Handling -->
            <section id="errors" class="card rounded-xl p-6">
                <h2 class="text-2xl font-bold text-white mb-4">Error Handling</h2>
                <p class="text-gray-300 mb-4">If an error occurs, the API will return a 4xx or 5xx status code and a JSON body with an <code>error</code> field.</p>
                
                <pre class="bg-black/50 rounded-lg p-4 border border-white/10 overflow-x-auto text-sm text-gray-300"><code>{
  "success": false,
  "error": "Insufficient credits"
}</code></pre>
            </section>

        </div>
    </div>
</div>

<?php include 'includes/footer_new.php'; ?>

