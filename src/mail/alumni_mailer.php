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
    error_log("PHPMailer autoloader not found. Email functionality for alumni will be disabled.");
    
    function sendAlumniRegistrationEmail($data) {
        error_log("Alumni email sending skipped - PHPMailer not available");
        // Record the intended email in a log file instead
        $logFile = __DIR__ . '/../../logs/alumni_email_queue.log';
        $dir = dirname($logFile);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        $logEntry = date('Y-m-d H:i:s') . " - Would have sent alumni registration email to: " . $data['email'] . 
                    " - JIS ID: " . $data['jis_id'] . "\n";
        file_put_contents($logFile, $logEntry, FILE_APPEND);
        return false;
    }
    
    function generateAlumniEmailTemplate($data) {
        return "Alumni email template generation skipped - PHPMailer not available";
    }
    
    function sendAlumniPaymentConfirmationEmail($data) {
        error_log("Alumni payment email sending skipped - PHPMailer not available");
        $logFile = __DIR__ . '/../../logs/alumni_email_queue.log';
        $dir = dirname($logFile);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        $logEntry = date('Y-m-d H:i:s') . " - Would have sent alumni payment confirmation email to: " . $data['email'] . 
                    " - Payment ID: " . $data['payment_id'] . "\n";
        file_put_contents($logFile, $logEntry, FILE_APPEND);
        return false;
    }
    
    function generateAlumniPaymentEmailTemplate($data) {
        return "Alumni payment email template generation skipped - PHPMailer not available";
    }
    
    // Exit this file early
    return;
}

/**
 * Function to send alumni registration confirmation email
 * 
 * @param array $data Registration data
 * @return bool Whether the email was sent successfully
 */
function sendAlumniRegistrationEmail($data) {
    // Create a new PHPMailer instance
    $mail = new PHPMailer(true);
    
    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com'; 
        $mail->SMTPAuth = true;
        $mail->Username   = 'alumni.jismajistic@gmail.com'; // Alumni-specific email
        $mail->Password   = 'zxcvbnm123456#'; // Replace with actual password for alumni email
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;
        
        // Recipients
        $mail->setFrom('alumni.jismajistic@gmail.com', 'maJIStic Alumni Association');
        $mail->addAddress($data['email'], $data['alumni_name']);
        $mail->addReplyTo('alumni.jismajistic@gmail.com', 'maJIStic Alumni Support');
        
        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Alumni Registration Confirmation - maJIStic 2025';
        
        // Email HTML body
        $mail->Body = generateAlumniEmailTemplate($data);
        
        // Plain text version for non-HTML mail clients
        $mail->AltBody = "Alumni Registration Confirmation - maJIStic 2025\n\n" .
                        "Dear {$data['alumni_name']},\n\n" .
                        "Thank you for registering for maJIStic 2025 as an alumni.\n" .
                        "JIS ID: {$data['jis_id']}\n" .
                        "Department: {$data['department']}\n" .
                        "Passout Year: {$data['passout_year']}\n" .
                        "Registration Date: {$data['registration_date']}\n\n" .
                        "Please proceed to complete your payment.\n\n" .
                        "Regards,\nmaJIStic Alumni Team";
        
        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Alumni email could not be sent. Mailer Error: {$mail->ErrorInfo}");
        return false;
    }
}

/**
 * Function to generate HTML email template for alumni registration
 * 
 * @param array $data Registration data
 * @return string HTML content of the email
 */
