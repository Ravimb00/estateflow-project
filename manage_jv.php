<?php

error_reporting(E_ALL);
ini_set('display_errors',1);

session_start();

include 'config/db.php';
include 'mail_config.php';

/* ADMIN LOGIN CHECK */

if(!isset($_SESSION['admin'])){
    header("Location:admin_login.php");
    exit();
}

/* APPROVE JV */

if(isset($_GET['approve'])){

    $id = intval($_GET['approve']);

    /* GET LAND */
    $getLand = mysqli_fetch_assoc(
        mysqli_query($conn, "SELECT * FROM jv_lands WHERE id='$id'")
    );

    /* UPDATE STATUS */
    mysqli_query($conn, "UPDATE jv_lands SET status='approved' WHERE id='$id'");

    /* USER DETAILS */
    // ✅ submitted_by column use ಮಾಡ್ತಿದ್ದೀವಿ
    $user_id = $getLand['submitted_by'];

    $getUser = mysqli_fetch_assoc(
        mysqli_query($conn, "SELECT * FROM users WHERE id='$user_id'")
    );

    /* SEND MAIL */
    if($getUser){

        $userEmail = $getUser['email'];
        $userName  = isset($getUser['name']) ? $getUser['name'] : "Customer";

        $subject = "EstateFlow Proposal Approval";

        $body = "
        <h2 style='color:#10b981'>Greetings from EstateFlow 🎉</h2>
        <p>Dear <b>$userName</b>,</p>
        <p>We are pleased to inform you that your <b>JV Land</b> proposal has been successfully <b style='color:green'>Approved</b> by our management team.</p>
        <p>
            <b>Land Name:</b> ".$getLand['land_name']." <br><br>
            <b>Location:</b> ".$getLand['location']." <br><br>
            <b>Status:</b> Approved ✅
        </p>
        <p>Please visit our office with the following documents:</p>
        <ul>
            <li>Original Property Documents</li>
            <li>Government ID Proof</li>
            <li>Supporting Land Records</li>
            <li>Company / Ownership Details (if applicable)</li>
        </ul>
        <p>Our team will guide you through the next steps.</p>
        <p>Thank you for choosing EstateFlow.</p>
        <p>Warm Regards,<br><b>EstateFlow Management Team</b></p>
        ";

        $mailResult = sendMail($userEmail, $subject, $body);

        if(!$mailResult){
            error_log("JV Approve Mail Failed — user_id: $user_id, email: $userEmail");
        }

    }

    header("Location:manage_jv.php");
    exit();

}

/* REJECT JV */

if(isset($_GET['reject'])){

    $id = intval($_GET['reject']);

    /* GET LAND */
    $getLand = mysqli_fetch_assoc(
        mysqli_query($conn, "SELECT * FROM jv_lands WHERE id='$id'")
    );

    /* UPDATE STATUS */
    mysqli_query($conn, "UPDATE jv_lands SET status='Rejected' WHERE id='$id'");

    /* USER DETAILS */
    // ✅ BUG FIX: user_id ಅಲ್ಲ, submitted_by ಉಪಯೋಗಿಸಬೇಕು
    $user_id = $getLand['submitted_by'];

    $getUser = mysqli_fetch_assoc(
        mysqli_query($conn, "SELECT * FROM users WHERE id='$user_id'")
    );

    /* SEND MAIL */
    // ✅ BUG FIX: null check add ಮಾಡಿದ್ದೀವಿ
    if($getUser){

        $userEmail = $getUser['email'];
        $userName  = isset($getUser['name']) ? $getUser['name'] : "Customer";

        $subject = "EstateFlow Proposal Update";

        $body = "
        <h2 style='color:#ef4444'>Greetings from EstateFlow</h2>
        <p>Dear <b>$userName</b>,</p>
        <p>Thank you for submitting your JV Land proposal with EstateFlow.</p>
        <p>After careful review by our verification team, we regret to inform you that your current proposal could <b style='color:red'>not be approved</b> at this stage due to verification or documentation requirements.</p>
        <p>
            <b>Land Name:</b> ".$getLand['land_name']." <br><br>
            <b>Location:</b> ".$getLand['location']." <br><br>
            <b>Status:</b> Rejected ❌
        </p>
        <p>You may update the required details/documents and submit again in the future.</p>
        <p>For any assistance or clarification, feel free to contact our support team.</p>
        <p>We appreciate your interest in working with EstateFlow.</p>
        <p>Warm Regards,<br><b>EstateFlow Support Team</b></p>
        ";

        $mailResult = sendMail($userEmail, $subject, $body);

        if(!$mailResult){
            error_log("JV Reject Mail Failed — user_id: $user_id, email: $userEmail");
        }

    }

    header("Location:manage_jv.php");
    exit();

}

