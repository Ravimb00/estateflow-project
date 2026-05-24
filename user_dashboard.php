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
EstateFlow User Dashboard
</title>

<link href="https://fonts.googleapis.com/css2?family=Sora:wght@400;500;600;700;800&display=swap" rel="stylesheet">

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
}

/* NAVBAR */

.navbar{
width:100%;
padding:18px 40px;
display:flex;
justify-content:space-between;
align-items:center;
background:rgba(15,23,42,.92);
backdrop-filter:blur(12px);
border-bottom:1px solid rgba(255,255,255,.06);
position:sticky;
top:0;
z-index:999;
}

.logo{
font-size:34px;
font-weight:800;
color:white;
}

.right{
display:flex;
align-items:center;
gap:16px;
}

.user{
font-size:14px;
color:#cbd5e1;
}

.logout{
padding:10px 18px;
background:#ef4444;
border-radius:12px;
text-decoration:none;
color:white;
font-size:13px;
font-weight:600;
transition:.3s;
}

.logout:hover{
opacity:.9;
transform:translateY(-2px);
}

/* HERO */

.hero{
height:460px;

background:
linear-gradient(
rgba(2,6,23,.70),
rgba(2,6,23,.78)
),

url('https://images.unsplash.com/photo-1511818966892-d7d671e672a2?q=80&w=1800&auto=format&fit=crop')

center/cover no-repeat;

display:flex;
justify-content:center;
align-items:center;
text-align:center;
padding:20px;
}

.hero-content{
max-width:900px;
}

.hero h1{
font-size:74px;
font-weight:800;
margin-bottom:18px;
line-height:1.1;
}

.hero p{
font-size:20px;
color:#cbd5e1;
font-weight:500;
}

#clock{
margin-top:20px;
font-size:19px;
font-weight:600;
color:#e2e8f0;
}

/* CARDS */

.cards{
width:100%;
max-width:1300px;
margin:auto;
margin-top:-70px;
padding:0 30px 60px;
display:grid;
grid-template-columns:repeat(3,1fr);
gap:28px;
position:relative;
z-index:10;
}

.card{
background:rgba(15,23,42,.94);
border:1px solid rgba(255,255,255,.07);
border-radius:28px;
padding:38px;
text-decoration:none;
color:white;
transition:.35s;
backdrop-filter:blur(12px);
box-shadow:
0 10px 30px rgba(0,0,0,.35);
position:relative;
overflow:hidden;
}

.card::before{
content:'';
position:absolute;
top:-80px;
right:-80px;
width:180px;
height:180px;
background:rgba(59,130,246,.12);
border-radius:50%;
transition:.4s;
}

.card:hover::before{
transform:scale(1.4);
}

.card:hover{
transform:
translateY(-8px)
scale(1.02);

border-color:
rgba(59,130,246,.45);

box-shadow:
0 18px 40px rgba(0,0,0,.45);
}

.card h2{
font-size:30px;
font-weight:800;
margin-bottom:14px;
position:relative;
z-index:2;
}

.card p{
font-size:15px;
line-height:1.7;
color:#94a3b8;
position:relative;
z-index:2;
}

/* BADGE */

.badge{
display:inline-block;
padding:8px 14px;
background:
linear-gradient(
135deg,
#3b82f6,
#14b8a6
);

border-radius:999px;
font-size:12px;
font-weight:700;
margin-bottom:20px;
position:relative;
z-index:2;
}

/* RESPONSIVE */

@media(max-width:1100px){

.cards{
grid-template-columns:1fr;
max-width:700px;
}

.hero h1{
font-size:58px;
}

}

@media(max-width:700px){

.navbar{
padding:16px 20px;
}

.logo{
font-size:26px;
}

.hero{
height:420px;
}

.hero h1{
font-size:42px;
}

.hero p{
font-size:16px;
}

#clock{
font-size:15px;
}

.cards{
padding:0 20px 50px;
margin-top:-50px;
}

.card{
padding:28px;
}

.card h2{
font-size:24px;
}

}

</style>

</head>

<body>

<!-- NAVBAR -->

<div class="navbar">

<div class="logo">
EstateFlow
</div>

<div class="right">

<div class="user">
Welcome,
<?= $_SESSION['user_email'] ?>
</div>

<a href="user_logout.php"
class="logout">
Logout
</a>

</div>

</div>

<!-- HERO -->

<div class="hero">

<div class="hero-content">

<h1>
EstateFlow Dashboard
</h1>

<p>
Real Estate Management Platform
</p>

<div id="clock"></div>

</div>

</div>

<!-- CARDS -->

<div class="cards">

<a href="user_jv_lands.php"
class="card">

<div class="badge">
JV MANAGEMENT
</div>

<h2>
Add JV Deals
</h2>

<p>
Submit premium joint venture land details securely into EstateFlow database system.
</p>

</a>

<a href="user_outrate_lands.php"
class="card">

<div class="badge">
OUTRATE MANAGEMENT
</div>

<h2>
Add Outrate Deals
</h2>

<p>
Add and manage outrate property details with secured submission workflow.
</p>

</a>

<a href="user_builders.php"
class="card">

<div class="badge">
BUILDERS & DEVELOPERS
</div>

<h2>
Add Builders & Developers
</h2>

<p>
Register trusted builders and development company information professionally.
</p>

</a>

</div>

<!-- CLOCK -->

<script>

function updateClock(){

const now = new Date();

const date = now.toLocaleDateString(
'en-IN',
{
day:'2-digit',
month:'long',
year:'numeric'
}
);

const time = now.toLocaleTimeString(
'en-IN',
{
hour:'2-digit',
minute:'2-digit',
second:'2-digit'
}
);

document.getElementById('clock').innerHTML =
date + " | " + time;

}

setInterval(updateClock,1000);

updateClock();

</script>

</body>
</html>