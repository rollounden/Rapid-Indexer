<?php
// Basic config for SpeedyIndex SaaS MVP

// SpeedyIndex API base
const SPEEDYINDEX_BASE_URL = 'https://api.speedyindex.com';

// TODO: Set your API key here for the server-side integration
// For production, move to environment variables.
const SPEEDYINDEX_API_KEY = 'REPLACE_WITH_YOUR_SPEEDYINDEX_API_KEY';

// Database configuration (update with your Hostinger MySQL credentials)
const DB_HOST = 'localhost';
const DB_NAME = 'REPLACE_DB_NAME';
const DB_USER = 'REPLACE_DB_USER';
const DB_PASS = 'REPLACE_DB_PASS';

// Logging
const LOG_FILE = __DIR__ . '/storage/logs/app.log';