/* DELETE JV */

if(isset($_GET['delete'])){

    $id = intval($_GET['delete']);

    mysqli_query($conn, "DELETE FROM jv_lands WHERE id='$id'");

    header("Location:manage_jv.php");
    exit();

}

/* FETCH JV LANDS */

$getJV = mysqli_query($conn, "SELECT * FROM jv_lands ORDER BY id DESC");

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Manage JV Lands</title>

<link href="https://fonts.googleapis.com/css2?family=Sora:wght@400;600;700;800&display=swap" rel="stylesheet">

<style>

*{
margin:0;
padding:0;
box-sizing:border-box;
font-family:'Sora',sans-serif;
}

body{
background:
linear-gradient(rgba(2,6,23,.85),rgba(2,6,23,.92)),
url('https://images.unsplash.com/photo-1486406146926-c627a92ad1ab?q=80&w=1920&auto=format&fit=crop');
background-size:cover;
background-position:center;
min-height:100vh;
padding:40px;
color:white;
}

.top{
display:flex;
justify-content:space-between;
align-items:center;
margin-bottom:35px;
}

.title{
font-size:46px;
font-weight:800;
}

.back{
text-decoration:none;
padding:14px 22px;
border-radius:14px;
background:linear-gradient(135deg,#3b82f6,#9333ea);
color:white;
font-weight:700;
}

.table-box{
background:rgba(255,255,255,.08);
border:1px solid rgba(255,255,255,.08);
backdrop-filter:blur(18px);
padding:30px;
border-radius:28px;
overflow:auto;
}

table{
width:100%;
border-collapse:collapse;
}

table th{
text-align:left;
padding-bottom:18px;
color:#94a3b8;
font-size:14px;
}

table td{
padding:18px 0;
border-top:1px solid rgba(255,255,255,.08);
font-size:14px;
}

.pending{ color:#facc15; font-weight:700; }
.approved{ color:#22c55e; font-weight:700; }
.rejected{ color:#f97316; font-weight:700; }

.btn{
padding:10px 16px;
border-radius:12px;
text-decoration:none;
font-size:13px;
font-weight:700;
display:inline-block;
margin-right:10px;
}

.approve{
background:linear-gradient(135deg,#22c55e,#16a34a);
color:white;
}

.reject{
background:linear-gradient(135deg,#f59e0b,#ea580c);
color:white;
}

.delete{
background:linear-gradient(135deg,#ef4444,#dc2626);
color:white;
}

</style>

</head>

<body>

<div class="top">
    <div class="title">Manage JV Lands</div>
    <a href="admin_dashboard.php" class="back">Dashboard</a>
</div>

<div class="table-box">

<table>

<tr>
    <th>Land Name</th>
    <th>Location</th>
    <th>Builder</th>
    <th>Acres</th>
    <th>Deal Value</th>
    <th>Status</th>
    <th>Action</th>
</tr>

<?php while($row = mysqli_fetch_assoc($getJV)){ ?>

<tr>
    <td><?php echo htmlspecialchars($row['land_name']); ?></td>
    <td><?php echo htmlspecialchars($row['location']); ?></td>
    <td><?php echo htmlspecialchars($row['builder']); ?></td>
    <td><?php echo htmlspecialchars($row['acres']); ?></td>
    <td><?php echo htmlspecialchars($row['deal_value']); ?></td>

    <td>
        <?php
        if($row['status']=="approved"){
            echo "<span class='approved'>Approved</span>";
        } elseif($row['status']=="Rejected"){
            echo "<span class='rejected'>Rejected</span>";
        } else {
            echo "<span class='pending'>Pending</span>";
        }
        ?>
    </td>

    <td>
        <?php if($row['status']!="approved"){ ?>
            <a href="?approve=<?php echo $row['id']; ?>" class="btn approve">Approve</a>
        <?php } ?>

        <?php if($row['status']!="Rejected"){ ?>
            <a href="?reject=<?php echo $row['id']; ?>" class="btn reject">Reject</a>
        <?php } ?>

        <a href="?delete=<?php echo $row['id']; ?>" class="btn delete" onclick="return confirm('Delete this JV Land?')">Delete</a>
    </td>
</tr>

<?php } ?>

</table>

</div>

</body>
</html>