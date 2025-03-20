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
    
    function sendAlumniPaymentConfirmationEmail($data) {
        error_log("Alumni email sending skipped - PHPMailer not available");
        // Record the intended email in a log file instead
        $logFile = __DIR__ . '/../../logs/alumni_email_queue.log';
        $dir = dirname($logFile);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        $logEntry = date('Y-m-d H:i:s') . " - Would have sent alumni email to: " . $data['email'] . 
                    " - Payment ID: " . $data['payment_id'] . 
                    " - Amount: " . $data['amount'] . "\n";
        file_put_contents($logFile, $logEntry, FILE_APPEND);
        return false;
    }
    
    function generateAlumniEmailTemplate($data) {
        return "Alumni email template generation skipped - PHPMailer not available";
    }
    
    // Exit this file early
    return;
}

/**
 * Function to send payment confirmation email to alumni
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
        $mail->Host = 'smtp.gmail.com'; // Set the SMTP server to send through
        $mail->SMTPAuth = true;
        $mail->Username   = 'payment.majistic@gmail.com';
        $mail->Password   = 'csibomhfcfmtxpjp';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;
        
        // Recipients
        $mail->setFrom('payment.majistic@gmail.com', 'maJIStic');
        $mail->addAddress($data['email'], $data['alumni_name']);
        $mail->addReplyTo('payment.majistic@gmail.com', 'maJIStic Support');
        
        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Alumni Payment Confirmation - maJIStic 2025';
        
        // Email HTML body
        $mail->Body = generateAlumniEmailTemplate($data);
        
        // Plain text version for non-HTML mail clients
        $mail->AltBody = "Alumni Payment Confirmation - maJIStic 2025\n\n" .
                        "Dear {$data['alumni_name']},\n\n" .
                        "Your alumni registration payment for maJIStic 2025 has been successfully received.\n" .
                        "Payment ID: {$data['payment_id']}\n" .
                        "Amount: Rs. {$data['amount']}\n" .
                        "Date: {$data['payment_date']}\n\n" .
                        "Thank you for registering as an alumnus for maJIStic 2025. We look forward to welcoming you back!\n\n" .
                        "Regards,\nmaJIStic Team";
        
        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Alumni email could not be sent. Mailer Error: {$mail->ErrorInfo}");
        return false;
    }
}

/**
 * Function to generate HTML email template for alumni
 * 
 * @param array $data Payment and alumni data
 * @return string HTML content of the email
 */
