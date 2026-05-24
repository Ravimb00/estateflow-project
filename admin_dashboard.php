<?php

error_reporting(E_ALL);
ini_set('display_errors',1);

session_start();

include 'config/db.php';

if(!isset($_SESSION['admin'])){
    header("Location:admin_login.php");
    exit();
}

/* COUNTS */

$userCount = mysqli_num_rows(
    mysqli_query($conn,"SELECT * FROM users")
);

$jvApproved = mysqli_num_rows(
    mysqli_query($conn,"SELECT * FROM jv_lands WHERE status='approved'")
);

$outrateApproved = mysqli_num_rows(
    mysqli_query($conn,"SELECT * FROM outrate_lands WHERE status='approved'")
);

$builderCount = mysqli_num_rows(
    mysqli_query($conn,"SELECT * FROM builders")
);

/* ADMIN DATA */

$getAdmin = mysqli_query(
    $conn,
    "SELECT * FROM admin LIMIT 1"
);

$admin = mysqli_fetch_assoc($getAdmin);

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport"
content="width=device-width, initial-scale=1.0">

<title>
EstateFlow Premium Admin
</title>

<link
href="https://fonts.googleapis.com/css2?family=Sora:wght@400;500;600;700;800&display=swap"
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
min-height:100vh;
overflow-x:hidden;
color:white;
display:flex;
}

/* BACKGROUND */

.slide-bg{
position:fixed;
top:0;
left:0;
width:100%;
height:100%;
z-index:0;
}

.slide{
position:absolute;
inset:0;
background-size:cover;
background-position:center;
background-repeat:no-repeat;
opacity:0;
transition:opacity 2s ease;
animation:zoom 14s linear infinite;
}

.slide.active{
opacity:1;
}

