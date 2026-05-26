<?php
session_start();

if(!isset($_SESSION['user_email'])){
    header("Location:user_login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport"
content="width=device-width, initial-scale=1.0">

<title>
EstateFlow Support
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

min-height:100vh;

background:
linear-gradient(
rgba(2,6,23,.82),
rgba(2,6,23,.90)
),

url('https://images.unsplash.com/photo-1486406146926-c627a92ad1ab?q=80&w=1920&auto=format&fit=crop');

background-size:cover;
background-position:center;

display:flex;
justify-content:center;
align-items:center;

padding:20px;

color:white;

}

.support-box{

width:100%;
max-width:700px;

background:rgba(255,255,255,.08);

border:1px solid rgba(255,255,255,.10);

backdrop-filter:blur(18px);

border-radius:30px;

padding:50px;

box-shadow:0 10px 40px rgba(0,0,0,.35);

}

.title{

font-size:42px;
font-weight:800;

margin-bottom:30px;

text-align:center;

background:linear-gradient(
135deg,
#f8d66d,
#fb923c
);

-webkit-background-clip:text;
-webkit-text-fill-color:transparent;

}

.info{

margin-bottom:24px;

}

.label{

font-size:15px;
font-weight:700;

color:#94a3b8;

margin-bottom:8px;

}

.value{

font-size:18px;
font-weight:600;

line-height:1.8;

color:white;

}

.back-btn{

display:inline-block;

margin-top:25px;

padding:14px 24px;

border-radius:14px;

text-decoration:none;

background:linear-gradient(
135deg,
#3b82f6,
#9333ea
);

color:white;

font-weight:700;

transition:.3s;

}

.back-btn:hover{

transform:translateY(-3px);

}

@media(max-width:700px){

.support-box{

padding:35px 25px;

}

.title{

font-size:32px;

}

.value{

font-size:16px;

}

}

</style>

</head>

<body>

<div class="support-box">

<div class="title">
📞 Contact EstateFlow Management
</div>

<div class="info">

<div class="label">
Phone
</div>

<div class="value">
+91 9876543210
</div>

</div>

<div class="info">

<div class="label">
Email
</div>

<div class="value">
estateflowofficial@gmail.com
</div>

</div>

<div class="info">

<div class="label">
Office Address
</div>

<div class="value">
EstateFlow Office,BTM Layout 2nd Stage 1152 ,25th main road ,<br>
Bangalore, Karnataka
</div>

</div>

<div class="info">

<div class="label">
Office Timings
</div>

<div class="value">
10:00 AM – 6:00 PM
</div>

</div>

<a
href="user_dashboard.php"
class="back-btn">

← Back to Dashboard

</a>

</div>

</body>
</html>