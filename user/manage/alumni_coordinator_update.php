<?php
session_start();
require_once '../../includes/db_config.php';

// Check if user is logged in with admin privileges
if (!isset($_SESSION['admin_id']) || !isset($_SESSION['admin_role']) || 
    ($_SESSION['admin_role'] !== 'Manage Website' && $_SESSION['admin_role'] !== 'Super Admin')) {
    header('Location: ../login.php');
    exit;
}

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_alumni_coordinator'])) {
    // Get the form data
    $name = isset($_POST['coordinator_name']) ? trim($_POST['coordinator_name']) : '';
    $contact = isset($_POST['coordinator_contact']) ? trim($_POST['coordinator_contact']) : '';
    $email = isset($_POST['coordinator_email']) ? trim($_POST['coordinator_email']) : '';
    $payment_qr_url = isset($_POST['payment_qr']) ? trim($_POST['payment_qr']) : '';
    $payment_instructions = isset($_POST['payment_instructions']) ? trim($_POST['payment_instructions']) : '';
    
    // Validate the data
    $errors = [];
    
    if (empty($name)) {
        $errors[] = "Coordinator name is required";
    }
    
    if (empty($contact) || !preg_match('/^\d{10}$/', $contact)) {
        $errors[] = "Valid 10-digit contact number is required";
    }
    
    if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format";
    }
    
    // Validate QR code URL if provided
    if (!empty($payment_qr_url) && !filter_var($payment_qr_url, FILTER_VALIDATE_URL)) {
        $errors[] = "Invalid QR code URL format. Please provide a valid URL";
    }
    
    // If no errors, update the config file
    if (empty($errors)) {
        // Path to the config file
        $config_file = '../../src/config/alumni_coordinator_config.php';
        
        // Create config directory if it doesn't exist
        $config_dir = dirname($config_file);
        if (!is_dir($config_dir)) {
            mkdir($config_dir, 0755, true);
        }
        
        // Save configuration to MySQL
        try {
            // Check if config already exists
            $query = "SELECT COUNT(*) FROM app_configs WHERE config_key = 'alumni_coordinator'";
            $stmt = $db->prepare($query);
            $stmt->execute();
            $config_exists = ($stmt->fetchColumn() > 0);
            
            $config_data = [
                'coordinator_name' => $name,
                'coordinator_contact' => $contact,
                'coordinator_email' => $email,
                'payment_instructions' => $payment_instructions,
                'payment_qr' => $payment_qr_url
            ];
            
            if ($config_exists) {
                // Update existing config
                $query = "UPDATE app_configs SET config_value = :config_value, updated_at = NOW() WHERE config_key = 'alumni_coordinator'";
                $stmt = $db->prepare($query);
                $stmt->execute([
                    ':config_value' => json_encode($config_data)
                ]);
            } else {
                // Create new config
                $query = "INSERT INTO app_configs (config_key, config_value, created_at, updated_at) VALUES ('alumni_coordinator', :config_value, NOW(), NOW())";
                $stmt = $db->prepare($query);
                $stmt->execute([
                    ':config_value' => json_encode($config_data)
                ]);
            }
            
            // Generate PHP config file
            $config_content = "<?php\n";
            $config_content .= "// Alumni Coordinator Configuration - Auto-generated\n";
            $config_content .= "define('ALUMNI_COORDINATOR_NAME', " . var_export($name, true) . ");\n";
            $config_content .= "define('ALUMNI_COORDINATOR_CONTACT', " . var_export($contact, true) . ");\n";
            
            if (!empty($email)) {
                $config_content .= "define('ALUMNI_COORDINATOR_EMAIL', " . var_export($email, true) . ");\n";
            }
            
            if (!empty($payment_qr_url)) {
                $config_content .= "define('ALUMNI_PAYMENT_QR', " . var_export($payment_qr_url, true) . ");\n";
            }
            
            if (!empty($payment_instructions)) {
                $config_content .= "define('ALUMNI_PAYMENT_INSTRUCTIONS', " . var_export($payment_instructions, true) . ");\n";
            }
            
            // Write to file
            file_put_contents($config_file, $config_content);
            
            $_SESSION['success_message'] = "Alumni coordinator settings updated successfully!";
        } catch (PDOException $e) {
            $_SESSION['error_message'] = "Database error: " . $e->getMessage();
        } catch (Exception $e) {
            $_SESSION['error_message'] = "Error updating configuration: " . $e->getMessage();
        }
    } else {
        $_SESSION['error_message'] = implode("<br>", $errors);
    }
}

// Redirect back to the alumni settings page
header("Location: index.php?page=alumni_settings");
exit;
?>
