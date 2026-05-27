<?php

session_start();

include 'config/db.php';

if(!isset($_SESSION['user_email'])){
header("Location:user_login.php");
exit();
}

$msg = "";

if(isset($_POST['add_builder'])){

$user_id = $_SESSION['user_id'];

$builder_name = mysqli_real_escape_string(
$conn,
$_POST['builder_name']
);

$location = mysqli_real_escape_string(
$conn,
$_POST['location']
);

$contact = mysqli_real_escape_string(
$conn,
$_POST['contact']
);

$email = mysqli_real_escape_string(
$conn,
$_POST['email']
);

$deal_type = mysqli_real_escape_string(
$conn,
$_POST['deal_type']
);

$project_type = mysqli_real_escape_string(
$conn,
$_POST['project_type']
);

$insert = mysqli_query(

$conn,

"INSERT INTO builders(
user_id,
builder_name,
location,
contact,
email,
deal_type,
project_type,
status
)

VALUES(

'$user_id',
'$builder_name',
'$location',
'$contact',
'$email',
'$deal_type',
'$project_type',
'pending'

)"

);

if($insert){

$msg = "Builder Added Successfully";

}else{

$msg = "Failed To Add Builder";

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
User Builders
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
color:white;
min-height:100vh;

}

/* NAVBAR */

.navbar{

padding:24px 50px;

background:rgba(2,6,23,.85);

backdrop-filter:blur(18px);

display:flex;
justify-content:space-between;
align-items:center;

}

.logo{

font-size:48px;
font-weight:800;

background:linear-gradient(
135deg,
#f8d66d,
#fb923c
);

-webkit-background-clip:text;
-webkit-text-fill-color:transparent;

}

/* HERO */

.hero{

height:240px;

background:
linear-gradient(
rgba(2,6,23,.72),
rgba(2,6,23,.82)
),

url('https://images.unsplash.com/photo-1460317442991-0ec209397118?q=80&w=1920&auto=format&fit=crop');

background-size:cover;
background-position:center;

display:flex;
flex-direction:column;
justify-content:center;
align-items:center;

text-align:center;

}

.hero h1{

font-size:62px;
font-weight:800;

margin-bottom:10px;

}

.hero p{

font-size:18px;

color:rgba(255,255,255,.72);

}

/* FORM BOX */

.container{

max-width:980px;

margin:50px auto;

padding:0 20px;

}

.form-box{

background:rgba(255,255,255,.05);

border:1px solid rgba(255,255,255,.08);

backdrop-filter:blur(18px);

border-radius:30px;

padding:40px;

}

.form-title{

font-size:48px;
font-weight:800;

margin-bottom:30px;

}

.msg{

padding:14px;

border-radius:14px;

background:rgba(34,197,94,.18);

margin-bottom:20px;

font-size:14px;

text-align:center;

}

.form-grid{

display:grid;

grid-template-columns:1fr 1fr;

gap:22px;

}

.input-box{

display:flex;
flex-direction:column;

}

.input-box label{

margin-bottom:10px;

font-size:15px;

color:#cbd5e1;

}

.input-box input,
.input-box select{

padding:18px;

border:none;
outline:none;

border-radius:16px;

background:rgba(255,255,255,.06);

border:1px solid rgba(255,255,255,.08);

color:white;

font-size:15px;

}

.full{

grid-column:1/3;

}

/* BUTTON */

.submit-btn{

margin-top:28px;

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

font-size:17px;

font-weight:700;

cursor:pointer;

transition:.3s;

}

.submit-btn:hover{

transform:translateY(-2px);

}

.note{

text-align:center;

margin-top:18px;

font-size:13px;

color:#fbbf24;

}

/* SIDE BTNS */

.side-btns{

position:fixed;

right:30px;
bottom:30px;

display:flex;
flex-direction:column;

gap:14px;

}

.side-btn{

padding:15px 28px;

border-radius:16px;

text-decoration:none;

font-weight:700;

color:white;

text-align:center;

}

.dashboard{

background:#3b82f6;

}

.logout{

background:#ef4444;

}

/* MOBILE */

@media(max-width:800px){

.form-grid{
grid-template-columns:1fr;
}

.full{
grid-column:auto;
}

.hero h1{
font-size:42px;
}

.logo{
font-size:36px;
}

}

</style>

</head>

<body>

<div class="navbar">

<div class="logo">
EstateFlow
</div>

</div>

<div class="hero">

<h1>
Builders & Developers
</h1>

<p>
Submit Builder and Developer details securely
</p>

</div>

<div class="container">

<div class="form-box">

<div class="form-title">
Add Builder
</div>

<?php if($msg!=""){ ?>

<div class="msg">
<?php echo $msg; ?>
</div>

<?php } ?>

<form method="POST">

<div class="form-grid">

<div class="input-box">

<label>
Builder Name
</label>

<input
type="text"
name="builder_name"
required>

</div>

<div class="input-box">

<label>
Location
</label>

<input
type="text"
name="location"
required>

</div>

<div class="input-box">

<label>
Contact Number
</label>

<input
type="text"
name="contact"
required>

</div>

<div class="input-box">

<label>
Email
</label>

<input
type="email"
name="email"
required>

</div>

<div class="input-box">

<label>
JV or Outrate
</label>

<select
name="deal_type"
id="dealType"
required
onchange="toggleProjectType()">

<option value="">
Select
</option>

<option value="JV">
JV
</option>

<option value="Outrate">
Outrate
</option>

</select>

</div>

<div
class="input-box"
id="projectTypeBox"
style="display:none;">

<label>
JV Type
</label>

<select
name="project_type">

<option value="">
Select JV Type
</option>

<option value="Villa">
Villa
</option>

<option value="Apartment">
Apartment
</option>

<option value="Layout">
Layout
</option>

</select>

</div>

</div>

<button
type="submit"
name="add_builder"
class="submit-btn">

Add Builder

</button>

<div class="note">
Please verify all details before submission
</div>

</form>

</div>

</div>

<div class="side-btns">

<a
href="user_dashboard.php"
class="side-btn dashboard">

← Dashboard

</a>

<a
href="user_logout.php"
class="side-btn logout">

Logout

</a>

</div>

<script>

function toggleProjectType(){

var dealType =
document.getElementById('dealType').value;

var projectBox =
document.getElementById('projectTypeBox');

if(dealType=="JV"){

projectBox.style.display="block";

}else{

projectBox.style.display="none";

}

}

</script>

</body>
</html>