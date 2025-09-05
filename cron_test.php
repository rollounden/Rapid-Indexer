<?php
// Test script to verify cron jobs are working
// This creates a log entry every time it runs

$log_file = __DIR__ . '/storage/logs/cron_test.log';
$log_dir = dirname($log_file);

// Ensure log directory exists
if (!is_dir($log_dir)) {
    mkdir($log_dir, 0755, true);
}

// Log the test run
$timestamp = date('Y-m-d H:i:s');
$log_entry = "[$timestamp] Cron job test - Script executed successfully\n";

file_put_contents($log_file, $log_entry, FILE_APPEND | LOCK_EX);

// Also output to console for manual testing
echo "Cron test executed at $timestamp\n";
echo "Log written to: $log_file\n";

// Show recent log entries
if (file_exists($log_file)) {
    $lines = file($log_file, FILE_IGNORE_NEW_LINES);
    $recent_lines = array_slice($lines, -5); // Last 5 entries
    
    echo "\nRecent cron test entries:\n";
    foreach ($recent_lines as $line) {
        echo "$line\n";
    }
}
?>
