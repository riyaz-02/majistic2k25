<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../includes/db_config.php'; // Include MySQL configuration

/**
 * Sends a payment confirmation email to the user
 * 
 * @param array $data The payment and user details
 * @return bool Whether the email was sent successfully
 */
function sendPaymentConfirmationEmail($data) {
    if (empty($data['email'])) {
        error_log("Cannot send email: recipient email is empty");
        return false;
    }
    
    try {
        $mail = new PHPMailer(true);
        
        // Server settings
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com'; // Replace with your SMTP server
        $mail->SMTPAuth = true;
        $mail->Username = 'payment.majistic@gmail.com'; // Replace with your email
        $mail->Password = 'csibomhfcfmtxpjp'; // Replace with app password or actual password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;
        
        // Recipients
        $mail->setFrom('payment.majistic@gmail.com', 'maJIStic');
        $mail->addAddress($data['email'], $data['name']);
        
        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Payment Confirmation - maJIStic 2K25';
        
        // Email content - HTML template
        $mail->Body = getEmailTemplate($data);
        
        // Plain text alternative
        $mail->AltBody = getPlainTextEmail($data);
        
        $mail->send();

        return true;
    } catch (Exception $e) {
        error_log("Error sending email: " . $e->getMessage());
        return false;
    }
}

/**
 * Generates the HTML email template
 * 
 * @param array $data The payment and user details
 * @return string The HTML email content
 */
