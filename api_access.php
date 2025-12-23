<?php
require_once __DIR__ . '/config/config.php';
session_start();

// Redirect if not logged in
if (!isset($_SESSION['uid'])) {
    header('Location: /login');
    exit;
}

require_once __DIR__ . '/src/Db.php';
require_once __DIR__ . '/src/UserService.php';

$userId = $_SESSION['uid'];
$error = '';
$success = '';
$apiKey = '';

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'generate_key') {
        try {
            $apiKey = UserService::generateApiKey($userId);
            $success = 'New API Key generated successfully!';
        } catch (Exception $e) {
            $error = 'Error generating API key: ' . $e->getMessage();
        }
    }
}

// Fetch current user data
$user = UserService::getById($userId);
if ($user) {
    // If api_key column exists in user array (fetched via getById might need update or separate query if not selected)
    // UserService::getById selects: id, email, credits_balance, status, role, created_at
    // It does NOT select api_key. We need to fetch it separately or update getById.
    // Let's query it directly here to be safe and avoid modifying UserService for now if not needed elsewhere.
    
    $pdo = Db::conn();
    $stmt = $pdo->prepare('SELECT api_key FROM users WHERE id = ?');
    $stmt->execute([$userId]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $apiKey = $result['api_key'] ?? '';
}

$page_title = 'API Access - Rapid Indexer';
include __DIR__ . '/includes/header_new.php';
?>

<div class="max-w-4xl mx-auto px-6 lg:px-8 py-10">
    
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-white">API Access</h1>
        <p class="text-gray-400 mt-2">Manage your API key for programmatic access to Rapid Indexer.</p>
    </div>

    <?php if ($error): ?>
        <div class="mb-6 bg-red-500/10 border border-red-500/20 text-red-400 p-4 rounded-lg flex items-center gap-3">
            <i class="fas fa-exclamation-circle"></i>
            <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>
    
    <?php if ($success): ?>
        <div class="mb-6 bg-green-500/10 border border-green-500/20 text-green-400 p-4 rounded-lg flex items-center gap-3">
            <i class="fas fa-check-circle"></i>
            <?php echo htmlspecialchars($success); ?>
        </div>
    <?php endif; ?>

    <div class="card rounded-xl overflow-hidden mb-8">
        <div class="px-6 py-4 border-b border-white/5 bg-white/5">
            <h3 class="text-lg font-bold text-white flex items-center gap-2">
                <i class="fas fa-key text-primary-500"></i> Your API Key
            </h3>
        </div>
        <div class="p-6">
            <?php if ($apiKey): ?>
                <div class="mb-6">
                    <label class="block text-xs font-bold text-gray-400 uppercase mb-2">Current API Key</label>
                    <div class="flex gap-2">
                        <input type="text" id="apiKeyField" value="<?php echo htmlspecialchars($apiKey); ?>" readonly class="w-full bg-[#111] border border-[#333] rounded-lg p-3 text-white font-mono text-sm focus:border-primary-500 focus:ring-1 focus:ring-primary-500 transition-colors">
                        <button onclick="copyToClipboard()" class="bg-white/10 hover:bg-white/20 text-white font-bold py-3 px-4 rounded-lg transition-colors" title="Copy to Clipboard">
                            <i class="fas fa-copy"></i>
                        </button>
                    </div>
                    <p class="text-xs text-gray-500 mt-2">
                        <i class="fas fa-exclamation-triangle text-yellow-500 mr-1"></i> 
                        Keep this key secret. Do not share it or expose it in client-side code.
                    </p>
                </div>
            <?php else: ?>
                <div class="text-center py-8 text-gray-500">
                    <i class="fas fa-key text-4xl mb-3 opacity-20"></i>
                    <p>You haven't generated an API key yet.</p>
                </div>
            <?php endif; ?>

            <form method="POST" onsubmit="return confirm('Are you sure? Any existing key will be invalidated immediately.');">
                <input type="hidden" name="action" value="generate_key">
                <button type="submit" class="bg-primary-600 hover:bg-primary-700 text-white font-bold py-2 px-4 rounded-lg transition-all shadow-lg shadow-primary-900/20 flex items-center gap-2">
                    <i class="fas fa-sync-alt"></i> <?php echo $apiKey ? 'Regenerate API Key' : 'Generate API Key'; ?>
                </button>
            </form>
        </div>
    </div>

    <div class="card rounded-xl overflow-hidden">
        <div class="px-6 py-4 border-b border-white/5 bg-white/5">
            <h3 class="text-lg font-bold text-white flex items-center gap-2">
                <i class="fas fa-book text-blue-500"></i> API Documentation
            </h3>
        </div>
        <div class="p-6">
            <p class="text-gray-300 mb-8">
                The Rapid Indexer User API allows you to integrate indexing capabilities directly into your applications, scripts, or WordPress sites.
            </p>
            
            <!-- Base URL -->
            <h3 class="text-xl font-bold text-white mb-4">Base URL</h3>
            <div class="bg-black/20 rounded-lg p-4 mb-4 border border-white/5">
                <code class="text-primary-400 text-sm font-mono block">https://rapid-indexer.com/api/v1/index.php</code>
            </div>

            <!-- Authentication -->
            <h3 class="text-xl font-bold text-white mt-8 mb-4">Authentication</h3>
            <p class="text-gray-300 mb-4">
                Authentication is handled via the <code class="bg-white/10 px-1.5 py-0.5 rounded text-sm font-mono text-primary-300">X-API-Key</code> header.
            </p>
            <ul class="list-disc list-inside text-gray-300 space-y-1 mb-4">
                <li>Get your API Key from the section above.</li>
                <li>Include it in every request.</li>
            </ul>
            
            <div class="mb-4">
                <p class="text-sm font-bold text-gray-400 mb-2">Header Example:</p>
                <div class="bg-[#111] rounded-lg p-4 border border-[#333] overflow-x-auto">
                    <pre class="text-gray-300 font-mono text-xs">X-API-Key: YOUR_API_KEY</pre>
                </div>
            </div>
            
            <div class="mb-4">
                <p class="text-sm font-bold text-gray-400 mb-2">Query Parameter Alternative:</p>
                <div class="bg-[#111] rounded-lg p-4 border border-[#333] overflow-x-auto">
                    <pre class="text-gray-300 font-mono text-xs">?api_key=YOUR_API_KEY</pre>
                </div>
            </div>

            <!-- Endpoints -->
            <h3 class="text-xl font-bold text-white mt-8 mb-4">Indexing API</h3>

            <!-- 1. Get User Profile -->
            <div class="mb-8 border-l-2 border-primary-500/30 pl-4">
                <h4 class="text-lg font-bold text-white mb-2">1. Get User Profile</h4>
                <p class="text-gray-400 mb-2 text-sm">Check your account status and credit balance.</p>
                <div class="flex gap-2 mb-3">
                    <span class="px-2 py-1 rounded bg-blue-500/20 text-blue-400 text-xs font-bold border border-blue-500/30">GET</span>
                    <code class="text-gray-300 text-sm font-mono">?action=me</code>
                </div>
                
                <p class="text-sm font-bold text-gray-400 mb-2">cURL Example:</p>
                <div class="bg-[#111] rounded-lg p-4 border border-[#333] overflow-x-auto mb-4">
<pre class="text-gray-300 font-mono text-xs">
curl -X GET "https://rapid-indexer.com/api/v1/index.php?action=me" \
     -H "X-API-Key: YOUR_API_KEY"
</pre>
                </div>

                <p class="text-sm font-bold text-gray-400 mb-2">Response Example:</p>
                <div class="bg-[#111] rounded-lg p-4 border border-[#333] overflow-x-auto">
<pre class="text-gray-300 font-mono text-xs">
{
  "success": true,
  "user": {
    "id": 123,
    "email": "user@example.com",
    "credits_balance": 500,
    "created_at": "2023-10-27 10:00:00"
  }
}
</pre>
                </div>
            </div>

            <!-- 2. Create Task -->
            <div class="mb-8 border-l-2 border-primary-500/30 pl-4">
                <h4 class="text-lg font-bold text-white mb-2">2. Create Task</h4>
                <p class="text-gray-400 mb-2 text-sm">Submit URLs for indexing or checking.</p>
                <div class="flex gap-2 mb-3">
                    <span class="px-2 py-1 rounded bg-green-500/20 text-green-400 text-xs font-bold border border-green-500/30">POST</span>
                    <code class="text-gray-300 text-sm font-mono">?action=create_task</code>
                </div>
                
                <div class="overflow-x-auto mb-4">
                    <table class="w-full text-sm text-left text-gray-400">
                        <thead class="text-xs text-gray-300 uppercase bg-white/5">
                            <tr>
                                <th class="px-3 py-2">Parameter</th>
                                <th class="px-3 py-2">Type</th>
                                <th class="px-3 py-2">Required</th>
                                <th class="px-3 py-2">Default</th>
                                <th class="px-3 py-2">Description</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/5">
                            <tr class="bg-white/5">
                                <td class="px-3 py-2 font-mono text-primary-300">urls</td>
                                <td class="px-3 py-2">array/string</td>
                                <td class="px-3 py-2 text-red-400">Yes</td>
                                <td class="px-3 py-2">-</td>
                                <td class="px-3 py-2">Array of URLs or newline-separated string</td>
                            </tr>
                            <tr>
                                <td class="px-3 py-2 font-mono text-primary-300">type</td>
                                <td class="px-3 py-2">string</td>
                                <td class="px-3 py-2">No</td>
                                <td class="px-3 py-2">indexer</td>
                                <td class="px-3 py-2"><code class="bg-white/10 px-1 rounded">indexer</code> or <code class="bg-white/10 px-1 rounded">checker</code></td>
                            </tr>
                            <tr class="bg-white/5">
                                <td class="px-3 py-2 font-mono text-primary-300">engine</td>
                                <td class="px-3 py-2">string</td>
                                <td class="px-3 py-2">No</td>
                                <td class="px-3 py-2">google</td>
                                <td class="px-3 py-2"><code class="bg-white/10 px-1 rounded">google</code> or <code class="bg-white/10 px-1 rounded">yandex</code></td>
                            </tr>
                            <tr>
                                <td class="px-3 py-2 font-mono text-primary-300">title</td>
                                <td class="px-3 py-2">string</td>
                                <td class="px-3 py-2">No</td>
                                <td class="px-3 py-2">null</td>
                                <td class="px-3 py-2">Optional reference title for the task</td>
                            </tr>
                            <tr class="bg-white/5">
                                <td class="px-3 py-2 font-mono text-primary-300">vip</td>
                                <td class="px-3 py-2">bool</td>
                                <td class="px-3 py-2">No</td>
                                <td class="px-3 py-2">false</td>
                                <td class="px-3 py-2">Enable VIP Queue (faster processing, costs extra)</td>
                            </tr>
                            <tr>
                                <td class="px-3 py-2 font-mono text-primary-300">drip_feed</td>
                                <td class="px-3 py-2">bool</td>
                                <td class="px-3 py-2">No</td>
                                <td class="px-3 py-2">false</td>
                                <td class="px-3 py-2">Enable Drip Feed (spread submissions over time)</td>
                            </tr>
                            <tr class="bg-white/5">
                                <td class="px-3 py-2 font-mono text-primary-300">drip_duration_days</td>
                                <td class="px-3 py-2">int</td>
                                <td class="px-3 py-2">No</td>
                                <td class="px-3 py-2">3</td>
                                <td class="px-3 py-2">Duration for drip feed in days</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <p class="text-sm font-bold text-gray-400 mb-2">Request Body Example:</p>
                <div class="bg-[#111] rounded-lg p-4 border border-[#333] overflow-x-auto mb-4">
<pre class="text-gray-300 font-mono text-xs">
{
  "urls": [
    "https://example.com/page1",
    "https://example.com/page2"
  ],
  "type": "indexer",
  "engine": "google",
  "title": "My Blog Posts"
}
</pre>
                </div>

                <p class="text-sm font-bold text-gray-400 mb-2">cURL Example:</p>
                <div class="bg-[#111] rounded-lg p-4 border border-[#333] overflow-x-auto mb-4">
<pre class="text-gray-300 font-mono text-xs">
curl -X POST "https://rapid-indexer.com/api/v1/index.php?action=create_task" \
     -H "X-API-Key: YOUR_API_KEY" \
     -H "Content-Type: application/json" \
     -d '{
       "urls": ["https://example.com/page1", "https://example.com/page2"],
       "type": "indexer",
       "engine": "google",
       "title": "My Blog Posts"
     }'
