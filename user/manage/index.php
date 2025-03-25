<?php
session_start();
require_once '../../includes/db_config.php';

// Debug what's in the session
error_log("SESSION redirect_url: " . (isset($_SESSION['redirect_url']) ? $_SESSION['redirect_url'] : 'not set'));

// Check for redirect after form processing - place this before ANY output
if (isset($_SESSION['redirect_url'])) {
    $redirect_url = $_SESSION['redirect_url'];
    unset($_SESSION['redirect_url']);
    
    // Log before redirecting
    error_log("Redirecting to: $redirect_url");
    
    header("Location: $redirect_url");
    exit;
}

// Check if user is logged in with admin privileges
if (!isset($_SESSION['admin_id']) || !isset($_SESSION['admin_role']) || $_SESSION['admin_role'] !== 'Manage Website') {
    // Fix the redirect path to avoid potential loops
    header('Location: ../login.php');
    exit;
}

// Get the current page from query parameter, default to dashboard
$page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';

// Process form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Registration Status Toggle
    if (isset($_POST['update_registration_status'])) {
        $registrationEnabled = isset($_POST['registration_enabled']) ? true : false;
        
        $configContent = "<?php\n";
        $configContent .= "/**\n";
        $configContent .= " * Registration Configuration\n";
        $configContent .= " * \n";
        $configContent .= " * Controls whether new registrations are accepted\n";
        $configContent .= " * When REGISTRATION_ENABLED is set to false, registration buttons will redirect to a message page\n";
        $configContent .= " */\n\n";
        $configContent .= "// Set to false to disable new registrations temporarily\n";
        $configContent .= "define('REGISTRATION_ENABLED', " . ($registrationEnabled ? 'true' : 'false') . ");\n";
        $configContent .= "?>";
        
        $configFile = realpath(__DIR__ . '/../../src/config/registration_config.php');
        
        if (!$configFile) {
            $configFile = '../../src/config/registration_config.php';
            $dir = dirname($configFile);
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
        }
        
        if (file_put_contents($configFile, $configContent)) {
            $success_message = "Registration status updated successfully!";
        } else {
            $error_message = "Failed to update registration status. Check file permissions.";
        }
    }
    
    // Email Configuration Update
    if (isset($_POST['update_email_config'])) {
        $logoUrl = trim($_POST['logo_url']);
        $baseUrl = trim($_POST['base_url']);
        
        $configContent = "<?php\n";
        $configContent .= "// Configuration file for email logo URL\n";
        $configContent .= "define('EMAIL_LOGO_URL', '$logoUrl');\n\n";
        $configContent .= "// Base URL for email links and buttons\n";
        $configContent .= "define('EMAIL_BASE_URL', '$baseUrl');\n";
        $configContent .= "?>";
        
        // Fix path to config file - use absolute path
        $configFile = realpath(__DIR__ . '/../../src/config/email_logo_config.php');
        
        // If the file doesn't exist, use the standard path
        if (!$configFile) {
            $configFile = '../../src/config/email_logo_config.php';
            
            // Check if directory exists, if not create it
            $dir = dirname($configFile);
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
        }
        
        if (file_put_contents($configFile, $configContent)) {
            $success_message = "Email configuration updated successfully!";
        } else {
            $error_message = "Failed to update email configuration. Check file permissions.";
        }
    }
    
    // Department Coordinator Management - Using MySQL
    if (isset($_POST['add_coordinator'])) {
        $department = trim($_POST['department']);
        $name = trim($_POST['coordinator_name']);
        $contact = trim($_POST['contact']);
        $available_time = trim($_POST['available_time']);
        
        if (empty($department) || empty($name) || empty($contact)) {
            $error_message = "All fields are required for adding a coordinator.";
        } else {
            try {
                $query = "INSERT INTO department_coordinators (department, name, contact, available_time, created_at) 
                          VALUES (:department, :name, :contact, :available_time, NOW())";
                          
                $stmt = $db->prepare($query);
                $stmt->execute([
                    ':department' => $department,
                    ':name' => $name,
                    ':contact' => $contact,
                    ':available_time' => $available_time
                ]);
                
                if ($stmt->rowCount() > 0) {
                    $success_message = "Coordinator added successfully!";
                } else {
                    $error_message = "Failed to add coordinator.";
                }
            } catch (PDOException $e) {
                $error_message = "Database error: " . $e->getMessage();
            }
        }
    }
    
    if (isset($_POST['update_coordinator'])) {
        $id = $_POST['coordinator_id'];
        $department = trim($_POST['department']);
        $name = trim($_POST['coordinator_name']);
        $contact = trim($_POST['contact']);
        $available_time = trim($_POST['available_time']);
        
        if (empty($department) || empty($name) || empty($contact)) {
            $error_message = "All fields are required for updating a coordinator.";
        } else {
            try {
                $query = "UPDATE department_coordinators 
                          SET department = :department, name = :name, contact = :contact, 
                          available_time = :available_time, updated_at = NOW() 
                          WHERE id = :id";
                          
                $stmt = $db->prepare($query);
                $stmt->execute([
                    ':department' => $department,
                    ':name' => $name,
                    ':contact' => $contact,
                    ':available_time' => $available_time,
                    ':id' => $id
                ]);
                
                if ($stmt->rowCount() > 0) {
                    $success_message = "Coordinator updated successfully!";
                } else {
                    $error_message = "No changes were made or coordinator not found.";
                }
            } catch (PDOException $e) {
                $error_message = "Database error: " . $e->getMessage();
            }
        }
    }
    
    if (isset($_POST['delete_coordinator'])) {
        $id = $_POST['coordinator_id'];
        
        try {
            $query = "DELETE FROM department_coordinators WHERE id = :id";
            $stmt = $db->prepare($query);
            $stmt->execute([':id' => $id]);
            
            if ($stmt->rowCount() > 0) {
                $success_message = "Coordinator deleted successfully!";
            } else {
                $error_message = "Failed to delete coordinator or coordinator not found.";
            }
        } catch (PDOException $e) {
            $error_message = "Database error: " . $e->getMessage();
        }
    }
}

