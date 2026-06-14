<?php
session_start();
require_once 'db.php';

if (!$conn) {
    die("Database connection failed.");
}

$title    = trim($_POST['title']    ?? '');
$status   = trim($_POST['status']   ?? 'To Do');
$priority = trim($_POST['priority'] ?? 'Normal');

if ($title == '') {
    die("Task title is required.");
}

$stmt = $conn->prepare("INSERT INTO tasks (title, status, priority) VALUES (?, ?, ?)");
$stmt->bind_param("sss", $title, $status, $priority);

if ($stmt->execute()) {
    // Redirect back to whichever page called us
    $ref = $_SERVER['HTTP_REFERER'] ?? '../tasks.php';
    header("Location: " . $ref);
    exit;
}

echo "Task save failed: " . $stmt->error;
?>
