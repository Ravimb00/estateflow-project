<?php

session_start();

include 'config/db.php';
include 'send_mail.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

/* LOGIN CHECK */

if(!isset($_SESSION['user_email'])){
    header("Location:user_login.php");
    exit();
}

/* SUCCESS MESSAGE */

$success = "";
$error   = "";

/* FORM SUBMIT */

if(isset($_POST['add_builder'])){

    $builder_name = mysqli_real_escape_string(
        $conn,
        $_POST['builder_name']
    );

    $preferred_location = mysqli_real_escape_string(
        $conn,
        $_POST['preferred_location']
    );

    $acres_needed = mysqli_real_escape_string(
        $conn,
        $_POST['acres_needed']
    );

    $purpose_type = mysqli_real_escape_string(
        $conn,
        $_POST['purpose_type']
    );

    $jv_type = mysqli_real_escape_string(
        $conn,
        $_POST['jv_type']
    );

    $contact_number = mysqli_real_escape_string(
        $conn,
        $_POST['contact_number']
    );

    $email = mysqli_real_escape_string(
        $conn,
        $_POST['email']
    );

    $status = "Pending";

    /* INSERT */

    $insert = mysqli_query($conn,

    "INSERT INTO builders
    (
        builder_name,
        preferred_location,
        acres_needed,
        purpose_type,
        jv_type,
        contact_number,
        email,
        status
    )

    VALUES
    (
        '$builder_name',
        '$preferred_location',
        '$acres_needed',
        '$purpose_type',
        '$jv_type',
        '$contact_number',
        '$email',
        '$status'
    )"

    );

    if($insert){

        $success = "Builder Requirement Added Successfully";

        /* ADMIN MAIL NOTIFICATION */

        $to = "estateflowofficial@gmail.com";

        $subject = "New Builder Requirement - EstateFlow";

        $body = "

New Builder Requirement Added

Builder Name       : $builder_name
Preferred Location : $preferred_location
Acres Needed       : $acres_needed
Purpose Type       : $purpose_type
JV Type            : $jv_type
Contact Number     : $contact_number
Email              : $email

Added By User:
".$_SESSION['user_email']."

EstateFlow Notification System

";

sendCustomMail($to,$subject,$body);

    }else{

        $error = "Something went wrong";

    }

}
?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport"
content="width=device-width, initial-scale=1.0">

<title>User Builders</title>

<link href="https://fonts.googleapis.com/css2?family=Sora:wght@400;600;700;800&display=swap" rel="stylesheet">

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
border-bottom:1px solid rgba(255,255,255,.05);
position:sticky;
top:0;
z-index:99;
}

.logo{
font-size:34px;
font-weight:800;
color:white;
}

/* FLOAT BUTTONS */

.float-actions{
position:fixed;
right:25px;
bottom:25px;
display:flex;
flex-direction:column;
align-items:flex-end;
gap:12px;
z-index:99999;
}

.logout{
padding:11px 18px;
background:#ef4444;
border-radius:12px;
text-decoration:none;
color:white;
font-size:13px;
font-weight:700;
transition:.3s;
width:180px;
text-align:center;
}

.back-btn{
padding:11px 18px;
background:#3b82f6;
border-radius:12px;
text-decoration:none;
color:white;
font-size:13px;
font-weight:700;
transition:.3s;
width:180px;
text-align:center;
}

.logout:hover,
.back-btn:hover{
transform:translateY(-2px);
opacity:.92;
}

/* HERO */

.hero{
height:260px;
background:
linear-gradient(
rgba(2,6,23,.78),
rgba(2,6,23,.78)
),
url('https://images.unsplash.com/photo-1486406146926-c627a92ad1ab?q=80&w=1600&auto=format&fit=crop')
center/cover no-repeat;

display:flex;
align-items:center;
justify-content:center;
text-align:center;
padding:20px;
}

.hero h1{
font-size:54px;
font-weight:800;
margin-bottom:12px;
}

.hero p{
font-size:16px;
color:#cbd5e1;
}

/* CONTAINER */

.container{
max-width:1000px;
margin:auto;
padding:50px 20px;
}

/* CARD */

.card{
background:rgba(15,23,42,.94);
border:1px solid rgba(255,255,255,.06);
border-radius:28px;
padding:35px;
backdrop-filter:blur(14px);
}

/* TITLE */

.title{
font-size:30px;
font-weight:800;
margin-bottom:30px;
}

/* ALERT */

