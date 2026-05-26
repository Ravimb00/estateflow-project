<?php

session_start();

include 'config/db.php';

$msg = "";
$step = 1;

/* SEND OTP */

if(isset($_POST['send_otp'])){

$email = mysqli_real_escape_string(
$conn,
trim($_POST['email'])
);

$check = mysqli_query(
$conn,
"SELECT * FROM users
WHERE email='$email'"
);

if(mysqli_num_rows($check) > 0){

$otp = rand(100000,999999);

$expiry = date(
"Y-m-d H:i:s",
strtotime("+10 minutes")
);

mysqli_query(

$conn,

"UPDATE users
SET otp_code='$otp',
otp_expiry='$expiry'
WHERE email='$email'"

);

$_SESSION['reset_email'] = $email;

$msg = "OTP Sent Successfully : ".$otp;

$step = 2;

}else{

$msg = "Email Not Found";

}

}

/* VERIFY OTP */

if(isset($_POST['verify_otp'])){

$email = $_SESSION['reset_email'];

$otp = trim($_POST['otp']);

$check = mysqli_query(

$conn,

"SELECT * FROM users
WHERE email='$email'
AND otp_code='$otp'
AND otp_expiry >= NOW()"

);

if(mysqli_num_rows($check) > 0){

$_SESSION['otp_verified'] = true;

$msg = "OTP Verified Successfully";

$step = 3;

}else{

$msg = "Invalid or Expired OTP";

$step = 2;

}

}

/* RESET PASSWORD */

if(isset($_POST['reset_password'])){

$email = $_SESSION['reset_email'];

$newPassword = password_hash(
$_POST['password'],
PASSWORD_DEFAULT
);

mysqli_query(

$conn,

"UPDATE users
SET password='$newPassword',
otp_code=NULL,
otp_expiry=NULL
WHERE email='$email'"

);

session_destroy();

$msg = "Password Reset Successful";

$step = 1;

}

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport"
content="width=device-width, initial-scale=1.0">

<title>
Forgot Password
</title>

<link href="https://fonts.googleapis.com/css2?family=Sora:wght@400;600;700;800&display=swap"
rel="stylesheet">

<style>

*{
margin:0;
padding:0;
box-sizing:border-box;
font-family:'Sora',sans-serif;
}

body{

height:100vh;

display:flex;
justify-content:center;
align-items:center;

background:
linear-gradient(
rgba(2,6,23,.88),
rgba(2,6,23,.92)
),

url('https://images.unsplash.com/photo-1486406146926-c627a92ad1ab?q=80&w=1920&auto=format&fit=crop');

background-size:cover;
background-position:center;

padding:20px;

color:white;

}

.box{

width:100%;
max-width:430px;

padding:40px;

background:rgba(255,255,255,.08);

border:1px solid rgba(255,255,255,.10);

backdrop-filter:blur(18px);

border-radius:30px;

box-shadow:0 10px 40px rgba(0,0,0,.40);

}

.logo{

font-size:42px;
font-weight:800;

text-align:center;

margin-bottom:10px;

background:linear-gradient(
135deg,
#f8d66d,
#fb923c
);

-webkit-background-clip:text;
-webkit-text-fill-color:transparent;

}

.sub{

text-align:center;

font-size:14px;

color:#94a3b8;

margin-bottom:30px;

}

.msg{

padding:14px;

border-radius:14px;

background:rgba(59,130,246,.18);

margin-bottom:18px;

font-size:14px;

text-align:center;

}

input{

width:100%;

padding:15px 18px;

margin-bottom:18px;

border:none;
outline:none;

border-radius:14px;

background:rgba(255,255,255,.07);

border:1px solid rgba(255,255,255,.08);

color:white;

font-size:14px;

}

button{

width:100%;

padding:15px;

border:none;

border-radius:14px;

background:linear-gradient(
135deg,
#3b82f6,
#14b8a6
);

color:white;

font-size:15px;

font-weight:700;

cursor:pointer;

transition:.3s;

}

button:hover{

transform:translateY(-2px);

}

.back{

display:block;

margin-top:20px;

text-align:center;

color:#93c5fd;

text-decoration:none;

font-size:13px;

}

</style>

</head>

<body>

<div class="box">

<div class="logo">
EstateFlow
</div>

<div class="sub">
Forgot Password via OTP
</div>

<?php if($msg!=""){ ?>

<div class="msg">
<?php echo $msg; ?>
</div>

<?php } ?>

<!-- STEP 1 -->

<?php if($step == 1){ ?>

<form method="POST">

<input
type="email"
name="email"
placeholder="Enter Registered Email"
required>

<button
type="submit"
name="send_otp">

Send OTP

</button>

</form>

<?php } ?>

<!-- STEP 2 -->

<?php if($step == 2){ ?>

<form method="POST">

<input
type="text"
name="otp"
placeholder="Enter OTP"
required>

<button
type="submit"
name="verify_otp">

Verify OTP

</button>

</form>

<?php } ?>

<!-- STEP 3 -->

<?php if($step == 3){ ?>

<form method="POST">

<input
type="password"
name="password"
placeholder="Enter New Password"
required>

<button
type="submit"
name="reset_password">

Reset Password

</button>

</form>

<?php } ?>

<a
href="user_login.php"
class="back">

← Back to Login

</a>

</div>

</body>
</html>