</pre>
                </div>

                <p class="text-sm font-bold text-gray-400 mb-2">Response Example:</p>
                <div class="bg-[#111] rounded-lg p-4 border border-[#333] overflow-x-auto">
<pre class="text-gray-300 font-mono text-xs">
{
  "success": true,
  "message": "Task created successfully",
  "task_id": 456,
  "provider": "speedyindex",
  "is_drip_feed": false
}
</pre>
                </div>
            </div>

            <!-- 3. Get Task Details -->
            <div class="mb-8 border-l-2 border-primary-500/30 pl-4">
                <h4 class="text-lg font-bold text-white mb-2">3. Get Task Details</h4>
                <p class="text-gray-400 mb-2 text-sm">Check the status of a specific task (indexing, checking, or traffic).</p>
                <div class="flex gap-2 mb-3">
                    <span class="px-2 py-1 rounded bg-blue-500/20 text-blue-400 text-xs font-bold border border-blue-500/30">GET</span>
                    <code class="text-gray-300 text-sm font-mono">?action=get_task&task_id={id}</code>
                </div>
                
                <p class="text-sm font-bold text-gray-400 mb-2">cURL Example:</p>
                <div class="bg-[#111] rounded-lg p-4 border border-[#333] overflow-x-auto mb-4">
<pre class="text-gray-300 font-mono text-xs">
curl -X GET "https://rapid-indexer.com/api/v1/index.php?action=get_task&task_id=456" \
     -H "X-API-Key: YOUR_API_KEY"
