<?php
session_start();
require_once 'db.php';

if (!$conn) {
    echo 'Database connection failed. MySQL start karo, phir http://localhost/FlowSpace_Fixed/setup.php open karo. Error: ' . ($GLOBALS['DB_ERROR'] ?? 'unknown');
    exit;
}

$email    = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

if (!$email || !$password) {
    echo 'Email aur password dono bharo.';
    exit;
}

$stmt = $conn->prepare('SELECT id, name, password FROM users WHERE email = ? LIMIT 1');
$stmt->bind_param('s', $email);
$stmt->execute();
$res = $stmt->get_result();
$u   = $res->fetch_assoc();

if ($u && password_verify($password, $u['password'])) {
    $_SESSION['user_id'] = $u['id'];
    $_SESSION['name']    = $u['name'];
    echo 'ok';
} else {
    echo 'Invalid email or password.';
}
?>
