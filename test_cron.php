<?php
// Simple cron job test script
// This will log when cron jobs are running

$log_file = __DIR__ . '/storage/logs/cron_test.log';
$log_dir = dirname($log_file);

// Create log directory if it doesn't exist
if (!is_dir($log_dir)) {
    mkdir($log_dir, 0755, true);
}

// Log the cron job execution
$timestamp = date('Y-m-d H:i:s');
$log_entry = "[$timestamp] Cron job test executed successfully\n";

file_put_contents($log_file, $log_entry, FILE_APPEND | LOCK_EX);

echo "Cron test logged at: $timestamp\n";
?>
