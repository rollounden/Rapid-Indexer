<?php
// Create a web-accessible info file to find the socket from the working web environment
// This file should be accessed via the browser
echo "<h3>MySQL Socket Information</h3>";
echo "<strong>CLI PHP Version:</strong> " . phpversion() . "<br>";
echo "<strong>MySQLi Default Socket:</strong> " . ini_get('mysqli.default_socket') . "<br>";
echo "<strong>PDO MySQL Default Socket:</strong> " . ini_get('pdo_mysql.default_socket') . "<br>";

echo "<h3>Connection Test</h3>";
$link = @mysqli_connect("localhost", "u906310247_FBapb", "Test123456**888", "u906310247_KEKRd");
if ($link) {
    echo "<strong>Success:</strong> Connected via MySQLi<br>";
    echo "<strong>Host Info:</strong> " . mysqli_get_host_info($link) . "<br>";
} else {
    echo "<strong>Error:</strong> " . mysqli_connect_error() . "<br>";
}
?>