</pre>
                </div>

                <p class="text-sm font-bold text-gray-400 mb-2">Response Example:</p>
                <div class="bg-[#111] rounded-lg p-4 border border-[#333] overflow-x-auto">
<pre class="text-gray-300 font-mono text-xs">
{
  "success": true,
  "task": {
    "id": 456,
    "title": "My Blog Posts",
    "status": "processing",
    "progress": {
      "updated": 10,
      "pending": 5
    }
  }
}
</pre>
                </div>
            </div>

            <!-- 4. Get Task Links -->
            <div class="mb-8 border-l-2 border-primary-500/30 pl-4">
                <h4 class="text-lg font-bold text-white mb-2">4. Get Task Links</h4>
                <p class="text-gray-400 mb-2 text-sm">Get detailed status for each link in an indexing/checking task.</p>
                <div class="flex gap-2 mb-3">
                    <span class="px-2 py-1 rounded bg-blue-500/20 text-blue-400 text-xs font-bold border border-blue-500/30">GET</span>
                    <code class="text-gray-300 text-sm font-mono">?action=get_task_links&task_id={id}</code>
                </div>
                
                <p class="text-sm font-bold text-gray-400 mb-2">cURL Example:</p>
                <div class="bg-[#111] rounded-lg p-4 border border-[#333] overflow-x-auto mb-4">
