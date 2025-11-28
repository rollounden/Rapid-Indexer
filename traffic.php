<?php
require_once __DIR__ . '/config/config.php';
session_start();

if (!isset($_SESSION['uid'])) {
    header('Location: /login.php');
    exit;
}

require_once __DIR__ . '/src/Db.php';
require_once __DIR__ . '/src/SettingsService.php';
require_once __DIR__ . '/src/CreditsService.php';
require_once __DIR__ . '/src/TrafficService.php';

$error = '';
$success = '';

$pricePer1000 = (int)SettingsService::get('traffic_price_per_1000', 30); // Credits

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $params = [
            'link' => $_POST['link'] ?? '',
            'quantity' => $_POST['quantity'] ?? 0,
            'country' => $_POST['country'] ?? 'WW',
            'device' => $_POST['device'] ?? '',
            'type_of_traffic' => $_POST['type_of_traffic'] ?? '',
            'google_keyword' => $_POST['google_keyword'] ?? '',
            'referring_url' => $_POST['referring_url'] ?? '',
        ];
        
        $result = TrafficService::createTrafficTask($_SESSION['uid'], $params);
        $success = 'Traffic order placed successfully! Task ID: ' . $result['task_id'];
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

include __DIR__ . '/includes/header_new.php';
?>

<div class="max-w-4xl mx-auto px-6 lg:px-8 py-10">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-white">Buy Website Traffic</h1>
        <p class="text-gray-400 mt-1">Boost your website rankings with real human traffic.</p>
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

    <div class="card p-8 rounded-xl">
        <form method="POST" id="trafficForm" class="space-y-6">
            
            <!-- Link -->
            <div>
                <label class="block text-sm font-medium text-gray-300 mb-2">Link to Page</label>
                <input type="url" name="link" required placeholder="https://example.com" 
                       class="w-full bg-black/20 border border-white/10 rounded-lg px-4 py-3 text-white focus:border-primary-500 focus:ring-1 focus:ring-primary-500 transition-all">
            </div>

            <!-- Quantity -->
            <div>
                <label class="block text-sm font-medium text-gray-300 mb-2">Quantity (Min: 100)</label>
                <input type="number" name="quantity" id="quantity" min="100" step="100" required
                       class="w-full bg-black/20 border border-white/10 rounded-lg px-4 py-3 text-white focus:border-primary-500 focus:ring-1 focus:ring-primary-500 transition-all">
                <p class="text-xs text-gray-500 mt-1">Price: <span id="priceDisplay">0</span> credits</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Country -->
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Country</label>
                    <select name="country" class="w-full bg-black/20 border border-white/10 rounded-lg px-4 py-3 text-white focus:border-primary-500 focus:ring-1 focus:ring-primary-500 transition-all">
                        <option value="WW">Worldwide</option>
                        <option value="US">United States</option>
                        <option value="CA">Canada</option>
                        <option value="GB">United Kingdom</option>
                        <option value="DE">Germany</option>
                        <option value="FR">France</option>
                        <option value="BR">Brazil</option>
                        <option value="IN">India</option>
                        <!-- Add more countries as needed -->
                    </select>
                </div>

                <!-- Device -->
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Device</label>
                    <select name="device" class="w-full bg-black/20 border border-white/10 rounded-lg px-4 py-3 text-white focus:border-primary-500 focus:ring-1 focus:ring-primary-500 transition-all">
                        <option value="5">Mixed (Mobile & Desktop)</option>
                        <option value="1">Desktop</option>
                        <option value="4">Mixed (Mobile)</option>
                        <option value="2">Mobile (Android)</option>
                        <option value="3">Mobile (iOS)</option>
                    </select>
                </div>
            </div>

            <!-- Traffic Type -->
            <div>
                <label class="block text-sm font-medium text-gray-300 mb-2">Type of Traffic</label>
                <select name="type_of_traffic" id="trafficType" class="w-full bg-black/20 border border-white/10 rounded-lg px-4 py-3 text-white focus:border-primary-500 focus:ring-1 focus:ring-primary-500 transition-all">
                    <option value="3">Blank Referrer</option>
                    <option value="1">Google Keyword</option>
                    <option value="2">Custom Referrer</option>
                </select>
            </div>

            <!-- Conditional Fields -->
            <div id="googleKeywordField" class="hidden">
                <label class="block text-sm font-medium text-gray-300 mb-2">Google Keyword</label>
                <input type="text" name="google_keyword" placeholder="e.g. buy shoes online"
                       class="w-full bg-black/20 border border-white/10 rounded-lg px-4 py-3 text-white focus:border-primary-500 focus:ring-1 focus:ring-primary-500 transition-all">
            </div>

            <div id="referrerField" class="hidden">
                <label class="block text-sm font-medium text-gray-300 mb-2">Referring URL</label>
                <input type="url" name="referring_url" placeholder="https://facebook.com"
                       class="w-full bg-black/20 border border-white/10 rounded-lg px-4 py-3 text-white focus:border-primary-500 focus:ring-1 focus:ring-primary-500 transition-all">
            </div>

            <div class="pt-4">
                <button type="submit" class="w-full bg-primary-600 hover:bg-primary-700 text-white font-bold py-4 px-6 rounded-lg transition-all shadow-lg shadow-primary-900/20">
                    Place Order
                </button>
            </div>
        </form>
    </div>
</div>

<script>
const quantityInput = document.getElementById('quantity');
const priceDisplay = document.getElementById('priceDisplay');
const pricePer1000 = <?php echo $pricePer1000; ?>;

quantityInput.addEventListener('input', function() {
    const qty = parseInt(this.value) || 0;
    const cost = Math.ceil((qty / 1000) * pricePer1000);
    priceDisplay.textContent = cost;
});

const trafficType = document.getElementById('trafficType');
const googleKeywordField = document.getElementById('googleKeywordField');
const referrerField = document.getElementById('referrerField');

trafficType.addEventListener('change', function() {
    const val = this.value;
    googleKeywordField.classList.add('hidden');
    referrerField.classList.add('hidden');
    
    if (val === '1') {
        googleKeywordField.classList.remove('hidden');
    } else if (val === '2') {
        referrerField.classList.remove('hidden');
    }
});
</script>

<?php include __DIR__ . '/includes/footer_new.php'; ?>

