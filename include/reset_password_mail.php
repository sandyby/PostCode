<?php
session_start();
require_once 'connection.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

$recipient_email = $_POST['email'];

require '../vendor/autoload.php';

$mail = new PHPMailer(true);
$mail->SMTPDebug = 0;                      
$mail->isSMTP();                           
$mail->Host       = 'smtp.gmail.com';     
$mail->SMTPAuth   = true;                  
$mail->Username   = 'post.code.umn@gmail.com'; 
$mail->Password   = 'ssamvfqmlpzuegbn';   
$mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS; 
$mail->Port       = 465;                   
$mail->isHTML(true);

return $mail;