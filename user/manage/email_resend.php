<?php
// This file is included by index.php and should not be accessed directly
if (!isset($db) || !isset($_SESSION)) {
    exit('This file cannot be accessed directly.');
}

// Include the registration mailers with error handling
$studentMailerAvailable = false;
$alumniMailerAvailable = false;
$paymentMailerAvailable = false;

if (file_exists(__DIR__ . '/../../src/mail/registration_mailer.php')) {
    try {
        require_once __DIR__ . '/../../src/mail/registration_mailer.php';
        $studentMailerAvailable = function_exists('sendRegistrationConfirmationEmail');
    } catch (Exception $e) {
        error_log("Error loading student registration mailer: " . $e->getMessage());
    }
}

if (file_exists(__DIR__ . '/../../src/mail/alumni_mailer.php')) {
    try {
        require_once __DIR__ . '/../../src/mail/alumni_mailer.php';
        $alumniMailerAvailable = function_exists('sendAlumniRegistrationEmail');
    } catch (Exception $e) {
        error_log("Error loading alumni registration mailer: " . $e->getMessage());
    }
}

// Include payment mailer
if (file_exists(__DIR__ . '/../../user/adm/email_sender.php')) {
    try {
        require_once __DIR__ . '/../../user/adm/email_sender.php';
        $paymentMailerAvailable = function_exists('sendPaymentConfirmationEmail');
    } catch (Exception $e) {
        error_log("Error loading payment mailer: " . $e->getMessage());
    }
}

// Get sort parameters (default: sort by registration_date DESC)
$sort_column = isset($_GET['sort']) ? $_GET['sort'] : 'registration_date';
$sort_direction = isset($_GET['dir']) ? $_GET['dir'] : 'desc';

// Validate sort column to prevent SQL injection
$allowed_columns = ['jis_id', 'student_name', 'department', 'mobile', 'email', 'registration_date', 'email_sent'];
if (!in_array($sort_column, $allowed_columns)) {
    $sort_column = 'registration_date';
}

// Validate sort direction
$sort_direction = strtolower($sort_direction) === 'desc' ? 'desc' : 'asc';

// Initialize variables for pagination
$current_page = isset($_GET['subpage']) ? (int)$_GET['subpage'] : 1;
$records_per_page = 15;
$offset = ($current_page - 1) * $records_per_page;

