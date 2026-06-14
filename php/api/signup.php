<?php
session_start(); require_once '../db.php';
if(!$conn){ die('Database connection failed. Import database/flowspace.sql first.'); }
$name=trim($_POST['name']??''); $email=trim($_POST['email']??''); $password=$_POST['password']??''; $plan=trim($_POST['plan']??'Starter');
if(!$name||!$email||strlen($password)<6){ die('Invalid signup data.'); }
$hash=password_hash($password,PASSWORD_DEFAULT);
$stmt=$conn->prepare('INSERT INTO users(name,email,password,plan) VALUES(?,?,?,?)'); $stmt->bind_param('ssss',$name,$email,$hash,$plan);
if($stmt->execute()){ $_SESSION['user_id']=$stmt->insert_id; $_SESSION['name']=$name; header('Location: ../../dashboard.php'); exit; }
echo 'Signup failed. Email may already exist.';
?>
