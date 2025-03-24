<?php
session_start();
require_once '../../includes/db_config.php';

// Check if user is logged in with admin privileges
if (!isset($_SESSION['admin_id']) || !isset($_SESSION['admin_role']) || $_SESSION['admin_role'] !== 'Manage Website') {
    header('Location: ../login.php');
    exit;
}

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_alumni_coordinator'])) {
    // Get the form data
    $name = isset($_POST['alumni_coordinator_name']) ? trim($_POST['alumni_coordinator_name']) : '';
    $contact = isset($_POST['alumni_coordinator_contact']) ? trim($_POST['alumni_coordinator_contact']) : '';
    $email = isset($_POST['alumni_coordinator_email']) ? trim($_POST['alumni_coordinator_email']) : '';
    $payment_qr = isset($_POST['alumni_payment_qr']) ? trim($_POST['alumni_payment_qr']) : '';
    $payment_instructions = isset($_POST['alumni_payment_instructions']) ? trim($_POST['alumni_payment_instructions']) : '';
    
    // Validate the input
    $errors = [];
    
    if (empty($name)) {
        $errors[] = "Coordinator name is required";
    }
    
    if (empty($contact)) {
        $errors[] = "Coordinator contact is required";
    }
    
    if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format";
    }
    
    if (empty($payment_qr)) {
        $errors[] = "Payment QR code URL is required";
    } 
    // Modified the URL validation to be more lenient - some valid URLs might not pass strict validation
    else if (!preg_match('/^https?:\/\//', $payment_qr)) {
        $errors[] = "QR code URL must start with http:// or https://";
    }
    
    // If no errors, update the configuration file
    if (empty($errors)) {
        // Create the configuration content
        $config_content = "<?php\n";
        $config_content .= "/**\n";
        $config_content .= " * Alumni Coordinator Configuration\n";
        $config_content .= " * \n";
        $config_content .= " * This file contains settings for the alumni coordinator\n";
        $config_content .= " * and payment QR code information\n";
        $config_content .= " */\n\n";
        $config_content .= "// Alumni Coordinator Details\n";
        $config_content .= "define('ALUMNI_COORDINATOR_NAME', '" . addslashes($name) . "');\n";
        $config_content .= "define('ALUMNI_COORDINATOR_CONTACT', '" . addslashes($contact) . "');\n";
        $config_content .= "define('ALUMNI_COORDINATOR_EMAIL', '" . addslashes($email) . "');\n\n";
        $config_content .= "// Payment QR Code Information\n";
        $config_content .= "define('ALUMNI_PAYMENT_QR', '" . addslashes($payment_qr) . "');\n";
        $config_content .= "define('ALUMNI_PAYMENT_INSTRUCTIONS', '" . addslashes($payment_instructions) . "');\n";
        $config_content .= "?>";
        
        // Write to the configuration file
        $config_file = __DIR__ . '/../../src/config/alumni_coordinator_config.php';
        
        // Make sure directory exists
        $dir = dirname($config_file);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        
        if (file_put_contents($config_file, $config_content)) {
            // Set success message
            $_SESSION['success_message'] = "Alumni coordinator settings updated successfully.";
        } else {
            // Set error message
            $_SESSION['error_message'] = "Failed to write to configuration file. Please check file permissions.";
        }
    } else {
        // Set error message
        $_SESSION['error_message'] = "Please correct the following errors: " . implode(", ", $errors);
    }
    
    // Redirect back to the manage page with the registration_control tab active
    header("Location: index.php?page=registration_control");
    exit;
}

// If not a POST request or update_alumni_coordinator not set, redirect back
header("Location: index.php?page=registration_control");
exit;
?>
