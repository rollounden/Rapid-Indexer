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
            'task_name' => $_POST['task_name'] ?? '',
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

<div class="max-w-6xl mx-auto px-6 lg:px-8 py-12">
    
    <!-- Hero Section -->
    <div class="text-center mb-12">
        <div class="inline-flex items-center gap-2 bg-primary-600/10 border border-primary-600/20 rounded-full px-4 py-1 text-primary-400 text-sm font-bold uppercase mb-4">
            <i class="fas fa-bolt"></i> New Feature
        </div>
        <h1 class="text-4xl md:text-5xl font-bold text-white mb-4">Viral Website Traffic</h1>
        <p class="text-xl text-gray-400 max-w-2xl mx-auto">Simulate organic virality with our drip-feed traffic system. Boost your rankings, improve metrics, and signal authority to search engines.</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Benefits Column (Left) -->
        <div class="lg:col-span-1 space-y-6">
            <div class="card p-6 rounded-xl bg-white/5 border-white/5 hover:border-white/10 transition-all">
                <div class="w-12 h-12 bg-blue-500/10 rounded-lg flex items-center justify-center text-blue-400 mb-4">
                    <i class="fas fa-chart-line text-2xl"></i>
                </div>
                <h3 class="text-lg font-bold text-white mb-2">Simulate Virality</h3>
                <p class="text-gray-400 text-sm">Our "Viral Wave" algorithm distributes visitors in random bursts over time, mimicking real social media traction.</p>
            </div>

            <div class="card p-6 rounded-xl bg-white/5 border-white/5 hover:border-white/10 transition-all">
                <div class="w-12 h-12 bg-purple-500/10 rounded-lg flex items-center justify-center text-purple-400 mb-4">
                    <i class="fas fa-globe text-2xl"></i>
                </div>
                <h3 class="text-lg font-bold text-white mb-2">Geo-Targeted</h3>
                <p class="text-gray-400 text-sm">Target specific countries or regions to ensure your traffic signals are relevant to your SEO goals.</p>
            </div>

            <div class="card p-6 rounded-xl bg-white/5 border-white/5 hover:border-white/10 transition-all">
                <div class="w-12 h-12 bg-green-500/10 rounded-lg flex items-center justify-center text-green-400 mb-4">
                    <i class="fas fa-shield-alt text-2xl"></i>
                </div>
                <h3 class="text-lg font-bold text-white mb-2">Safe & Secure</h3>
                <p class="text-gray-400 text-sm">Traffic comes from real residential IPs and diverse device types, keeping your analytics looking natural.</p>
            </div>
        </div>

        <!-- Main Form (Center/Right) -->
        <div class="lg:col-span-2">
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

            <div class="card p-8 rounded-xl border-white/10 shadow-2xl relative overflow-hidden">
                <!-- Background decorative glow -->
                <div class="absolute top-0 right-0 w-64 h-64 bg-primary-600/5 rounded-full blur-3xl pointer-events-none"></div>

                <form method="POST" id="trafficForm" class="space-y-6 relative z-10">
                    
                    <!-- Task Name -->
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">Campaign Name <span class="text-gray-500">(Optional)</span></label>
                        <input type="text" name="task_name" placeholder="e.g. My Blog Viral Push" 
                            class="w-full bg-black/20 border border-white/10 rounded-lg px-4 py-3 text-white focus:border-primary-500 focus:ring-1 focus:ring-primary-500 transition-all">
                    </div>

                    <!-- Link -->
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">Target URL</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <span class="text-gray-500"><i class="fas fa-link"></i></span>
                            </div>
                            <input type="url" name="link" required placeholder="https://example.com" 
                                class="w-full bg-black/20 border border-white/10 rounded-lg pl-10 pr-4 py-3 text-white focus:border-primary-500 focus:ring-1 focus:ring-primary-500 transition-all">
                        </div>
                    </div>

                    <div class="h-px bg-white/5 my-6"></div>

                    <!-- Mode: Single vs Campaign -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-2">Campaign Mode</label>
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
                    <div id="quantityField" class="bg-white/5 p-4 rounded-lg border border-white/10">
                        <div class="flex justify-between items-center mb-2">
                            <label class="block text-sm font-medium text-white" id="qtyLabel">Total Visitors</label>
                            <span class="text-xs text-gray-400 bg-black/30 px-2 py-1 rounded">Min: 1000</span>
                        </div>
                        <input type="number" name="quantity" id="quantity" min="100" step="100" value="4000" required
                            class="w-full bg-black/20 border border-white/10 rounded-lg px-4 py-3 text-white text-lg font-bold focus:border-primary-500 focus:ring-1 focus:ring-primary-500 transition-all mb-2">
                        
                        <div class="flex items-center gap-2 text-xs text-gray-400" id="campaignInfo">
                            <i class="fas fa-info-circle text-primary-400"></i>
                            <span>Visitors distributed in random viral bursts over <span id="daysDisplay" class="text-white font-bold">3</span> days.</span>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Country -->
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-2">Target Country</label>
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

                    <div class="pt-6 border-t border-white/10 flex flex-col md:flex-row items-center justify-between gap-6">
                        <div class="text-center md:text-left">
                            <div class="text-sm text-gray-400 mb-1">Total Cost</div>
                            <div class="text-3xl font-bold text-white" id="priceDisplay">0</div>
                        </div>
                        
                        <button type="submit" class="w-full md:w-auto flex-1 bg-gradient-to-r from-primary-600 to-primary-500 hover:from-primary-500 hover:to-primary-400 text-white font-bold py-4 px-8 rounded-xl transition-all shadow-lg shadow-primary-900/20 transform hover:scale-[1.02] active:scale-[0.98]">
                            Launch Viral Campaign <i class="fas fa-rocket ml-2"></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>
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
    // Calculate cost for user
    const userCost = (cost * 0.01).toFixed(2);
    priceDisplay.innerHTML = cost + ' credits <span class="text-gray-400 ml-1">($' + userCost + ')</span>';
    
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