// Load current registration status
$registrationEnabled = true; // Default value
$registrationConfigFile = realpath(__DIR__ . '/../../src/config/registration_config.php');

if ($registrationConfigFile && file_exists($registrationConfigFile)) {
    include_once $registrationConfigFile;
    if (defined('REGISTRATION_ENABLED')) {
        $registrationEnabled = REGISTRATION_ENABLED;
    }
}

// Load current email configuration
$logoUrl = '';
$baseUrl = '';

// Try different paths to find the configuration file
$possiblePaths = [
    realpath(__DIR__ . '/../../src/config/email_logo_config.php'),
    '../../src/config/email_logo_config.php',
    realpath(__DIR__ . '/../../../src/config/email_logo_config.php'),
    '../../../src/config/email_logo_config.php'
];

foreach ($possiblePaths as $configFile) {
    if ($configFile && file_exists($configFile)) {
        include_once $configFile;
        if (defined('EMAIL_LOGO_URL') && defined('EMAIL_BASE_URL')) {
            $logoUrl = EMAIL_LOGO_URL;
            $baseUrl = EMAIL_BASE_URL;
            break;
        }
    }
}

// Load alumni coordinator configuration
$alumni_coordinator_name = 'Dr. Proloy Ghosh'; // Default values
$alumni_coordinator_contact = '7980532913';
$alumni_coordinator_email = 'alumni.majistic@gmail.com';
$alumni_payment_qr = '';
$alumni_payment_instructions = 'Scan the QR code with any UPI app to pay the alumni registration fee (Rs. 1000). After payment, please send a screenshot to the coordinator via WhatsApp for verification.';

$alumni_config_path = realpath(__DIR__ . '/../../src/config/alumni_coordinator_config.php');
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

// Fetch all department coordinators using MySQL
$coordinators = [];
try {
    $query = "SELECT id, department, name, contact, available_time FROM department_coordinators ORDER BY department ASC";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $coordinators = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error_message = "Error fetching coordinators: " . $e->getMessage();
}

// Fetch all admin users
$admin_users = [];
try {
    $query = "SELECT id, name, username, email, mobile, role, department, created_at, last_login FROM admin_users ORDER BY name ASC";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $admin_users = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error_message = "Error fetching admin users: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>maJIStic Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        .sidebar {
            position: fixed;
            top: 0;
            bottom: 0;
            left: 0;
            z-index: 100;
            padding: 48px 0 0;
            box-shadow: inset -1px 0 0 rgba(0, 0, 0, .1);
        }
        .sidebar-sticky {
            position: relative;
            top: 0;
            height: calc(100vh - 48px);
            padding-top: .5rem;
            overflow-x: hidden;
            overflow-y: auto;
        }
        .nav-link {
            font-weight: 500;
            color: #333;
        }
        .nav-link.active {
            color: #2470dc;
        }
        /* Toggle switch styling */
        .form-switch .form-check-input {
            width: 3em;
            height: 1.5em;
            margin-left: 0;
        }
        .form-switch .form-check-input:checked {
            background-color: #198754;
            border-color: #198754;
        }
    </style>
