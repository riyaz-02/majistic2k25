<?php
// Initialize session
session_start();

// Check if user is logged in as admin (you should implement proper authentication)
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php'); // Redirect to admin login page
    exit;
}

// Handle logout
if (isset($_GET['logout'])) {
    // Unset all session variables
    $_SESSION = array();
    
    // Destroy the session
    session_destroy();
    
    // Redirect to login page
    header('Location: login.php');
    exit;
}

// Path to the configuration files
$config_file = __DIR__ . '/../src/config/payment_config.php';
$email_config_file = __DIR__ . '/../src/config/email_payment_config.php';
$email_logo_config_file = __DIR__ . '/../src/config/email_logo_config.php';

// Process form submission
$message = '';
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the payment status from the form - Default to false if not set
    $payment_status = isset($_POST['payment_status']) ? true : false;
    $email_payment_status = isset($_POST['email_payment_status']) ? true : false;
    
    // Get the logo URL from the form - Use a default if empty
    $logo_url = isset($_POST['logo_url']) && !empty($_POST['logo_url']) 
        ? $_POST['logo_url'] 
        : 'https://cdn.emailacademy.com/user/fecdcd5176d5ee6a27e1962040645abfa28cce551d682738efd2fc3e158c65e3/majisticlogo2025_03_18_22_18_20.png';
    
    // Create configuration content
    $config_content = "<?php\n// Configuration file for payment status\ndefine('PAYMENT_ENABLED', " . ($payment_status ? 'true' : 'false') . ");\n?>";
    $email_config_content = "<?php\n// Configuration file for email payment link status\ndefine('EMAIL_PAYMENT_ENABLED', " . ($email_payment_status ? 'true' : 'false') . ");\n?>";
    $email_logo_config_content = "<?php\n// Configuration file for email logo URL\ndefine('EMAIL_LOGO_URL', '" . $logo_url . "');\n?>";
    
    // Write to config files
    $main_config_success = file_put_contents($config_file, $config_content);
    $email_config_success = file_put_contents($email_config_file, $email_config_content);
    $email_logo_config_success = file_put_contents($email_logo_config_file, $email_logo_config_content);
    
    if ($main_config_success && $email_config_success && $email_logo_config_success) {
        $message = "Settings updated successfully!";
        $success = true;
    } else {
        $message = "Failed to update one or more settings. Check file permissions.";
    }
}

// Get current payment statuses
$payment_enabled = false;
if (file_exists($config_file)) {
    include $config_file;
    $payment_enabled = defined('PAYMENT_ENABLED') ? PAYMENT_ENABLED : false;
}

$email_payment_enabled = false;
if (file_exists($email_config_file)) {
    include $email_config_file;
    $email_payment_enabled = defined('EMAIL_PAYMENT_ENABLED') ? EMAIL_PAYMENT_ENABLED : false;
}

