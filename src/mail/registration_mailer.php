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
        $mail->Password   = 'qbbegaqdqyhvdrla';
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
                        "Please pay the registration fee to your department coordinator.\n\n" .
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
    // Use the configured base URL if available, or fallback to default
    $baseUrl = defined('EMAIL_BASE_URL') ? EMAIL_BASE_URL : 'https://majistic.org';
    
    // Format registration date for display in email if not already formatted
    if (isset($data['registration_date']) && strtotime($data['registration_date'])) {
        $formatted_date = date('d M Y, h:i A', strtotime($data['registration_date']));
    } else {
        $formatted_date = date('d M Y, h:i A'); // Use current time if not provided
    }
    $jis_id=$data['jis_id'];
    
    // Logo URL - get from config or use default
    $logoUrl = defined('EMAIL_LOGO_URL') ? EMAIL_LOGO_URL : 'https://cdn.emailacademy.com/user/fecdcd5176d5ee6a27e1962040645abfa28cce551d682738efd2fc3e158c65e3/majisticlogo2025_03_18_22_18_20.png';
    
    // Get coordinator information based on department
    $coordinator_info = getCoordinatorInfo($data['department']);
    
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
            font-family: 'Segoe UI', 'Arial', sans-serif;
            line-height: 1.6;
            color: #333333;
            max-width: 600px;
            margin: 0 auto;
            padding: 0;
            background-color: #f9f9f9;
        }
        .email-container {
            border: 1px solid #dddddd;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        .header {
            background: linear-gradient(135deg, #000000 0%, #242424 100%);
            padding: 25px;
            text-align: center;
        }
        .header img {
            max-width: 250px;
            height: auto;
        }
        .content {
            padding: 35px;
            background-color: #ffffff;
        }
        h2 {
            color: #2b2d42;
            border-bottom: 2px solid #3498db;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        .registration-info {
            background-color: #f8f9fa;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            padding: 25px;
            margin: 25px 0;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        }
        .registration-info h3 {
            color: #3498db;
            margin-top: 0;
            border-bottom: 1px solid #dee2e6;
            padding-bottom: 10px;
        }
        .registration-info table {
            width: 100%;
            border-collapse: collapse;
        }
        .registration-info td {
            padding: 12px 0;
            border-bottom: 1px solid #eeeeee;
        }
        .registration-info td:first-child {
            font-weight: bold;
            width: 40%;
            color: #555;
        }
        .footer {
            background: linear-gradient(135deg, #f0f0f0 0%, #e6e6e6 100%);
            padding: 20px;
            text-align: center;
            font-size: 13px;
            color: #666666;
            border-top: 3px solid #3498db;
        }
        .social-links {
            margin-top: 15px;
        }
        .social-links a {
            display: inline-block;
            margin: 0 10px;
            color: #3498db;
            text-decoration: none;
            font-weight: bold;
            transition: color 0.3s;
        }
        .social-links a:hover {
            color: #2980b9;
        }
        .button {
            display: inline-block;
            background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
            color: white !important;
            text-decoration: none;
            padding: 14px 28px;
            border-radius: 50px;
            margin-top: 20px;
            font-weight: bold;
            font-size: 16px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            transition: transform 0.3s, box-shadow 0.3s;
        }
        .button:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(0,0,0,0.15);
        }
        .payment-note {
            margin-top: 25px;
            padding: 18px;
            background-color: #fff8e6;
            border-left: 4px solid #f59e0b;
            color: #92400e;
            font-size: 15px;
            border-radius: 6px;
        }
        .coordinator-info {
            background-color: #e8f4fd;
            border: 1px solid #cce5ff;
            border-radius: 8px;
            padding: 20px;
            margin: 25px 0;
            color: #0c5460;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        }
        .coordinator-info h4 {
            margin-top: 0;
            color: #0c5460;
            border-bottom: 1px solid #bee5eb;
            padding-bottom: 10px;
            font-size: 18px;
        }
        .important-notice {
            background-color: #ffeeee;
            border: 1px solid #ff6b6b;
            padding: 15px;
            color: #cc0000;
            font-weight: bold;
            text-align: center;
            margin: 20px 0;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        }
        .payment-amount {
            font-size: 18px;
            font-weight: bold;
            color: #e63946;
            background-color: #f8f9fa;
            padding: 10px 15px;
            border-radius: 5px;
            display: inline-block;
            margin: 10px 0;
            border: 1px dashed #e63946;
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
            <p>Dear <strong>{$data['student_name']}</strong>,</p>
            <p>Thank you for registering for maJIStic 2025. Your registration has been successfully received and recorded in our system.</p>
            
            <div class="registration-info">
                <h3>Registration Details</h3>
                <table>
                    <tr>
                        <td>JIS ID</td>
                        <td><strong>{$data['jis_id']}</strong></td>
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
                        <td>Registration Fee</td>
                        <td><span class="payment-amount">Rs. 500</span></td>
                    </tr>
                </table>
            </div>
            
            <p>Your registration is now complete! To confirm your spot at maJIStic 2025, please make the payment to your department coordinator.</p>
            
            <div class="coordinator-info">
                {$coordinator_info}
            </div>
            
            <div class="payment-note">
                <p><strong>Note:</strong> Please pay the registration fee of <strong>Rs. 500</strong> to your department coordinator as soon as possible to secure your spot. Payment should be made in cash only.</p>
            </div>
            
            <p>If you have any questions or need further assistance, please don't hesitate to contact our support team.</p>
            
            <div class="important-notice">
                <p><strong>IMPORTANT:</strong> College ID is MANDATORY for check-in on event day. No entry without ID.</p>
            </div>
            
            <p>We look forward to seeing you at maJIStic 2025!</p>
            
            <p style='text-align: center; margin: 30px 0;'>
                <a href="$baseUrl/check_status.php" class="button">Check Registration Status</a>
            </p>
            
            <p>Warm Regards,<br><strong>maJIStic Team</strong></p>

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
 * Function to get coordinator information based on department
 * 
 * @param string $department The student's department
 * @return string HTML content for coordinator information
 */
function getCoordinatorInfo($department) {
    global $db;
    
    // Initialize default coordinator info with contact support message
    $default_info = "<h4>Department Coordinator Contact</h4>";
    $default_info .= "<p><strong>Payment Amount:</strong> <span style='color: #e63946; font-weight: bold;'>Rs. 500</span></p>";
    $default_info .= "<p><strong>Note:</strong> No specific coordinator found for your department. Please contact your respective department for ticket fee payment or contact maJIStic Support. or</p>";
    $default_info .= "<p><strong>Contact: </strong> Dr. Madhura Chakraborty (+91 7980979789)</p>";
    $default_info .= "<p><strong>Support Email:</strong> <a href='mailto:majistic@jiscollege.ac.in'>majistic@jiscollege.ac.in</a></p>";
    $default_info .= "<p><strong>WhatsApp Community:</strong> <a href='https://chat.whatsapp.com/JyDMUAA3zw9KfbPvWhXQ1l'>Join Here</a></p>";
    
    // Try to find a coordinator for the specific department
    if (!empty($department)) {
        try {
            // MySQL query to find coordinator by department
            $query = "SELECT * FROM department_coordinators WHERE department LIKE :department LIMIT 1";
            $stmt = $db->prepare($query);
            $stmt->execute([':department' => '%'.$department.'%']);
            $coordinator = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($coordinator) {
                $available_time = isset($coordinator['available_time']) ? 
                    $coordinator['available_time'] : 
                    '10:00 AM - 4:00 PM (Monday-Friday)';
                
                $coordinator_info = "<h4>Department Coordinator Contact</h4>";
                $coordinator_info .= "<p><strong>Payment Amount:</strong> <span style='color: #e63946; font-weight: bold;'>Rs. 500</span></p>";
                $coordinator_info .= "<p><strong>Name:</strong> " . htmlspecialchars($coordinator['name']) . "</p>";
                $coordinator_info .= "<p><strong>Department:</strong> " . htmlspecialchars($coordinator['department']) . "</p>";
                $coordinator_info .= "<p><strong>Contact:</strong> " . htmlspecialchars($coordinator['contact']) . "</p>";
                $coordinator_info .= "<p><strong>Available:</strong> " . htmlspecialchars($available_time) . "</p>";
                                
                return $coordinator_info;
            }
        } catch (PDOException $e) {
            error_log("Error fetching coordinator for email: " . $e->getMessage());
        }
    }
    
    // Return default coordinator info if no specific coordinator is found
    return $default_info;
}
?>
