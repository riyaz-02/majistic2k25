<?php
// This file is included by index.php and should not be accessed directly
if (!isset($db) || !isset($_SESSION)) {
    exit('This file cannot be accessed directly.');
}

// Initialize variables for pagination
$current_page = isset($_GET['subpage']) ? (int)$_GET['subpage'] : 1;
$records_per_page = 15;
$offset = ($current_page - 1) * $records_per_page;

// Set default registration type filter
$reg_type = isset($_GET['type']) ? $_GET['type'] : 'all';

// Get sort parameters (default: sort by jis_id ASC)
$sort_column = isset($_GET['sort']) ? $_GET['sort'] : 'jis_id';
$sort_direction = isset($_GET['dir']) ? $_GET['dir'] : 'asc';

// Validate sort column to prevent SQL injection
$allowed_columns = ['jis_id', 'name', 'department', 'mobile', 'registration_date', 'payment_status'];
if (!in_array($sort_column, $allowed_columns)) {
    $sort_column = 'jis_id';
}

// Validate sort direction
$sort_direction = strtolower($sort_direction) === 'desc' ? 'desc' : 'asc';

// Build search condition for both tables
$search_condition_student = '';
$search_condition_alumni = '';
$search_params = [];

if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search_term = '%' . $_GET['search'] . '%';
    $search_condition_student = " WHERE student_name LIKE :search OR jis_id LIKE :search OR email LIKE :search OR mobile LIKE :search OR department LIKE :search";
    $search_condition_alumni = " WHERE alumni_name LIKE :search OR jis_id LIKE :search OR email LIKE :search OR mobile LIKE :search OR department LIKE :search";
    $search_params[':search'] = $search_term;
}

// Add payment status filter if set
if (isset($_GET['payment_status']) && !empty($_GET['payment_status'])) {
    $param_name = ':payment_status';
    if (empty($search_condition_student)) {
        $search_condition_student = " WHERE payment_status = $param_name";
        $search_condition_alumni = " WHERE payment_status = $param_name";
    } else {
        $search_condition_student .= " AND payment_status = $param_name";
        $search_condition_alumni .= " AND payment_status = $param_name";
    }
    $search_params[$param_name] = $_GET['payment_status'];
}

// Add department filter if set
if (isset($_GET['department']) && !empty($_GET['department'])) {
    $param_name = ':department';
    if (empty($search_condition_student)) {
        $search_condition_student = " WHERE department = $param_name";
        $search_condition_alumni = " WHERE department = $param_name";
    } else {
        $search_condition_student .= " AND department = $param_name";
        $search_condition_alumni .= " AND department = $param_name";
    }
    $search_params[$param_name] = $_GET['department'];
}

