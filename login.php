<!DOCTYPE html>
<html>
<head>
<title>EstateFlow Login</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body{
background:#0f172a;
display:flex;
justify-content:center;
align-items:center;
height:100vh;
font-family:Arial;
color:white;
}
.login-box{
width:400px;
background:#111827;
padding:30px;
border-radius:15px;
}
</style>
</head>
<body>

<div class="login-box">
<h2 class="text-center mb-4">EstateFlow Login</h2>

<form action="login_process.php" method="POST">

<div class="mb-3">
<label>Email</label>
<input type="email" name="email" class="form-control">
</div>

<div class="mb-3">
<label>Password</label>
<input type="password" name="password" class="form-control">
</div>

<button type="submit"class="btn btn-info w-100">
Login
</button>

</form>
</div>

</body>
</html>