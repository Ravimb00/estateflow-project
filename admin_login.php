<?php
session_start();

/* Already logged in → go to dashboard */
if(isset($_SESSION['admin'])){
    header("Location:admin_dashboard.php");
    exit();
}

$msg = "";

if($_SERVER['REQUEST_METHOD'] === "POST"){
    $email    = trim($_POST['email']    ?? '');
    $password = trim($_POST['password'] ?? '');

    if(
        $email    === "estateflowofficial@gmail.com" &&
        $password === "#Admin@estateflow"
    ){
        session_regenerate_id(true);          // security: fresh session id
        $_SESSION['admin'] = $email;
        header("Location:admin_dashboard.php");
        exit();
    } else {
        $msg = "Invalid admin credentials. Please try again.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Login – EstateFlow</title>
<link href="https://fonts.googleapis.com/css2?family=Sora:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>

*{ margin:0; padding:0; box-sizing:border-box; font-family:'Sora',sans-serif; }

/* ── Full-page dark background with real estate slideshow ── */
body{
    min-height:100vh;
    display:flex;
    align-items:center;
    justify-content:center;
    background:#020617;
    overflow:hidden;
}

/* Slideshow */
.slide-bg{ position:fixed; inset:0; z-index:0; }
.slide{
    position:absolute; inset:0;
    background-size:cover; background-position:center;
    opacity:0; transition:opacity 2s ease;
    animation:zoom 14s linear infinite;
}
.slide.active{ opacity:1; }
@keyframes zoom{ 0%{transform:scale(1)} 100%{transform:scale(1.08)} }
.slide:nth-child(1){ background-image:url('https://images.unsplash.com/photo-1486406146926-c627a92ad1ab?w=1920&q=95'); }
.slide:nth-child(2){ background-image:url('https://images.unsplash.com/photo-1494526585095-c41746248156?w=1920&q=95'); }
.slide:nth-child(3){ background-image:url('https://images.unsplash.com/photo-1460317442991-0ec209397118?w=1920&q=95'); }
.slide-overlay{ position:fixed; inset:0; z-index:1; background:rgba(2,6,23,0.55); }

/* ══════════════════════════
   WHITE MODAL CARD
══════════════════════════ */
.modal-wrap{
    position:relative;
    z-index:10;
    width:860px;
    max-width:95vw;
    border-radius:28px;
    overflow:hidden;
    display:flex;
    box-shadow:0 32px 80px rgba(0,0,0,0.55);
    animation:fadeUp 0.5s cubic-bezier(0.22,1,0.36,1) both;
}
@keyframes fadeUp{ from{opacity:0;transform:translateY(28px)} to{opacity:1;transform:translateY(0)} }

/* LEFT — white form panel */
.form-panel{
    flex:1;
    background:#ffffff;
    padding:52px 48px;
    display:flex;
    flex-direction:column;
    justify-content:center;
}

.brand{
    font-size:13px; font-weight:700;
    color:#3b82f6; letter-spacing:1.2px;
    text-transform:uppercase; margin-bottom:14px;
}

.form-panel h1{
    font-size:34px; font-weight:800;
    color:#0f172a; letter-spacing:-1px;
    margin-bottom:6px;
}

.form-panel .sub{
    font-size:14px; color:#64748b;
    margin-bottom:32px;
}

/* Error */
.error-box{
    display:flex; align-items:center; gap:10px;
    background:#fef2f2; border:1px solid #fecaca;
    border-radius:12px; padding:13px 16px;
    margin-bottom:22px;
    color:#dc2626; font-size:13px; font-weight:600;
    animation:shake 0.4s ease;
}
@keyframes shake{
    0%,100%{transform:translateX(0)}
    20%{transform:translateX(-6px)}
    40%{transform:translateX(6px)}
    60%{transform:translateX(-4px)}
    80%{transform:translateX(4px)}
}

/* Input group */
.input-group{ margin-bottom:16px; }
.input-group label{
    display:block; font-size:11.5px; font-weight:700;
    color:#94a3b8; text-transform:uppercase;
    letter-spacing:0.6px; margin-bottom:7px;
}
.input-group input{
    width:100%; padding:14px 16px;
    border:1.5px solid #e2e8f0;
    border-radius:12px;
    background:#f8fafc;
    color:#0f172a; font-family:'Sora',sans-serif; font-size:14px;
    outline:none; transition:border-color 0.2s, box-shadow 0.2s, background 0.2s;
}
.input-group input:focus{
    border-color:#3b82f6;
    background:#fff;
    box-shadow:0 0 0 4px rgba(59,130,246,0.10);
}
.input-group input::placeholder{ color:#cbd5e1; }

/* Password row */
.pw-row{ position:relative; }
.pw-row input{ padding-right:46px; }
.pw-eye{
    position:absolute; right:14px; top:50%; transform:translateY(-50%);
    background:none; border:none; cursor:pointer;
    font-size:17px; color:#94a3b8; transition:color 0.2s; padding:0;
}
.pw-eye:hover{ color:#475569; }

/* Submit */
.btn-login{
    width:100%; padding:15px;
    margin-top:8px;
    border:none; border-radius:14px;
    background:linear-gradient(135deg,#3b82f6,#9333ea);
    color:#fff; font-family:'Sora',sans-serif;
    font-size:15px; font-weight:700;
    cursor:pointer; letter-spacing:0.2px;
    transition:transform 0.2s, box-shadow 0.2s;
    box-shadow:0 4px 18px rgba(59,130,246,0.32);
}
.btn-login:hover{
    transform:translateY(-2px);
    box-shadow:0 8px 28px rgba(59,130,246,0.42);
}
.btn-login:active{ transform:translateY(0); }

.footer-note{
    margin-top:22px; font-size:12px;
    color:#cbd5e1; text-align:center;
}
.footer-note span{ color:#3b82f6; font-weight:600; }

/* RIGHT — photo panel */
.photo-panel{
    width:360px;
    position:relative;
    overflow:hidden;
    flex-shrink:0;
}
.photo-panel img{
    width:100%; height:100%;
    object-fit:cover; display:block;
}
.photo-panel .photo-overlay{
    position:absolute; inset:0;
    background:linear-gradient(160deg,rgba(59,130,246,0.35),rgba(147,51,234,0.50));
}
.photo-panel .photo-text{
    position:absolute; bottom:32px; left:28px; right:28px;
    color:#fff;
}
.photo-panel .photo-text h3{
    font-size:22px; font-weight:800;
    margin-bottom:6px; letter-spacing:-0.5px;
    text-shadow:0 2px 12px rgba(0,0,0,0.30);
}
.photo-panel .photo-text p{
    font-size:13px; color:rgba(255,255,255,0.75);
    line-height:1.6;
}

/* Responsive */
@media(max-width:760px){
    .photo-panel{ display:none; }
    .modal-wrap{ max-width:440px; }
    .form-panel{ padding:38px 28px; }
    .form-panel h1{ font-size:26px; }
}

</style>
</head>
<body>

<!-- Slideshow bg -->
<div class="slide-bg">
    <div class="slide active"></div>
    <div class="slide"></div>
    <div class="slide"></div>
</div>
<div class="slide-overlay"></div>

<!-- WHITE MODAL -->
<div class="modal-wrap">

    <!-- LEFT: form -->
    <div class="form-panel">

        <div class="brand">EstateFlow · Management</div>

        <h1>Welcome Back</h1>
        <p class="sub">Sign in to access your admin panel</p>

        <?php if($msg !== ""): ?>
        <div class="error-box">
            <span>🔒</span>
            <?= htmlspecialchars($msg) ?>
        </div>
        <?php endif; ?>

        <form method="POST" autocomplete="off">

            <div class="input-group">
                <label>Admin Email</label>
                <input type="email" name="email"
                       placeholder="estateflowofficial@gmail.com"
                       value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                       required autofocus>
            </div>

            <div class="input-group">
                <label>Password</label>
                <div class="pw-row">
                    <input type="password" name="password" id="pwInput"
                           placeholder="Enter your password" required>
                    <button type="button" class="pw-eye" onclick="togglePw()">👁</button>
                </div>
            </div>

            <button type="submit" class="btn-login">
                🔐 &nbsp;Secure Login
            </button>

        </form>

        <p class="footer-note">
            EstateFlow Admin Panel &nbsp;·&nbsp; <span>Authorized Access Only</span>
        </p>

    </div>

    <!-- RIGHT: photo -->
    <div class="photo-panel">
        <img src="https://images.unsplash.com/photo-1486406146926-c627a92ad1ab?w=800&q=90" alt="building">
        <div class="photo-overlay"></div>
        <div class="photo-text">
            <h3>EstateFlow Admin</h3>
            <p>Manage properties, users and approvals from one powerful dashboard.</p>
        </div>
    </div>

</div>

<script>
/* Slideshow */
(function(){
    var s=document.querySelectorAll('.slide'), c=0;
    setInterval(function(){
        s[c].classList.remove('active');
        c=(c+1)%s.length;
        s[c].classList.add('active');
    },6000);
})();

/* Password toggle */
function togglePw(){
    var i=document.getElementById('pwInput');
    i.type=i.type==='password'?'text':'password';
}
</script>
</body>
</html>