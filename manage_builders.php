<?php

error_reporting(E_ALL);
ini_set('display_errors',1);

session_start();

include 'config/db.php';
include 'mail_config.php';

/* ADMIN CHECK */

if(!isset($_SESSION['admin'])){

header("Location:admin_login.php");
exit();

}

/* AVAILABLE */

if(isset($_GET['available'])){

$id = $_GET['available'];

/* GET BUILDER */

$getBuilder = mysqli_fetch_assoc(

mysqli_query(

$conn,

"SELECT * FROM builders
WHERE id='$id'"

)

);

/* UPDATE STATUS */

mysqli_query(

$conn,

"UPDATE builders
SET status='available'
WHERE id='$id'"

);

/* GET USER */

$user_id = $getBuilder['user_id'];

$getUser = mysqli_fetch_assoc(

mysqli_query(

$conn,

"SELECT * FROM users
WHERE id='$user_id'"

)

);

$userEmail = $getUser['email'];

$userName  = $getUser['name'] ?? "Customer";

/* SEND MAIL */

$subject = "EstateFlow Builder Availability Approved";

$body = "

<h2 style='color:#10b981'>
Greetings from EstateFlow 🎉
</h2>

<p>
Dear <b>".$userName."</b>,
</p>

<p>
We are pleased to inform you that your Builder request has been marked as <b>Available</b> successfully by the EstateFlow Management Team.
</p>

<p>

<b>Builder Name:</b>
".$getBuilder['builder_name']." <br><br>

<b>Location:</b>
".$getBuilder['location']." <br><br>

<b>Status:</b>
Available ✅

</p>

<p>
Kindly visit our office along with your company profile and required original documents for further onboarding and verification process.
</p>

<p>

<b>Required Documents:</b>

<ul>
<li>Company Profile</li>
<li>Business Registration</li>
<li>ID Proof</li>
<li>Previous Project Details</li>
</ul>

</p>

<p>
Thank you for choosing EstateFlow.
We look forward to working with you.
</p>

";

sendMail(
$userEmail,
$subject,
$body
);

header("Location:manage_builders.php");
exit();

}

/* UNAVAILABLE */

if(isset($_GET['unavailable'])){

$id = $_GET['unavailable'];

/* GET BUILDER */

$getBuilder = mysqli_fetch_assoc(

mysqli_query(

$conn,

"SELECT * FROM builders
WHERE id='$id'"

)

);

/* UPDATE STATUS */

mysqli_query(

$conn,

"UPDATE builders
SET status='unavailable'
WHERE id='$id'"

);

/* GET USER */

$user_id = $getBuilder['user_id'];

$getUser = mysqli_fetch_assoc(

mysqli_query(

$conn,

"SELECT * FROM users
WHERE id='$user_id'"

)

);

$userEmail = $getUser['email'];

$userName  = $getUser['name'] ?? "Customer";

/* SEND MAIL */

$subject = "EstateFlow Builder Status Update";

$body = "

<h2 style='color:#ef4444'>
Greetings from EstateFlow
</h2>

<p>
Dear <b>".$userName."</b>,
</p>

<p>
Thank you for your interest and association with EstateFlow.
</p>

<p>
Currently your Builder request has been marked as <b>Unavailable</b> by the EstateFlow Management Team based on our present operational requirements and verification review.
</p>

<p>

<b>Builder Name:</b>
".$getBuilder['builder_name']." <br><br>

<b>Location:</b>
".$getBuilder['location']." <br><br>

<b>Status:</b>
Unavailable ❌

</p>

<p>
You may reconnect with us in future for upcoming opportunities and onboarding updates.
</p>

<p>
For any clarification or support, please contact EstateFlow Support Team.
</p>

<p>
Thank you for your understanding and continued support.
</p>

";

sendMail(
$userEmail,
$subject,
$body
);

header("Location:manage_builders.php");
exit();

}

/* DELETE */

