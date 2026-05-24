<!DOCTYPE html>
<html lang="en">
 
<head>
 
<meta charset="UTF-8">
 
<meta name="viewport"
content="width=device-width, initial-scale=1.0">
 
<title>EstateFlow</title>
 
<link rel="stylesheet"
href="style.css?v=6">
 
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap"
rel="stylesheet">
 
<style>
 
/* ═══════════════════════════════════════════
   PHOTO SLIDESHOW BACKGROUND
   — replaces the old <video> tag
   — everything else in style.css untouched
═══════════════════════════════════════════ */
 
/* Full-screen slide container — sits behind everything */
.photo-bg {
    position: fixed;
    inset: 0;
    z-index: -2;
    overflow: hidden;
}
 
/* Individual slides */
.photo-slide {
    position: absolute;
    inset: 0;
    background-size: cover;
    background-position: center;
    background-repeat: no-repeat;
    opacity: 0;
    transition: opacity 2.4s ease-in-out;
}
 
.photo-slide.active {
    opacity: 1;
}
 
/* Ken Burns — slow cinematic zoom/pan, different per slide */
.photo-slide:nth-child(1) { animation: kb1 14s ease-in-out infinite alternate; }
.photo-slide:nth-child(2) { animation: kb2 14s ease-in-out infinite alternate; }
.photo-slide:nth-child(3) { animation: kb3 14s ease-in-out infinite alternate; }
.photo-slide:nth-child(4) { animation: kb4 14s ease-in-out infinite alternate; }
.photo-slide:nth-child(5) { animation: kb5 14s ease-in-out infinite alternate; }
 
@keyframes kb1 { 0%{transform:scale(1.00) translate(0,0)}       100%{transform:scale(1.08) translate(-12px,-7px)} }
@keyframes kb2 { 0%{transform:scale(1.07) translate(10px,5px)}  100%{transform:scale(1.00) translate(0,0)} }
@keyframes kb3 { 0%{transform:scale(1.00) translate(-7px,9px)}  100%{transform:scale(1.07) translate(9px,-5px)} }
@keyframes kb4 { 0%{transform:scale(1.06) translate(5px,-7px)}  100%{transform:scale(1.00) translate(-9px,5px)} }
@keyframes kb5 { 0%{transform:scale(1.00) translate(0,7px)}     100%{transform:scale(1.06) translate(-7px,-5px)} }
 
/* ── Overlay — LIGHT so photos are BRIGHT & visible ──
   Just a gentle dark tint, not heavy black          */
.photo-overlay {
    position: fixed;
    inset: 0;
    z-index: -1;
    /* Very light overlay — photos pop through clearly */
    background: linear-gradient(
        135deg,
        rgba(2, 6, 23, 0.38) 0%,
        rgba(2, 6, 23, 0.22) 50%,
        rgba(2, 6, 23, 0.40) 100%
    );
}
 
/* Hide the old hero <video> — we use photo bg now */
.hero video {
    display: none !important;
}
 
</style>
 
</head>
 
<body>
 
<!-- ═══════════════════════════════════════════
     PHOTO SLIDESHOW BACKGROUND
     5 premium real estate photos, crossfade loop
     Ken Burns cinematic zoom on each photo
═══════════════════════════════════════════ -->
 
<div class="photo-bg" id="photoBg">
    <div class="photo-slide active"></div>
    <div class="photo-slide"></div>
    <div class="photo-slide"></div>
    <div class="photo-slide"></div>
    <div class="photo-slide"></div>
</div>
 
<div class="photo-overlay"></div>
 
<!-- BLUR EFFECTS — same as original -->
<div class="blur blur1"></div>
<div class="blur blur2"></div>
 
<!-- NAVBAR — untouched -->
<div class="navbar">
 
    <div class="logo">
        EstateFlow
    </div>
 
    <button
        class="login-btn"
        onclick="openModal()">
        Login or Register
    </button>
 
</div>
 
