<?php
error_reporting(E_ALL);
ini_set('display_errors',1);
session_start();
include 'config/db.php';

if(!isset($_SESSION['admin'])){
    header("Location:admin_login.php");
    exit();
}

$adminId  = $_SESSION['admin'];
$adminRow = mysqli_fetch_assoc(mysqli_query($conn,"SELECT * FROM admin WHERE id='$adminId' LIMIT 1"));

$success = '';
$error   = '';

/* ══ 1. Update Name ══ */
if(isset($_POST['update_name'])){
    $name = trim(mysqli_real_escape_string($conn,$_POST['admin_name']));
    if($name===''){
        $error = 'Name cannot be empty.';
    } else {
        mysqli_query($conn,"UPDATE admin SET name='$name' WHERE id='$adminId'");
        $success = '✅ Name updated successfully.';
        $adminRow['name'] = $name;
    }
}

/* ══ 2. Update Email ══ */
if(isset($_POST['update_email'])){
    $email = trim(mysqli_real_escape_string($conn,$_POST['admin_email']));
    if(!filter_var($email,FILTER_VALIDATE_EMAIL)){
        $error = 'Enter a valid email address.';
    } else {
        $dup = mysqli_num_rows(mysqli_query($conn,"SELECT id FROM admin WHERE email='$email' AND id!='$adminId'"));
        if($dup){ $error = 'Email already used by another admin.'; }
        else {
            mysqli_query($conn,"UPDATE admin SET email='$email' WHERE id='$adminId'");
            $success = '✅ Email updated successfully.';
            $adminRow['email'] = $email;
        }
    }
}

/* ══ 3. Change Password ══ */
if(isset($_POST['change_password'])){
    $cur    = $_POST['current_password'];
    $newp   = $_POST['new_password'];
    $conf   = $_POST['confirm_password'];
    $stored = $adminRow['password'] ?? '';
    $curOk  = password_verify($cur,$stored) || ($cur===$stored);
    if(!$curOk)            { $error = 'Current password is incorrect.'; }
    elseif(strlen($newp)<6){ $error = 'New password must be at least 6 characters.'; }
    elseif($newp!==$conf)  { $error = 'New passwords do not match.'; }
    else {
        $hash     = password_hash($newp, PASSWORD_BCRYPT);
        $safeHash = mysqli_real_escape_string($conn, $hash);
        mysqli_query($conn, "UPDATE admin SET password='$safeHash' WHERE id='$adminId'");
        $check = mysqli_fetch_assoc(mysqli_query($conn,"SELECT password FROM admin WHERE id='$adminId'"));
        if($check && password_verify($newp, $check['password'])){
            $success = '✅ Password changed! Use new password next login.';
        } else {
            $error = 'Save failed — try again.';
        }
    }
}

/* ══ 4. Upload Photo ══ */
if(isset($_POST['upload_photo'])){
    if(!empty($_FILES['admin_photo']['name'])){
        $allowed = ['image/jpeg','image/png','image/webp','image/gif'];
        $mime    = mime_content_type($_FILES['admin_photo']['tmp_name']);
        if(!in_array($mime,$allowed)){
            $error = 'Only JPG, PNG, WebP, GIF allowed.';
        } elseif($_FILES['admin_photo']['size'] > 3*1024*1024){
            $error = 'Image must be under 3 MB.';
        } else {
            $ext   = strtolower(pathinfo($_FILES['admin_photo']['name'],PATHINFO_EXTENSION));
            $fname = 'admin_'.$adminId.'_'.time().'.'.$ext;
            $dir   = 'uploads/admin/';
            if(!is_dir($dir)) mkdir($dir,0755,true);
            if(!empty($adminRow['photo']) && file_exists($adminRow['photo'])) unlink($adminRow['photo']);
            if(move_uploaded_file($_FILES['admin_photo']['tmp_name'],$dir.$fname)){
                $path = $dir.$fname;
                mysqli_query($conn,"UPDATE admin SET photo='$path' WHERE id='$adminId'");
                $success = '✅ Profile photo updated.';
                $adminRow['photo'] = $path;
            } else { $error = 'Upload failed. Check folder permissions.'; }
        }
    } else { $error = 'Please select a file.'; }
}

/* ══ 5. Logout All Users ══ */
if(isset($_POST['logout_all_users'])){
    mysqli_query($conn,"UPDATE users SET session_token=NULL WHERE session_token IS NOT NULL");
    $sp    = session_save_path() ?: sys_get_temp_dir();
    $files = glob($sp.'/sess_*');
    if($files){ $me = session_id(); foreach($files as $f){ $sid=str_replace($sp.'/sess_','',$f); if($sid!==$me) @unlink($f); } }
    $success = '✅ All users have been logged out.';
}

/* re-fetch fresh */
$adminRow   = mysqli_fetch_assoc(mysqli_query($conn,"SELECT * FROM admin WHERE id='$adminId' LIMIT 1"));
$photoSrc   = (!empty($adminRow['photo']) && file_exists($adminRow['photo'])) ? $adminRow['photo'] : 'https://i.pravatar.cc/100?img=12';
$adminName  = htmlspecialchars($adminRow['name']  ?? 'Admin');
$adminEmail = htmlspecialchars($adminRow['email'] ?? 'admin@estateflow.com');

