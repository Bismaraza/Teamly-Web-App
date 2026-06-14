<?php
session_start();
require_once '../db.php';

header('Content-Type: application/json');

if (!$conn) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

// Get POST data
$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$workspace = trim($_POST['workspace'] ?? '');

// Validation
if (empty($name) || empty($email)) {
    echo json_encode(['success' => false, 'message' => 'Name and email are required']);
    exit;
}

// Validate email format
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Invalid email format']);
    exit;
}

// Get user_id from session or default to 1 (demo user)
$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 1;

// Update user settings
$stmt = $conn->prepare("UPDATE users SET name = ?, email = ? WHERE id = ?");
if (!$stmt) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $conn->error]);
    exit;
}

$stmt->bind_param("ssi", $name, $email, $user_id);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Settings saved successfully']);
} else {
    if (strpos($stmt->error, 'Duplicate entry') !== false) {
        echo json_encode(['success' => false, 'message' => 'Email already in use']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error saving settings: ' . $stmt->error]);
    }
}

$stmt->close();
$conn->close();
?>
