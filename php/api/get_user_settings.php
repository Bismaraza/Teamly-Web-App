<?php
session_start();
require_once '../db.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    // Return default values for non-authenticated users
    echo json_encode([
        'success' => false,
        'name' => 'Demo User',
        'email' => 'demo@teamly.com',
        'workspace' => 'Semester Project Team'
    ]);
    exit;
}

if (!$conn) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch user data
$stmt = $conn->prepare("SELECT name, email FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result && $result->num_rows > 0) {
    $user = $result->fetch_assoc();
    echo json_encode([
        'success' => true,
        'name' => $user['name'],
        'email' => $user['email'],
        'workspace' => 'Semester Project Team' // Default workspace
    ]);
} else {
    echo json_encode([
        'success' => false,
        'name' => 'Demo User',
        'email' => 'demo@teamly.com',
        'workspace' => 'Semester Project Team'
    ]);
}

$stmt->close();
$conn->close();
?>
