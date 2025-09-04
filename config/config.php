<?php
// Basic config for SpeedyIndex SaaS MVP

// SpeedyIndex API base
const SPEEDYINDEX_BASE_URL = 'https://api.speedyindex.com';

// TODO: Set your API key here for the server-side integration
// For production, move to environment variables.
const SPEEDYINDEX_API_KEY = 'e52842c5690fdc017a8949064c4b4d86';

// Database configuration (update with your Hostinger MySQL credentials)
// Find these in your Hostinger control panel > Databases > MySQL Databases
const DB_HOST = 'localhost';
const DB_NAME = 'u906310247_KEKRd';  // Using your most recent database
const DB_USER = 'u906310247_FBapb';  // Corresponding user
const DB_PASS = 'Test123456**888';  // Updated password from Hostinger

// Logging
const LOG_FILE = __DIR__ . '/../storage/logs/app.log';

// Credits & Pricing
const CREDITS_PER_URL = 1; // default credits per URL
const VIP_EXTRA_CREDITS_PER_URL = 1; // additional credits per URL when VIP is selected
const PRICE_PER_CREDIT_USD = 0.01; // example price per credit (USD)

// PayPal (placeholders; wire actual credentials in environment/config)
const PAYPAL_ENV = 'sandbox'; // 'live' in production
const PAYPAL_CLIENT_ID = 'AdsnzKeKmo5cHtx_QqYTG5nNQ_kyaoR012ltrAad107RUxiLu2H2Z59kKAYZei9XY4zcQyBW-Lj3_OKU';
const PAYPAL_CLIENT_SECRET = 'ENWQ_M-NsZxmr_9s2qBEzSKcuLFhxG00wcF_uaEVTSh_Vs7rSZFjXgrYuzPxwgNHXR5u0r5im6dl-3Gt';
const PAYPAL_WEBHOOK_SECRET = '9M9241950W022223V';
const PAYPAL_BN_CODE = 'FLAVORsb-j2gdy45737228_MP';
