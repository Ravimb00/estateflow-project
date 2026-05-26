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

/* APPROVE */

if(isset($_GET['approve'])){

$id = $_GET['approve'];

/* GET LAND DETAILS */

$getLand = mysqli_fetch_assoc(

mysqli_query(

$conn,

"SELECT * FROM outrate_lands
WHERE id='$id'"

)

);

/* APPROVE LAND */

mysqli_query(

$conn,

"UPDATE outrate_lands
SET status='approved'
WHERE id='$id'"

);

/* GET USER */

$user_id = $getLand['user_id'];

$getUser = mysqli_fetch_assoc(

mysqli_query(

$conn,

"SELECT * FROM users
WHERE id='$user_id'"

)

);

$userEmail = $getUser['email'];

/* SEND MAIL */

$subject = "EstateFlow Property Approval Confirmation";

$body = "

<h2>Hello from EstateFlow 👋</h2>

<p>
Dear Customer,
</p>

<p>
We are pleased to inform you that your submitted Outrate Land request has been successfully approved by the EstateFlow Management Team.
</p>

<p>

<b>Land Name:</b>
".$getLand['land_name']." <br>

<b>Location:</b>
".$getLand['location']." <br>

<b>Status:</b>
Approved ✅

</p>

<p>
Our team may contact you shortly regarding the next process and verification steps.
</p>

<p>
Thank you for choosing EstateFlow.
</p>

<p>
Warm Regards,<br>
<b>EstateFlow Management Team</b>
</p>

";

sendMail(
$userEmail,
$subject,
$body
);

header("Location:manage_outrate.php");

exit();

}

/* REJECT */

if(isset($_GET['reject'])){

$id = $_GET['reject'];

/* GET LAND DETAILS */

$getLand = mysqli_fetch_assoc(

mysqli_query(

$conn,

"SELECT * FROM outrate_lands
WHERE id='$id'"

)

);

/* REJECT LAND */

mysqli_query(

$conn,

"UPDATE outrate_lands
SET status='Rejected'
WHERE id='$id'"

);

/* GET USER */

$user_id = $getLand['user_id'];

$getUser = mysqli_fetch_assoc(

mysqli_query(

$conn,

"SELECT * FROM users
WHERE id='$user_id'"

)

);

$userEmail = $getUser['email'];

/* SEND MAIL */

$subject = "EstateFlow Property Status Update";

$body = "

<h2>Hello from EstateFlow 👋</h2>

<p>
Dear Customer,
</p>

<p>
We regret to inform you that your submitted Outrate Land request could not be approved by the EstateFlow Management Team at this time.
</p>

<p>

<b>Land Name:</b>
".$getLand['land_name']." <br>

<b>Location:</b>
".$getLand['location']." <br>

<b>Status:</b>
Rejected ❌

</p>

<p>
For additional information or clarification, please contact EstateFlow Support.
</p>

<p>
Thank you for your understanding.
</p>

<p>
Warm Regards,<br>
<b>EstateFlow Management Team</b>
</p>

";

sendMail(
$userEmail,
$subject,
$body
);

header("Location:manage_outrate.php");

exit();

}

/* DELETE */

if(isset($_GET['delete'])){

$id = $_GET['delete'];

mysqli_query(
$conn,
"DELETE FROM outrate_lands
WHERE id='$id'"
);

header("Location:manage_outrate.php");

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
Manage Outrate Lands
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
rgba(2,6,23,.84),
rgba(2,6,23,.88)
),

url('https://images.unsplash.com/photo-1486406146926-c627a92ad1ab?q=80&w=1920&auto=format&fit=crop');

background-size:cover;
background-position:center;

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

/* BTN */

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

/* TABLE BOX */

.table-box{

background:rgba(255,255,255,.08);

border:1px solid rgba(255,255,255,.08);

backdrop-filter:blur(18px);

border-radius:28px;

padding:30px;

overflow:auto;

}

/* TABLE */

table{

width:100%;
border-collapse:collapse;

min-width:1200px;

}

th{

text-align:left;

padding-bottom:18px;

color:#94a3b8;

font-size:14px;

}

td{

padding:18px 0;

border-top:1px solid rgba(255,255,255,.06);

font-size:14px;

}

/* STATUS */

.pending{

background:#f59e0b;

padding:8px 14px;

border-radius:20px;

font-size:12px;

font-weight:700;

}

.approved{

background:#10b981;

padding:8px 14px;

border-radius:20px;

font-size:12px;

font-weight:700;

}

.rejected{

background:#ef4444;

padding:8px 14px;

border-radius:20px;

font-size:12px;

font-weight:700;

}

/* BUTTONS */

.action-btn{

text-decoration:none;

padding:10px 16px;

border-radius:12px;

font-size:13px;

font-weight:700;

margin-right:10px;

display:inline-block;

}

.approve-btn{

background:#10b981;
color:white;

}

.reject-btn{

background:#f97316;
color:white;

}

.delete-btn{

background:#ef4444;
color:white;

}

</style>

</head>

<body>

<div class="top">

<div class="title">
Outrate Lands
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

<th>Land</th>

<th>Location</th>

<th>Owner</th>

<th>Acres</th>

<th>Expected Price</th>

<th>Status</th>

<th>Action</th>

</tr>

<?php

$getData = mysqli_query(
$conn,
"SELECT * FROM outrate_lands
ORDER BY id DESC"
);

while($row = mysqli_fetch_assoc($getData)){

?>

<tr>

<td>
<?php echo $row['id']; ?>
</td>

<td>
<?php echo $row['land_name']; ?>
</td>

<td>
<?php echo $row['location']; ?>
</td>

<td>
<?php echo $row['owner_name']; ?>
</td>

<td>
<?php echo $row['acres']; ?>
</td>

<td>
<?php echo $row['expected_price']; ?>
</td>

<td>

<?php

if($row['status']=="approved"){

echo "<span class='approved'>
Approved
</span>";

}
elseif($row['status']=="Rejected"){

echo "<span class='rejected'>
Rejected
</span>";

}
else{

echo "<span class='pending'>
Pending
</span>";

}

?>

</td>

<td>

<?php if($row['status']!="approved"){ ?>

<a
class="action-btn approve-btn"
href="?approve=<?php echo $row['id']; ?>">

Approve

</a>

<?php } ?>

<?php if($row['status']!="Rejected"){ ?>

<a
class="action-btn reject-btn"
href="?reject=<?php echo $row['id']; ?>">

Reject

</a>

<?php } ?>

<a
class="action-btn delete-btn"
href="?delete=<?php echo $row['id']; ?>"

onclick="return confirm('Delete this Outrate Land?')">

Delete

</a>

</td>

</tr>

<?php } ?>

</table>

</div>

</body>
</html>