@keyframes zoom{
0%{
transform:scale(1);
}
100%{
transform:scale(1.08);
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

.overlay{
position:fixed;
inset:0;
background:rgba(2,6,23,.45);
z-index:1;
}

/* SIDEBAR */

.sidebar{

width:250px;
height:100vh;

position:fixed;
left:0;
top:0;

z-index:100;

padding:28px 18px;

display:flex;
flex-direction:column;
justify-content:space-between;

background:rgba(2,6,23,.55);

backdrop-filter:blur(18px);

border-right:1px solid rgba(255,255,255,.06);

}

.logo{

font-size:34px;
font-weight:800;

margin-bottom:40px;

background:
linear-gradient(
135deg,
#f8d66d,
#d78655
);

-webkit-background-clip:text;
-webkit-text-fill-color:transparent;

}

.menu{
display:flex;
flex-direction:column;
gap:8px;
}

.menu a{

text-decoration:none;

padding:14px 16px;

border-radius:14px;

font-size:14px;
font-weight:600;

color:rgba(255,255,255,.72);

transition:.3s;

}

.menu a:hover{

background:rgba(255,255,255,.08);

color:white;

}

.menu .active{

background:
linear-gradient(
135deg,
#3b82f6,
#9333ea
);

color:white;

}

/* LOGOUT */

.logout{

display:flex;
justify-content:center;
align-items:center;

padding:14px;

border-radius:14px;

text-decoration:none;

background:
linear-gradient(
135deg,
#ef4444,
#dc2626
);

font-weight:700;

color:white;

}

/* MAIN */

.main{

margin-left:250px;

width:calc(100% - 250px);

padding:30px;

position:relative;
z-index:10;

}

/* TOPBAR */

.topbar{

display:flex;
justify-content:space-between;
align-items:center;

gap:16px;

margin-bottom:28px;

}

/* WELCOME */

.welcome-box{

flex:1;

padding:24px;

border-radius:22px;

background:
linear-gradient(
135deg,
rgba(109,60,200,.55),
rgba(56,108,220,.35)
);

border:1px solid rgba(255,255,255,.10);

backdrop-filter:blur(10px);

}

.welcome-box h2{

font-size:24px;
font-weight:800;

margin-bottom:6px;

}

.welcome-box p{

font-size:13px;

color:rgba(255,255,255,.72);

}

/* RIGHT */

.right-top{

display:flex;
align-items:center;
gap:14px;

}

/* TIME */

.time-box{

padding:14px 18px;

border-radius:18px;

background:rgba(2,6,23,.35);

border:1px solid rgba(255,255,255,.08);

backdrop-filter:blur(10px);

}

.t-date{

font-size:11px;

color:rgba(255,255,255,.55);

margin-bottom:4px;

}

.t-time{

font-size:22px;
font-weight:800;

}

/* ADMIN */

.admin-box{

display:flex;
align-items:center;

gap:12px;

padding:12px 16px;

border-radius:18px;

background:rgba(2,6,23,.35);

border:1px solid rgba(255,255,255,.08);

backdrop-filter:blur(10px);

}

.admin-box img{

width:46px;
height:46px;

border-radius:50%;

object-fit:cover;

border:2px solid rgba(255,255,255,.15);

}

.a-name{

font-size:15px;
font-weight:700;

}

.a-email{

font-size:11px;

color:rgba(255,255,255,.55);

margin-top:3px;

}

/* TITLE */

.main-title h1{

font-size:42px;
font-weight:800;

margin-bottom:8px;

text-shadow:0 2px 30px rgba(0,0,0,.45);

}

.main-title p{

font-size:14px;

color:rgba(255,255,255,.70);

}

/* CARDS */

.cards{

display:grid;

grid-template-columns:repeat(4,1fr);

gap:18px;

margin-top:26px;

}

.card{

height:150px;

padding:22px;

border-radius:22px;

display:flex;
flex-direction:column;
justify-content:center;

border:1px solid rgba(255,255,255,.08);

backdrop-filter:blur(8px);

transition:.3s;

box-shadow:0 6px 20px rgba(0,0,0,.25);

}

.card:hover{

transform:translateY(-5px);

}

.c-label{

font-size:14px;
font-weight:700;

margin-bottom:12px;

}

.c-value{

font-size:54px;
font-weight:800;

line-height:1;

}

.green{
background:
linear-gradient(
135deg,
rgba(34,197,94,.35),
rgba(0,80,40,.45)
);
}

.blue{
background:
linear-gradient(
135deg,
rgba(59,130,246,.35),
rgba(30,64,175,.45)
);
}

.purple{
background:
linear-gradient(
135deg,
rgba(168,85,247,.35),
rgba(91,33,182,.45)
);
}

.orange{
background:
linear-gradient(
135deg,
rgba(251,146,60,.35),
rgba(154,52,18,.45)
);
}

/* BOTTOM */

.bottom-grid{

display:grid;

grid-template-columns:2fr 1fr;

gap:20px;

margin-top:28px;

}

.glass-panel{

background:rgba(4,10,30,.35);

padding:26px;

border-radius:22px;

border:1px solid rgba(255,255,255,.08);

backdrop-filter:blur(10px);

}

.glass-panel h3{

font-size:20px;
font-weight:700;

margin-bottom:18px;

}

/* TABLE */

table{
width:100%;
border-collapse:collapse;
}

table th{

text-align:left;

padding-bottom:12px;

font-size:12px;

color:rgba(255,255,255,.45);

border-bottom:1px solid rgba(255,255,255,.06);

}

table td{

padding:14px 0;

font-size:13px;

border-bottom:1px solid rgba(255,255,255,.05);

}

.badge{

background:rgba(16,185,129,.18);

color:#34d399;

padding:5px 12px;

border-radius:20px;

font-size:11px;
font-weight:700;

}

/* ACTIVITY */

.activity-item{

display:flex;
align-items:center;

gap:12px;

padding:14px 0;

border-bottom:1px solid rgba(255,255,255,.05);

font-size:13px;

}

.activity-dot{

width:8px;
height:8px;

border-radius:50%;

}

/* RESPONSIVE */

@media(max-width:1200px){

.cards{
grid-template-columns:repeat(2,1fr);
}

}

@media(max-width:1000px){

.sidebar{
display:none;
}

.main{
margin-left:0;
width:100%;
}

.bottom-grid{
grid-template-columns:1fr;
}

.topbar{
flex-direction:column;
align-items:flex-start;
}

}

@media(max-width:700px){

.cards{
grid-template-columns:1fr;
}

.main-title h1{
font-size:30px;
}

}

</style>

</head>

<body>

<!-- BACKGROUND -->

<div class="slide-bg">

<div class="slide active"></div>
<div class="slide"></div>
<div class="slide"></div>
<div class="slide"></div>
<div class="slide"></div>

</div>

<div class="overlay"></div>

<!-- SIDEBAR -->

<div class="sidebar">

<div>

<div class="logo">
EstateFlow
</div>

<div class="menu">

<a
href="admin_dashboard.php"
class="active">

🏠 Dashboard

</a>

<a href="users.php">
👥 Users
</a>

<a href="manage_jv.php">
🏢 JV Lands
</a>

<a href="manage_outrate.php">
🤝 Outrate Lands
</a>

<a href="manage_builders.php">
👷 Builders
</a>

<a href="settings.php">
⚙️ Settings
</a>

</div>

</div>

<a
href="admin_logout.php"
class="logout">

🚪 Logout

</a>

</div>

<!-- MAIN -->

<div class="main">

<!-- TOPBAR -->

<div class="topbar">

<div class="welcome-box">

<h2>
Welcome Back,
<?php echo $admin['name']; ?> 👋
</h2>

<p>
Manage users, approvals and builder activities in real-time
</p>

</div>

<div class="right-top">

<div class="time-box">

<div class="t-date" id="date"></div>

<div class="t-time" id="time"></div>

</div>

<div class="admin-box">

<img
src="<?php echo $admin['photo']; ?>"
alt="admin">

<div>

<div class="a-name">
<?php echo $admin['name']; ?>
</div>

<div class="a-email">
<?php echo $admin['email']; ?>
</div>

</div>

</div>

</div>

</div>

<!-- TITLE -->

<div class="main-title">

<h1>
EstateFlow Dashboard
</h1>

<p>
Here's what's happening with EstateFlow today.
</p>

</div>

<!-- CARDS -->

<div class="cards">

<div class="card green">

<div class="c-label">
Total Users
</div>

<div class="c-value">
<?php echo $userCount; ?>
</div>

</div>

<div class="card blue">

<div class="c-label">
JV Lands
</div>

<div class="c-value">
<?php echo $jvApproved; ?>
</div>

</div>

<div class="card purple">

<div class="c-label">
Outrate Lands
</div>

<div class="c-value">
<?php echo $outrateApproved; ?>
</div>

</div>

<div class="card orange">

<div class="c-label">
Builders
</div>

<div class="c-value">
<?php echo $builderCount; ?>
</div>

</div>

</div>

<!-- BOTTOM -->

<div class="bottom-grid">

<div class="glass-panel">

<h3>
Latest Registered Users
</h3>

<table>

<tr>

<th>User</th>
<th>Email</th>
<th>Status</th>

</tr>

<?php

$getUsers = mysqli_query(
$conn,
"SELECT * FROM users
ORDER BY id DESC
LIMIT 5"
);

while($row = mysqli_fetch_assoc($getUsers)){

?>

<tr>

<td>
<?php
echo htmlspecialchars(
$row['first_name']." ".$row['last_name']
);
?>
</td>

<td>
<?php
echo htmlspecialchars($row['email']);
?>
</td>

<td>
<span class="badge">
✓ Verified
</span>
</td>

</tr>

<?php } ?>

</table>

</div>

<div class="glass-panel">

<h3>
Recent Activities
</h3>

<div class="activity-item">
<div class="activity-dot" style="background:#f59e0b"></div>
EstateFlow Admin Panel Live
</div>

<div class="activity-item">
<div class="activity-dot" style="background:#3b82f6"></div>
JV Approval System Active
</div>

<div class="activity-item">
<div class="activity-dot" style="background:#12b886"></div>
Outrate Approval System Active
</div>

<div class="activity-item">
<div class="activity-dot" style="background:#9333ea"></div>
Builder Management Active
</div>

<div class="activity-item">
<div class="activity-dot" style="background:#34d399"></div>
User Verification Enabled
</div>

</div>

</div>

</div>

<script>

/* CLOCK */

function updateClock(){

var now = new Date();

document.getElementById('date')
.textContent =
now.toLocaleDateString(
'en-US',
{
weekday:'long',
month:'long',
day:'numeric',
year:'numeric'
}
);

document.getElementById('time')
.textContent =
now.toLocaleTimeString(
'en-IN',
{
hour:'2-digit',
minute:'2-digit',
second:'2-digit'
}
);

}

setInterval(updateClock,1000);

updateClock();

/* SLIDESHOW */

(function(){

var slides =
document.querySelectorAll('.slide');

var current = 0;

setInterval(function(){

slides[current]
.classList.remove('active');

current =
(current + 1)
% slides.length;

slides[current]
.classList.add('active');

},6000);

})();

</script>

</body>
</html>