if(isset($_GET['delete'])){

$id = $_GET['delete'];

mysqli_query(
$conn,
"DELETE FROM builders
WHERE id='$id'"
);

header("Location:manage_builders.php");
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
Manage Builders
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

background:
linear-gradient(
rgba(2,6,23,.65),
rgba(2,6,23,.72)
),

url('https://images.unsplash.com/photo-1460317442991-0ec209397118?q=80&w=1920&auto=format&fit=crop');

background-size:cover;
background-position:center;
background-attachment:fixed;

min-height:100vh;

padding:40px;

color:white;

}

/* TITLE */

.title{

font-size:56px;
font-weight:800;

margin-bottom:35px;

}

/* TOP */

.top{

display:flex;
justify-content:space-between;
align-items:center;

margin-bottom:25px;

}

/* DASHBOARD BTN */

.dashboard-btn{

text-decoration:none;

padding:14px 24px;

border-radius:14px;

background:linear-gradient(
135deg,
#3b82f6,
#9333ea
);

color:white;
font-weight:700;

}

/* TABLE */

.table-box{

background:rgba(255,255,255,.08);

border:1px solid rgba(255,255,255,.08);

backdrop-filter:blur(18px);

border-radius:28px;

padding:30px;

overflow:auto;

}

table{

width:100%;

border-collapse:separate;
border-spacing:0;

min-width:1200px;

}

th{

text-align:left;

padding:0 16px 18px 16px;

color:#94a3b8;

font-size:14px;

border-right:1px solid rgba(255,255,255,.08);

}

td{

padding:22px 16px;

border-top:1px solid rgba(255,255,255,.06);

border-right:1px solid rgba(255,255,255,.06);

font-size:14px;

vertical-align:middle;

}

th:last-child,
td:last-child{

border-right:none;

}

/* STATUS */

.available{

background:#10b981;

padding:8px 14px;

border-radius:20px;

font-size:12px;

font-weight:700;

display:inline-block;

}

.unavailable{

background:#ef4444;

padding:8px 14px;

border-radius:20px;

font-size:12px;

font-weight:700;

display:inline-block;

}

/* BUTTONS */

.action-btn{

text-decoration:none;

padding:7px 12px;

border-radius:10px;

font-size:11px;

font-weight:700;

margin-right:6px;

display:inline-block;

margin-top:8px;

}

.available-btn{

background:#10b981;
color:white;

}

.unavailable-btn{

background:#f59e0b;
color:white;

}

.delete-btn{

background:#ef4444;
color:white;

}

/* MOBILE */

@media(max-width:900px){

.title{
font-size:42px;
}

}

</style>

</head>

<body>

<div class="top">

<div class="title">
Builders
</div>

<a
href="admin_dashboard.php"
class="dashboard-btn">

Dashboard

</a>

</div>

<div class="table-box">

<table>

<tr>

<th>ID</th>

<th>Builder</th>

<th>Location</th>

<th>Purpose</th>

<th>Acres Needed</th>

<th>Status</th>

<th>Action</th>

</tr>

<?php

$getData = mysqli_query(
$conn,
"SELECT * FROM builders
ORDER BY id DESC"
);

while($row = mysqli_fetch_assoc($getData)){

?>

<tr>

<td>
<?php echo $row['id']; ?>
</td>

<td>
<?php echo $row['builder_name']; ?>
</td>

<td>
<?php echo $row['location']; ?>
</td>

<td>
<?php echo $row['purpose_type']; ?>
</td>

<td>
<?php echo $row['acres_needed']; ?>
</td>

<td>

<?php

if($row['status']=="available"){

echo "<span class='available'>
Available
</span>";

}else{

echo "<span class='unavailable'>
Unavailable
</span>";

}

?>

</td>

<td>

<a
class="action-btn available-btn"
href="?available=<?php echo $row['id']; ?>">

Available

</a>

<a
class="action-btn unavailable-btn"
href="?unavailable=<?php echo $row['id']; ?>">

Unavailable

</a>

<a
class="action-btn delete-btn"
href="?delete=<?php echo $row['id']; ?>">

Delete

</a>

</td>

</tr>

<?php } ?>

</table>

</div>

</body>
</html>