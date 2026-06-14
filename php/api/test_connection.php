<?php
session_start();
require_once '../db.php';

echo json_encode([
    'connection_status' => ($conn ? 'Connected' : 'Failed'),
    'conn_value' => var_export($conn, true),
    'current_file' => __FILE__,
    'dirname' => __DIR__,
    'db_file_path' => __DIR__ . '/../db.php',
    'db_file_exists' => file_exists(__DIR__ . '/../db.php')
]);
?>
