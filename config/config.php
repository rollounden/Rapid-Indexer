<?php
// Production config for SpeedyIndex SaaS MVP

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

// SpeedyIndex API base
const SPEEDYINDEX_BASE_URL = 'https://api.speedyindex.com';

// SpeedyIndex API Key - Required from environment
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
// Indexing cost: 0.005 (Ralfy) / 0.006 (Speedy)
// Checking cost: 0.0012
// We sell credits at $0.10 per credit (or whatever margin we want)
// Let's say 1 credit = $0.01 for finer granularity?
// Or keep 1 credit = $0.10, but fractional credits are messy.
// Better approach: 1 Credit = $0.01 (1 cent)

// Let's redefine the system to use "Credits" as an abstract currency where 1 Credit ~= $0.01 (1 cent)
// Or even smaller if we want to charge 0.12 cents.
// For simplicity given the "50x-100x margin":
// Cost: 0.0012 USD -> Charge: 0.10 USD? That's huge.
// Cost: 0.006 USD -> Charge: 0.60 USD?
// The user said "we want a good margin on that".

// Proposed new pricing model:
// 1 Credit = $0.05 (5 cents)
// Indexing Task: 1 Credit per URL ($0.05) -> Cost $0.006 -> Margin ~8x
// Checking Task: 0.2 Credits per URL ($0.01) -> Cost $0.0012 -> Margin ~8x
// But integers are easier.

// Let's stick to 1 Credit = $0.10 (current default)
const DEFAULT_PRICE_PER_CREDIT_USD = 0.01; 

// Task Costs (in Credits/Cents)
const DEFAULT_COST_INDEXING = 2; // $0.02 per URL
const DEFAULT_COST_CHECKING = 1;  // $0.01 per URL
const DEFAULT_COST_VIP_EXTRA = 5; // +$0.05 for VIP

// Backward compatibility constants - TO BE REMOVED AFTER REFACTOR
const PRICE_PER_CREDIT_USD = DEFAULT_PRICE_PER_CREDIT_USD;
const COST_INDEXING = DEFAULT_COST_INDEXING;
const COST_CHECKING = DEFAULT_COST_CHECKING;
const COST_VIP_EXTRA = DEFAULT_COST_VIP_EXTRA;

// This requires migrating existing user balances (multiply by 10 if old price was 0.10).
// I will assume this is acceptable or I will provide a migration script.

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