.success{
background:rgba(34,197,94,.15);
border:1px solid rgba(34,197,94,.3);
padding:14px;
border-radius:14px;
margin-bottom:22px;
font-size:14px;
color:#86efac;
}

.error{
background:rgba(239,68,68,.15);
border:1px solid rgba(239,68,68,.3);
padding:14px;
border-radius:14px;
margin-bottom:22px;
font-size:14px;
color:#fca5a5;
}

/* FORM */

.grid{
display:grid;
grid-template-columns:1fr 1fr;
gap:20px;
}

.field{
display:flex;
flex-direction:column;
}

.field label{
font-size:13px;
margin-bottom:8px;
color:#94a3b8;
}

.field input,
.field select{
padding:15px;
background:rgba(255,255,255,.05);
border:1px solid rgba(255,255,255,.08);
border-radius:14px;
outline:none;
color:white;
font-size:14px;
}

.field input:focus,
.field select:focus{
border-color:#3b82f6;
}

.field select option{
background:#0f172a;
color:white;
}

/* BUTTON */

.btn{
margin-top:30px;
width:100%;
padding:16px;
border:none;
border-radius:16px;
background:linear-gradient(
135deg,
#3b82f6,
#14b8a6
);
font-size:15px;
font-weight:800;
color:white;
cursor:pointer;
transition:.3s;
}

.btn:hover{
transform:translateY(-2px);
opacity:.92;
}

/* WARNING */

.warn{
margin-top:18px;
text-align:center;
font-size:12px;
color:#fbbf24;
}

/* RESPONSIVE */

@media(max-width:700px){

.grid{
grid-template-columns:1fr;
}

.hero h1{
font-size:38px;
}

.navbar{
padding:16px 20px;
}

.logo{
font-size:26px;
}

.float-actions{
right:15px;
bottom:15px;
}

.back-btn,
.logout{
width:150px;
font-size:12px;
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

</div>

<!-- FLOAT BUTTONS -->

<div class="float-actions">

<a href="user_dashboard.php"
class="back-btn">
← Dashboard
</a>

<a href="logout.php"
class="logout">
Logout
</a>

</div>

<!-- HERO -->

<div class="hero">

<div>

<h1>
Builders Requirements
</h1>

<p>
Submit Builder Requirements securely
</p>

</div>

</div>

<!-- CONTAINER -->

<div class="container">

<div class="card">

<div class="title">
Add Builder Requirement
</div>

<?php if($success!=""){ ?>

<div class="success">
<?= $success ?>
</div>

<?php } ?>

<?php if($error!=""){ ?>

<div class="error">
<?= $error ?>
</div>

<?php } ?>

<form method="POST">

<div class="grid">

<div class="field">

<label>
Builder Name
</label>

<input
type="text"
name="builder_name"
required>

</div>

<div class="field">

<label>
Preferred Location
</label>

<input
type="text"
name="preferred_location"
required>

</div>

<div class="field">

<label>
Acres Needed
</label>

<input
type="number"
name="acres_needed"
required>

</div>

<div class="field">

<label>
Purpose Type
</label>

<select
name="purpose_type"
id="purpose_type"
required
onchange="toggleJVType()"
>

<option value="">
Select Purpose
</option>

<option value="JV">
JV
</option>

<option value="Outrate">
Outrate
</option>

</select>

</div>

<div class="field"
id="jv_type_box"
style="display:none;">

<label>
JV Type
</label>

<select
name="jv_type"
>

<option value="">
Select JV Type
</option>

<option value="JV Apartment">
JV Apartment
</option>

<option value="JV Villa">
JV Villa
</option>

<option value="JV Layout">
JV Layout
</option>

</select>

</div>

<div class="field">

<label>
Contact Number
</label>

<input
type="text"
name="contact_number"
required>

</div>

<div class="field">

<label>
Email
</label>

<input
type="email"
name="email"
required>

</div>

</div>

<button
type="submit"
name="add_builder"
class="btn"

onclick="
return confirm(
'Are you sure you want to add this Builder Requirement?'
)
">

Add Requirement

</button>

<div class="warn">
Please verify all details before submission
</div>

</form>

</div>

</div>

<script>

function toggleJVType(){

let purpose =
document.getElementById('purpose_type').value;

let jvBox =
document.getElementById('jv_type_box');

if(purpose == "JV"){

jvBox.style.display = "block";

}else{

jvBox.style.display = "none";

}

}

</script>

</body>
</html>