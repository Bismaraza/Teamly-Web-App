<?php
$host='localhost'; 
$user='root'; 
$pass=''; 
$db='flowspace_db';

$conn = new mysqli($host, $user, $pass, $db);

if ($conn && !$conn->connect_error) {
    $conn->set_charset('utf8mb4');
    
    $result = $conn->query("SELECT id, name, email FROM users");
    if ($result) {
        echo "Users in database:\n\n";
        while ($row = $result->fetch_assoc()) {
            echo "ID: " . $row['id'] . "\n";
            echo "Name: " . $row['name'] . "\n";
            echo "Email: " . $row['email'] . "\n\n";
        }
    }
    
    $conn->close();
}
?>
