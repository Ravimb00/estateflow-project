<?php
/* get_admin_id.php — returns admin id by email (used by OTP login flow) */
session_start();
include 'config/db.php';
header('Content-Type: application/json');

$email = trim($_POST['email'] ?? '');
if(!$email){ echo json_encode(['ok'=>false,'msg'=>'Email required']); exit; }

$safe = mysqli_real_escape_string($conn,$email);
$row  = mysqli_fetch_assoc(mysqli_query($conn,"SELECT id FROM admin WHERE email='$safe' LIMIT 1"));

if($row){ echo json_encode(['ok'=>true,'admin_id'=>$row['id']]); }
else     { echo json_encode(['ok'=>false,'msg'=>'No admin found with this email']); }