// Get combined registrations or filtered by type
try {
    $total_records = 0;
    $registrations = [];

    if ($reg_type === 'all' || $reg_type === 'student') {
        // Count student registrations
        $count_query = "SELECT COUNT(*) FROM registrations$search_condition_student";
        $count_stmt = $db->prepare($count_query);
        
        if (!empty($search_params)) {
            foreach ($search_params as $param => $value) {
                $count_stmt->bindValue($param, $value);
            }
        }
        
        $count_stmt->execute();
        $student_count = $count_stmt->fetchColumn();
        
        if ($reg_type === 'all') {
            $total_records += $student_count;
        } else {
            $total_records = $student_count;
        }
    }

    if ($reg_type === 'all' || $reg_type === 'alumni') {
        // Count alumni registrations
        $count_query = "SELECT COUNT(*) FROM alumni_registrations$search_condition_alumni";
        $count_stmt = $db->prepare($count_query);
        
        if (!empty($search_params)) {
            foreach ($search_params as $param => $value) {
                $count_stmt->bindValue($param, $value);
            }
        }
        
        $count_stmt->execute();
        $alumni_count = $count_stmt->fetchColumn();
        
        if ($reg_type === 'all') {
            $total_records += $alumni_count;
        } else {
            $total_records = $alumni_count;
        }
    }

    // Calculate total pages
    $total_pages = ceil($total_records / $records_per_page);

    // Fetch registrations based on type filter
    if ($reg_type === 'all') {
        // For combined view, we need to adjust pagination
        if ($current_page > $total_pages) {
            $current_page = 1;
        }
        
        // Calculate new offset
        $offset = ($current_page - 1) * $records_per_page;
        
        // Build queries with UNION to combine results
        $query = "(SELECT id, 'student' AS reg_type, jis_id, student_name AS name, department, mobile, registration_date, payment_status 
                  FROM registrations$search_condition_student)
                 UNION ALL
                 (SELECT id, 'alumni' AS reg_type, jis_id, alumni_name AS name, department, mobile, registration_date, payment_status 
                  FROM alumni_registrations$search_condition_alumni)
                 ORDER BY $sort_column $sort_direction
                 LIMIT :offset, :limit";
                 
        $stmt = $db->prepare($query);
        
        if (!empty($search_params)) {
            foreach ($search_params as $param => $value) {
                $stmt->bindValue($param, $value);
            }
        }
        
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $records_per_page, PDO::PARAM_INT);
        $stmt->execute();
        
        $registrations = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } elseif ($reg_type === 'student') {
        // Get only student registrations
        $query = "SELECT id, 'student' AS reg_type, jis_id, student_name AS name, department, mobile, registration_date, payment_status 
                 FROM registrations$search_condition_student
                 ORDER BY $sort_column $sort_direction
                 LIMIT :offset, :limit";
                 
        $stmt = $db->prepare($query);
        
        if (!empty($search_params)) {
            foreach ($search_params as $param => $value) {
                $stmt->bindValue($param, $value);
            }
        }
        
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $records_per_page, PDO::PARAM_INT);
        $stmt->execute();
        
        $registrations = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } elseif ($reg_type === 'alumni') {
        // Get only alumni registrations
        $query = "SELECT id, 'alumni' AS reg_type, jis_id, alumni_name AS name, department, mobile, registration_date, payment_status 
                 FROM alumni_registrations$search_condition_alumni
                 ORDER BY $sort_column $sort_direction
                 LIMIT :offset, :limit";
                 
        $stmt = $db->prepare($query);
        
        if (!empty($search_params)) {
            foreach ($search_params as $param => $value) {
                $stmt->bindValue($param, $value);
            }
        }
        
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $records_per_page, PDO::PARAM_INT);
        $stmt->execute();
        
        $registrations = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (PDOException $e) {
    $error_message = "Error fetching registrations: " . $e->getMessage();
    $registrations = [];
    $total_records = 0;
    $total_pages = 1;
}

// Get unique departments for filter dropdown
try {
    $dept_query = "SELECT DISTINCT department FROM (
                     SELECT department FROM registrations
                     UNION
                     SELECT department FROM alumni_registrations
                   ) AS combined_depts
                   ORDER BY department";
    $dept_stmt = $db->prepare($dept_query);
    $dept_stmt->execute();
    $departments = $dept_stmt->fetchAll(PDO::FETCH_COLUMN);
} catch (PDOException $e) {
    $departments = [];
}

