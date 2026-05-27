<?php
/**
 * webauthn_ajax.php — Multiple fingerprint support
 * Uses admin_credentials table (separate from admin table)
 * Actions: get_register_options, verify_register, get_auth_options, verify_auth, delete_credential
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
   1. GET REGISTRATION OPTIONS
══════════════════════════════════════════ */
if($action === 'get_register_options'){
    $adminId = intval($_POST['admin_id'] ?? 0);
    $row = mysqli_fetch_assoc(mysqli_query($conn,"SELECT * FROM admin WHERE id='$adminId' LIMIT 1"));
    if(!$row){ echo json_encode(['ok'=>false,'msg'=>'Admin not found']); exit; }

    $challenge = b64url_encode(random_bytes(32));

    /* Store challenge temporarily in admin table */
    mysqli_query($conn,"UPDATE admin SET webauthn_challenge='".mysqli_real_escape_string($conn,$challenge)."' WHERE id='$adminId'");

    /* Exclude already registered credential IDs (so same finger not registered twice) */
    $excludeList = [];
    $existing = mysqli_query($conn,"SELECT credential_id FROM admin_credentials WHERE admin_id='$adminId'");
    while($ec = mysqli_fetch_assoc($existing)){
        $excludeList[] = ['type'=>'public-key','id'=>$ec['credential_id']];
    }

    echo json_encode([
        'ok' => true,
        'options' => [
            'challenge'             => $challenge,
            'rp'                    => ['name'=>'EstateFlow Admin','id'=>rp_id()],
            'user'                  => ['id'=>b64url_encode((string)$adminId),'name'=>$row['email'],'displayName'=>$row['name']],
            'pubKeyCredParams'      => [['type'=>'public-key','alg'=>-7],['type'=>'public-key','alg'=>-257]],
            'authenticatorSelection'=> ['authenticatorAttachment'=>'platform','userVerification'=>'required'],
            'excludeCredentials'    => $excludeList,
            'timeout'               => 60000,
            'attestation'           => 'none'
        ]
    ]);
    exit;
}

