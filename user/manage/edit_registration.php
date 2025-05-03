<?php
session_start();
require_once '../../includes/db_config.php';

// Check if user is logged in with admin privileges
if (!isset($_SESSION['admin_id']) || !isset($_SESSION['admin_role']) || 
    ($_SESSION['admin_role'] !== 'Manage Website' && $_SESSION['admin_role'] !== 'Super Admin')) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

// Get registration type and ID from URL
$type = isset($_GET['type']) ? $_GET['type'] : '';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (empty($type) || $id <= 0) {
    $_SESSION['error_message'] = "Invalid registration specified.";
    header('Location: index.php');
    exit;
}

// Determine the table based on registration type
$table = $type === 'student' ? 'registrations' : 'alumni_registrations';
$name_field = $type === 'student' ? 'student_name' : 'alumni_name';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Prepare base data array with common fields
        $data = [
            'jis_id' => $_POST['jis_id'],
            'email' => $_POST['email'],
            'mobile' => $_POST['mobile'],
            'department' => $_POST['department'],
            'payment_status' => $_POST['payment_status'],
            'id' => $id
        ];
        
        // Add type-specific fields
        if ($type === 'student') {
            $data['student_name'] = $_POST['name'];
            $data['gender'] = $_POST['gender'];
            
            // Handle competition fields
            if (isset($_POST['inhouse_competition']) && $_POST['inhouse_competition'] === 'Yes') {
                $data['inhouse_competition'] = 'Yes';
                $data['competition_name'] = $_POST['competition_name'] ?? '';
            } else {
                $data['inhouse_competition'] = 'No';
                $data['competition_name'] = '';
            }
            
            // Build query
            $query = "UPDATE registrations SET 
                student_name = :student_name,
                jis_id = :jis_id, 
                gender = :gender,
                email = :email, 
                mobile = :mobile, 
                department = :department,
                inhouse_competition = :inhouse_competition,
                competition_name = :competition_name,
                payment_status = :payment_status
                WHERE id = :id";
        } else {
            $data['alumni_name'] = $_POST['name'];
            $data['passout_year'] = $_POST['passout_year'];
            $data['current_organization'] = $_POST['current_organization'] ?? '';
            
            // Build query
            $query = "UPDATE alumni_registrations SET 
                alumni_name = :alumni_name,
                jis_id = :jis_id, 
                email = :email, 
                mobile = :mobile, 
                department = :department,
                passout_year = :passout_year,
                current_organization = :current_organization,
                payment_status = :payment_status
                WHERE id = :id";
        }
        
        $stmt = $db->prepare($query);
        $stmt->execute($data);
        
        $_SESSION['success_message'] = "Registration updated successfully!";
        header("Location: view_registration.php?type=$type&id=$id");
        exit;
    } catch (PDOException $e) {
        $_SESSION['error_message'] = "Error updating registration: " . $e->getMessage();
    }
}

