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
    
    function sendRegistrationConfirmationEmail($data) {
        error_log("Email sending skipped - PHPMailer not available");
        // Record the intended email in a log file instead
        $logFile = __DIR__ . '/../../logs/email_queue.log';
        $dir = dirname($logFile);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        $logEntry = date('Y-m-d H:i:s') . " - Would have sent registration email to: " . $data['email'] . 
                    " - JIS ID: " . $data['jis_id'] . "\n";
        file_put_contents($logFile, $logEntry, FILE_APPEND);
        return false;
    }
    
    function generateRegistrationEmailTemplate($data) {
        return "Email template generation skipped - PHPMailer not available";
    }
    
    // Exit this file early
    return;
}

/**
 * Function to send registration confirmation email
 * 
 * @param array $data Registration data
 * @return bool Whether the email was sent successfully
 */
function sendRegistrationConfirmationEmail($data) {
    // Create a new PHPMailer instance
    $mail = new PHPMailer(true);
    
    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com'; // Set the SMTP server to send through
        $mail->SMTPAuth = true;
        $mail->Username   = 'majistic.reg@gmail.com';
        $mail->Password   = 'uflsvdypbnnbnisn';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;
        
        // Recipients
        $mail->setFrom('majistic.reg@gmail.com', 'maJIStic');
        $mail->addAddress($data['email'], $data['student_name']);
        $mail->addReplyTo('majistic.reg@gmail.com', 'maJIStic Support');
        
        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Registration Confirmation - maJIStic 2025';
        
        // Email HTML body
        $mail->Body = generateRegistrationEmailTemplate($data);
        
        // Plain text version for non-HTML mail clients
        $mail->AltBody = "Registration Confirmation - maJIStic 2025\n\n" .
                        "Dear {$data['student_name']},\n\n" .
                        "Thank you for registering for maJIStic 2025.\n" .
                        "JIS ID: {$data['jis_id']}\n" .
                        "Department: {$data['department']}\n" .
                        "Registration Date: {$data['registration_date']}\n\n" .
                        "Please proceed to complete your payment.\n\n" .
                        "Regards,\nmaJIStic Team";
        
        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Email could not be sent. Mailer Error: {$mail->ErrorInfo}");
        return false;
    }
}

/**
 * Function to generate HTML email template for registration
 * 
 * @param array $data Registration data
 * @return string HTML content of the email
 */
function generateRegistrationEmailTemplate($data) {
    // Get current year for copyright (using IST)
    $year = date('Y');
    
    // Format registration date for display in email if not already formatted
    if (isset($data['registration_date']) && strtotime($data['registration_date'])) {
        $formatted_date = date('d M Y, h:i A', strtotime($data['registration_date']));
    } else {
        $formatted_date = date('d M Y, h:i A'); // Use current time if not provided
    }
    $jis_id=$data['jis_id'];
    // Generate payment link
    $payment_link = "https://skriyaz.com/majistic/src/transaction/payment.php?jis_id=" . urlencode($data['jis_id']);
    
    // Logo URL - update with actual URL to the maJIStic logo
    $logoUrl = '../../images/majisticlogo.png';
    
    // HTML Template
    $html = <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration Confirmation</title>
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
        .registration-info {
            background-color: #f7f7f7;
            border: 1px solid #eeeeee;
            border-radius: 5px;
            padding: 20px;
            margin: 20px 0;
        }
        .registration-info table {
            width: 100%;
            border-collapse: collapse;
        }
        .registration-info td {
            padding: 8px 0;
            border-bottom: 1px solid #eeeeee;
        }
        .registration-info td:first-child {
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
            color: white !important;
            text-decoration: none;
            padding: 12px 24px;
            border-radius: 5px;
            margin-top: 15px;
            font-weight: bold;
            font-size: 16px;
        }
        .payment-note {
            margin-top: 25px;
            padding: 15px;
            background-color: #fffbeb;
            border-left: 4px solid #f59e0b;
            color: #92400e;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="header">
            <img src="{$logoUrl}" alt="maJIStic 2025 Logo">
        </div>
        
        <div class="content">
            <h2>Registration Confirmation</h2>
            <p>Dear {$data['student_name']},</p>
            <p>Thank you for registering for maJIStic 2025. Your registration has been successfully received and recorded in our system.</p>
            
            <div class="registration-info">
                <h3>Registration Details</h3>
                <table>
                    <tr>
                        <td>JIS ID</td>
                        <td>{$data['jis_id']}</td>
                    </tr>
                    <tr>
                        <td>Name</td>
                        <td>{$data['student_name']}</td>
                    </tr>
                    <tr>
                        <td>Department</td>
                        <td>{$data['department']}</td>
                    </tr>
                    <tr>
                        <td>Email</td>
                        <td>{$data['email']}</td>
                    </tr>
                    <tr>
                        <td>Mobile</td>
                        <td>{$data['mobile']}</td>
                    </tr>

HTML;

    // Add competition info if available
    if (isset($data['competition']) && !empty($data['competition'])) {
        $html .= <<<HTML
                    <tr>
                        <td>Competition</td>
                        <td>{$data['competition']}</td>
                    </tr>

HTML;
    }

    $html .= <<<HTML
                    <tr>
                        <td>Registration Date</td>
                        <td>{$formatted_date}</td>
                    </tr>
                    <tr>
                        <td>Payment Status</td>
                        <td><strong style="color: #f59e0b;">PENDING</strong></td>
                    </tr>
                </table>
            </div>
            
            <p>Your registration is almost complete! To confirm your spot at maJIStic 2025, please complete the payment process by clicking the button below:</p>
            
            <div style="text-align: center; margin: 30px 0;">
                <a href="{$payment_link}" class="button">Complete Payment</a>
            </div>
            
            <div class="payment-note">
                <p><strong>Note:</strong> If you've already completed the payment, please disregard this message. You will receive a separate payment confirmation email.</p>
            </div>
            
            <p>If you have any questions or need further assistance, please don't hesitate to contact our support team.</p>
            
            <p style='background-color: #ffeeee; border: 1px solid #ff6b6b; padding: 10px; color: #cc0000; font-weight: bold; text-align: center; margin: 15px 0;'>
                <strong>IMPORTANT:</strong> College ID is MANDATORY for check-in on event day. No entry without ID.
            </p>
            <p>We look forward to seeing you at maJIStic 2025!</p>
            
            <p>Warm Regards,<br>maJIStic Team</p>

        </div>
        
        <div class="footer">
            <p>&copy; {$year} maJIStic 2025. All rights reserved.</p>
            <p>JIS College of Engineering, Kalyani, Nadia - 741235, West Bengal, India</p>
            <div class="social-links">
                <a href="https://www.facebook.com/profile.php?id=100090087469753" target="_blank">Facebook</a> |
                <a href="https://www.instagram.com/majistic_jisce" target="_blank">Instagram</a> |
                <a href="https://www.linkedin.com/company/majistic-jisce/" target="_blank">LinkedIn</a>
            </div>
        </div>
    </div>
</body>
</html>
HTML;

    return $html;
}
?>
