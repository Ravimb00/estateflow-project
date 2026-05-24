<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

$mail_username = "estateflow.232@gmail.com";

$mail_password = "hgninwytbotzvpaw";

function sendMail($to,$subject,$body){

global $mail_username;
global $mail_password;

$mail = new PHPMailer(true);

try{

$mail->isSMTP();

$mail->Host = 'smtp.gmail.com';

$mail->SMTPAuth = true;

$mail->Username = $mail_username;

$mail->Password = $mail_password;

$mail->SMTPSecure = 'tls';

$mail->Port = 587;

$mail->setFrom(
$mail_username,
'EstateFlow'
);

$mail->addAddress($to);

$mail->isHTML(false);

$mail->Subject = $subject;

$mail->Body = $body;

$mail->send();

return true;

}catch(Exception $e){

return false;

}

}