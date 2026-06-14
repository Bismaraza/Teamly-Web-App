<?php
$host='localhost'; 
$user='root'; 
$pass=''; 
$db='flowspace_db';

echo "Testing MySQL connection...\n";
echo "Host: $host\n";
echo "User: $user\n";
echo "Database: $db\n\n";

$conn = @new mysqli($host, $user, $pass, $db);

if ($conn && !$conn->connect_error) {
    echo "✓ Connection SUCCESSFUL\n";
    $conn->set_charset('utf8mb4');
    
    // Test query
    $result = $conn->query("SELECT COUNT(*) as count FROM users");
    if ($result) {
        $row = $result->fetch_assoc();
        echo "✓ Users table has " . $row['count'] . " records\n";
    }
    
    $conn->close();
} else {
    echo "✗ Connection FAILED\n";
    if ($conn) {
        echo "Error: " . $conn->connect_error . "\n";
    } else {
        echo "Could not even create connection object\n";
    }
}
?>
