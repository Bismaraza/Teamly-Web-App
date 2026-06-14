<?php
session_start();
require_once 'db.php';

if (!$conn) {
    die("Database connection failed.");
}

$title   = trim($_POST['title']   ?? '');
$content = trim($_POST['content'] ?? '');

if ($title == '' || $content == '') {
    die("Title and content are required.");
}

$stmt = $conn->prepare("INSERT INTO notes (title, content) VALUES (?, ?)");
$stmt->bind_param("ss", $title, $content);

if ($stmt->execute()) {
    header("Location: ../notes.php");
    exit;
}

echo "Note save failed: " . $stmt->error;
?>
