<?php
session_start();
require_once 'includes/db_config.php';

// Initialize variables
$error_message = '';
$student_data = null;
$registration_type = '';
$payment_status = false; // Default to false, will update if payment found
$coordinator_info = null;
$ticket_generated = false;
$checkin_day1 = false;
$checkin_day2 = false;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $jis_id = isset($_POST['jis_id']) ? trim($_POST['jis_id']) : '';
    $student_name = isset($_POST['student_name']) ? trim($_POST['student_name']) : '';
    
    if (!empty($jis_id) && !empty($student_name)) {
        try {
            // Check regular registrations table first
            $query = "SELECT * FROM registrations WHERE jis_id = :jis_id LIMIT 1";
            $stmt = $db->prepare($query);
            $stmt->execute([':jis_id' => $jis_id]);
            $registration = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($registration) {
                $student_data = $registration;
                $registration_type = 'student';
                
                // Check if name matches (case-insensitive)
                if (strtolower(trim($student_data['student_name'])) != strtolower(trim($student_name))) {
                    $message = 'The provided name does not match with the registration. Please check your information.';
                    $student_data = null;
                } else {
                    // Check payment status
                    $payment_status = ($student_data['payment_status'] == 'Paid');
                    
                    // Check ticket generation status - if column exists
                    $ticket_generated = isset($student_data['ticket_generated']) && $student_data['ticket_generated'] == 'Yes';
                    
                    // Check check-in status - if columns exist
                    $checkin_day1 = false;
                    $checkin_day2 = false;
                    
                    // Check for day 1
                    if (isset($student_data['checkin_1'])) {
                        $checkin_day1 = ($student_data['checkin_1'] === true || 
                                        $student_data['checkin_1'] === 'checkedin' || 
                                        $student_data['checkin_1'] == '1' ||
                                        $student_data['checkin_1'] == 'Yes');
                    }
                    
                    // Check for day 2
                    if (isset($student_data['checkin_2'])) {
                        $checkin_day2 = ($student_data['checkin_2'] === true || 
                                        $student_data['checkin_2'] === 'checkedin' || 
                                        $student_data['checkin_2'] == '1' ||
                                        $student_data['checkin_2'] == 'Yes');
                    }
                    
                    // If payment is not complete, get department coordinator info
                    if (!$payment_status && isset($student_data['department'])) {
                        $coord_query = "SELECT * FROM department_coordinators WHERE BINARY department = BINARY :department LIMIT 1";
                        $coord_stmt = $db->prepare($coord_query);
                        $coord_stmt->execute([':department' => $student_data['department']]);
                        $coordinator_info = $coord_stmt->fetch(PDO::FETCH_ASSOC);
                    }
                }
            } else {
                // If not found in registrations table, check alumni table
                $query = "SELECT * FROM alumni_registrations WHERE jis_id = :jis_id LIMIT 1";
                $stmt = $db->prepare($query);
                $stmt->execute([':jis_id' => $jis_id]);
                $registration = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($registration) {
                    $student_data = $registration;
                    $registration_type = 'alumni';
                    
                    // Check if name matches (case-insensitive)
                    if (strtolower(trim($student_data['alumni_name'])) != strtolower(trim($student_name))) {
                        $message = 'The provided name does not match with the registration. Please check your information.';
                        $student_data = null;
                    } else {
                        // Change the key name for consistency in display
                        $student_data['student_name'] = $student_data['alumni_name'];
                        
                        // Check payment status
                        $payment_status = ($student_data['payment_status'] == 'Paid');
                        
                        // Check ticket generation status
                        $ticket_generated = isset($student_data['ticket']) && $student_data['ticket'] == 'generated';
                        
                        // Check check-in status - if columns exist
                        $checkin_day1 = false;
                        $checkin_day2 = false;
                        
                        // Check for day 1
                        if (isset($student_data['checkin_1'])) {
                            $checkin_day1 = ($student_data['checkin_1'] === true || 
                                            $student_data['checkin_1'] === 'checkedin' || 
                                            $student_data['checkin_1'] == '1' ||
                                            $student_data['checkin_1'] == 'yes');
                        }
                        
                        // Check for day 2
                        if (isset($student_data['checkin_2'])) {
                            $checkin_day2 = ($student_data['checkin_2'] === true || 
                                            $student_data['checkin_2'] === 'checkedin' || 
                                            $student_data['checkin_2'] == '1' ||
                                            $student_data['checkin_2'] == 'yes');
                        }
                        
                        // If payment is not complete, get department coordinator info
                        if (!$payment_status && isset($student_data['department'])) {
                            $coord_query = "SELECT * FROM department_coordinators WHERE BINARY department = BINARY :department LIMIT 1";
                            $coord_stmt = $db->prepare($coord_query);
                            $coord_stmt->execute([':department' => $student_data['department']]);
                            $coordinator_info = $coord_stmt->fetch(PDO::FETCH_ASSOC);
                        }
                    }
                } else {
                    $message = 'No registration found for the provided JIS ID.';
                }
            }
        } catch (PDOException $e) {
            $message = 'An error occurred while retrieving your information. Please try again later.';
            error_log("Error in check_status.php: " . $e->getMessage());
        }
    } else {
        $message = 'Please enter your Name and JIS ID.';
    }
}

