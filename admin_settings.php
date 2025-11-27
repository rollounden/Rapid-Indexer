<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/config/config.php';
session_start();

// Check if user is logged in and is admin
if (!isset($_SESSION['uid']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header('Location: /login.php');
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

        if (isset($_POST['enable_vip_queue'])) {
            SettingsService::set('enable_vip_queue', $_POST['enable_vip_queue']);
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

// Cryptomus settings
$cryptomus_merchant_id_decrypted = SettingsService::getDecrypted('cryptomus_merchant_id', '');
$cryptomus_merchant_id_display = $cryptomus_merchant_id_decrypted ? substr($cryptomus_merchant_id_decrypted, 0, 4) . str_repeat('*', 20) . substr($cryptomus_merchant_id_decrypted, -4) : '';

$cryptomus_api_key_decrypted = SettingsService::getDecrypted('cryptomus_api_key', '');
$cryptomus_api_key_display = $cryptomus_api_key_decrypted ? substr($cryptomus_api_key_decrypted, 0, 4) . str_repeat('*', 20) . substr($cryptomus_api_key_decrypted, -4) : '';

$enable_paypal = SettingsService::get('enable_paypal', '1');
$enable_vip_queue = SettingsService::get('enable_vip_queue', '1');

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

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Settings - Rapid Indexer</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="/assets/css/style.css" rel="stylesheet">
</head>
<body>
    <?php include __DIR__ . '/includes/navbar.php'; ?>
    
    <div class="container mt-4">
        <div class="row">
            <div class="col-md-8 offset-md-2">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h1 class="h3">System Settings</h1>
                    <a href="/admin.php" class="btn btn-outline-secondary">Back to Dashboard</a>
                </div>
                
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>
                
                <?php if ($success): ?>
                    <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
                <?php endif; ?>
                
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Indexing Provider</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label">Default Provider for Indexing Tasks</label>
                                <select name="indexing_provider" class="form-select">
                                    <option value="speedyindex" <?php echo $current_provider === 'speedyindex' ? 'selected' : ''; ?>>SpeedyIndex (Default)</option>
                                    <option value="ralfy" <?php echo $current_provider === 'ralfy' ? 'selected' : ''; ?>>RalfyIndex</option>
                                </select>
                                <div class="form-text">
                                    Note: Checking tasks (Checkers) will always use SpeedyIndex regardless of this setting.
                                </div>
                            </div>

                            <hr>

                            <h6 class="mb-3">RalfyIndex Configuration</h6>
                            <div class="mb-3">
                                <label class="form-label">API Key</label>
                                <input type="text" name="ralfy_api_key" class="form-control" value="<?php echo htmlspecialchars($ralfy_api_key_display); ?>" placeholder="Enter new key to update">
                            </div>

                            <?php if ($ralfy_api_key_decrypted): ?>
                                <div class="alert alert-info">
                                    <strong>RalfyIndex Status:</strong>
                                    <?php 
                                    if ($ralfy_status && isset($ralfy_status['status']) && $ralfy_status['status'] === 'ok') {
                                        echo '<span class="badge bg-success">Connected</span>';
                                    } else {
                                        echo '<span class="badge bg-danger">Error</span>';
                                    }
                                    ?>
                                    <br>
                                    <strong>Balance:</strong> 
                                    <?php echo isset($ralfy_balance['balance']) ? number_format($ralfy_balance['balance']) . ' credits' : 'N/A'; ?>
                                </div>
                            <?php endif; ?>

                            <button type="submit" class="btn btn-primary">Save Settings</button>
                        </form>
                    </div>
                </div>
                
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Payment Providers</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <div class="mb-3 form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="enable_paypal" value="1" id="enablePaypal" <?php echo $enable_paypal === '1' ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="enablePaypal">Enable PayPal</label>
                                <input type="hidden" name="enable_paypal" value="0"> <!-- Fallback if unchecked -->
                            </div>
                            <div class="mb-3 form-check form-switch">
                                <input class="form-check-input" type="checkbox" checked disabled>
                                <label class="form-check-label">Enable Cryptomus (Always enabled if keys provided)</label>
                            </div>
                            <script>
                                // Ensure the fallback hidden input doesn't override if checked
                                document.getElementById('enablePaypal').addEventListener('change', function() {
                                    this.nextElementSibling.nextElementSibling.disabled = this.checked;
                                });
                                // Initial state
                                document.querySelector('input[type="hidden"][name="enable_paypal"]').disabled = document.getElementById('enablePaypal').checked;
                            </script>
                            <button type="submit" class="btn btn-primary">Save Payment Settings</button>
                        </form>
                    </div>
                </div>

                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Feature Settings</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <div class="mb-3 form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="enable_vip_queue" value="1" id="enableVip" <?php echo $enable_vip_queue === '1' ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="enableVip">Enable VIP Queue</label>
                                <input type="hidden" name="enable_vip_queue" value="0">
                            </div>
                            <script>
                                document.getElementById('enableVip').addEventListener('change', function() {
                                    this.nextElementSibling.nextElementSibling.disabled = this.checked;
                                });
                                document.querySelector('input[type="hidden"][name="enable_vip_queue"]').disabled = document.getElementById('enableVip').checked;
                            </script>
                            <button type="submit" class="btn btn-primary">Save Feature Settings</button>
                        </form>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">SpeedyIndex Configuration</h5>
                    </div>
                    <div class="card-body">
                        <p>SpeedyIndex is configured via <code>config/config.php</code> and environment variables.</p>
                        <div class="mb-3">
                            <label class="form-label">API URL</label>
                            <input type="text" class="form-control" value="<?php echo htmlspecialchars(SPEEDYINDEX_BASE_URL); ?>" readonly disabled>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">API Key</label>
                            <input type="password" class="form-control" value="************************" readonly disabled>
                            <div class="form-text">To change this, update the .env file on the server.</div>
                        </div>
                    </div>
                </div>

                <div class="card mt-4">
                    <div class="card-header">
                        <h5 class="mb-0">Cryptomus Settings</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label">Merchant ID</label>
                                <input type="text" name="cryptomus_merchant_id" class="form-control" value="<?php echo htmlspecialchars($cryptomus_merchant_id_display); ?>" placeholder="Enter new Merchant ID to update">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Payment Key</label>
                                <input type="text" name="cryptomus_api_key" class="form-control" value="<?php echo htmlspecialchars($cryptomus_api_key_display); ?>" placeholder="Enter new Payment Key to update">
                            </div>
                            <button type="submit" class="btn btn-primary">Save Crypto Settings</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
