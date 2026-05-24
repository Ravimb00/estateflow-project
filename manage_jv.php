<?php

error_reporting(E_ALL);
ini_set('display_errors',1);

session_start();

include 'config/db.php';

/* ADMIN LOGIN CHECK */

if(!isset($_SESSION['admin'])){

header(
"Location:admin_login.php"
);

exit();

}

/* APPROVE JV */

if(isset($_GET['approve'])){

$id = $_GET['approve'];

mysqli_query(

$conn,

"UPDATE jv_lands
SET status='approved'
WHERE id='$id'"

);

header(
"Location:manage_jv.php"
);

exit();

}

/* DELETE JV */

if(isset($_GET['delete'])){

$id = $_GET['delete'];

mysqli_query(

$conn,

"DELETE FROM jv_lands
WHERE id='$id'"
);

header(
"Location:manage_jv.php"
);

exit();

}

/* FETCH JV LANDS */

$getJV = mysqli_query(

$conn,

"SELECT * FROM jv_lands
ORDER BY id DESC"

);

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport"
content="width=device-width, initial-scale=1.0">

<title>
Manage JV Lands
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
rgba(2,6,23,.85),
rgba(2,6,23,.92)
),

url('https://images.unsplash.com/photo-1486406146926-c627a92ad1ab?q=80&w=1920&auto=format&fit=crop');

background-size:cover;
background-position:center;

min-height:100vh;

padding:40px;

color:white;

}

/* TOP */

.top{

display:flex;
justify-content:space-between;
align-items:center;

margin-bottom:35px;

}

.title{

font-size:46px;
font-weight:800;

}

.back{

text-decoration:none;

padding:14px 22px;

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

padding:30px;

border-radius:28px;

overflow:auto;

}

/* TABLE */

table{

width:100%;

border-collapse:collapse;

}

table th{

text-align:left;

padding-bottom:18px;

color:#94a3b8;

font-size:14px;

}

table td{

padding:18px 0;

border-top:1px solid rgba(255,255,255,.08);

font-size:14px;

}

/* STATUS */

.pending{

color:#facc15;
font-weight:700;

}

.approved{

color:#22c55e;
font-weight:700;

}

/* BUTTONS */

.btn{

padding:10px 16px;

border-radius:12px;

text-decoration:none;

font-size:13px;

font-weight:700;

display:inline-block;

margin-right:10px;

}

/* APPROVE */

.approve{

background:linear-gradient(
135deg,
#22c55e,
#16a34a
);

color:white;

}

/* DELETE */

.delete{

background:linear-gradient(
135deg,
#ef4444,
#dc2626
);

color:white;

}

</style>

</head>

<body>

<div class="top">

<div class="title">
Manage JV Lands
</div>

<a
href="admin_dashboard.php"
class="back">

Dashboard

</a>

</div>

<div class="table-box">

<table>

<tr>

<th>
Land Name
</th>

<th>
Location
</th>

<th>
Builder
</th>

<th>
Acres
</th>

<th>
Deal Value
</th>

<th>
Status
</th>

<th>
Action
</th>

</tr>

<?php

while($row = mysqli_fetch_assoc($getJV)){

?>

<tr>

<td>
<?php echo $row['land_name']; ?>
</td>

<td>
<?php echo $row['location']; ?>
</td>

<td>
<?php echo $row['builder']; ?>
</td>

<td>
<?php echo $row['acres']; ?>
</td>

<td>
<?php echo $row['deal_value']; ?>
</td>

<td>

<?php

if($row['status']=="approved"){

?>

<span class="approved">
Approved
</span>

<?php }else{ ?>

<span class="pending">
Pending
</span>

<?php } ?>

</td>

<td>

<?php
if($row['status']!="approved"){
?>

<a
href="?approve=<?php echo $row['id']; ?>"
class="btn approve">

Approve

</a>

<?php } ?>

<a
href="?delete=<?php echo $row['id']; ?>"
class="btn delete"

onclick="return confirm('Delete this JV Land?')">

Delete

</a>

</td>

</tr>

<?php } ?>

</table>

</div>

</body>
</html>