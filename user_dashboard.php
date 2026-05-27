<?php
session_start();

include 'config/db.php';

if(!isset($_SESSION['user_email'])){
    header("Location:user_login.php");
    exit();
}

$userName = $_SESSION['user_name'] ?? $_SESSION['user_email'];

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport"
content="width=device-width, initial-scale=1.0">

<title>
EstateFlow – User Dashboard
</title>

<link href="https://fonts.googleapis.com/css2?family=Sora:wght@400;500;600;700;800&display=swap"
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
color:white;
overflow-x:hidden;
min-height:100vh;
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
background-repeat:no-repeat;
opacity:0;
transition:opacity 2.4s ease-in-out;
animation:kb 14s ease-in-out infinite alternate;
}

.slide.active{
opacity:1;
}

@keyframes kb{

0%{
transform:scale(1.00) translate(0,0)
}

100%{
transform:scale(1.09) translate(-14px,-8px)
}

}

.slide:nth-child(1){
background-image:url('https://images.unsplash.com/photo-1486406146926-c627a92ad1ab?w=1920&q=95');
}

.slide:nth-child(2){
background-image:url('https://images.unsplash.com/photo-1494526585095-c41746248156?w=1920&q=95');
}

.slide:nth-child(3){
background-image:url('https://images.unsplash.com/photo-1460317442991-0ec209397118?w=1920&q=95');
}

.slide:nth-child(4){
background-image:url('https://images.unsplash.com/photo-1505693416388-ac5ce068fe85?w=1920&q=95');
}

.slide:nth-child(5){
background-image:url('https://images.unsplash.com/photo-1511818966892-d7d671e672a2?w=1920&q=95');
}

.slide-overlay{
position:fixed;
inset:0;
z-index:1;
background:rgba(2,6,23,0.55);
}

/* NAVBAR */

.navbar{
width:100%;
padding:16px 40px;
display:flex;
justify-content:space-between;
align-items:center;
background:rgba(2,6,23,0.50);
backdrop-filter:blur(18px);
border-bottom:1px solid rgba(255,255,255,.07);
position:sticky;
top:0;
z-index:100;
}

.nav-left{
display:flex;
flex-direction:column;
gap:10px;
}

