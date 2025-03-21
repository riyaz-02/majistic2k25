<?php
session_start();
require_once '../../includes/db_config.php';

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
    
    // Department Coordinator Management
    if (isset($_POST['add_coordinator'])) {
        $department = trim($_POST['department']);
        $name = trim($_POST['coordinator_name']);
        $contact = trim($_POST['contact']);
        $available_time = trim($_POST['available_time']);
        if (empty($department) || empty($name) || empty($contact)) {
            $error_message = "All fields are required for adding a coordinator.";
        } else {
            $result = $department_coordinators->insertOne([
                'department' => $department,
                'name' => $name,
                'contact' => $contact,
                'available_time' => $available_time,
                'created_at' => new MongoDB\BSON\UTCDateTime()
            ]);
            
            if ($result->getInsertedCount()) {
                $success_message = "Coordinator added successfully!";
            } else {
                $error_message = "Failed to add coordinator.";
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
            $result = $department_coordinators->updateOne(
                ['_id' => new MongoDB\BSON\ObjectId($id)],
                ['$set' => [
                    'department' => $department,
                    'name' => $name,
                    'contact' => $contact,
                    'available_time' => $available_time,
                    'updated_at' => new MongoDB\BSON\UTCDateTime()
                ]]
            );
            
            if ($result->getModifiedCount()) {
                $success_message = "Coordinator updated successfully!";
            } else {
                $error_message = "No changes were made or coordinator not found.";
            }
        }
    }
    
    if (isset($_POST['delete_coordinator'])) {
        $id = $_POST['coordinator_id'];
        
        $result = $department_coordinators->deleteOne(
            ['_id' => new MongoDB\BSON\ObjectId($id)]
        );
        
        if ($result->getDeletedCount()) {
            $success_message = "Coordinator deleted successfully!";
        } else {
            $error_message = "Failed to delete coordinator or coordinator not found.";
        }
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

// Fetch all department coordinators
$coordinators = [];
$cursor = $department_coordinators->find([], ['sort' => ['department' => 1]]);
foreach ($cursor as $doc) {
    $coordinators[] = [
        'id' => (string)$doc->_id,
        'department' => $doc->department,
        'name' => $doc->name,
        'contact' => $doc->contact,
        'available_time' => $doc->available_time ?? ''
    ];
}

// Fetch all admin users
$admin_users = [];
try {
    $cursor = $db->admin_users->find([], ['sort' => ['name' => 1]]);
    foreach ($cursor as $doc) {
        $admin_users[] = [
            'id' => (string)$doc->_id,
            'name' => $doc->name ?? '',
            'username' => $doc->username ?? '',
            'email' => $doc->email ?? '',
            'mobile' => $doc->mobile ?? '',
            'role' => $doc->role ?? '',
            'department' => $doc->department ?? 'N/A',
            'created_at' => isset($doc->created_at) ? $doc->created_at->toDateTime()->format('Y-m-d H:i:s') : '',
            'last_login' => isset($doc->last_login) ? $doc->last_login->toDateTime()->format('Y-m-d H:i:s') : 'Never'
        ];
    }
} catch (Exception $e) {
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
                            case 'email_config':
                                echo 'Email Configuration';
                                break;
                            case 'coordinators':
                                echo 'Department Coordinators';
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

                <?php if ($page === 'email_config'): ?>
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
                <?php else: ?>
                <!-- Dashboard Content -->
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Welcome to maJIStic Admin Dashboard</h5>
                        <p class="card-text">Use the navigation menu to manage various aspects of the system.</p>
                        
                        <div class="row mt-4">
                            <div class="col-md-4">
                                <div class="card bg-primary text-white mb-4">
                                    <div class="card-body">
                                        <h5 class="card-title">Email Configuration</h5>
                                        <p class="card-text">Manage email logos and base URLs for links in emails.</p>
                                    </div>
                                    <div class="card-footer d-flex align-items-center justify-content-between">
                                        <a class="small text-white stretched-link" href="?page=email_config">View Details</a>
                                        <div class="small text-white"><i class="bi bi-chevron-right"></i></div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="card bg-success text-white mb-4">
                                    <div class="card-body">
                                        <h5 class="card-title">Department Coordinators</h5>
                                        <p class="card-text">Manage department coordinators information.</p>
                                    </div>
                                    <div class="card-footer d-flex align-items-center justify-content-between">
                                        <a class="small text-white stretched-link" href="?page=coordinators">View Details</a>
                                        <div class="small text-white"><i class="bi bi-chevron-right"></i></div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="card bg-info text-white mb-4">
                                    <div class="card-body">
                                        <h5 class="card-title">Admin Users</h5>
                                        <p class="card-text">View all admin users and their details.</p>
                                    </div>
                                    <div class="card-footer d-flex align-items-center justify-content-between">
                                        <a class="small text-white stretched-link" href="?page=admin_users">View Details</a>
                                        <div class="small text-white"><i class="bi bi-chevron-right"></i></div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Add more dashboard cards as needed -->
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </main>
        </div>
    </div>

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
