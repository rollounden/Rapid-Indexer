<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/src/Db.php';
require_once __DIR__ . '/src/SettingsService.php';
require_once __DIR__ . '/src/JustAnotherPanelClient.php';
require_once __DIR__ . '/src/TrafficService.php';

// Prevent running from web unless strictly necessary (usually CLI only)
if (php_sapi_name() !== 'cli' && !isset($_GET['force'])) {
    die('Access denied');
}

echo "[" . date('Y-m-d H:i:s') . "] Starting Traffic Campaign processor...\n";

try {
    TrafficService::processScheduledRuns();
    echo "[" . date('Y-m-d H:i:s') . "] Completed run.\n";
} catch (Exception $e) {
    echo "[" . date('Y-m-d H:i:s') . "] Error: " . $e->getMessage() . "\n";
}