// Set default registration type filter
$reg_type = isset($_GET['type']) ? $_GET['type'] : 'all';
$email_type = isset($_GET['email_type']) ? $_GET['email_type'] : 'registration';

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
        $query = "(SELECT id, 'student' AS reg_type, jis_id, student_name AS name, email, department, mobile, registration_date, payment_status 
                  FROM registrations$search_condition_student)
                 UNION ALL
                 (SELECT id, 'alumni' AS reg_type, jis_id, alumni_name AS name, email, department, mobile, registration_date, payment_status 
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
        $query = "SELECT id, 'student' AS reg_type, jis_id, student_name AS name, email, department, mobile, registration_date, payment_status 
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
        $query = "SELECT id, 'alumni' AS reg_type, jis_id, alumni_name AS name, email, department, mobile, registration_date, payment_status 
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

// Success and error messages
$email_success = isset($_SESSION['email_success']) ? $_SESSION['email_success'] : '';
$email_error = isset($_SESSION['email_error']) ? $_SESSION['email_error'] : '';

// Clear session messages after displaying
unset($_SESSION['email_success']);
unset($_SESSION['email_error']);
?>

<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Email Resend</h5>
        <span class="badge bg-primary"><?php echo number_format($total_records); ?> Total Registrations</span>
    </div>
    <div class="card-body">
        <!-- Success/Error Messages -->
        <?php if (!empty($email_success)): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle me-2"></i> <?php echo $email_success; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($email_error)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-circle me-2"></i> <?php echo $email_error; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        
        <!-- Search & Filter Form -->
        <form method="get" class="row g-3 mb-4">
            <input type="hidden" name="page" value="email_resend">
            
            <!-- Keep sort parameters when search changes -->
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
                <select class="form-select" name="email_type" onchange="this.form.submit()">
                    <option value="registration" <?php echo $email_type === 'registration' ? 'selected' : ''; ?>>Registration Email</option>
                    <option value="payment" <?php echo $email_type === 'payment' ? 'selected' : ''; ?>>Payment Email</option>
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
                <div class="d-flex gap-2">
                    <select class="form-select" name="department" onchange="this.form.submit()">
                        <option value="">-- All Departments --</option>
                        <?php foreach ($departments as $dept): ?>
                            <option value="<?php echo htmlspecialchars($dept); ?>" <?php echo (isset($_GET['department']) && $_GET['department'] === $dept) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($dept); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <a href="?page=email_resend" class="btn btn-outline-secondary">
                        <i class="bi bi-x-circle"></i>
                    </a>
                </div>
            </div>
        </form>
        
        <!-- Mailer Status Indicator -->
        <div class="row mb-4">
            <div class="col-md-8">
                <div class="card bg-light">
                    <div class="card-body p-3">
                        <h6 class="card-title mb-2">Mailer Status</h6>
                        <div class="d-flex gap-3 flex-wrap">
                            <span class="badge <?php echo $studentMailerAvailable ? 'bg-success' : 'bg-danger'; ?> p-2">
                                <i class="bi <?php echo $studentMailerAvailable ? 'bi-check-circle' : 'bi-x-circle'; ?> me-1"></i>
                                Student Mailer: <?php echo $studentMailerAvailable ? 'Available' : 'Unavailable'; ?>
                            </span>
                            <span class="badge <?php echo $alumniMailerAvailable ? 'bg-success' : 'bg-danger'; ?> p-2">
                                <i class="bi <?php echo $alumniMailerAvailable ? 'bi-check-circle' : 'bi-x-circle'; ?> me-1"></i>
                                Alumni Mailer: <?php echo $alumniMailerAvailable ? 'Available' : 'Unavailable'; ?>
                            </span>
                            <span class="badge <?php echo $paymentMailerAvailable ? 'bg-success' : 'bg-danger'; ?> p-2">
                                <i class="bi <?php echo $paymentMailerAvailable ? 'bi-check-circle' : 'bi-x-circle'; ?> me-1"></i>
                                Payment Mailer: <?php echo $paymentMailerAvailable ? 'Available' : 'Unavailable'; ?>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4 d-flex align-items-center">
                <div class="alert alert-info mb-0 p-2 px-3 w-100">
                    <i class="bi bi-info-circle me-2"></i>
                    <small>Email will be sent using the corresponding mailer based on email type.</small>
                </div>
            </div>
        </div>
        
        <?php if (!empty($registrations)): ?>
            <!-- Registrations Table -->
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>Type</th>
                            <th>
                                <a href="?page=email_resend&type=<?php echo $reg_type; ?>&email_type=<?php echo $email_type; ?>&sort=jis_id&dir=<?php echo ($sort_column == 'jis_id' && $sort_direction == 'asc') ? 'desc' : 'asc'; ?><?php echo isset($_GET['search']) ? '&search='.urlencode($_GET['search']) : ''; ?><?php echo isset($_GET['payment_status']) ? '&payment_status='.urlencode($_GET['payment_status']) : ''; ?><?php echo isset($_GET['department']) ? '&department='.urlencode($_GET['department']) : ''; ?>" class="text-dark text-decoration-none">
                                    JIS ID
                                    <?php if($sort_column == 'jis_id'): ?>
                                        <i class="bi bi-arrow-<?php echo $sort_direction == 'asc' ? 'up' : 'down'; ?>"></i>
                                    <?php endif; ?>
                                </a>
                            </th>
                            <th>
                                <a href="?page=email_resend&type=<?php echo $reg_type; ?>&email_type=<?php echo $email_type; ?>&sort=name&dir=<?php echo ($sort_column == 'name' && $sort_direction == 'asc') ? 'desc' : 'asc'; ?><?php echo isset($_GET['search']) ? '&search='.urlencode($_GET['search']) : ''; ?><?php echo isset($_GET['payment_status']) ? '&payment_status='.urlencode($_GET['payment_status']) : ''; ?><?php echo isset($_GET['department']) ? '&department='.urlencode($_GET['department']) : ''; ?>" class="text-dark text-decoration-none">
                                    Name
                                    <?php if($sort_column == 'name'): ?>
                                        <i class="bi bi-arrow-<?php echo $sort_direction == 'asc' ? 'up' : 'down'; ?>"></i>
                                    <?php endif; ?>
                                </a>
                            </th>
                            <th>Email</th>
                            <th>
                                <a href="?page=email_resend&type=<?php echo $reg_type; ?>&email_type=<?php echo $email_type; ?>&sort=department&dir=<?php echo ($sort_column == 'department' && $sort_direction == 'asc') ? 'desc' : 'asc'; ?><?php echo isset($_GET['search']) ? '&search='.urlencode($_GET['search']) : ''; ?><?php echo isset($_GET['payment_status']) ? '&payment_status='.urlencode($_GET['payment_status']) : ''; ?><?php echo isset($_GET['department']) ? '&department='.urlencode($_GET['department']) : ''; ?>" class="text-dark text-decoration-none">
                                    Department
                                    <?php if($sort_column == 'department'): ?>
                                        <i class="bi bi-arrow-<?php echo $sort_direction == 'asc' ? 'up' : 'down'; ?>"></i>
                                    <?php endif; ?>
                                </a>
                            </th>
                            <th>
                                <a href="?page=email_resend&type=<?php echo $reg_type; ?>&email_type=<?php echo $email_type; ?>&sort=registration_date&dir=<?php echo ($sort_column == 'registration_date' && $sort_direction == 'asc') ? 'desc' : 'asc'; ?><?php echo isset($_GET['search']) ? '&search='.urlencode($_GET['search']) : ''; ?><?php echo isset($_GET['payment_status']) ? '&payment_status='.urlencode($_GET['payment_status']) : ''; ?><?php echo isset($_GET['department']) ? '&department='.urlencode($_GET['department']) : ''; ?>" class="text-dark text-decoration-none">
                                    Registration Date
                                    <?php if($sort_column == 'registration_date'): ?>
                                        <i class="bi bi-arrow-<?php echo $sort_direction == 'asc' ? 'up' : 'down'; ?>"></i>
                                    <?php endif; ?>
                                </a>
                            </th>
                            <th>Payment</th>
                            <th width="220px">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($registrations as $reg): 
                        $reg_id = $reg['id'];
                        $reg_type_label = $reg['reg_type'];
                        $name = htmlspecialchars($reg['name']);
                        $jis_id = htmlspecialchars($reg['jis_id']);
                        $email = htmlspecialchars($reg['email']);
                        $department = htmlspecialchars($reg['department']);
                        $reg_date = date('d M Y, h:i A', strtotime($reg['registration_date']));
                        $payment_status = $reg['payment_status'];
                        $status_badge = $payment_status === 'Paid' ? 'success' : 'warning';
                        
                        // Set appropriate mailer availability based on email type
                        $reg_mailer_available = $reg_type_label === 'student' ? $studentMailerAvailable : $alumniMailerAvailable;
                        $payment_mailer_available = $paymentMailerAvailable && $payment_status === 'Paid';
                    ?>
                        <tr>
                            <td>
                                <span class="badge bg-<?php echo $reg_type_label === 'student' ? 'info' : 'secondary'; ?>">
                                    <?php echo ucfirst($reg_type_label); ?>
                                </span>
                            </td>
                            <td><?php echo $jis_id; ?></td>
                            <td><?php echo $name; ?></td>
                            <td><?php echo $email; ?></td>
                            <td><?php echo $department; ?></td>
                            <td>
                                <a href="?page=email_resend&type=<?php echo $reg_type; ?>&email_type=<?php echo $email_type; ?>&sort=registration_date&dir=<?php echo ($sort_column == 'registration_date' && $sort_direction == 'asc') ? 'desc' : 'asc'; ?><?php echo isset($_GET['search']) ? '&search='.urlencode($_GET['search']) : ''; ?><?php echo isset($_GET['payment_status']) ? '&payment_status='.urlencode($_GET['payment_status']) : ''; ?><?php echo isset($_GET['department']) ? '&department='.urlencode($_GET['department']) : ''; ?>" class="text-dark text-decoration-none">
                                    <?php echo $reg_date; ?>
                                    <?php if($sort_column == 'registration_date'): ?>
                                        <i class="bi bi-arrow-<?php echo $sort_direction == 'asc' ? 'up' : 'down'; ?>"></i>
                                    <?php endif; ?>
                                </a>
                            </td>
                            <td><span class="badge bg-<?php echo $status_badge; ?>"><?php echo $payment_status; ?></span></td>
                            <td>
                                <div class="btn-group">
                                    <button type="button" class="btn btn-sm btn-info view-btn" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#viewModal"
                                            data-id="<?php echo $reg_id; ?>"
                                            data-type="<?php echo $reg_type_label; ?>"
                                            title="View Details">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                    
                                    <!-- Registration Email Button -->
                                    <button type="button" class="btn btn-sm btn-primary resend-email-btn"
                                            <?php if (!$reg_mailer_available): ?>disabled<?php endif; ?>
                                            data-id="<?php echo $reg_id; ?>"
                                            data-type="<?php echo $reg_type_label; ?>"
                                            data-bs-toggle="modal"
                                            data-bs-target="#resendConfirmModal"
                                            data-name="<?php echo $name; ?>"
                                            data-email="<?php echo $email; ?>"
                                            data-email-type="registration"
                                            data-payment="<?php echo $payment_status; ?>"
                                            title="Resend Registration Email">
                                        <i class="bi bi-envelope"></i>
                                    </button>
                                    
                                    <!-- Payment Email Button -->
                                    <button type="button" class="btn btn-sm btn-success resend-email-btn"
                                            <?php if (!$payment_mailer_available): ?>disabled<?php endif; ?>
                                            data-id="<?php echo $reg_id; ?>"
                                            data-type="<?php echo $reg_type_label; ?>"
                                            data-bs-toggle="modal"
                                            data-bs-target="#resendConfirmModal"
                                            data-name="<?php echo $name; ?>"
                                            data-email="<?php echo $email; ?>"
                                            data-email-type="payment"
                                            data-payment="<?php echo $payment_status; ?>"
                                            title="Resend Payment Email">
                                        <i class="bi bi-cash-coin"></i>
                                    </button>
                                    
                                    <a href="javascript:void(0);" onclick="window.open('mailto:<?php echo $email; ?>?subject=maJIStic 2K25 Registration&body=Dear <?php echo $name; ?>, %0A%0ARegarding your registration for maJIStic 2K25 (JIS ID: <?php echo $jis_id; ?>), %0A%0A')" class="btn btn-sm btn-outline-secondary" title="Compose Custom Email">
                                        <i class="bi bi-envelope-plus"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
                <nav aria-label="Registration pagination">
                    <ul class="pagination justify-content-center">
                        <li class="page-item <?php echo $current_page <= 1 ? 'disabled' : ''; ?>">
                            <a class="page-link" href="?page=email_resend&type=<?php echo $reg_type; ?>&email_type=<?php echo $email_type; ?>&subpage=<?php echo $current_page - 1; ?><?php echo isset($_GET['search']) ? '&search='.urlencode($_GET['search']) : ''; ?><?php echo isset($_GET['payment_status']) ? '&payment_status='.urlencode($_GET['payment_status']) : ''; ?><?php echo isset($_GET['department']) ? '&department='.urlencode($_GET['department']) : ''; ?><?php echo isset($_GET['sort']) ? '&sort='.urlencode($_GET['sort']) : ''; ?><?php echo isset($_GET['dir']) ? '&dir='.urlencode($_GET['dir']) : ''; ?>">Previous</a>
                        </li>
                        
                        <?php
                        $start_page = max(1, $current_page - 2);
                        $end_page = min($total_pages, $current_page + 2);
                        
                        // Helper function to generate pagination URL with all parameters
                        function getPaginationUrl($page_num, $reg_type, $email_type, $sort_column, $sort_direction) {
                            global $_GET;
                            $url = "?page=email_resend&type=" . urlencode($reg_type) . "&email_type=" . urlencode($email_type) . "&subpage=" . $page_num;
                            if(isset($_GET['search'])) $url .= '&search='.urlencode($_GET['search']);
                            if(isset($_GET['payment_status'])) $url .= '&payment_status='.urlencode($_GET['payment_status']);
                            if(isset($_GET['department'])) $url .= '&department='.urlencode($_GET['department']);
                            if($sort_column) $url .= '&sort='.urlencode($sort_column);
                            if($sort_direction) $url .= '&dir='.urlencode($sort_direction);
                            return $url;
                        }
                        
                        if ($start_page > 1) {
                            echo '<li class="page-item"><a class="page-link" href="' . getPaginationUrl(1, $reg_type, $email_type, $sort_column, $sort_direction) . '">1</a></li>';
                            
                            if ($start_page > 2) {
                                echo '<li class="page-item disabled"><a class="page-link" href="#">...</a></li>';
                            }
                        }
                        
                        for ($i = $start_page; $i <= $end_page; $i++) {
                            echo '<li class="page-item '.($i == $current_page ? 'active' : '').'">
                                <a class="page-link" href="' . getPaginationUrl($i, $reg_type, $email_type, $sort_column, $sort_direction) . '">'.$i.'</a></li>';
                        }
                        
                        if ($end_page < $total_pages) {
                            if ($end_page < $total_pages - 1) {
                                echo '<li class="page-item disabled"><a class="page-link" href="#">...</a></li>';
                            }
                            
                            echo '<li class="page-item"><a class="page-link" href="' . getPaginationUrl($total_pages, $reg_type, $email_type, $sort_column, $sort_direction) . '">'.$total_pages.'</a></li>';
                        }
                        ?>
                        
                        <li class="page-item <?php echo $current_page >= $total_pages ? 'disabled' : ''; ?>">
                            <a class="page-link" href="?page=email_resend&type=<?php echo $reg_type; ?>&email_type=<?php echo $email_type; ?>&subpage=<?php echo $current_page + 1; ?><?php echo isset($_GET['search']) ? '&search='.urlencode($_GET['search']) : ''; ?><?php echo isset($_GET['payment_status']) ? '&payment_status='.urlencode($_GET['payment_status']) : ''; ?><?php echo isset($_GET['department']) ? '&department='.urlencode($_GET['department']) : ''; ?><?php echo isset($_GET['sort']) ? '&sort='.urlencode($_GET['sort']) : ''; ?><?php echo isset($_GET['dir']) ? '&dir='.urlencode($_GET['dir']) : ''; ?>">Next</a>
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
            </div>
        </div>
    </div>
</div>

<!-- Resend Email Confirmation Modal -->
<div class="modal fade" id="resendConfirmModal" tabindex="-1" aria-labelledby="resendConfirmModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="resendConfirmModalLabel">Confirm Email Resend</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to resend the <span id="emailTypeLabel" class="fw-bold"></span> email to:</p>
                <div class="mb-3 p-3 border rounded bg-light">
                    <p class="mb-1"><strong>Name:</strong> <span id="recipientName"></span></p>
                    <p class="mb-0"><strong>Email:</strong> <span id="recipientEmail"></span></p>
                </div>
                <div id="paymentEmailWarning" class="alert alert-danger" style="display: none;">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    Registration must be marked as "Paid" to send payment confirmation emails.
                </div>
            </div>
            <div class="modal-footer">
                <form method="post" action="resend_email.php">
                    <input type="hidden" name="reg_id" id="regId">
                    <input type="hidden" name="reg_type" id="regType">
                    <input type="hidden" name="email_type" id="emailType">
                    <input type="hidden" name="return_url" id="returnUrl" value="index.php?page=email_resend">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="resend_email" class="btn btn-primary">
                        <i class="bi bi-envelope me-1"></i> Resend Email
                    </button>
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

        // Handle Resend Email button
        const resendButtons = document.querySelectorAll('.resend-email-btn');
        resendButtons.forEach(button => {
            button.addEventListener('click', function() {
                const id = this.getAttribute('data-id');
                const type = this.getAttribute('data-type');
                const name = this.getAttribute('data-name');
                const email = this.getAttribute('data-email');
                const emailType = this.getAttribute('data-email-type');
                const paymentStatus = this.getAttribute('data-payment');
                
                // Set values in confirmation modal
                document.getElementById('regId').value = id;
                document.getElementById('regType').value = type;
                document.getElementById('emailType').value = emailType;
                document.getElementById('recipientName').textContent = name;
                document.getElementById('recipientEmail').textContent = email;
                document.getElementById('emailTypeLabel').textContent = emailType;
                
                // Update modal title based on email type
                const modalTitle = document.getElementById('resendConfirmModalLabel');
                if (emailType === 'registration') {
                    modalTitle.textContent = 'Confirm Registration Email Resend';
                } else if (emailType === 'payment') {
                    modalTitle.textContent = 'Confirm Payment Email Resend';
                }
                
                // Show warning for payment emails if the registration is not paid
                const paymentWarning = document.getElementById('paymentEmailWarning');
                if (emailType === 'payment' && paymentStatus !== 'Paid') {
                    paymentWarning.style.display = 'block';
                } else {
                    paymentWarning.style.display = 'none';
                }
                
                // Update return URL with current filters
                const urlParams = new URLSearchParams(window.location.search);
                let returnUrl = 'index.php?page=email_resend';
                
                if (urlParams.has('type')) {
                    returnUrl += `&type=${urlParams.get('type')}`;
                }
                if (urlParams.has('email_type')) {
                    returnUrl += `&email_type=${urlParams.get('email_type')}`;
                }
                if (urlParams.has('subpage')) {
                    returnUrl += `&subpage=${urlParams.get('subpage')}`;
                }
                if (urlParams.has('search')) {
                    returnUrl += `&search=${urlParams.get('search')}`;
                }
                if (urlParams.has('payment_status')) {
                    returnUrl += `&payment_status=${urlParams.get('payment_status')}`;
                }
                if (urlParams.has('department')) {
                    returnUrl += `&department=${urlParams.get('department')}`;
                }
                
                document.getElementById('returnUrl').value = returnUrl;
            });
        });
    });
</script>
