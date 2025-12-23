<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/config/config.php';
if (session_status() === PHP_SESSION_NONE) { session_start(); }

// Check if user is logged in and is admin
if (!isset($_SESSION['uid']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header('Location: /login');
    exit;
}

require_once __DIR__ . '/src/Db.php';
require_once __DIR__ . '/src/SettingsService.php';
require_once __DIR__ . '/src/RalfyIndexClient.php';

$error = '';
$success = '';
$ralfy_status = null;
$ralfy_balance = null;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (isset($_POST['indexing_provider'])) {
            $provider = $_POST['indexing_provider'];
            if (!in_array($provider, ['speedyindex', 'ralfy'])) {
                throw new Exception('Invalid provider selected');
            }
            SettingsService::set('indexing_provider', $provider);
        }

        if (isset($_POST['ralfy_api_key'])) {
            $key = trim($_POST['ralfy_api_key']);
            // Only update if not empty (to allow keeping existing masked value)
            if (!empty($key) && strpos($key, '*') === false) {
                SettingsService::setEncrypted('ralfy_api_key', $key);
            }
        }

        if (isset($_POST['rocket_api_key'])) {
            $key = trim($_POST['rocket_api_key']);
            if (!empty($key) && strpos($key, '*') === false) {
                SettingsService::setEncrypted('rocket_api_key', $key);
            }
        }
        
        if (isset($_POST['use_rocket_for_vip'])) {
            SettingsService::set('use_rocket_for_vip', $_POST['use_rocket_for_vip']);
        }

        if (isset($_POST['cryptomus_merchant_id'])) {
            $merchantId = trim($_POST['cryptomus_merchant_id']);
            $apiKey = trim($_POST['cryptomus_api_key']);
            
            if (!empty($merchantId) && strpos($merchantId, '*') === false) {
                SettingsService::setEncrypted('cryptomus_merchant_id', $merchantId);
            }
            
            if (!empty($apiKey) && strpos($apiKey, '*') === false) {
                SettingsService::setEncrypted('cryptomus_api_key', $apiKey);
            }
        }

        if (isset($_POST['enable_paypal'])) {
            SettingsService::set('enable_paypal', $_POST['enable_paypal']);
        }

        if (isset($_POST['enable_cryptomus'])) {
            SettingsService::set('enable_cryptomus', $_POST['enable_cryptomus']);
        }

        if (isset($_POST['enable_vip_queue'])) {
            SettingsService::set('enable_vip_queue', $_POST['enable_vip_queue']);
        }

        if (isset($_POST['vip_require_payment'])) {
            SettingsService::set('vip_require_payment', $_POST['vip_require_payment']);
        }

        if (isset($_POST['free_credits_on_signup'])) {
            SettingsService::set('free_credits_on_signup', intval($_POST['free_credits_on_signup']));
        }

        if (isset($_POST['price_per_credit'])) {
            SettingsService::set('price_per_credit', $_POST['price_per_credit']);
        }
        if (isset($_POST['cost_indexing'])) {
            SettingsService::set('cost_indexing', $_POST['cost_indexing']);
        }
        if (isset($_POST['cost_checking'])) {
            SettingsService::set('cost_checking', $_POST['cost_checking']);
        }
        if (isset($_POST['cost_vip'])) {
            SettingsService::set('cost_vip', $_POST['cost_vip']);
        }
        if (isset($_POST['traffic_price_per_1000'])) {
            SettingsService::set('traffic_price_per_1000', $_POST['traffic_price_per_1000']);
        }

        if (isset($_POST['jap_api_key'])) {
            $key = trim($_POST['jap_api_key']);
            if (!empty($key) && strpos($key, '*') === false) {
                SettingsService::setEncrypted('jap_api_key', $key);
            }
        }

        if (isset($_POST['traffic_service_id'])) {
            SettingsService::set('traffic_service_id', trim($_POST['traffic_service_id']));
        }

        $success = 'Settings saved successfully.';
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

// Load current settings
$current_provider = SettingsService::get('indexing_provider', 'speedyindex');
$ralfy_api_key_decrypted = SettingsService::getDecrypted('ralfy_api_key', '');
// Mask the key for display if it exists
$ralfy_api_key_display = $ralfy_api_key_decrypted ? substr($ralfy_api_key_decrypted, 0, 4) . str_repeat('*', 20) . substr($ralfy_api_key_decrypted, -4) : '';

$rocket_api_key_decrypted = SettingsService::getDecrypted('rocket_api_key', '');
$rocket_api_key_display = $rocket_api_key_decrypted ? substr($rocket_api_key_decrypted, 0, 4) . str_repeat('*', 20) . substr($rocket_api_key_decrypted, -4) : '';
$use_rocket_for_vip = SettingsService::get('use_rocket_for_vip', '0');

// Cryptomus settings
$cryptomus_merchant_id_decrypted = SettingsService::getDecrypted('cryptomus_merchant_id', '');
$cryptomus_merchant_id_display = $cryptomus_merchant_id_decrypted ? substr($cryptomus_merchant_id_decrypted, 0, 4) . str_repeat('*', 20) . substr($cryptomus_merchant_id_decrypted, -4) : '';

$cryptomus_api_key_decrypted = SettingsService::getDecrypted('cryptomus_api_key', '');
$cryptomus_api_key_display = $cryptomus_api_key_decrypted ? substr($cryptomus_api_key_decrypted, 0, 4) . str_repeat('*', 20) . substr($cryptomus_api_key_decrypted, -4) : '';

$enable_paypal = SettingsService::get('enable_paypal', '1');
$enable_cryptomus = SettingsService::get('enable_cryptomus', '1');
$enable_vip_queue = SettingsService::get('enable_vip_queue', '1');
$vip_require_payment = SettingsService::get('vip_require_payment', '1');
$free_credits_on_signup = SettingsService::get('free_credits_on_signup', '30');

// Pricing Settings
$price_per_credit = SettingsService::get('price_per_credit', (string)PRICE_PER_CREDIT_USD);
$cost_indexing = SettingsService::get('cost_indexing', (string)COST_INDEXING);
$cost_checking = SettingsService::get('cost_checking', (string)COST_CHECKING);
$cost_vip = SettingsService::get('cost_vip', (string)COST_VIP_EXTRA);
$traffic_price_per_1000 = SettingsService::get('traffic_price_per_1000', '60');

$jap_api_key_decrypted = SettingsService::getDecrypted('jap_api_key', '');
$jap_api_key_display = $jap_api_key_decrypted ? substr($jap_api_key_decrypted, 0, 4) . str_repeat('*', 20) . substr($jap_api_key_decrypted, -4) : '';
$traffic_service_id = SettingsService::get('traffic_service_id', '9184');

// Check Ralfy Status if key is present
if ($ralfy_api_key_decrypted) {
    try {
        $client = new RalfyIndexClient($ralfy_api_key_decrypted);
        $status = $client->getStatus();
        $balance = $client->getBalance();
        
        $ralfy_status = json_decode($status['body'] ?? '', true);
        $ralfy_balance = json_decode($balance['body'] ?? '', true);
    } catch (Exception $e) {
        // Ignore connection errors for display
    }
}

// Check RocketIndexer Status if key is present
$rocket_balance_data = null;
if ($rocket_api_key_decrypted) {
    try {
        require_once __DIR__ . '/src/RocketIndexerClient.php';
        $rocketClient = new RocketIndexerClient($rocket_api_key_decrypted);
        $rBalance = $rocketClient->getBalance();
        $rocket_balance_data = json_decode($rBalance['body'] ?? '', true);
    } catch (Exception $e) {
        // Ignore
    }
}

include __DIR__ . '/includes/header_new.php';
?>

<div class="max-w-3xl mx-auto px-6 lg:px-8 py-10">
    <div class="flex flex-col md:flex-row justify-between items-center mb-8 gap-4">
        <div>
            <h1 class="text-3xl font-bold text-white">System Settings</h1>
            <nav class="flex items-center text-sm text-gray-400 mt-1">
                <a href="/admin" class="hover:text-white transition-colors">Dashboard</a>
                <span class="mx-2">/</span>
                <span class="text-white">Settings</span>
            </nav>
        </div>
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
    
    <div class="space-y-8">
        <!-- Indexing Provider -->
        <div class="card rounded-xl p-6">
            <h3 class="text-xl font-bold text-white mb-6 border-b border-white/5 pb-4">Indexing Provider</h3>
            <form method="POST">
                <div class="mb-6">
                    <label class="block text-xs font-bold text-gray-400 uppercase mb-2">Default Provider for Indexing Tasks</label>
                    <select name="indexing_provider" class="w-full bg-[#111] border border-[#333] rounded-lg p-3 text-white focus:border-primary-500 focus:ring-1 focus:ring-primary-500 transition-colors">
                        <option value="speedyindex" <?php echo $current_provider === 'speedyindex' ? 'selected' : ''; ?>>SpeedyIndex (Default)</option>
                        <option value="ralfy" <?php echo $current_provider === 'ralfy' ? 'selected' : ''; ?>>RalfyIndex</option>
                    </select>
                    <p class="text-xs text-gray-500 mt-2">Note: Checking tasks (Checkers) will always use SpeedyIndex regardless of this setting.</p>
                </div>

                <div class="border-t border-white/5 pt-6 mb-6">
                    <h4 class="text-lg font-bold text-white mb-4">RalfyIndex Configuration</h4>
                    <div class="mb-4">
                        <label class="block text-xs font-bold text-gray-400 uppercase mb-2">API Key</label>
                        <input type="text" name="ralfy_api_key" class="w-full bg-[#111] border border-[#333] rounded-lg p-3 text-white focus:border-primary-500 focus:ring-1 focus:ring-primary-500 transition-colors" value="<?php echo htmlspecialchars($ralfy_api_key_display); ?>" placeholder="Enter new key to update">
                    </div>
                    
                    <?php if ($ralfy_api_key_decrypted): ?>
                        <div class="bg-blue-500/10 border border-blue-500/20 rounded-lg p-4 text-sm">
                            <div class="flex items-center gap-2 mb-2">
                                <strong class="text-blue-400">RalfyIndex Status:</strong>
                                <?php 
                                if ($ralfy_status && isset($ralfy_status['status']) && $ralfy_status['status'] === 'ok') {
                                    echo '<span class="text-xs font-bold px-2 py-0.5 rounded bg-green-500/20 text-green-400 border border-green-500/20">Connected</span>';
                                } else {
                                    echo '<span class="text-xs font-bold px-2 py-0.5 rounded bg-red-500/20 text-red-400 border border-red-500/20">Error</span>';
                                }
                                ?>
                            </div>
                            <div class="flex items-center gap-2">
                                <strong class="text-blue-400">Balance:</strong> 
                                <span class="text-white"><?php echo isset($ralfy_balance['balance']) ? number_format($ralfy_balance['balance']) . ' credits' : 'N/A'; ?></span>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- RocketIndexer Configuration -->
                <div class="border-t border-white/5 pt-6 mb-6">
                    <h4 class="text-lg font-bold text-white mb-4">RocketIndexer Configuration (VIP)</h4>
                    
                    <div class="mb-4 flex items-center justify-between">
                        <div>
                            <label class="text-white font-bold block">Use for VIP Queue</label>
                            <p class="text-xs text-gray-400">Enable RocketIndexer for all VIP tasks</p>
                        </div>
                        <div class="relative inline-block w-12 mr-2 align-middle select-none transition duration-200 ease-in">
                            <input type="checkbox" name="use_rocket_for_vip" id="useRocketVip" value="1" <?php echo $use_rocket_for_vip === '1' ? 'checked' : ''; ?> class="toggle-checkbox absolute block w-6 h-6 rounded-full bg-white border-4 appearance-none cursor-pointer"/>
                            <label for="useRocketVip" class="toggle-label block overflow-hidden h-6 rounded-full bg-gray-700 cursor-pointer"></label>
                        </div>
                        <input type="hidden" name="use_rocket_for_vip" value="0" id="hiddenRocketVip">
                    </div>

                    <div class="mb-4">
                        <label class="block text-xs font-bold text-gray-400 uppercase mb-2">RocketIndexer API Key</label>
                        <input type="text" name="rocket_api_key" class="w-full bg-[#111] border border-[#333] rounded-lg p-3 text-white focus:border-primary-500 focus:ring-1 focus:ring-primary-500 transition-colors" value="<?php echo htmlspecialchars($rocket_api_key_display); ?>" placeholder="Enter new key to update">
                    </div>
                    
                    <?php if ($rocket_api_key_decrypted): ?>
                        <div class="bg-purple-500/10 border border-purple-500/20 rounded-lg p-4 text-sm">
                            <div class="flex items-center gap-2 mb-2">
                                <strong class="text-purple-400">RocketIndexer Status:</strong>
                                <?php 
                                if ($rocket_balance_data && isset($rocket_balance_data['success']) && $rocket_balance_data['success']) {
                                    echo '<span class="text-xs font-bold px-2 py-0.5 rounded bg-green-500/20 text-green-400 border border-green-500/20">Connected</span>';
                                } else {
                                    echo '<span class="text-xs font-bold px-2 py-0.5 rounded bg-red-500/20 text-red-400 border border-red-500/20">Error</span>';
                                }
                                ?>
                            </div>
                            <div class="flex items-center gap-2">
                                <strong class="text-purple-400">Balance:</strong> 
                                <span class="text-white"><?php echo isset($rocket_balance_data['data']['credits']['available']) ? number_format($rocket_balance_data['data']['credits']['available']) . ' credits' : 'N/A'; ?></span>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

                <script>
                    const rocketToggle = document.getElementById('useRocketVip');
                    const rocketHidden = document.getElementById('hiddenRocketVip');
                    
                    function updateRocketHidden() {
                        rocketHidden.disabled = rocketToggle.checked;
                    }
                    
                    rocketToggle.addEventListener('change', updateRocketHidden);
                    updateRocketHidden();
                </script>

                <button type="submit" class="px-6 py-2 rounded-lg bg-primary-600 text-white hover:bg-primary-700 transition-colors font-bold">Save Settings</button>
            </form>
        </div>
        
        <!-- Payment Providers -->
        <div class="card rounded-xl p-6">
            <h3 class="text-xl font-bold text-white mb-6 border-b border-white/5 pb-4">Payment Providers</h3>
            <form method="POST">
                <div class="mb-6 flex items-center justify-between">
                    <div>
                        <label class="text-white font-bold block">Enable PayPal</label>
                        <p class="text-xs text-gray-400">Allow users to pay with PayPal</p>
                    </div>
                    <div class="relative inline-block w-12 mr-2 align-middle select-none transition duration-200 ease-in">
                        <input type="checkbox" name="enable_paypal" id="enablePaypal" value="1" <?php echo $enable_paypal === '1' ? 'checked' : ''; ?> class="toggle-checkbox absolute block w-6 h-6 rounded-full bg-white border-4 appearance-none cursor-pointer"/>
                        <label for="enablePaypal" class="toggle-label block overflow-hidden h-6 rounded-full bg-gray-700 cursor-pointer"></label>
                    </div>
                    <input type="hidden" name="enable_paypal" value="0" id="hiddenPaypal"> 
                </div>
                
                <div class="mb-6 flex items-center justify-between">
                    <div>
                        <label class="text-white font-bold block">Enable Cryptomus</label>
                        <p class="text-xs text-gray-400">Allow users to pay with Cryptocurrency</p>
                    </div>
                    <div class="relative inline-block w-12 mr-2 align-middle select-none transition duration-200 ease-in">
                        <input type="checkbox" name="enable_cryptomus" id="enableCryptomus" value="1" <?php echo $enable_cryptomus === '1' ? 'checked' : ''; ?> class="toggle-checkbox absolute block w-6 h-6 rounded-full bg-white border-4 appearance-none cursor-pointer"/>
                        <label for="enableCryptomus" class="toggle-label block overflow-hidden h-6 rounded-full bg-gray-700 cursor-pointer"></label>
                    </div>
                    <input type="hidden" name="enable_cryptomus" value="0" id="hiddenCryptomus">
                </div>
                
                <script>
                    // Ensure checkbox behavior works for hidden field fallback logic
                    const toggle = document.getElementById('enablePaypal');
                    const hidden = document.getElementById('hiddenPaypal');
                    const toggleCrypto = document.getElementById('enableCryptomus');
                    const hiddenCrypto = document.getElementById('hiddenCryptomus');
                    
                    function updateHidden() {
                        hidden.disabled = toggle.checked;
                    }

                    function updateHiddenCrypto() {
                        hiddenCrypto.disabled = toggleCrypto.checked;
                    }
                    
                    toggle.addEventListener('change', updateHidden);
                    toggleCrypto.addEventListener('change', updateHiddenCrypto);
                    updateHidden(); // Init
                    updateHiddenCrypto(); // Init
                </script>
                <style>
                    .toggle-checkbox:checked { right: 0; border-color: #be123c; }
                    .toggle-checkbox:checked + .toggle-label { background-color: #be123c; }
                    .toggle-checkbox { right: auto; left: 0; border-color: #4b5563; transition: all 0.3s; top: 0; }
                    .toggle-label { width: 3rem; }
                    .relative { position: relative; height: 1.5rem; width: 3rem; }
                </style>

                <button type="submit" class="px-6 py-2 rounded-lg bg-primary-600 text-white hover:bg-primary-700 transition-colors font-bold">Save Payment Settings</button>
            </form>
        </div>

        <!-- Feature Settings -->
        <div class="card rounded-xl p-6">
            <h3 class="text-xl font-bold text-white mb-6 border-b border-white/5 pb-4">Feature Settings</h3>
            <form method="POST">
                <div class="mb-6 flex items-center justify-between">
                    <div>
                        <label class="text-white font-bold block">Enable VIP Queue</label>
                        <p class="text-xs text-gray-400">Allow users to pay extra for priority processing</p>
                    </div>
                    <div class="relative inline-block w-12 mr-2 align-middle select-none transition duration-200 ease-in">
                        <input type="checkbox" name="enable_vip_queue" id="enableVip" value="1" <?php echo $enable_vip_queue === '1' ? 'checked' : ''; ?> class="toggle-checkbox absolute block w-6 h-6 rounded-full bg-white border-4 appearance-none cursor-pointer"/>
                        <label for="enableVip" class="toggle-label block overflow-hidden h-6 rounded-full bg-gray-700 cursor-pointer"></label>
                    </div>
                    <input type="hidden" name="enable_vip_queue" value="0" id="hiddenVip">
                </div>
                
                <div class="mb-6 flex items-center justify-between">
                    <div>
                        <label class="text-white font-bold block">Require Payment for VIP</label>
                        <p class="text-xs text-gray-400">Only allow users who have spent money to use VIP Queue</p>
                    </div>
                    <div class="relative inline-block w-12 mr-2 align-middle select-none transition duration-200 ease-in">
                        <input type="checkbox" name="vip_require_payment" id="vipPayment" value="1" <?php echo $vip_require_payment === '1' ? 'checked' : ''; ?> class="toggle-checkbox absolute block w-6 h-6 rounded-full bg-white border-4 appearance-none cursor-pointer"/>
                        <label for="vipPayment" class="toggle-label block overflow-hidden h-6 rounded-full bg-gray-700 cursor-pointer"></label>
                    </div>
                    <input type="hidden" name="vip_require_payment" value="0" id="hiddenVipPayment">
                </div>
                
                <div class="mb-6">
                    <label class="block text-xs font-bold text-gray-400 uppercase mb-2">Free Credits on Signup</label>
                    <input type="number" name="free_credits_on_signup" class="w-full bg-[#111] border border-[#333] rounded-lg p-3 text-white focus:border-primary-500 focus:ring-1 focus:ring-primary-500 transition-colors" value="<?php echo htmlspecialchars($free_credits_on_signup); ?>" min="0">
                </div>
                
                <script>
                    const vipToggle = document.getElementById('enableVip');
                    const vipHidden = document.getElementById('hiddenVip');
                    const vipPayToggle = document.getElementById('vipPayment');
                    const vipPayHidden = document.getElementById('hiddenVipPayment');
                    
                    function updateVipHidden() {
                        vipHidden.disabled = vipToggle.checked;
                        vipPayHidden.disabled = vipPayToggle.checked;
                    }
                    
                    vipToggle.addEventListener('change', updateVipHidden);
                    vipPayToggle.addEventListener('change', updateVipHidden);
                    updateVipHidden();
                </script>

                <button type="submit" class="px-6 py-2 rounded-lg bg-primary-600 text-white hover:bg-primary-700 transition-colors font-bold">Save Feature Settings</button>
            </form>
        </div>

        <!-- Pricing Settings -->
        <div class="card rounded-xl p-6">
            <h3 class="text-xl font-bold text-white mb-6 border-b border-white/5 pb-4">Pricing Settings</h3>
            <form method="POST">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <label class="block text-xs font-bold text-gray-400 uppercase mb-2">Price Per Credit (USD)</label>
                        <input type="number" step="0.0001" name="price_per_credit" class="w-full bg-[#111] border border-[#333] rounded-lg p-3 text-white focus:border-primary-500 focus:ring-1 focus:ring-primary-500 transition-colors" value="<?php echo htmlspecialchars($price_per_credit); ?>" required>
                        <p class="text-xs text-gray-500 mt-2">Amount to charge per 1 credit</p>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-400 uppercase mb-2">Cost per Indexing Task (Credits)</label>
                        <input type="number" step="1" name="cost_indexing" class="w-full bg-[#111] border border-[#333] rounded-lg p-3 text-white focus:border-primary-500 focus:ring-1 focus:ring-primary-500 transition-colors" value="<?php echo htmlspecialchars($cost_indexing); ?>" required>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-400 uppercase mb-2">Cost per Checking Task (Credits)</label>
                        <input type="number" step="1" name="cost_checking" class="w-full bg-[#111] border border-[#333] rounded-lg p-3 text-white focus:border-primary-500 focus:ring-1 focus:ring-primary-500 transition-colors" value="<?php echo htmlspecialchars($cost_checking); ?>" required>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-400 uppercase mb-2">VIP Extra Cost (Credits)</label>
                        <input type="number" step="1" name="cost_vip" class="w-full bg-[#111] border border-[#333] rounded-lg p-3 text-white focus:border-primary-500 focus:ring-1 focus:ring-primary-500 transition-colors" value="<?php echo htmlspecialchars($cost_vip); ?>" required>
                        <p class="text-xs text-gray-500 mt-2">Additional credits for VIP processing</p>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-400 uppercase mb-2">Traffic Price per 1000 (Credits)</label>
                        <input type="number" step="1" name="traffic_price_per_1000" class="w-full bg-[#111] border border-[#333] rounded-lg p-3 text-white focus:border-primary-500 focus:ring-1 focus:ring-primary-500 transition-colors" value="<?php echo htmlspecialchars($traffic_price_per_1000); ?>" required>
                    </div>
                </div>
                <button type="submit" class="px-6 py-2 rounded-lg bg-primary-600 text-white hover:bg-primary-700 transition-colors font-bold">Save Pricing Settings</button>
            </form>
        </div>

        <!-- Traffic Settings -->
        <div class="card rounded-xl p-6">
            <h3 class="text-xl font-bold text-white mb-6 border-b border-white/5 pb-4">Traffic Service Settings</h3>
            <form method="POST">
                <div class="mb-6">
                    <label class="block text-xs font-bold text-gray-400 uppercase mb-2">JustAnotherPanel API Key</label>
                    <input type="text" name="jap_api_key" class="w-full bg-[#111] border border-[#333] rounded-lg p-3 text-white focus:border-primary-500 focus:ring-1 focus:ring-primary-500 transition-colors" value="<?php echo htmlspecialchars($jap_api_key_display); ?>" placeholder="Enter new API Key">
                </div>
                <button type="submit" class="px-6 py-2 rounded-lg bg-primary-600 text-white hover:bg-primary-700 transition-colors font-bold">Save Traffic Settings</button>
            </form>
        </div>

        <!-- SpeedyIndex Config -->
        <div class="card rounded-xl p-6">
            <h3 class="text-xl font-bold text-white mb-6 border-b border-white/5 pb-4">SpeedyIndex Configuration</h3>
            <p class="text-sm text-gray-400 mb-4">SpeedyIndex is configured via <code>config/config.php</code> and environment variables.</p>
            
            <div class="mb-4">
                <label class="block text-xs font-bold text-gray-400 uppercase mb-2">API URL</label>
                <input type="text" class="w-full bg-black/20 border border-[#333] rounded-lg p-3 text-gray-400 cursor-not-allowed" value="<?php echo htmlspecialchars(SPEEDYINDEX_BASE_URL); ?>" readonly disabled>
            </div>
            <div class="mb-4">
                <label class="block text-xs font-bold text-gray-400 uppercase mb-2">API Key</label>
                <input type="password" class="w-full bg-black/20 border border-[#333] rounded-lg p-3 text-gray-400 cursor-not-allowed" value="************************" readonly disabled>
                <p class="text-xs text-gray-500 mt-2">To change this, update the .env file on the server.</p>
            </div>
        </div>

        <!-- Cryptomus Settings -->
        <div class="card rounded-xl p-6">
            <h3 class="text-xl font-bold text-white mb-6 border-b border-white/5 pb-4">Cryptomus Settings</h3>
            <form method="POST">
                <div class="mb-4">
                    <label class="block text-xs font-bold text-gray-400 uppercase mb-2">Merchant ID</label>
                    <input type="text" name="cryptomus_merchant_id" class="w-full bg-[#111] border border-[#333] rounded-lg p-3 text-white focus:border-primary-500 focus:ring-1 focus:ring-primary-500 transition-colors" value="<?php echo htmlspecialchars($cryptomus_merchant_id_display); ?>" placeholder="Enter new Merchant ID to update">
                </div>
                <div class="mb-6">
                    <label class="block text-xs font-bold text-gray-400 uppercase mb-2">Payment Key</label>
                    <input type="text" name="cryptomus_api_key" class="w-full bg-[#111] border border-[#333] rounded-lg p-3 text-white focus:border-primary-500 focus:ring-1 focus:ring-primary-500 transition-colors" value="<?php echo htmlspecialchars($cryptomus_api_key_display); ?>" placeholder="Enter new Payment Key to update">
                </div>
                <button type="submit" class="px-6 py-2 rounded-lg bg-primary-600 text-white hover:bg-primary-700 transition-colors font-bold">Save Crypto Settings</button>
            </form>
        </div>
    </div>
</div>

<?php include __DIR__ . '/includes/footer_new.php'; ?>