<!-- HERO SECTION — untouched -->
<section class="hero">
 
    <!-- old video hidden via CSS above, photo bg shows instead -->
    <video autoplay muted loop playsinline style="display:none">
        <source src="" type="video/mp4">
    </video>
 
    <div class="hero-badge">
        #1 Smart Real Estate Workflow Platform
    </div>
 
    <h1>
        Transforming Real Estate Deal Management
    </h1>
 
    <p>
        Manage JV Lands, Outrate Deals and Builder Requirements securely with EstateFlow.
    </p>
 
    <div class="hero-buttons">
        <button
            type="button"
            class="hero-btn"
            onclick="openModal()">
            Get Started
        </button>
    </div>
 
    <div class="stats">
 
        <div class="stat-card">
            <h2>500+</h2>
            <span>Deals Managed</span>
        </div>
 
        <div class="stat-card">
            <h2>120+</h2>
            <span>Builders</span>
        </div>
 
        <div class="stat-card">
            <h2>50+</h2>
            <span>Locations</span>
        </div>
 
    </div>
 
</section>
 
<!-- LOGIN MODAL — untouched -->
<div class="modal" id="loginModal">
 
    <div class="modal-box">
 
        <div class="left">
 
            <h2>Welcome Back</h2>
 
            <p class="sub">Login to continue EstateFlow</p>
 
            <div class="tabs">
                <button class="tab active" onclick="showUser()" id="userTab">User</button>
                <button class="tab" onclick="showManager()" id="managerTab">Management</button>
            </div>
 
            <div id="userLogin">
                <form action="user_login.php" method="POST">
                    <input type="email" name="email" placeholder="Enter Email" required>
                    <input type="password" name="password" placeholder="Enter Password" required>
                    <button type="submit" class="submit-btn">Login</button>
                </form>
                <div class="bottom-text">
                    New User? <a href="user_signup.php">Register Now</a>
                </div>
            </div>
 
            <div id="managerLogin" style="display:none;">
                <form action="admin_login.php" method="POST">
                    <input type="email" name="email" placeholder="Management Email" required>
                    <input type="password" name="password" placeholder="Password" required>
                    <button type="submit" class="submit-btn manager">Secure Login</button>
                </form>
            </div>
 
        </div>
 
        <div class="right"></div>
 
        <div class="close-btn" onclick="closeModal()">✕</div>
 
    </div>
 
</div>
 
<!-- COOKIES — untouched -->
<div class="cookie-box" id="cookieBox">
 
    <div>
        <h3>🍪 Cookies Consent</h3>
        <p>EstateFlow uses cookies to improve your platform experience.</p>
    </div>
 
    <button type="button" onclick="acceptCookies()">Accept</button>
 
</div>
 
<script src="script.js?v=6"></script>
 
<script>
 
/* ═══════════════════════════════════════════
   PHOTO SLIDESHOW ENGINE
   — 5 premium real estate photos from Unsplash
   — Crossfade every 6 seconds
   — Ken Burns zoom handled by CSS animation
═══════════════════════════════════════════ */
 
(function(){
 
    /* Premium real estate photos — high brightness, high quality */
    var photos = [
        'https://images.unsplash.com/photo-1486325212027-8081e485255e?w=1920&q=95',  /* City skyline night */
        'https://images.unsplash.com/photo-1545324418-cc1a3fa10c00?w=1920&q=95',  /* Luxury apartments aerial */
        'https://images.unsplash.com/photo-1560518883-ce09059eeffa?w=1920&q=95',  /* Premium real estate */
        'https://images.unsplash.com/photo-1582407947304-fd86f028f716?w=1920&q=95',  /* Modern township */
        'https://images.unsplash.com/photo-1512917774080-9991f1c4c750?w=1920&q=95'   /* Luxury villa exterior */
    ];
 
    var slides  = document.querySelectorAll('.photo-slide');
    var current = 0;
 
    /* Set background images */
    slides.forEach(function(slide, i){
        slide.style.backgroundImage = "url('" + photos[i] + "')";
    });
 
    /* Crossfade to next slide every 6 seconds */
    setInterval(function(){
        slides[current].classList.remove('active');
        current = (current + 1) % photos.length;
        slides[current].classList.add('active');
    }, 6000);
 
})();
 
</script>
 
</body>
</html>
 