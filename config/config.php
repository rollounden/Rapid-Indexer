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
const SPEEDYINDEX_API_KEY = $_ENV['SPEEDYINDEX_API_KEY'];

// Database configuration - Required from environment
if (!isset($_ENV['DB_HOST']) || !isset($_ENV['DB_NAME']) || !isset($_ENV['DB_USER']) || !isset($_ENV['DB_PASS'])) {
    die('Missing required database environment variables');
}
const DB_HOST = $_ENV['DB_HOST'];
const DB_NAME = $_ENV['DB_NAME'];
const DB_USER = $_ENV['DB_USER'];
const DB_PASS = $_ENV['DB_PASS'];

// Logging
const LOG_FILE = __DIR__ . '/../storage/logs/app.log';

// Credits & Pricing
const CREDITS_PER_URL = 1; // default credits per URL
const VIP_EXTRA_CREDITS_PER_URL = 1; // additional credits per URL when VIP is selected
const PRICE_PER_CREDIT_USD = 0.10; // example price per credit (USD)

// PayPal Configuration - Required from environment
if (!isset($_ENV['PAYPAL_ENV']) || !isset($_ENV['PAYPAL_CLIENT_ID']) || !isset($_ENV['PAYPAL_CLIENT_SECRET'])) {
    die('Missing required PayPal environment variables');
}
const PAYPAL_ENV = $_ENV['PAYPAL_ENV'];
const PAYPAL_CLIENT_ID = $_ENV['PAYPAL_CLIENT_ID'];
const PAYPAL_CLIENT_SECRET = $_ENV['PAYPAL_CLIENT_SECRET'];
const PAYPAL_WEBHOOK_SECRET = $_ENV['PAYPAL_WEBHOOK_SECRET'] ?? '';
const PAYPAL_BN_CODE = $_ENV['PAYPAL_BN_CODE'] ?? '';