<pre class="text-gray-300 font-mono text-xs">
curl -X GET "https://rapid-indexer.com/api/v1/index.php?action=get_task_links&task_id=456" \
     -H "X-API-Key: YOUR_API_KEY"
</pre>
                </div>

                <p class="text-sm font-bold text-gray-400 mb-2">Response Example:</p>
                <div class="bg-[#111] rounded-lg p-4 border border-[#333] overflow-x-auto">
<pre class="text-gray-300 font-mono text-xs">
{
  "success": true,
  "links": [
    {
      "url": "https://example.com/page1",
      "status": "indexed",
      "checked_at": "2023-12-23 14:05:00"
    }
  ]
}
</pre>
                </div>
            </div>

            <!-- Traffic API -->
            <h3 class="text-xl font-bold text-white mt-8 mb-4">Traffic API</h3>

            <!-- 5. Create Traffic Task -->
            <div class="mb-8 border-l-2 border-primary-500/30 pl-4">
                <h4 class="text-lg font-bold text-white mb-2">5. Create Traffic Task</h4>
                <p class="text-gray-400 mb-2 text-sm">Simulate viral traffic to your URLs.</p>
                <div class="flex gap-2 mb-3">
                    <span class="px-2 py-1 rounded bg-green-500/20 text-green-400 text-xs font-bold border border-green-500/30">POST</span>
                    <code class="text-gray-300 text-sm font-mono">?action=create_task</code>
                </div>
                
                <div class="overflow-x-auto mb-4">
                    <table class="w-full text-sm text-left text-gray-400">
                        <thead class="text-xs text-gray-300 uppercase bg-white/5">
                            <tr>
                                <th class="px-3 py-2">Parameter</th>
                                <th class="px-3 py-2">Required</th>
                                <th class="px-3 py-2">Default</th>
                                <th class="px-3 py-2">Description</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/5">
                            <tr class="bg-white/5">
                                <td class="px-3 py-2 font-mono text-primary-300">type</td>
                                <td class="px-3 py-2 text-red-400">Yes</td>
                                <td class="px-3 py-2">-</td>
                                <td class="px-3 py-2">Must be <code class="bg-white/10 px-1 rounded">traffic</code></td>
                            </tr>
                            <tr>
                                <td class="px-3 py-2 font-mono text-primary-300">link</td>
                                <td class="px-3 py-2 text-red-400">Yes</td>
                                <td class="px-3 py-2">-</td>
                                <td class="px-3 py-2">Target URL to boost</td>
                            </tr>
                            <tr class="bg-white/5">
                                <td class="px-3 py-2 font-mono text-primary-300">quantity</td>
                                <td class="px-3 py-2 text-red-400">Yes</td>
                                <td class="px-3 py-2">-</td>
                                <td class="px-3 py-2">Total visitors (Min: 100)</td>
                            </tr>
                            <tr>
                                <td class="px-3 py-2 font-mono text-primary-300">mode</td>
                                <td class="px-3 py-2">No</td>
                                <td class="px-3 py-2">single</td>
                                <td class="px-3 py-2"><code class="bg-white/10 px-1 rounded">single</code> (Quick) or <code class="bg-white/10 px-1 rounded">campaign</code> (Drip-feed)</td>
                            </tr>
                            <tr class="bg-white/5">
                                <td class="px-3 py-2 font-mono text-primary-300">days</td>
                                <td class="px-3 py-2">No</td>
                                <td class="px-3 py-2">1</td>
                                <td class="px-3 py-2">Duration in days (only for <code class="bg-white/10 px-1 rounded">campaign</code> mode)</td>
                            </tr>
                            <tr>
                                <td class="px-3 py-2 font-mono text-primary-300">country</td>
                                <td class="px-3 py-2">No</td>
                                <td class="px-3 py-2">WW</td>
                                <td class="px-3 py-2">2-letter ISO code (see reference below)</td>
                            </tr>
                            <tr class="bg-white/5">
                                <td class="px-3 py-2 font-mono text-primary-300">device</td>
                                <td class="px-3 py-2">No</td>
                                <td class="px-3 py-2">5</td>
                                <td class="px-3 py-2">Device type (see reference below)</td>
                            </tr>
                            <tr>
                                <td class="px-3 py-2 font-mono text-primary-300">type_of_traffic</td>
                                <td class="px-3 py-2">No</td>
                                <td class="px-3 py-2">2</td>
                                <td class="px-3 py-2">Traffic source type (see reference below)</td>
                            </tr>
                            <tr class="bg-white/5">
                                <td class="px-3 py-2 font-mono text-primary-300">google_keyword</td>
                                <td class="px-3 py-2">Conditional</td>
                                <td class="px-3 py-2">-</td>
                                <td class="px-3 py-2">Required if type_of_traffic=<code class="bg-white/10 px-1 rounded">1</code></td>
                            </tr>
                            <tr>
                                <td class="px-3 py-2 font-mono text-primary-300">referring_url</td>
                                <td class="px-3 py-2">Conditional</td>
                                <td class="px-3 py-2">-</td>
                                <td class="px-3 py-2">Required if type_of_traffic=<code class="bg-white/10 px-1 rounded">2</code></td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <p class="text-sm font-bold text-gray-400 mb-2">Request Body Example:</p>
                <div class="bg-[#111] rounded-lg p-4 border border-[#333] overflow-x-auto mb-4">