function generateAlumniEmailTemplate($data) {
    // Get current year for copyright (using IST)
    $year = date('Y');
    
    // Format registration date for display in email if not already formatted
    if (isset($data['registration_date']) && strtotime($data['registration_date'])) {
        $formatted_date = date('d M Y, h:i A', strtotime($data['registration_date']));
    } else {
        $formatted_date = date('d M Y, h:i A'); // Use current time if not provided
    }
    
    // Generate payment link
    $payment_link = "https://skriyaz.com/majistic/src/transaction/payment.php?jis_id=" . urlencode($data['jis_id']) . "&alumni=1";
    
    // Logo URL - update with actual URL to the maJIStic logo
    $logoUrl = 'https://i.ibb.co/RGQ7Lj6K/majisticlogo.png';
    
    // HTML Template
    $html = <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alumni Registration Confirmation</title>
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
        .alumni-header {
            background: linear-gradient(to right, #3498db, #8e44ad);
            color: white;
            padding: 10px 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="header">
            <img src="{$logoUrl}" alt="maJIStic 2025 Logo">
        </div>
        
        <div class="content">
            <div class="alumni-header">
                <h2>Alumni Registration Confirmation</h2>
            </div>
            
            <p>Dear {$data['alumni_name']},</p>
            <p>Thank you for registering for maJIStic 2025 as an alumni. We're excited to welcome you back to your alma mater for this special event. Your registration has been successfully received and recorded in our system.</p>
            
            <div class="registration-info">
                <h3>Registration Details</h3>
                <table>
                    <tr>
                        <td>JIS ID</td>
                        <td>{$data['jis_id']}</td>
                    </tr>
                    <tr>
                        <td>Name</td>
                        <td>{$data['alumni_name']}</td>
                    </tr>
                    <tr>
                        <td>Department</td>
                        <td>{$data['department']}</td>
                    </tr>
                    <tr>
                        <td>Passout Year</td>
                        <td>{$data['passout_year']}</td>
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

    // Add current organization if available
    if (isset($data['current_organization']) && !empty($data['current_organization'])) {
        $html .= <<<HTML
                    <tr>
                        <td>Current Organization</td>
                        <td>{$data['current_organization']}</td>
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
                <a href="{$payment_link}" class="button">Complete Payment (₹1000)</a>
            </div>
            
            <div class="payment-note">
                <p><strong>Note:</strong> If you've already completed the payment, please disregard this message. You will receive a separate payment confirmation email.</p>
            </div>
            
            <p>If you have any questions or need further assistance, please don't hesitate to contact our alumni support team.</p>
            
            <p style='background-color: #ffeeee; border: 1px solid #ff6b6b; padding: 10px; color: #cc0000; font-weight: bold; text-align: center; margin: 15px 0;'>
                <strong>IMPORTANT:</strong> Please bring your Alumni ID or any Government ID for verification on the event day.
            </p>
            <p>We look forward to seeing you at maJIStic 2025!</p>
            
            <p>Warm Regards,<br>maJIStic Alumni Team</p>

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

/**
 * Function to send alumni payment confirmation email
 * 
 * @param array $data Payment and alumni data
 * @return bool Whether the email was sent successfully
 */
function sendAlumniPaymentConfirmationEmail($data) {
    // Create a new PHPMailer instance
    $mail = new PHPMailer(true);
    
    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com'; 
        $mail->SMTPAuth = true;
        $mail->Username   = 'alumni.jismajistic@gmail.com'; // Alumni-specific email
        $mail->Password   = 'zxcvbnm123456#'; // Replace with actual password for alumni email
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;
        
        // Recipients
        $mail->setFrom('alumni.jismajistic@gmail.com', 'maJIStic Alumni Association');
        $mail->addAddress($data['email'], $data['alumni_name']);
        $mail->addReplyTo('alumni.jismajistic@gmail.com', 'maJIStic Alumni Support');
        
        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Payment Confirmation - maJIStic 2025 Alumni';
        
        // Email HTML body
        $mail->Body = generateAlumniPaymentEmailTemplate($data);
        
        // Plain text version for non-HTML mail clients
        $mail->AltBody = "Payment Confirmation - maJIStic 2025 Alumni\n\n" .
                        "Dear {$data['alumni_name']},\n\n" .
                        "Your payment for maJIStic 2025 has been successfully received.\n" .
                        "Payment ID: {$data['payment_id']}\n" .
                        "Amount: Rs. {$data['amount']}\n" .
                        "Date: {$data['payment_date']}\n\n" .
                        "Thank you for registering for maJIStic 2025. We look forward to seeing you at the event!\n\n" .
                        "Regards,\nmaJIStic Alumni Team";
        
        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Alumni payment email could not be sent. Mailer Error: {$mail->ErrorInfo}");
        return false;
    }
}

/**
 * Function to generate HTML email template for alumni payment
 * 
 * @param array $data Payment and alumni data
 * @return string HTML content of the email
 */
function generateAlumniPaymentEmailTemplate($data) {
    // Get current year for copyright (using IST)
    $year = date('Y');
    
    // Format payment date for display in email if not already formatted
    if (strtotime($data['payment_date'])) {
        $formatted_date = date('d M Y, h:i A', strtotime($data['payment_date']));
    } else {
        $formatted_date = $data['payment_date'];
    }
    
    // Logo URL - update with actual URL to the maJIStic logo
    $logoUrl = 'https://i.ibb.co/RGQ7Lj6K/majisticlogo.png';
    
    // HTML Template
    $html = <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Confirmation - Alumni</title>
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
        .alumni-header {
            background: linear-gradient(to right, #3498db, #8e44ad);
            color: white;
            padding: 10px 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="header">
            <img src="{$logoUrl}" alt="maJIStic 2025 Logo">
        </div>
        
        <div class="content">
            <div class="alumni-header">
                <h2>Alumni Payment Confirmation</h2>
            </div>
            
            <p>Dear {$data['alumni_name']},</p>
            <p>Thank you for your payment for maJIStic 2025. Your transaction has been successfully processed.</p>
            
            <div class="ticket-info">
                <h3>Payment Details</h3>
                <table>
                    <tr>
                        <td>JIS ID</td>
                        <td>{$data['jis_id']}</td>
                    </tr>
                    <tr>
                        <td>Passout Year</td>
                        <td>{$data['passout_year']}</td>
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
                <p><strong>Important:</strong> You will receive your Alumni Event Pass in your registered email 3 days before the event. Don't forget to check your email, including spam folder.</p>
            </div>
            
            <p>Please keep this email for your records. You may be required to show this confirmation at the event.</p>
            
            <p>We look forward to welcoming you back to JIS for maJIStic 2025!</p>
            
            <p>Warm Regards,<br>maJIStic Alumni Team</p>
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
