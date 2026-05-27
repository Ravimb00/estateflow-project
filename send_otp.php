<?php

error_reporting(E_ALL);
ini_set('display_errors',1);

header('Content-Type: application/json');

session_start();

include 'config/db.php';
include 'mail_config.php';

/* ONLY POST */

if($_SERVER['REQUEST_METHOD'] !== 'POST'){

echo json_encode([
    'ok' => false,
    'msg' => 'Invalid Request'
]);

exit();

}

/* GET EMAIL */

$email = trim($_POST['email'] ?? '');

if($email == ""){

echo json_encode([
    'ok' => false,
    'msg' => 'Email Required'
]);

exit();

}

$safeEmail = mysqli_real_escape_string(
$conn,
$email
);

/* CHECK ADMIN */

$getAdmin = mysqli_query(

$conn,

"SELECT * FROM admin
WHERE email='$safeEmail'
LIMIT 1"

);

if(mysqli_num_rows($getAdmin) == 0){

echo json_encode([
    'ok' => false,
    'msg' => 'Admin Not Found'
]);

exit();

}

$row = mysqli_fetch_assoc($getAdmin);

$adminId = $row['id'];

/* GENERATE OTP */

$otp = rand(100000,999999);

$expires = date(
"Y-m-d H:i:s",
strtotime("+5 minutes")
);

/* SAVE OTP */

mysqli_query(

$conn,

"INSERT INTO admin_otp(

admin_id,
otp_code,
otp_type,
expires_at,
used

)

VALUES(

'$adminId',
'$otp',
'login',
'$expires',
0

)"

);

/* EMAIL SUBJECT */

$subject = "EstateFlow Admin Login OTP";

/* EMAIL BODY */

$body = "

<div style='
font-family:Arial,sans-serif;
padding:20px;
background:#f8fafc;
'>

<div style='
max-width:600px;
margin:auto;
background:white;
padding:40px;
border-radius:16px;
'>

<h1 style='
color:#0f172a;
margin-bottom:10px;
'>
EstateFlow Admin Login
</h1>

<p style='
font-size:15px;
color:#475569;
line-height:1.7;
'>
Hello Admin,
</p>

<p style='
font-size:15px;
color:#475569;
line-height:1.7;
'>
Your secure One Time Password (OTP)
for EstateFlow Admin Login is:
</p>

<div style='
font-size:38px;
font-weight:800;
letter-spacing:10px;
text-align:center;
margin:30px 0;
color:#14b8a6;
'>
$otp
</div>

<p style='
font-size:14px;
color:#64748b;
line-height:1.7;
'>
This OTP will expire in 5 minutes.
</p>

<p style='
font-size:14px;
color:#64748b;
line-height:1.7;
'>
If you did not request this login,
please ignore this email.
</p>

<hr style='
margin:30px 0;
border:none;
border-top:1px solid #e2e8f0;
'>

<p style='
font-size:13px;
color:#94a3b8;
text-align:center;
'>
EstateFlow Security System
</p>

</div>

</div>

";

/* SEND MAIL */

$sent = sendMail(
$row['email'],
$subject,
$body
);

/* RESPONSE */

if($sent){

echo json_encode([

    'ok' => true,
    'msg' => 'OTP sent successfully',
    'admin_id' => $adminId

]);

}else{

echo json_encode([

    'ok' => false,
    'msg' => 'Mail sending failed'

]);

}

?>