<?php
session_start();
require_once '../../includes/db_config.php';

// Check if user is logged in with admin privileges
if (!isset($_SESSION['admin_id'])) {
    header('Location: ../login.php');
    exit;
}

// Set default return URL
$return_url = isset($_POST['return_url']) ? $_POST['return_url'] : 'index.php?page=email_resend';

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['resend_email'])) {
    $reg_id = isset($_POST['reg_id']) ? (int)$_POST['reg_id'] : 0;
    $reg_type = isset($_POST['reg_type']) ? $_POST['reg_type'] : '';
    $email_type = isset($_POST['email_type']) ? $_POST['email_type'] : 'registration';
    
    // Validate inputs
    if ($reg_id <= 0 || empty($reg_type)) {
        $_SESSION['email_error'] = 'Invalid registration details provided.';
        header("Location: $return_url");
        exit;
    }
    
    try {
        if ($email_type === 'registration') {
            // Process registration email resend
            if ($reg_type === 'student') {
                // Include student mailer
                if (file_exists(__DIR__ . '/../../src/mail/registration_mailer.php')) {
                    require_once __DIR__ . '/../../src/mail/registration_mailer.php';
                    $mailerLoaded = function_exists('sendRegistrationConfirmationEmail');
                }
                
                if (!$mailerLoaded) {
                    $_SESSION['email_error'] = 'Student mailer module is not available.';
                    header("Location: $return_url");
                    exit;
                }
                
                // Fetch registration data from database
                $query = "SELECT * FROM registrations WHERE id = :id LIMIT 1";
                $stmt = $db->prepare($query);
                $stmt->execute([':id' => $reg_id]);
                $registration = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if (!$registration) {
                    $_SESSION['email_error'] = 'Student registration not found.';
                    header("Location: $return_url");
                    exit;
                }
                
                // Prepare data for email
                $emailData = [
                    'student_name' => $registration['student_name'],
                    'email' => $registration['email'],
                    'jis_id' => $registration['jis_id'],
                    'department' => $registration['department'],
                    'mobile' => $registration['mobile'],
                    'registration_date' => $registration['registration_date'],
                    'competition' => isset($registration['competition_name']) ? $registration['competition_name'] : '',
                    'gender' => $registration['gender']
                ];
                
                // Send the email
                $emailSent = sendRegistrationConfirmationEmail($emailData);
                
                if ($emailSent) {
                    // Log the email resend action to server log
                    $admin_id = $_SESSION['admin_id'];
                    $admin_name = $_SESSION['admin_name'] ?? 'Unknown';
                    error_log("Admin $admin_name (ID: $admin_id) resent student registration email to {$registration['email']} (JIS ID: {$registration['jis_id']})");
                    
                    $_SESSION['email_success'] = "Registration email successfully resent to {$registration['student_name']} ({$registration['email']}).";
                } else {
                    $_SESSION['email_error'] = "Failed to resend registration email. Please try again.";
                }
            } 
            else if ($reg_type === 'alumni') {
                // Include alumni mailer
                if (file_exists(__DIR__ . '/../../src/mail/alumni_mailer.php')) {
                    require_once __DIR__ . '/../../src/mail/alumni_mailer.php';
                    $mailerLoaded = function_exists('sendAlumniRegistrationEmail');
                }
                
                if (!$mailerLoaded) {
                    $_SESSION['email_error'] = 'Alumni mailer module is not available.';
                    header("Location: $return_url");
                    exit;
                }
                
                // Fetch alumni registration data from database
                $query = "SELECT * FROM alumni_registrations WHERE id = :id LIMIT 1";
                $stmt = $db->prepare($query);
                $stmt->execute([':id' => $reg_id]);
                $registration = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if (!$registration) {
                    $_SESSION['email_error'] = 'Alumni registration not found.';
                    header("Location: $return_url");
                    exit;
                }
                
                // Prepare data for email
                $emailData = [
                    'alumni_name' => $registration['alumni_name'],
                    'email' => $registration['email'],
                    'jis_id' => $registration['jis_id'],
                    'department' => $registration['department'],
                    'passout_year' => $registration['passout_year'],
                    'mobile' => $registration['mobile'],
                    'registration_date' => $registration['registration_date'],
                    'current_organization' => $registration['current_organization']
                ];
                
                // Send the email
                $emailSent = sendAlumniRegistrationEmail($emailData);
                
                if ($emailSent) {
                    // Log the email resend action to server log
                    $admin_id = $_SESSION['admin_id'];
                    $admin_name = $_SESSION['admin_name'] ?? 'Unknown';
                    error_log("Admin $admin_name (ID: $admin_id) resent alumni registration email to {$registration['email']} (JIS ID: {$registration['jis_id']})");
                    
                    $_SESSION['email_success'] = "Alumni registration email successfully resent to {$registration['alumni_name']} ({$registration['email']}).";
                } else {
                    $_SESSION['email_error'] = "Failed to resend alumni email. Please try again.";
                }
            } 
            else {
                $_SESSION['email_error'] = 'Invalid registration type.';
            }
        }
        else if ($email_type === 'payment') {
            // Process payment email resend
            // Include payment email sender
            if (file_exists(__DIR__ . '/../../user/adm/email_sender.php')) {
                require_once __DIR__ . '/../../user/adm/email_sender.php';
                $mailerLoaded = function_exists('sendPaymentConfirmationEmail');
            }
            
            if (!$mailerLoaded) {
                $_SESSION['email_error'] = 'Payment mailer module is not available.';
                header("Location: $return_url");
                exit;
            }
            
            // Determine table based on registration type
            $table = $reg_type === 'student' ? 'registrations' : 'alumni_registrations';
            $name_field = $reg_type === 'student' ? 'student_name' : 'alumni_name';
            
            // Fetch registration data
            $query = "SELECT * FROM $table WHERE id = :id LIMIT 1";
            $stmt = $db->prepare($query);
            $stmt->execute([':id' => $reg_id]);
            $registration = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$registration) {
                $_SESSION['email_error'] = ucfirst($reg_type) . ' registration not found.';
                header("Location: $return_url");
                exit;
            }
            
            // Verify payment status
            if ($registration['payment_status'] !== 'Paid') {
                $_SESSION['email_error'] = 'Cannot send payment email for unpaid registration.';
                header("Location: $return_url");
                exit;
            }
            
            // Prepare data for payment email
            $emailData = [
                'email' => $registration['email'],
                'name' => $registration[$name_field],
                'jis_id' => $registration['jis_id'],
                'department' => $registration['department'],
                'receipt_number' => $registration['receipt_number'] ?? 'RECEIPT-' . time(),
                'payment_date' => $registration['payment_date'] ?? date('Y-m-d H:i:s'),
                'type' => $reg_type,
                'paid_amount' => $registration['paid_amount'] ?? ($reg_type === 'student' ? 500 : 1000)
            ];
            
            // Add type-specific data
            if ($reg_type === 'student') {
                $emailData['competition'] = $registration['competition_name'] ?? 'N/A';
            } else {
                $emailData['passout_year'] = $registration['passout_year'] ?? 'N/A';
            }
            
            // Send the payment email
            $emailSent = sendPaymentConfirmationEmail($emailData);
            
            if ($emailSent) {
                // Log the payment email resend action
                $admin_id = $_SESSION['admin_id'];
                $admin_name = $_SESSION['admin_name'] ?? 'Unknown';
                error_log("Admin $admin_name (ID: $admin_id) resent payment email to {$registration['email']} (JIS ID: {$registration['jis_id']})");
                
                $_SESSION['email_success'] = "Payment confirmation email successfully resent to {$emailData['name']} ({$registration['email']}).";
            } else {
                $_SESSION['email_error'] = "Failed to resend payment email. Please try again.";
            }
        }
        else {
            $_SESSION['email_error'] = 'Invalid email type.';
        }
    } catch (Exception $e) {
        error_log("Email resend error: " . $e->getMessage());
        $_SESSION['email_error'] = 'An error occurred: ' . $e->getMessage();
    }
} else {
    $_SESSION['email_error'] = 'Invalid request.';
}

// Redirect back to the email resend page
header("Location: $return_url");
exit;
?>
