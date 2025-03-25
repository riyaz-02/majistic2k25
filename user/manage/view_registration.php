<?php
session_start();
require_once '../../includes/db_config.php';

// Check if user is logged in with admin privileges
if (!isset($_SESSION['admin_id']) || !isset($_SESSION['admin_role']) || $_SESSION['admin_role'] !== 'Manage Website') {
    header('Location: ../login.php');
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
    <title>View Registration - maJIStic Admin</title>
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
        .detail-item {
            padding: 10px 0;
            border-bottom: 1px solid #eee;
        }
        .detail-label {
            font-weight: 600;
            color: #555;
        }
        .detail-value {
            color: #333;
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
        .action-buttons {
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="registration-container">
        <div class="back-btn">
            <a href="index.php?page=<?php echo $type === 'student' ? 'student_registrations' : 'alumni_registrations'; ?>" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Back to List
            </a>
        </div>
        
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <?php echo $type === 'student' ? 'Student' : 'Alumni'; ?> Registration Details
                    <span class="badge bg-<?php echo $registration['payment_status'] == 'Paid' ? 'success' : 'warning'; ?> float-end">
                        <?php echo $registration['payment_status']; ?>
                    </span>
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="detail-item">
                            <div class="detail-label">JIS ID</div>
                            <div class="detail-value"><?php echo htmlspecialchars($registration['jis_id']); ?></div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="detail-item">
                            <div class="detail-label">Name</div>
                            <div class="detail-value"><?php echo htmlspecialchars($registration[$name_field]); ?></div>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="detail-item">
                            <div class="detail-label">Department</div>
                            <div class="detail-value"><?php echo htmlspecialchars($registration['department']); ?></div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <?php if ($type === 'alumni'): ?>
                        <div class="detail-item">
                            <div class="detail-label">Passout Year</div>
                            <div class="detail-value"><?php echo htmlspecialchars($registration['passout_year']); ?></div>
                        </div>
                        <?php else: ?>
                        <div class="detail-item">
                            <div class="detail-label">Gender</div>
                            <div class="detail-value"><?php echo htmlspecialchars($registration['gender']); ?></div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="detail-item">
                            <div class="detail-label">Email</div>
                            <div class="detail-value"><?php echo htmlspecialchars($registration['email']); ?></div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="detail-item">
                            <div class="detail-label">Mobile</div>
                            <div class="detail-value"><?php echo htmlspecialchars($registration['mobile']); ?></div>
                        </div>
                    </div>
                </div>
                
                <?php if ($type === 'student' && isset($registration['inhouse_competition']) && $registration['inhouse_competition'] === 'Yes'): ?>
                <div class="row">
                    <div class="col-md-6">
                        <div class="detail-item">
                            <div class="detail-label">Participating in In-house Competition</div>
                            <div class="detail-value">Yes</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="detail-item">
                            <div class="detail-label">Competition Name</div>
                            <div class="detail-value"><?php echo htmlspecialchars($registration['competition_name'] ?? 'Not specified'); ?></div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                
                <?php if ($type === 'alumni' && isset($registration['current_organization'])): ?>
                <div class="row">
                    <div class="col-md-12">
                        <div class="detail-item">
                            <div class="detail-label">Current Organization</div>
                            <div class="detail-value"><?php echo htmlspecialchars($registration['current_organization']); ?></div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="detail-item">
                            <div class="detail-label">Registration Date</div>
                            <div class="detail-value"><?php echo date('d M Y, h:i A', strtotime($registration['registration_date'])); ?></div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="detail-item">
                            <div class="detail-label">Payment Status</div>
                            <div class="detail-value">
                                <span class="badge bg-<?php echo $registration['payment_status'] == 'Paid' ? 'success' : 'warning'; ?>">
                                    <?php echo htmlspecialchars($registration['payment_status']); ?>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="action-buttons text-center">
                    <a href="edit_registration.php?type=<?php echo $type; ?>&id=<?php echo $id; ?>" class="btn btn-primary">
                        <i class="bi bi-pencil"></i> Edit Registration
                    </a>
                    
                    <?php if ($registration['payment_status'] !== 'Paid'): ?>
                    <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#markAsPaidModal">
                        <i class="bi bi-check-circle"></i> Mark as Paid
                    </button>
                    <?php else: ?>
                    <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#markAsUnpaidModal">
                        <i class="bi bi-x-circle"></i> Mark as Unpaid
                    </button>
                    <?php endif; ?>
                    
                    <a href="index.php?page=<?php echo $type === 'student' ? 'student_registrations' : 'alumni_registrations'; ?>" class="btn btn-secondary">
                        <i class="bi bi-arrow-left"></i> Back to List
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Mark as Paid Modal -->
    <div class="modal fade" id="markAsPaidModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirm Payment Status Update</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to mark this registration as Paid?</p>
                    <p><strong>JIS ID:</strong> <?php echo htmlspecialchars($registration['jis_id']); ?></p>
                    <p><strong>Name:</strong> <?php echo htmlspecialchars($registration[$name_field]); ?></p>
                </div>
                <div class="modal-footer">
                    <form method="post" action="update_payment_status.php">
                        <input type="hidden" name="type" value="<?php echo $type; ?>">
                        <input type="hidden" name="id" value="<?php echo $id; ?>">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="mark_as_paid" class="btn btn-success">Confirm Payment</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Mark as Unpaid Modal -->
    <div class="modal fade" id="markAsUnpaidModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirm Payment Status Update</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to mark this registration as <strong>Not Paid</strong>?</p>
                    <p><strong>JIS ID:</strong> <?php echo htmlspecialchars($registration['jis_id']); ?></p>
                    <p><strong>Name:</strong> <?php echo htmlspecialchars($registration[$name_field]); ?></p>
                </div>
                <div class="modal-footer">
                    <form method="post" action="update_payment_status.php">
                        <input type="hidden" name="type" value="<?php echo $type; ?>">
                        <input type="hidden" name="id" value="<?php echo $id; ?>">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="mark_as_unpaid" class="btn btn-danger">Confirm Unpaid</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