function getEmailTemplate($data) {
    // Default logo URL in case the config file is not accessible
    $logoUrl = 'https://cdn.emailacademy.com/user/fecdcd5176d5ee6a27e1962040645abfa28cce551d682738efd2fc3e158c65e3/majisticlogo2025_03_18_22_18_20.png';
    
    // Include logo configuration file
    $logoConfigPath = __DIR__ . '/../../src/config/email_logo_config.php';
    if (file_exists($logoConfigPath)) {
        include_once $logoConfigPath;
        // If EMAIL_LOGO_URL is defined in the config, use that instead
        if (defined('EMAIL_LOGO_URL')) {
            $logoUrl = EMAIL_LOGO_URL;
        }
    }
    
    // Use the configured base URL if available, or fallback to default
    $baseUrl = defined('EMAIL_BASE_URL') ? EMAIL_BASE_URL : 'https://jiscollege.ac.in/majistic';
    
    $paymentDate = $data['payment_date'];
    $receiptNumber = $data['receipt_number'];
    $name = $data['name'];
    $jisId = $data['jis_id'];
    $department = $data['department'];
    
    // Set parameters based on registration type
    $isStudent = ($data['type'] === 'student');
    $amount = $isStudent ? '500' : '1000';
    $receiptLabel = $isStudent ? 'Receipt Number' : 'Receipt/Ref. Number';
    
    // Custom payment details content based on type
    $paymentDetails = "<p><strong>Name:</strong> $name</p>
                       <p><strong>JIS ID:</strong> $jisId</p>
                       <p><strong>Department:</strong> $department</p>
                       <p><strong>Amount Paid:</strong> ₹$amount</p>
                       <p><strong>$receiptLabel:</strong> $receiptNumber</p>
                       <p><strong>Payment Date:</strong> $paymentDate</p>";
    
    // Email template with inline CSS
    return <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Confirmation - MaJIStic 2K25</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            line-height: 1.6;
            color: #333333;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        .header {
            background: linear-gradient(135deg, #2c3e50, #3498db);
            color: #ffffff;
            padding: 20px;
            text-align: center;
        }
        .logo {
            max-width: 150px;
            height: auto;
            margin-bottom: 10px;
        }
        .content {
            padding: 30px;
        }
        .payment-details {
            background-color: #f8f9fa;
            border-left: 4px solid #3498db;
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
        }
        .important-notice {
            background-color: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
            font-weight: 500;
        }
        .ticket-notice {
            background-color: #d4edda;
            border-left: 4px solid #28a745;
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
            font-weight: 500;
            color: #155724;
        }
        .check-status-btn {
            background: #3498db;
            color: #ffffff;
            padding: 10px 20px;
            text-align: center;
            text-decoration: none;
            display: inline-block;
            border-radius: 4px;
            font-weight: bold;
            margin: 20px 0;
        }
        .footer {
            background-color: #f4f4f4;
            padding: 15px;
            text-align: center;
            font-size: 12px;
            color: #666666;
        }
        .social-links {
            margin: 15px 0;
        }
        .social-link {
            display: inline-block;
            margin: 0 10px;
            color: #3498db;
            text-decoration: none;
        }
        h1 {
            color: #2c3e50;
            margin-top: 0;
        }
        p {
            margin: 10px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <img src="$logoUrl" alt="maJIStic 2K25" class="logo">
        </div>
        <div class="content">
            <h1>Payment Confirmation</h1>
            <p>Dear $name,</p>
            <p>We're pleased to confirm that your payment for maJIStic 2K25 has been successfully processed. Below are the details of your payment:</p>
            
            <div class="payment-details">
                $paymentDetails
            </div>
            
            <div class="important-notice">
                <p><strong>Important:</strong> Please bring your College ID card for check-in. It is mandatory for entry.</p>
            </div>
            
            <div class="ticket-notice">
                <p><strong>Ticket Information:</strong> The event ticket will be emailed to your registered mail address 3 days before the event.</p>
            </div>
            
            <p>You can check your registration status by clicking the button below:</p>
            <p style='text-align: center; margin: 20px 0;'>
                <a href="$baseUrl/check_status.php?jis_id=$jisId" class="check-status-btn">Check Status</a>
            </p>
            
            <p>If you have any questions or concerns, please don't hesitate to contact us at <a href="mailto:majistic@jiscollege.ac.in">majistic@jiscollege.ac.in</a></p>
            
            <p>We look forward to seeing you at maJIStic 2K25! 🎉</p>
            
            <p>Best regards,<br>maJIStic Team
        </div>
        <div class="footer">
            <div class="social-links">
            <a href="https://www.facebook.com/profile.php?id=100090087469753" target="_blank">Facebook</a> |
                <a href="https://www.instagram.com/majistic_jisce" target="_blank">Instagram</a> |
                <a href="https://www.linkedin.com/company/majistic-jisce/" target="_blank">LinkedIn</a>
            </div>
            <p>&copy; 2025 maJIStic. All rights reserved.</p>
            <p>JIS College of Engineering, Kalyani, West Bengal, India</p>
        </div>
    </div>
</body>
</html>
HTML;
}

/**
 * Generates the plain text version of the email
 * 
 * @param array $data The payment and user details
 * @return string The plain text email content
 */
function getPlainTextEmail($data) {
    // Use the configured base URL if available, or fallback to default
    $baseUrl = 'https://jiscollege.ac.in/majistic';
    $logoConfigPath = __DIR__ . '/../../src/config/email_logo_config.php';
    if (file_exists($logoConfigPath)) {
        include_once $logoConfigPath;
        if (defined('EMAIL_BASE_URL')) {
            $baseUrl = EMAIL_BASE_URL;
        }
    }

    $paymentDate = $data['payment_date'];
    $receiptNumber = $data['receipt_number'];
    $name = $data['name'];
    $jisId = $data['jis_id'];
    $department = $data['department'];
    
    // Set parameters based on registration type
    $isStudent = ($data['type'] === 'student');
    $amount = $isStudent ? '500' : '1000';
    $receiptLabel = $isStudent ? 'Receipt Number' : 'Receipt/Ref. Number';
    
    return <<<TEXT
PAYMENT CONFIRMATION - MAJISTIC 2K25

Dear $name,

We're pleased to confirm that your payment for MaJIStic 2K25 has been successfully processed. Below are the details of your payment:

Name: $name
JIS ID: $jisId
Department: $department
Amount Paid: ₹$amount
$receiptLabel: $receiptNumber
Payment Date: $paymentDate

IMPORTANT: Please bring your College ID card for check-in. It is mandatory for entry.

You can check your registration status at: $baseUrl/check_status.php?jis_id=$jisId

If you have any questions or concerns, please don't hesitate to contact us at majistic@jiscollege.ac.in.

We look forward to seeing you at maJIStic 2K25!

Best regards,
maJIStic Team
JIS College of Engineering

-------------------------
© 2025 maJIStic. All rights reserved.
JIS College of Engineering, Kalyani, West Bengal, India
TEXT;
}
?>