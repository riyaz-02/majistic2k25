<?php
include __DIR__ . '/../../includes/db_config.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/../../vendor/autoload.php'; // Include the PHPMailer autoload file

// Include the registration mailers with error handling
$studentMailerAvailable = false;
$alumniMailerAvailable = false;

if (file_exists(__DIR__ . '/../mail/registration_mailer.php')) {
    try {
        require_once __DIR__ . '/../mail/registration_mailer.php';
        $studentMailerAvailable = function_exists('sendRegistrationConfirmationEmail');
    } catch (Exception $e) {
        error_log("Error loading student registration mailer: " . $e->getMessage());
    }
}

if (file_exists(__DIR__ . '/../mail/alumni_mailer.php')) {
    try {
        require_once __DIR__ . '/../mail/alumni_mailer.php';
        $alumniMailerAvailable = function_exists('sendAlumniRegistrationEmail');
    } catch (Exception $e) {
        error_log("Error loading alumni registration mailer: " . $e->getMessage());
    }
}

$message = ""; // Variable to store messages
$registration_success = false; // Variable to track registration success
$email_option = false; // Variable to track if user wants to receive email

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST" && !isset($_POST['payment_check'])) {
    $student_name = isset($_POST['student_name']) ? $_POST['student_name'] : '';
    $jis_id = isset($_POST['jis_id']) ? $_POST['jis_id'] : '';
    $mobile = isset($_POST['mobile']) ? $_POST['mobile'] : '';
    $email = isset($_POST['email']) ? $_POST['email'] : '';
    $gender = isset($_POST['gender']) ? $_POST['gender'] : '';
    $department = isset($_POST['department']) ? $_POST['department'] : '';
    
    // Check if this is an alumni registration
    $is_alumni = isset($_POST['registration_type']) && $_POST['registration_type'] == 'alumni';
    
    if ($is_alumni) {
        // Additional alumni fields
        $passout_year = isset($_POST['passout_year']) ? $_POST['passout_year'] : '';
        $current_organization = isset($_POST['current_organization']) ? $_POST['current_organization'] : '';
        
        // Check if the JIS ID already exists in alumni registrations
        $existing_alumni = $alumni_registrations->findOne(['jis_id' => $jis_id]);
        
        if ($existing_alumni) {
            // JIS ID exists - display message that they're already registered
            $student_name = $existing_alumni['alumni_name'];
            $email = $existing_alumni['email'];
            $mobile = $existing_alumni['mobile'];
            $department = $existing_alumni['department'];
            $passout_year = $existing_alumni['passout_year'];
            
            $message = "Dear $student_name, you have already registered for maJIStic 2k25 on " . $existing_alumni['registration_date'] . ". Please contact the alumni coordinator for details.";
            $registration_success = true;
        } else {
            // If JIS ID is new, check if email or mobile already exists in alumni registrations
            $existing_email = $alumni_registrations->findOne(['email' => $email]);
            $existing_mobile = $alumni_registrations->findOne(['mobile' => $mobile]);
            
            if ($existing_email) {
                $message = "Registration failed! This email address is already registered for the event.";
            } elseif ($existing_mobile) {
                $message = "Registration failed! This mobile number is already registered for the event.";
            } else {
                // All checks passed, proceed with alumni registration
                $alumni_data = [
                    'alumni_name' => $student_name,
                    'gender' => $gender,
                    'jis_id' => $jis_id,
                    'mobile' => $mobile,
                    'email' => $email,
                    'department' => $department,
                    'passout_year' => $passout_year,
                    'current_organization' => $current_organization,
                    'registration_date' => date('Y-m-d H:i:s'),
                    'payment_status' => 'Not Paid'
                ];

                try {
                    $insert_result = $alumni_registrations->insertOne($alumni_data);
                    
                    if ($insert_result->getInsertedCount() > 0) {
                        $message = "Thank You for your Interest. Your registration is complete. Please pay at the registration desk on the event day.";
                        $registration_success = true;

                        // Send email using alumni mailer
                        if ($alumniMailerAvailable) {
                            // Set timezone to ensure consistent date format
                            date_default_timezone_set('Asia/Kolkata');
                            
                            // Prepare data for email
                            $emailData = [
                                'alumni_name' => $student_name,
                                'email' => $email,
                                'jis_id' => $jis_id,
                                'department' => $department,
                                'passout_year' => $passout_year,
                                'mobile' => $mobile,
                                'registration_date' => date('Y-m-d H:i:s'),
                                'current_organization' => $current_organization
                            ];
                            
                            try {
                                $emailSent = sendAlumniRegistrationEmail($emailData);
                                error_log("Alumni registration email to $email " . ($emailSent ? "sent successfully" : "failed to send"));
                            } catch (Exception $e) {
                                error_log("Error sending alumni registration email: " . $e->getMessage());
                            }
                        } else {
                            // Fallback to basic email if alumni mailer not available
                            $mail = new PHPMailer(true);
                            try {
                                // Server settings
                                $mail->isSMTP();
                                $mail->Host       = 'smtp.gmail.com';
                                $mail->SMTPAuth   = true;
                                $mail->Username   = 'majistic.alumni@gmail.com';
                                $mail->Password   = 'iakqdaxcbtmcfucr';  // Replace with actual password
                                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                                $mail->Port       = 587;

                                // Recipients
                                $mail->setFrom('majistic.alumni@gmail.com', 'maJIStic');
                                $mail->addAddress($email);

                                // Content
                                $mail->isHTML(true);
                                $mail->Subject = 'maJIStic 2k25 Alumni Registration Confirmation';
                                
                                // Use a simple email template as fallback for alumni
                                $mail->Body = '
                                    <html>
                                    <body>
                                        <h2>Alumni Registration Confirmation</h2>
                                        <p>Dear ' . $student_name . ',</p>
                                        <p>Thank you for registering for maJIStic 2k25 as an alumni. Your registration is confirmed.</p>
                                        <p>Please contact the alumni coordinator or pay at the registration desk on the event day.</p>
                                        <p>Regards,<br>maJIStic Alumni Team</p>
                                        <p style="background-color: #ffeeee; border: 1px solid #ff6b6b; padding: 10px; color: #cc0000; font-weight: bold; text-align: center; margin: 15px 0;">
                                            <strong>IMPORTANT:</strong> Please bring your Alumni ID or any Government ID for verification on the event day.
                                        </p>
                                    </body>
                                    </html>';
                                
                                $mail->send();
                            } catch (Exception $e) {
                                error_log("Alumni email could not be sent. Mailer Error: {$mail->ErrorInfo}");
                            }
                        }
                    } else {
                        $message = "Registration failed! Please try again.";
                    }
                } catch (Exception $e) {
                    $message = "Registration failed! Database error: " . $e->getMessage();
                    error_log("MongoDB insertion error: " . $e->getMessage());
                }
            }
        }
    } else {
        // Regular student registration
        // First check if the JIS ID already exists
        $existing_student = $registrations->findOne(['jis_id' => $jis_id]);
        
        if ($existing_student) {
            // JIS ID exists - show message they're already registered
            $student_name = $existing_student['student_name'];
            $email = $existing_student['email'];
            $mobile = $existing_student['mobile'];
            $department = $existing_student['department'];
            $inhouse_competition = $existing_student['inhouse_competition'] ?? '';
            $competition = $existing_student['competition_name'] ?? '';
            
            $message = "Dear $student_name, you have already registered for maJIStic 2k25 on " . $existing_student['registration_date'] . ". Please contact your department coordinator for details.";
            $registration_success = true;
        } else {
            // If JIS ID is new, check if email or mobile already exists
            $existing_email = $registrations->findOne(['email' => $email]);
            $existing_mobile = $registrations->findOne(['mobile' => $mobile]);
            
            if ($existing_email) {
                $message = "Registration failed! This email address is already registered for the event.";
            } elseif ($existing_mobile) {
                $message = "Registration failed! This mobile number is already registered for the event.";
            } else {
                // All checks passed, proceed with registration
                $inhouse_competition = isset($_POST['inhouse_competition']) ? $_POST['inhouse_competition'] : null;
                $competition = isset($_POST['competition']) ? $_POST['competition'] : null;
                
                $student_data = [
                    'student_name' => $student_name,
                    'gender' => $gender,
                    'jis_id' => $jis_id,
                    'mobile' => $mobile,
                    'email' => $email,
                    'department' => $department,
                    'inhouse_competition' => $inhouse_competition,
                    'competition_name' => $competition,
                    'registration_date' => date('Y-m-d H:i:s'),
                    'payment_status' => 'Not Paid'
                ];

                try {
                    $insert_result = $registrations->insertOne($student_data);
                    
                    if ($insert_result->getInsertedCount() > 0) {
                        $message = "Thank You for your Interest. Your registration is complete. Please pay at your department coordinator.";
                        $registration_success = true;

                        // Send email using our new professional template
                        if ($studentMailerAvailable) {
                            // Set timezone to ensure consistent date format
                            date_default_timezone_set('Asia/Kolkata');
                            
                            // Prepare data for email
                            $emailData = [
                                'student_name' => $student_name,
                                'email' => $email,
                                'jis_id' => $jis_id,
                                'department' => $department,
                                'mobile' => $mobile,
                                'registration_date' => date('Y-m-d H:i:s'),
                                'competition' => isset($competition) ? $competition : '',
                                'gender' => $gender
                            ];
                            
                            try {
                                $emailSent = sendRegistrationConfirmationEmail($emailData);
                                error_log("Registration email to $email " . ($emailSent ? "sent successfully" : "failed to send"));
                            } catch (Exception $e) {
                                error_log("Error sending registration email: " . $e->getMessage());
                            }
                        } else {
                            // Fallback to basic email if mailer module not available
                            $mail = new PHPMailer(true);
                            try {
                                // Server settings
                                $mail->isSMTP();
                                $mail->Host       = 'smtp.gmail.com';
                                $mail->SMTPAuth   = true;
                                $mail->Username   = 'majistic.reg@gmail.com';
                                $mail->Password   = 'uflsvdypbnnbnisn';
                                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                                $mail->Port       = 587;

                                // Recipients
                                $mail->setFrom('majistic.reg@gmail.com', 'maJIStic');
                                $mail->addAddress($email);

                                // Content
                                $mail->isHTML(true);
                                $mail->Subject = 'maJIStic 2k25 Registration Confirmation';
                                
                                // Use a simple email template as fallback
                                $mail->Body = '
                                    <html>
                                    <body>
                                        <h2>Registration Confirmation</h2>
                                        <p>Dear ' . $student_name . ',</p>
                                        <p>Thank you for registering for maJIStic 2k25. Your registration is confirmed.</p>
                                        <p>Please contact your department coordinator for payment.</p>
                                        <p>Regards,<br>maJIStic Team</p>
                                        <p style="background-color: #ffeeee; border: 1px solid #ff6b6b; padding: 10px; color: #cc0000; font-weight: bold; text-align: center; margin: 15px 0;">
                                            <strong>IMPORTANT:</strong> College ID is MANDATORY for check-in on event day. No entry without ID.
                                        </p>
                                    </body>
                                    </html>';
                                
                                $mail->send();
                            } catch (Exception $e) {
                                error_log("Email could not be sent. Mailer Error: {$mail->ErrorInfo}");
                            }
                        }
                    } else {
                        $message = "Registration failed! Please try again.";
                    }
                } catch (Exception $e) {
                    $message = "Registration failed! Database error: " . $e->getMessage();
                    error_log("MongoDB insertion error: " . $e->getMessage());
                }
            }
        }
    }
}
?>
