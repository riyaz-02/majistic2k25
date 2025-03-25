<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit();
}

// Correct the include path
require_once __DIR__ . '/../../includes/db_config.php';

// Get filters from URL
$gender_filter = isset($_GET['gender']) ? $_GET['gender'] : '';
$competition_filter = isset($_GET['competition']) ? $_GET['competition'] : '';
$payment_status_filter = isset($_GET['payment_status']) ? $_GET['payment_status'] : '';
$department_filter = isset($_GET['department']) ? $_GET['department'] : '';
$passout_year_filter = isset($_GET['passout_year']) ? $_GET['passout_year'] : '';
$tab = isset($_GET['tab']) ? $_GET['tab'] : 'inhouse';

// Build filters for MySQL queries
$inhouse_conditions = [];
$alumni_conditions = [];
$params = [];

if ($gender_filter) {
    $inhouse_conditions[] = "gender = :gender";
    $alumni_conditions[] = "gender = :gender";
    $params[':gender'] = $gender_filter;
}
if ($competition_filter) {
    $inhouse_conditions[] = "competition_name = :competition";
    $params[':competition'] = $competition_filter;
}
if ($payment_status_filter) {
    $inhouse_conditions[] = "payment_status = :payment_status";
    $alumni_conditions[] = "payment_status = :payment_status";
    $params[':payment_status'] = $payment_status_filter;
}
if ($department_filter) {
    $inhouse_conditions[] = "department = :department";
    $alumni_conditions[] = "department = :department";
    $params[':department'] = $department_filter;
}
if ($passout_year_filter) {
    $alumni_conditions[] = "passout_year = :passout_year";
    $params[':passout_year'] = $passout_year_filter;
}

// Combine conditions into WHERE clauses
$inhouse_where = $inhouse_conditions ? 'WHERE ' . implode(' AND ', $inhouse_conditions) : '';
$alumni_where = $alumni_conditions ? 'WHERE ' . implode(' AND ', $alumni_conditions) : '';

// Fetch inhouse data without pagination limits
$inhouse_data = [];
try {
    $query = "SELECT * FROM registrations $inhouse_where ORDER BY registration_date DESC";
    $stmt = $db->prepare($query);
    $stmt->execute($params);
    $inhouse_data = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Error fetching inhouse data: " . $e->getMessage());
}

// Fetch alumni data without pagination limits
$alumni_data = [];
try {
    $query = "SELECT * FROM alumni_registrations $alumni_where ORDER BY registration_date DESC";
    $stmt = $db->prepare($query);
    $stmt->execute($params);
    $alumni_data = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Error fetching alumni data: " . $e->getMessage());
}

// Set result based on current tab
$result = $tab == 'inhouse' ? $inhouse_data : $alumni_data;

// Fetch distinct values for dropdowns
$inhouse_competitions = [];
try {
    $query = "SELECT DISTINCT competition_name FROM registrations WHERE competition_name IS NOT NULL";
    $stmt = $db->query($query);
    $inhouse_competitions = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Error fetching inhouse competitions: " . $e->getMessage());
}

$departments = [];
try {
    $query = "SELECT DISTINCT department FROM (
                SELECT department FROM registrations
                UNION
                SELECT department FROM alumni_registrations
              ) AS combined_departments";
    $stmt = $db->query($query);
    $departments = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Error fetching departments: " . $e->getMessage());
}

$passout_years = [];
try {
    $query = "SELECT DISTINCT passout_year FROM alumni_registrations WHERE passout_year IS NOT NULL";
    $stmt = $db->query($query);
    $passout_years = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Error fetching passout years: " . $e->getMessage());
}

// Calculate statistics
$stats = [
    'inhouse' => [
        'total' => 0,
        'paid' => 0,
        'not_paid' => 0
    ],
    'alumni' => [
        'total' => 0,
        'paid' => 0,
        'not_paid' => 0
    ]
];

try {
    $query = "SELECT 
                COUNT(*) AS total, 
                SUM(CASE WHEN payment_status = 'Paid' THEN 1 ELSE 0 END) AS paid,
                SUM(CASE WHEN payment_status = 'Not Paid' THEN 1 ELSE 0 END) AS not_paid
              FROM registrations";
    $stmt = $db->query($query);
    $stats['inhouse'] = $stmt->fetch(PDO::FETCH_ASSOC);

    $query = "SELECT 
                COUNT(*) AS total, 
                SUM(CASE WHEN payment_status = 'Paid' THEN 1 ELSE 0 END) AS paid,
                SUM(CASE WHEN payment_status = 'Not Paid' THEN 1 ELSE 0 END) AS not_paid
              FROM alumni_registrations";
    $stmt = $db->query($query);
    $stats['alumni'] = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Error calculating statistics: " . $e->getMessage());
}

// Handle CSV download
if (isset($_GET['download']) && $_GET['download'] == 'csv') {
    if ($tab == 'inhouse') {
        $data = [['Sl. No.', 'Student Name', 'JIS ID', 'Mobile', 'Email', 'Gender', 'Department', 
                  'Inhouse Competition', 'Competition', 'Payment Status', 'Registration Date']];
        
        $all_registrations = $inhouse_data;
        $sl_no = 1;
        foreach ($all_registrations as $row) {
            $data[] = [
                $sl_no++,
                $row['student_name'],
                $row['jis_id'],
                $row['mobile'],
                $row['email'],
                $row['gender'],
                $row['department'],
                $row['inhouse_competition'],
                $row['competition_name'],
                $row['payment_status'],
                $row['registration_date']
            ];
        }
        
        downloadCsv($data, "inhouse_registrations_" . date('Y-m-d') . ".csv");
    } elseif ($tab == 'alumni') {
        $data = [['Sl. No.', 'Name', 'JIS ID', 'Gender', 'Email', 'Mobile', 'Department', 
                  'Passout Year', 'Current Organization', 'Payment Status', 'Registration Date']];
        
        $all_registrations = $alumni_data;
        $sl_no = 1;
        foreach ($all_registrations as $row) {
            $data[] = [
                $sl_no++,
                $row['alumni_name'],
                $row['jis_id'],
                $row['gender'],
                $row['email'],
                $row['mobile'],
                $row['department'],
                $row['passout_year'],
                $row['current_organization'] ?? 'N/A',
                $row['payment_status'],
                $row['registration_date']
            ];
        }
        
        downloadCsv($data, "alumni_registrations_" . date('Y-m-d') . ".csv");
    }
}

function downloadCsv($data, $filename) {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    $fp = fopen('php://output', 'wb');
    foreach ($data as $line) {
        fputcsv($fp, $line);
    }
    fclose($fp);
    exit;
}
?>
