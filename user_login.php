<?php
session_start();

include 'config/db.php';

/* Already logged in */

if(isset($_SESSION['user_email'])){
    header("Location:user_dashboard.php");
    exit();
}

$msg = "";

if(isset($_POST['login'])){

    $email = mysqli_real_escape_string(
        $conn,
        trim($_POST['email'])
    );

    $password = trim($_POST['password']);

    /* CAPTCHA CHECK */

    if(
        strtolower(trim($_POST['captcha']))
        !==
        strtolower($_SESSION['captcha_text'] ?? '')
    ){

        $msg = "Invalid Captcha. Please try again.";

    }else{

        $check = mysqli_query(

            $conn,

            "SELECT * FROM users
            WHERE email='$email'
            LIMIT 1"

        );

        if(mysqli_num_rows($check) > 0){

            $user = mysqli_fetch_assoc($check);

            /* PASSWORD CHECK */

            $isValid =

            password_verify(
                $password,
                $user['password']
            )

            ||

            ($password === $user['password']);

            if($isValid){

                if($user['is_verified'] == 1){

                    /* SESSION */

                    session_regenerate_id(true);

                    $_SESSION['user_email']
                    = $user['email'];

                    $_SESSION['user_id']
                    = $user['id'];

                    $_SESSION['user_role']
                    = $user['role'];

                    $_SESSION['user_name']

                    =

                    !empty($user['name'])

                    ?

                    $user['name']

                    :

                    trim(
                        $user['first_name']
                        .' '.
                        $user['last_name']
                    );

                    /* REDIRECT */

                    header(
                        "Location:user_dashboard.php"
                    );

                    exit();

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

            $msg = "User Not Found. Please register.";

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

<title>
EstateFlow – User Login
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
overflow:hidden;
background:#020617;
position:relative;
}

/* SLIDESHOW */

.slide-bg{
position:fixed;
inset:0;
z-index:0;
}

.slide{
position:absolute;
inset:0;
background-size:cover;
background-position:center;
opacity:0;
transition:opacity 2s ease;
animation:zoom 14s linear infinite;
}

.slide.active{
opacity:1;
}

@keyframes zoom{

0%{
transform:scale(1)
}

100%{
transform:scale(1.08)
}

}

.slide:nth-child(1){
background-image:url('https://images.unsplash.com/photo-1486406146926-c627a92ad1ab?w=1920&q=95');
}

.slide:nth-child(2){
background-image:url('https://images.unsplash.com/photo-1560518883-ce09059eeffa?w=1920&q=95');
}

.slide:nth-child(3){
background-image:url('https://images.unsplash.com/photo-1512917774080-9991f1c4c750?w=1920&q=95');
}

.slide:nth-child(4){
background-image:url('https://images.unsplash.com/photo-1494526585095-c41746248156?w=1920&q=95');
}

.slide-overlay{
position:fixed;
inset:0;
z-index:1;
background:rgba(2,6,23,0.62);
}

/* BLOBS */

.blob1{
position:absolute;
width:320px;
height:320px;
background:#14b8a6;
border-radius:50%;
top:120px;
left:220px;
filter:blur(90px);
opacity:.35;
animation:float1 6s ease-in-out infinite;
z-index:2;
}

.blob2{
position:absolute;
width:350px;
height:350px;
background:#3b82f6;
border-radius:50%;
bottom:100px;
right:220px;
filter:blur(90px);
opacity:.35;
animation:float2 8s ease-in-out infinite;
z-index:2;
}

@keyframes float1{

0%,100%{
transform:translateY(0)
}

50%{
transform:translateY(-25px)
}

}

@keyframes float2{

0%,100%{
transform:translateY(0)
}

50%{
transform:translateY(25px)
}

}

/* CARD */

.card{
width:430px;
padding:50px;
border-radius:36px;
background:rgba(255,255,255,.08);
border:1px solid rgba(255,255,255,.12);
backdrop-filter:blur(22px);
box-shadow:0 0 50px rgba(0,0,0,.45);
position:relative;
z-index:10;
}

.logo{
font-size:50px;
font-weight:800;
text-align:center;
background:linear-gradient(135deg,#f8d66d,#d78655);
-webkit-background-clip:text;
-webkit-text-fill-color:transparent;
margin-bottom:10px;
}

.sub{
text-align:center;
font-size:14px;
color:#94a3b8;
margin-bottom:32px;
}

.msg{
background:rgba(239,68,68,.15);
border:1px solid rgba(239,68,68,.22);
padding:13px;
border-radius:13px;
margin-bottom:18px;
color:#fca5a5;
text-align:center;
font-size:13.5px;
}

input[type=email],
input[type=password],
input[type=text]{

width:100%;
padding:15px 18px;
margin-bottom:16px;
border:none;
outline:none;
border-radius:14px;
background:rgba(255,255,255,.07);
color:white;
font-size:14px;
border:1px solid rgba(255,255,255,.09);

}

input::placeholder{
color:#64748b;
}

input:focus{
border-color:#3b82f6;
box-shadow:0 0 0 3px rgba(59,130,246,.15);
}

.captcha-box{
margin-bottom:16px;
display:flex;
justify-content:center;
}

.captcha-box img{
border-radius:12px;
cursor:pointer;
}

.captcha-refresh{
text-align:center;
font-size:11.5px;
color:#64748b;
margin-bottom:12px;
cursor:pointer;
}

button{
width:100%;
padding:16px;
border:none;
border-radius:14px;
background:linear-gradient(135deg,#3b82f6,#14b8a6);
color:white;
font-size:15px;
font-weight:700;
cursor:pointer;
transition:.3s;
}

button:hover{
transform:translateY(-2px);
}

.register{
margin-top:22px;
text-align:center;
font-size:13.5px;
color:#64748b;
}

.register a{
color:#5eead4;
font-weight:700;
text-decoration:none;
}

@media(max-width:600px){

.card{
width:92%;
padding:34px 22px;
}

.logo{
font-size:40px;
}

}

</style>
</head>

<body>

<div class="slide-bg">

<div class="slide active"></div>
<div class="slide"></div>
<div class="slide"></div>
<div class="slide"></div>

</div>

<div class="slide-overlay"></div>

<div class="blob1"></div>
<div class="blob2"></div>

<div class="card">

<div class="logo">
EstateFlow
</div>

<div class="sub">
User Login
</div>

<?php if($msg !== ""): ?>

<div class="msg">
<?= htmlspecialchars($msg) ?>
</div>

<?php endif; ?>

<form method="POST" autocomplete="off">

<input
type="email"
name="email"
placeholder="Enter Email"
value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
required>

<input
type="password"
name="password"
placeholder="Enter Password"
required>

<div class="captcha-box">

<img
src="captcha.php"
alt="captcha"
id="captchaImg"

onclick="
this.src='captcha.php?'+Math.random()
">

</div>

<div class="captcha-refresh"

onclick="
document.getElementById('captchaImg').src='captcha.php?'+Math.random()
">

🔄 Click image to refresh captcha

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

<script>

(function(){

var s=document.querySelectorAll('.slide');

var c=0;

setInterval(function(){

s[c].classList.remove('active');

c=(c+1)%s.length;

s[c].classList.add('active');

},6000);

})();

</script>

</body>
</html>