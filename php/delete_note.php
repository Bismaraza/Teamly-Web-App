<?php
session_start();
require_once 'db.php';

if (!$conn) die("DB connection failed.");

$id = intval($_POST['id'] ?? 0);
if ($id > 0) {
    $stmt = $conn->prepare("DELETE FROM notes WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
}

header("Location: ../notes.php");
exit;
?>