// Include alumni coordinator configuration
$alumni_coordinator_name = 'Dr. Proloy Ghosh'; // Default values
$alumni_coordinator_contact = '7980532913';
$alumni_coordinator_email = 'alumni.majistic@gmail.com';
$alumni_payment_qr = '';
$alumni_payment_instructions = 'Scan the QR code with any UPI app to pay the alumni registration fee (Rs. 1000). After payment, please send a screenshot to the coordinator via WhatsApp for verification.';

$alumni_config_path = realpath(__DIR__ . '/src/config/alumni_coordinator_config.php');
if ($alumni_config_path && file_exists($alumni_config_path)) {
    include_once $alumni_config_path;
    if (defined('ALUMNI_COORDINATOR_NAME')) {
        $alumni_coordinator_name = ALUMNI_COORDINATOR_NAME;
    }
    if (defined('ALUMNI_COORDINATOR_CONTACT')) {
        $alumni_coordinator_contact = ALUMNI_COORDINATOR_CONTACT;
    }
    if (defined('ALUMNI_COORDINATOR_EMAIL')) {
        $alumni_coordinator_email = ALUMNI_COORDINATOR_EMAIL;
    }
    if (defined('ALUMNI_PAYMENT_QR')) {
        $alumni_payment_qr = ALUMNI_PAYMENT_QR;
    }
    if (defined('ALUMNI_PAYMENT_INSTRUCTIONS')) {
        $alumni_payment_instructions = ALUMNI_PAYMENT_INSTRUCTIONS;
    }
}

