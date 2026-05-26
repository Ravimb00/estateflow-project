
<?php
session_start();
include 'config/db.php';
 
if(!isset($_POST['email'])){
    header("Location:index.php");
    exit();
}
 
$email    = mysqli_real_escape_string($conn, trim($_POST['email']));
$password = trim($_POST['password']);
 
/* ── Find user ── */
$check = mysqli_query($conn, "SELECT * FROM users WHERE email='$email' LIMIT 1");
 
if(mysqli_num_rows($check) === 0){
    /* User not found → back to login with error */
    header("Location:index.php?error=notfound");
    exit();
}
 
$user       = mysqli_fetch_assoc($check);
$dbPassword = $user['password'];
 
/* ── Password check (bcrypt + plain legacy) ── */
$isValid = password_verify($password, $dbPassword) || ($password === $dbPassword);
 
if(!$isValid){
    header("Location:index.php?error=wrongpass");
    exit();
}
 
/* ── Password correct — check verified ── */
if($user['is_verified'] == 1){
 
    /* ✅ Verified → direct to dashboard */
    session_regenerate_id(true);
    $_SESSION['user_id']    = $user['id'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['user_name']  = $user['name'] ?? ($user['first_name'].' '.$user['last_name']);
    $_SESSION['user_role']  = $user['role'];
    header("Location:user_dashboard.php");
    exit();
 
} else {
 
    /* ❌ Not verified → signup/verify page */
    $_SESSION['pending_email'] = $user['email'];
    header("Location:user_signup.php?verify=1");
    exit();
}
?>