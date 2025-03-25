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

// Include logo configuration
$logoConfigPath = __DIR__ . '/../../src/config/email_logo_config.php';
if (file_exists($logoConfigPath)) {
    include_once $logoConfigPath;
}

// Include alumni coordinator configuration
$alumni_config_path = __DIR__ . '/../../src/config/alumni_coordinator_config.php';
if (file_exists($alumni_config_path)) {
    include_once $alumni_config_path;
}

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
    error_log("PHPMailer autoloader not found. Alumni email functionality will be disabled.");
    
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
    
    function generateAlumniRegistrationTemplate($data) {
        return "Alumni registration email template generation skipped - PHPMailer not available";
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
        $mail->Host = 'smtp.gmail.com'; // Set the SMTP server to send through
        $mail->SMTPAuth = true;
        $mail->Username   = 'majistic.alumni@gmail.com';
        $mail->Password   = 'iakqdaxcbtmcfucr';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;
        
        // Recipients
        $mail->setFrom('majistic.alumni@gmail.com', 'maJIStic Alumni');
        $mail->addAddress($data['email'], $data['alumni_name']);
        $mail->addReplyTo('majistic.alumni@gmail.com', 'maJIStic Alumni Support');
        
        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Alumni Registration Confirmation - maJIStic 2025';
        
        // Email HTML body
        $mail->Body = generateAlumniRegistrationTemplate($data);
        
        // Plain text version for non-HTML mail clients
        $mail->AltBody = "Alumni Registration Confirmation - maJIStic 2025\n\n" .
                        "Dear {$data['alumni_name']},\n\n" .
                        "Thank you for registering for maJIStic 2025 as an alumnus.\n" .
                        "JIS ID: {$data['jis_id']}\n" .
                        "Department: {$data['department']}\n" .
                        "Passout Year: {$data['passout_year']}\n" .
                        "Registration Date: {$data['registration_date']}\n\n" .
                        "Please proceed to complete your payment.\n\n" .
                        "Regards,\nmaJIStic Alumni Team";
        
        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Alumni registration email could not be sent. Mailer Error: {$mail->ErrorInfo}");
        return false;
    }
}

/**
 * Function to generate HTML email template for alumni registration
 * 
 * @param array $data Registration data
 * @return string HTML content of the email
 */
