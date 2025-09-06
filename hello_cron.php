<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

$nowUtc = gmdate('Y-m-d H:i:s') . ' UTC';
$sapi = PHP_SAPI;

// Ensure log directory exists
$logDir = __DIR__ . '/storage/logs';
if (!is_dir($logDir)) {
    @mkdir($logDir, 0775, true);
}

$logFile = $logDir . '/cron_hello.log';
$line = '[' . $nowUtc . "] Hello from hello_cron.php (SAPI=" . $sapi . ")\n";
@file_put_contents($logFile, $line, FILE_APPEND);

echo "OK: hello_cron ran at $nowUtc (SAPI=$sapi)\n";
echo "Log file: $logFile\n";
?>