</head>
<body>
    <header class="navbar navbar-dark sticky-top bg-dark flex-md-nowrap p-0 shadow">
        <a class="navbar-brand col-md-3 col-lg-2 me-0 px-3" href="#">maJIStic Admin</a>
        <button class="navbar-toggler position-absolute d-md-none collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#sidebarMenu" aria-controls="sidebarMenu" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="navbar-nav">
            <div class="nav-item text-nowrap">
                <a class="nav-link px-3" href="../logout.php">Sign out</a>
            </div>
        </div>
    </header>

    <div class="container-fluid">
        <div class="row">
            <nav id="sidebarMenu" class="col-md-3 col-lg-2 d-md-block bg-light sidebar collapse">
                <div class="position-sticky pt-3 sidebar-sticky">
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link <?php echo $page === 'dashboard' ? 'active' : ''; ?>" href="?page=dashboard">
                                <i class="bi bi-speedometer2 me-2"></i>
                                Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo $page === 'registration_control' ? 'active' : ''; ?>" href="?page=registration_control">
                                <i class="bi bi-toggle-on me-2"></i>
                                Registration Control
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo $page === 'alumni_settings' ? 'active' : ''; ?>" href="?page=alumni_settings">
                                <i class="bi bi-mortarboard me-2"></i>
                                Alumni Coordinator
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo $page === 'email_config' ? 'active' : ''; ?>" href="?page=email_config">
                                <i class="bi bi-envelope me-2"></i>
                                Email Configuration
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo $page === 'coordinators' ? 'active' : ''; ?>" href="?page=coordinators">
                                <i class="bi bi-people me-2"></i>
                                Department Coordinators
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo $page === 'all_registrations' ? 'active' : ''; ?>" href="?page=all_registrations">
                                <i class="bi bi-list-check me-2"></i>
                                All Registrations
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo $page === 'admin_users' ? 'active' : ''; ?>" href="?page=admin_users">
                                <i class="bi bi-person-badge me-2"></i>
                                Admin Users
                            </a>
                        </li>
                        <!-- Add other menu items here -->
                    </ul>
                </div>
            </nav>

            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">
                        <?php
                        switch ($page) {
                            case 'registration_control':
                                echo 'Registration Control';
                                break;
                            case 'alumni_settings':
                                echo 'Alumni Coordinator Settings';
                                break;
                            case 'email_config':
                                echo 'Email Configuration';
                                break;
                            case 'coordinators':
                                echo 'Department Coordinators';
                                break;
                            case 'all_registrations':
                                echo 'Manage Registrations';
                                break;
                            case 'admin_users':
                                echo 'Admin Users';
                                break;
                            default:
                                echo 'Dashboard';
                        }
                        ?>
                    </h1>
                </div>

                <?php if (isset($success_message)): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?php echo $success_message; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php endif; ?>

                <?php if (isset($error_message)): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?php echo $error_message; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php endif; ?>

                <?php if ($page === 'registration_control'): ?>
                <!-- Registration Control Page -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Registration Status Control</h5>
                    </div>
                    <div class="card-body">
                        <div class="alert <?php echo $registrationEnabled ? 'alert-success' : 'alert-danger'; ?>">
                            <strong>Current Status:</strong> 
                            <?php echo $registrationEnabled ? 
                                'Registrations are <span class="badge bg-success">OPEN</span>' : 
                                'Registrations are <span class="badge bg-danger">CLOSED</span>'; ?>
                        </div>
                        
                        <form method="post" action="?page=registration_control">
                            <div class="form-check form-switch mb-3">
                                <input class="form-check-input" type="checkbox" id="registration_enabled" name="registration_enabled" <?php echo $registrationEnabled ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="registration_enabled">
                                    <span class="fs-5">Toggle Registration Status</span>
                                </label>
                            </div>
                            
                            <div class="mb-3">
                                <div class="card bg-light">
                                    <div class="card-body">
                                        <h6>What happens when registrations are closed?</h6>
                                        <ul>
                                            <li>Registration buttons will redirect to a message page</li>
                                            <li>Visitors will see a "Registrations are closed" message</li>
                                            <li>All registration forms will be inaccessible</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            
                            <button type="submit" name="update_registration_status" class="btn btn-primary">
                                Update Registration Status
                            </button>
                        </form>
                    </div>
                </div>
                
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Registration Message Preview</h5>
                    </div>
                    <div class="card-body">
                        <p>When registrations are closed, visitors will see the following page:</p>
                        <div class="border p-3 mt-3 bg-light">
                            <div class="text-center mb-3">
                                <i class="bi bi-exclamation-circle text-warning" style="font-size: 3rem;"></i>
                            </div>
                            <h3 class="text-center">We are not accepting Registrations Right Now</h3>
                            <p class="text-center">It will be resumed shortly. Keep an eye on the portal. Meanwhile, explore the website.</p>
                        </div>
                        <div class="mt-3">
                            <a href="../../src/handlers/registration_closed.php" target="_blank" class="btn btn-secondary">
                                <i class="bi bi-eye"></i> View Full Message Page
                            </a>
                        </div>
                    </div>
                </div>
                
                <?php elseif ($page === 'alumni_settings'): ?>
                <!-- Alumni Coordinator Settings Page -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Alumni Coordinator Settings</h5>
                    </div>
                    <div class="card-body">
                        <form method="post" action="alumni_coordinator_update.php">
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="coordinator_name" class="form-label">Coordinator Name</label>
                                    <input type="text" class="form-control" id="coordinator_name" name="coordinator_name" value="<?php echo htmlspecialchars($alumni_coordinator_name); ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="coordinator_contact" class="form-label">Contact Number</label>
                                    <input type="text" class="form-control" id="coordinator_contact" name="coordinator_contact" value="<?php echo htmlspecialchars($alumni_coordinator_contact); ?>" required>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="coordinator_email" class="form-label">Email Address</label>
                                <input type="email" class="form-control" id="coordinator_email" name="coordinator_email" value="<?php echo htmlspecialchars($alumni_coordinator_email); ?>">
                            </div>
                            
                            <div class="mb-3">
                                <label for="payment_qr" class="form-label">Payment QR Code URL</label>
                                <input type="url" class="form-control" id="payment_qr" name="payment_qr" value="<?php echo htmlspecialchars($alumni_payment_qr); ?>">
                                <small class="form-text text-muted">Enter the direct URL to the payment QR code image (must be a valid URL starting with http:// or https://)</small>
                            </div>
                            
                            <?php if (!empty($alumni_payment_qr)): ?>
                            <div class="mb-3">
                                <label class="form-label">Current QR Code Preview</label>
                                <div class="border p-3 text-center bg-light">
                                    <img src="<?php echo htmlspecialchars($alumni_payment_qr); ?>" alt="Payment QR Code" style="max-width: 200px; height: auto;">
                                </div>
                            </div>
                            <?php endif; ?>
                            
                            <div class="mb-3">
                                <label for="payment_instructions" class="form-label">Payment Instructions</label>
                                <textarea class="form-control" id="payment_instructions" name="payment_instructions" rows="3" required><?php echo htmlspecialchars($alumni_payment_instructions); ?></textarea>
                                <small class="form-text text-muted">These instructions will be shown to alumni when making payments.</small>
                            </div>
                            
                            <button type="submit" name="update_alumni_coordinator" class="btn btn-primary">
                                <i class="bi bi-save me-1"></i> Update Alumni Coordinator Settings
                            </button>
                        </form>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Alumni Email Preview</h5>
                    </div>
                    <div class="card-body">
                        <p>Alumni will receive an email with the following QR code and coordinator information:</p>
                        
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle me-2"></i> The email will include the coordinator details and payment QR code above.
                        </div>
                        
                        <div class="text-center mt-4">
                            <a href="../../src/handler/email_preview.php?type=alumni" target="_blank" class="btn btn-secondary">
                                <i class="bi bi-envelope-fill me-1"></i> Preview Alumni Email
                            </a>
                        </div>
                    </div>
                </div>

                <?php elseif ($page === 'email_config'): ?>
                <!-- Email Configuration Page -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Email Settings</h5>
                    </div>
                    <div class="card-body">
                        <form method="post" action="?page=email_config">
                            <div class="mb-3">
                                <label for="logo_url" class="form-label">Email Logo URL</label>
                                <input type="text" class="form-control" id="logo_url" name="logo_url" value="<?php echo htmlspecialchars($logoUrl); ?>" required>
                                <div class="form-text">Enter the full URL for the logo image used in emails.</div>
                            </div>
                            <div class="mb-3">
                                <label for="base_url" class="form-label">Base URL for Email Links</label>
                                <input type="text" class="form-control" id="base_url" name="base_url" value="<?php echo htmlspecialchars($baseUrl); ?>" required>
                                <div class="form-text">Enter the base URL used for all links in emails (e.g., https://majistic.in).</div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Current Logo Preview</label>
                                <div class="border p-3 text-center bg-dark">
                                    <?php if (!empty($logoUrl)): ?>
                                    <img src="<?php echo htmlspecialchars($logoUrl); ?>" alt="Current Logo" style="max-height: 100px;">
                                    <?php else: ?>
                                    <p class="text-muted">No logo URL set</p>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <button type="submit" name="update_email_config" class="btn btn-primary">Update Configuration</button>
                        </form>
                    </div>
                </div>
                <?php elseif ($page === 'coordinators'): ?>
                <!-- Department Coordinators Management Page -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">Add New Coordinator</h5>
                            </div>
                            <div class="card-body">
                                <form method="post" action="?page=coordinators">
                                    <div class="mb-3">
                                        <label for="department" class="form-label">Department</label>
                                        <select class="form-select" id="department" name="department" required>
                                            <option value="">--Select Department--</option>
                                            <option value="CSE">CSE</option>
                                            <option value="CSE AI-ML">CSE AI-ML</option>
                                            <option value="CST">CST</option>
                                            <option value="IT">IT</option>
                                            <option value="ECE">ECE</option>
                                            <option value="EE">EE</option>
                                            <option value="BME">BME</option>
                                            <option value="CE">CE</option>
                                            <option value="ME">ME</option>
                                            <option value="AGE">AGE</option>
                                            <option value="BBA">BBA</option>
                                            <option value="MBA">MBA</option>
                                            <option value="BCA">BCA</option>
                                            <option value="MCA">MCA</option>
                                            <option value="Diploma ME">Diploma ME</option>
                                            <option value="Diploma CE">Diploma CE</option>
                                            <option value="Diploma EE">Diploma EE</option>
                                            <option value="B. Pharmacy">Pharmacy</option>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label for="coordinator_name" class="form-label">Coordinator Name</label>
                                        <input type="text" class="form-control" id="coordinator_name" name="coordinator_name" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="contact" class="form-label">Contact Number</label>
                                        <input type="text" class="form-control" id="contact" name="contact" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="available_time" class="form-label">Available Time</label>
                                        <input type="text" class="form-control" id="available_time" name="available_time" placeholder="e.g., Mon-Fri: 10 AM - 4 PM">
                                    </div>
                                    <button type="submit" name="add_coordinator" class="btn btn-primary">Add Coordinator</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Department Coordinators</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>Department</th>
                                        <th>Coordinator Name</th>
                                        <th>Contact</th>
                                        <th>Available Time</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($coordinators)): ?>
                                        <tr>
                                            <td colspan="5" class="text-center">No coordinators found. Add your first coordinator!</td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($coordinators as $coordinator): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($coordinator['department']); ?></td>
                                            <td><?php echo htmlspecialchars($coordinator['name']); ?></td>
                                            <td><?php echo htmlspecialchars($coordinator['contact']); ?></td>
                                            <td><?php echo htmlspecialchars($coordinator['available_time'] ?? 'Not specified'); ?></td>
                                            <td>
                                                <button type="button" class="btn btn-sm btn-primary edit-coordinator" 
                                                    data-id="<?php echo $coordinator['id']; ?>"
                                                    data-department="<?php echo htmlspecialchars($coordinator['department']); ?>"
                                                    data-name="<?php echo htmlspecialchars($coordinator['name']); ?>"
                                                    data-contact="<?php echo htmlspecialchars($coordinator['contact']); ?>"
                                                    data-available-time="<?php echo htmlspecialchars($coordinator['available_time'] ?? ''); ?>">
                                                    <i class="bi bi-pencil"></i> Edit
                                                </button>
                                                <button type="button" class="btn btn-sm btn-danger delete-coordinator" 
                                                    data-id="<?php echo $coordinator['id']; ?>"
                                                    data-name="<?php echo htmlspecialchars($coordinator['name']); ?>">
                                                    <i class="bi bi-trash"></i> Delete
                                                </button>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Edit Coordinator Modal -->
                <div class="modal fade" id="editCoordinatorModal" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Edit Coordinator</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <form method="post" action="?page=coordinators">
                                <div class="modal-body">
                                    <input type="hidden" name="coordinator_id" id="edit_coordinator_id">
                                    <div class="mb-3">
                                        <label for="edit_department" class="form-label">Department</label>
                                        <select class="form-select" id="edit_department" name="department" required>
                                            <option value="">--Select Department--</option>
                                            <option value="CSE">CSE</option>
                                            <option value="CSE AI-ML">CSE AI-ML</option>
                                            <option value="CST">CST</option>
                                            <option value="IT">IT</option>
                                            <option value="ECE">ECE</option>
                                            <option value="EE">EE</option>
                                            <option value="BME">BME</option>
                                            <option value="CE">CE</option>
                                            <option value="ME">ME</option>
                                            <option value="AGE">AGE</option>
                                            <option value="BBA">BBA</option>
                                            <option value="MBA">MBA</option>
                                            <option value="BCA">BCA</option>
                                            <option value="MCA">MCA</option>
                                            <option value="Diploma ME">Diploma ME</option>
                                            <option value="Diploma CE">Diploma CE</option>
                                            <option value="Diploma EE">Diploma EE</option>
                                            <option value="B. Pharmacy">Pharmacy</option>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label for="edit_coordinator_name" class="form-label">Coordinator Name</label>
                                        <input type="text" class="form-control" id="edit_coordinator_name" name="coordinator_name" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="edit_contact" class="form-label">Contact Number</label>
                                        <input type="text" class="form-control" id="edit_contact" name="contact" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="edit_available_time" class="form-label">Available Time</label>
                                        <input type="text" class="form-control" id="edit_available_time" name="available_time" placeholder="e.g., Mon-Fri: 10 AM - 4 PM">
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                    <button type="submit" name="update_coordinator" class="btn btn-primary">Update</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Delete Coordinator Modal -->
                <div class="modal fade" id="deleteCoordinatorModal" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Confirm Delete</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <p>Are you sure you want to delete coordinator <span id="delete_coordinator_name" class="fw-bold"></span>?</p>
                                <p class="text-danger">This action cannot be undone.</p>
                            </div>
                            <div class="modal-footer">
                                <form method="post" action="?page=coordinators">
                                    <input type="hidden" name="coordinator_id" id="delete_coordinator_id">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                    <button type="submit" name="delete_coordinator" class="btn btn-danger">Delete</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <?php elseif ($page === 'student_registrations'): ?>
    <!-- Include the student registration list page -->
    <?php include 'student_registrations.php'; ?>

<?php elseif ($page === 'alumni_registrations'): ?>
    <!-- Include the alumni registration list page -->
    <?php include 'alumni_registrations.php'; ?>

<?php elseif ($page === 'all_registrations'): ?>
    <!-- Include the unified registrations list page -->
    <?php include 'all_registrations.php'; ?>

<?php elseif ($page === 'admin_users'): ?>
                <!-- Admin Users Management Page -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">System Admin Users</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Username</th>
                                        <th>Email</th>
                                        <th>Mobile</th>
                                        <th>Role</th>
                                        <th>Department</th>
                                        <th>Created</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($admin_users)): ?>
                                        <tr>
                                            <td colspan="7" class="text-center">No admin users found.</td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($admin_users as $user): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($user['name']); ?></td>
                                            <td><?php echo htmlspecialchars($user['username']); ?></td>
                                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                                            <td><?php echo htmlspecialchars($user['mobile']); ?></td>
                                            <td><span class="badge bg-primary"><?php echo htmlspecialchars($user['role']); ?></span></td>
                                            <td><?php echo $user['role'] === 'Coordinator' ? htmlspecialchars($user['department']) : 'N/A'; ?></td>
                                            <td><?php echo htmlspecialchars($user['created_at']); ?></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <?php elseif ($page === 'dashboard'): ?>
