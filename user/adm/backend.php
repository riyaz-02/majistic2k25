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

// Remove pagination - show all entries
$items_per_page = 1000000; // Use a very large number to essentially show all records
$page = 1;
$skip = 0;

// Build filters for MongoDB queries
$inhouse_filter = [];
if ($gender_filter) $inhouse_filter['gender'] = ['$regex' => $gender_filter, '$options' => 'i'];
if ($competition_filter) $inhouse_filter['competition_name'] = ['$regex' => $competition_filter, '$options' => 'i'];
if ($payment_status_filter) $inhouse_filter['payment_status'] = ['$regex' => $payment_status_filter, '$options' => 'i'];
if ($department_filter) $inhouse_filter['department'] = ['$regex' => $department_filter, '$options' => 'i'];

$alumni_filter = [];
if ($gender_filter) $alumni_filter['gender'] = ['$regex' => $gender_filter, '$options' => 'i'];
if ($payment_status_filter) $alumni_filter['payment_status'] = ['$regex' => $payment_status_filter, '$options' => 'i'];
if ($passout_year_filter) $alumni_filter['passout_year'] = $passout_year_filter;
if ($department_filter) $alumni_filter['department'] = ['$regex' => $department_filter, '$options' => 'i'];

// MongoDB options for sorting by registration date descending
$options = [
    'sort' => ['registration_date' => -1]
];

// Show all entries without pagination
$items_per_page = 1000000; // Setting a very high number to show all entries
$page = 1;
$skip = 0;

// Load data for both tabs regardless of current tab
$inhouse_data = [];
$alumni_data = [];

// Fetch inhouse data
$cursor = $registrations->find($inhouse_filter, $options);
foreach ($cursor as $doc) {
    $doc_array = json_decode(json_encode($doc), true);
    if (isset($doc_array['_id']) && $doc_array['_id'] instanceof MongoDB\BSON\ObjectId) {
        $doc_array['_id'] = (string)$doc_array['_id'];
    }
    $inhouse_data[] = $doc_array;
}

// Fetch alumni data
$cursor = $alumni_registrations->find($alumni_filter, $options);
foreach ($cursor as $doc) {
    $doc_array = json_decode(json_encode($doc), true);
    if (isset($doc_array['_id']) && $doc_array['_id'] instanceof MongoDB\BSON\ObjectId) {
        $doc_array['_id'] = (string)$doc_array['_id'];
    }
    $alumni_data[] = $doc_array;
}

// Set result based on current tab
$result = $tab == 'inhouse' ? $inhouse_data : $alumni_data;

// Set total pages to 1 since we're showing all records
$inhouse_total_pages = 1;
$alumni_total_pages = 1;

// Get distinct values for dropdowns in a format compatible with the template
$inhouse_competitions = [];
$competitions = $registrations->distinct('competition_name', ['competition_name' => ['$exists' => true]]);
foreach ($competitions as $comp) {
    $inhouse_competitions[] = ['competition_name' => $comp];
}

$departments = [];
$dept_list = $registrations->distinct('department', ['department' => ['$exists' => true]]);
foreach ($dept_list as $dept) {
    $departments[] = ['department' => $dept];
}

$passout_years = [];
$year_list = $alumni_registrations->distinct('passout_year', ['passout_year' => ['$exists' => true]]);
foreach ($year_list as $year) {
    $passout_years[] = ['passout_year' => $year];
}

// Calculate statistics with only payment status
$stats = [
    'inhouse' => [
        'total' => $registrations->countDocuments(),
        'paid' => $registrations->countDocuments(['payment_status' => 'Paid']),
        'not_paid' => $registrations->countDocuments(['payment_status' => 'Not Paid'])
    ],
    'alumni' => [
        'total' => $alumni_registrations->countDocuments(),
        'paid' => $alumni_registrations->countDocuments(['payment_status' => 'Paid']),
        'not_paid' => $alumni_registrations->countDocuments(['payment_status' => 'Not Paid'])
    ]
];

// Handle CSV download
if (isset($_GET['download']) && $_GET['download'] == 'csv') {
    if ($tab == 'inhouse') {
        $data = [['Sl. No.', 'Student Name', 'JIS ID', 'Mobile', 'Email', 'Gender', 'Department', 
                  'Inhouse Competition', 'Competition', 'Payment Status', 'Registration Date']];
        
        $all_registrations = $registrations->find($inhouse_filter, ['sort' => ['registration_date' => -1]]);
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
        
        $all_registrations = $alumni_registrations->find($alumni_filter, ['sort' => ['registration_date' => -1]]);
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
