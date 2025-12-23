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
                <i class="fas fa-book text-blue-500"></i> Documentation
            </h3>
        </div>
        <div class="p-6">
            <p class="text-gray-300 mb-4">
                Rapid Indexer provides a simple REST API to create tasks, check status, and manage your account programmatically.
            </p>
            
            <div class="bg-black/20 rounded-lg p-4 mb-4 border border-white/5">
                <h4 class="text-white font-bold mb-2">Base URL</h4>
                <code class="text-primary-400 text-sm font-mono block">https://rapid-indexer.com/api/v1/index.php</code>
            </div>

            <h4 class="text-white font-bold mb-2">Example: Check Account Balance</h4>
            <div class="bg-[#111] rounded-lg p-4 border border-[#333] mb-6 overflow-x-auto">
<pre class="text-gray-300 font-mono text-xs">
curl -H "X-API-Key: YOUR_API_KEY" \
     https://rapid-indexer.com/api/v1/index.php?action=me
</pre>
            </div>

            <a href="/docs/USER_API_REFERENCE.md" target="_blank" class="text-primary-400 hover:text-primary-300 font-bold flex items-center gap-1">
                View Full Documentation <i class="fas fa-external-link-alt text-xs"></i>
            </a>
            <p class="text-xs text-gray-500 mt-1">(Link to markdown file, HTML docs coming soon)</p>
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