/* ══════════════════════════════════════════
   2. VERIFY REGISTRATION — save to admin_credentials
══════════════════════════════════════════ */
if($action === 'verify_register'){
    $adminId = intval($_POST['admin_id'] ?? 0);
    $credId  = $_POST['credential_id'] ?? '';
    $pubKey  = $_POST['public_key']    ?? '';
    $label   = trim($_POST['label']    ?? 'Fingerprint');

    if(!$adminId || !$credId){ echo json_encode(['ok'=>false,'msg'=>'Missing data']); exit; }

    /* Check duplicate credential_id */
    $dup = mysqli_num_rows(mysqli_query($conn,"SELECT id FROM admin_credentials WHERE credential_id='".mysqli_real_escape_string($conn,$credId)."'"));
    if($dup){ echo json_encode(['ok'=>false,'msg'=>'This fingerprint is already registered.']); exit; }

    $safeCredId = mysqli_real_escape_string($conn,$credId);
    $safePubKey = mysqli_real_escape_string($conn,$pubKey);
    $safeLabel  = mysqli_real_escape_string($conn,$label);

    /* Insert into admin_credentials table */
    mysqli_query($conn,"INSERT INTO admin_credentials (admin_id, credential_id, public_key, label, created_at)
        VALUES ('$adminId','$safeCredId','$safePubKey','$safeLabel',NOW())");

    $newId = mysqli_insert_id($conn);

    /* Update biometric_enabled flag on admin */
    mysqli_query($conn,"UPDATE admin SET biometric_enabled=1, webauthn_challenge=NULL WHERE id='$adminId'");

    echo json_encode(['ok'=>true,'msg'=>'Fingerprint registered successfully!','id'=>$newId]);
    exit;
}

/* ══════════════════════════════════════════
   3. GET AUTH OPTIONS (login challenge)
      — returns all credentials for this admin
══════════════════════════════════════════ */
if($action === 'get_auth_options'){
    $email = trim($_POST['email'] ?? '');
    $safe  = mysqli_real_escape_string($conn,$email);
    $row   = mysqli_fetch_assoc(mysqli_query($conn,"SELECT * FROM admin WHERE email='$safe' AND biometric_enabled=1 LIMIT 1"));

    if(!$row){ echo json_encode(['ok'=>false,'msg'=>'No biometric registered for this account']); exit; }

    /* Get ALL credentials for this admin */
    $credsRes = mysqli_query($conn,"SELECT credential_id FROM admin_credentials WHERE admin_id='{$row['id']}'");
    $allowList = [];
    while($cr = mysqli_fetch_assoc($credsRes)){
        $allowList[] = ['type'=>'public-key','id'=>$cr['credential_id']];
    }

    if(empty($allowList)){ echo json_encode(['ok'=>false,'msg'=>'No fingerprints registered']); exit; }

    $challenge = b64url_encode(random_bytes(32));
    mysqli_query($conn,"UPDATE admin SET webauthn_challenge='".mysqli_real_escape_string($conn,$challenge)."' WHERE id='{$row['id']}'");

    echo json_encode([
        'ok'       => true,
        'admin_id' => $row['id'],
        'options'  => [
            'challenge'        => $challenge,
            'rpId'             => rp_id(),
            'allowCredentials' => $allowList,
            'userVerification' => 'required',
            'timeout'          => 60000
        ]
    ]);
    exit;
}

/* ══════════════════════════════════════════
   4. VERIFY AUTH — match credential_id from admin_credentials
══════════════════════════════════════════ */
if($action === 'verify_auth'){
    $adminId = intval($_POST['admin_id'] ?? 0);
    $credId  = $_POST['credential_id']  ?? '';

    /* Check credential exists for this admin */
    $credRow = mysqli_fetch_assoc(mysqli_query($conn,
        "SELECT * FROM admin_credentials WHERE admin_id='$adminId' AND credential_id='".mysqli_real_escape_string($conn,$credId)."' LIMIT 1"));

    if(!$credRow){
        echo json_encode(['ok'=>false,'msg'=>'Biometric verification failed']);
        exit;
    }

    /* Check admin exists and biometric enabled */
    $adminRow = mysqli_fetch_assoc(mysqli_query($conn,"SELECT * FROM admin WHERE id='$adminId' AND biometric_enabled=1 LIMIT 1"));
    if(!$adminRow){
        echo json_encode(['ok'=>false,'msg'=>'Admin not found or biometric disabled']);
        exit;
    }

    /* Clear challenge */
    mysqli_query($conn,"UPDATE admin SET webauthn_challenge=NULL WHERE id='$adminId'");

    /* Create session */
    session_regenerate_id(true);
    $_SESSION['admin'] = $adminId;

    echo json_encode(['ok'=>true,'redirect'=>'admin_dashboard.php']);
    exit;
}

/* ══════════════════════════════════════════
   5. DELETE SINGLE CREDENTIAL
══════════════════════════════════════════ */
if($action === 'delete_credential'){
    if(!isset($_SESSION['admin'])){ echo json_encode(['ok'=>false,'msg'=>'Not authenticated']); exit; }

    $adminId      = intval($_SESSION['admin']);
    $credDbId     = intval($_POST['credential_db_id'] ?? 0);

    if(!$credDbId){ echo json_encode(['ok'=>false,'msg'=>'Invalid credential']); exit; }

    /* Make sure this credential belongs to this admin */
    $credRow = mysqli_fetch_assoc(mysqli_query($conn,
        "SELECT * FROM admin_credentials WHERE id='$credDbId' AND admin_id='$adminId' LIMIT 1"));

    if(!$credRow){ echo json_encode(['ok'=>false,'msg'=>'Credential not found']); exit; }

    mysqli_query($conn,"DELETE FROM admin_credentials WHERE id='$credDbId'");

    /* If no more credentials, disable biometric_enabled flag */
    $remaining = mysqli_num_rows(mysqli_query($conn,"SELECT id FROM admin_credentials WHERE admin_id='$adminId'"));
    if($remaining == 0){
        mysqli_query($conn,"UPDATE admin SET biometric_enabled=0 WHERE id='$adminId'");
    }

    echo json_encode(['ok'=>true,'msg'=>'Fingerprint removed','remaining'=>$remaining]);
    exit;
}

echo json_encode(['ok'=>false,'msg'=>'Unknown action']);