<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';

$mail = new PHPMailer(true);

try {
    $mail->SMTPDebug = 2;  // Enable debug for troubleshooting
    $mail->Debugoutput = 'html';
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com'; 
    $mail->SMTPAuth = true;
    $mail->Username = 'register@majistic.org'; 
    $mail->Password = 'xejqfqgsdjijlejz';  // Use App Password
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; 
    $mail->Port = 587;

    $mail->setFrom('register@majistic.org', 'Riyaz');
    $mail->addAddress('skriyaz30092002@gmail.com');

    $mail->isHTML(true);
    $mail->Subject = 'SMTP Test';
    $mail->Body    = 'This is a test email';

    $mail->send();
    echo 'Test email sent successfully';
} catch (Exception $e) {
    echo "Mailer Error: {$mail->ErrorInfo}";
}
?>