<?php
// Set timezone to IST at the beginning of the file
date_default_timezone_set('Asia/Kolkata');

// Import PHPMailer classes into the global namespace
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// Correct the autoloader path and add error handling
$autoloaderPaths = [
    __DIR__ . '/../../vendor/autoload.php',
    'vendor/autoload.php',
    '../../../vendor/autoload.php'
];

$autoloaderLoaded = false;
foreach ($autoloaderPaths as $path) {
    if (file_exists($path)) {
        require $path;
        $autoloaderLoaded = true;
        break;
    }
}

// If autoloader isn't found, create a dummy function that logs the error but doesn't crash
if (!$autoloaderLoaded) {
    error_log("PHPMailer autoloader not found. Email functionality will be disabled.");
    
    function sendPaymentConfirmationEmail($data) {
        error_log("Email sending skipped - PHPMailer not available");
        // Record the intended email in a log file instead
        $logFile = __DIR__ . '/../../logs/email_queue.log';
        $dir = dirname($logFile);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        $logEntry = date('Y-m-d H:i:s') . " - Would have sent email to: " . $data['email'] . 
                    " - Payment ID: " . $data['payment_id'] . 
                    " - Amount: " . $data['amount'] . "\n";
        file_put_contents($logFile, $logEntry, FILE_APPEND);
        return false;
    }
    
    function generateEmailTemplate($data) {
        return "Email template generation skipped - PHPMailer not available";
    }
    
    // Exit this file early
    return;
}

/**
 * Function to send payment confirmation email
 * 
 * @param array $data Payment and student data
 * @return bool Whether the email was sent successfully
 */
function sendPaymentConfirmationEmail($data) {
    // Create a new PHPMailer instance
    $mail = new PHPMailer(true);
    
    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com'; // Set the SMTP server to send through
        $mail->SMTPAuth = true;
        $mail->Username   = 'payment.majistic@gmail.com';
        $mail->Password   = 'csibomhfcfmtxpjp';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;
        
        // Recipients
        $mail->setFrom('payment.majistic@gmail.com', 'maJIStic');
        $mail->addAddress($data['email'], $data['student_name']);
        $mail->addReplyTo('payment.majistic@gmail.com', 'maJIStic Support');
        
        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Payment Confirmation - maJIStic 2025';
        
        // Email HTML body
        $mail->Body = generateEmailTemplate($data);
        
        // Plain text version for non-HTML mail clients
        $mail->AltBody = "Payment Confirmation - maJIStic 2025\n\n" .
                        "Dear {$data['student_name']},\n\n" .
                        "Your payment for maJIStic 2025 has been successfully received.\n" .
                        "Payment ID: {$data['payment_id']}\n" .
                        "Amount: Rs. {$data['amount']}\n" .
                        "Date: {$data['payment_date']}\n\n" .
                        "Thank you for registering for maJIStic 2025. We look forward to seeing you at the event!\n\n" .
                        "Regards,\nmaJIStic Team";
        
        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Email could not be sent. Mailer Error: {$mail->ErrorInfo}");
        return false;
    }
}

/**
 * Function to generate HTML email template
 * 
 * @param array $data Payment and student data
 * @return string HTML content of the email
 */
