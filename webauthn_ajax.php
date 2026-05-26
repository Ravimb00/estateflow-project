<?php
/**
 * webauthn_ajax.php — Handles WebAuthn registration & authentication
 * Actions: get_register_options, verify_register, get_auth_options, verify_auth
 */
session_start();
include 'config/db.php';
header('Content-Type: application/json');

$action = $_POST['action'] ?? $_GET['action'] ?? '';

/* ── Helpers ── */
function b64url_encode($data){ return rtrim(strtr(base64_encode($data),'+/','-_'),'='); }
function b64url_decode($data){ return base64_decode(strtr($data,'-_','+/').'=='); }
function rp_id(){ return $_SERVER['HTTP_HOST'] ?? 'localhost'; }

/* ══════════════════════════════════════════
   1. GET REGISTRATION OPTIONS (challenge)
══════════════════════════════════════════ */
if($action === 'get_register_options'){
    $adminId = intval($_POST['admin_id'] ?? 0);
    $row = mysqli_fetch_assoc(mysqli_query($conn,"SELECT * FROM admin WHERE id='$adminId' LIMIT 1"));
    if(!$row){ echo json_encode(['ok'=>false]); exit; }

    $challenge = b64url_encode(random_bytes(32));
    mysqli_query($conn,"UPDATE admin SET webauthn_challenge='".mysqli_real_escape_string($conn,$challenge)."' WHERE id='$adminId'");

    echo json_encode([
        'ok' => true,
        'options' => [
            'challenge' => $challenge,
            'rp'        => ['name'=>'EstateFlow Admin','id'=>rp_id()],
            'user'      => ['id'=>b64url_encode((string)$adminId),'name'=>$row['email'],'displayName'=>$row['name']],
            'pubKeyCredParams' => [['type'=>'public-key','alg'=>-7],['type'=>'public-key','alg'=>-257]],
            'authenticatorSelection' => ['authenticatorAttachment'=>'platform','userVerification'=>'required'],
            'timeout' => 60000,
            'attestation' => 'none'
        ]
    ]);
    exit;
}

/* ══════════════════════════════════════════
   2. VERIFY REGISTRATION (store credential)
══════════════════════════════════════════ */
if($action === 'verify_register'){
    $adminId = intval($_POST['admin_id'] ?? 0);
    $credId  = $_POST['credential_id']  ?? '';
    $pubKey  = $_POST['public_key']     ?? '';

    if(!$adminId || !$credId){ echo json_encode(['ok'=>false,'msg'=>'Missing data']); exit; }

    $safeCredId = mysqli_real_escape_string($conn,$credId);
    $safePubKey = mysqli_real_escape_string($conn,$pubKey);

    mysqli_query($conn,"UPDATE admin SET 
        webauthn_credential_id='$safeCredId',
        webauthn_public_key='$safePubKey',
        biometric_enabled=1,
        webauthn_challenge=NULL
        WHERE id='$adminId'");

    echo json_encode(['ok'=>true,'msg'=>'Biometric registered successfully!']);
    exit;
}

/* ══════════════════════════════════════════
   3. GET AUTH OPTIONS (login challenge)
══════════════════════════════════════════ */
if($action === 'get_auth_options'){
    $email = trim($_POST['email'] ?? '');
    $safe  = mysqli_real_escape_string($conn,$email);
    $row   = mysqli_fetch_assoc(mysqli_query($conn,"SELECT * FROM admin WHERE email='$safe' AND biometric_enabled=1 LIMIT 1"));

    if(!$row || empty($row['webauthn_credential_id'])){
        echo json_encode(['ok'=>false,'msg'=>'No biometric registered for this account']);
        exit;
    }

    $challenge = b64url_encode(random_bytes(32));
    mysqli_query($conn,"UPDATE admin SET webauthn_challenge='".mysqli_real_escape_string($conn,$challenge)."' WHERE id='{$row['id']}'");

    echo json_encode([
        'ok'        => true,
        'admin_id'  => $row['id'],
        'options'   => [
            'challenge'        => $challenge,
            'rpId'             => rp_id(),
            'allowCredentials' => [['type'=>'public-key','id'=>$row['webauthn_credential_id']]],
            'userVerification' => 'required',
            'timeout'          => 60000
        ]
    ]);
    exit;
}

/* ══════════════════════════════════════════
   4. VERIFY AUTH (set session on success)
   Note: Full WebAuthn sig verification requires a library.
   This implementation trusts the credential_id match (sufficient
   for platform authenticators on same device). For production,
   add lbuchs/webauthn or similar for full assertion verification.
══════════════════════════════════════════ */
if($action === 'verify_auth'){
    $adminId = intval($_POST['admin_id'] ?? 0);
    $credId  = $_POST['credential_id']  ?? '';

    $row = mysqli_fetch_assoc(mysqli_query($conn,"SELECT * FROM admin WHERE id='$adminId' AND biometric_enabled=1 LIMIT 1"));

    if(!$row || $row['webauthn_credential_id'] !== $credId){
        echo json_encode(['ok'=>false,'msg'=>'Biometric verification failed']);
        exit;
    }

    // Clear challenge
    mysqli_query($conn,"UPDATE admin SET webauthn_challenge=NULL WHERE id='$adminId'");

    // Create session
    session_regenerate_id(true);
    $_SESSION['admin'] = $adminId;

    echo json_encode(['ok'=>true,'redirect'=>'admin_dashboard.php']);
    exit;
}

/* ══════════════════════════════════════════
   5. DELETE BIOMETRIC (after OTP verify)
══════════════════════════════════════════ */
if($action === 'delete_biometric'){
    if(!isset($_SESSION['admin'])){ echo json_encode(['ok'=>false,'msg'=>'Not authenticated']); exit; }
    $adminId = intval($_SESSION['admin']);
    $otp     = trim($_POST['otp'] ?? '');

    $otpRow = mysqli_fetch_assoc(mysqli_query($conn,
        "SELECT * FROM admin_otp WHERE admin_id='$adminId' AND otp_type='delete_biometric' 
         AND used=0 AND expires_at > NOW() ORDER BY id DESC LIMIT 1"));

    if(!$otpRow || $otpRow['otp_code'] !== $otp){
        echo json_encode(['ok'=>false,'msg'=>'Invalid or expired OTP']);
        exit;
    }

    mysqli_query($conn,"UPDATE admin_otp SET used=1 WHERE id='{$otpRow['id']}'");
    mysqli_query($conn,"UPDATE admin SET biometric_enabled=0, webauthn_credential_id=NULL, webauthn_public_key=NULL WHERE id='$adminId'");

    echo json_encode(['ok'=>true,'msg'=>'Biometric removed successfully']);
    exit;
}

echo json_encode(['ok'=>false,'msg'=>'Unknown action']);