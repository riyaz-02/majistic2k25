<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in with Controller role
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true || 
    !isset($_SESSION['admin_role']) || $_SESSION['admin_role'] !== 'Controller') {
    // Not logged in or not a controller, redirect to login
    header('Location: ../login.php');
    exit;
}

require_once __DIR__ . '/../../includes/db_config.php';

// Fetch data from the database
$registrations = $db->registrations->find(['payment_status' => 'Paid'])->toArray();
$alumni_registrations = $db->alumni_registrations->find(['payment_status' => 'Paid'])->toArray();

// Combine data
$students = array_merge($registrations, $alumni_registrations);

// Calculate stats
$total_students = count($students);
$tickets_generated = count(array_filter($students, fn($s) => isset($s['ticket']) && $s['ticket'] === "generated"));
$tickets_not_generated = $total_students - $tickets_generated;
$day1_checked_in = count(array_filter($students, fn($s) => isset($s['checkin_1']) && $s['checkin_1'] === "checkedin"));
$day2_checked_in = count(array_filter($students, fn($s) => isset($s['checkin_2']) && $s['checkin_2'] === "checkedin"));
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Management - Controller Panel</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <!-- Navigation bar with user info and logout button -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">
                <i class="fas fa-cog fa-spin me-2"></i>maJIStic 2K25 Controller Panel
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <span class="nav-user-info">
                            <i class="fas fa-user me-1"></i> Welcome, <?php echo htmlspecialchars($_SESSION['admin_username']); ?>
                        </span>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../logout.php"><i class="fas fa-sign-out-alt me-1"></i> Logout</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container-fluid p-4">
        <div class="row mb-4">
            <div class="col-12">
                <div class="card dashboard-header">
                    <div class="card-body">
                        <h2><i class="fas fa-tachometer-alt me-2"></i>Controller Dashboard</h2>
                        <p class="text-muted">Manage student registrations and check-ins</p>
                        <p class="text-muted">THIS PAGE CONTAINS ONLY PAID STUDENTS DETAILS</p>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Stat Cards -->
        <div class="row g-4 mb-4">
            <div class="col-md-4 col-lg-2">
                <div class="card stat-card">
                    <div class="card-body">
                        <div class="stat-icon"><i class="fas fa-users"></i></div>
                        <h5 class="card-title">Total Paid Students</h5>
                        <p class="stat-number"><?= $total_students ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-4 col-lg-2">
                <div class="card stat-card ticket-generated">
                    <div class="card-body">
                        <div class="stat-icon"><i class="fas fa-ticket-alt"></i></div>
                        <h5 class="card-title">Tickets Generated</h5>
                        <p class="stat-number"><?= $tickets_generated ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-4 col-lg-2">
                <div class="card stat-card ticket-not-generated">
                    <div class="card-body">
                        <div class="stat-icon"><i class="fas fa-times-circle"></i></div>
                        <h5 class="card-title">Not Generated</h5>
                        <p class="stat-number"><?= $tickets_not_generated ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="card stat-card day-one">
                    <div class="card-body">
                        <div class="stat-icon"><i class="fas fa-calendar-day"></i></div>
                        <h5 class="card-title">Day 1 Check-Ins</h5>
                        <p class="stat-number"><?= $day1_checked_in ?> / <?= $total_students ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="card stat-card day-two">
                    <div class="card-body">
                        <div class="stat-icon"><i class="fas fa-calendar-day"></i></div>
                        <h5 class="card-title">Day 2 Check-Ins</h5>
                        <p class="stat-number"><?= $day2_checked_in ?> / <?= $total_students ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Search and Filter -->
        <div class="row mb-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="mb-0"><i class="fas fa-filter me-2"></i>Search & Filter</h5>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <input type="text" id="searchInput" class="form-control" placeholder="Search by name, ID, email...">
                            </div>
                            <div class="col-md-3">
                                <select id="departmentFilter" class="form-select">
                                    <option value="">All Departments</option>
                                    <option value="CSE">CSE</option>
                                    <option value="BME">BME</option>
                                    <option value="ECE">ECE</option>
                                    <option value="EE">EE</option>
                                    <option value="CE">CE</option>
                                    <option value="ME">ME</option>
                                    <option value="MCA">MCA</option>
                                    <option value="MBA">MBA</option>
                                    <option value="BBA">BBA</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <select id="typeFilter" class="form-select">
                                    <option value="">All Types</option>
                                    <option value="student">Student</option>
                                    <option value="alumni">Alumni</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <button id="resetFilters" class="btn btn-secondary w-100">Reset</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Table -->
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title mb-4"><i class="fas fa-list me-2"></i>Student List</h5>
                        <div class="table-responsive">
                            <table class="table table-striped table-hover" id="studentTable">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Name</th>
                                        <th>JIS ID</th>
                                        <th>Department</th>
                                        <th>Type</th>
                                        <th>Email</th>
                                        <th>Phone</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($students as $student): 
                                        // Determine if student or alumni based on available fields
                                        $type = isset($student['passout_year']) ? 'alumni' : 'student';
                                        
                                        // Get the name based on type
                                        $name = $type === 'alumni' ? ($student['alumni_name'] ?? 'N/A') : ($student['student_name'] ?? 'N/A');
                                        
                                        // Get other fields
                                        $jisId = $student['jis_id'] ?? 'N/A';
                                        $department = $student['department'] ?? 'N/A';
                                        $email = $student['email'] ?? 'N/A';
                                        $phone = $student['mobile'] ?? 'N/A';
                                        
                                        // Check statuses
                                        $ticketGenerated = isset($student['ticket']) && $student['ticket'] === "generated";
                                        $day1CheckedIn = isset($student['checkin_1']) && $student['checkin_1'] === "checkedin";
                                        $day2CheckedIn = isset($student['checkin_2']) && $student['checkin_2'] === "checkedin";
                                    ?>
                                        <tr data-department="<?= $department ?>" data-type="<?= $type ?>">
                                            <td><?= htmlspecialchars($name) ?></td>
                                            <td><?= htmlspecialchars($jisId) ?></td>
                                            <td><?= htmlspecialchars($department) ?></td>
                                            <td><span class="badge bg-<?= $type === 'alumni' ? 'warning' : 'info' ?>"><?= ucfirst($type) ?></span></td>
                                            <td><?= htmlspecialchars($email) ?></td>
                                            <td><?= htmlspecialchars($phone) ?></td>
                                            <td>
                                                <button class="btn btn-sm btn-primary view-btn" data-id="<?= $student['_id'] ?>" data-type="<?= $type ?>" title="View Details">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <button class="btn btn-sm <?= $ticketGenerated ? 'btn-success disabled' : 'btn-outline-success' ?> generate-ticket-btn" 
                                                    data-id="<?= $student['_id'] ?>" data-type="<?= $type ?>" 
                                                    <?= $ticketGenerated ? 'disabled' : '' ?> title="<?= $ticketGenerated ? 'Ticket Generated' : 'Generate Ticket' ?>">
                                                    <i class="fas fa-ticket-alt"></i>
                                                </button>
                                                <button class="btn btn-sm <?= $day1CheckedIn ? 'btn-info disabled' : 'btn-outline-info' ?> checkin1-btn" 
                                                    data-id="<?= $student['_id'] ?>" data-type="<?= $type ?>" 
                                                    <?= $day1CheckedIn ? 'disabled' : '' ?> title="<?= $day1CheckedIn ? 'Day 1 Checked In' : 'Day 1 Check-In' ?>">
                                                    <i class="fas fa-calendar-check"></i> 1
                                                </button>
                                                <button class="btn btn-sm <?= $day2CheckedIn ? 'btn-warning disabled' : 'btn-outline-warning' ?> checkin2-btn" 
                                                    data-id="<?= $student['_id'] ?>" data-type="<?= $type ?>" 
                                                    <?= $day2CheckedIn ? 'disabled' : '' ?> title="<?= $day2CheckedIn ? 'Day 2 Checked In' : 'Day 2 Check-In' ?>">
                                                    <i class="fas fa-calendar-check"></i> 2
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Student Details Modal -->
    <div class="modal fade" id="viewModal" tabindex="-1" aria-labelledby="viewModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="viewModalLabel"><i class="fas fa-user-graduate me-2"></i>Student Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="text-center">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p>Loading details...</p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Loading Overlay -->
    <div id="loadingOverlay" class="overlay-loading d-none">
        <div class="spinner-container">
            <div class="spinner-border text-light" role="status"></div>
            <p class="text-light mt-2">Processing...</p>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            // View button click
            $('.view-btn').click(function() {
                const id = $(this).data('id');
                const type = $(this).data('type');
                $('#viewModal .modal-body').html('<div class="text-center"><div class="spinner-border text-primary" role="status"></div><p>Loading details...</p></div>');
                $('#viewModal').modal('show');
                
                $.get('fetch_details.php', { id: id, type: type }, function(data) {
                    $('#viewModal .modal-body').html(data);
                });
            });

            // Generate ticket button click
            $('.generate-ticket-btn').click(function() {
                if($(this).hasClass('disabled')) return;
                
                const id = $(this).data('id');
                const type = $(this).data('type');
                const btn = $(this);
                
                $('#loadingOverlay').removeClass('d-none');
                
                $.post('update_ticket.php', { id: id, type: type }, function(response) {
                    $('#loadingOverlay').addClass('d-none');
                    
                    if(response.success) {
                        btn.removeClass('btn-outline-success').addClass('btn-success disabled');
                        btn.attr('disabled', true);
                        btn.attr('title', 'Ticket Generated');
                        
                        // Update stats
                        location.reload();
                    } else {
                        alert('Error: ' + response.message);
                    }
                })
                .fail(function() {
                    $('#loadingOverlay').addClass('d-none');
                    alert('Server error occurred. Please try again.');
                });
            });

            // Checkin 1 button click
            $('.checkin1-btn').click(function() {
                if($(this).hasClass('disabled')) return;
                
                const id = $(this).data('id');
                const type = $(this).data('type');
                const btn = $(this);
                
                $('#loadingOverlay').removeClass('d-none');
                
                $.post('update_checkin.php', { id: id, type: type, day: 1 }, function(response) {
                    $('#loadingOverlay').addClass('d-none');
                    
                    if(response.success) {
                        btn.removeClass('btn-outline-info').addClass('btn-info disabled');
                        btn.attr('disabled', true);
                        btn.attr('title', 'Day 1 Checked In');
                        
                        // Update stats
                        location.reload();
                    } else {
                        alert('Error: ' + response.message);
                    }
                })
                .fail(function() {
                    $('#loadingOverlay').addClass('d-none');
                    alert('Server error occurred. Please try again.');
                });
            });

            // Checkin 2 button click
            $('.checkin2-btn').click(function() {
                if($(this).hasClass('disabled')) return;
                
                const id = $(this).data('id');
                const type = $(this).data('type');
                const btn = $(this);
                
                $('#loadingOverlay').removeClass('d-none');
                
                $.post('update_checkin.php', { id: id, type: type, day: 2 }, function(response) {
                    $('#loadingOverlay').addClass('d-none');
                    
                    if(response.success) {
                        btn.removeClass('btn-outline-warning').addClass('btn-warning disabled');
                        btn.attr('disabled', true);
                        btn.attr('title', 'Day 2 Checked In');
                        
                        // Update stats
                        location.reload();
                    } else {
                        alert('Error: ' + response.message);
                    }
                })
                .fail(function() {
                    $('#loadingOverlay').addClass('d-none');
                    alert('Server error occurred. Please try again.');
                });
            });

            // Search functionality
            $('#searchInput').on('keyup', function() {
                const value = $(this).val().toLowerCase();
                $("#studentTable tbody tr").filter(function() {
                    $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
                });
            });

            // Department filter
            $('#departmentFilter').on('change', function() {
                const value = $(this).val().toLowerCase();
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
                $("#studentTable tbody tr").show();
            });

            function applyFilters() {
                const departmentValue = $('#departmentFilter').val().toLowerCase();
                const typeValue = $('#typeFilter').val().toLowerCase();
                
                $("#studentTable tbody tr").each(function() {
                    const department = $(this).data('department').toLowerCase();
                    const type = $(this).data('type').toLowerCase();
                    
                    const departmentMatch = !departmentValue || department === departmentValue;
                    const typeMatch = !typeValue || type === typeValue;
                    
                    $(this).toggle(departmentMatch && typeMatch);
                });
            }
        });
    </script>
</body>
</html>