/* ══ Fetch registered fingerprints ══ */
$fingerprintsRes = mysqli_query($conn,"SELECT * FROM admin_credentials WHERE admin_id='$adminId' ORDER BY id DESC");
$fingerprints    = [];
while($fp = mysqli_fetch_assoc($fingerprintsRes)) $fingerprints[] = $fp;
$fpCount = count($fingerprints);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Settings – EstateFlow Admin</title>
<link href="https://fonts.googleapis.com/css2?family=Sora:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
*{ margin:0; padding:0; box-sizing:border-box; font-family:'Sora',sans-serif; }
body{ background:#020617; min-height:100vh; overflow-x:hidden; color:white; display:flex; }

/* SLIDESHOW */
.slide-bg{ position:fixed; top:0; left:0; width:100%; height:100%; z-index:0; }
.slide{
    position:absolute; inset:0;
    background-size:cover; background-position:center; background-repeat:no-repeat;
    opacity:0; transition:opacity 2.4s ease-in-out;
    animation:kb 14s ease-in-out infinite alternate;
}
.slide.active{ opacity:1; }
@keyframes kb  { 0%{transform:scale(1.00) translate(0,0)}       100%{transform:scale(1.09) translate(-14px,-8px)} }
@keyframes kb2 { 0%{transform:scale(1.07) translate(12px,6px)}  100%{transform:scale(1.00) translate(0,0)} }
@keyframes kb3 { 0%{transform:scale(1.00) translate(-8px,10px)} 100%{transform:scale(1.08) translate(10px,-6px)} }
@keyframes kb4 { 0%{transform:scale(1.06) translate(6px,-8px)}  100%{transform:scale(1.00) translate(-10px,5px)} }
@keyframes kb5 { 0%{transform:scale(1.00) translate(0,8px)}     100%{transform:scale(1.07) translate(-8px,-6px)} }
.slide:nth-child(1){ animation-name:kb;  background-image:url('https://images.unsplash.com/photo-1486325212027-8081e485255e?w=1920&q=95'); }
.slide:nth-child(2){ animation-name:kb2; background-image:url('https://images.unsplash.com/photo-1545324418-cc1a3fa10c00?w=1920&q=95'); }
.slide:nth-child(3){ animation-name:kb3; background-image:url('https://images.unsplash.com/photo-1560518883-ce09059eeffa?w=1920&q=95'); }
.slide:nth-child(4){ animation-name:kb4; background-image:url('https://images.unsplash.com/photo-1582407947304-fd86f028f716?w=1920&q=95'); }
.slide:nth-child(5){ animation-name:kb5; background-image:url('https://images.unsplash.com/photo-1512917774080-9991f1c4c750?w=1920&q=95'); }
.slide-overlay{
    position:fixed; inset:0; z-index:1;
    background:radial-gradient(ellipse 90% 80% at 55% 45%, rgba(2,6,23,0.22) 0%, rgba(2,6,23,0.52) 100%);
}
.sidebar-shadow{
    position:fixed; top:0; left:0; width:260px; height:100%; z-index:2;
    background:linear-gradient(to right, rgba(2,6,23,0.72) 0%, rgba(2,6,23,0.0) 100%);
    pointer-events:none;
}

/* SIDEBAR */
.sidebar{
    width:250px; height:100vh; position:fixed; left:0; top:0; z-index:100;
    padding:30px 18px 28px; display:flex; flex-direction:column; justify-content:space-between;
    background:rgba(2,6,23,0.48); backdrop-filter:blur(22px); -webkit-backdrop-filter:blur(22px);
    border-right:1px solid rgba(255,255,255,0.07);
}
.logo{
    font-size:30px; font-weight:800; margin-bottom:42px;
    background:linear-gradient(135deg,#e8c46a,#d4825a);
    -webkit-background-clip:text; -webkit-text-fill-color:transparent;
    letter-spacing:-0.5px;
}
.menu{ display:flex; flex-direction:column; gap:5px; }
.menu a{
    text-decoration:none; color:rgba(255,255,255,0.62);
    padding:13px 15px; border-radius:13px; font-size:13.5px; font-weight:600;
    transition:all 0.22s; display:flex; align-items:center; gap:10px;
}
.menu a:hover{ background:rgba(255,255,255,0.07); color:rgba(255,255,255,0.95); }
.menu .active{
    background:linear-gradient(135deg,rgba(59,130,246,0.88),rgba(147,51,234,0.88));
    color:#fff; box-shadow:0 4px 22px rgba(59,130,246,0.28);
}
.logout{
    display:flex; align-items:center; justify-content:center; gap:8px;
    padding:13px 20px; border-radius:13px; text-decoration:none;
    background:linear-gradient(135deg,rgba(239,68,68,0.88),rgba(220,38,38,0.88));
    color:white; font-weight:700; font-size:13.5px;
    transition:opacity 0.2s, transform 0.2s;
}
.logout:hover{ opacity:0.85; transform:translateY(-1px); }

/* SLIDE DOTS */
.slide-dots{
    position:fixed; bottom:20px; left:50%; transform:translateX(-50%);
    display:flex; gap:7px; z-index:50;
}
.dot{ width:7px; height:7px; border-radius:50%; background:rgba(255,255,255,0.28); transition:all 0.4s ease; }
.dot.active{ background:rgba(255,255,255,0.90); width:22px; border-radius:4px; }

/* MAIN */
.main{ margin-left:250px; width:calc(100% - 250px); padding:30px 34px; position:relative; z-index:10; }

/* TOPBAR */
.topbar{ display:flex; justify-content:space-between; align-items:center; margin-bottom:30px; gap:16px; }
.welcome-box{
    flex:1; max-width:52%; padding:22px 28px; border-radius:20px;
    background:linear-gradient(135deg,rgba(109,60,200,0.52),rgba(56,108,220,0.38));
    border:1px solid rgba(255,255,255,0.10);
    backdrop-filter:blur(10px); -webkit-backdrop-filter:blur(10px);
    box-shadow:0 6px 28px rgba(0,0,0,0.20);
}
.welcome-box h2{ font-size:21px; font-weight:800; margin-bottom:6px; letter-spacing:-0.3px; }
.welcome-box p { font-size:13px; color:rgba(255,255,255,0.68); }
.right-top{ display:flex; align-items:center; gap:14px; }
.admin-box{
    display:flex; align-items:center; gap:12px;
    padding:11px 17px; border-radius:17px;
    background:rgba(2,6,23,0.38); backdrop-filter:blur(10px); -webkit-backdrop-filter:blur(10px);
    border:1px solid rgba(255,255,255,0.10);
}
.admin-box img{ width:44px; height:44px; border-radius:50%; object-fit:cover; border:2px solid rgba(255,255,255,0.16); }
.admin-box .a-name { font-size:15px; font-weight:700; }
.admin-box .a-email{ font-size:11px; color:rgba(255,255,255,0.45); margin-top:2px; }

/* PAGE TITLE */
.main-title{ margin-bottom:26px; }
.main-title h1{ font-size:38px; font-weight:800; letter-spacing:-1.2px; margin-bottom:6px; text-shadow:0 2px 30px rgba(0,0,0,0.55); }
.main-title p{ font-size:14px; color:rgba(255,255,255,0.58); font-weight:500; }

/* Alert */
.alert{
    padding:14px 20px; border-radius:14px;
    font-size:13.5px; font-weight:600; margin-bottom:22px;
    display:flex; align-items:center; gap:10px;
    animation:fadeDown 0.35s ease;
}
@keyframes fadeDown{ from{opacity:0;transform:translateY(-8px)} to{opacity:1;transform:translateY(0)} }
.alert-success{ background:rgba(16,185,129,0.12); border:1px solid rgba(16,185,129,0.26); color:#34d399; }
.alert-error  { background:rgba(239,68,68,0.10);  border:1px solid rgba(239,68,68,0.24);  color:#f87171; }

/* Settings grid */
.settings-grid{ display:grid; grid-template-columns:repeat(2,1fr); gap:20px; }

/* Cards */
.s-card{
    background:rgba(4,10,30,0.38); padding:26px 28px 24px;
    border-radius:20px; border:1px solid rgba(255,255,255,0.08);
    backdrop-filter:blur(8px); -webkit-backdrop-filter:blur(8px);
    box-shadow:0 6px 28px rgba(0,0,0,0.25);
    position:relative; overflow:hidden;
    transition:border-color 0.2s, box-shadow 0.2s;
}
.s-card:hover{ border-color:rgba(255,255,255,0.14); box-shadow:0 10px 38px rgba(0,0,0,0.36); }
.s-card::before{ content:''; position:absolute; top:0; left:0; right:0; height:3px; border-radius:20px 20px 0 0; }
.s-card.c-blue::before   { background:linear-gradient(90deg,#3b82f6,#6366f1); }
.s-card.c-green::before  { background:linear-gradient(90deg,#10b981,#34d399); }
.s-card.c-purple::before { background:linear-gradient(90deg,#8b5cf6,#a78bfa); }
.s-card.c-orange::before { background:linear-gradient(90deg,#f59e0b,#fb923c); }
.s-card.c-red::before    { background:linear-gradient(90deg,#ef4444,#f87171); }
.s-card.full{ grid-column:1 / -1; }

.card-icon{ width:42px; height:42px; border-radius:11px; display:flex; align-items:center; justify-content:center; font-size:20px; margin-bottom:14px; }
.ic-blue   { background:rgba(59,130,246,0.15); }
.ic-green  { background:rgba(16,185,129,0.15); }
.ic-purple { background:rgba(139,92,246,0.15); }
.ic-orange { background:rgba(245,158,11,0.15); }
.ic-red    { background:rgba(239,68,68,0.12);  }

.card-title{ font-size:15px; font-weight:700; margin-bottom:4px; color:rgba(255,255,255,0.92); }
.card-desc { font-size:12.5px; color:rgba(255,255,255,0.48); margin-bottom:18px; line-height:1.5; }

/* Form */
label.flabel{ display:block; font-size:11px; font-weight:700; color:rgba(255,255,255,0.40); text-transform:uppercase; letter-spacing:0.6px; margin-bottom:7px; }
input[type=text],input[type=email],input[type=password],input[type=file]{
    width:100%; padding:11px 14px; border-radius:10px;
    border:1px solid rgba(255,255,255,0.12);
    background:rgba(255,255,255,0.06);
    color:#fff; font-family:'Sora',sans-serif; font-size:13.5px;
    outline:none; transition:border-color 0.2s, box-shadow 0.2s; margin-bottom:13px;
}
input[type=text]:focus,input[type=email]:focus,input[type=password]:focus{
    border-color:rgba(59,130,246,0.70); box-shadow:0 0 0 3px rgba(59,130,246,0.13);
}
input[type=file]{ padding:9px 14px; color:rgba(255,255,255,0.55); cursor:pointer; }

/* password eye */
.pw-row{ position:relative; }
.pw-eye{ position:absolute; right:12px; top:50%; transform:translateY(-60%); background:none; border:none; color:rgba(255,255,255,0.35); cursor:pointer; font-size:16px; padding:0; transition:color 0.2s; }
.pw-eye:hover{ color:rgba(255,255,255,0.70); }

/* strength */
.str-bar{ height:4px; border-radius:4px; background:rgba(255,255,255,0.08); margin-bottom:6px; overflow:hidden; }
.str-fill{ height:100%; border-radius:4px; width:0; transition:width 0.3s,background 0.3s; }
.str-label{ font-size:11px; color:rgba(255,255,255,0.35); margin-bottom:12px; min-height:15px; }

/* photo preview */
.photo-row{ display:flex; align-items:center; gap:14px; margin-bottom:16px; }
.photo-ring{ width:66px; height:66px; border-radius:50%; object-fit:cover; border:2.5px solid rgba(255,255,255,0.18); transition:border-color 0.2s; }
.photo-row:hover .photo-ring{ border-color:rgba(59,130,246,0.60); }
.photo-hint{ font-size:12px; color:rgba(255,255,255,0.40); line-height:1.8; }

/* Buttons */
.btn{
    display:inline-flex; align-items:center; justify-content:center; gap:8px;
    width:100%; padding:12px 22px; border-radius:10px; border:none;
    font-family:'Sora',sans-serif; font-size:13.5px; font-weight:700;
    cursor:pointer; transition:all 0.22s; margin-top:2px;
}
.btn-blue  { background:linear-gradient(135deg,#3b82f6,#6366f1); color:#fff; box-shadow:0 4px 16px rgba(59,130,246,0.28); }
.btn-green { background:linear-gradient(135deg,#10b981,#059669); color:#fff; box-shadow:0 4px 14px rgba(16,185,129,0.26); }
.btn-purple{ background:linear-gradient(135deg,#8b5cf6,#6366f1); color:#fff; box-shadow:0 4px 14px rgba(139,92,246,0.26); }
.btn-orange{ background:linear-gradient(135deg,#f59e0b,#d97706); color:#fff; box-shadow:0 4px 14px rgba(245,158,11,0.24); }
.btn-red   { background:linear-gradient(135deg,#ef4444,#dc2626); color:#fff; box-shadow:0 4px 14px rgba(239,68,68,0.26); }
.btn:hover { transform:translateY(-2px); filter:brightness(1.08); }
.btn-sm{ width:auto; padding:9px 18px; font-size:12.5px; }
.btn-cancel{ background:rgba(255,255,255,0.08); color:rgba(255,255,255,0.80); }
.btn-cancel:hover{ background:rgba(255,255,255,0.13); }

/* Danger zone */
.danger-row{ display:flex; align-items:flex-start; gap:16px; margin-bottom:20px; }
.danger-row .dr-text h4{ font-size:14px; font-weight:700; color:#f87171; margin-bottom:5px; }
.danger-row .dr-text p { font-size:12.5px; color:rgba(255,255,255,0.48); line-height:1.6; }

/* Confirm modal */
.overlay{ display:none; position:fixed; inset:0; background:rgba(2,6,23,0.78); backdrop-filter:blur(10px); z-index:999; align-items:center; justify-content:center; }
.overlay.show{ display:flex; }
.modal{ background:#0d1424; border:1px solid rgba(255,255,255,0.12); border-radius:22px; padding:38px 42px; max-width:400px; width:90%; text-align:center; box-shadow:0 28px 70px rgba(0,0,0,0.60); animation:pop 0.3s cubic-bezier(0.34,1.56,0.64,1); }
@keyframes pop{ from{transform:scale(0.82);opacity:0} to{transform:scale(1);opacity:1} }
.modal .m-icon { font-size:46px; margin-bottom:14px; }
.modal h3{ font-size:20px; font-weight:800; margin-bottom:9px; }
.modal p{ font-size:13px; color:rgba(255,255,255,0.55); line-height:1.7; margin-bottom:26px; }
.modal-btns{ display:flex; gap:12px; }
.modal-btns .btn{ margin-top:0; }

/* Theme toggle */
.theme-btn{
    display:flex; align-items:center; gap:8px; padding:11px 18px; border-radius:14px;
    background:rgba(2,6,23,0.38); backdrop-filter:blur(10px);
    border:1px solid rgba(255,255,255,0.10);
    color:rgba(255,255,255,0.85); font-family:'Sora',sans-serif;
    font-size:13px; font-weight:600; cursor:pointer; transition:background 0.2s, border-color 0.2s;
}
.theme-btn:hover{ background:rgba(255,255,255,0.08); }

/* Light theme */
body.light{ background:#eef2f7; color:#0f172a; }
body.light .sidebar{ background:rgba(255,255,255,0.80); border-right:1px solid rgba(0,0,0,0.08); }
body.light .menu a{ color:#475569; }
body.light .menu a:hover{ background:rgba(0,0,0,0.05); color:#0f172a; }
body.light .welcome-box{ background:linear-gradient(135deg,rgba(109,60,200,0.18),rgba(56,108,220,0.14)); border-color:rgba(0,0,0,0.08); }
body.light .s-card{ background:rgba(255,255,255,0.82); border-color:rgba(0,0,0,0.08); }
body.light .card-title{ color:#0f172a; }
body.light .card-desc{ color:#64748b; }
body.light .flabel{ color:#94a3b8; }
body.light input[type=text],body.light input[type=email],body.light input[type=password],body.light input[type=file]{ background:rgba(0,0,0,0.04); border-color:rgba(0,0,0,0.13); color:#0f172a; }
body.light .main-title h1{ color:#0f172a; text-shadow:none; }
body.light .main-title p{ color:#64748b; }
body.light .slide-overlay,body.light .slide-bg,body.light .sidebar-shadow,body.light .slide-dots{ display:none; }
body.light .theme-btn{ background:rgba(0,0,0,0.06); border-color:rgba(0,0,0,0.10); color:#0f172a; }
body.light .logo{ background:linear-gradient(135deg,#c8940a,#b45a1a); -webkit-background-clip:text; -webkit-text-fill-color:transparent; }

/* Responsive */
@media(max-width:1100px){ .settings-grid{ grid-template-columns:1fr; } }
@media(max-width:1000px){ .sidebar{display:none;} .main{margin-left:0;width:100%;padding:20px;} .topbar{flex-direction:column;align-items:flex-start;} .welcome-box{max-width:100%;width:100%;} }
@media(max-width:640px){ .main-title h1{font-size:26px;} .s-card{padding:20px 16px 18px;} }

/* ══ BIOMETRIC STYLES ══ */
.bio-status{ display:flex; align-items:center; gap:10px; padding:10px 14px; border-radius:12px; margin-bottom:14px; font-size:13px; font-weight:600; }
.bio-on { background:rgba(34,197,94,0.12); border:1px solid rgba(34,197,94,0.22); color:#4ade80; }
.bio-off{ background:rgba(107,114,128,0.12); border:1px solid rgba(107,114,128,0.20); color:rgba(255,255,255,0.45); }
.bio-dot{ width:8px; height:8px; border-radius:50%; flex-shrink:0; }
.bio-on  .bio-dot{ background:#22c55e; animation:bp 1.5s ease infinite; }
.bio-off .bio-dot{ background:#6b7280; }
@keyframes bp{0%,100%{opacity:1}50%{opacity:.3}}
.fp-icon{ font-size:42px; text-align:center; display:block; margin:6px 0 14px; }

/* Fingerprint list */
.fp-list{ display:flex; flex-direction:column; gap:8px; margin-bottom:16px; }
.fp-item{
    display:flex; align-items:center; justify-content:space-between;
    padding:10px 14px; border-radius:12px;
    background:rgba(59,130,246,0.08); border:1px solid rgba(59,130,246,0.18);
    font-size:13px;
}
.fp-item-left{ display:flex; align-items:center; gap:10px; }
.fp-item-left span{ font-size:20px; }
.fp-item-name{ font-weight:600; color:rgba(255,255,255,0.85); }
.fp-item-date{ font-size:11px; color:rgba(255,255,255,0.38); margin-top:2px; }
.fp-del-btn{
    background:rgba(239,68,68,0.15); border:1px solid rgba(239,68,68,0.25);
    color:#f87171; border-radius:8px; padding:5px 12px;
    font-size:11px; font-weight:700; cursor:pointer; transition:all 0.2s;
}
.fp-del-btn:hover{ background:rgba(239,68,68,0.28); }

/* Add fingerprint name input */
.fp-add-row{ display:flex; gap:8px; align-items:center; margin-bottom:12px; }
.fp-add-row input{ margin-bottom:0; flex:1; }
.fp-count-badge{
    display:inline-flex; align-items:center; gap:6px;
    padding:5px 12px; border-radius:20px;
    background:rgba(59,130,246,0.12); border:1px solid rgba(59,130,246,0.20);
    font-size:12px; font-weight:700; color:#93c5fd; margin-bottom:14px;
}

.otp-inline{ display:flex; gap:6px; margin:8px 0 12px; }
.otp-inline input{ width:38px; height:44px; text-align:center; font-size:17px; font-weight:700; background:rgba(255,255,255,0.07); border:1px solid rgba(255,255,255,0.12); border-radius:9px; color:#fff; outline:none; font-family:'Sora',sans-serif; transition:border-color .2s; }
.otp-inline input:focus{ border-color:#8b5cf6; }
</style>
</head>
<body id="pageBody">

<!-- SLIDESHOW -->
<div class="slide-bg">
    <div class="slide active"></div>
    <div class="slide"></div>
    <div class="slide"></div>
    <div class="slide"></div>
    <div class="slide"></div>
</div>
<div class="slide-overlay"></div>
<div class="sidebar-shadow"></div>
<div class="slide-dots" id="slideDots">
    <div class="dot active"></div><div class="dot"></div><div class="dot"></div>
    <div class="dot"></div><div class="dot"></div>
</div>

<!-- SIDEBAR -->
<div class="sidebar">
    <div>
        <div class="logo">EstateFlow</div>
        <div class="menu">
            <a href="admin_dashboard.php"><span>🏠</span> Dashboard</a>
            <a href="users.php"><span>👥</span> Users</a>
            <a href="manage_jv.php"><span>🏢</span> JV Lands</a>
            <a href="manage_outrate.php"><span>🤝</span> Outrate Lands</a>
            <a href="manage_builders.php"><span>👷</span> Builders</a>
            <a href="settings.php" class="active"><span>⚙️</span> Settings</a>
        </div>
    </div>
    <a href="admin_logout.php" class="logout"><span>🚪</span> Logout</a>
</div>

<!-- MAIN -->
<div class="main">

    <!-- TOPBAR -->
    <div class="topbar">
        <div class="welcome-box">
            <h2>⚙️ Admin Settings</h2>
            <p>Manage your profile, security and session controls</p>
        </div>
        <div class="right-top">
            <button class="theme-btn" id="themeBtn" onclick="toggleTheme()">
                <span id="themeIcon">☀️</span>
                <span id="themeLabel">Light Mode</span>
            </button>
            <div class="admin-box">
                <img src="<?= $photoSrc ?>" alt="admin" id="topPhoto">
                <div>
                    <div class="a-name"><?= $adminName ?></div>
                    <div class="a-email"><?= $adminEmail ?></div>
                </div>
            </div>
        </div>
    </div>

    <!-- PAGE TITLE -->
    <div class="main-title">
        <h1>Settings</h1>
        <p>Update your account details, password and platform controls.</p>
    </div>

    <!-- ALERTS -->
    <?php if($success): ?>
    <div class="alert alert-success"><?= $success ?></div>
    <?php endif; ?>
    <?php if($error): ?>
    <div class="alert alert-error">⚠️ <?= $error ?></div>
    <?php endif; ?>

    <!-- SETTINGS GRID -->
    <div class="settings-grid">

        <!-- 1. Change Name -->
        <div class="s-card c-blue">
            <div class="card-icon ic-blue">✏️</div>
            <div class="card-title">Change Name</div>
            <div class="card-desc">Update the admin display name shown across the panel.</div>
            <form method="POST">
                <label class="flabel">Full Name</label>
                <input type="text" name="admin_name" value="<?= $adminName ?>" placeholder="Enter admin name" required>
                <button type="submit" name="update_name" class="btn btn-blue">💾 Save Name</button>
            </form>
        </div>

        <!-- 2. Change Email -->
        <div class="s-card c-green">
            <div class="card-icon ic-green">📧</div>
            <div class="card-title">Change Email</div>
            <div class="card-desc">Update your login email and contact address.</div>
            <form method="POST">
                <label class="flabel">Email Address</label>
                <input type="email" name="admin_email" value="<?= $adminEmail ?>" placeholder="Enter email" required>
                <button type="submit" name="update_email" class="btn btn-green">💾 Save Email</button>
            </form>
        </div>

        <!-- 3. Change Password -->
        <div class="s-card c-purple">
            <div class="card-icon ic-purple">🔐</div>
            <div class="card-title">Change Password</div>
            <div class="card-desc">Use a strong, unique password to keep your account safe.</div>
            <form method="POST">
                <label class="flabel">Current Password</label>
                <div class="pw-row">
                    <input type="password" name="current_password" id="pw0" placeholder="••••••••" required>
                    <button type="button" class="pw-eye" onclick="eye('pw0',this)">👁</button>
                </div>
                <label class="flabel">New Password</label>
                <div class="pw-row">
                    <input type="password" name="new_password" id="pw1" placeholder="••••••••" oninput="strength(this.value)" required>
                    <button type="button" class="pw-eye" onclick="eye('pw1',this)">👁</button>
                </div>
                <div class="str-bar"><div class="str-fill" id="strFill"></div></div>
                <div class="str-label" id="strLabel"></div>
                <label class="flabel">Confirm Password</label>
                <div class="pw-row">
                    <input type="password" name="confirm_password" id="pw2" placeholder="••••••••" required>
                    <button type="button" class="pw-eye" onclick="eye('pw2',this)">👁</button>
                </div>
                <button type="submit" name="change_password" class="btn btn-purple">🔒 Update Password</button>
            </form>
        </div>

        <!-- 4. Upload Photo -->
        <div class="s-card c-orange">
            <div class="card-icon ic-orange">📷</div>
            <div class="card-title">Profile Photo</div>
            <div class="card-desc">Upload a new photo. JPG · PNG · WebP · max 3 MB.</div>
            <form method="POST" enctype="multipart/form-data">
                <div class="photo-row">
                    <img src="<?= $photoSrc ?>" alt="preview" class="photo-ring" id="photoPreview">
                    <div class="photo-hint">Current photo<br>Click below to pick a new image<br><span style="font-size:11px;opacity:.6">JPG · PNG · WebP · GIF</span></div>
                </div>
                <label class="flabel">Choose Image</label>
                <input type="file" name="admin_photo" id="fileInput" accept="image/jpeg,image/png,image/webp,image/gif" onchange="preview(this)">
                <button type="submit" name="upload_photo" class="btn btn-orange">📤 Upload Photo</button>
            </form>
        </div>

        <!-- 5. Logout All Users -->
        <div class="s-card c-red full">
            <div class="danger-row">
                <div class="card-icon ic-red" style="flex-shrink:0">⚠️</div>
                <div class="dr-text">
                    <h4>Danger Zone — Logout All Users</h4>
                    <p>Immediately ends <strong>all active user sessions</strong> across the platform. Every logged-in user will be signed out instantly. The admin session is <strong>not</strong> affected. This action cannot be undone.</p>
                </div>
            </div>
            <form method="POST" onsubmit="return openModal(event)">
                <button type="submit" name="logout_all_users" class="btn btn-red">🚨 Logout All Users Now</button>
            </form>
        </div>

        <!-- 6. Biometric Management — MULTIPLE FINGERPRINTS -->
        <div class="s-card c-blue full" style="border-color:rgba(96,165,250,0.2);">
            <div class="card-icon ic-blue">🫆</div>
            <div class="card-title">Biometric Login</div>
            <div class="card-desc">Register one or more fingerprints or Face ID for fast, secure login. You can add multiple fingers for convenience.</div>

            <!-- Count badge -->
            <div class="fp-count-badge">
                🫆 <span id="fpCountText"><?= $fpCount ?> fingerprint<?= $fpCount != 1 ? 's' : '' ?> registered</span>
            </div>

            <!-- Registered fingerprints list -->
            <div class="fp-list" id="fpList">
                <?php if(empty($fingerprints)): ?>
                <div style="font-size:13px;color:rgba(255,255,255,0.35);padding:8px 0;" id="fpEmpty">No fingerprints registered yet.</div>
                <?php else: ?>
                <?php foreach($fingerprints as $fp): ?>
                <div class="fp-item" id="fpItem_<?= $fp['id'] ?>">
                    <div class="fp-item-left">
                        <span>🫆</span>
                        <div>
                            <div class="fp-item-name"><?= htmlspecialchars($fp['label'] ?? 'Fingerprint') ?></div>
                            <div class="fp-item-date">Added: <?= date('d M Y, h:i A', strtotime($fp['created_at'])) ?></div>
                        </div>
                    </div>
                    <button class="fp-del-btn" onclick="deleteFingerprint(<?= $fp['id'] ?>, this)">🗑 Remove</button>
                </div>
                <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <!-- Add new fingerprint -->
            <label class="flabel">Fingerprint Label (e.g. Right Thumb, Index Finger)</label>
            <div class="fp-add-row">
                <input type="text" id="fpLabel" placeholder="e.g. Right Thumb" maxlength="40">
            </div>
            <button class="btn btn-blue" id="bioRegBtn" onclick="registerBiometric()">
                👆 Add Fingerprint / Face ID
            </button>

            <div id="bioMsg" style="margin-top:12px;font-size:13px;"></div>
        </div>

    </div><!-- /settings-grid -->
</div><!-- /main -->

<!-- CONFIRM MODAL -->
<div class="overlay" id="modal">
    <div class="modal">
        <div class="m-icon">🚨</div>
        <h3>Logout All Users?</h3>
        <p>This will instantly end every active user session.<br>They will need to log in again.<br><strong>This cannot be undone.</strong></p>
        <div class="modal-btns">
            <button class="btn btn-cancel" onclick="closeModal()">Cancel</button>
            <button class="btn btn-red" id="confirmBtn">Yes, Logout All</button>
        </div>
    </div>
</div>

<script>
/* Slideshow */
(function(){
    var photos=[
        'https://images.unsplash.com/photo-1486325212027-8081e485255e?w=1920&q=95',
        'https://images.unsplash.com/photo-1545324418-cc1a3fa10c00?w=1920&q=95',
        'https://images.unsplash.com/photo-1560518883-ce09059eeffa?w=1920&q=95',
        'https://images.unsplash.com/photo-1582407947304-fd86f028f716?w=1920&q=95',
        'https://images.unsplash.com/photo-1512917774080-9991f1c4c750?w=1920&q=95'
    ];
    var slides=document.querySelectorAll('.slide'),dots=document.querySelectorAll('.dot'),cur=0;
    slides.forEach(function(s,i){ s.style.backgroundImage="url('"+photos[i]+"')"; });
    function goTo(n){ slides[cur].classList.remove('active'); dots[cur].classList.remove('active'); cur=n%photos.length; slides[cur].classList.add('active'); dots[cur].classList.add('active'); }
    setInterval(function(){ goTo(cur+1); },6000);
})();

/* Theme */
(function(){ var t=localStorage.getItem('ef_theme')||'dark'; applyTheme(t); })();
function applyTheme(t){ document.getElementById('pageBody').className=t==='light'?'light':''; document.getElementById('themeIcon').textContent=t==='dark'?'☀️':'🌙'; document.getElementById('themeLabel').textContent=t==='dark'?'Light Mode':'Dark Mode'; localStorage.setItem('ef_theme',t); }
function toggleTheme(){ var cur=localStorage.getItem('ef_theme')||'dark'; applyTheme(cur==='dark'?'light':'dark'); }

/* Password eye */
function eye(id,btn){ var el=document.getElementById(id); el.type=el.type==='password'?'text':'password'; btn.textContent=el.type==='password'?'👁':'🙈'; }

/* Password strength */
function strength(v){ var f=document.getElementById('strFill'),l=document.getElementById('strLabel'); var s=0; if(v.length>=6)s++; if(v.length>=10)s++; if(/[A-Z]/.test(v))s++; if(/[0-9]/.test(v))s++; if(/[^A-Za-z0-9]/.test(v))s++; var w=['0%','20%','40%','65%','85%','100%'][s]; var c=['#ef4444','#f97316','#eab308','#84cc16','#22c55e','#10b981'][s]; var n=['','Weak','Fair','Good','Strong','Very Strong'][s]||''; f.style.width=w; f.style.background=c; l.textContent=n; l.style.color=c; }

/* Photo preview */
function preview(input){ if(input.files&&input.files[0]){ var r=new FileReader(); r.onload=function(e){ document.getElementById('photoPreview').src=e.target.result; document.getElementById('topPhoto').src=e.target.result; }; r.readAsDataURL(input.files[0]); } }

/* Modal */
var _form=null;
function openModal(e){ e.preventDefault(); _form=e.target; document.getElementById('modal').classList.add('show'); return false; }
function closeModal(){ document.getElementById('modal').classList.remove('show'); }
document.getElementById('confirmBtn').onclick=function(){ closeModal(); if(_form)_form.submit(); };
document.getElementById('modal').addEventListener('click',function(e){ if(e.target===this)closeModal(); });

/* Auto-dismiss alerts */
document.querySelectorAll('.alert').forEach(function(a){ setTimeout(function(){ a.style.transition='opacity .5s'; a.style.opacity='0'; },4500); setTimeout(function(){ a.remove(); },5000); });

/* ══ BIOMETRIC — MULTIPLE FINGERPRINTS ══ */
var ADMIN_ID = <?= $adminId ?>;
var fpCount  = <?= $fpCount ?>;

function bioMsg(txt, ok){
    var d=document.getElementById('bioMsg');
    d.innerHTML='<div class="alert '+(ok?'alert-success':'alert-error')+'">'+txt+'</div>';
    setTimeout(function(){ d.innerHTML=''; }, 5000);
}

function updateFpCount(n){
    fpCount = n;
    document.getElementById('fpCountText').textContent = n+' fingerprint'+(n!=1?'s':'')+' registered';
}

/* Register new fingerprint */
async function registerBiometric(){
    if(!window.PublicKeyCredential){ bioMsg('⚠️ WebAuthn not supported on this browser.'); return; }

    var label = document.getElementById('fpLabel').value.trim();
    if(!label){ bioMsg('⚠️ Please enter a label (e.g. Right Thumb).'); return; }

    var btn = document.getElementById('bioRegBtn');
    btn.disabled=true; btn.textContent='Scanning...';

    try{
        var res  = await fetch('webauthn_ajax.php',{method:'POST',body:new URLSearchParams({action:'get_register_options',admin_id:ADMIN_ID})});
        var data = await res.json();
        if(!data.ok){ bioMsg('❌ '+(data.msg||'Failed')); btn.disabled=false; btn.textContent='👆 Add Fingerprint / Face ID'; return; }

        var opts = data.options;
        opts.challenge = Uint8Array.from(atob(opts.challenge.replace(/-/g,'+').replace(/_/g,'/')),c=>c.charCodeAt(0));
        opts.user.id   = Uint8Array.from(atob(opts.user.id.replace(/-/g,'+').replace(/_/g,'/')),c=>c.charCodeAt(0));

        var cred   = await navigator.credentials.create({publicKey:opts});
        var credId = btoa(String.fromCharCode(...new Uint8Array(cred.rawId))).replace(/\+/g,'-').replace(/\//g,'_').replace(/=/g,'');
        var pubKey = btoa(String.fromCharCode(...new Uint8Array(cred.response.getPublicKey?cred.response.getPublicKey():new Uint8Array(0)))).replace(/\+/g,'-').replace(/\//g,'_').replace(/=/g,'');

        var verRes  = await fetch('webauthn_ajax.php',{method:'POST',body:new URLSearchParams({action:'verify_register',admin_id:ADMIN_ID,credential_id:credId,public_key:pubKey,label:label})});
        var verData = await verRes.json();

        if(verData.ok){
            bioMsg('✅ Fingerprint "'+label+'" registered!', true);
            document.getElementById('fpLabel').value = '';

            /* Add to UI */
            var empty = document.getElementById('fpEmpty');
            if(empty) empty.remove();

            var now = new Date();
            var dateStr = now.toLocaleDateString('en-IN',{day:'2-digit',month:'short',year:'numeric'})+', '+now.toLocaleTimeString('en-IN',{hour:'2-digit',minute:'2-digit'});
            var item = document.createElement('div');
            item.className = 'fp-item';
            item.id = 'fpItem_'+verData.id;
            item.innerHTML = '<div class="fp-item-left"><span>🫆</span><div><div class="fp-item-name">'+label+'</div><div class="fp-item-date">Added: '+dateStr+'</div></div></div><button class="fp-del-btn" onclick="deleteFingerprint('+verData.id+', this)">🗑 Remove</button>';
            document.getElementById('fpList').appendChild(item);
            updateFpCount(fpCount+1);
        } else {
            bioMsg('❌ '+(verData.msg||'Registration failed'));
        }
    } catch(e){
        if(e.name==='NotAllowedError') bioMsg('⚠️ Biometric scan cancelled.');
        else bioMsg('❌ Error: '+e.message);
    }
    btn.disabled=false; btn.textContent='👆 Add Fingerprint / Face ID';
}

/* Delete a fingerprint */
async function deleteFingerprint(fpId, btn){
    if(!confirm('Remove this fingerprint?')) return;
    btn.disabled=true; btn.textContent='Removing...';

    var res  = await fetch('webauthn_ajax.php',{method:'POST',body:new URLSearchParams({action:'delete_credential',credential_db_id:fpId,admin_id:ADMIN_ID})});
    var data = await res.json();

    if(data.ok){
        document.getElementById('fpItem_'+fpId).remove();
        updateFpCount(fpCount-1);
        if(fpCount<=0){
            var list = document.getElementById('fpList');
            list.innerHTML = '<div style="font-size:13px;color:rgba(255,255,255,0.35);padding:8px 0;" id="fpEmpty">No fingerprints registered yet.</div>';
        }
        bioMsg('✅ Fingerprint removed.', true);
    } else {
        bioMsg('❌ '+(data.msg||'Failed to remove'));
        btn.disabled=false; btn.textContent='🗑 Remove';
    }
}
</script>
</body>
</html>