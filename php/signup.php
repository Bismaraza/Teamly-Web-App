<?php
session_start();
require_once 'db.php';

if (!$conn) {
    echo 'Database connection failed. MySQL start karo, phir http://localhost/FlowSpace_Fixed/setup.php open karo. Error: ' . ($GLOBALS['DB_ERROR'] ?? 'unknown');
    exit;
}

$name     = trim($_POST['name'] ?? '');
$email    = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$plan     = trim($_POST['plan'] ?? 'Starter');

if (!$name || !$email || strlen($password) < 6) {
    echo 'Invalid data. Naam, email aur 6+ character password bharo.';
    exit;
}

$hash = password_hash($password, PASSWORD_DEFAULT);

$stmt = $conn->prepare('INSERT INTO users(name, email, password, plan) VALUES(?, ?, ?, ?)');
$stmt->bind_param('ssss', $name, $email, $hash, $plan);

if ($stmt->execute()) {
    $_SESSION['user_id'] = $stmt->insert_id;
    $_SESSION['name']    = $name;
    echo 'ok';
} else {
    echo 'Signup failed. Ye email pehle se register hai.';
}
?>
