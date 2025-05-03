<?php
session_start();
require_once '../../includes/db_config.php';

// Check if user is logged in with admin privileges
if (!isset($_SESSION['admin_id']) || !isset($_SESSION['admin_role']) || 
    ($_SESSION['admin_role'] !== 'Manage Website' && 
     $_SESSION['admin_role'] !== 'Controller' && 
     $_SESSION['admin_role'] !== 'Super Admin')) {
    header('Location: ../login.php');
    exit;
}

// Get sorting parameters
$sort_column = isset($_GET['sort']) ? $_GET['sort'] : 'payment_update_timestamp';
$sort_order = isset($_GET['order']) ? $_GET['order'] : 'desc';

// Validate sort column to prevent SQL injection
$allowed_columns = ['name', 'jis_id', 'department', 'mobile', 'receipt_number', 'payment_update_timestamp', 'payment_updated_by'];
if (!in_array($sort_column, $allowed_columns)) {
    $sort_column = 'payment_update_timestamp';
}

// Validate sort order
if ($sort_order != 'asc' && $sort_order != 'desc') {
    $sort_order = 'desc';
}

// Fetch all paid registrations from both tables
try {
    // Student registrations
    $stmt1 = $db->prepare("SELECT id, student_name AS name, jis_id, mobile, payment_status, 
                           receipt_number, payment_updated_by, payment_update_timestamp, 
                           'student' AS type, department
                           FROM registrations 
                           WHERE payment_status = 'Paid'");
    $stmt1->execute();
    $student_registrations = $stmt1->fetchAll(PDO::FETCH_ASSOC);

    // Alumni registrations
    $stmt2 = $db->prepare("SELECT id, alumni_name AS name, jis_id, mobile, payment_status, 
                           receipt_number, payment_updated_by, payment_update_timestamp, 
                           'alumni' AS type, department
                           FROM alumni_registrations 
                           WHERE payment_status = 'Paid'");
    $stmt2->execute();
    $alumni_registrations = $stmt2->fetchAll(PDO::FETCH_ASSOC);

    // Combine results
    $registrations = array_merge($student_registrations, $alumni_registrations);

    // Sort by the selected column and order
    usort($registrations, function($a, $b) use ($sort_column, $sort_order) {
        // Special handling for timestamps
        if ($sort_column == 'payment_update_timestamp') {
            $timeA = strtotime($a[$sort_column] ?? '1970-01-01');
            $timeB = strtotime($b[$sort_column] ?? '1970-01-01');
            
            return $sort_order === 'asc' ? $timeA - $timeB : $timeB - $timeA;
        }
        
        // For other columns - case insensitive string comparison
        $valA = strtolower($a[$sort_column] ?? '');
        $valB = strtolower($b[$sort_column] ?? '');
        
        if ($valA == $valB) {
            return 0;
        }
        
        if ($sort_order === 'asc') {
            return $valA < $valB ? -1 : 1;
        } else {
            return $valA > $valB ? -1 : 1;
        }
    });

} catch (PDOException $e) {
    $error_message = "Database error: " . $e->getMessage();
    $registrations = [];
}

// Function to generate sort URL
function getSortUrl($column) {
    global $sort_column, $sort_order;
    
    $new_order = ($sort_column == $column && $sort_order == 'asc') ? 'desc' : 'asc';
    
    // Preserve existing query parameters except sort and order
    $params = $_GET;
    $params['sort'] = $column;
    $params['order'] = $new_order;
    
    return '?' . http_build_query($params);
}

// Function to get sort indicator
function getSortIndicator($column) {
    global $sort_column, $sort_order;
    
    if ($sort_column != $column) {
        return '<i class="bi bi-sort-alpha-down text-muted opacity-50"></i>';
    }
    
    return ($sort_order == 'asc') 
        ? '<i class="bi bi-sort-alpha-up"></i>' 
        : '<i class="bi bi-sort-alpha-down"></i>';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Paid Registrations - maJIStic 2K25</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        .table-responsive {
            overflow-x: auto;
        }
        .search-filters {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .department-tag {
            font-size: 85%;
            padding: 0.2em 0.6em;
        }
        .registration-type {
            font-weight: 500;
        }
        .sortable {
            cursor: pointer;
        }
        .sortable:hover {
            background-color: rgba(0,0,0,0.05);
        }
        .sort-icon {
            display: inline-block;
            width: 16px;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="container-fluid py-4">
        <div class="row mb-4">
            <div class="col-md-12">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h2><i class="bi bi-cash-coin me-2"></i>Paid Registrations</h2>
                    <a href="index.php" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left me-1"></i> Back to Dashboard
                    </a>
                </div>
                
                <?php if (isset($error_message)): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?php echo $error_message; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php endif; ?>

                <div class="card">
                    <div class="card-body">
                        <div class="search-filters mb-3">
                            <div class="row">
                                <div class="col-md-4 mb-2">
                                    <input type="text" id="searchInput" class="form-control" placeholder="Search by name, JIS ID, receipt number...">
                                </div>
                                <div class="col-md-3 mb-2">
                                    <select id="departmentFilter" class="form-select">
                                        <option value="">All Departments</option>
                                        <option value="CSE">CSE</option>
                                        <option value="CSE AI-ML">CSE AI-ML</option>
                                        <option value="ECE">ECE</option>
                                        <option value="EE">EE</option>
                                        <option value="BME">BME</option>
                                        <option value="CE">CE</option>
                                        <option value="ME">ME</option>
                                        <option value="MCA">MCA</option>
                                        <option value="MBA">MBA</option>
                                        <option value="BBA">BBA</option>
                                    </select>
                                </div>
                                <div class="col-md-3 mb-2">
                                    <select id="typeFilter" class="form-select">
                                        <option value="">All Types</option>
                                        <option value="student">Students</option>
                                        <option value="alumni">Alumni</option>
                                    </select>
                                </div>
                                <div class="col-md-2 mb-2">
                                    <button id="resetFilters" class="btn btn-secondary w-100">Reset</button>
                                </div>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-striped table-hover" id="registrationsTable">
                                <thead class="table-dark">
                                    <tr>
                                        <th class="sortable" onclick="window.location.href='<?php echo getSortUrl('name'); ?>'">
                                            Name <span class="sort-icon"><?php echo getSortIndicator('name'); ?></span>
                                        </th>
                                        <th class="sortable" onclick="window.location.href='<?php echo getSortUrl('jis_id'); ?>'">
                                            JIS ID <span class="sort-icon"><?php echo getSortIndicator('jis_id'); ?></span>
                                        </th>
                                        <th class="sortable" onclick="window.location.href='<?php echo getSortUrl('department'); ?>'">
                                            Department <span class="sort-icon"><?php echo getSortIndicator('department'); ?></span>
                                        </th>
                                        <th class="sortable" onclick="window.location.href='<?php echo getSortUrl('mobile'); ?>'">
                                            Mobile <span class="sort-icon"><?php echo getSortIndicator('mobile'); ?></span>
                                        </th>
                                        <th class="sortable" onclick="window.location.href='<?php echo getSortUrl('receipt_number'); ?>'">
                                            Receipt No. <span class="sort-icon"><?php echo getSortIndicator('receipt_number'); ?></span>
                                        </th>
                                        <th class="sortable" onclick="window.location.href='<?php echo getSortUrl('payment_update_timestamp'); ?>'">
                                            Payment Updated <span class="sort-icon"><?php echo getSortIndicator('payment_update_timestamp'); ?></span>
                                        </th>
                                        <th class="sortable" onclick="window.location.href='<?php echo getSortUrl('payment_updated_by'); ?>'">
                                            Updated By <span class="sort-icon"><?php echo getSortIndicator('payment_updated_by'); ?></span>
                                        </th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($registrations)): ?>
                                    <tr>
                                        <td colspan="8" class="text-center py-4"></td></td>
                                            <i class="bi bi-inbox text-muted" style="font-size: 2rem;"></i>
                                            <p class="text-muted mt-2">No paid registrations found</p>
                                        </td>
                                    </tr>
                                    <?php else: ?>
                                    <?php foreach ($registrations as $reg): ?>
                                    <tr data-type="<?php echo $reg['type']; ?>" data-department="<?php echo $reg['department']; ?>">
                                        <td>
                                            <?php echo htmlspecialchars($reg['name']); ?>
                                            <br>
                                            <span class="badge bg-<?php echo $reg['type'] === 'student' ? 'info' : 'warning'; ?> registration-type">
                                                <?php echo ucfirst($reg['type']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo htmlspecialchars($reg['jis_id']); ?></td>
                                        <td>
                                            <span class="badge bg-secondary department-tag">
                                                <?php echo htmlspecialchars($reg['department']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo htmlspecialchars($reg['mobile']); ?></td>
                                        <td><?php echo htmlspecialchars($reg['receipt_number'] ?? 'N/A'); ?></td>
                                        <td>
                                            <?php if (!empty($reg['payment_update_timestamp'])): ?>
                                                <?php echo date('d M Y, h:i A', strtotime($reg['payment_update_timestamp'])); ?>
                                            <?php else: ?>
                                                <span class="text-muted">N/A</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo htmlspecialchars($reg['payment_updated_by'] ?? 'N/A'); ?></td>
                                        <td>
                                            <button class="btn btn-sm btn-primary view-details" 
                                                data-id="<?php echo $reg['id']; ?>" 
                                                data-type="<?php echo $reg['type']; ?>">
                                                <i class="bi bi-eye"></i> View
                                            </button>
                                            <?php if ($_SESSION['admin_role'] === 'Manage Website'): ?>
                                            <a href="edit_registration.php?id=<?php echo $reg['id']; ?>&type=<?php echo $reg['type']; ?>" 
                                               class="btn btn-sm btn-outline-secondary">
                                                <i class="bi bi-pencil"></i> Edit
                                            </a>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <div class="mt-3 d-flex justify-content-between align-items-center">
                            <p class="text-muted"><small>Showing <?php echo count($registrations); ?> paid registrations</small></p>
                            <a href="export_paid_registrations.php" class="btn btn-success">
                                <i class="bi bi-file-excel me-1"></i> Export to Excel
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Registration Details Modal -->
    <div class="modal fade" id="registrationDetailsModal" tabindex="-1" aria-labelledby="registrationDetailsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="registrationDetailsModalLabel">Registration Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="text-center py-4">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-2">Loading registration details...</p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        $(document).ready(function() {
            // Search functionality
            $('#searchInput').on('keyup', function() {
                const value = $(this).val().toLowerCase();
                applyFilters();
            });

            // Department filter
            $('#departmentFilter').on('change', function() {
                applyFilters();
            });

            // Type filter
            $('#typeFilter').on('change', function() {
                applyFilters();
            });

            // Reset filters
            $('#resetFilters').on('click', function() {
                $('#searchInput').val('');
                $('#departmentFilter').val('');
                $('#typeFilter').val('');
                $("#registrationsTable tbody tr").show();
            });

            // View details
            $('.view-details').on('click', function() {
                const id = $(this).data('id');
                const type = $(this).data('type');
                
                $('#registrationDetailsModal').modal('show');
                
                // Fetch registration details using AJAX
                $.ajax({
                    url: 'get_registration.php',
                    type: 'GET',
                    data: {
                        id: id,
                        type: type
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.status === 'success') {
                            $('#registrationDetailsModal .modal-body').html(response.html);
                        } else {
                            $('#registrationDetailsModal .modal-body').html(
                                '<div class="alert alert-danger">' + response.message + '</div>'
                            );
                        }
                    },
                    error: function() {
                        $('#registrationDetailsModal .modal-body').html(
                            '<div class="alert alert-danger">Failed to load registration details. Please try again.</div>'
                        );
                    }
                });
            });

            // Function to apply all filters
            function applyFilters() {
                const searchValue = $('#searchInput').val().toLowerCase();
                const departmentValue = $('#departmentFilter').val();
                const typeValue = $('#typeFilter').val();
                
                $("#registrationsTable tbody tr").each(function() {
                    const rowText = $(this).text().toLowerCase();
                    const department = $(this).data('department');
                    const type = $(this).data('type');
                    
                    const searchMatch = rowText.indexOf(searchValue) > -1;
                    const departmentMatch = !departmentValue || department === departmentValue;
                    const typeMatch = !typeValue || type === typeValue;
                    
                    if (searchMatch && departmentMatch && typeMatch) {
                        $(this).show();
                    } else {
                        $(this).hide();
                    }
                });
            }
        });
    </script>
</body>
</html>
