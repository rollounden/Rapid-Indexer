<?php
// Basic config for SpeedyIndex SaaS MVP

// SpeedyIndex API base
const SPEEDYINDEX_BASE_URL = 'https://api.speedyindex.com';

// TODO: Set your API key here for the server-side integration
// For production, move to environment variables.
const SPEEDYINDEX_API_KEY = 'REPLACE_WITH_YOUR_SPEEDYINDEX_API_KEY';

// Database configuration (update with your Hostinger MySQL credentials)
// Find these in your Hostinger control panel > Databases > MySQL Databases
const DB_HOST = 'localhost';
const DB_NAME = 'u906310247_KEKRd';  // Using your most recent database
const DB_USER = 'u906310247_FBapb';  // Corresponding user
const DB_PASS = 'your_database_password';  // Replace with your actual database password

// Logging
const LOG_FILE = __DIR__ . '/../storage/logs/app.log';

// Credits & Pricing
const CREDITS_PER_URL = 1; // default credits per URL
const VIP_EXTRA_CREDITS_PER_URL = 1; // additional credits per URL when VIP is selected
const PRICE_PER_CREDIT_USD = 0.01; // example price per credit (USD)

// PayPal (placeholders; wire actual credentials in environment/config)
const PAYPAL_ENV = 'sandbox'; // 'live' in production
const PAYPAL_WEBHOOK_SECRET = 'REPLACE_WITH_WEBHOOK_SECRET';