// Fetch registration details
try {
    $query = "SELECT * FROM $table WHERE id = :id LIMIT 1";
    $stmt = $db->prepare($query);
    $stmt->execute([':id' => $id]);
    $registration = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$registration) {
        $_SESSION['error_message'] = "Registration not found.";
        header('Location: index.php');
        exit;
    }
} catch (PDOException $e) {
    $_SESSION['error_message'] = "Database error: " . $e->getMessage();
    header('Location: index.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Registration - maJIStic Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        body {
            background-color: #f5f5f5;
        }
        .registration-container {
            max-width: 800px;
            margin: 30px auto;
        }
        .back-btn {
            margin-bottom: 20px;
        }
        .card {
            border: none;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        }
        .card-header {
            background-color: #343a40;
            color: white;
            font-weight: 600;
        }
        .form-label {
            font-weight: 500;
        }
        .required-field::after {
            content: " *";
            color: red;
        }
    </style>
</head>
<body>
    <div class="registration-container">
        <div class="back-btn">
            <a href="view_registration.php?type=<?php echo $type; ?>&id=<?php echo $id; ?>" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Back to Details
            </a>
        </div>
        
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Edit <?php echo $type === 'student' ? 'Student' : 'Alumni'; ?> Registration</h5>
            </div>
            <div class="card-body">
                <?php if (isset($_SESSION['error_message'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php endif; ?>
                
                <form method="post" action="edit_registration.php?type=<?php echo $type; ?>&id=<?php echo $id; ?>">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="name" class="form-label required-field">Name</label>
                            <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($registration[$name_field]); ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label for="jis_id" class="form-label required-field">JIS ID</label>
                            <input type="text" class="form-control" id="jis_id" name="jis_id" value="<?php echo htmlspecialchars($registration['jis_id']); ?>" required>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="email" class="form-label required-field">Email</label>
                            <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($registration['email']); ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label for="mobile" class="form-label required-field">Mobile</label>
                            <input type="text" class="form-control" id="mobile" name="mobile" value="<?php echo htmlspecialchars($registration['mobile']); ?>" required>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="department" class="form-label required-field">Department</label>
                            <select class="form-select" id="department" name="department" required>
                                <option value="">--Select Department--</option>
                                <option value="CSE" <?php echo $registration['department'] === 'CSE' ? 'selected' : ''; ?>>CSE</option>
                                <option value="CSE AI-ML" <?php echo $registration['department'] === 'CSE AI-ML' ? 'selected' : ''; ?>>CSE AI-ML</option>
                                <option value="CST" <?php echo $registration['department'] === 'CST' ? 'selected' : ''; ?>>CST</option>
                                <option value="IT" <?php echo $registration['department'] === 'IT' ? 'selected' : ''; ?>>IT</option>
                                <option value="ECE" <?php echo $registration['department'] === 'ECE' ? 'selected' : ''; ?>>ECE</option>
                                <option value="EE" <?php echo $registration['department'] === 'EE' ? 'selected' : ''; ?>>EE</option>
                                <option value="BME" <?php echo $registration['department'] === 'BME' ? 'selected' : ''; ?>>BME</option>
                                <option value="CE" <?php echo $registration['department'] === 'CE' ? 'selected' : ''; ?>>CE</option>
                                <option value="ME" <?php echo $registration['department'] === 'ME' ? 'selected' : ''; ?>>ME</option>
                                <option value="AGE" <?php echo $registration['department'] === 'AGE' ? 'selected' : ''; ?>>AGE</option>
                                <option value="BBA" <?php echo $registration['department'] === 'BBA' ? 'selected' : ''; ?>>BBA</option>
                                <option value="MBA" <?php echo $registration['department'] === 'MBA' ? 'selected' : ''; ?>>MBA</option>
                                <option value="BCA" <?php echo $registration['department'] === 'BCA' ? 'selected' : ''; ?>>BCA</option>
                                <option value="MCA" <?php echo $registration['department'] === 'MCA' ? 'selected' : ''; ?>>MCA</option>
                                <option value="Diploma ME" <?php echo $registration['department'] === 'Diploma ME' ? 'selected' : ''; ?>>Diploma ME</option>
                                <option value="Diploma CE" <?php echo $registration['department'] === 'Diploma CE' ? 'selected' : ''; ?>>Diploma CE</option>
                                <option value="Diploma EE" <?php echo $registration['department'] === 'Diploma EE' ? 'selected' : ''; ?>>Diploma EE</option>
                                <option value="B. Pharmacy" <?php echo $registration['department'] === 'B. Pharmacy' ? 'selected' : ''; ?>>Pharmacy</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <?php if ($type === 'student'): ?>
                            <label for="gender" class="form-label required-field">Gender</label>
                            <select class="form-select" id="gender" name="gender" required>
                                <option value="">--Select Gender--</option>
                                <option value="Male" <?php echo $registration['gender'] === 'Male' ? 'selected' : ''; ?>>Male</option>
                                <option value="Female" <?php echo $registration['gender'] === 'Female' ? 'selected' : ''; ?>>Female</option>
                                <option value="Other" <?php echo $registration['gender'] === 'Other' ? 'selected' : ''; ?>>Other</option>
                            </select>
                            <?php else: ?>
                            <label for="passout_year" class="form-label required-field">Passout Year</label>
                            <select class="form-select" id="passout_year" name="passout_year" required>
                                <option value="">--Select Year--</option>
                                <?php for ($year = date('Y'); $year >= 1990; $year--): ?>
                                    <option value="<?php echo $year; ?>" <?php echo $registration['passout_year'] == $year ? 'selected' : ''; ?>><?php echo $year; ?></option>
                                <?php endfor; ?>
                            </select>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <?php if ($type === 'student'): ?>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Participating in In-house Competition?</label>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="inhouse_competition" id="inhouse_yes" value="Yes" <?php echo ($registration['inhouse_competition'] ?? '') === 'Yes' ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="inhouse_yes">Yes</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="inhouse_competition" id="inhouse_no" value="No" <?php echo ($registration['inhouse_competition'] ?? '') !== 'Yes' ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="inhouse_no">No</label>
                            </div>
                        </div>
                        <div class="col-md-6" id="competition_group" style="display: <?php echo ($registration['inhouse_competition'] ?? '') === 'Yes' ? 'block' : 'none'; ?>">
                            <label for="competition_name" class="form-label">Competition</label>
                            <select class="form-select" id="competition_name" name="competition_name">
                                <option value="">--Select Competition--</option>
                                <option value="Taal Se Taal Mila (Dance)" <?php echo ($registration['competition_name'] ?? '') === 'Taal Se Taal Mila (Dance)' ? 'selected' : ''; ?>>Taal Se Taal Mila (Dance)</option>
                                <option value="Actomania (Drama)" <?php echo ($registration['competition_name'] ?? '') === 'Actomania (Drama)' ? 'selected' : ''; ?>>Actomania (Drama)</option>
                                <option value="Jam Room (Band)" <?php echo ($registration['competition_name'] ?? '') === 'Jam Room (Band)' ? 'selected' : ''; ?>>Jam Room (Band)</option>
                                <option value="Fashion Fiesta (Fashion Show)" <?php echo ($registration['competition_name'] ?? '') === 'Fashion Fiesta (Fashion Show)' ? 'selected' : ''; ?>>Fashion Fiesta (Fashion Show)</option>
                            </select>
                        </div>
                    </div>
                    <?php else: ?>
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <label for="current_organization" class="form-label">Current Organization</label>
                            <input type="text" class="form-control" id="current_organization" name="current_organization" value="<?php echo htmlspecialchars($registration['current_organization'] ?? ''); ?>">
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="payment_status" class="form-label">Payment Status</label>
                            <div class="form-control bg-light">
                                <span class="badge bg-<?php echo $registration['payment_status'] === 'Paid' ? 'success' : 'warning'; ?> p-2">
                                    <?php echo $registration['payment_status']; ?>
                                </span>
                                <input type="hidden" name="payment_status" value="<?php echo $registration['payment_status']; ?>">
                            </div>
                            <small class="text-muted">* Payment status can only be updated from the main registration list or view page</small>
                        </div>
                    </div>
                    
                    <div class="text-center mt-4">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle"></i> Save Changes
                        </button>
                        <a href="view_registration.php?type=<?php echo $type; ?>&id=<?php echo $id; ?>" class="btn btn-secondary">
                            <i class="bi bi-x-circle"></i> Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Show/hide competition dropdown based on radio button selection
        document.addEventListener('DOMContentLoaded', function() {
            const inhouseYes = document.getElementById('inhouse_yes');
            const inhouseNo = document.getElementById('inhouse_no');
            const competitionGroup = document.getElementById('competition_group');
            
            if (inhouseYes && inhouseNo && competitionGroup) {
                inhouseYes.addEventListener('change', function() {
                    competitionGroup.style.display = 'block';
                    document.getElementById('competition_name').setAttribute('required', 'required');
                });
                
                inhouseNo.addEventListener('change', function() {
                    competitionGroup.style.display = 'none';
                    document.getElementById('competition_name').removeAttribute('required');
                    document.getElementById('competition_name').value = '';
                });
            }
        });
    </script>
</body>
</html>
