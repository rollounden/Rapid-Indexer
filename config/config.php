<?php
// Production config for Indexing SaaS

// Set Timezone to UTC for consistency
date_default_timezone_set('UTC');

// Set up production error handling
// Temporarily enabling display_errors for debugging if requested
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
ini_set('log_errors', 1); // Log errors to file
ini_set('error_log', __DIR__ . '/../storage/logs/php_errors.log');

// Load environment variables from .env file
if (file_exists(__DIR__ . '/../.env')) {
    $lines = @file(__DIR__ . '/../.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if ($lines !== false) {
        foreach ($lines as $line) {
            if (strpos($line, '=') !== false && strpos($line, '#') !== 0) {
                list($key, $value) = explode('=', $line, 2);
                $_ENV[trim($key)] = trim($value);
            }
        }
    }
}

// API base
const SPEEDYINDEX_BASE_URL = 'https://api.speedyindex.com';

// API Key - Required from environment
if (!isset($_ENV['SPEEDYINDEX_API_KEY'])) {
    die('Missing required environment variable: SPEEDYINDEX_API_KEY');
}
define('SPEEDYINDEX_API_KEY', $_ENV['SPEEDYINDEX_API_KEY']);

// Database configuration - Required from environment
if (!isset($_ENV['DB_HOST']) || !isset($_ENV['DB_NAME']) || !isset($_ENV['DB_USER']) || !isset($_ENV['DB_PASS'])) {
    die('Missing required database environment variables');
}
define('DB_HOST', $_ENV['DB_HOST']);
define('DB_NAME', $_ENV['DB_NAME']);
define('DB_USER', $_ENV['DB_USER']);
define('DB_PASS', $_ENV['DB_PASS']);

// Logging
const LOG_FILE = __DIR__ . '/../storage/logs/app.log';

// Encryption Key (generated)
const ENCRYPTION_KEY = 'D6XcXFGWrDiByIpRyuNZap1d+OlBsHjwk/IaXCzq5wI=';

// Credits & Pricing
// Costs in USD (internal reference)
// Indexing cost: 0.005 (Primary) / 0.006 (Secondary)
// Checking cost: 0.0012

// Let's stick to 1 Credit = $0.10 (current default)
const DEFAULT_PRICE_PER_CREDIT_USD = 0.01; 

// Task Costs (in Credits/Cents)
const DEFAULT_COST_INDEXING = 2; // $0.02 per URL
const DEFAULT_COST_CHECKING = 1;  // $0.01 per URL
const DEFAULT_COST_VIP_EXTRA = 15; // +$0.15 for VIP

// Backward compatibility constants - TO BE REMOVED AFTER REFACTOR
const PRICE_PER_CREDIT_USD = DEFAULT_PRICE_PER_CREDIT_USD;
const COST_INDEXING = DEFAULT_COST_INDEXING;
const COST_CHECKING = DEFAULT_COST_CHECKING;
const COST_VIP_EXTRA = DEFAULT_COST_VIP_EXTRA;

// PayPal Configuration - Required from environment
if (!isset($_ENV['PAYPAL_ENV']) || !isset($_ENV['PAYPAL_CLIENT_ID']) || !isset($_ENV['PAYPAL_CLIENT_SECRET'])) {
    die('Missing required PayPal environment variables');
}
define('PAYPAL_ENV', $_ENV['PAYPAL_ENV']);
define('PAYPAL_CLIENT_ID', $_ENV['PAYPAL_CLIENT_ID']);
define('PAYPAL_CLIENT_SECRET', $_ENV['PAYPAL_CLIENT_SECRET']);
define('PAYPAL_WEBHOOK_SECRET', $_ENV['PAYPAL_WEBHOOK_SECRET'] ?? '');
define('PAYPAL_BN_CODE', $_ENV['PAYPAL_BN_CODE'] ?? '');

// Admin API Key - Optional, but required for using the Admin API
if (isset($_ENV['ADMIN_API_KEY'])) {
    define('ADMIN_API_KEY', $_ENV['ADMIN_API_KEY']);
}

// Cryptomus Configuration
if (isset($_ENV['CRYPTOMUS_MERCHANT_ID']) && isset($_ENV['CRYPTOMUS_PAYMENT_KEY'])) {
    define('CRYPTOMUS_MERCHANT_ID', $_ENV['CRYPTOMUS_MERCHANT_ID']);
    define('CRYPTOMUS_PAYMENT_KEY', $_ENV['CRYPTOMUS_PAYMENT_KEY']);
}

// Email Configuration
define('MAIL_FROM_ADDRESS', 'support@rapid-indexer.com');
define('MAIL_FROM_NAME', 'Rapid Indexer');
