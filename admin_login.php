<?php
session_start();
include 'config/db.php';

if(isset($_SESSION['admin'])){
    header("Location:admin_dashboard.php");
    exit();
}

$msg = "";

/* ── OTP Login Verify (step 2) ── */
if(isset($_POST['verify_otp_login'])){
    $adminId = intval($_POST['admin_id'] ?? 0);
    $otp     = trim($_POST['otp_code'] ?? '');

    $otpRow = mysqli_fetch_assoc(mysqli_query($conn,
        "SELECT * FROM admin_otp WHERE admin_id='$adminId' AND otp_type='login'
         AND used=0 AND expires_at > NOW() ORDER BY id DESC LIMIT 1"));

    if($otpRow && $otpRow['otp_code'] === $otp){
        mysqli_query($conn,"UPDATE admin_otp SET used=1 WHERE id='{$otpRow['id']}'");
        session_regenerate_id(true);
        $_SESSION['admin'] = $adminId;
        header("Location:admin_dashboard.php");
        exit();
    } else {
        $msg = "❌ Invalid or expired OTP. Please try again.";
        // Stay on OTP step — pass admin_id back
        $keepOtpStep = intval($adminId);
    }
}

/* ── Password Login ── */
if(isset($_POST['password_login'])){
    $email    = trim($_POST['email']    ?? '');
    $password = trim($_POST['password'] ?? '');
    $safeEmail= mysqli_real_escape_string($conn,$email);
    $result   = mysqli_query($conn,"SELECT * FROM admin WHERE email='$safeEmail' LIMIT 1");

    if($result && mysqli_num_rows($result) > 0){
        $admin  = mysqli_fetch_assoc($result);
        $stored = $admin['password'];
        $valid  = password_verify($password,$stored) || ($password===$stored);
        if($valid){
            session_regenerate_id(true);
            $_SESSION['admin'] = $admin['id'];
            header("Location:admin_dashboard.php");
            exit();
        } else {
            $msg = "❌ Invalid credentials. Please try again.";
        }
    } else {
        $msg = "❌ No admin found with that email.";
    }
}

/* ── Index.php Management tab direct POST (password_login not set) ── */
if($_SERVER['REQUEST_METHOD']==='POST' && !isset($_POST['password_login'])
   && !isset($_POST['verify_otp_login']) && isset($_POST['email']) && isset($_POST['password'])){
    $email    = trim($_POST['email']    ?? '');
    $password = trim($_POST['password'] ?? '');
    $safeEmail= mysqli_real_escape_string($conn,$email);
    $result   = mysqli_query($conn,"SELECT * FROM admin WHERE email='$safeEmail' LIMIT 1");
    if($result && mysqli_num_rows($result) > 0){
        $admin  = mysqli_fetch_assoc($result);
        $stored = $admin['password'];
        $valid  = password_verify($password,$stored) || ($password===$stored);
        if($valid){
            session_regenerate_id(true);
            $_SESSION['admin'] = $admin['id'];
            header("Location:admin_dashboard.php");
            exit();
        } else {
            $msg = "❌ Invalid credentials.";
        }
    } else {
        $msg = "❌ No admin found with that email.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Login – EstateFlow</title>
<link href="https://fonts.googleapis.com/css2?family=Clash+Display:wght@400;500;600;700&family=Instrument+Sans:wght@400;500;600&display=swap" rel="stylesheet">
<style>
:root{
  --gold:#c9a84c;--gold2:#f0d080;
  --bg:#07080f;--border:rgba(255,255,255,0.08);
  --text:rgba(255,255,255,0.92);--muted:rgba(255,255,255,0.45);
}
*{margin:0;padding:0;box-sizing:border-box;}
html,body{height:100%;background:var(--bg);color:var(--text);font-family:'Instrument Sans',sans-serif;overflow:hidden;}

.slides{position:fixed;inset:0;z-index:0;}
.slide{position:absolute;inset:0;opacity:0;transition:opacity 2.5s ease;background-size:cover;background-position:center;animation:kz 16s linear infinite;}
.slide.on{opacity:1;}
@keyframes kz{0%{transform:scale(1)}100%{transform:scale(1.07)}}
.slide:nth-child(1){background-image:url('https://images.unsplash.com/photo-1486406146926-c627a92ad1ab?w=1920&q=90');}
.slide:nth-child(2){background-image:url('https://images.unsplash.com/photo-1494526585095-c41746248156?w=1920&q=90');}
.slide:nth-child(3){background-image:url('https://images.unsplash.com/photo-1460317442991-0ec209397118?w=1920&q=90');}
.scrim{position:fixed;inset:0;z-index:1;background:rgba(7,8,15,0.70);}
.amb{position:fixed;inset:0;z-index:1;pointer-events:none;}
.orb{position:absolute;border-radius:50%;filter:blur(80px);opacity:.12;}
.orb1{width:600px;height:600px;background:radial-gradient(circle,#4f3aff,transparent 70%);top:-150px;right:-100px;}
.orb2{width:400px;height:400px;background:radial-gradient(circle,#c9a84c,transparent 70%);bottom:-80px;left:50px;}

.page{position:relative;z-index:10;min-height:100vh;display:flex;align-items:center;justify-content:center;padding:20px;}

.card{
  width:900px;max-width:96vw;
  border-radius:26px;overflow:hidden;
  display:flex;
  background:rgba(7,8,15,0.55);
  border:1px solid rgba(201,168,76,0.15);
  backdrop-filter:blur(22px);
  box-shadow:0 40px 100px rgba(0,0,0,0.55);
  animation:fadeUp .5s cubic-bezier(.22,1,.36,1) both;
}
@keyframes fadeUp{from{opacity:0;transform:translateY(24px)}to{opacity:1;transform:translateY(0)}}

.form-side{flex:1;padding:50px 46px;display:flex;flex-direction:column;justify-content:center;}
.logo-row{display:flex;align-items:center;gap:10px;margin-bottom:32px;}
.logo-icon{width:38px;height:38px;background:linear-gradient(135deg,var(--gold),#e8a84c);border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:18px;box-shadow:0 0 18px rgba(201,168,76,0.3);}
.logo-text{font-family:'Clash Display',sans-serif;font-size:20px;font-weight:700;background:linear-gradient(135deg,var(--gold2),var(--gold));-webkit-background-clip:text;-webkit-text-fill-color:transparent;}
.form-title{font-family:'Clash Display',sans-serif;font-size:32px;font-weight:700;line-height:1.1;margin-bottom:6px;}
.form-sub{font-size:13.5px;color:var(--muted);margin-bottom:28px;}

.tabs{display:flex;gap:0;border:1px solid var(--border);border-radius:12px;overflow:hidden;margin-bottom:24px;}
.tab{flex:1;padding:11px;text-align:center;font-size:13px;font-weight:600;cursor:pointer;color:var(--muted);background:rgba(255,255,255,0.03);transition:all .2s;border:none;font-family:inherit;}
.tab.active{background:linear-gradient(135deg,rgba(201,168,76,0.18),rgba(201,168,76,0.06));color:var(--gold2);border-bottom:2px solid var(--gold);}

.panel{display:none;}.panel.show{display:block;}

.alert{display:flex;align-items:center;gap:9px;padding:12px 16px;border-radius:12px;font-size:13px;font-weight:500;margin-bottom:16px;}
.alert.err{background:rgba(239,68,68,0.12);border:1px solid rgba(239,68,68,0.22);color:#f87171;animation:shake .4s ease;}
.alert.ok {background:rgba(34,197,94,0.10);border:1px solid rgba(34,197,94,0.22);color:#4ade80;}
@keyframes shake{0%,100%{transform:translateX(0)}20%{transform:translateX(-6px)}40%{transform:translateX(6px)}60%{transform:translateX(-4px)}80%{transform:translateX(4px)}}

.field{margin-bottom:16px;}
.field label{display:block;font-size:11px;font-weight:600;letter-spacing:.08em;text-transform:uppercase;color:var(--muted);margin-bottom:7px;}
.field input{
  width:100%;padding:13px 16px;
  background:rgba(255,255,255,0.06);
  border:1px solid var(--border);
  border-radius:12px;color:var(--text);
  font-family:'Instrument Sans',sans-serif;font-size:14px;
  outline:none;transition:border-color .2s,background .2s;
}
.field input:focus{border-color:var(--gold);background:rgba(255,255,255,0.09);}
.field input::placeholder{color:rgba(255,255,255,0.22);}
.pw-wrap{position:relative;}
.pw-wrap input{padding-right:44px;}
.pw-eye{position:absolute;right:13px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:var(--muted);font-size:16px;padding:0;transition:color .2s;}
.pw-eye:hover{color:var(--text);}

.btn{width:100%;padding:14px;border:none;border-radius:13px;font-family:'Instrument Sans',sans-serif;font-size:14.5px;font-weight:600;cursor:pointer;transition:transform .2s,box-shadow .2s,opacity .2s;}
.btn:hover{transform:translateY(-2px);}
.btn:disabled{opacity:.6;cursor:not-allowed;transform:none;}
.btn-gold{background:linear-gradient(135deg,var(--gold),#e8a84c);color:#07080f;box-shadow:0 4px 18px rgba(201,168,76,0.25);}
.btn-gold:hover{box-shadow:0 8px 28px rgba(201,168,76,0.38);}
.btn-glass{background:rgba(255,255,255,0.07);border:1px solid var(--border);color:var(--text);}
.btn-bio{background:linear-gradient(135deg,rgba(96,165,250,0.2),rgba(59,130,246,0.1));border:1px solid rgba(96,165,250,0.3);color:#93c5fd;display:flex;align-items:center;justify-content:center;gap:10px;}
.btn-bio:hover{background:linear-gradient(135deg,rgba(96,165,250,0.3),rgba(59,130,246,0.18));}

.divider{display:flex;align-items:center;gap:10px;margin:14px 0;color:var(--muted);font-size:12px;}
.divider::before,.divider::after{content:'';flex:1;height:1px;background:var(--border);}

/* Biometric */
.bio-icon{text-align:center;margin:8px 0 18px;}
.bio-icon .fp{font-size:52px;display:block;animation:pulse-fp 2s ease infinite;}
@keyframes pulse-fp{0%,100%{transform:scale(1);filter:drop-shadow(0 0 0 #60a5fa)}50%{transform:scale(1.05);filter:drop-shadow(0 0 10px #60a5fa)}}
.bio-title{font-family:'Clash Display',sans-serif;font-size:20px;font-weight:600;text-align:center;margin-bottom:6px;}
.bio-sub{font-size:13px;color:var(--muted);text-align:center;margin-bottom:20px;line-height:1.5;}

/* OTP boxes */
.otp-box{display:flex;gap:8px;justify-content:center;margin:12px 0 16px;}
.otp-box input{
  width:46px;height:54px;text-align:center;
  font-size:20px;font-weight:700;
  font-family:'Clash Display',sans-serif;
  background:rgba(255,255,255,0.07);
  border:1.5px solid var(--border);
  border-radius:12px;color:var(--text);
  outline:none;transition:border-color .2s,background .2s;
}
.otp-box input:focus{border-color:var(--gold);background:rgba(255,255,255,0.11);}
.otp-box input.filled{border-color:rgba(201,168,76,0.5);}
.timer{text-align:center;font-size:12.5px;color:var(--muted);margin-bottom:14px;}
.timer span{color:var(--gold2);font-weight:600;font-variant-numeric:tabular-nums;}

/* Photo */
.photo-side{width:340px;flex-shrink:0;position:relative;overflow:hidden;}
.photo-side img{width:100%;height:100%;object-fit:cover;display:block;}
.photo-side::after{content:'';position:absolute;inset:0;background:linear-gradient(160deg,rgba(79,58,255,0.28),rgba(201,168,76,0.32));}
.photo-text{position:absolute;bottom:32px;left:26px;right:26px;z-index:2;}
.photo-text h3{font-family:'Clash Display',sans-serif;font-size:22px;font-weight:700;margin-bottom:7px;text-shadow:0 2px 12px rgba(0,0,0,.4);}
.photo-text p{font-size:13px;color:rgba(255,255,255,.7);line-height:1.55;}
.photo-badge{display:inline-flex;align-items:center;gap:6px;padding:5px 12px;background:rgba(34,197,94,0.15);border:1px solid rgba(34,197,94,0.25);border-radius:20px;font-size:11px;font-weight:600;color:#4ade80;margin-bottom:12px;}
.photo-dot{width:6px;height:6px;border-radius:50%;background:#22c55e;animation:pd 1.5s ease infinite;}
@keyframes pd{0%,100%{opacity:1}50%{opacity:.3}}
.back-link{display:inline-flex;align-items:center;gap:6px;font-size:12.5px;color:var(--muted);background:none;border:none;cursor:pointer;font-family:inherit;padding:8px 0;transition:color .2s;}
.back-link:hover{color:var(--text);}

@media(max-width:760px){.photo-side{display:none;}.card{max-width:430px;}.form-side{padding:36px 26px;}}
</style>
</head>
<body>

<div class="slides">
  <div class="slide on"></div><div class="slide"></div><div class="slide"></div>
</div>
<div class="scrim"></div>
<div class="amb"><div class="orb orb1"></div><div class="orb orb2"></div></div>

<div class="page">
<div class="card">

  <!-- FORM SIDE -->
  <div class="form-side">
    <div class="logo-row">
      <div class="logo-icon">🏛️</div>
      <div class="logo-text">EstateFlow</div>
    </div>
    <div class="form-title">Welcome Back</div>
    <div class="form-sub">Sign in to your admin panel</div>

    <!-- PHP error -->
    <?php if($msg !== ""): ?>
    <div class="alert err"><?= htmlspecialchars($msg) ?></div>
    <?php endif; ?>
    <!-- JS messages -->
    <div id="jsMsg"></div>

    <!-- TABS -->
    <div class="tabs">
      <button class="tab active" id="tabPass" onclick="switchTab('pass')">🔑 Password</button>
      <button class="tab" id="tabBio"  onclick="switchTab('bio')">👆 Biometric</button>
      <button class="tab" id="tabOtp"  onclick="switchTab('otp')">📧 OTP Login</button>
    </div>

    <!-- ══ PASSWORD ══ -->
    <div class="panel show" id="panelPass">
      <form method="POST">
        <div class="field">
          <label>Admin Email</label>
          <input type="email" name="email" placeholder="estateflowofficial@gmail.com"
                 value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required autofocus>
        </div>
        <div class="field">
          <label>Password</label>
          <div class="pw-wrap">
            <input type="password" name="password" id="pw1" placeholder="••••••••" required>
            <button type="button" class="pw-eye" onclick="togglePw('pw1',this)">👁</button>
          </div>
        </div>
        <button type="submit" name="password_login" class="btn btn-gold">🔐 Secure Login</button>
      </form>
      <div class="divider">or</div>
      <button class="btn btn-bio" onclick="switchTab('bio')">👆 Use Fingerprint / Face ID</button>
    </div>

    <!-- ══ BIOMETRIC ══ -->
    <div class="panel" id="panelBio">
      <div class="bio-icon"><span class="fp">🫆</span></div>
      <div class="bio-title">Biometric Login</div>
      <div class="bio-sub">Enter your registered admin email,<br>then authenticate with fingerprint or Face ID.</div>
      <div class="field">
        <label>Admin Email</label>
        <input type="email" id="bioEmail" placeholder="estateflowofficial@gmail.com">
      </div>
      <button class="btn btn-bio" onclick="startBiometricLogin()">👆 Authenticate with Biometric</button>
      <div class="divider">or</div>
      <button class="btn btn-glass" onclick="switchTab('pass')">🔑 Use Password Instead</button>
    </div>

    <!-- ══ OTP LOGIN ══ -->
    <div class="panel" id="panelOtp">

      <!-- Step 1: enter email & send OTP -->
      <div id="otpStep1">
        <?php
        // If redirected from index.php Management tab — pre-fill email
        $prefillEmail = htmlspecialchars($_GET['email'] ?? '');
        ?>
        <div class="field">
          <label>Admin Email</label>
          <input type="email" id="otpEmail" placeholder="estateflowofficial@gmail.com"
                 value="<?= $prefillEmail ?>">
        </div>
        <button class="btn btn-gold" id="sendOtpBtn" onclick="sendLoginOtp()">📧 Send OTP to Email</button>
        <div class="divider">or</div>
        <button class="btn btn-glass" onclick="switchTab('pass')">🔑 Use Password Instead</button>
      </div>

      <!-- Step 2: enter OTP -->
      <div id="otpStep2" style="display:none">
        <div class="bio-title">Check Your Email</div>
        <div class="bio-sub">6-digit code sent to<br>
          <strong id="otpEmailDisplay" style="color:var(--gold2)"></strong>
        </div>
        <form method="POST" id="otpForm">
          <input type="hidden" name="admin_id" id="otpAdminId">
          <input type="hidden" name="verify_otp_login" value="1">
          <input type="hidden" name="otp_code" id="otpFinalCode">
          <div class="otp-box">
            <?php for($i=0;$i<6;$i++): ?>
            <input type="text" maxlength="1" inputmode="numeric" pattern="[0-9]"
                   class="otp-digit" oninput="otpMove(this,<?=$i?>)" onkeydown="otpBack(event,<?=$i?>)">
            <?php endfor; ?>
          </div>
          <div class="timer">Expires in <span id="otpTimer">05:00</span></div>
          <button type="button" class="btn btn-gold" onclick="submitOtp()">✅ Verify & Login</button>
        </form>
        <div style="margin-top:12px;display:flex;gap:8px;align-items:center;flex-wrap:wrap;">
          <button class="back-link" onclick="goOtpStep1()">← Back</button>
          <button class="back-link" id="resendBtn" onclick="resendOtp()" style="margin-left:auto">🔄 Resend OTP</button>
        </div>
      </div>

      <?php
      /* If OTP verify failed, stay on step 2 */
      if(!empty($keepOtpStep)): ?>
      <script>
        document.addEventListener('DOMContentLoaded',function(){
          switchTab('otp');
          document.getElementById('otpStep1').style.display='none';
          document.getElementById('otpStep2').style.display='block';
          document.getElementById('otpAdminId').value='<?= $keepOtpStep ?>';
          startOtpTimer();
        });
      </script>
      <?php endif; ?>

    </div>
  </div>

  <!-- PHOTO SIDE -->
  <div class="photo-side">
    <img src="https://images.unsplash.com/photo-1486406146926-c627a92ad1ab?w=800&q=90" alt="building">
    <div class="photo-text">
      <div class="photo-badge"><div class="photo-dot"></div> Secure Access</div>
      <h3>EstateFlow Admin</h3>
      <p>Manage properties, users and approvals from one powerful dashboard. Fingerprint & Face ID supported.</p>
    </div>
  </div>

</div>
</div>

<script>
/* Slideshow */
(function(){var s=document.querySelectorAll('.slide'),i=0;setInterval(function(){s[i].classList.remove('on');i=(i+1)%s.length;s[i].classList.add('on');},7000);})();

/* Tabs */
function switchTab(t){
  ['pass','bio','otp'].forEach(function(k,i){
    document.getElementById('tab'+k.charAt(0).toUpperCase()+k.slice(1)).classList.toggle('active',k===t);
    document.getElementById('panel'+k.charAt(0).toUpperCase()+k.slice(1)).classList.toggle('show',k===t);
  });
}
// Fix capitalisation for tab IDs
(function(){
  document.getElementById('tabPass').id='tabPass';
  document.getElementById('tabBio').id='tabBio';
  document.getElementById('tabOtp').id='tabOtp';
})();

/* Pw toggle */
function togglePw(id,btn){var i=document.getElementById(id);i.type=i.type==='password'?'text':'password';btn.textContent=i.type==='password'?'👁':'🙈';}

/* JS alert */
function showMsg(txt,type){
  var d=document.getElementById('jsMsg');
  d.innerHTML='<div class="alert '+(type||'err')+'">'+txt+'</div>';
  if(type==='ok') setTimeout(function(){d.innerHTML='';},5000);
}

/* ══ BIOMETRIC LOGIN ══ */
async function startBiometricLogin(){
  var email=document.getElementById('bioEmail').value.trim();
  if(!email){showMsg('Please enter your admin email first.');return;}
  if(!window.PublicKeyCredential){showMsg('⚠️ Biometric not supported on this browser.');return;}
  try{
    var res=await fetch('webauthn_ajax.php',{method:'POST',body:new URLSearchParams({action:'get_auth_options',email:email})});
    var data=await res.json();
    if(!data.ok){showMsg('❌ '+data.msg);return;}
    var opts=data.options;
    opts.challenge=Uint8Array.from(atob(opts.challenge.replace(/-/g,'+').replace(/_/g,'/')),c=>c.charCodeAt(0));
    opts.allowCredentials=opts.allowCredentials.map(function(c){
      return{type:c.type,id:Uint8Array.from(atob(c.id.replace(/-/g,'+').replace(/_/g,'/')),x=>x.charCodeAt(0))};
    });
    var assertion=await navigator.credentials.get({publicKey:opts});
    var credId=btoa(String.fromCharCode(...new Uint8Array(assertion.rawId))).replace(/\+/g,'-').replace(/\//g,'_').replace(/=/g,'');
    var verRes=await fetch('webauthn_ajax.php',{method:'POST',body:new URLSearchParams({action:'verify_auth',admin_id:data.admin_id,credential_id:credId})});
    var verData=await verRes.json();
    if(verData.ok){window.location.href=verData.redirect;}
    else{showMsg('❌ '+verData.msg);}
  }catch(e){
    showMsg(e.name==='NotAllowedError'?'⚠️ Biometric cancelled.':'❌ '+e.message);
  }
}

/* ══ OTP LOGIN ══ */
var otpCountdown=null, otpAdminIdVal=0, otpEmailVal='';

async function sendLoginOtp(){
  var email=document.getElementById('otpEmail').value.trim();
  if(!email){showMsg('Please enter your admin email.');return;}
  var btn=document.getElementById('sendOtpBtn');
  btn.disabled=true; btn.textContent='Sending...';

  try{
    var res=await fetch('send_otp.php',{
      method:'POST',
      body:new URLSearchParams({email:email, otp_type:'login'})
    });
    var data=await res.json();

    if(data.ok){
      otpAdminIdVal=data.admin_id;
      otpEmailVal=email;
      document.getElementById('otpAdminId').value=data.admin_id;
      document.getElementById('otpEmailDisplay').textContent=email;
      document.getElementById('otpStep1').style.display='none';
      document.getElementById('otpStep2').style.display='block';
      // Focus first OTP box
      document.querySelectorAll('.otp-digit')[0].focus();
      startOtpTimer();
      showMsg('✅ OTP sent! Check your email.','ok');
    } else {
      showMsg('❌ '+data.msg);
      btn.disabled=false; btn.textContent='📧 Send OTP to Email';
    }
  } catch(e){
    showMsg('❌ Network error. Try again.');
    btn.disabled=false; btn.textContent='📧 Send OTP to Email';
  }
}

async function resendOtp(){
  var btn=document.getElementById('resendBtn');
  btn.disabled=true; btn.textContent='Sending...';
  try{
    var res=await fetch('send_otp.php',{method:'POST',body:new URLSearchParams({admin_id:otpAdminIdVal,otp_type:'login'})});
    var data=await res.json();
    if(data.ok){startOtpTimer();showMsg('✅ New OTP sent!','ok');}
    else{showMsg('❌ '+data.msg);}
  }catch(e){showMsg('❌ Network error.');}
  setTimeout(function(){btn.disabled=false;btn.textContent='🔄 Resend OTP';},3000);
}

function startOtpTimer(){
  var secs=300;
  if(otpCountdown)clearInterval(otpCountdown);
  otpCountdown=setInterval(function(){
    secs--;
    var m=Math.floor(secs/60),s=secs%60;
    var el=document.getElementById('otpTimer');
    if(el) el.textContent=(m<10?'0':'')+m+':'+(s<10?'0':'')+s;
    if(secs<=0){clearInterval(otpCountdown);if(el)el.textContent='Expired';}
  },1000);
}

function otpMove(el,idx){
  el.value=el.value.replace(/[^0-9]/g,'');
  el.classList.toggle('filled',el.value!=='');
  var digits=document.querySelectorAll('.otp-digit');
  if(el.value&&idx<5) digits[idx+1].focus();
  // Auto submit when all filled
  var code=Array.from(digits).map(function(d){return d.value;}).join('');
  if(code.length===6) submitOtp();
}

function otpBack(e,idx){
  if(e.key==='Backspace'){
    var digits=document.querySelectorAll('.otp-digit');
    if(!digits[idx].value && idx>0){ digits[idx-1].focus(); digits[idx-1].value=''; digits[idx-1].classList.remove('filled'); }
  }
}

function submitOtp(){
  var digits=document.querySelectorAll('.otp-digit');
  var code=Array.from(digits).map(function(d){return d.value;}).join('');
  if(code.length!==6){showMsg('Enter all 6 digits.');return;}
  document.getElementById('otpFinalCode').value=code;
  document.getElementById('otpForm').submit();
}

function goOtpStep1(){
  document.getElementById('otpStep2').style.display='none';
  document.getElementById('otpStep1').style.display='block';
  document.getElementById('sendOtpBtn').disabled=false;
  document.getElementById('sendOtpBtn').textContent='📧 Send OTP to Email';
  if(otpCountdown)clearInterval(otpCountdown);
  document.querySelectorAll('.otp-digit').forEach(function(d){d.value='';d.classList.remove('filled');});
}

/* Auto-switch to correct tab if PHP msg relates to OTP */
<?php if(!empty($msg) && str_contains($msg,'OTP')): ?>
document.addEventListener('DOMContentLoaded',function(){switchTab('otp');});
<?php endif; ?>
</script>
</body>
</html>