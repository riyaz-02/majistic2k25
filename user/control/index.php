<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in with appropriate role
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true || 
    !isset($_SESSION['admin_role']) || !in_array($_SESSION['admin_role'], ['Controller', 'Super Admin', 'Convenor', 'Department Coordinator'])) {
    // Not logged in or not authorized, redirect to login
    header('Location: ../login.php');
    exit;
}

require_once __DIR__ . '/../../includes/db_config.php';

// Fetch data from the database using PDO - include all students (paid and not paid)
$stmt1 = $db->prepare("SELECT * FROM registrations");
$stmt1->execute();
$registrations = $stmt1->fetchAll(PDO::FETCH_ASSOC);

$stmt2 = $db->prepare("SELECT * FROM alumni_registrations");
$stmt2->execute();
$alumni_registrations = $stmt2->fetchAll(PDO::FETCH_ASSOC);

// Combine data
$students = array_merge($registrations, $alumni_registrations);

// Sort combined array based on checkin_2_timestamp (most recent first)
// If no timestamp exists, use 0 (which will sort to the end of the list)
usort($students, function($a, $b) {
    // First prioritize students who have checked in for Day 2
    $aHasCheckin2 = isset($a['checkin_2']) && $a['checkin_2'] === "Yes" ? 1 : 0;
    $bHasCheckin2 = isset($b['checkin_2']) && $b['checkin_2'] === "Yes" ? 1 : 0;
    
    if ($aHasCheckin2 !== $bHasCheckin2) {
        return $bHasCheckin2 - $aHasCheckin2; // Day 2 checked-in students first
    }
    
    // Then sort by timestamp for students who have checked in for Day 2
    $timeA = isset($a['checkin_2_timestamp']) && !empty($a['checkin_2_timestamp']) ? 
        strtotime($a['checkin_2_timestamp']) : 0;
    $timeB = isset($b['checkin_2_timestamp']) && !empty($b['checkin_2_timestamp']) ? 
        strtotime($b['checkin_2_timestamp']) : 0;
    
    // If both have day 2 check-in, sort by timestamp
    if ($timeA && $timeB) {
        return $timeB - $timeA; // Descending order (most recent first)
    }
    
    // If no day 2 check-ins, fall back to day 1 check-in status
    $aHasCheckin1 = isset($a['checkin_1']) && $a['checkin_1'] === "Yes" ? 1 : 0;
    $bHasCheckin1 = isset($b['checkin_1']) && $b['checkin_1'] === "Yes" ? 1 : 0;
    
    if ($aHasCheckin1 !== $bHasCheckin1) {
        return $bHasCheckin1 - $aHasCheckin1; // Day 1 checked-in students next
    }
    
    // Finally sort by day 1 timestamp
    $time1A = isset($a['checkin_1_timestamp']) && !empty($a['checkin_1_timestamp']) ? 
        strtotime($a['checkin_1_timestamp']) : 0;
    $time1B = isset($b['checkin_1_timestamp']) && !empty($b['checkin_1_timestamp']) ? 
        strtotime($b['checkin_1_timestamp']) : 0;
    
    return $time1B - $time1A; // Descending order (most recent first)
});

// Calculate stats for paid students only
$paid_students = array_filter($students, fn($s) => isset($s['payment_status']) && $s['payment_status'] === "Paid");
$total_paid_students = count($paid_students);
$tickets_generated = count(array_filter($paid_students, fn($s) => isset($s['ticket_generated']) && $s['ticket_generated'] === "Yes"));
$tickets_not_generated = $total_paid_students - $tickets_generated;
$day1_checked_in = count(array_filter($paid_students, fn($s) => isset($s['checkin_1']) && $s['checkin_1'] === "Yes"));
$day2_checked_in = count(array_filter($paid_students, fn($s) => isset($s['checkin_2']) && $s['checkin_2'] === "Yes"));

// Count total students (paid and unpaid)
$total_students = count($students);
$total_unpaid_students = $total_students - $total_paid_students;

// Temporary flag for Day 1 - remove after Day 1 is over
$is_day_one = false;

