<?php
// Include database connection
require_once '../includes/db_config.php';

// Handle role update through AJAX
if (isset($_POST['update_role']) && isset($_POST['jis_id']) && isset($_POST['role'])) {
    $jis_id = trim($_POST['jis_id']);
    $role = trim($_POST['role']);
    
    try {
        $stmt = $db->prepare("UPDATE registrations SET role = :role WHERE jis_id = :jis_id");
        $result = $stmt->execute([':role' => $role, ':jis_id' => $jis_id]);
        
        if ($result) {
            echo json_encode(['status' => 'success', 'message' => 'Role updated successfully']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to update role']);
        }
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
    }
    
    exit;
}

// Initialize search parameters
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 50; // Records per page
$offset = ($page - 1) * $limit;

try {
    // Build query based on search parameters
    $query = "SELECT jis_id, student_name, competition_name, role FROM registrations";
    $countQuery = "SELECT COUNT(*) as total FROM registrations";
    $params = [];
    
    if (!empty($search)) {
        $query .= " WHERE (student_name LIKE :search OR jis_id LIKE :search OR competition_name LIKE :search OR role LIKE :search)";
        $countQuery .= " WHERE (student_name LIKE :search OR jis_id LIKE :search OR competition_name LIKE :search OR role LIKE :search)";
        $params[':search'] = "%$search%";
    }
    
    // Fix: Use numeric literals instead of placeholders for LIMIT and OFFSET
    $query .= " ORDER BY student_name ASC LIMIT " . $limit . " OFFSET " . $offset;
    
    // Get total count for pagination
    $countStmt = $db->prepare($countQuery);
    if (!empty($search)) {
        $countStmt->bindValue(':search', "%$search%", PDO::PARAM_STR);
    }
    $countStmt->execute();
    $totalCount = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];
    $totalPages = ceil($totalCount / $limit);
    
    // Get registrations with corrected query
    $stmt = $db->prepare($query);
    foreach ($params as $param => $value) {
        $stmt->bindValue($param, $value, PDO::PARAM_STR);
    }
    $stmt->execute();
    $registrations = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    // Handle database errors gracefully
    $error = "Database error: " . $e->getMessage();
    // Log the error for debugging
    error_log("SQL Error in role management: " . $e->getMessage());
    // Initialize empty arrays to prevent undefined variable errors
    $registrations = [];
    $totalCount = 0;
    $totalPages = 0;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Role Management - MaJIStic 2k25</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background-color: #f5f5f5;
            padding: 20px;
        }
        .container {
            max-width: 1400px;
        }
        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        }
        .card-header {
            background: linear-gradient(135deg, #6a11cb, #2575fc);
            color: white;
            border-radius: 10px 10px 0 0 !important;
            font-weight: 600;
            padding: 15px 20px;
        }
        .table th {
            background-color: #f8f9fa;
        }
        .btn-role {
            border-radius: 50px;
            font-size: 0.8rem;
            padding: 0.3rem 0.8rem;
            margin: 0.2rem;
            white-space: nowrap;
        }
        .btn-volunteer {
            background-color: #28a745;
            color: white;
        }
        .btn-crew {
            background-color: #fd7e14;
            color: white;
        }
        .btn-remove-role {
            background-color: #dc3545;
            color: white;
        }
        .pagination {
            margin-bottom: 0;
        }
        .role-badge {
            display: inline-block;
            padding: 0.3rem 0.6rem;
            font-size: 0.75rem;
            font-weight: 600;
            border-radius: 50px;
            margin-right: 0.5rem;
        }
        .role-volunteer {
            background-color: rgba(40, 167, 69, 0.2);
            color: #28a745;
            border: 1px solid #28a745;
        }
        .role-crew {
            background-color: rgba(253, 126, 20, 0.2);
            color: #fd7e14;
            border: 1px solid #fd7e14;
        }
        .search-box {
            position: relative;
        }
        .search-box .form-control {
            padding-right: 40px;
            border-radius: 50px;
        }
        .search-box .search-btn {
            position: absolute;
            right: 5px;
            top: 5px;
            border: none;
            background: transparent;
            color: #6c757d;
        }
        .tooltip-inner {
            max-width: 300px;
        }
        .table-responsive {
            border-radius: 0 0 10px 10px;
            overflow: hidden;
        }
        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(255,255,255,0.8);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 9999;
            visibility: hidden;
            opacity: 0;
            transition: visibility 0s, opacity 0.3s linear;
        }
        .loading-spinner {
            width: 50px;
            height: 50px;
            border: 5px solid #f3f3f3;
            border-top: 5px solid #3498db;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="mb-0">Role Management</h3>
                <a href="../index.php" class="btn btn-light btn-sm"><i class="fas fa-home me-1"></i> Back to Home</a>
            </div>
            <div class="card-body">
                <!-- Display error message if any -->
                <?php if (isset($error)): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                </div>
                <?php endif; ?>
                
                <div class="row mb-3">
                    <div class="col-md-6 mb-3 mb-md-0">
                        <form action="" method="GET" id="searchForm">
                            <div class="search-box">
                                <input type="text" name="search" class="form-control" placeholder="Search by name, JIS ID, competition, or role..." value="<?php echo htmlspecialchars($search); ?>">
                                <button type="submit" class="search-btn"><i class="fas fa-search"></i></button>
                            </div>
                        </form>
                    </div>
                    <div class="col-md-6 text-md-end">
                        <div class="d-flex justify-content-md-end">
                            <span class="me-3">
                                <span class="badge bg-success">Volunteer</span> = Volunteer Role
                            </span>
                            <span>
                                <span class="badge bg-warning text-dark">Crew</span> = Crew Member Role
                            </span>
                        </div>
                    </div>
                </div>
                
                <?php if (!empty($search)): ?>
                <div class="alert alert-info">
                    Showing results for: <strong><?php echo htmlspecialchars($search); ?></strong> 
                    <a href="index.php" class="alert-link ms-2">Clear search</a>
                </div>
                <?php endif; ?>
                
                <div class="table-responsive">
                    <table class="table table-hover table-striped mb-0">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Student Name</th>
                                <th>JIS ID</th>
                                <th>Competition</th>
                                <th>Current Role</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($registrations)): ?>
                            <tr>
                                <td colspan="6" class="text-center py-4">
                                    <?php if (!empty($search)): ?>
                                        No registrations found matching your search.
                                    <?php elseif (isset($error)): ?>
                                        An error occurred while fetching registrations.
                                    <?php else: ?>
                                        No registrations available.
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php else: ?>
                                <?php $counter = ($page - 1) * $limit + 1; ?>
                                <?php foreach ($registrations as $reg): ?>
                                <tr>
                                    <td><?php echo $counter++; ?></td>
                                    <td><?php echo htmlspecialchars($reg['student_name']); ?></td>
                                    <td><?php echo htmlspecialchars($reg['jis_id']); ?></td>
                                    <td>
                                        <?php if (!empty($reg['competition_name'])): ?>
                                            <span class="text-truncate d-inline-block" style="max-width: 300px;" data-bs-toggle="tooltip" title="<?php echo htmlspecialchars($reg['competition_name']); ?>">
                                                <?php echo htmlspecialchars($reg['competition_name']); ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="text-muted fst-italic">None</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php
                                        $currentRole = strtolower($reg['role'] ?? '');
                                        if (strpos($currentRole, 'volunteer') !== false) {
                                            echo '<span class="role-badge role-volunteer">Volunteer</span>';
                                        }
                                        if (strpos($currentRole, 'crew') !== false) {
                                            echo '<span class="role-badge role-crew">Crew Member</span>';
                                        }
                                        if (empty($currentRole)) {
                                            echo '<span class="text-muted fst-italic">None</span>';
                                        }
                                        ?>
                                    </td>
                                    <td class="role-actions">
                                        <button class="btn btn-sm btn-role btn-volunteer" data-jisid="<?php echo htmlspecialchars($reg['jis_id']); ?>" data-role="volunteer">
                                            <i class="fas fa-user-plus me-1"></i>Volunteer
                                        </button>
                                        <button class="btn btn-sm btn-role btn-crew" data-jisid="<?php echo htmlspecialchars($reg['jis_id']); ?>" data-role="crew member">
                                            <i class="fas fa-users-gear me-1"></i>Crew
                                        </button>
                                        <button class="btn btn-sm btn-role btn-remove-role" data-jisid="<?php echo htmlspecialchars($reg['jis_id']); ?>" data-role="">
                                            <i class="fas fa-user-minus me-1"></i>Remove
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <small>Showing <?php echo count($registrations); ?> of <?php echo $totalCount; ?> registrations</small>
                    </div>
                    <?php if ($totalPages > 1): ?>
                    <nav>
                        <ul class="pagination">
                            <li class="page-item <?php echo ($page <= 1) ? 'disabled' : ''; ?>">
                                <a class="page-link" href="?page=<?php echo $page - 1; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>" aria-label="Previous">
                                    <span aria-hidden="true">&laquo;</span>
                                </a>
                            </li>
                            
                            <?php
                            $startPage = max(1, $page - 2);
                            $endPage = min($totalPages, $page + 2);
                            
                            if ($startPage > 1) {
                                echo '<li class="page-item"><a class="page-link" href="?page=1' . (!empty($search) ? '&search=' . urlencode($search) : '') . '">1</a></li>';
                                if ($startPage > 2) {
                                    echo '<li class="page-item disabled"><a class="page-link" href="#">...</a></li>';
                                }
                            }
                            
                            for ($i = $startPage; $i <= $endPage; $i++) {
                                echo '<li class="page-item ' . ($i == $page ? 'active' : '') . '"><a class="page-link" href="?page=' . $i . (!empty($search) ? '&search=' . urlencode($search) : '') . '">' . $i . '</a></li>';
                            }
                            
                            if ($endPage < $totalPages) {
                                if ($endPage < $totalPages - 1) {
                                    echo '<li class="page-item disabled"><a class="page-link" href="#">...</a></li>';
                                }
                                echo '<li class="page-item"><a class="page-link" href="?page=' . $totalPages . (!empty($search) ? '&search=' . urlencode($search) : '') . '">' . $totalPages . '</a></li>';
                            }
                            ?>
                            
                            <li class="page-item <?php echo ($page >= $totalPages) ? 'disabled' : ''; ?>">
                                <a class="page-link" href="?page=<?php echo $page + 1; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>" aria-label="Next">
                                    <span aria-hidden="true">&raquo;</span>
                                </a>
                            </li>
                        </ul>
                    </nav>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Loading overlay -->
    <div class="loading-overlay" id="loadingOverlay">
        <div class="loading-spinner"></div>
    </div>

    <!-- Toast for notifications -->
    <div class="position-fixed bottom-0 end-0 p-3" style="z-index: 11">
        <div id="notificationToast" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="toast-header">
                <i class="fas fa-bell me-2"></i>
                <strong class="me-auto">Notification</strong>
                <small>Just now</small>
                <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
            <div class="toast-body">
                Role updated successfully.
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Initialize tooltips
        document.addEventListener('DOMContentLoaded', function() {
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
            
            // Setup role buttons
            const roleButtons = document.querySelectorAll('.btn-role');
            roleButtons.forEach(button => {
                button.addEventListener('click', function() {
                    updateRole(this.dataset.jisid, this.dataset.role);
                });
            });
        });
        
        // Function to update role via AJAX
        function updateRole(jisId, role) {
            // Show loading overlay
            const loadingOverlay = document.getElementById('loadingOverlay');
            loadingOverlay.style.visibility = 'visible';
            loadingOverlay.style.opacity = '1';
            
            // Prepare form data
            const formData = new FormData();
            formData.append('update_role', '1');
            formData.append('jis_id', jisId);
            formData.append('role', role);
            
            // Send AJAX request
            fetch('index.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                // Hide loading overlay
                loadingOverlay.style.opacity = '0';
                setTimeout(() => {
                    loadingOverlay.style.visibility = 'hidden';
                }, 300);
                
                // Show toast notification
                const toast = document.getElementById('notificationToast');
                const toastBody = toast.querySelector('.toast-body');
                
                if (data.status === 'success') {
                    toastBody.textContent = data.message;
                    toast.classList.remove('bg-danger', 'text-white');
                    toast.classList.add('bg-success', 'text-white');
                    
                    // Update the UI to reflect the change
                    updateRoleDisplay(jisId, role);
                } else {
                    toastBody.textContent = data.message || 'An error occurred';
                    toast.classList.remove('bg-success', 'text-white');
                    toast.classList.add('bg-danger', 'text-white');
                }
                
                const bsToast = new bootstrap.Toast(toast);
                bsToast.show();
            })
            .catch(error => {
                console.error('Error:', error);
                
                // Hide loading overlay
                loadingOverlay.style.opacity = '0';
                setTimeout(() => {
                    loadingOverlay.style.visibility = 'hidden';
                }, 300);
                
                // Show error toast
                const toast = document.getElementById('notificationToast');
                const toastBody = toast.querySelector('.toast-body');
                toastBody.textContent = 'Failed to update role. Please try again.';
                toast.classList.remove('bg-success', 'text-white');
                toast.classList.add('bg-danger', 'text-white');
                
                const bsToast = new bootstrap.Toast(toast);
                bsToast.show();
            });
        }
        
        // Update the UI to reflect role changes
        function updateRoleDisplay(jisId, role) {
            // Find the row with the matching JIS ID
            const row = document.querySelector(`button[data-jisid="${jisId}"]`).closest('tr');
            const roleCell = row.querySelector('td:nth-child(5)');
            
            // Clear existing role badges
            roleCell.innerHTML = '';
            
            // Add appropriate badges based on the new role
            if (role === 'volunteer') {
                roleCell.innerHTML = '<span class="role-badge role-volunteer">Volunteer</span>';
            } else if (role === 'crew member') {
                roleCell.innerHTML = '<span class="role-badge role-crew">Crew Member</span>';
            } else if (role === '') {
                roleCell.innerHTML = '<span class="text-muted fst-italic">None</span>';
            }
            
            // Refresh the page after a short delay to ensure accurate display
            setTimeout(() => {
                window.location.reload();
            }, 1500);
        }
    </script>
</body>
</html>
