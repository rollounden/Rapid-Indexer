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
            'days' => $_POST['days'] ?? 1,
            'mode' => $_POST['mode'] ?? 'single',
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
        <h1 class="text-3xl font-bold text-white">Viral Website Traffic</h1>
        <p class="text-gray-400 mt-1">Simulate virality and temporarily boost webpage rankings.</p>
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

            <!-- Mode: Single vs Campaign -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Mode</label>
                    <select name="mode" id="modeSelector" class="w-full bg-black/20 border border-white/10 rounded-lg px-4 py-3 text-white focus:border-primary-500 focus:ring-1 focus:ring-primary-500 transition-all">
                        <option value="campaign" selected>Viral Simulation (Drip-Feed)</option>
                        <option value="single">Quick Boost (24h Delivery)</option>
                    </select>
                </div>
                
                <div id="daysField">
                    <label class="block text-sm font-medium text-gray-300 mb-2">Duration (Days)</label>
                    <input type="number" name="days" id="days" min="1" max="30" value="3"
                           class="w-full bg-black/20 border border-white/10 rounded-lg px-4 py-3 text-white focus:border-primary-500 focus:ring-1 focus:ring-primary-500 transition-all">
                </div>
            </div>
            
            <!-- Quantity -->
            <div id="quantityField">
                <label class="block text-sm font-medium text-gray-300 mb-2" id="qtyLabel">Total Visitors (Min: 1000 Recommended)</label>
                <input type="number" name="quantity" id="quantity" min="100" step="100" value="4000" required
                       class="w-full bg-black/20 border border-white/10 rounded-lg px-4 py-3 text-white focus:border-primary-500 focus:ring-1 focus:ring-primary-500 transition-all">
                <p class="text-xs text-gray-500 mt-1">Estimated Price: <span id="priceDisplay">0</span> credits</p>
                <p class="text-xs text-gray-400 mt-1 hidden" id="campaignInfo">
                    <i class="fas fa-chart-line"></i> Visitors will be distributed in random viral bursts over <span id="daysDisplay">3</span> days to simulate real social engagement.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Country -->
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Country</label>
                    <select name="country" class="w-full bg-black/20 border border-white/10 rounded-lg px-4 py-3 text-white focus:border-primary-500 focus:ring-1 focus:ring-primary-500 transition-all">
                        <optgroup label="Continents">
                            <option value="WW">Worldwide</option>
                            <option value="NAM">North America (US, CA, MX)</option>
                            <option value="EUR">Europe (DE, UK, FR, IT)</option>
                            <option value="ASI">Asia (CN, IN, ID, JP)</option>
                            <option value="AFR">Africa (NG, EG, ZA)</option>
                            <option value="SAM">South America (BR, AR, VE)</option>
                            <option value="MEA">Middle East (TR, SA, AE)</option>
                        </optgroup>
                        
                        <optgroup label="North America">
                            <option value="US">United States</option>
                            <option value="CA">Canada</option>
                        </optgroup>

                        <optgroup label="Europe">
                            <option value="GB">United Kingdom</option>
                            <option value="DE">Germany</option>
                            <option value="FR">France</option>
                            <option value="ES">Spain</option>
                            <option value="IT">Italy</option>
                            <option value="NL">Netherlands</option>
                            <option value="SE">Sweden</option>
                            <option value="CH">Switzerland</option>
                            <option value="PL">Poland</option>
                            <option value="BE">Belgium</option>
                            <option value="AT">Austria</option>
                            <option value="CZ">Czech Republic</option>
                            <option value="DK">Denmark</option>
                            <option value="HU">Hungary</option>
                            <option value="LT">Lithuania</option>
                            <option value="RO">Romania</option>
                            <option value="RU">Russia</option>
                            <option value="RS">Serbia</option>
                            <option value="UA">Ukraine</option>
                        </optgroup>

                        <optgroup label="South America">
                            <option value="BR">Brazil</option>
                            <option value="AR">Argentina</option>
                            <option value="CL">Chile</option>
                        </optgroup>
                        
                        <optgroup label="Asia">
                            <option value="IN">India</option>
                            <option value="ID">Indonesia</option>
                            <option value="JP">Japan</option>
                            <option value="KR">South Korea</option>
                            <option value="HK">Hong Kong</option>
                            <option value="SG">Singapore</option>
                            <option value="TW">Taiwan</option>
                            <option value="TH">Thailand</option>
                            <option value="VN">Vietnam</option>
                            <option value="PK">Pakistan</option>
                            <option value="AE">United Arab Emirates</option>
                        </optgroup>

                        <optgroup label="Oceania">
                            <option value="AU">Australia</option>
                        </optgroup>

                        <optgroup label="Africa">
                            <option value="ZA">South Africa</option>
                        </optgroup>
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
                    <option value="2" selected>Social Media / Custom Referrer (Recommended for Virality)</option>
                    <option value="1">Google Keyword Search</option>
                    <option value="3">Direct / Blank Referrer</option>
                </select>
            </div>

            <!-- Conditional Fields -->
            <div id="googleKeywordField" class="hidden">
                <label class="block text-sm font-medium text-gray-300 mb-2">Google Keyword</label>
                <input type="text" name="google_keyword" placeholder="e.g. buy shoes online"
                       class="w-full bg-black/20 border border-white/10 rounded-lg px-4 py-3 text-white focus:border-primary-500 focus:ring-1 focus:ring-primary-500 transition-all">
            </div>

            <div id="referrerField">
                <label class="block text-sm font-medium text-gray-300 mb-2">Referring URL (e.g. a Backlink, Reddit Post, or Tweet)</label>
                <input type="url" name="referring_url" placeholder="https://twitter.com/user/status/123456789" value=""
                       class="w-full bg-black/20 border border-white/10 rounded-lg px-4 py-3 text-white focus:border-primary-500 focus:ring-1 focus:ring-primary-500 transition-all">
                <p class="text-xs text-gray-500 mt-1">We recommend using a specific backlink or social post URL instead of a root domain for better realism.</p>
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

const modeSelector = document.getElementById('modeSelector');
const daysField = document.getElementById('daysField');
const daysInput = document.getElementById('days');
const qtyLabel = document.getElementById('qtyLabel');
const campaignInfo = document.getElementById('campaignInfo');
const daysDisplay = document.getElementById('daysDisplay');

function updateUI() {
    const mode = modeSelector.value;
    const qty = parseInt(quantityInput.value) || 0;
    const days = parseInt(daysInput.value) || 1;
    
    const cost = Math.ceil((qty / 1000) * pricePer1000);
    priceDisplay.textContent = cost;
    
    if (mode === 'campaign') {
        daysField.classList.remove('hidden');
        qtyLabel.textContent = 'Total Visitors (Min: 1000 Recommended)';
        campaignInfo.classList.remove('hidden');
        daysDisplay.textContent = days;
    } else {
        daysField.classList.add('hidden');
        qtyLabel.textContent = 'Quantity (Min: 100)';
        campaignInfo.classList.add('hidden');
    }
}

quantityInput.addEventListener('input', updateUI);
daysInput.addEventListener('input', updateUI);
modeSelector.addEventListener('change', updateUI);

// Init
updateUI();

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

