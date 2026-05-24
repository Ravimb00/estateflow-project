<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

/* OTP MAIL FUNCTION */

function sendOTP($email,$otp){

$mail = new PHPMailer(true);

try{

$mail->isSMTP();

$mail->Host = 'smtp.gmail.com';

$mail->SMTPAuth = true;

$mail->Username = 'estateflowofficial@gmail.com';

/* APP PASSWORD */

$mail->Password = 'ephriqtmeimdgpoj';

$mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;

$mail->Port = 587;

$mail->setFrom(
'estateflowofficial@gmail.com',
'EstateFlow'
);

$mail->addAddress($email);

$mail->isHTML(true);

$mail->Subject = 'EstateFlow OTP Verification';

$mail->Body = "

<h2>EstateFlow Verification</h2>

<p>Your OTP is:</p>

<h1>".$otp."</h1>

";

$mail->send();

return true;

}catch(Exception $e){

echo $mail->ErrorInfo;

return false;

}

}

/* CUSTOM MAIL FUNCTION */

function sendCustomMail($to,$subject,$body){

$mail = new PHPMailer(true);

try{

$mail->isSMTP();

$mail->Host = 'smtp.gmail.com';

$mail->SMTPAuth = true;

$mail->Username = 'estateflowofficial@gmail.com';

$mail->Password = 'ephriqtmeimdgpoj';

$mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;

$mail->Port = 587;

$mail->setFrom(
'estateflowofficial@gmail.com',
'EstateFlow'
);

$mail->addAddress($to);

$mail->isHTML(false);

$mail->Subject = $subject;

$mail->Body = $body;

$mail->send();

return true;

}catch(Exception $e){

echo $mail->ErrorInfo;

return false;

}

}

?>