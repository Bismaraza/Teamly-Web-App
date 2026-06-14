<?php
session_start(); require_once '../db.php'; if(!$conn){ die('Database connection failed.'); }
$email=trim($_POST['email']??''); $password=$_POST['password']??'';
$stmt=$conn->prepare('SELECT id,name,password FROM users WHERE email=? LIMIT 1'); $stmt->bind_param('s',$email); $stmt->execute(); $res=$stmt->get_result(); $u=$res->fetch_assoc();
if($u && password_verify($password,$u['password'])){ $_SESSION['user_id']=$u['id']; $_SESSION['name']=$u['name']; header('Location: ../../dashboard.php'); exit; }
echo 'Invalid email or password.';
?>
