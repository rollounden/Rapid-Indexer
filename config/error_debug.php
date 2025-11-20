<?php
// ... existing code ...
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
// Ensure log directory exists
if (!is_dir(__DIR__ . '/../storage/logs')) {
    mkdir(__DIR__ . '/../storage/logs', 0777, true);
}
ini_set('error_log', __DIR__ . '/../storage/logs/php_errors.log');
// ... existing code ...

