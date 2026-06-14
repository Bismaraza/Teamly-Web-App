<?php
$host = 'localhost';
$user = 'root';
$pass = '';
$db   = 'flowspace_db';

mysqli_report(MYSQLI_REPORT_OFF);
$conn = @new mysqli($host, $user, $pass, $db);

if ($conn && !$conn->connect_error) {
    $conn->set_charset('utf8mb4');
} else {
    $GLOBALS['DB_ERROR'] = $conn ? $conn->connect_error : 'MySQL connection object not created';
    $conn = null;
}
?>
