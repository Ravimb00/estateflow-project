<?php

session_start();

include 'config/db.php';

if(!isset($_SESSION['admin'])){
    header("Location:admin_login.php");
    exit();
}

/* GET ADMIN */

$getAdmin = mysqli_query(
$conn,
"SELECT * FROM admin LIMIT 1"
);

$admin = mysqli_fetch_assoc($getAdmin);

/* UPDATE PROFILE */

if(isset($_POST['save_profile'])){

    $name  = mysqli_real_escape_string($conn,$_POST['name']);
    $email = mysqli_real_escape_string($conn,$_POST['email']);

    mysqli_query(
    $conn,
    "UPDATE admin
    SET
    name='$name',
    email='$email'"
    );

    header("Location:settings.php?success=1");
}

/* CHANGE PASSWORD */

if(isset($_POST['change_password'])){

   $newPassword = md5(
trim($_POST['new_password'])
);

mysqli_query(
$conn,
"UPDATE admin
SET password='$newPassword'
WHERE id='1'"
);
    header("Location:settings.php?password=changed");
}

/* PHOTO UPLOAD */

if(isset($_POST['upload_photo'])){

    if(!empty($_FILES['photo']['name'])){

        if(!is_dir("uploads")){
            mkdir("uploads");
        }

        $photo =
        "uploads/".
        time().
        $_FILES['photo']['name'];

        move_uploaded_file(
        $_FILES['photo']['tmp_name'],
        $photo
        );

        mysqli_query(
        $conn,
        "UPDATE admin
        SET photo='$photo'"
        );

        header("Location:settings.php?photo=updated");
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
EstateFlow Settings
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

color:white;

padding:40px;

}

/* HEADER */

.top{

display:flex;
justify-content:space-between;
align-items:center;

margin-bottom:35px;

}

.top h1{

font-size:34px;
font-weight:800;

}

/* GRID */

.grid{

display:grid;

grid-template-columns:1fr 1fr;

gap:24px;

}

/* CARD */

.card{

background:rgba(255,255,255,.05);

border:1px solid rgba(255,255,255,.08);

border-radius:24px;

padding:28px;

backdrop-filter:blur(14px);

}

/* TITLE */

.card h2{

font-size:22px;

margin-bottom:22px;

}

/* INPUT */

.input-box{

margin-bottom:18px;

}

.input-box label{

display:block;

margin-bottom:8px;

font-size:13px;

color:#cbd5e1;

}

.input-box input{

width:100%;

padding:14px 16px;

border:none;
outline:none;

border-radius:14px;

background:rgba(255,255,255,.06);

border:1px solid rgba(255,255,255,.08);

color:white;

font-size:14px;

}

/* BUTTON */

.btn{

padding:14px 22px;

border:none;

border-radius:14px;

font-weight:700;

cursor:pointer;

color:white;

font-size:14px;

transition:.3s;

}

.btn:hover{

transform:translateY(-2px);

}

.blue{
background:linear-gradient(135deg,#3b82f6,#2563eb);
}

.purple{
background:linear-gradient(135deg,#9333ea,#7e22ce);
}

.orange{
background:linear-gradient(135deg,#f59e0b,#ea580c);
}

.red{
background:linear-gradient(135deg,#ef4444,#dc2626);
}

/* PROFILE */

.profile{

display:flex;
align-items:center;

gap:18px;

margin-bottom:24px;

}

.profile img{

width:80px;
height:80px;

border-radius:50%;

object-fit:cover;

border:3px solid rgba(255,255,255,.15);

}

.profile h3{

font-size:20px;

margin-bottom:5px;

}

/* ALERT */

.alert{

padding:14px 18px;

border-radius:14px;

background:rgba(16,185,129,.18);

border:1px solid rgba(16,185,129,.30);

color:#34d399;

margin-bottom:25px;

font-size:14px;

}

/* RESPONSIVE */

@media(max-width:900px){

.grid{
grid-template-columns:1fr;
}

}

</style>

</head>

<body>

<div class="top">

<h1>
⚙️ Settings
</h1>

<a
href="admin_dashboard.php"
class="btn blue"
style="text-decoration:none;">

← Dashboard

</a>

</div>

<?php if(isset($_GET['success'])){ ?>

<div class="alert">
✅ Profile Updated Successfully
</div>

<?php } ?>

<?php if(isset($_GET['password'])){ ?>

<div class="alert">
✅ Password Changed Successfully
</div>

<?php } ?>

<?php if(isset($_GET['photo'])){ ?>

<div class="alert">
✅ Admin Photo Updated
</div>

<?php } ?>

<div class="grid">

<!-- PROFILE SETTINGS -->

<div class="card">

<h2>
👤 Admin Profile
</h2>

<div class="profile">

<img
src="<?php echo $admin['photo']; ?>">

<div>

<h3>
<?php echo $admin['name']; ?>
</h3>

<p>
<?php echo $admin['email']; ?>
</p>

</div>

</div>

<form method="POST">

<div class="input-box">

<label>
Admin Name
</label>

<input
type="text"
name="name"
value="<?php echo $admin['name']; ?>"
required>

</div>

<div class="input-box">

<label>
Admin Email
</label>

<input
type="email"
name="email"
value="<?php echo $admin['email']; ?>"
required>

</div>

<button
type="submit"
name="save_profile"
class="btn blue">

Save Changes

</button>

</form>

</div>

<!-- PASSWORD -->

<div class="card">

<h2>
🔐 Change Password
</h2>

<form method="POST">

<div class="input-box">

<label>
New Password
</label>

<input
type="password"
name="new_password"
required>

</div>

<button
type="submit"
name="change_password"
class="btn purple">

Update Password

</button>

</form>

</div>

<!-- PHOTO -->

<div class="card">

<h2>
🖼 Upload Admin Photo
</h2>

<form
method="POST"
enctype="multipart/form-data">

<div class="input-box">

<label>
Choose Photo
</label>

<input
type="file"
name="photo"
required>

</div>

<button
type="submit"
name="upload_photo"
class="btn orange">

Upload Photo

</button>

</form>

</div>

<!-- LOGOUT USERS -->

<div class="card">

<h2>
🚨 User Session Control
</h2>

<p
style="
color:#cbd5e1;
margin-bottom:20px;
line-height:1.6;
">

Logout all currently logged-in users from EstateFlow system.

</p>

<a
href="logout_all.php"
class="btn red"
style="text-decoration:none;display:inline-block;">

Logout All Users

</a>

</div>

</div>

</body>
</html>