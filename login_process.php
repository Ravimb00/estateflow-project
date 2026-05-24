<?php

session_start();

include 'config/db.php';

/* USER LOGIN */

if(isset($_POST['email'])){

$email = mysqli_real_escape_string(
$conn,
trim($_POST['email'])
);

$password =
trim($_POST['password']);

/* FIND USER */

$check = mysqli_query(
$conn,
"SELECT * FROM users
WHERE email='$email'"
);

/* USER EXISTS */

if(mysqli_num_rows($check)>0){

$user = mysqli_fetch_assoc($check);

$dbPassword =
$user['password'];

$isValid = false;

/* HASH PASSWORD */

if(
password_verify(
$password,
$dbPassword
)
){

$isValid = true;

}

/* OLD PLAIN PASSWORD */

if(
$password === $dbPassword
){

$isValid = true;

}

/* PASSWORD SUCCESS */

if($isValid){

/* VERIFIED USER */

if($user['is_verified']==1){

$_SESSION['user_email'] =
$user['email'];

$_SESSION['user_role'] =
$user['role'];

$_SESSION['user_name'] =
$user['name'];

/* DIRECT DASHBOARD */

header(
"Location:user_dashboard.php"
);

exit();

/* UNVERIFIED USER */

}else{

header(
"Location:user_signup.php"
);

exit();

}

}else{

/* WRONG PASSWORD */

header(
"Location:index.php"
);

exit();

}

}else{

/* USER NOT FOUND */

header(
"Location:index.php"
);

exit();

}

}else{

header(
"Location:index.php"
);

exit();

}

?>