// Function to format timestamp
function formatTimestamp($timestamp) {
    if (!$timestamp) return 'Not checked in';
    
    try {
        // Convert to DateTime object
        $dt = new DateTime($timestamp);
        
        // Add 5 hours and 30 minutes for IST
        $dt->modify('+5 hours 30 minutes');
        
        // Format timestamp with IST indicator
        return $dt->format('d-M-Y h:i A') . ' IST';
    } catch (Exception $e) {
        // Handle any datetime parsing errors
        error_log("Error formatting timestamp: " . $e->getMessage());
        return 'Invalid timestamp';
    }
}

// Before displaying timestamps, set the timezone to IST
date_default_timezone_set('Asia/Kolkata');

// Check if a department filter is set in the URL (for Convenor and Department Coordinator roles)
$selectedDepartment = isset($_GET['filter']) ? $_GET['filter'] : 'All';

// For Department Coordinator, set default filter to their department if not explicitly changed
if ($_SESSION['admin_role'] === 'Department Coordinator' && !isset($_GET['filter'])) {
    $selectedDepartment = isset($_SESSION['admin_department']) ? $_SESSION['admin_department'] : 'All';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Management - maJIStic 2K25</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
    <style>
        /* Modern dashboard styles */
        :root {
            --primary-color: #3a0ca3;
            --secondary-color: #4361ee;
            --accent-color: #7209b7;
            --light-color: #f8f9fa;
            --dark-color: #212529;
            --success-color: #4cc9f0;
            --warning-color: #f72585;
        }
        
        body {
            background-color: #f5f7fb;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        /* Navbar styling */
        .navbar {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            padding: 0.4rem 1rem; /* Reduced navbar height */
        }
        
        .navbar-brand {
            font-weight: 600;
            letter-spacing: 0.5px;
            display: flex;
            align-items: center;
        }
        
        .navbar-logo {
            height: 36px;
            margin-right: 10px;
        }
        
        .nav-user-info {
            color: white;
            padding: 0.3rem 1rem; /* Reduced padding */
            background-color: rgba(255, 255, 255, 0.1);
            border-radius: 50px;
            margin-right: 10px;
            font-size: 0.9rem; /* Slightly smaller text */
        }
        
        /* Dashboard header card */
        .dashboard-header {
            background: linear-gradient(135deg, #4cc9f0, #4361ee);
            color: white;
            border: none;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(76, 201, 240, 0.3);
            transition: transform 0.3s ease;
        }
        
        .dashboard-header:hover {
            transform: translateY(-5px);
        }
        
        .dashboard-header h2 {
            margin-bottom: 0.5rem;
            font-weight: 600;
            font-size: 1.75rem;
        }
        
        .dashboard-header p {
            opacity: 0.85;
            font-size: 1rem;
            margin-bottom: 0.5rem;
        }
        
        /* Notice styling */
        .paid-students-notice {
            font-weight: 600;
            color: #003566;
            background-color: #ade8f4;
            padding: 0.75rem;
            border-radius: 8px;
            border-left: 5px solid #0077b6;
            margin-top: 1rem;
            font-size: 1rem;
            text-align: center;
            box-shadow: 0 2px 8px rgba(0, 119, 182, 0.15);
            animation: gentle-pulse 2.5s infinite;
        }
        
        @keyframes gentle-pulse {
            0% { box-shadow: 0 2px 8px rgba(0, 119, 182, 0.15); }
            50% { box-shadow: 0 2px 15px rgba(0, 119, 182, 0.3); }
            100% { box-shadow: 0 2px 8px rgba(0, 119, 182, 0.15); }
        }
        
        /* Stat cards */
        .stat-card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
            overflow: hidden;
            height: 100%;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.08);
        }
        
        .stat-card .card-body {
            padding: 1.25rem;
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
        }
        
        .stat-card .stat-icon {
            height: 50px;
            width: 50px;
            background: linear-gradient(135deg, rgba(76, 201, 240, 0.2), rgba(67, 97, 238, 0.2));
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            margin-bottom: 1rem;
            font-size: 1.25rem;
            color: var(--primary-color);
        }
        
        .stat-card .card-title {
            color: #6c757d;
            font-size: 0.875rem;
            font-weight: 500;
            margin-bottom: 0.5rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .stat-card .stat-number {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--dark-color);
            margin-bottom: 0;
        }
        
        .stat-card.day-one .stat-icon,
        .stat-card.ticket-generated .stat-icon {
            background: linear-gradient(135deg, rgba(76, 201, 240, 0.2), rgba(76, 201, 240, 0.1));
            color: #0077b6;
        }
        
        .stat-card.day-two .stat-icon {
            background: linear-gradient(135deg, rgba(247, 37, 133, 0.2), rgba(247, 37, 133, 0.1));
            color: #f72585;
        }
        
        .stat-card.ticket-not-generated .stat-icon {
            background: linear-gradient(135deg, rgba(247, 37, 133, 0.2), rgba(247, 37, 133, 0.1));
            color: #f72585;
        }
        
        /* Filter card */
        .filter-card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
            margin-bottom: 1.5rem;
        }
        
        .filter-card .card-body {
            padding: 1.25rem;
        }
        
        .filter-card h5 {
            color: var(--dark-color);
            font-weight: 600;
            margin-bottom: 1.25rem;
        }
        
        /* Form elements */
        .form-control, .form-select {
            border-radius: 8px;
            padding: 0.6rem 1rem;
            border: 1px solid #e0e0e0;
            box-shadow: none;
            transition: all 0.3s ease;
        }
        
        .form-control:focus, .form-select:focus {
            border-color: #4361ee;
            box-shadow: 0 0 0 0.25rem rgba(67, 97, 238, 0.25);
        }
        
        .btn {
            border-radius: 8px;
            padding: 0.6rem 1.5rem;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .btn-primary {
            background-color: #4361ee;
            border-color: #4361ee;
        }
        
        .btn-primary:hover {
            background-color: #3a0ca3;
            border-color: #3a0ca3;
        }
        
        .btn-success {
            background-color: #4cc9f0;
            border-color: #4cc9f0;
        }
        
        .btn-success:hover {
            background-color: #0077b6;
            border-color: #0077b6;
        }
        
        .btn-secondary {
            background-color: #e9ecef;
            color: #495057;
            border-color: #e9ecef;
        }
        
        .btn-secondary:hover {
            background-color: #ced4da;
            color: #212529;
            border-color: #ced4da;
        }
        
        /* Container padding */
        .container-fluid {
            padding: 1.5rem;
        }
        
        /* Modal styling */
        .modal-content {
            border-radius: 12px;
            border: none;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }
        
        .modal-header {
            background-color: #4361ee;
            color: white;
            border-top-left-radius: 12px;
            border-top-right-radius: 12px;
            border-bottom: none;
        }
        
        .modal-title {
            font-weight: 600;
        }
        
        .modal-footer {
            border-top: 1px solid #e9ecef;
        }
        
        /* Loading overlay */
        .overlay-loading {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: rgba(0, 0, 0, 0.6);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 9999;
        }
        
        .spinner-container {
            background-color: rgba(255, 255, 255, 0.9);
            padding: 2rem;
            border-radius: 12px;
            text-align: center;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        }
        
        /* Keep cards in the same row on desktop */
        @media (min-width: 992px) {
            .stats-container {
                display: flex;
                flex-wrap: wrap;
            }
            
            .stats-container > div {
                flex: 1;
                min-width: 0; /* Allows flex items to shrink below content size */
            }
        }
        
        /* Modal permissions for specific roles */
        .role-permission {
            font-size: 0.85rem;
            margin-top: 5px;
            color: #6c757d;
        }
        
        /* Role indicator styles */
        .role-indicator {
            display: flex;
            align-items: center;
            gap: 5px;
            color: #4361ee;
            font-weight: 500;
        }
        
        .role-indicator i {
            font-size: 14px;
        }
    </style>
</head>
<body>
    <!-- Navigation bar with user info and logout button -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">
                <img src="../../images/majisticlogo.png" alt="maJIStic Logo" class="navbar-logo">
                <span class="d-none d-md-inline">Management Panel</span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <span class="nav-user-info">
                            <i class="fas fa-user-circle me-1"></i> <?php echo htmlspecialchars($_SESSION['admin_name']); ?> (<?php echo htmlspecialchars($_SESSION['admin_role']); ?>)
                        </span>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../logout.php"><i class="fas fa-sign-out-alt me-1"></i> Logout</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container-fluid">
        <div class="row mb-4">
            <div class="col-12">
                <div class="card dashboard-header">
                    <div class="card-body">
                        <h2><i class="fas fa-tachometer-alt me-2"></i>Registration Dashboard</h2>
                        <p>Monitor student registrations, payments, and attendance</p>
                        <div class="paid-students-notice">
                            <i class="fas fa-info-circle me-1"></i> THIS DASHBOARD DISPLAYS BOTH PAID AND UNPAID STUDENT RECORDS
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Stat Cards -->
        <div class="row stats-container mb-4">
            <div class="col-md-4 col-lg mb-3">
                <div class="card stat-card">
                    <div class="card-body">
                        <div class="stat-icon"><i class="fas fa-users"></i></div>
                        <h5 class="card-title">Total Students</h5>
                        <p class="stat-number"><?= $total_students ?></p>
                        <p class="text-muted mb-0"><?= $total_paid_students ?> Paid, <?= $total_unpaid_students ?> Unpaid</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4 col-lg mb-3">
                <div class="card stat-card ticket-generated">
                    <div class="card-body">
                        <div class="stat-icon"><i class="fas fa-ticket-alt"></i></div>
                        <h5 class="card-title">Tickets Generated</h5>
                        <p class="stat-number"><?= $tickets_generated ?></p>
                        <p class="text-muted mb-0"><?= round(($tickets_generated/$total_paid_students)*100) ?>% of paid students</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4 col-lg mb-3">
                <div class="card stat-card ticket-not-generated">
                    <div class="card-body">
                        <div class="stat-icon"><i class="fas fa-times-circle"></i></div>
                        <h5 class="card-title">Not Generated</h5>
                        <p class="stat-number"><?= $tickets_not_generated ?></p>
                        <p class="text-muted mb-0"><?= round(($tickets_not_generated/$total_paid_students)*100) ?>% of paid students</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg mb-3">
                <div class="card stat-card day-one">
                    <div class="card-body">
                        <div class="stat-icon"><i class="fas fa-calendar-day"></i></div>
                        <h5 class="card-title">Day 1 Check-Ins</h5>
                        <p class="stat-number"><?= $day1_checked_in ?> / <?= $total_paid_students ?></p>
                        <p class="text-muted mb-0"><?= round(($day1_checked_in/$total_paid_students)*100) ?>% attendance</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg mb-3">
                <div class="card stat-card day-two">
                    <div class="card-body">
                        <div class="stat-icon"><i class="fas fa-calendar-day"></i></div>
                        <h5 class="card-title">Day 2 Check-Ins</h5>
                        <p class="stat-number"><?= $day2_checked_in ?> / <?= $total_paid_students ?></p>
                        <p class="text-muted mb-0"><?= round(($day2_checked_in/$total_paid_students)*100) ?>% attendance</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Search and Filter -->
        <div class="row mb-4">
            <div class="col-md-12">
                <div class="card filter-card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="mb-0"><i class="fas fa-filter me-2"></i>Search & Filter</h5>
                            <!-- Download Button - Only for Super Admin -->
                            <?php if($_SESSION['admin_role'] === 'Super Admin'): ?>
                            <a href="download_excel.php" class="btn btn-success" title="Download All Registrations">
                                <i class="fas fa-file-excel me-1"></i> Export Data
                            </a>
                            <?php endif; ?>
                        </div>
                        <div class="row g-3">
                            <div class="col-md-3">
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                                    <input type="text" id="searchInput" class="form-control" placeholder="Search by name, ID, email...">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <select id="departmentFilter" class="form-select">
                                    <option value="">--Select Department--</option>
                                    <option value="CSE" <?= ($selectedDepartment == 'CSE') ? 'selected' : '' ?>>CSE</option>
                                    <option value="CSE AI-ML" <?= ($selectedDepartment == 'CSE AI-ML') ? 'selected' : '' ?>>CSE AI-ML</option>
                                    <option value="CST" <?= ($selectedDepartment == 'CST') ? 'selected' : '' ?>>CST</option>
                                    <option value="IT" <?= ($selectedDepartment == 'IT') ? 'selected' : '' ?>>IT</option>
                                    <option value="ECE" <?= ($selectedDepartment == 'ECE') ? 'selected' : '' ?>>ECE</option>
                                    <option value="EE" <?= ($selectedDepartment == 'EE') ? 'selected' : '' ?>>EE</option>
                                    <option value="BME" <?= ($selectedDepartment == 'BME') ? 'selected' : '' ?>>BME</option>
                                    <option value="CE" <?= ($selectedDepartment == 'CE') ? 'selected' : '' ?>>CE</option>
                                    <option value="ME" <?= ($selectedDepartment == 'ME') ? 'selected' : '' ?>>ME</option>
                                    <option value="AGE" <?= ($selectedDepartment == 'AGE') ? 'selected' : '' ?>>AGE</option>
                                    <option value="BBA" <?= ($selectedDepartment == 'BBA') ? 'selected' : '' ?>>BBA</option>
                                    <option value="MBA" <?= ($selectedDepartment == 'MBA') ? 'selected' : '' ?>>MBA</option>
                                    <option value="BCA" <?= ($selectedDepartment == 'BCA') ? 'selected' : '' ?>>BCA</option>
                                    <option value="MCA" <?= ($selectedDepartment == 'MCA') ? 'selected' : '' ?>>MCA</option>
                                    <option value="Diploma ME" <?= ($selectedDepartment == 'Diploma ME') ? 'selected' : '' ?>>Diploma ME</option>
                                    <option value="Diploma CE" <?= ($selectedDepartment == 'Diploma CE') ? 'selected' : '' ?>>Diploma CE</option>
                                    <option value="Diploma EE" <?= ($selectedDepartment == 'Diploma EE') ? 'selected' : '' ?>>Diploma EE</option>
                                    <option value="B. Pharmacy" <?= ($selectedDepartment == 'B. Pharmacy') ? 'selected' : '' ?>>Pharmacy</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <select id="typeFilter" class="form-select">
                                    <option value="">All Types</option>
                                    <option value="student">Student</option>
                                    <option value="alumni">Alumni</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <select id="paymentFilter" class="form-select">
                                    <option value="">All Payment Status</option>
                                    <option value="Paid">Paid</option>
                                    <option value="Not Paid">Not Paid</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <select id="checkInFilter" class="form-select">
                                    <option value="">All Check-In Status</option>
                                    <option value="checkin1">Day 1 Checked In</option>
                                    <option value="checkin2">Day 2 Checked In</option>
                                    <option value="not-checkin1">Day 1 Not Checked In</option>
                                    <option value="not-checkin2">Day 2 Not Checked In</option>
                                </select>
                            </div>
                            <div class="col-md-1">
                                <button id="resetFilters" class="btn btn-secondary w-100">
                                    <i class="fas fa-redo-alt"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Student List Table - Keep existing table as requested -->
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title mb-4"><i class="fas fa-list me-2"></i>Student List</h5>
                        <div class="table-responsive">
                            <!-- Keep the exact same table structure and styling -->
                            <table class="table table-striped table-hover" id="studentTable">
                                <thead class="table-dark">
                                    <tr>
                                        <th>#</th>
                                        <th>Name</th>
                                        <th>JIS ID</th>
                                        <th>Department</th>
                                        <th>Type</th>
                                        <th>Phone</th>
                                        <th>Payment Status</th>
                                        <th>Day 1 Check-in</th>
                                        <th>Day 2 Check-in</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $serialNo = 1; // Initialize serial number counter
                                    foreach ($students as $student): 
                                        // Determine if student or alumni based on available fields
                                        $type = isset($student['passout_year']) ? 'alumni' : 'student';
                                        
                                        // Get the name based on type
                                        $name = $type === 'alumni' ? ($student['alumni_name'] ?? 'N/A') : ($student['student_name'] ?? 'N/A');
                                        
                                        // Get other fields
                                        $jisId = $student['jis_id'] ?? 'N/A';
                                        $department = $student['department'] ?? 'N/A';
                                        $phone = $student['mobile'] ?? 'N/A';
                                        $paymentStatus = $student['payment_status'] ?? 'Not Paid';
                                        
                                        // Get check-in timestamp for Day 1
                                        $day1CheckinTimestamp = isset($student['checkin_1']) && $student['checkin_1'] === "Yes" && 
                                                            isset($student['checkin_1_timestamp']) ? 
                                                            $student['checkin_1_timestamp'] : null;
                                        
                                        // Get check-in timestamp for Day 2
                                        $day2CheckinTimestamp = isset($student['checkin_2']) && $student['checkin_2'] === "Yes" && 
                                                            isset($student['checkin_2_timestamp']) ? 
                                                            $student['checkin_2_timestamp'] : null;
                                        
                                        // Format timestamps
                                        $formattedDay1Timestamp = formatTimestamp($day1CheckinTimestamp);
                                        
                                        // Special handling for Day 2 timestamp
                                        if ($day2CheckinTimestamp) {
                                            try {
                                                $dt2 = new DateTime($day2CheckinTimestamp);
                                                $dt2->modify('+5 hours 30 minutes');
                                                $formattedDay2Timestamp = $dt2->format('d-M-Y h:i A') . ' IST';
                                            } catch (Exception $e) {
                                                error_log("Error formatting Day 2 timestamp: " . $e->getMessage());
                                                $formattedDay2Timestamp = 'Invalid timestamp';
                                            }
                                        } else {
                                            $formattedDay2Timestamp = 'Not checked in';
                                        }
                                        
                                        // Check statuses
                                        $ticketGenerated = isset($student['ticket_generated']) && $student['ticket_generated'] === "Yes";
                                        $day1CheckedIn = isset($student['checkin_1']) && $student['checkin_1'] === "Yes";
                                        $day2CheckedIn = isset($student['checkin_2']) && $student['checkin_2'] === "Yes";
                                        $isPaid = $paymentStatus === 'Paid';
                                    ?>
                                        <tr data-department="<?= $department ?>" data-type="<?= $type ?>" data-payment="<?= $paymentStatus ?>" 
                                            data-checkin1="<?= $day1CheckedIn ? 'Yes' : 'No' ?>" data-checkin2="<?= $day2CheckedIn ? 'Yes' : 'No' ?>">
                                            <td><?= $serialNo++ ?></td>
                                            <td><?= htmlspecialchars($name) ?></td>
                                            <td><?= htmlspecialchars($jisId) ?></td>
                                            <td><?= htmlspecialchars($department) ?></td>
                                            <td><span class="badge bg-<?= $type === 'alumni' ? 'warning' : 'info' ?>"><?= ucfirst($type) ?></span></td>
                                            <td><?= htmlspecialchars($phone) ?></td>
                                            <td><span class="badge bg-<?= $isPaid ? 'success' : 'danger' ?>"><?= $paymentStatus ?></span></td>
                                            <td><?= $formattedDay1Timestamp ?></td>
                                            <td><?= $formattedDay2Timestamp ?></td>
                                            <td>
                                                <button class="btn btn-sm btn-primary view-btn" data-id="<?= $student['id'] ?>" data-type="<?= $type ?>" title="View Details">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                
                                                <!-- All check-in and ticket buttons are disabled -->
                                                <!-- <button class="btn btn-sm btn-secondary disabled" disabled title="Feature Disabled">
                                                    <i class="fas fa-ticket-alt"></i>
                                                </button>
                                                <button class="btn btn-sm btn-secondary disabled" disabled title="Feature Disabled">
                                                    <i class="fas fa-calendar-check"></i> 1
                                                </button>
                                                <button class="btn btn-sm btn-secondary disabled" disabled title="Feature Disabled">
                                                    <i class="fas fa-calendar-check"></i> 2
                                                </button> -->
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

    <!-- Student Details Modal - Accessible to all roles -->
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
                <div class="modal-footer d-flex justify-content-between">
                    <div class="role-indicator">
                        <i class="fas fa-user-shield"></i>
                        <?php if($_SESSION['admin_role'] === 'Department Coordinator'): ?>
                            Department Coordinator
                        <?php elseif($_SESSION['admin_role'] === 'Convenor'): ?>
                            Convenor
                        <?php else: ?>
                            <?php echo htmlspecialchars($_SESSION['admin_role']); ?>
                        <?php endif; ?>
                    </div>
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
                
                // Pass additional parameters to fetch_details.php
                $.get('fetch_details.php', { 
                    id: id, 
                    type: type,
                    role: '<?php echo $_SESSION['admin_role']; ?>',
                    department: '<?php echo isset($_SESSION['admin_department']) ? $_SESSION['admin_department'] : ""; ?>'
                }, function(data) {
                    $('#viewModal .modal-body').html(data);
                });
            });

            // Function to update serial numbers on visible rows
            function updateSerialNumbers() {
                let counter = 1;
                $("#studentTable tbody tr:visible").each(function() {
                    $(this).find("td:first").text(counter++);
                });
            }

            // Search functionality
            $('#searchInput').on('keyup', function() {
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

            // Payment status filter
            $('#paymentFilter').on('change', function() {
                applyFilters();
            });

            // Check-in status filter
            $('#checkInFilter').on('change', function() {
                applyFilters();
            });

            // Reset filters
            $('#resetFilters').on('click', function() {
                $('#searchInput').val('');
                $('#departmentFilter').val('');
                $('#typeFilter').val('');
                $('#paymentFilter').val('');
                $('#checkInFilter').val('');
                $("#studentTable tbody tr").show();
                
                // Update serial numbers after showing all rows
                updateSerialNumbers();
            });

            function applyFilters() {
                const searchValue = $('#searchInput').val().toLowerCase();
                const departmentValue = $('#departmentFilter').val().toLowerCase();
                const typeValue = $('#typeFilter').val().toLowerCase();
                const paymentValue = $('#paymentFilter').val();
                const checkInValue = $('#checkInFilter').val();
                
                $("#studentTable tbody tr").each(function() {
                    const rowText = $(this).text().toLowerCase();
                    const department = $(this).data('department').toLowerCase();
                    const type = $(this).data('type').toLowerCase();
                    const payment = $(this).data('payment');
                    const checkin1 = $(this).data('checkin1');
                    const checkin2 = $(this).data('checkin2');
                    
                    const searchMatch = !searchValue || rowText.indexOf(searchValue) > -1;
                    const departmentMatch = !departmentValue || department === departmentValue;
                    const typeMatch = !typeValue || type === typeValue;
                    const paymentMatch = !paymentValue || 
                        (paymentValue === 'Paid' && payment === 'Paid') || 
                        (paymentValue === 'Not Paid' && payment !== 'Paid');
                    
                    let checkInMatch = true;
                    if (checkInValue) {
                        if (checkInValue === 'checkin1') {
                            checkInMatch = checkin1 === 'Yes';
                        } else if (checkInValue === 'checkin2') {
                            checkInMatch = checkin2 === 'Yes';
                        } else if (checkInValue === 'not-checkin1') {
                            checkInMatch = checkin1 === 'No';
                        } else if (checkInValue === 'not-checkin2') {
                            checkInMatch = checkin2 === 'No';
                        }
                    }
                    
                    $(this).toggle(searchMatch && departmentMatch && typeMatch && paymentMatch && checkInMatch);
                });
                
                // Update serial numbers after filtering
                updateSerialNumbers();
            }
        });
    </script>
</body>
</html>