<pre class="text-gray-300 font-mono text-xs">
{
  "type": "traffic",
  "link": "https://example.com/viral-post",
  "quantity": 5000,
  "mode": "campaign",
  "country": "US",
  "device": 5,
  "type_of_traffic": 2,
  "referring_url": "https://twitter.com/news/status/123"
}
</pre>
                </div>

                <p class="text-sm font-bold text-gray-400 mb-2">cURL Example:</p>
                <div class="bg-[#111] rounded-lg p-4 border border-[#333] overflow-x-auto">
<pre class="text-gray-300 font-mono text-xs">
curl -X POST "https://rapid-indexer.com/api/v1/index.php?action=create_task" \
     -H "X-API-Key: YOUR_API_KEY" \
     -H "Content-Type: application/json" \
     -d '{
       "type": "traffic",
       "link": "https://example.com/viral-post",
       "quantity": 5000,
       "mode": "campaign",
       "country": "US",
       "device": 5,
       "type_of_traffic": 2,
       "referring_url": "https://twitter.com/news"
     }'
</pre>
                </div>
            </div>

            <!-- Traffic API Reference Values -->
            <h3 class="text-xl font-bold text-white mt-8 mb-4">Traffic API Reference Values</h3>
            
            <!-- Device Options -->
            <div class="mb-6 border-l-2 border-blue-500/30 pl-4">
                <h4 class="text-lg font-bold text-white mb-3"><i class="fas fa-mobile-alt text-blue-400 mr-2"></i>Device Options</h4>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left text-gray-400">
                        <thead class="text-xs text-gray-300 uppercase bg-white/5">
                            <tr>
                                <th class="px-3 py-2">Value</th>
                                <th class="px-3 py-2">Description</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/5">
                            <tr class="bg-white/5"><td class="px-3 py-2 font-mono text-primary-300">5</td><td class="px-3 py-2">Mixed (Mobile & Desktop) <span class="text-green-400 text-xs">— Default</span></td></tr>
                            <tr><td class="px-3 py-2 font-mono text-primary-300">1</td><td class="px-3 py-2">Desktop</td></tr>
                            <tr class="bg-white/5"><td class="px-3 py-2 font-mono text-primary-300">4</td><td class="px-3 py-2">Mixed (Mobile Only)</td></tr>
                            <tr><td class="px-3 py-2 font-mono text-primary-300">2</td><td class="px-3 py-2">Mobile (Android)</td></tr>
                            <tr class="bg-white/5"><td class="px-3 py-2 font-mono text-primary-300">3</td><td class="px-3 py-2">Mobile (iOS)</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Traffic Type Options -->
            <div class="mb-6 border-l-2 border-purple-500/30 pl-4">
                <h4 class="text-lg font-bold text-white mb-3"><i class="fas fa-chart-line text-purple-400 mr-2"></i>Traffic Type Options</h4>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left text-gray-400">
                        <thead class="text-xs text-gray-300 uppercase bg-white/5">
                            <tr>
                                <th class="px-3 py-2">Value</th>
                                <th class="px-3 py-2">Description</th>
                                <th class="px-3 py-2">Extra Parameter</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/5">
                            <tr class="bg-white/5"><td class="px-3 py-2 font-mono text-primary-300">2</td><td class="px-3 py-2">Social Media / Custom Referrer <span class="text-green-400 text-xs">— Recommended</span></td><td class="px-3 py-2 font-mono text-xs">referring_url</td></tr>
                            <tr><td class="px-3 py-2 font-mono text-primary-300">1</td><td class="px-3 py-2">Google Keyword Search</td><td class="px-3 py-2 font-mono text-xs">google_keyword</td></tr>
                            <tr class="bg-white/5"><td class="px-3 py-2 font-mono text-primary-300">3</td><td class="px-3 py-2">Direct / Blank Referrer</td><td class="px-3 py-2 text-gray-500">None</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Country Options -->
            <div class="mb-6 border-l-2 border-green-500/30 pl-4">
                <h4 class="text-lg font-bold text-white mb-3"><i class="fas fa-globe text-green-400 mr-2"></i>Available Countries</h4>
                <p class="text-gray-400 text-sm mb-4">Use 2-letter ISO country codes. Default is <code class="bg-white/10 px-1.5 py-0.5 rounded text-sm font-mono text-primary-300">WW</code> (Worldwide).</p>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Regions -->
                    <div class="bg-white/5 rounded-lg p-4">
                        <h5 class="text-sm font-bold text-gray-300 mb-2">Regions</h5>
                        <div class="grid grid-cols-2 gap-1 text-xs">
                            <div><code class="text-primary-300">WW</code> Worldwide</div>
                            <div><code class="text-primary-300">NAM</code> North America</div>
                            <div><code class="text-primary-300">EUR</code> Europe</div>
                            <div><code class="text-primary-300">ASI</code> Asia</div>
                            <div><code class="text-primary-300">AFR</code> Africa</div>
                            <div><code class="text-primary-300">SAM</code> South America</div>
                            <div><code class="text-primary-300">MEA</code> Middle East</div>
                        </div>
                    </div>
                    
                    <!-- North America -->
                    <div class="bg-white/5 rounded-lg p-4">
                        <h5 class="text-sm font-bold text-gray-300 mb-2">North America</h5>
                        <div class="grid grid-cols-2 gap-1 text-xs">
                            <div><code class="text-primary-300">US</code> United States</div>
                            <div><code class="text-primary-300">CA</code> Canada</div>
                        </div>
                    </div>
                    
                    <!-- Europe -->
                    <div class="bg-white/5 rounded-lg p-4">
                        <h5 class="text-sm font-bold text-gray-300 mb-2">Europe</h5>
                        <div class="grid grid-cols-2 gap-1 text-xs">
                            <div><code class="text-primary-300">GB</code> United Kingdom</div>
                            <div><code class="text-primary-300">DE</code> Germany</div>
                            <div><code class="text-primary-300">FR</code> France</div>
                            <div><code class="text-primary-300">ES</code> Spain</div>
                            <div><code class="text-primary-300">IT</code> Italy</div>
                            <div><code class="text-primary-300">NL</code> Netherlands</div>
                            <div><code class="text-primary-300">SE</code> Sweden</div>
                            <div><code class="text-primary-300">CH</code> Switzerland</div>
                            <div><code class="text-primary-300">PL</code> Poland</div>
                            <div><code class="text-primary-300">BE</code> Belgium</div>
                            <div><code class="text-primary-300">AT</code> Austria</div>
                            <div><code class="text-primary-300">CZ</code> Czech Republic</div>
                            <div><code class="text-primary-300">DK</code> Denmark</div>
                            <div><code class="text-primary-300">HU</code> Hungary</div>
                            <div><code class="text-primary-300">LT</code> Lithuania</div>
                            <div><code class="text-primary-300">RO</code> Romania</div>
                            <div><code class="text-primary-300">RU</code> Russia</div>
                            <div><code class="text-primary-300">RS</code> Serbia</div>
                            <div><code class="text-primary-300">UA</code> Ukraine</div>
                        </div>
                    </div>
                    
                    <!-- Asia & Others -->
                    <div class="bg-white/5 rounded-lg p-4">
                        <h5 class="text-sm font-bold text-gray-300 mb-2">Asia & Pacific</h5>
                        <div class="grid grid-cols-2 gap-1 text-xs">
                            <div><code class="text-primary-300">IN</code> India</div>
                            <div><code class="text-primary-300">ID</code> Indonesia</div>
                            <div><code class="text-primary-300">JP</code> Japan</div>
                            <div><code class="text-primary-300">KR</code> South Korea</div>
                            <div><code class="text-primary-300">HK</code> Hong Kong</div>
                            <div><code class="text-primary-300">SG</code> Singapore</div>
                            <div><code class="text-primary-300">TW</code> Taiwan</div>
                            <div><code class="text-primary-300">TH</code> Thailand</div>
                            <div><code class="text-primary-300">VN</code> Vietnam</div>
                            <div><code class="text-primary-300">PK</code> Pakistan</div>
                            <div><code class="text-primary-300">AE</code> UAE</div>
                            <div><code class="text-primary-300">AU</code> Australia</div>
                        </div>
                    </div>
                    
                    <!-- South America & Africa -->
                    <div class="bg-white/5 rounded-lg p-4">
                        <h5 class="text-sm font-bold text-gray-300 mb-2">South America & Africa</h5>
                        <div class="grid grid-cols-2 gap-1 text-xs">
                            <div><code class="text-primary-300">BR</code> Brazil</div>
                            <div><code class="text-primary-300">AR</code> Argentina</div>
                            <div><code class="text-primary-300">CL</code> Chile</div>
                            <div><code class="text-primary-300">ZA</code> South Africa</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Error Handling -->
            <h3 class="text-xl font-bold text-white mt-8 mb-4">Error Handling</h3>
            <p class="text-gray-300 mb-4">
                If an error occurs, the API will return a 4xx or 5xx status code and a JSON body with an <code class="bg-white/10 px-1.5 py-0.5 rounded text-sm font-mono text-primary-300">error</code> field.
            </p>
            <div class="bg-[#111] rounded-lg p-4 border border-[#333] overflow-x-auto">
<pre class="text-gray-300 font-mono text-xs">
{
  "success": false,
  "error": "Insufficient credits"
}
</pre>
            </div>
            
        </div>
    </div>

</div>

<script>
    function copyToClipboard() {
        var copyText = document.getElementById("apiKeyField");
        copyText.select();
        copyText.setSelectionRange(0, 99999); // For mobile devices
        navigator.clipboard.writeText(copyText.value).then(function() {
            // Optional: visual feedback
            const btn = document.querySelector('button[onclick="copyToClipboard()"]');
            const original = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-check text-green-400"></i>';
            setTimeout(() => {
                btn.innerHTML = original;
            }, 2000);
        });
    }
</script>

<?php include __DIR__ . '/includes/footer_new.php'; ?>

