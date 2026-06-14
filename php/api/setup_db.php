<?php
$host='localhost'; 
$user='root'; 
$pass=''; 

echo "Setting up FlowSpace database...\n\n";

// Connect to MySQL without selecting a database
$conn = new mysqli($host, $user, $pass);

if ($conn && !$conn->connect_error) {
    echo "✓ Connected to MySQL\n";
    
    // Read and execute the SQL file
    $sql_file = __DIR__ . '/../../database/flowspace.sql';
    
    if (file_exists($sql_file)) {
        echo "✓ Found SQL file: $sql_file\n";
        
        $sql_content = file_get_contents($sql_file);
        
        // Split by semicolon and execute each statement
        $statements = array_filter(array_map('trim', explode(';', $sql_content)));
        
        $executed = 0;
        $errors = [];
        
        foreach ($statements as $statement) {
            if (!empty($statement)) {
                if (!$conn->query($statement)) {
                    $errors[] = $conn->error;
                } else {
                    $executed++;
                }
            }
        }
        
        if (empty($errors)) {
            echo "✓ Executed $executed SQL statements successfully\n";
            echo "✓ Database setup complete!\n\n";
            
            // Test the connection to the new database
            $db_conn = new mysqli($host, $user, $pass, 'flowspace_db');
            if ($db_conn && !$db_conn->connect_error) {
                echo "✓ Connected to flowspace_db\n";
                
                $result = $db_conn->query("SELECT COUNT(*) as count FROM users");
                if ($result) {
                    $row = $result->fetch_assoc();
                    echo "✓ Users table has " . $row['count'] . " records\n";
                }
                
                $db_conn->close();
            }
        } else {
            echo "✗ Errors during execution:\n";
            foreach ($errors as $error) {
                echo "  - $error\n";
            }
        }
    } else {
        echo "✗ SQL file not found: $sql_file\n";
    }
    
    $conn->close();
} else {
    echo "✗ MySQL Connection Failed\n";
    if ($conn) {
        echo "Error: " . $conn->connect_error . "\n";
    }
}
?>