<!-- Dashboard Content -->
<div class="row">
    <div class="col-md-6 col-lg-3 mb-4">
        <div class="card bg-primary text-white h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-uppercase fw-bold mb-1">Student Registrations</h6>
                        <?php
                        try {
                            $query = "SELECT COUNT(*) FROM registrations";
                            $stmt = $db->prepare($query);
                            $stmt->execute();
                            $student_count = $stmt->fetchColumn();
                            
                            $query = "SELECT COUNT(*) FROM registrations WHERE payment_status = 'Paid'";
                            $stmt = $db->prepare($query);
                            $stmt->execute();
                            $student_paid = $stmt->fetchColumn();
                        } catch (PDOException $e) {
                            $student_count = 0;
                            $student_paid = 0;
                        }
                        ?>
                        <h2 class="display-4 fw-bold mb-0"><?php echo number_format($student_count); ?></h2>
                    </div>
                    <div class="icon-shape bg-white bg-opacity-25 text-white rounded-3 p-3">
                        <i class="bi bi-people-fill fs-1"></i>
                    </div>
                </div>
                <small class="fw-semibold"><?php echo number_format($student_paid); ?> Paid / <?php echo number_format($student_count - $student_paid); ?> Pending</small>
            </div>
            <div class="card-footer bg-primary bg-opacity-75 py-2">
                <a href="?page=student_registrations" class="text-white d-flex justify-content-between align-items-center text-decoration-none">
                    <span>View Details</span>
                    <i class="bi bi-arrow-right"></i>
                </a>
            </div>
        </div>
    </div>
    
    <div class="col-md-6 col-lg-3 mb-4">
        <div class="card bg-info text-white h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-uppercase fw-bold mb-1">Alumni Registrations</h6>
                        <?php
                        try {
                            $query = "SELECT COUNT(*) FROM alumni_registrations";
                            $stmt = $db->prepare($query);
                            $stmt->execute();
                            $alumni_count = $stmt->fetchColumn();
                            
                            $query = "SELECT COUNT(*) FROM alumni_registrations WHERE payment_status = 'Paid'";
                            $stmt = $db->prepare($query);
                            $stmt->execute();
                            $alumni_paid = $stmt->fetchColumn();
                        } catch (PDOException $e) {
                            $alumni_count = 0;
                            $alumni_paid = 0;
                        }
                        ?>
                        <h2 class="display-4 fw-bold mb-0"><?php echo number_format($alumni_count); ?></h2>
                    </div>
                    <div class="icon-shape bg-white bg-opacity-25 text-white rounded-3 p-3">
                        <i class="bi bi-mortarboard-fill fs-1"></i>
                    </div>
                </div>
                <small class="fw-semibold"><?php echo number_format($alumni_paid); ?> Paid / <?php echo number_format($alumni_count - $alumni_paid); ?> Pending</small>
            </div>
            <div class="card-footer bg-info bg-opacity-75 py-2">
                <a href="?page=alumni_registrations" class="text-white d-flex justify-content-between align-items-center text-decoration-none">
                    <span>View Details</span>
                    <i class="bi bi-arrow-right"></i>
                </a>
            </div>
        </div>
    </div>
    
    <div class="col-md-6 col-lg-3 mb-4">
        <div class="card bg-success text-white h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-uppercase fw-bold mb-1">Payment Status</h6>
                        <?php
                        $total_paid = $student_paid + $alumni_paid;
                        $total_registrations = $student_count + $alumni_count;
                        $paid_percentage = ($total_registrations > 0) ? round(($total_paid / $total_registrations) * 100) : 0;
                        ?>
                        <h2 class="display-4 fw-bold mb-0"><?php echo $paid_percentage; ?>%</h2>
                    </div>
                    <div class="icon-shape bg-white bg-opacity-25 text-white rounded-3 p-3">
                        <i class="bi bi-cash-stack fs-1"></i>
                    </div>
                </div>
                <small class="fw-semibold"><?php echo number_format($total_paid); ?> Paid / <?php echo number_format($total_registrations - $total_paid); ?> Pending</small>
            </div>
            <div class="card-footer bg-success bg-opacity-75 py-2">
                <a href="?page=all_registrations&payment_status=Paid" class="text-white d-flex justify-content-between align-items-center text-decoration-none">
                    <span>View Paid Registrations</span>
                    <i class="bi bi-arrow-right"></i>
                </a>
            </div>
        </div>
    </div>
    
    <div class="col-md-6 col-lg-3 mb-4">
        <div class="card bg-warning text-dark h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-uppercase fw-bold mb-1">Department Coordinators</h6>
                        <?php
                        try {
                            $query = "SELECT COUNT(*) FROM department_coordinators";
                            $stmt = $db->prepare($query);
                            $stmt->execute();
                            $coordinator_count = $stmt->fetchColumn();
                        } catch (PDOException $e) {
                            $coordinator_count = 0;
                        }
                        ?>
                        <h2 class="display-4 fw-bold mb-0"><?php echo number_format($coordinator_count); ?></h2>
                    </div>
                    <div class="icon-shape bg-white bg-opacity-25 text-dark rounded-3 p-3">
                        <i class="bi bi-person-badge fs-1"></i>
                    </div>
                </div>
                <small class="fw-semibold">Contact information for students</small>
            </div>
            <div class="card-footer bg-warning bg-opacity-75 py-2">
                <a href="?page=coordinators" class="text-dark d-flex justify-content-between align-items-center text-decoration-none">
                    <span>Manage Coordinators</span>
                    <i class="bi bi-arrow-right"></i>
                </a>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6 mb-4">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="mb-0">Registration Status</h5>
            </div>
            <div class="card-body">
                < class="mb-4">
                    <h6>Registration is currently <?php echo $registrationEnabled ? '<span class="badge bg-success">OPEN</span>' : '<span class="badge bg-danger">CLOSED</span>'; ?></h6>
                    <p class="text-muted">
                        <?php if ($registrationEnabled): ?>
                            Students and alumni can currently register for the event.
                        <?php else: ?>
                            Registration has been temporarily disabled.
                        <?php endif; ?>
                    </p>
                    <div class="progress" style="height: 25px;">
                        <div class="progress-bar <?php echo $registrationEnabled ? 'bg-success' : 'bg-danger'; ?>" 
                             role="progressbar" 
                             style="width: <?php echo $registrationEnabled ? '100%' : '0%'; ?>;"
                             aria-valuenow="<?php echo $registrationEnabled ? '100' : '0'; ?>" 
                             aria-valuemin="0" 
                             aria-valuemax="100"></div>
                            <?php echo $registrationEnabled ? 'ENABLED' : 'DISABLED'; ?>
                        </div>
                    </div>
                </div>
                <div class="d-grid">
                    <a href="?page=registration_control" class="btn btn-primary"></a>
                        <i class="bi bi-gear-fill me-2"></i> Configure Registration Settings
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-6 mb-4"></div>
        <div class="card h-100">
            <div class="card-header">
                <h5 class="mb-0">Recent Registrations</h5>
            </div>
            <div class="card-body">
                <?php
                // Fetch most recent registrations
                try {
                    $query = "(SELECT id, 'student' AS type, student_name AS name, jis_id, department, registration_date 
                              FROM registrations
                              ORDER BY registration_date DESC LIMIT 5)
                              UNION ALL
                              (SELECT id, 'alumni' AS type, alumni_name AS name, jis_id, department, registration_date 
                              FROM alumni_registrations
                              ORDER BY registration_date DESC LIMIT 5)
                              ORDER BY registration_date DESC LIMIT 5";
                    $stmt = $db->prepare($query);
                    $stmt->execute();
                    $recent_registrations = $stmt->fetchAll(PDO::FETCH_ASSOC);
                } catch (PDOException $e) {
                    $recent_registrations = [];
                }
                
                if (!empty($recent_registrations)):
                ?>
                <div class="list-group">
                    <?php foreach ($recent_registrations as $reg): ?>
                    <div class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-0"><?php echo htmlspecialchars($reg['name']); ?>
                                <small class="ms-2 badge bg-<?php echo $reg['type'] === 'student' ? 'info' : 'secondary'; ?>">
                                    <?php echo ucfirst($reg['type']); ?>
                                </small>
                            </h6>
                            <p class="text-muted small mb-0">
                                <?php echo htmlspecialchars($reg['jis_id']); ?> - 
                                <?php echo htmlspecialchars($reg['department']); ?>
                            </p>
                        </div>
                        <div class="text-muted small">
                            <?php 
                            $date = new DateTime($reg['registration_date']);
                            $now = new DateTime();
                            $diff = $date->diff($now);
                            
                            if ($diff->d == 0) {
                                if ($diff->h == 0) {
                                    echo $diff->i . ' min ago';
                                } else {
                                    echo $diff->h . ' hours ago';
                                }
                            } elseif ($diff->d == 1) {
                                echo 'Yesterday';
                            } else {
                                echo $date->format('d M Y');
                            }
                            ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php else: ?>
                <div class="text-center py-4">
                    <i class="bi bi-calendar2-x text-muted" style="font-size: 2rem;"></i>
                    <p class="text-muted mt-2">No recent registrations found</p>
                </div>
                <?php endif; ?>
                
                <div class="d-grid mt-3">
                    <a href="?page=all_registrations" class="btn btn-outline-primary">
                        View All Registrations
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Footer -->
<footer class="mt-4 mb-2 py-3 text-center text-muted">
    <small>&copy; <?php echo date('Y'); ?> maJIStic Admin Dashboard | JIS College of Engineering</small>
</footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // JavaScript for handling modals
        document.addEventListener('DOMContentLoaded', function() {
            // Edit Coordinator Modal
            const editButtons = document.querySelectorAll('.edit-coordinator');
            if (editButtons) {
                editButtons.forEach(button => {
                    button.addEventListener('click', function() {
                        const id = this.getAttribute('data-id');
                        const department = this.getAttribute('data-department');
                        const name = this.getAttribute('data-name');
                        const contact = this.getAttribute('data-contact');
                        const availableTime = this.getAttribute('data-available-time');
                        
                        document.getElementById('edit_coordinator_id').value = id;
                        
                        // Set the selected department in dropdown
                        const departmentSelect = document.getElementById('edit_department');
                        for (let i = 0; i < departmentSelect.options.length; i++) {
                            if (departmentSelect.options[i].value === department) {
                                departmentSelect.options[i].selected = true;
                                break;
                            }
                        }
                       
                        document.getElementById('edit_coordinator_name').value = name;
                        document.getElementById('edit_contact').value = contact;
                        document.getElementById('edit_available_time').value = availableTime || '';
                        
                        const modal = new bootstrap.Modal(document.getElementById('editCoordinatorModal'));
                        modal.show();
                    });
                });
            }
            
            // Delete Coordinator Modal
            const deleteButtons = document.querySelectorAll('.delete-coordinator');
            if (deleteButtons) {
                deleteButtons.forEach(button => {
                    button.addEventListener('click', function() {
                        const id = this.getAttribute('data-id');
                        const name = this.getAttribute('data-name');
                        
                        document.getElementById('delete_coordinator_id').value = id;
                        document.getElementById('delete_coordinator_name').textContent = name;
                        
                        const modal = new bootstrap.Modal(document.getElementById('deleteCoordinatorModal'));
                        modal.show();
                    });
                });
            }
        });
    </script>
</body>
</html>

            const deleteAdminButtons = document.querySelectorAll('.delete-admin-user');
            if (deleteAdminButtons) {
                deleteAdminButtons.forEach(button => {
                    button.addEventListener('click', function() {
                        const id = this.getAttribute('data-id');
                        const name = this.getAttribute('data-name');
                        
                        document.getElementById('delete_admin_user_id').value = id;
                        document.getElementById('delete_admin_user_name').textContent = name;
                        
                        const modal = new bootstrap.Modal(document.getElementById('deleteAdminUserModal'));
                        modal.show();
                    });
                });
            }
        });
    </script>
</body>
</html>
