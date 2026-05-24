<?php

session_start();

include 'config/db.php';

$msg = "";

if(isset($_POST['login'])){

$email = mysqli_real_escape_string(
$conn,
trim($_POST['email'])
);

$password =
trim($_POST['password']);

$userCaptcha =
trim($_POST['captcha']);

/* CAPTCHA CHECK */

if(
strtolower($userCaptcha)
!=
strtolower($_SESSION['captcha_text'])
){

$msg = "Invalid Captcha";

}else{

/* CHECK USER */

$check = mysqli_query(
$conn,
"SELECT * FROM users
WHERE email='$email'"
);

if(mysqli_num_rows($check) > 0){

$user = mysqli_fetch_assoc($check);

/* PASSWORD CHECK */

if(

password_verify(
$password,
$user['password']
)

||

$password == $user['password']

){

/* VERIFIED USER */

if($user['is_verified'] == 1){

$_SESSION['user_email'] =
$user['email'];

$_SESSION['user_role'] =
$user['role'];

$_SESSION['user_name'] =
$user['name'];

/* LOGIN SUCCESS */

header(
"Location:user_dashboard.php"
);

exit();

/* UNVERIFIED USER */

}else{

header(
"Location:user_signup.php"
);

exit();

}

}else{

$msg = "Invalid Login Credentials";

}

}else{

$msg = "User Not Found";

}

}

}

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport"
content="width=device-width, initial-scale=1.0">

<title>EstateFlow Login</title>

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
overflow:hidden;

background:
linear-gradient(
135deg,
#07152d,
#0f172a,
#111827
);

position:relative;
}

/* BLUR */

body::before{
content:'';
position:absolute;
width:320px;
height:320px;
background:#14b8a6;
border-radius:50%;
top:120px;
left:220px;
filter:blur(90px);
opacity:.7;
animation:float1 6s ease-in-out infinite;
}

body::after{
content:'';
position:absolute;
width:350px;
height:350px;
background:#3b82f6;
border-radius:50%;
bottom:100px;
right:220px;
filter:blur(90px);
opacity:.7;
animation:float2 8s ease-in-out infinite;
}

/* BUBBLES */

.bubble1{
position:absolute;
width:180px;
height:180px;
border-radius:50%;

background:linear-gradient(
135deg,
#5eead4,
#14b8a6
);

top:140px;
right:300px;
opacity:.9;
}

.bubble2{
position:absolute;
width:120px;
height:120px;
border-radius:50%;

background:linear-gradient(
135deg,
#60a5fa,
#2563eb
);

bottom:130px;
left:280px;
opacity:.9;
}

/* CARD */

.card{
width:430px;
padding:55px;

border-radius:40px;

background:rgba(255,255,255,.08);

border:1px solid rgba(255,255,255,.12);

backdrop-filter:blur(22px);

box-shadow:
0 0 40px rgba(0,0,0,.35);

position:relative;
z-index:5;

animation:popup .6s ease;
}

/* LOGO */

.logo{
font-size:54px;
font-weight:800;
text-align:center;
color:white;
margin-bottom:12px;
}

/* SUB */

.sub{
text-align:center;
font-size:15px;
color:#cbd5e1;
margin-bottom:35px;
}

/* MESSAGE */

.msg{
background:rgba(239,68,68,.15);

border:1px solid rgba(239,68,68,.2);

padding:14px;

border-radius:14px;

margin-bottom:20px;

color:white;

text-align:center;

font-size:14px;
}

/* INPUT */

input{
width:100%;

padding:18px;

margin-bottom:20px;

border:none;
outline:none;

border-radius:18px;

background:rgba(255,255,255,.08);

color:white;

font-size:15px;

backdrop-filter:blur(12px);

border:1px solid rgba(255,255,255,.08);

transition:.3s;
}

input::placeholder{
color:#cbd5e1;
}

input:focus{

border-color:#3b82f6;

box-shadow:
0 0 0 4px rgba(59,130,246,.15);
}

/* CAPTCHA IMAGE */

.captcha-image{
margin-bottom:18px;
display:flex;
justify-content:center;
}

.captcha-image img{
border-radius:14px;
border:1px solid rgba(255,255,255,.1);
}

/* BUTTON */

button{
width:100%;

padding:18px;

border:none;

border-radius:18px;

background:linear-gradient(
135deg,
#3b82f6,
#14b8a6
);

color:white;

font-size:16px;

font-weight:700;

cursor:pointer;

transition:.35s;

box-shadow:
0 0 30px rgba(59,130,246,.25);
}

button:hover{
transform:translateY(-3px);
}

/* REGISTER */

.register{
margin-top:24px;
text-align:center;
font-size:14px;
color:#cbd5e1;
}

.register a{
color:#5eead4;
font-weight:700;
text-decoration:none;
}

/* ANIMATION */

@keyframes popup{

from{
opacity:0;
transform:scale(.8);
}

to{
opacity:1;
transform:scale(1);
}

}

@keyframes float1{

0%{
transform:translateY(0px);
}

50%{
transform:translateY(-25px);
}

100%{
transform:translateY(0px);
}

}

@keyframes float2{

0%{
transform:translateY(0px);
}

50%{
transform:translateY(25px);
}

100%{
transform:translateY(0px);
}

}

/* MOBILE */

@media(max-width:600px){

.card{
width:92%;
padding:35px 25px;
}

.logo{
font-size:42px;
}

}

</style>

</head>

<body>

<div class="bubble1"></div>
<div class="bubble2"></div>

<div class="card">

<div class="logo">
EstateFlow
</div>

<div class="sub">
User Login
</div>

<?php if($msg!=""){ ?>

<div class="msg">

<?php echo $msg; ?>

</div>

<?php } ?>

<form method="POST">

<input
type="email"
name="email"
placeholder="Enter Email"
required>

<input
type="password"
name="password"
placeholder="Enter Password"
required>

<div class="captcha-image">

<img
src="captcha.php"
alt="captcha">

</div>

<input
type="text"
name="captcha"
placeholder="Enter Captcha"
required>

<button
type="submit"
name="login">

Login

</button>

</form>

<div class="register">

New User?

<a href="user_signup.php">
Register Now
</a>

</div>

</div>

</body>
</html>