// Process bulk actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['bulk_action'])) {
    if (isset($_POST['selected_ids']) && !empty($_POST['selected_ids']) && isset($_POST['selected_types'])) {
        $selected_ids = $_POST['selected_ids'];
        $selected_types = $_POST['selected_types'];
        $action = $_POST['bulk_action'];
        
        // Force arrays for single selections
        if (!is_array($selected_ids)) {
            $selected_ids = [$selected_ids];
        }
        if (!is_array($selected_types)) {
            $selected_types = [$selected_types];
        }
        
        // Debug log what we received
        error_log("Processing action: $action");
        error_log("Selected IDs: " . print_r($selected_ids, true));
        error_log("Selected Types: " . print_r($selected_types, true));
        
        // Group IDs by registration type
        $grouped_ids = [];
        foreach ($selected_ids as $index => $id) {
            if (isset($selected_types[$index])) {
                $type = $selected_types[$index];
                if (!isset($grouped_ids[$type])) {
                    $grouped_ids[$type] = [];
                }
                $grouped_ids[$type][] = $id;
            }
        }
        
        // Debug log grouped IDs
        error_log("Grouped IDs: " . print_r($grouped_ids, true));
        
        // Process each group separately
        $success_count = 0;
        
        if ($action === 'mark_paid') {
            // Update payment status for each type
            foreach ($grouped_ids as $type => $ids) {
                if (empty($ids)) continue;
                
                $table = ($type === 'student') ? 'registrations' : 'alumni_registrations';
                $placeholders = implode(',', array_fill(0, count($ids), '?'));
                $update_query = "UPDATE $table SET payment_status = 'Paid' WHERE id IN ($placeholders)";
                $update_stmt = $db->prepare($update_query);
                
                foreach ($ids as $index => $id) {
                    $update_stmt->bindValue($index + 1, $id, PDO::PARAM_INT);
                }
                
                $update_stmt->execute();
                $success_count += $update_stmt->rowCount();
                
                // Debug log the query results
                error_log("Updated $type payments: " . $update_stmt->rowCount());
            }
            
            $_SESSION['success_message'] = "Successfully marked $success_count registrations as Paid.";
        } elseif ($action === 'delete') {
            // Delete registrations for each type
            foreach ($grouped_ids as $type => $ids) {
                if (empty($ids)) continue;
                
                $table = ($type === 'student') ? 'registrations' : 'alumni_registrations';
                $placeholders = implode(',', array_fill(0, count($ids), '?'));
                $delete_query = "DELETE FROM $table WHERE id IN ($placeholders)";
                $delete_stmt = $db->prepare($delete_query);
                
                foreach ($ids as $index => $id) {
                    $delete_stmt->bindValue($index + 1, $id, PDO::PARAM_INT);
                }
                
                $delete_stmt->execute();
                $success_count += $delete_stmt->rowCount();
                
                // Debug log the query results
                error_log("Deleted $type records: " . $delete_stmt->rowCount());
            }
            
            $_SESSION['success_message'] = "Successfully deleted $success_count registrations.";
        }
        
        // Force redirect to refresh the page
        $_SESSION['redirect_url'] = "index.php?page=all_registrations&type=$reg_type";
    } else {
        $_SESSION['error_message'] = "No registrations selected for bulk action.";
    }
}
?>

