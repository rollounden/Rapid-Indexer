<?php
// Force error display immediately
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "1. Starting debug...\n";

try {
    echo "2. Loading config/config.php...\n";
    require_once __DIR__ . '/config/config.php';
    echo "   Config loaded.\n";
} catch (Throwable $e) {
    echo "!!! Error loading config: " . $e->getMessage() . "\n";
}

try {
    echo "3. Loading src/Db.php...\n";
    require_once __DIR__ . '/src/Db.php';
    echo "   Db.php loaded.\n";
} catch (Throwable $e) {
    echo "!!! Error loading Db.php: " . $e->getMessage() . "\n";
}

try {
    echo "4. Loading src/SettingsService.php...\n";
    require_once __DIR__ . '/src/SettingsService.php';
    echo "   SettingsService.php loaded.\n";
} catch (Throwable $e) {
    echo "!!! Error loading SettingsService.php: " . $e->getMessage() . "\n";
}

try {
    echo "5. Testing DB Connection...\n";
    $pdo = Db::conn();
    echo "   DB Connected.\n";
} catch (Throwable $e) {
    echo "!!! Error connecting to DB: " . $e->getMessage() . "\n";
}

try {
    echo "6. Testing SettingsService...\n";
    $val = SettingsService::get('enable_paypal', 'default');
    echo "   SettingsService fetched: $val\n";
} catch (Throwable $e) {
    echo "!!! Error calling SettingsService: " . $e->getMessage() . "\n";
}

echo "7. Loading includes/header_new.php (simulated)...\n";
// We won't actually include it because it produces HTML output and might rely on session variables which we haven't set fully.
// But we can check for syntax errors by linting it.

echo "Debug complete.\n";

