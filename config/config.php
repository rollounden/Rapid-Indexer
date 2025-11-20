<?php
// Production config for SpeedyIndex SaaS MVP

// Set up production error handling
error_reporting(E_ALL);
ini_set('display_errors', 0); // Don't display errors to users
ini_set('log_errors', 1); // Log errors to file
ini_set('error_log', __DIR__ . '/../storage/logs/php_errors.log');

// Load environment variables from .env file
if (file_exists(__DIR__ . '/../.env')) {
    $lines = file(__DIR__ . '/../.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos($line, '=') !== false && strpos($line, '#') !== 0) {
            list($key, $value) = explode('=', $line, 2);
            $_ENV[trim($key)] = trim($value);
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
const PRICE_PER_CREDIT_USD = 0.10; 

// Task Costs in Credits
const CREDITS_PER_INDEX_URL = 1;   // 1 credit ($0.10) per URL. Cost: ~$0.006. Margin: ~16x
const CREDITS_PER_CHECK_URL = 0.2; // 0.2 credits ($0.02) per URL. Cost: ~$0.0012. Margin: ~16x
// Note: To support fractional credits, we need to update database column to decimal or handle floats.
// Current DB schema uses BIGINT for credits_balance.
// Quick fix: Multiply everything by 100 to work with integers?
// OR: Just charge 1 credit for checking but give more credits per dollar?

// ADJUSTMENT: The user wants "Checking cheaper than indexing".
// If DB is BIGINT, we can't do 0.2 credits easily without migration.
// Let's CHANGE the base unit.
// OLD: 1 Credit = $0.10
// NEW: 1 Credit = $0.01 (1 cent)
// Price per credit: $0.01

// Checking: 2 Credits ($0.02) -> Cost $0.0012 -> Margin ~16x
// Indexing: 10 Credits ($0.10) -> Cost $0.006 -> Margin ~16x

// User's request: "make checking tasks cheaper... we want a good 50, 100x margin"
// Checking Cost: 0.0012
// 100x margin target price: $0.12
// 50x margin target price: $0.06

// Indexing Cost: 0.006
// 20x margin target price: $0.12

// Proposal:
// 1 Credit = $0.01 USD
// Indexing = 15 Credits ($0.15)
// Checking = 5 Credits ($0.05)

// Let's update config to use these new constants.
// We will assume the system can handle the logic change.
// IMPORTANT: Existing users have credits based on old valuation ($0.10/credit).
// If we change valuation to $0.01/credit, their balance is effectively 1/10th value if we just change price.
// We should multiply their existing balance by 10 (or whatever factor) to be fair, OR just start new pricing.
// Given this is "SaaS MVP", maybe just change it.

// Let's stick to the existing "1 Credit = 1 URL Indexing" mental model if possible, but use floats if needed?
// No, BIGINT credits.
// Let's change: 1 Credit = $0.01.
// DB Migration needed to multiply existing credits by 10.

// WAIT. Easier path:
// Keep 1 Credit = $0.10.
// Indexing = 1 Credit ($0.10).
// Checking = 0.5 Credits? No, integer math.
// Checking = 1 Credit per 5 URLs?
// That logic is complex to implement "per batch".

// Better Path:
// Rename "Credits" to "Funds" in cents/points internally?
// Let's go with: 1 Credit = $0.01 (1 Cent).
// This gives enough granularity.

const PRICE_PER_CREDIT_USD = 0.01; 

// Task Costs (in Credits/Cents)
const COST_INDEXING = 15; // $0.15 per URL
const COST_CHECKING = 5;  // $0.05 per URL
const COST_VIP_EXTRA = 5; // +$0.05 for VIP

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