.nav-logo{
font-size:32px;
font-weight:800;
background:linear-gradient(135deg,#f8d66d,#d78655);
-webkit-background-clip:text;
-webkit-text-fill-color:transparent;
}

/* SUPPORT */

.support-row{
display:flex;
align-items:center;
gap:14px;
}

.support-btn{
display:flex;
align-items:center;
gap:8px;
text-decoration:none;
color:#facc15;
font-size:14px;
font-weight:700;
background:rgba(255,255,255,.06);
padding:8px 16px;
border-radius:12px;
border:1px solid rgba(255,255,255,.08);
transition:.3s;
}

.support-btn:hover{
transform:translateY(-2px);
background:rgba(255,255,255,.10);
}

/* RIGHT */

.nav-right{
display:flex;
align-items:center;
gap:14px;
}

.nav-user{
display:flex;
align-items:center;
gap:10px;
padding:10px 16px;
border-radius:14px;
background:rgba(255,255,255,.07);
border:1px solid rgba(255,255,255,.09);
}

.avatar{
width:36px;
height:36px;
border-radius:50%;
background:linear-gradient(135deg,#3b82f6,#9333ea);
display:flex;
align-items:center;
justify-content:center;
font-size:15px;
font-weight:800;
}

.uname{
font-size:14px;
font-weight:600;
color:rgba(255,255,255,.88);
}

.uemail{
font-size:11px;
color:rgba(255,255,255,.40);
margin-top:1px;
}

.nav-logout{
padding:10px 20px;
background:linear-gradient(135deg,#ef4444,#dc2626);
border-radius:12px;
text-decoration:none;
color:white;
font-size:13px;
font-weight:700;
transition:.25s;
}

.nav-logout:hover{
transform:translateY(-2px);
opacity:.88;
}

/* HERO */

.hero{
position:relative;
z-index:10;
min-height:420px;
display:flex;
align-items:center;
justify-content:center;
text-align:center;
padding:60px 20px 100px;
}

.hero-content{
max-width:860px;
}

.hero-badge{
display:inline-block;
margin-bottom:20px;
padding:8px 20px;
border-radius:999px;
background:rgba(59,130,246,.18);
border:1px solid rgba(59,130,246,.30);
font-size:12px;
font-weight:700;
letter-spacing:1px;
color:#93c5fd;
text-transform:uppercase;
}

.hero h1{
font-size:68px;
font-weight:800;
letter-spacing:-2px;
line-height:1.05;
margin-bottom:16px;
text-shadow:0 4px 40px rgba(0,0,0,.55);
}

.hero h1 span{
background:linear-gradient(135deg,#f8d66d,#fb923c);
-webkit-background-clip:text;
-webkit-text-fill-color:transparent;
}

.hero p{
font-size:18px;
color:rgba(255,255,255,.68);
font-weight:500;
margin-bottom:22px;
}

/* SECTION */

.section{
position:relative;
z-index:10;
max-width:1280px;
margin:auto;
padding:0 32px 70px;
}

/* SECTION TITLE */

.section-title{
font-size:22px;
font-weight:800;
margin-bottom:20px;
color:rgba(255,255,255,.90);
}

/* CARDS */

.cards{
display:grid;
grid-template-columns:repeat(3,1fr);
gap:24px;
}

.card{
padding:36px;
border-radius:26px;
background:rgba(4,10,30,0.55);
border:1px solid rgba(255,255,255,.08);
backdrop-filter:blur(12px);
text-decoration:none;
color:white;
transition:.30s;
box-shadow:0 8px 28px rgba(0,0,0,.30);
position:relative;
overflow:hidden;
}

.card:hover{
transform:translateY(-7px);
}

.card-icon{
font-size:36px;
margin-bottom:16px;
}

.badge{
display:inline-block;
padding:6px 14px;
border-radius:999px;
font-size:11px;
font-weight:700;
margin-bottom:14px;
}

.badge.b1{
background:rgba(59,130,246,.18);
color:#93c5fd;
}

.badge.b2{
background:rgba(16,185,129,.18);
color:#6ee7b7;
}

.badge.b3{
background:rgba(245,158,11,.18);
color:#fcd34d;
}

.card h2{
font-size:24px;
font-weight:800;
margin-bottom:12px;
}

.card p{
font-size:14px;
line-height:1.7;
color:rgba(255,255,255,.55);
}

.card-arrow{
position:absolute;
bottom:24px;
right:24px;
font-size:20px;
}

/* RESPONSIVE */

@media(max-width:1100px){

.cards{
grid-template-columns:1fr;
}

.hero h1{
font-size:52px;
}

}

@media(max-width:700px){

.hero h1{
font-size:38px;
}

.section{
padding:0 16px 50px;
}

.navbar{
padding:14px 20px;
flex-direction:column;
gap:18px;
align-items:flex-start;
}

.nav-logo{
font-size:26px;
}

.nav-right{
width:100%;
justify-content:space-between;
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
<div class="slide"></div>

</div>

<div class="slide-overlay"></div>

<!-- NAVBAR -->

<div class="navbar">

<div class="nav-left">

<div class="nav-logo">
EstateFlow
</div>

<div class="support-row">

<a
href="support.php"
class="support-btn">

🎧 Support

</a>

</div>

</div>

<div class="nav-right">

<div class="nav-user">

<div class="avatar">
<?= strtoupper(substr($userName,0,1)) ?>
</div>

<div>

<div class="uname">
<?= htmlspecialchars($userName) ?>
</div>

<div class="uemail">
<?= htmlspecialchars($_SESSION['user_email']) ?>
</div>

</div>

</div>

<a href="user_logout.php"
class="nav-logout">

🚪 Logout

</a>

</div>

</div>

<!-- HERO -->

<div class="hero">

<div class="hero-content">

<div class="hero-badge">
✦ EstateFlow Platform
</div>

<h1>
Welcome Back,<br>
<span><?= htmlspecialchars($userName) ?></span>
</h1>

<p>
Real Estate Management Platform — Manage your deals in real-time
</p>

</div>

</div>

<!-- SECTION -->

<div class="section">

<div class="section-title">
📋 Your Management Panel
</div>

<div class="cards">

<a href="user_jv_lands.php"
class="card">

<div class="card-icon">
🤝
</div>

<div class="badge b1">
JV MANAGEMENT
</div>

<h2>
Add JV Deals
</h2>

<p>
Submit premium joint venture land details securely into the EstateFlow database system.
</p>

<div class="card-arrow">
→
</div>

</a>

<a href="user_outrate_lands.php"
class="card">

<div class="card-icon">
🏗️
</div>

<div class="badge b2">
OUTRATE MANAGEMENT
</div>

<h2>
Add Outrate Deals
</h2>

<p>
Add and manage outrate property details with a secured submission workflow.
</p>

<div class="card-arrow">
→
</div>

</a>

<a href="user_builders.php"
class="card">

<div class="card-icon">
👷
</div>

<div class="badge b3">
BUILDERS & DEVELOPERS
</div>

<h2>
Add Builders
</h2>

<p>
Register trusted builders and development company information professionally.
</p>

<div class="card-arrow">
→
</div>

</a>

</div>

</div>

<script>

(function(){

var slides=document.querySelectorAll('.slide');

var cur=0;

setInterval(function(){

slides[cur].classList.remove('active');

cur=(cur+1)%slides.length;

slides[cur].classList.add('active');

},6000);

})();

</script>

</body>
</html>