// Calculate days remaining until the event
$event_date = new DateTime('2025-04-11');
$current_date = new DateTime();
$interval = $current_date->diff($event_date);
$days_remaining = $interval->format('%a');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Check Registration Status - maJIStic 2k25</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap">
    <?php include 'includes/links.php'; ?>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="css/check_status.css">
    <style>
        body {
            background-image: url('images/pageback.png');
            background-size: cover;
            background-attachment: fixed;
            min-height: 100vh;
            font-family: 'Roboto', sans-serif;
            color: #ffffff;
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="status-container">
        <div class="status-card">
            <div class="card-header">
                <img src="images/majisticlogo.png" alt="maJIStic Logo" class="logo">
                
                <!-- Remove ribbon and action buttons from event completion message -->
                <div class="event-completion-banner">
                    <h3 class="completion-title">maJIStic 2k25 Successfully Concluded!</h3>
                    <p>Thank you to everyone who participated and made this cultural fest a grand success.</p>
                </div>
            </div>
            
            <div class="card-body">
                <h2 class="page-title">Check Registration Status</h2>

                <?php if (!empty($message)): ?>
                    <div class="message-box error"><?php echo $message; ?></div>
                <?php endif; ?>
                
                <?php if (!$student_data): ?>
                    <div class="form-container">
                        <form method="post">
                            <div class="form-group">
                                <label for="student_name">Full Name</label>
                                <input type="text" id="student_name" name="student_name" placeholder="Enter your name as per registration" required>
                            </div>
                            <div class="form-group">
                                <label for="jis_id">JIS ID</label>
                                <input type="text" id="jis_id" name="jis_id" placeholder="JIS/20XX/0000" required>
                            </div>
                            <div style="text-align: center;">
                                <button type="submit" class="btn btn-primary" style="background: linear-gradient(135deg,rgb(146, 46, 204),rgb(43, 22, 136)); color: white;">
                                    <i class="fas fa-search"></i> Check Status
                                </button>
                            </div>
                        </form>
                    </div>
                    
                    <?php if ($_SERVER["REQUEST_METHOD"] == "POST" && !empty($message)): ?>
                        <div class="no-results-message">
                            <i class="fas fa-exclamation-circle"></i>
                            <h3>Registration Not Found</h3>
                            <p>We couldn't find any registration matching the details you provided. Please verify your JIS ID and name, then try again.</p>
                            <p>If you haven't registered yet, please visit the registration page to participate in maJIStic 2k25.</p>
                            <a href="registration_inhouse.php" class="btn btn-primary">
                                Register Now
                            </a>
                        </div>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="results-container">
                        <div class="status-layout">
                            <div class="details-column">
                                <div class="student-details">
                                    <h3>Your Registration Details</h3>
                                    <div class="detail-row">
                                        <span class="detail-label">Name:</span>
                                        <span class="detail-value"><?php echo htmlspecialchars($student_data['student_name']); ?></span>
                                    </div>
                                    <div class="detail-row">
                                        <span class="detail-label">JIS ID:</span>
                                        <span class="detail-value"><?php echo htmlspecialchars($jis_id); ?></span>
                                    </div>
                                    <?php if ($registration_type === 'student'): ?>
                                        <div class="detail-row">
                                            <span class="detail-label">Department:</span>
                                            <span class="detail-value"><?php echo htmlspecialchars($student_data['department']); ?></span>
                                        </div>
                                    <?php elseif ($registration_type === 'alumni'): ?>
                                        <div class="detail-row">
                                            <span class="detail-label">Department:</span>
                                            <span class="detail-value"><?php echo htmlspecialchars($student_data['department']); ?></span>
                                        </div>
                                        <div class="detail-row">
                                            <span class="detail-label">Passout Year:</span>
                                            <span class="detail-value"><?php echo htmlspecialchars($student_data['passout_year']); ?></span>
                                        </div>
                                    <?php endif; ?>
                                    <div class="detail-row">
                                        <span class="detail-label">Email:</span>
                                        <span class="detail-value"><?php echo htmlspecialchars($student_data['email']); ?></span>
                                    </div>
                                    <div class="detail-row">
                                        <span class="detail-label">Mobile:</span>
                                        <span class="detail-value"><?php echo htmlspecialchars($student_data['mobile']); ?></span>
                                    </div>
                                    <div class="detail-row">
                                        <span class="detail-label">Registration Date:</span>
                                        <span class="detail-value"><?php echo htmlspecialchars($student_data['registration_date']); ?></span>
                                    </div>
                                    <?php if ($registration_type === 'student' && !empty($student_data['inhouse_competition']) && $student_data['inhouse_competition'] === 'Yes'): ?>
                                        <div class="detail-row">
                                            <span class="detail-label">Inhouse Competition:</span>
                                            <span class="detail-value">Yes</span>
                                        </div>
                                        <?php if (!empty($student_data['competition_name'])): ?>
                                            <div class="detail-row">
                                                <span class="detail-label">Competition:</span>
                                                <span class="detail-value"><?php echo htmlspecialchars($student_data['competition_name']); ?></span>
                                            </div>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </div>

                                <?php if (!$payment_status): ?>
                                    <?php if ($registration_type === 'alumni'): ?>
                                    <!-- Alumni Payment Section with QR Code -->
                                    <div class="note" style="background-color: rgba(124, 58, 237, 0.15); border-left: 4px solid #7c3aed; padding: 20px; margin: 20px 0; text-align: left; border-radius: 8px;">
                                        <h4 style="color: #7c3aed; margin-top: 0; margin-bottom: 15px; font-size: 18px;">Alumni Payment</h4>
                                        
                                        <p style="margin-bottom: 15px;">Please complete your payment using the QR code below to confirm your participation in maJIStic 2k25.</p>
                                        
                                        <div style="margin: 20px auto; text-align: center;">
                                            <p style="font-weight: bold; font-size: 20px; color: #7c3aed; margin-bottom: 15px;">₹1000</p>
                                            
                                            <?php if (defined('ALUMNI_PAYMENT_QR') && !empty(ALUMNI_PAYMENT_QR)): ?>
                                            <div style="background-color: white; padding: 15px; border-radius: 8px; display: inline-block; margin-bottom: 15px; box-shadow: 0 4px 10px rgba(0,0,0,0.1);">
                                                <img src="<?php echo htmlspecialchars(ALUMNI_PAYMENT_QR); ?>" alt="Payment QR Code" style="max-width: 200px; height: auto;">
                                            </div>
                                            <!-- Removed QR code URL display -->
                                            <?php else: ?>
                                            <div style="background-color: rgba(231, 76, 60, 0.1); color: #e74c3c; padding: 10px; border-radius: 5px; font-weight: bold; margin-bottom: 15px;">
                                                QR code not available. Please contact the alumni coordinator directly.
                                            </div>
                                            <?php endif; ?>
                                            
                                            <div style="text-align: left; font-size: 14px; background-color: rgba(255, 255, 255, 0.1); padding: 15px; border-radius: 5px;">
                                                <p style="margin-top: 0;"><strong>Instructions:</strong></p>
                                                <p style="margin-bottom: 0;"><?php echo defined('ALUMNI_PAYMENT_INSTRUCTIONS') ? nl2br(htmlspecialchars(ALUMNI_PAYMENT_INSTRUCTIONS)) : 'Scan the QR code with any UPI app to pay the alumni registration fee (Rs. 1000). After payment, please send a screenshot to the coordinator via WhatsApp for verification.'; ?></p>
                                            </div>
                                            
                                            <!-- New message for alumni who have made payments but status not updated -->
                                            <div style="background-color: rgba(246, 229, 141, 0.2); border-left: 4px solid #f6e58d; padding: 15px; margin-top: 20px; border-radius: 5px; text-align: left;">
                                                <p style="color: #be9e44; font-weight: 600; margin-top: 0; margin-bottom: 10px; font-size: 16px;"><i class="fas fa-clock" style="margin-right: 8px;"></i> Payment Done But Status Not Updated?</p>
                                                <p style="margin-bottom: 0;">Hold on! Our team is diligently verifying all payments. Your status will be updated soon. In case of any issues, please contact the Alumni Coordinator directly using the contact details below.</p>
                                            </div>
                                        </div>
                                        
                                        <div style="margin-top: 20px; background-color: rgba(255, 255, 255, 0.1); padding: 15px; border-radius: 8px;">
                                            <h4 style="color: #7c3aed; margin-top: 0; margin-bottom: 10px; font-size: 16px;">Alumni Coordinator</h4>
                                            <p style="margin: 5px 0;"><strong>Name:</strong> <?php echo defined('ALUMNI_COORDINATOR_NAME') ? htmlspecialchars(ALUMNI_COORDINATOR_NAME) : 'Dr. Proloy Ghosh'; ?></p>
                                            <p style="margin: 5px 0;"><strong>Contact:</strong> <?php echo defined('ALUMNI_COORDINATOR_CONTACT') ? htmlspecialchars(ALUMNI_COORDINATOR_CONTACT) : '7980532913'; ?></p>
                                            <?php if (defined('ALUMNI_COORDINATOR_EMAIL') && !empty(ALUMNI_COORDINATOR_EMAIL)): ?>
                                            <p style="margin: 5px 0;"><strong>Email:</strong> <?php echo htmlspecialchars(ALUMNI_COORDINATOR_EMAIL); ?></p>
                                            <?php endif; ?>
                                            
                                            <div style="display: flex; flex-wrap: wrap; gap: 10px; margin-top: 10px;">
                                                <?php if (defined('ALUMNI_COORDINATOR_CONTACT') && !empty(ALUMNI_COORDINATOR_CONTACT)): ?>
                                                <a href="tel:+91<?php echo ALUMNI_COORDINATOR_CONTACT; ?>" style="display: inline-flex; align-items: center; background: rgba(52, 152, 219, 0.2); color: #3498db; padding: 8px 15px; border-radius: 5px; text-decoration: none; font-size: 14px; gap: 5px;">
                                                    <i class="fas fa-phone-alt"></i> Call
                                                </a>
                                                
                                                <a href="https://wa.me/91<?php echo ALUMNI_COORDINATOR_CONTACT; ?>?text=Hello,%20I%20have%20registered%20for%20maJIStic%202025%20as%20an%20alumnus%20(JIS%20ID:%20<?php echo urlencode($jis_id); ?>).%20I%20would%20like%20to%20complete%20my%20payment." target="_blank" style="display: inline-flex; align-items: center; background: rgba(37, 211, 102, 0.2); color: #25d366; padding: 8px 15px; border-radius: 5px; text-decoration: none; font-size: 14px; gap: 5px;">
                                                    <i class="fab fa-whatsapp"></i> WhatsApp
                                                </a>
                                                <?php endif; ?>
                                                
                                                <?php if (defined('ALUMNI_COORDINATOR_EMAIL') && !empty(ALUMNI_COORDINATOR_EMAIL)): ?>
                                                <a href="mailto:<?php echo ALUMNI_COORDINATOR_EMAIL; ?>?subject=Alumni%20Registration%20Payment%20for%20maJIStic%202025&body=Hello,%0A%0AI%20have%20registered%20for%20maJIStic%202025%20as%20an%20alumnus%20(JIS%20ID:%20<?php echo $jis_id; ?>).%0A%0AI%20would%20like%20to%20complete%20my%20payment.%0A%0AThank%20you." style="display: inline-flex; align-items: center; background: rgba(231, 76, 60, 0.2); color: #e74c3c; padding: 8px 15px; border-radius: 5px; text-decoration: none; font-size: 14px; gap: 5px;">
                                                    <i class="fas fa-envelope"></i> Email
                                                </a>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                    <?php else: ?>
                                    <!-- Regular student payment note -->
                                    <div class="note" style="background-color: rgba(241, 196, 15, 0.15); border-left: 4px solid #f1c40f; padding: 15px; margin: 20px 0; text-align: left; border-radius: 4px;">
                                        <p><strong>Important:</strong> Please complete your payment with your department coordinator to confirm your participation in maJIStic 2k25.</p>
                                        
                                        <?php if ($coordinator_info): ?>
                                        <div style="margin-top: 20px; background-color: rgba(255, 255, 255, 0.08); padding: 15px; border-radius: 8px;">
                                            <h4 style="color: #f1c40f; margin-top: 0; margin-bottom: 10px;">Your Department Coordinator</h4>
                                            <p><strong>Name:</strong> <?php echo htmlspecialchars($coordinator_info['name']); ?></p>
                                            <p><strong>Department:</strong> <?php echo htmlspecialchars($coordinator_info['department']); ?></p>
                                            <p><strong>Contact:</strong> <?php echo htmlspecialchars($coordinator_info['contact']); ?></p>
                                            <?php if (isset($coordinator_info['available_time'])): ?>
                                            <p><strong>Available:</strong> <?php echo htmlspecialchars($coordinator_info['available_time']); ?></p>
                                            <?php endif; ?>
                                        </div>
                                        <?php else: ?>
                                        <div style="margin-top: 20px; background-color: rgba(255, 255, 255, 0.08); padding: 15px; border-radius: 8px;">
                                            <h4 style="color: #f1c40f; margin-top: 0; margin-bottom: 10px;">No Department Coordinators Found</h4>
                                            <p>Contact your respective department for ticket payment or contact maJIStic Support.</p>
                                            
                                            <div style="margin-top: 15px; padding-top: 15px; border-top: 1px dashed rgba(255, 255, 255, 0.1);">
                                                <p><strong>For assistance with payment:</strong></p>
                                                <p>Email: <a href="mailto:majistic@jiscollege.ac.in" style="color: #3498db;">majistic@jiscollege.ac.in</a></p>
                                                <p>Visit the registration desk at the college during office hours for in-person assistance.</p>
                                            </div>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <div class="note" style="background-color: rgba(46, 204, 113, 0.15); border-left: 4px solid #2ecc71; padding: 15px; margin: 20px 0; text-align: left; border-radius: 4px;">
                                        <p><strong>Thank you!</strong> Your payment has been completed. 
                                        <?php if ($ticket_generated): ?>
                                            Your event ticket has been generated and sent to your email.
                                        <?php else: ?>
                                            Your event ticket will be generated soon.
                                        <?php endif; ?>
                                        </p>
                                    </div>
                                <?php endif; ?>

                                <div class="action-buttons">
                                    <a href="/majistic/index.php" class="btn btn-primary">
                                        <i class="fas fa-home"></i> Back to Home
                                    </a>
                                    <a href="/majistic/merchandise.php" class="btn btn-accent" style="background: linear-gradient(135deg, #2ecc71, #27ae60); color: white;">
                                        <i class="fas fa-tshirt"></i> Book Merchandise
                                    </a>
                                    <a href="/majistic/check_status.php" class="btn" style="background: linear-gradient(135deg,rgb(96, 93, 97),rgb(46, 34, 51)); color: white;">
                                        <i class="fas fa-search"></i> New Search
                                    </a>
                                </div>
                                
                                <!-- Support Team Section - Moved here from below -->
                                <div class="support-container">
                                    <div class="contact-section">
                                        <h2 class="contact-section-title">Need Help?</h2>
                                        
                                        <div class="contact-tabs">
                                            <button class="contact-tab active" data-target="tech-team">Tech Team</button>
                                            <button class="contact-tab" data-target="support-team">Support Team</button>
                                        </div>
                                        
                                        <div class="contact-panel active" id="tech-team">
                                            <p class="contact-description">
                                                In case of any technical issues, feel free to contact our Tech Team
                                            </p>
                                            <div class="contact-cards">
                                                <div class="contact-card">
                                                    <div class="icon">
                                                        <i class="fas fa-user-cog"></i>
                                                    </div>
                                                    <div class="info">
                                                        <h4>Priyanshu Nayan</h4>
                                                        <a href="tel:+917004706722">+91 7004706722</a>
                                                    </div>
                                                </div>
                                                <div class="contact-card">
                                                    <div class="icon">
                                                        <i class="fas fa-user-cog"></i>
                                                    </div>
                                                    <div class="info">
                                                        <h4>Sk Riyaz</h4>
                                                        <a href="tel:+917029621489">+91 7029621489</a>
                                                    </div>
                                                </div>
                                                <div class="contact-card">
                                                    <div class="icon">
                                                        <i class="fas fa-user-cog"></i>
                                                    </div>
                                                    <div class="info">
                                                        <h4>Ronit Pal</h4>
                                                        <a href="tel:+917501005155">+91 7501005155</a>
                                                    </div>
                                                </div>
                                                <div class="contact-card">
                                                    <div class="icon">
                                                        <i class="fas fa-user-cog"></i>
                                                    </div>
                                                    <div class="info">
                                                        <h4>Mohit Kumar</h4>
                                                        <a href="tel:+918016804158">+91 8016804158</a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="contact-panel" id="support-team">
                                            <p class="contact-description">
                                                For events related support, contact our Support Team
                                            </p>
                                            <div class="contact-cards">
                                                <div class="contact-card">
                                                    <div class="icon">
                                                        <i class="fas fa-headset"></i>
                                                    </div>
                                                    <div class="info">
                                                        <h4>Dr. Madhura Chakraborty</h4>
                                                        <a href="tel:+917980979789">+91 7980979789</a>
                                                    </div>
                                                </div>
                                                <div class="contact-card">
                                                    <div class="icon">
                                                        <i class="fas fa-headset"></i>
                                                    </div>
                                                    <div class="info">
                                                        <h4>Dr. Proloy Ghosh</h4>
                                                        <a href="tel:+917980532913">+91 7980532913</a>
                                                    </div>
                                                </div>
                                                <div class="contact-card">
                                                    <div class="icon">
                                                        <i class="fas fa-headset"></i>
                                                    </div>
                                                    <div class="info">
                                                        <h4>Anamitra Mondal</h4>
                                                        <a href="tel:+916289654490">+91 6289654490</a>
                                                    </div>
                                                </div>
                                                <div class="contact-card">
                                                    <div class="icon">
                                                        <i class="fas fa-envelope"></i>
                                                    </div>
                                                    <div class="info">
                                                        <h4>Email Support</h4>
                                                        <a href="mailto:majistic@jiscollege.ac.in">majistic@jiscollege.ac.in</a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- WhatsApp Community Section -->
                                <div class="whatsapp-community-section">
                                    <h4><i class="fab fa-whatsapp"></i> Join Our WhatsApp Community</h4>
                                    <p>Stay updated with all maJIStic 2k25 announcements, events, and connect with other participants!</p>
                                    <a href="https://chat.whatsapp.com/JyDMUAA3zw9KfbPvWhXQ1l" target="_blank" class="whatsapp-btn">
                                        <i class="fab fa-whatsapp"></i> Join Community
                                    </a>
                                </div>
                            </div>

                            <div class="timeline-column">
                                <div class="timeline-box">
                                    <h3>Your maJIStic Journey</h3>
                                    <div class="timeline-container">
                                        <div class="timeline">
                                            <div class="step completed" style="--index: 1;">
                                                <div class="step-icon">
                                                    <i class="icon fas fa-check"></i>
                                                </div>
                                                <div class="step-content">
                                                    <div class="step-title">Registration Complete</div>
                                                    <div class="step-description">You're officially registered for maJIStic 2k25! Your details have been saved in our system and you're on your way to an amazing experience.</div>
                                                </div>
                                            </div>
                                            
                                            <div class="step <?php echo $payment_status ? 'completed' : 'active'; ?>" style="--index: 2;">
                                                <div class="step-icon">
                                                    <i class="icon fas <?php echo $payment_status ? 'fa-check' : 'fa-credit-card'; ?>"></i>
                                                </div>
                                                <div class="step-content">
                                                    <div class="step-title">Payment Status</div>
                                                    <div class="step-description">
                                                        <?php if ($payment_status): ?>
                                                            Your payment has been received and processed successfully. You're all set for the event!
                                                        <?php else: ?>
                                                            Your payment is pending. Please complete your payment to confirm your participation.
                                                            <?php if ($coordinator_info): ?>
                                                                Contact your department coordinator for payment assistance.
                                                            <?php endif; ?>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <div class="step <?php echo $ticket_generated ? 'completed' : ($payment_status ? 'active' : ''); ?>" style="--index: 3;">
                                                <div class="step-icon">
                                                    <i class="icon fas <?php echo $ticket_generated ? 'fa-check' : 'fa-ticket-alt'; ?>"></i>
                                                </div>
                                                <div class="step-content">
                                                    <div class="step-title">Ticket Generation</div>
                                                    <div class="step-description">
                                                        <?php if ($ticket_generated): ?>
                                                            Your event ticket has been generated and emailed to your registered email address. Please keep it handy during the event. In case of any issues, contact our support team.
                                                        <?php elseif ($payment_status): ?>
                                                            Your payment has been confirmed. Your ticket will be generated 3 days before the event and emailed to you. Please keep checking your inbox and spam folders.
                                                        <?php else: ?>
                                                            Your ticket will be generated after payment confirmation and sent to your email 3 days before the event.
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <div class="step <?php echo $checkin_day1 ? 'completed' : ($ticket_generated ? 'active' : ''); ?>" style="--index: 4;">
                                                <div class="step-icon">
                                                    <i class="icon fas <?php echo $checkin_day1 ? 'fa-check' : 'fa-calendar-check'; ?>"></i>
                                                </div>
                                                <div class="step-content">
                                                    <div class="step-title">Day 1 Check-in</div>
                                                    <div class="step-description">
                                                        <?php if ($checkin_day1): ?>
                                                            You've successfully checked in for Day 1 of maJIStic 2k25. The excitement has begun! Get ready for a day full of innovation, creativity, and incredible performances!
                                                        <?php elseif ($ticket_generated): ?>
                                                            Get ready for an electrifying Day 1! Bring your ticket to check in at the registration desk and prepare to be amazed by spectacular performances.! 
                                                        <?php else: ?>
                                                            Day 1 promises to be an unforgettable experience with opening ceremonies, and thrilling events. Don't miss out!
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <div class="step <?php echo $checkin_day2 ? 'completed' : ($checkin_day1 ? 'active' : ''); ?>" style="--index: 5;">
                                                <div class="step-icon">
                                                    <i class="icon fas <?php echo $checkin_day2 ? 'fa-check' : 'fa-calendar-check'; ?>"></i>
                                                </div>
                                                <div class="step-content">
                                                    <div class="step-title">Day 2 Check-in</div>
                                                    <div class="step-description">
                                                        <?php if ($checkin_day2): ?>
                                                            You've successfully checked in for Day 2 of maJIStic 2k25! The grand day is here - prepare for mind-blowing cultural extravaganzas, and celebration of the evening!
                                                        <?php elseif ($checkin_day1): ?>
                                                            Day 2 is where the magic culminates! Don't forget to check in again for the grand day featuring cultural showcases, and the grand proshows!
                                                        <?php else: ?>
                                                            The grand maJIStic Day 2 will feature electrifying proshows, cultural performances, and unforgettable moments. Be part of this celebration of the evening!
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Event Day Information
                                <div class="event-message">
                                    <div class="event-day-message">
                                        <h4 style="margin-top: 0; margin-bottom: 15px; font-size: 20px;">Day 1: April 11, 2025</h4>
                                        <p style="margin: 0;">Opening ceremony, cultural perfomences and electryfing enjoyments</p>
                                    </div>
                                    <div class="event-day-message">
                                        <h4 style="margin-top: 0; margin-bottom: 15px; font-size: 20px;">Day 2: April 12, 2025</h4>
                                        <p style="margin: 0;">Cultural events, grand proshows, evening enjoyment and closing ceremony</p>
                                    </div>
                                </div> -->
                            </div>
                        </div>
                        
                        <!-- Removed support container from here -->
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <?php include 'includes/footer.php'; ?>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Add animation to form
            const formContainer = document.querySelector('.form-container');
            if (formContainer) {
                formContainer.style.opacity = '0';
                formContainer.style.transform = 'translateY(20px)';
                
                setTimeout(() => {
                    formContainer.style.transition = 'opacity 0.8s ease, transform 0.8s ease';
                    formContainer.style.opacity = '1';
                    formContainer.style.transform = 'translateY(0)';
                }, 100);
            }
            
            // JIS ID formatting code removed
            
            // Removed countdown timer code
            
            // Highlight active step with pulsing effect
            const activeStep = document.querySelector('.step.active');
            if (activeStep) {
                setInterval(() => {
                    activeStep.querySelector('.step-icon').style.boxShadow = '0 0 30px rgba(52, 152, 219, 0.9)';
                    setTimeout(() => {
                        activeStep.querySelector('.step-icon').style.boxShadow = '0 0 15px rgba(52, 152, 219, 0.7)';
                    }, 1000);
                }, 2000);
            }
            
            // Apply parallax effect to header
            window.addEventListener('scroll', function() {
                const header = document.querySelector('.card-header');
                const scrollPosition = window.scrollY;
                
                if (header) {
                    header.style.backgroundPosition = `0% ${scrollPosition * 0.05}%`;
                }
            });
            
            // Support team tabs
            const contactTabs = document.querySelectorAll('.contact-tab');
            const contactPanels = document.querySelectorAll('.contact-panel');
            
            contactTabs.forEach(tab => {
                tab.addEventListener('click', function() {
                    const target = this.getAttribute('data-target');
                    
                    // Remove active class from all tabs and panels
                    contactTabs.forEach(t => t.classList.remove('active'));
                    contactPanels.forEach(p => p.classList.remove('active'));
                    
                    // Add active class to clicked tab and its panel
                    this.classList.add('active');
                    const panel = document.getElementById(target);
                    if(panel) {
                        panel.classList.add('active');
                    }
                });
            });
        });
    </script>
    <style>
        .whatsapp-community-section {
            background: linear-gradient(145deg, rgba(37, 211, 102, 0.1), rgba(18, 140, 126, 0.15));
            border-radius: 12px;
            padding: 20px;
            margin-top: 30px;
            text-align: center;
            border: 1px solid rgba(37, 211, 102, 0.2);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .whatsapp-community-section h4 {
            color: #25d366;
            margin-bottom: 15px;
            font-size: 1.2rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .whatsapp-community-section p {
            margin-bottom: 20px;
            font-size: 0.95rem;
            color: #e0e0e0;
        }

        .whatsapp-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background-color: #25d366;
            color: white;
            padding: 10px 20px;
            border-radius: 50px;
            font-weight: 500;
            transition: all 0.3s ease;
            text-decoration: none;
            gap: 8px;
            box-shadow: 0 4px 15px rgba(37, 211, 102, 0.3);
        }

        .whatsapp-btn:hover {
            background-color: #128c7e;
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(37, 211, 102, 0.4);
            text-decoration: none;
            color: white;
        }

        .whatsapp-btn i {
            font-size: 1.2rem;
        }
        
        /* Add styles for event completion banner */
        .event-completion-banner {
            max-width: 800px;
            margin: 20px auto 0;
            padding: 25px;
            background: linear-gradient(135deg, rgba(155, 89, 182, 0.8), rgba(41, 128, 185, 0.8));
            border-radius: 16px;
            box-shadow: 0 15px 25px rgba(0, 0, 0, 0.3);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            position: relative;
            z-index: 1;
            width: 100%;
            text-align: center;
            overflow: hidden;
        }
        
        .event-completion-banner::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('images/pattern.png');
            opacity: 0.1;
            z-index: 0;
            animation: patternMove 40s linear infinite;
        }
        
        @keyframes patternMove {
            0% { transform: translateX(0) translateY(0); }
            50% { transform: translateX(-10px) translateY(-10px); }
            100% { transform: translateX(0) translateY(0); }
        }
        
        .ribbon {
            position: absolute;
            top: 20px;
            right: -30px;
            transform: rotate(45deg);
            background: rgba(231, 76, 60, 0.9);
            padding: 5px 40px;
            font-size: 14px;
            font-weight: 600;
            color: white;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.3);
            text-transform: uppercase;
            letter-spacing: 1px;
            z-index: 2;
        }
        
        .completion-title {
            color: white;
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 15px;
            position: relative;
            z-index: 1;
            text-shadow: 0 2px 5px rgba(0, 0, 0, 0.3);
        }
        
        .event-completion-banner p {
            color: rgba(255, 255, 255, 0.9);
            font-size: 16px;
            line-height: 1.6;
            margin-bottom: 20px;
            position: relative;
            z-index: 1;
        }
        
        .completion-actions {
            display: flex;
            justify-content: center;
            gap: 15px;
            position: relative;
            z-index: 1;
            flex-wrap: wrap;
        }
        
        .completion-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 10px 20px;
            border-radius: 50px;
            font-weight: 600;
            color: white;
            text-decoration: none;
            transition: all 0.3s ease;
            border: 1px solid rgba(255, 255, 255, 0.2);
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
        }
        
        .completion-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.3);
            color: white;
            text-decoration: none;
        }
        
        .completion-btn.photos {
            background: linear-gradient(135deg, rgba(52, 152, 219, 0.7), rgba(41, 128, 185, 0.7));
        }
        
        .completion-btn.videos {
            background: linear-gradient(135deg, rgba(231, 76, 60, 0.7), rgba(192, 57, 43, 0.7));
        }
        
        @media (max-width: 768px) {
            .completion-title {
                font-size: 20px;
            }
            
            .event-completion-banner p {
                font-size: 14px;
            }
            
            .ribbon {
                font-size: 12px;
                padding: 4px 30px;
            }
        }
        
        @media (max-width: 480px) {
            .completion-title {
                font-size: 18px;
            }
            
            .event-completion-banner {
                padding: 20px 15px;
            }
            
            .completion-actions {
                flex-direction: column;
                gap: 10px;
            }
            
            .completion-btn {
                width: 80%;
                margin: 0 auto;
            }
        }
    </style>
</body>
</html>
