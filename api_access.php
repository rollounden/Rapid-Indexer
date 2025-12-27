<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['uid'])) {
    header('Location: /login');
    exit;
}

$page_title = 'API Access - Rapid Indexer';
$current_page = 'api_access.php';

require_once 'src/Db.php';
require_once 'src/UserService.php';

try {
    $user = UserService::getById($_SESSION['uid']);
} catch (Exception $e) {
    die("Error loading user data");
}

include 'includes/header_new.php';
?>

<div class="max-w-4xl mx-auto px-6 lg:px-8 py-12">
    <div class="mb-12">
        <h1 class="text-3xl font-bold text-white mb-2">API Access</h1>
        <p class="text-gray-400">Manage your API key and integrations.</p>
    </div>

    <!-- API Key Card -->
    <div class="card rounded-xl p-6 mb-8">
        <h2 class="text-xl font-bold text-white mb-4">Your API Key</h2>
        <p class="text-gray-400 text-sm mb-6">
            Use this key to authenticate your requests to the Rapid Indexer API. 
            Keep it secret! If you suspect it has been compromised, contact support to regenerate it.
        </p>
        
        <div class="relative">
            <div class="bg-black/50 border border-white/10 rounded-lg p-4 flex items-center justify-between gap-4">
                <code class="font-mono text-primary-400 text-lg truncate" id="apiKeyDisplay">
                    <?php echo htmlspecialchars($user['api_key']); ?>
                </code>
                <button onclick="copyApiKey()" class="text-gray-400 hover:text-white transition-colors p-2" title="Copy to clipboard">
                    <i class="fas fa-copy"></i>
                </button>
            </div>
        </div>
        
        <div class="mt-4 flex gap-4 text-sm">
            <a href="/api-docs" class="text-primary-400 hover:text-primary-300 flex items-center gap-2">
                <i class="fas fa-book"></i> View Documentation
            </a>
        </div>
    </div>
    
    <!-- Integration Examples -->
    <div class="card rounded-xl p-6">
        <h2 class="text-xl font-bold text-white mb-4">Quick Integration</h2>
        
        <div x-data="{ tab: 'curl' }">
            <div class="flex gap-4 border-b border-white/10 mb-4">
                <button @click="tab = 'curl'" :class="{ 'text-primary-500 border-primary-500': tab === 'curl', 'text-gray-400 border-transparent hover:text-white': tab !== 'curl' }" class="pb-2 border-b-2 font-medium transition-colors">cURL</button>
                <button @click="tab = 'php'" :class="{ 'text-primary-500 border-primary-500': tab === 'php', 'text-gray-400 border-transparent hover:text-white': tab !== 'php' }" class="pb-2 border-b-2 font-medium transition-colors">PHP</button>
                <button @click="tab = 'node'" :class="{ 'text-primary-500 border-primary-500': tab === 'node', 'text-gray-400 border-transparent hover:text-white': tab !== 'node' }" class="pb-2 border-b-2 font-medium transition-colors">Node.js</button>
            </div>
            
            <div x-show="tab === 'curl'" class="bg-black/50 rounded-lg p-4 border border-white/10 overflow-x-auto">
<pre class="text-sm text-gray-300 font-mono"><code>curl -X POST "https://rapid-indexer.com/api/v1/index.php?action=create_task" \
     -H "X-API-Key: <?php echo htmlspecialchars($user['api_key']); ?>" \
     -H "Content-Type: application/json" \
     -d '{
       "urls": ["https://example.com/page1"],
       "type": "indexer"
     }'</code></pre>
            </div>
            
            <div x-show="tab === 'php'" x-cloak class="bg-black/50 rounded-lg p-4 border border-white/10 overflow-x-auto">
<pre class="text-sm text-gray-300 font-mono"><code>$apiKey = '<?php echo htmlspecialchars($user['api_key']); ?>';
$url = 'https://rapid-indexer.com/api/v1/index.php?action=create_task';

$data = [
    'urls' => ['https://example.com/page1'],
    'type' => 'indexer'
];

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['X-API-Key: ' . $apiKey, 'Content-Type: application/json']);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($ch);
curl_close($ch);</code></pre>
            </div>

            <div x-show="tab === 'node'" x-cloak class="bg-black/50 rounded-lg p-4 border border-white/10 overflow-x-auto">
<pre class="text-sm text-gray-300 font-mono"><code>const axios = require('axios');

const apiKey = '<?php echo htmlspecialchars($user['api_key']); ?>';

axios.post('https://rapid-indexer.com/api/v1/index.php?action=create_task', {
    urls: ['https://example.com/page1'],
    type: 'indexer'
}, {
    headers: {
        'X-API-Key': apiKey
    }
}).then(response => {
    console.log(response.data);
});</code></pre>
            </div>
        </div>
    </div>
</div>

<script>
function copyApiKey() {
    const key = document.getElementById('apiKeyDisplay').innerText.trim();
    navigator.clipboard.writeText(key).then(() => {
        // Show a temporary success message or change icon
        const btn = document.querySelector('button[onclick="copyApiKey()"]');
        const originalIcon = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-check text-green-500"></i>';
        setTimeout(() => {
            btn.innerHTML = originalIcon;
        }, 2000);
    });
}
</script>

<?php include 'includes/footer_new.php'; ?>

