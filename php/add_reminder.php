<?php
session_start();
require_once 'db.php';

if (!$conn)
    die("DB connection failed.");

$title = trim($_POST['title'] ?? '');
$date = trim($_POST['reminder_date'] ?? '');
$time = trim($_POST['reminder_time'] ?? '09:00');
$color = trim($_POST['color'] ?? 'blue');

if ($title && $date) {
    $stmt = $conn->prepare("INSERT INTO reminders (title, reminder_date, reminder_time, color) VALUES (?,?,?,?)");
    $stmt->bind_param("ssss", $title, $date, $time, $color);
    $stmt->execute();
}

header("Location: ../calendar.php");
exit;
?>