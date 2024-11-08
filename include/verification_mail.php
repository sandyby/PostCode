<?php
session_start();
require_once 'connection.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

$recipient_name = $_POST['username'];
$recipient_email = $_POST['email'];

require '../vendor/autoload.php';

$mail = new PHPMailer(true);
try {
    $mail->SMTPDebug = 0;                      
    $mail->isSMTP();                           
    $mail->Host       = 'smtp.gmail.com';     
    $mail->SMTPAuth   = true;                  
    $mail->Username   = 'post.code.umn@gmail.com'; 
    $mail->Password   = 'ssamvfqmlpzuegbn';   
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS; 
    $mail->Port       = 465;                   
    
    $mail->setFrom('noreply@post-code.com', 'PostCode');
    $mail->addAddress($recipient_email, $recipient_name);

    $mail->isHTML(true);

    $verification_code = hash("sha256", bin2hex(random_bytes(16)));

    $mail->Subject = 'PostCode E-mail Verification';

    $mail->Body = '
    <html>
    <head>
        <style>
            body {
                font-family: Arial, sans-serif;
                background-color: #FAF7F0;
                margin: 0;
                padding: 0;
            }
            .container {
                width: 100%;
                max-width: 600px;
                margin: 0 auto;
                background: white;
                padding: 20px;
                border-radius: 5px;
                box-shadow: 0 0 10px rgba(0,0,0,0.1);
            }
            .header {
                padding: 10px;
                text-align: center;
                border-radius: 5px 5px 0 0;
                display: flex;
                flex-direction: row;
            }
            .header img {
                max-width: 100%;
                height: 50px;
            }
            .content {
                margin-top: 20px;
            }
            .content p {
                line-height: 1.6;
            }
            .button {
                display: inline-block;
                padding: 10px 20px;
                background: #dee2e6;
                color: black !important;
                text-decoration: none;
                border-radius: 5px;
                margin-top: 20px;
            }
            .button:hover {
                background: #f8f9fa;
            }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="header">
                <img class="post-code-logo" src="https://i.ibb.co.com/9Y9gtG9/PostCode.png" alt="PostCode" border="0"></a>
            </div>
            <div class="content">
                <p>Hello, ' . $recipient_name . '! Thank you for registering to PostCode.</p>
                <p>Please click the link below to verify your account!</p>
                <a href="http://localhost/PostCode-main/PostCode-main/include/verification.php?verification_code=' . $verification_code . '" class="button">Verify</a>
                <br/><br/>
                <b>If you did not register a PostCode account with this email, please disregard this email.</b>
            </div>
        </div>
    </body>
    </html>';

    if (!isset($_SESSION['event-verification_mail_sent']) || $_SESSION['event-verification_mail_sent'] === false) {
        $mail->send();
        $_SESSION['event-verification_mail_sent'] = true;
    } else {
        throw new Exception("Verification e-mail has been sent before");
        header("Location: ../public/register.php");
        exit();
    }
} catch (Exception $e) {
    error_log("E-mail failed to be sent! Mailer Error: {$mail->ErrorInfo}");
}