function generateAlumniRegistrationTemplate($data) {
    // Get current year for copyright (using IST)
    $year = date('Y');
    
    // Format registration date for display in email if not already formatted
    if (isset($data['registration_date']) && strtotime($data['registration_date'])) {
        $formatted_date = date('d M Y, h:i A', strtotime($data['registration_date']));
    } else {
        $formatted_date = date('d M Y, h:i A'); // Use current time if not provided
    }
    
    // Logo URL - get from config or use default
    $logoUrl = defined('EMAIL_LOGO_URL') ? EMAIL_LOGO_URL : 'https://cdn.emailacademy.com/user/fecdcd5176d5ee6a27e1962040645abfa28cce551d682738efd2fc3e158c65e3/majisticlogo2025_03_18_22_18_20.png';
    
    // Get coordinator information
    $coordinator_name = defined('ALUMNI_COORDINATOR_NAME') ? ALUMNI_COORDINATOR_NAME : 'Dr. Proloy Ghosh';
    $coordinator_contact = defined('ALUMNI_COORDINATOR_CONTACT') ? ALUMNI_COORDINATOR_CONTACT : '7980532913';
    $coordinator_email = defined('ALUMNI_COORDINATOR_EMAIL') ? ALUMNI_COORDINATOR_EMAIL : 'majistic@jiscollege.ac.in';
    $payment_qr = defined('ALUMNI_PAYMENT_QR') ? ALUMNI_PAYMENT_QR : '';
    $payment_instructions = defined('ALUMNI_PAYMENT_INSTRUCTIONS') ? ALUMNI_PAYMENT_INSTRUCTIONS : 'Scan the QR code with any UPI app to pay the alumni registration fee (Rs. 1000). After payment, please send a screenshot to the coordinator via WhatsApp for verification.';
    
    // Base URL for links - get from config or use default
    $baseUrl = defined('EMAIL_BASE_URL') ? EMAIL_BASE_URL : 'https://jiscollege.ac.in/majistic';
    
    // HTML Template - Redesigned to include QR code
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
            line-height: 1.5;
            color: #333333;
            max-width: 600px;
            margin: 0 auto;
            padding: 0;
        }
        .email-container {
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .header {
            background: linear-gradient(135deg, #1a1a1a 0%, #303030 100%);
            padding: 25px 20px;
            text-align: center;
        }
        .header img {
            max-width: 220px;
            height: auto;
        }
        .content {
            padding: 30px 25px;
            background-color: #ffffff;
        }
        .registration-info {
            background-color: #f8f9fa;
            border-left: 4px solid #3498db;
            border-radius: 4px;
            padding: 15px;
            margin: 15px 0;
        }
        .registration-info table {
            width: 100%;
            border-collapse: collapse;
            font-size: 14px;
        }
        .registration-info td {
            padding: 6px 0;
        }
        .registration-info td:first-child {
            font-weight: bold;
            width: 40%;
            color: #444;
        }
        .footer {
            background-color: #f0f0f0;
            padding: 15px;
            text-align: center;
            font-size: 12px;
            color: #666666;
        }
        .button {
            display: inline-block;
            background: linear-gradient(135deg, #2980b9 0%, #3498db 100%);
            color: white !important;
            text-decoration: none;
            padding: 10px 20px;
            border-radius: 30px;
            font-weight: bold;
            font-size: 14px;
            margin-top: 10px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            transition: all 0.3s;
        }
        .button:hover {
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        }
        .alumni-badge {
            display: inline-block;
            background: linear-gradient(135deg, #7c3aed 0%, #9333ea 100%);
            color: white;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 11px;
            margin-bottom: 8px;
            font-weight: bold;
            letter-spacing: 0.5px;
        }
        .payment-box {
            background: linear-gradient(to right, #e7f3ff, #f0f8ff);
            border-left: 4px solid #3498db;
            padding: 12px 15px;
            border-radius: 4px;
            margin: 15px 0;
        }
        .payment-box h4 {
            color: #2980b9;
            margin-top: 0;
            margin-bottom: 8px;
            font-size: 15px;
        }
        .alert-box {
            background-color: #fff5f5;
            border-left: 4px solid #ff6b6b;
            padding: 10px;
            border-radius: 4px;
            color: #cc0000;
            font-weight: bold;
            text-align: center;
            margin: 15px 0;
            font-size: 13px;
        }
        .social-links {
            margin-top: 12px;
        }
        .social-links a {
            display: inline-block;
            margin: 0 8px;
            color: #666666;
            text-decoration: none;
        }
        hr {
            border: none;
            border-top: 1px solid #eeeeee;
            margin: 15px 0;
        }
        .qr-container {
            text-align: center;
            background-color: #f9f9f9;
            border-radius: 8px;
            padding: 15px;
            margin: 20px 0;
            border: 1px solid #e0e0e0;
        }
        .qr-container .qr-image {
            background-color: white;
            padding: 10px;
            border-radius: 8px;
            display: inline-block;
            margin-bottom: 10px;
            border: 1px solid #e0e0e0;
            max-width: 200px;
        }
        .qr-container img {
            max-width: 100%;
            height: auto;
        }
        .payment-amount {
            font-size: 18px;
            font-weight: bold;
            color: #7c3aed;
            margin: 10px 0;
        }
        .coordinator-info {
            background-color: #f8f9fa;
            border: 1px solid #e0e0e0;
            border-radius: 4px;
            padding: 12px;
            margin-top: 15px;
            font-size: 13px;
        }
        .coordinator-info p {
            margin: 5px 0;
        }
        .contact-buttons {
            margin-top: 10px;
        }
        .contact-button {
            display: inline-block;
            background-color: #f8f9fa;
            color: #3498db !important;
            border: 1px solid #3498db;
            text-decoration: none;
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 12px;
            margin-right: 5px;
            margin-bottom: 5px;
        }
        .contact-button.whatsapp {
            color: #25D366 !important;
            border-color: #25D366;
        }
        .contact-button.email {
            color: #e74c3c !important;
            border-color: #e74c3c;
        }
        .payment-status-note {
            background-color: #fff9db;
            border-left: 4px solid #fcc419;
            padding: 12px 15px;
            border-radius: 4px;
            margin: 15px 0;
            font-size: 13px;
        }
        .payment-status-note h4 {
            color: #e67700;
            margin-top: 0;
            margin-bottom: 8px;
            font-size: 15px;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="header">
            <img src="{$logoUrl}" alt="maJIStic 2025 Logo">
        </div>
        
        <div class="content">
            <span class="alumni-badge">ALUMNI REGISTRATION</span>
            <h2 style="margin-top:5px;margin-bottom:10px;">Registration Confirmed</h2>
            <p>Dear <strong>{$data['alumni_name']}</strong>,</p>
            <p>Thank you for registering for <strong>maJIStic 2025</strong>! Your registration has been successfully recorded.</p>
            
            <div class="registration-info">
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
                        <td>Passout Year</td>
                        <td>{$data['passout_year']}</td>
                    </tr>
                    <tr>
                        <td>Payment Status</td>
                        <td><strong style="color: #f59e0b;">PENDING</strong></td>
                    </tr>
                </table>
            </div>
            
            <div class="payment-box">
                <h4>💳 Payment Instructions</h4>
                <p style="margin:5px 0;font-size:13px;">
                   Please use the QR code below to make your payment | <strong>Amount: Rs. 1000</strong>
                </p>
            </div>
            
            <!-- QR Code for Payment -->
            <div class="qr-container">
                <div class="payment-amount">₹1000</div>
                
                <?php if (!empty($payment_qr)): ?>
                <div class="qr-image">
                    <img src="{$payment_qr}" alt="Payment QR Code">
                </div>
                <p style="font-size: 13px; color: #666; text-align: left;">{$payment_instructions}</p>
                <?php else: ?>
                <p style="color: #e74c3c; font-weight: bold;">QR code not available. Please contact the alumni coordinator directly.</p>
                <?php endif; ?>
            </div>
            
            <!-- Payment status note -->
            <div class="payment-status-note">
                <h4>⏱️ Payment Status Update</h4>
                <p style="margin:5px 0;">After making your payment, please allow some time for your payment status to be updated. Our team is diligently verifying all payments and will update your status as soon as possible.</p>
                <p style="margin:5px 0;">If you've already made the payment and status is not updated within 48 hours, please contact the alumni coordinator.</p>
            </div>
            
            <div class="coordinator-info">
                <p><strong>Alumni Coordinator:</strong> {$coordinator_name}</p>
                <p><strong>Contact:</strong> {$coordinator_contact}</p>
                <?php if (!empty($coordinator_email)): ?>
                <p><strong>Email:</strong> {$coordinator_email}</p>
                <?php endif; ?>
                
                <div class="contact-buttons">
                    <a href="tel:+91{$coordinator_contact}" class="contact-button">📞 Call</a>
                    <a href="https://wa.me/91{$coordinator_contact}?text=Hello,%20I%20have%20registered%20for%20maJIStic%202025%20as%20an%20alumnus%20(JIS%20ID:%20{$data['jis_id']}).%20I%20would%20like%20to%20complete%20my%20payment." class="contact-button whatsapp">📱 WhatsApp</a>
                    <?php if (!empty($coordinator_email)): ?>
                    <a href="mailto:{$coordinator_email}?subject=Alumni%20Registration%20Payment%20for%20maJIStic%202025&body=Hello,%0A%0AI%20have%20registered%20for%20maJIStic%202025%20as%20an%20alumnus%20(JIS%20ID:%20{$data['jis_id']}).%0A%0AI%20would%20like%20to%20complete%20my%20payment.%0A%0AThank%20you." class="contact-button email">✉️ Email</a>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="alert-box">
                <span>🆔 Please bring your College ID or Government ID on the event day for check-in.</span>
            </div>

            <div style="text-align:center;margin:20px 0 15px;">
                <a href="{$baseUrl}/check_status.php" class="button">Check Registration Status</a>
            </div>
            
            <hr>
            
            <p style="margin-bottom:0;">Warm Regards,<br><strong>maJIStic Team</strong></p>
        </div>
        
        <div class="footer">
            <p>&copy; {$year} maJIStic | JIS College of Engineering</p>
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
 * Function to get alumni coordinator information based on department
 * 
 * @param string $department The alumni's department
 * @return string HTML content for coordinator information
 */
function getAlumniCoordinatorInfo($department) {
    global $department_coordinators;
    
    // Initialize default coordinator info with support contacts
    $default_info = "<p style='margin:5px 0'><strong>Note:</strong> No specific coordinator found. Please contact maJIStic Support.</p>";
    $default_info .= "<p style='margin:5px 0'><strong>Support Email:</strong> <a href='mailto:majistic@jiscollege.ac.in'>majistic@jiscollege.ac.in</a></p>";
    $default_info .= "<p style='margin:5px 0'><strong>WhatsApp Community:</strong> <a href='https://chat.whatsapp.com/JyDMUAA3zw9KfbPvWhXQ1l'>Join Here</a></p>";
    
    // Try to find a coordinator for the specific department
    if (isset($department_coordinators) && !empty($department)) {
        try {
            // Create a department filter
            $filter = ['department' => ['$regex' => $department, '$options' => 'i']];
            
            // Find the coordinator
            $coordinator = $department_coordinators->findOne($filter);
            
            if ($coordinator) {
                $coordinator_info = "<p style='margin:5px 0'><strong>Name:</strong> " . htmlspecialchars($coordinator['name']) . "</p>";
                $coordinator_info .= "<p style='margin:5px 0'><strong>Contact:</strong> " . htmlspecialchars($coordinator['contact']) . "</p>";
                
                return $coordinator_info;
            }
        } catch (Exception $e) {
            error_log("Error fetching coordinator for alumni email: " . $e->getMessage());
        }
    }
    
    // Return default coordinator info if no specific coordinator is found
    return $default_info;
}
?>
