<?php
// Visit this once: http://localhost/FlowSpace_Fixed/setup.php
$host = 'localhost';
$user = 'root';
$pass = '';

header('Content-Type: text/plain; charset=utf-8');

echo "FlowSpace setup starting...\n\n";
$conn = @new mysqli($host, $user, $pass);

if (!$conn || $conn->connect_error) {
    echo "ERROR: MySQL is not running or credentials are wrong.\n";
    echo "Fix: Start MySQL in XAMPP Control Panel, then refresh this page.\n";
    if ($conn) echo "MySQL error: " . $conn->connect_error . "\n";
    exit;
}

$conn->set_charset('utf8mb4');
$files = [
    __DIR__ . '/database/flowspace.sql',
    __DIR__ . '/database/add_reminders_table.sql'
];

foreach ($files as $file) {
    if (!file_exists($file)) {
        echo "ERROR: SQL file missing: $file\n";
        exit;
    }
    $sql = file_get_contents($file);
    if (!$conn->multi_query($sql)) {
        echo "ERROR while importing " . basename($file) . ": " . $conn->error . "\n";
        exit;
    }
    do {
        if ($result = $conn->store_result()) $result->free();
    } while ($conn->more_results() && $conn->next_result());
    if ($conn->error) {
        echo "ERROR after importing " . basename($file) . ": " . $conn->error . "\n";
        exit;
    }
    echo "Imported: " . basename($file) . "\n";
}

echo "\nSUCCESS: Database is ready.\n";
echo "Now open: http://localhost/FlowSpace_Fixed/signup.html\n";
echo "Demo login: admin@flowspace.com / password is set in SQL if you know it. Better create a new signup.\n";
?>