<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">All Registrations</h5>
        <span class="badge bg-primary"><?php echo number_format($total_records); ?> Total</span>
    </div>
    <div class="card-body">
        <!-- Excel Export Button -->
        <div class="d-flex justify-content-end mb-3">
            <a href="../control/filtered_excel_export.php?<?php 
                $params = [];
                if(isset($_GET['type']) && !empty($_GET['type'])) $params[] = 'type=' . urlencode($_GET['type']);
                if(isset($_GET['search']) && !empty($_GET['search'])) $params[] = 'search=' . urlencode($_GET['search']);
                if(isset($_GET['payment_status']) && !empty($_GET['payment_status'])) $params[] = 'payment_status=' . urlencode($_GET['payment_status']);
                if(isset($_GET['department']) && !empty($_GET['department'])) $params[] = 'department=' . urlencode($_GET['department']);
                if(isset($_GET['sort']) && !empty($_GET['sort'])) $params[] = 'sort=' . urlencode($_GET['sort']);
                if(isset($_GET['dir']) && !empty($_GET['dir'])) $params[] = 'dir=' . urlencode($_GET['dir']);
                echo implode('&', $params);
            ?>" class="btn btn-success">
                <i class="bi bi-file-earmark-excel me-2"></i> Export to Excel
            </a>
        </div>
        
        <!-- Search & Filter Form -->
        <form method="get" class="row g-3 mb-4">
            <input type="hidden" name="page" value="all_registrations">
            
            <!-- Keep sort parameters when filters change -->
            <?php if(isset($_GET['sort'])): ?>
                <input type="hidden" name="sort" value="<?php echo htmlspecialchars($_GET['sort']); ?>">
            <?php endif; ?>
            <?php if(isset($_GET['dir'])): ?>
                <input type="hidden" name="dir" value="<?php echo htmlspecialchars($_GET['dir']); ?>">
            <?php endif; ?>
            
            <div class="col-md-3">
                <div class="input-group">
                    <input type="text" class="form-control" name="search" placeholder="Search..." value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                    <button class="btn btn-outline-primary" type="submit"><i class="bi bi-search"></i></button>
                </div>
            </div>
            
            <div class="col-md-2">
                <select class="form-select" name="type" onchange="this.form.submit()">
                    <option value="all" <?php echo $reg_type === 'all' ? 'selected' : ''; ?>>All Registrations</option>
                    <option value="student" <?php echo $reg_type === 'student' ? 'selected' : ''; ?>>Students Only</option>
                    <option value="alumni" <?php echo $reg_type === 'alumni' ? 'selected' : ''; ?>>Alumni Only</option>
                </select>
            </div>
            
            <div class="col-md-2">
                <select class="form-select" name="payment_status" onchange="this.form.submit()">
                    <option value="">-- Payment Status --</option>
                    <option value="Paid" <?php echo (isset($_GET['payment_status']) && $_GET['payment_status'] === 'Paid') ? 'selected' : ''; ?>>Paid</option>
                    <option value="Not Paid" <?php echo (isset($_GET['payment_status']) && $_GET['payment_status'] === 'Not Paid') ? 'selected' : ''; ?>>Not Paid</option>
                </select>
            </div>
            
            <div class="col-md-3">
                <select class="form-select" name="department" onchange="this.form.submit()">
                    <option value="">-- All Departments --</option>
                    <?php foreach ($departments as $dept): ?>
                        <option value="<?php echo htmlspecialchars($dept); ?>" <?php echo (isset($_GET['department']) && $_GET['department'] === $dept) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($dept); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="col-md-2">
                <a href="?page=all_registrations" class="btn btn-outline-secondary w-100">
                    <i class="bi bi-x-circle"></i> Clear Filters
                </a>
            </div>
        </form>
        
        <?php if (!empty($registrations)): ?>
        <!-- Bulk Actions Form -->
        <form method="post" id="bulkActionForm">
            <div class="row mb-3">
                <div class="col-md-6">
                    <div class="input-group">
                        <select class="form-select" name="bulk_action" id="bulk_action">
                            <option value="">-- Select Bulk Action --</option>
                            <option value="mark_paid">Mark as Paid</option>
                            <option value="delete">Delete Selected</option>
                        </select>
                        <button type="submit" class="btn btn-primary" id="applyBulkAction" disabled>Apply</button>
                    </div>
                </div>
            </div>
            
            <!-- Combined Registrations Table -->
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th width="40px">
                                <input class="form-check-input" type="checkbox" id="selectAll">
                            </th>
                            <th>Type</th>
                            <th>
                                <a href="?page=all_registrations&type=<?php echo $reg_type; ?>&sort=jis_id&dir=<?php echo ($sort_column == 'jis_id' && $sort_direction == 'asc') ? 'desc' : 'asc'; ?><?php echo isset($_GET['search']) ? '&search='.urlencode($_GET['search']) : ''; ?><?php echo isset($_GET['payment_status']) ? '&payment_status='.urlencode($_GET['payment_status']) : ''; ?><?php echo isset($_GET['department']) ? '&department='.urlencode($_GET['department']) : ''; ?>" class="text-dark text-decoration-none">
                                    JIS ID
                                    <?php if($sort_column == 'jis_id'): ?>
                                        <i class="bi bi-arrow-<?php echo $sort_direction == 'asc' ? 'up' : 'down'; ?>"></i>
                                    <?php endif; ?>
                                </a>
                            </th>
                            <th>
                                <a href="?page=all_registrations&type=<?php echo $reg_type; ?>&sort=name&dir=<?php echo ($sort_column == 'name' && $sort_direction == 'asc') ? 'desc' : 'asc'; ?><?php echo isset($_GET['search']) ? '&search='.urlencode($_GET['search']) : ''; ?><?php echo isset($_GET['payment_status']) ? '&payment_status='.urlencode($_GET['payment_status']) : ''; ?><?php echo isset($_GET['department']) ? '&department='.urlencode($_GET['department']) : ''; ?>" class="text-dark text-decoration-none">
                                    Name
                                    <?php if($sort_column == 'name'): ?>
                                        <i class="bi bi-arrow-<?php echo $sort_direction == 'asc' ? 'up' : 'down'; ?>"></i>
                                    <?php endif; ?>
                                </a>
                            </th>
                            <th>
                                <a href="?page=all_registrations&type=<?php echo $reg_type; ?>&sort=department&dir=<?php echo ($sort_column == 'department' && $sort_direction == 'asc') ? 'desc' : 'asc'; ?><?php echo isset($_GET['search']) ? '&search='.urlencode($_GET['search']) : ''; ?><?php echo isset($_GET['payment_status']) ? '&payment_status='.urlencode($_GET['payment_status']) : ''; ?><?php echo isset($_GET['department']) ? '&department='.urlencode($_GET['department']) : ''; ?>" class="text-dark text-decoration-none">
                                    Department
                                    <?php if($sort_column == 'department'): ?>
                                        <i class="bi bi-arrow-<?php echo $sort_direction == 'asc' ? 'up' : 'down'; ?>"></i>
                                    <?php endif; ?>
                                </a>
                            </th>
                            <th>
                                <a href="?page=all_registrations&type=<?php echo $reg_type; ?>&sort=mobile&dir=<?php echo ($sort_column == 'mobile' && $sort_direction == 'asc') ? 'desc' : 'asc'; ?><?php echo isset($_GET['search']) ? '&search='.urlencode($_GET['search']) : ''; ?><?php echo isset($_GET['payment_status']) ? '&payment_status='.urlencode($_GET['payment_status']) : ''; ?><?php echo isset($_GET['department']) ? '&department='.urlencode($_GET['department']) : ''; ?>" class="text-dark text-decoration-none">
                                    Mobile
                                    <?php if($sort_column == 'mobile'): ?>
                                        <i class="bi bi-arrow-<?php echo $sort_direction == 'asc' ? 'up' : 'down'; ?>"></i>
                                    <?php endif; ?>
                                </a>
                            </th>
                            <th>
                                <a href="?page=all_registrations&type=<?php echo $reg_type; ?>&sort=registration_date&dir=<?php echo ($sort_column == 'registration_date' && $sort_direction == 'asc') ? 'desc' : 'asc'; ?><?php echo isset($_GET['search']) ? '&search='.urlencode($_GET['search']) : ''; ?><?php echo isset($_GET['payment_status']) ? '&payment_status='.urlencode($_GET['payment_status']) : ''; ?><?php echo isset($_GET['department']) ? '&department='.urlencode($_GET['department']) : ''; ?>" class="text-dark text-decoration-none">
                                    Registration Date
                                    <?php if($sort_column == 'registration_date'): ?>
                                        <i class="bi bi-arrow-<?php echo $sort_direction == 'asc' ? 'up' : 'down'; ?>"></i>
                                    <?php endif; ?>
                                </a>
                            </th>
                            <th>
                                <a href="?page=all_registrations&type=<?php echo $reg_type; ?>&sort=payment_status&dir=<?php echo ($sort_column == 'payment_status' && $sort_direction == 'asc') ? 'desc' : 'asc'; ?><?php echo isset($_GET['search']) ? '&search='.urlencode($_GET['search']) : ''; ?><?php echo isset($_GET['payment_status']) ? '&payment_status='.urlencode($_GET['payment_status']) : ''; ?><?php echo isset($_GET['department']) ? '&department='.urlencode($_GET['department']) : ''; ?>" class="text-dark text-decoration-none">
                                    Payment Status
                                    <?php if($sort_column == 'payment_status'): ?>
                                        <i class="bi bi-arrow-<?php echo $sort_direction == 'asc' ? 'up' : 'down'; ?>"></i>
                                    <?php endif; ?>
                                </a>
                            </th>
                            <th width="120px">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($registrations as $reg): 
                        $reg_id = $reg['id'];
                        $reg_type_label = $reg['reg_type'];
                        $name = htmlspecialchars($reg['name']);
                        $jis_id = htmlspecialchars($reg['jis_id']);
                        $department = htmlspecialchars($reg['department']);
                        $mobile = htmlspecialchars($reg['mobile']);
                        $reg_date = date('d M Y, h:i A', strtotime($reg['registration_date']));
                        $payment_status = $reg['payment_status'];
                        $status_badge = $payment_status === 'Paid' ? 'success' : 'warning';
                    ?>
                        <tr>
                            <td>
                                <input type="checkbox" class="form-check-input registration-checkbox" name="selected_ids[]" value="<?php echo $reg_id; ?>">
                                <input type="hidden" name="selected_types[]" value="<?php echo $reg_type_label; ?>">
                            </td>
                            <td>
                                <span class="badge bg-<?php echo $reg_type_label === 'student' ? 'info' : 'secondary'; ?>">
                                    <?php echo ucfirst($reg_type_label); ?>
                                </span>
                            </td>
                            <td><?php echo $jis_id; ?></td>
                            <td><?php echo $name; ?></td>
                            <td><?php echo $department; ?></td>
                            <td><?php echo $mobile; ?></td>
                            <td><?php echo $reg_date; ?></td>
                            <td><span class="badge bg-<?php echo $status_badge; ?>"><?php echo $payment_status; ?></span></td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <button type="button" class="btn btn-info view-btn" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#viewModal"
                                            data-id="<?php echo $reg_id; ?>"
                                            data-type="<?php echo $reg_type_label; ?>"
                                            title="View Details">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                    <button type="button" class="btn btn-primary edit-btn"
                                            data-bs-toggle="modal" 
                                            data-bs-target="#editModal"
                                            data-id="<?php echo $reg_id; ?>"
                                            data-type="<?php echo $reg_type_label; ?>"
                                            title="Edit Registration">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <?php if ($payment_status !== 'Paid'): ?>
                                    <button type="button" class="btn btn-success mark-paid-btn"
                                            data-bs-toggle="modal" 
                                            data-bs-target="#markPaidModal"
                                            data-id="<?php echo $reg_id; ?>"
                                            data-type="<?php echo $reg_type_label; ?>"
                                            data-name="<?php echo addslashes($name); ?>"
                                            title="Mark as Paid">
                                        <i class="bi bi-check-circle"></i>
                                    </button>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </form>
            
        <!-- Pagination - updated to include sort parameters -->
        <?php if ($total_pages > 1): ?>
            <nav aria-label="Registration pagination">
                <ul class="pagination justify-content-center">
                    <li class="page-item <?php echo $current_page <= 1 ? 'disabled' : ''; ?>">
                        <a class="page-link" href="?page=all_registrations&type=<?php echo $reg_type; ?>&subpage=<?php echo $current_page - 1; ?><?php echo isset($_GET['search']) ? '&search='.urlencode($_GET['search']) : ''; ?><?php echo isset($_GET['payment_status']) ? '&payment_status='.urlencode($_GET['payment_status']) : ''; ?><?php echo isset($_GET['department']) ? '&department='.urlencode($_GET['department']) : ''; ?><?php echo isset($_GET['sort']) ? '&sort='.urlencode($_GET['sort']) : ''; ?><?php echo isset($_GET['dir']) ? '&dir='.urlencode($_GET['dir']) : ''; ?>">Previous</a>
                    </li>
                    
                    <?php
                    $start_page = max(1, $current_page - 2);
                    $end_page = min($total_pages, $current_page + 2);
                    
                    // Helper function to generate pagination URL with all parameters
                    function getPaginationUrl($page_num, $reg_type, $sort_column, $sort_direction) {
                        global $_GET;
                        $url = "?page=all_registrations&type=" . urlencode($reg_type) . "&subpage=" . $page_num;
                        if(isset($_GET['search'])) $url .= '&search='.urlencode($_GET['search']);
                        if(isset($_GET['payment_status'])) $url .= '&payment_status='.urlencode($_GET['payment_status']);
                        if(isset($_GET['department'])) $url .= '&department='.urlencode($_GET['department']);
                        if($sort_column) $url .= '&sort='.urlencode($sort_column);
                        if($sort_direction) $url .= '&dir='.urlencode($sort_direction);
                        return $url;
                    }
                    
                    if ($start_page > 1) {
                        echo '<li class="page-item"><a class="page-link" href="' . getPaginationUrl(1, $reg_type, $sort_column, $sort_direction) . '">1</a></li>';
                        
                        if ($start_page > 2) {
                            echo '<li class="page-item disabled"><a class="page-link" href="#">...</a></li>';
                        }
                    }
                    
                    for ($i = $start_page; $i <= $end_page; $i++) {
                        echo '<li class="page-item '.($i == $current_page ? 'active' : '').'">
                            <a class="page-link" href="' . getPaginationUrl($i, $reg_type, $sort_column, $sort_direction) . '">'.$i.'</a></li>';
                    }
                    
                    if ($end_page < $total_pages) {
                        if ($end_page < $total_pages - 1) {
                            echo '<li class="page-item disabled"><a class="page-link" href="#">...</a></li>';
                        }
                        
                        echo '<li class="page-item"><a class="page-link" href="' . getPaginationUrl($total_pages, $reg_type, $sort_column, $sort_direction) . '">'.$total_pages.'</a></li>';
                    }
                    ?>
                    
                    <li class="page-item <?php echo $current_page >= $total_pages ? 'disabled' : ''; ?>">
                        <a class="page-link" href="?page=all_registrations&type=<?php echo $reg_type; ?>&subpage=<?php echo $current_page + 1; ?><?php echo isset($_GET['search']) ? '&search='.urlencode($_GET['search']) : ''; ?><?php echo isset($_GET['payment_status']) ? '&payment_status='.urlencode($_GET['payment_status']) : ''; ?><?php echo isset($_GET['department']) ? '&department='.urlencode($_GET['department']) : ''; ?><?php echo isset($_GET['sort']) ? '&sort='.urlencode($_GET['sort']) : ''; ?><?php echo isset($_GET['dir']) ? '&dir='.urlencode($_GET['dir']) : ''; ?>">Next</a>
                    </li>
                </ul>
            </nav>
        <?php endif; ?>
            
        <?php else: ?>
            <div class="alert alert-info">
                <i class="bi bi-info-circle me-2"></i>
                No registrations found. Please try a different search or filter criteria.
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- View Registration Modal -->
<div class="modal fade" id="viewModal" tabindex="-1" aria-labelledby="viewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="viewModalLabel">Registration Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="viewModalContent">
                    <div class="text-center">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-2">Loading registration details...</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="viewEditBtn">Edit</button>
            </div>
        </div>
    </div>
