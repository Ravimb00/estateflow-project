<?php

error_reporting(E_ALL);
ini_set('display_errors',1);

session_start();

include 'config/db.php';

$msg = "";

if(isset($_POST['verify'])){

$user_otp = trim($_POST['otp']);

/* CHECK OTP */

if($user_otp == $_SESSION['signup_otp']){

/* GET SESSION DATA */

$first_name = mysqli_real_escape_string(
$conn,
$_SESSION['signup_first_name']
);

$last_name = mysqli_real_escape_string(
$conn,
$_SESSION['signup_last_name']
);

$email = mysqli_real_escape_string(
$conn,
$_SESSION['signup_email']
);

$password = $_SESSION['signup_password'];

/* INSERT USER */

$insert = mysqli_query(

$conn,

"INSERT INTO users
SET

first_name='$first_name',
last_name='$last_name',
email='$email',
password='$password',
role='user',
is_verified='1'
"

);

/* IF SQL ERROR */

if(!$insert){

die(mysqli_error($conn));

}

/* CLEAR SESSION */

unset($_SESSION['signup_otp']);
unset($_SESSION['signup_email']);
unset($_SESSION['signup_password']);
unset($_SESSION['signup_first_name']);
unset($_SESSION['signup_last_name']);

/* REDIRECT */

header(
"Location:user_login.php"
);

exit();

}else{

$msg = "Invalid OTP";

}

}

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport"
content="width=device-width, initial-scale=1.0">

<title>
Verify OTP
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

background:#020617;

height:100vh;

display:flex;
justify-content:center;
align-items:center;

overflow:hidden;

}

/* CARD */

.card{

width:400px;

background:#0f172a;

padding:40px;

border-radius:24px;

border:1px solid rgba(255,255,255,.08);

box-shadow:
0 0 30px rgba(0,0,0,.35);

}

/* LOGO */

.logo{

font-size:38px;

font-weight:800;

color:white;

text-align:center;

margin-bottom:10px;

}

/* SUB */

.sub{

text-align:center;

color:#94a3b8;

font-size:14px;

margin-bottom:25px;

}

/* ERROR MESSAGE */

.msg{

background:rgba(239,68,68,.12);

padding:14px;

border-radius:12px;

margin-bottom:18px;

color:white;

font-size:13px;

text-align:center;

border:1px solid rgba(239,68,68,.15);

}

/* INPUT */

input{

width:100%;

padding:15px;

border:none;
outline:none;

border-radius:14px;

background:rgba(255,255,255,.05);

border:1px solid rgba(255,255,255,.08);

color:white;

font-size:14px;

margin-bottom:20px;

transition:.3s;

}

input:focus{

border-color:#3b82f6;

box-shadow:
0 0 0 4px rgba(59,130,246,.12);

}

/* BUTTON */

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

/* MOBILE */

@media(max-width:500px){

.card{

width:92%;

padding:30px 22px;

}

.logo{
font-size:32px;
}

}

</style>

</head>

<body>

<div class="card">

<div class="logo">
EstateFlow
</div>

<div class="sub">
Enter OTP sent to your email
</div>

<?php if($msg!=""){ ?>

<div class="msg">

<?php echo $msg; ?>

</div>

<?php } ?>

<form method="POST">

<input
type="text"
name="otp"
placeholder="Enter OTP"
required>

<button
type="submit"
name="verify">

Verify OTP

</button>

</form>

</div>

</body>
</html>