// Get current logo URL
$logo_url = 'https://cdn.emailacademy.com/user/fecdcd5176d5ee6a27e1962040645abfa28cce551d682738efd2fc3e158c65e3/majisticlogo2025_03_18_22_18_20.png'; // Default URL
if (file_exists($email_logo_config_file)) {
    include $email_logo_config_file;
    $logo_url = defined('EMAIL_LOGO_URL') ? EMAIL_LOGO_URL : $logo_url;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>maJIStic Admin Management</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap">
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            background-color: #f5f5f5;
            color: #333;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 1000px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background-color: #2c3e50;
            color: white;
            padding: 20px;
            text-align: center;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        
        /* Navigation bar styles */
        .navbar {
            background-color: #2c3e50;
            color: white;
            padding: 10px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
            position: sticky;
            top: 0;
            z-index: 1000;
        }
        .navbar-brand {
            font-size: 24px;
            font-weight: bold;
            display: flex;
            align-items: center;
            color: white;
            text-decoration: none;
        }
        .navbar-brand i {
            margin-right: 10px;
        }
        .admin-info {
            display: flex;
            align-items: center;
        }
        .admin-avatar {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            background-color: #1abc9c;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 10px;
            font-weight: bold;
        }
        .admin-name {
            margin-right: 15px;
        }
        .logout-btn {
            background-color: #e74c3c;
            color: white;
            border: none;
            padding: 5px 15px;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s;
            text-decoration: none;
            font-size: 14px;
        }
        .logout-btn:hover {
            background-color: #c0392b;
        }
        
        .card {
            background-color: #fff;
            border-radius: 5px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 20px;
            margin-bottom: 20px;
        }
        .card-title {
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
            margin-top: 0;
            color: #2c3e50;
        }
        .toggle-container {
            display: flex;
            align-items: center;
            margin: 20px 0;
        }
        .toggle {
            position: relative;
            display: inline-block;
            width: 60px;
            height: 34px;
            margin-right: 10px;
        }
        .toggle input {
            opacity: 0;
            width: 0;
            height: 0;
        }
        .slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #ccc;
            transition: .4s;
            border-radius: 34px;
        }
        .slider:before {
            position: absolute;
            content: "";
            height: 26px;
            width: 26px;
            left: 4px;
            bottom: 4px;
            background-color: white;
            transition: .4s;
            border-radius: 50%;
        }
        input:checked + .slider {
            background-color: #2196F3;
        }
        input:focus + .slider {
            box-shadow: 0 0 1px #2196F3;
        }
        input:checked + .slider:before {
            transform: translateX(26px);
        }
        .status-label {
            font-size: 18px;
        }
        .btn {
            background-color: #3498db;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            transition: background-color 0.3s;
        }
        .btn:hover {
            background-color: #2980b9;
        }
        .alert {
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .info-text {
            background-color: #e7f3fe;
            border-left: 5px solid #2196F3;
            padding: 10px;
            margin: 15px 0;
        }
        .warning-text {
            background-color: #fff3cd;
            border-left: 5px solid #ffc107;
            padding: 10px;
            margin: 15px 0;
        }
        /* Adding styles for the logo URL field */
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        .form-control {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }
        .logo-preview {
            margin-top: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 10px;
            text-align: center;
            background-color: #f9f9f9;
        }
        .logo-preview img {
            max-width: 100%;
            max-height: 100px;
        }
    </style>
</head>
<body>
    <!-- Navigation Bar -->
    <div class="navbar">
        <a href="management.php" class="navbar-brand">
            <i class="fas fa-cog"></i> maJIStic Admin Panel
        </a>
        <div class="admin-info">
            <div class="admin-avatar">
                <i class="fas fa-user"></i>
            </div>
            <span class="admin-name">Admin</span>
            <a href="logout.php" class="logout-btn" onclick="return confirm('Are you sure you want to log out?')">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </div>
    </div>

    <div class="container">
        <div class="header">
            <h1><i class="fas fa-cog"></i> Site Management</h1>
        </div>
        
        <?php if ($message): ?>
            <div class="alert <?php echo $success ? 'alert-success' : 'alert-danger'; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>
        
        <div class="card">
            <h2 class="card-title"><i class="fas fa-money-bill-wave"></i> Payment System Control</h2>
            
            <div class="info-text">
                <p><strong>Current Status:</strong> Payment system is <strong><?php echo $payment_enabled ? 'ENABLED' : 'DISABLED'; ?></strong></p>
            </div>
            
            <div class="warning-text">
                <p><strong>Important:</strong> When disabled, users will be redirected to the confirmation page instead of the payment page after registration.</p>
            </div>
            
            <form method="POST" action="">
                <div class="toggle-container">
                    <label class="toggle">
                        <input type="checkbox" name="payment_status" value="1" <?php echo $payment_enabled ? 'checked' : ''; ?>>
                        <span class="slider"></span>
                    </label>
                    <span class="status-label">
                        <?php echo $payment_enabled ? 'Payment System Enabled' : 'Payment System Disabled'; ?>
                    </span>
                </div>

                <div style="margin-top: 30px;">
                    <h3><i class="fas fa-envelope"></i> Email Payment Link Control</h3>
                    
                    <div class="info-text">
                        <p><strong>Current Status:</strong> Email payment link is <strong><?php echo $email_payment_enabled ? 'ENABLED' : 'DISABLED'; ?></strong></p>
                    </div>
                    
                    <div class="warning-text">
                        <p><strong>Important:</strong> When disabled, the payment link in confirmation emails will redirect users to a notification page instead of the actual payment page.</p>
                    </div>

                    <div class="toggle-container">
                        <label class="toggle">
                            <input type="checkbox" name="email_payment_status" value="1" <?php echo $email_payment_enabled ? 'checked' : ''; ?>>
                            <span class="slider"></span>
                        </label>
                        <span class="status-label email-status-label">
                            <?php echo $email_payment_enabled ? 'Email Payment Link Enabled' : 'Email Payment Link Disabled'; ?>
                        </span>
                    </div>
                </div>

                <div style="margin-top: 30px;">
                    <h3><i class="fas fa-image"></i> Email Logo URL Control</h3>
                    
                    <div class="info-text">
                        <p><strong>Current Logo URL:</strong> The logo URL for all email templates can be changed below.</p>
                    </div>
                    
                    <div class="warning-text">
                        <p><strong>Important:</strong> The URL must be a direct link to an image file (preferably PNG or JPG). The image should be publicly accessible.</p>
                    </div>

                    <div class="form-group">
                        <label for="logo-url">Email Logo URL:</label>
                        <input type="url" id="logo-url" name="logo_url" class="form-control" value="<?php echo htmlspecialchars($logo_url); ?>" required>
                        
                        <div class="logo-preview">
                            <p>Logo Preview:</p>
                            <img id="logo-preview-img" src="<?php echo htmlspecialchars($logo_url); ?>" alt="Email Logo Preview" onerror="this.src='';this.alt='Image preview not available'">
                        </div>
                    </div>
                </div>
                
                <button type="submit" class="btn" style="margin-top: 20px;">Save Settings</button>
            </form>
            
            <div class="info-text" style="margin-top: 20px;">
                <h3>How this works:</h3>
                <p>- <strong>Payment System:</strong> When enabled, users will be redirected to the payment page after successful registration.</p>
                <p>- <strong>Email Payment Link:</strong> When enabled, the payment link in confirmation emails will direct users to the actual payment page. When disabled, users will be redirected to a notification page.</p>
                <p>- <strong>Email Logo URL:</strong> The URL of the logo image that appears in all email templates. Change this if the current URL expires or you want to update the logo.</p>
            </div>
        </div>
        
        <div class="card">
            <h2 class="card-title"><i class="fas fa-chart-bar"></i> Registration Statistics</h2>
            <p>Coming soon: Registration statistics and management tools.</p>
        </div>
    </div>
    
    <script>
        // Update the status labels when the toggles change
        document.querySelector('input[name="payment_status"]').addEventListener('change', function() {
            const statusLabel = document.querySelector('.status-label');
            if (this.checked) {
                statusLabel.textContent = 'Payment System Enabled';
            } else {
                statusLabel.textContent = 'Payment System Disabled';
            }
        });

        document.querySelector('input[name="email_payment_status"]').addEventListener('change', function() {
            const emailStatusLabel = document.querySelector('.email-status-label');
            if (this.checked) {
                emailStatusLabel.textContent = 'Email Payment Link Enabled';
            } else {
                emailStatusLabel.textContent = 'Email Payment Link Disabled';
            }
        });
        
        // Live preview of the logo image when URL changes
        document.getElementById('logo-url').addEventListener('input', function() {
            const previewImg = document.getElementById('logo-preview-img');
            previewImg.src = this.value;
        });
    </script>
</body>
</html>
