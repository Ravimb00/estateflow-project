<?php
/**
 * send_otp.php — Admin OTP sender (fixed)
 * Accepts: admin_id OR email, otp_type
 * Uses send_mail.php → sendOTP()
 * Also auto-creates admin_otp table if missing
 */
session_start();
include 'config/db.php';
include 'send_mail.php';
 
header('Content-Type: application/json');
 
/* ── Accept email OR admin_id ── */
$adminId = intval($_POST['admin_id'] ?? 0);
$email   = trim($_POST['email'] ?? '');
$otpType = in_array($_POST['otp_type'] ?? '', ['login','delete_biometric','add_biometric'])
           ? $_POST['otp_type'] : 'login';
 
/* Lookup by email if no admin_id given */
if(!$adminId && $email){
    $safe = mysqli_real_escape_string($conn, $email);
    $r    = mysqli_fetch_assoc(mysqli_query($conn,
            "SELECT id FROM admin WHERE email='$safe' LIMIT 1"));
    if($r) $adminId = intval($r['id']);
}
 
if(!$adminId){
    echo json_encode(['ok'=>false,'msg'=>'Admin not found']);
    exit;
}
 
$row = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT * FROM admin WHERE id='$adminId' LIMIT 1"));
if(!$row){
    echo json_encode(['ok'=>false,'msg'=>'Admin not found']);
    exit;
}
 
/* ── Auto-create admin_otp table if missing ── */
mysqli_query($conn,"
    CREATE TABLE IF NOT EXISTS admin_otp (
        id         INT AUTO_INCREMENT PRIMARY KEY,
        admin_id   INT NOT NULL,
        otp_code   VARCHAR(10) NOT NULL,
        otp_type   VARCHAR(30) NOT NULL DEFAULT 'login',
        expires_at DATETIME NOT NULL,
        used       TINYINT(1) DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )
");
 
/* ── Generate OTP ── */
$otp     = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
$expires = date('Y-m-d H:i:s', time() + 300);
 
/* Invalidate old OTPs */
mysqli_query($conn,
    "UPDATE admin_otp SET used=1
     WHERE admin_id='$adminId' AND otp_type='$otpType' AND used=0");
 
/* Insert new OTP */
$ins = mysqli_query($conn,
    "INSERT INTO admin_otp (admin_id, otp_code, otp_type, expires_at)
     VALUES ('$adminId','$otp','$otpType','$expires')");
 
if(!$ins){
    echo json_encode(['ok'=>false,'msg'=>'DB error: '.mysqli_error($conn)]);
    exit;
}
 
/* ── Send via existing sendOTP() ── */
$sent = sendOTP($row['email'], $otp);
 
if($sent){
    echo json_encode([
        'ok'       => true,
        'msg'      => 'OTP sent to '.$row['email'],
        'admin_id' => $adminId
    ]);
} else {
    echo json_encode(['ok'=>false,'msg'=>'Mail sending failed. Check PHPMailer/SMTP config.']);
}