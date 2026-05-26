<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

/* MAIL CONFIG */

$mail_username = "estateflow.232@gmail.com";

$mail_password = "hgninwytbotzvpaw";

/* SEND FUNCTION */

function sendMail(
    $to,
    $subject,
    $body
){

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

    $mail->isHTML(true);

    /* PROFESSIONAL TEMPLATE */

    $finalBody = "

    <div style='
    font-family:Segoe UI,sans-serif;
    background:#f8fafc;
    padding:30px;
    '>

        <div style='
        max-width:650px;
        margin:auto;
        background:white;
        border-radius:18px;
        overflow:hidden;
        box-shadow:0 10px 30px rgba(0,0,0,.08);
        '>

            <div style='
            background:linear-gradient(135deg,#0f172a,#14b8a6);
            padding:30px;
            text-align:center;
            color:white;
            '>

                <h1 style='margin:0'>
                EstateFlow
                </h1>

                <p style='margin-top:8px'>
                Smart Real Estate Management
                </p>

            </div>

            <div style='padding:35px'>

                $body

                <br><br>

                <hr style='border:none;
                border-top:1px solid #e2e8f0'>

                <p style='
                line-height:1.9;
                color:#475569;
                font-size:14px;
                '>

                For support or assistance,
                feel free to contact us.

                <br><br>

                📞 +91 9876543210

                <br>

                📧 support@estateflow.com

                <br><br>

                Regards,<br>

                <b>EstateFlow Team</b>

                </p>

            </div>

        </div>

    </div>

    ";

    $mail->Subject = $subject;

    $mail->Body = $finalBody;

    $mail->send();

    return true;

}catch(Exception $e){

    return false;

}

}
?>