function generateAlumniEmailTemplate($data) {
    // Get current year for copyright (using IST)
    $year = date('Y');
    
    // Format payment date for display in email if not already formatted
    if (strtotime($data['payment_date'])) {
        $formatted_date = date('d M Y, h:i A', strtotime($data['payment_date']));
    } else {
        $formatted_date = $data['payment_date'];
    }
    
    // Logo URL - get from config or use default
    $logoUrl = defined('EMAIL_LOGO_URL') ? EMAIL_LOGO_URL : 'https://cdn.emailacademy.com/user/fecdcd5176d5ee6a27e1962040645abfa28cce551d682738efd2fc3e158c65e3/majisticlogo2025_03_18_22_18_20.png';
    
    // Base URL for links - get from config or use default
    $baseUrl = defined('EMAIL_BASE_URL') ? EMAIL_BASE_URL : 'https://jiscollege.ac.in/majistic';
    
    // HTML Template with alumni-specific details
    $html = <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alumni Payment Confirmation</title>
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
        .alumni-badge {
            display: inline-block;
            background-color: #7c3aed;
            color: white;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
            margin-bottom: 10px;
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
            <h2>Payment Confirmation</h2>
            <p>Dear {$data['alumni_name']},</p>
            <p>Thank you for your alumni registration payment for maJIStic 2025. Your transaction has been successfully processed.</p>
            
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
                        <td>Passout Year</td>
                        <td>{$data['passout_year']}</td>
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
                <p><strong>Important:</strong> You will receive additional alumni event details in your registered email closer to the event date. Don't forget to check your email, including spam folder.</p>
            </div>
            
            <p>Please keep this email for your records. You may be required to show this confirmation at the alumni meet during maJIStic 2025.</p>
            
            <p style='text-align: center; margin: 25px 0;'>
                <a href="{$baseUrl}/check_status.php?jis_id={$data['jis_id']}" class="button" style="background-color: #3498db; color: white !important; text-decoration: none; padding: 12px 24px; border-radius: 5px; font-weight: bold; font-size: 16px;">Check Status</a>
            </p>
            
            <p>We look forward to welcoming you back at maJIStic 2025!</p>
            
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
    
    // Generate payment link
    $payment_link = "https://skriyaz.com/majistic/src/transaction/payment.php?jis_id=" . urlencode($data['jis_id']) . "&alumni=1";
    
    // Logo URL - get from config or use default
    $logoUrl = defined('EMAIL_LOGO_URL') ? EMAIL_LOGO_URL : 'https://cdn.emailacademy.com/user/fecdcd5176d5ee6a27e1962040645abfa28cce551d682738efd2fc3e158c65e3/majisticlogo2025_03_18_22_18_20.png';
    
    // Get coordinator information
    $coordinator_info = getAlumniCoordinatorInfo($data['department']);
    
    // Base URL for links - get from config or use default
    $baseUrl = defined('EMAIL_BASE_URL') ? EMAIL_BASE_URL : 'https://jiscollege.ac.in/majistic';
    
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
        .alumni-badge {
            display: inline-block;
            background-color: #7c3aed;
            color: white;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
            margin-bottom: 10px;
        }
        .coordinator-info {
            background-color: #e8f4fd;
            border: 1px solid #cce5ff;
            border-radius: 5px;
            padding: 15px;
            margin: 20px 0;
            color: #0c5460;
        }
        .coordinator-info h4 {
            margin-top: 0;
            color: #0c5460;
            border-bottom: 1px solid #bee5eb;
            padding-bottom: 10px;
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
            <h2>Registration Confirmation</h2>
            <p>Dear {$data['alumni_name']},</p>
            <p>Thank you for registering for maJIStic 2025 as an alumnus. Your registration has been successfully received and recorded in our system.</p>
            
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
                    <tr>
                        <td>Current Organization</td>
                        <td>{$data['current_organization']}</td>
                    </tr>
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
            
            <p>Your registration is almost complete! To confirm your spot at maJIStic 2025, please make the payment to your department coordinator in person.</p>
            
            <div style="background-color: #e7f3ff; border: 1px solid #3498db; padding: 15px; border-radius: 5px; margin: 20px 0;">
                <h4 style="color: #3498db; margin-top: 0;">Payment Instructions</h4>
                <p><strong>1.</strong> Contact your department coordinator listed below</p>
                <p><strong>2.</strong> Make the payment in person</p>
                <p><strong>3.</strong> Collect your receipt as proof of payment</p>
                <p><strong>Amount:</strong> Please confirm the registration fee with your department coordinator</p>
            </div>
            
            <div class="coordinator-info">
                <h4>Department Coordinator</h4>
                {$coordinator_info}
            </div>
            
            <div class="payment-note">
                <p><strong>Note:</strong> If you've already completed the payment to your department coordinator, please disregard this message. You will receive a separate payment confirmation email once your payment is processed.</p>
            </div>
            
            <p>If you have any questions or need further assistance, please don't hesitate to contact our alumni support team.</p>
            
            <p style='background-color: #ffeeee; border: 1px solid #ff6b6b; padding: 10px; color: #cc0000; font-weight: bold; text-align: center; margin: 15px 0;'>
                <strong>IMPORTANT:</strong> Please bring your College ID or any Government ID for verification on the event day.
            </p>
            <p>We look forward to welcoming you back at maJIStic 2025!</p>
            
            <p style='text-align: center; margin: 25px 0;'>
                <a href="{$baseUrl}/check_status.php?jis_id={$data['jis_id']}" class="button" style="background-color: #3498db; color: white !important; text-decoration: none; padding: 12px 24px; border-radius: 5px; font-weight: bold; font-size: 16px;">Check Status</a>
            </p>
            
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

/**
 * Function to get alumni coordinator information based on department
 * 
 * @param string $department The alumni's department
 * @return string HTML content for coordinator information
 */
function getAlumniCoordinatorInfo($department) {
    global $department_coordinators;
    
    // Initialize default coordinator info with multiple contacts
    $default_info = "<h4>Alumni Coordinator Contacts</h4>";
    $default_info .= "<p><strong>Primary Contact:</strong></p>";
    $default_info .= "<p><strong>Name:</strong> Priyanshu Nayan</p>";
    $default_info .= "<p><strong>Contact:</strong> 7004706722</p>";
    $default_info .= "<p><strong>Available:</strong> 10:00 AM - 5:00 PM (Monday-Friday)</p>";
    
    $default_info .= "<p style='margin-top:15px'><strong>Alternative Contacts:</strong></p>";
    $default_info .= "<p><strong>Name:</strong> Dr. Proloy Ghosh</p>";
    $default_info .= "<p><strong>Contact:</strong> 7980532913</p>";
    
    $default_info .= "<p style='margin-top:10px'><strong>Name:</strong> Dr. Madhura Chakraborty</p>";
    $default_info .= "<p><strong>Contact:</strong> 7980979789</p>";
    
    // Try to find a coordinator for the specific department
    if (isset($department_coordinators) && !empty($department)) {
        try {
            // Create a department filter
            $filter = ['department' => ['$regex' => $department, '$options' => 'i']];
            
            // Find the coordinator
            $coordinator = $department_coordinators->findOne($filter);
            
            if ($coordinator) {
                $available_time = isset($coordinator['available_time']) ? 
                    $coordinator['available_time'] : 
                    '9:00 AM - 5:00 PM (Monday-Friday)';
                
                $coordinator_info = "<h4>Department Alumni Coordinator</h4>";
                $coordinator_info .= "<p><strong>Name:</strong> " . htmlspecialchars($coordinator['name']) . "</p>";
                $coordinator_info .= "<p><strong>Department:</strong> " . htmlspecialchars($coordinator['department']) . "</p>";
                $coordinator_info .= "<p><strong>Contact:</strong> " . htmlspecialchars($coordinator['contact']) . "</p>";
                $coordinator_info .= "<p><strong>Available:</strong> " . htmlspecialchars($available_time) . "</p>";
                
                // Also provide the default contacts as alternatives
                $coordinator_info .= "<p style='margin-top:15px'><strong>Alternative Contacts (if unavailable):</strong></p>";
                $coordinator_info .= "<p><strong>Name:</strong> Priyanshu Nayan (7004706722)</p>";
                $coordinator_info .= "<p><strong>Name:</strong> Dr. Proloy Ghosh (7980532913)</p>";
                $coordinator_info .= "<p><strong>Name:</strong> Dr. Madhura Chakraborty (7980979789)</p>";
                
                $coordinator_info .= "<p style='margin-top:15px'>For alumni-specific inquiries, please also contact our alumni team at <strong>majistic.alumni@gmail.com</strong></p>";
                
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