function generateEmailTemplate($data) {
    // Get current year for copyright (using IST)
    $year = date('Y');
    
    // Format payment date for display in email if not already formatted
    if (strtotime($data['payment_date'])) {
        $formatted_date = date('d M Y, h:i A', strtotime($data['payment_date']));
    } else {
        $formatted_date = $data['payment_date'];
    }
    
    // Logo URL - update with actual URL to the maJIStic logo
    $logoUrl = '../../images/majisticlogo.png';
    
    // HTML Template
    $html = <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Confirmation</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            line-height: 1.6;
            color: #333333;
            max-width: 600px;
            margin: 0 auto;
            padding: 0;
        }
        .email-container {
            border: 1px solid #dddddd;
            border-radius: 5px;
            overflow: hidden;
        }
        .header {
            background-color: #000000;
            padding: 20px;
            text-align: center;
        }
        .header img {
            max-width: 250px;
            height: auto;
        }
        .content {
            padding: 30px;
            background-color: #ffffff;
        }
        .ticket-info {
            background-color: #f7f7f7;
            border: 1px solid #eeeeee;
            border-radius: 5px;
            padding: 20px;
            margin: 20px 0;
        }
        .ticket-info table {
            width: 100%;
            border-collapse: collapse;
        }
        .ticket-info td {
            padding: 8px 0;
            border-bottom: 1px solid #eeeeee;
        }
        .ticket-info td:first-child {
            font-weight: bold;
            width: 40%;
        }
        .footer {
            background-color: #f0f0f0;
            padding: 15px;
            text-align: center;
            font-size: 12px;
            color: #666666;
        }
        .social-links {
            margin-top: 15px;
        }
        .social-links a {
            display: inline-block;
            margin: 0 10px;
            color: #666666;
            text-decoration: none;
        }
        .button {
            display: inline-block;
            background-color: #4CAF50;
            color: white;
            text-decoration: none;
            padding: 10px 20px;
            border-radius: 5px;
            margin-top: 15px;
        }
        .event-pass-box {
            background-color: #e7f5e9;
            border: 2px solid #4CAF50;
            border-radius: 5px;
            padding: 15px;
            margin: 25px 0;
            position: relative;
        }
        .event-pass-box::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 5px;
            background-color: #4CAF50;
            border-top-left-radius: 3px;
            border-top-right-radius: 3px;
        }
        .event-pass-box p {
            color: #1e7d27;
            font-size: 14px;
            margin: 0;
            padding: 5px 0;
        }
        .event-pass-box strong {
            font-weight: 600;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="header">
            <img src="{$logoUrl}" alt="maJIStic 2025 Logo">
        </div>
        
        <div class="content">
            <h2>Payment Confirmation</h2>
            <p>Dear {$data['student_name']},</p>
            <p>Thank you for your payment for maJIStic 2025. Your transaction has been successfully processed.</p>
            
            <div class="ticket-info">
                <h3>Payment Details</h3>
                <table>
                    <tr>
                        <td>JIS ID</td>
                        <td>{$data['jis_id']}</td>
                    </tr>
                    <tr>
                        <td>Department</td>
                        <td>{$data['department']}</td>
                    </tr>
                    <tr>
                        <td>Payment ID</td>
                        <td>{$data['payment_id']}</td>
                    </tr>
                    <tr>
                        <td>Amount Paid</td>
                        <td>Rs. {$data['amount']}</td>
                    </tr>
                    <tr>
                        <td>Payment Date</td>
                        <td>{$formatted_date}</td>
                    </tr>
                    <tr>
                        <td>Payment Status</td>
                        <td><strong style="color: #4CAF50;">CONFIRMED</strong></td>
                    </tr>
                </table>
            </div>
            
            <div class="event-pass-box">
                <p><strong>Important:</strong> You will receive your Event Pass in your registered email 3 days before the event. Don't forget to check your email, including spam folder.</p>
            </div>
            
            <p>Please keep this email for your records. You may be required to show this confirmation at the event.</p>
            
            <p>We look forward to seeing you at maJIStic 2025!</p>
            
            <p>Warm Regards,<br>maJIStic Team</p>
        </div>
        
        <div class="footer">
            <p>&copy; {$year} maJIStic 2025. All rights reserved.</p>
            <p>JIS College of Engineering, Kalyani, Nadia - 741235, West Bengal, India</p>
            <div class="social-links">
                <a href="https://www.facebook.com/profile.php?id=100090087469753" target="_blank">Facebook</a> |
                <a href="https://www.instagram.com/majistic_jisce" target="_blank">Instagram</a> |
                <a href="https://www.linkedin.com/company/majistic-jisce/" target="_blank">LinkedIN</a>
            </div>
        </div>
    </div>
</body>
</html>
HTML;

    return $html;
}
?>
