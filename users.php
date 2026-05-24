<?php

error_reporting(E_ALL);
ini_set('display_errors',1);

session_start();

include 'config/db.php';

/* ADMIN CHECK */

if(!isset($_SESSION['admin'])){

header(
"Location:admin_login.php"
);

exit();

}

/* DELETE USER */

if(isset($_GET['delete'])){

$id = $_GET['delete'];

mysqli_query(
$conn,
"DELETE FROM users
WHERE id='$id'"
);

header(
"Location:users.php"
);

exit();

}

/* SEARCH */

$search = "";

if(isset($_GET['search'])){

$search =
mysqli_real_escape_string(
$conn,
$_GET['search']
);

$query = mysqli_query(

$conn,

"SELECT * FROM users

WHERE

first_name LIKE '%$search%'

OR

last_name LIKE '%$search%'

OR

email LIKE '%$search%'

ORDER BY id DESC"

);

}else{

$query = mysqli_query(
$conn,
"SELECT * FROM users
ORDER BY id DESC"
);

}

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport"
content="width=device-width, initial-scale=1.0">

<title>
Manage Users
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
rgba(2,6,23,.88),
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

.logo{

font-size:40px;
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

/* SEARCH */

.search-box{

margin-bottom:30px;

}

.search-box form{
display:flex;
gap:15px;
}

.search-box input{

flex:1;

padding:16px;

border:none;
outline:none;

border-radius:16px;

background:rgba(255,255,255,.08);

border:1px solid rgba(255,255,255,.08);

color:white;

font-size:14px;
}

.search-box button{

padding:16px 24px;

border:none;

border-radius:16px;

background:linear-gradient(
135deg,
#14b8a6,
#06b6d4
);

color:white;

font-weight:700;

cursor:pointer;
}

/* TABLE */

.table-box{

background:rgba(255,255,255,.08);

padding:30px;

border-radius:28px;

backdrop-filter:blur(18px);

border:1px solid rgba(255,255,255,.08);

overflow:auto;
}

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

.status{

padding:8px 14px;

border-radius:30px;

font-size:12px;

font-weight:700;

display:inline-block;
}

.verified{

background:rgba(16,185,129,.18);

color:#34d399;
}

.pending{

background:rgba(239,68,68,.18);

color:#f87171;
}

/* DELETE */

.delete{

text-decoration:none;

padding:10px 16px;

border-radius:12px;

background:linear-gradient(
135deg,
#ef4444,
#dc2626
);

color:white;

font-size:13px;

font-weight:700;
}

/* MOBILE */

@media(max-width:700px){

.top{
flex-direction:column;
gap:20px;
align-items:flex-start;
}

.search-box form{
flex-direction:column;
}

}

</style>

</head>

<body>

<!-- TOP -->

<div class="top">

<div class="logo">
Manage Users
</div>

<a href="admin_dashboard.php"
class="back">

Dashboard

</a>

</div>

<!-- SEARCH -->

<div class="search-box">

<form method="GET">

<input
type="text"
name="search"
placeholder="Search users by name or email">

<button
type="submit">

Search

</button>

</form>

</div>

<!-- TABLE -->

<div class="table-box">

<table>

<tr>

<th>
ID
</th>

<th>
Name
</th>

<th>
Email
</th>

<th>
Status
</th>

<th>
Action
</th>

</tr>

<?php

while($row =
mysqli_fetch_assoc($query)){

?>

<tr>

<td>

<?php
echo $row['id'];
?>

</td>

<td>

<?php

echo
$row['first_name']
." ".
$row['last_name'];

?>

</td>

<td>

<?php
echo $row['email'];
?>

</td>

<td>

<?php

if($row['is_verified']==1){

?>

<span class="status verified">
Verified
</span>

<?php }else{ ?>

<span class="status pending">
Pending
</span>

<?php } ?>

</td>

<td>

<a
class="delete"

href="users.php?delete=<?php echo $row['id']; ?>"

onclick="return confirm('Delete this user?')">

Delete

</a>

</td>

</tr>

<?php } ?>

</table>

</div>

</body>
</html>