</div>

<!-- Edit Registration Modal -->
<div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editModalLabel">Edit Registration</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="editModalContent">
                    <div class="text-center">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-2">Loading edit form...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Mark as Paid Modal -->
<div class="modal fade" id="markPaidModal" tabindex="-1" aria-labelledby="markPaidModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="markPaidModalLabel">Confirm Payment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to mark <strong id="registrantName"></strong> as Paid?</p>
            </div>
            <div class="modal-footer">
                <form method="post" action="update_payment_status.php">
                    <input type="hidden" name="type" id="registrantType">
                    <input type="hidden" name="id" id="registrantId">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="mark_as_paid" class="btn btn-success">Confirm Payment</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Handle View Registration button
        const viewButtons = document.querySelectorAll('.view-btn');
        viewButtons.forEach(button => {
            button.addEventListener('click', function() {
                const id = this.getAttribute('data-id');
                const type = this.getAttribute('data-type');
                const viewEditBtn = document.getElementById('viewEditBtn');
                
                // Set edit button attributes
                viewEditBtn.setAttribute('data-id', id);
                viewEditBtn.setAttribute('data-type', type);
                
                // Load registration details
                fetch(`get_registration.php?id=${id}&type=${type}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === 'success') {
                            const content = document.getElementById('viewModalContent');
                            content.innerHTML = data.html;
                        } else {
                            const content = document.getElementById('viewModalContent');
                            content.innerHTML = `<div class="alert alert-danger">${data.message}</div>`;
                        }
                    })
                    .catch(error => {
                        const content = document.getElementById('viewModalContent');
                        content.innerHTML = `<div class="alert alert-danger">Error loading registration: ${error}</div>`;
                    });
            });
        });
        
        // Handle Edit button in View modal
        document.getElementById('viewEditBtn').addEventListener('click', function() {
            const id = this.getAttribute('data-id');
            const type = this.getAttribute('data-type');
            
            // Hide view modal and show edit modal
            const viewModal = bootstrap.Modal.getInstance(document.getElementById('viewModal'));
            viewModal.hide();
            
            // Set data attributes for edit modal and show it
            const editBtn = document.querySelector(`button.edit-btn[data-id="${id}"][data-type="${type}"]`);
            if (editBtn) {
                editBtn.click();
            }
        });
        
        // Handle Edit Registration button
        const editButtons = document.querySelectorAll('.edit-btn');
        editButtons.forEach(button => {
            button.addEventListener('click', function() {
                const id = this.getAttribute('data-id');
                const type = this.getAttribute('data-type');
                
                // Load edit form
                fetch(`get_edit_form.php?id=${id}&type=${type}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === 'success') {
                            const content = document.getElementById('editModalContent');
                            content.innerHTML = data.html;
                            
                            // Initialize any form elements in the loaded content
                            initializeEditForm();
                        } else {
                            const content = document.getElementById('editModalContent');
                            content.innerHTML = `<div class="alert alert-danger">${data.message}</div>`;
                        }
                    })
                    .catch(error => {
                        const content = document.getElementById('editModalContent');
                        content.innerHTML = `<div class="alert alert-danger">Error loading edit form: ${error}</div>`;
                    });
            });
        });
        
        // Initialize edit form elements
        function initializeEditForm() {
            // Handle inhouse competition radio buttons for students
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
        }
        
        // Handle Mark as Paid button
        const markPaidButtons = document.querySelectorAll('.mark-paid-btn');
        markPaidButtons.forEach(button => {
            button.addEventListener('click', function() {
                const id = this.getAttribute('data-id');
                const type = this.getAttribute('data-type');
                const name = this.getAttribute('data-name');
                
                document.getElementById('registrantId').value = id;
                document.getElementById('registrantType').value = type;
                document.getElementById('registrantName').textContent = name;
            });
        });
        
        // Handle bulk actions and checkboxes
        const selectAllCheckbox = document.getElementById('selectAll');
        const registrationCheckboxes = document.querySelectorAll('.registration-checkbox');
        const applyBulkAction = document.getElementById('applyBulkAction');
        const bulkAction = document.getElementById('bulk_action');
        
        // Function to update apply button state
        function updateApplyButton() {
            const checkedCount = document.querySelectorAll('.registration-checkbox:checked').length;
            applyBulkAction.disabled = checkedCount === 0 || bulkAction.value === '';
        }
        
        // Select All functionality
        if (selectAllCheckbox) {
            selectAllCheckbox.addEventListener('change', function() {
                registrationCheckboxes.forEach(checkbox => {
                    checkbox.checked = selectAllCheckbox.checked;
                });
                updateApplyButton();
            });
        }
        
        // Individual checkbox changes
        registrationCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                const allChecked = document.querySelectorAll('.registration-checkbox:checked').length === registrationCheckboxes.length;
                if (selectAllCheckbox) selectAllCheckbox.checked = allChecked;
                updateApplyButton();
            });
        });
        
        // Bulk action dropdown change
        if (bulkAction) {
            bulkAction.addEventListener('change', updateApplyButton);
        }
        
        // Confirm bulk delete
        if (document.getElementById('bulkActionForm')) {
            document.getElementById('bulkActionForm').addEventListener('submit', function(event) {
                if (bulkAction.value === 'delete') {
                    event.preventDefault(); // Always prevent default first
                    if (confirm('Are you sure you want to delete the selected registrations? This action cannot be undone.')) {
                        // Form submission will proceed after the user clicks OK
                        this.submit();
                    }
                }
            });
        }
    });
</script>
