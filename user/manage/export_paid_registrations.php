<?php
session_start();
require_once '../../includes/db_config.php';

// Check if user is logged in with admin privileges
if (!isset($_SESSION['admin_id']) || !isset($_SESSION['admin_role']) || 
    ($_SESSION['admin_role'] !== 'Manage Website' && $_SESSION['admin_role'] !== 'Controller' && $_SESSION['admin_role'] !== 'Super Admin')) {
    header('Location: ../login.php');
    exit;
}

// Fetch all paid registrations from both tables
try {
    // Student registrations
    $stmt1 = $db->prepare("SELECT id, student_name AS name, jis_id, mobile, email, department, 
                           payment_status, receipt_number, payment_updated_by, payment_update_timestamp, 
                           paid_amount, 'Student' AS type
                           FROM registrations 
                           WHERE payment_status = 'Paid'
                           ORDER BY payment_update_timestamp DESC");
    $stmt1->execute();
    $student_registrations = $stmt1->fetchAll(PDO::FETCH_ASSOC);

    // Alumni registrations
    $stmt2 = $db->prepare("SELECT id, alumni_name AS name, jis_id, mobile, email, department, 
                           payment_status, receipt_number, payment_updated_by, payment_update_timestamp, 
                           paid_amount, 'Alumni' AS type
                           FROM alumni_registrations 
                           WHERE payment_status = 'Paid'
                           ORDER BY payment_update_timestamp DESC");
    $stmt2->execute();
    $alumni_registrations = $stmt2->fetchAll(PDO::FETCH_ASSOC);

    // Combine results
    $registrations = array_merge($student_registrations, $alumni_registrations);

    // Sort by payment update timestamp (most recent first)
    usort($registrations, function($a, $b) {
        return strtotime($b['payment_update_timestamp']) - strtotime($a['payment_update_timestamp']);
    });

    // Set headers for Excel download
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment;filename="majistic_paid_registrations_'.date('Y-m-d').'.xls"');
    header('Cache-Control: max-age=0');

    // Create Excel content
    echo "<!DOCTYPE html>
    <html>
    <head>
        <meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\" />
        <title>maJIStic 2K25 - Paid Registrations</title>
        <style>
            table { border-collapse: collapse; width: 100%; }
            th, td { border: 1px solid #000; padding: 5px; text-align: left; }
            th { background-color: #f2f2f2; }
        </style>
    </head>
    <body>
        <h2>maJIStic 2K25 - Paid Registrations (Generated: ".date('Y-m-d H:i:s').")</h2>
        <table>
            <thead>
                <tr>
                    <th>No.</th>
                    <th>Name</th>
                    <th>Type</th>
                    <th>JIS ID</th>
                    <th>Department</th>
                    <th>Mobile</th>
                    <th>Email</th>
                    <th>Receipt Number</th>
                    <th>Amount Paid</th>
                    <th>Payment Date</th>
                    <th>Updated By</th>
                </tr>
            </thead>
            <tbody>";

    $count = 0;
    foreach ($registrations as $reg) {
        $count++;
        echo "<tr>
            <td>".$count."</td>
            <td>".htmlspecialchars($reg['name'])."</td>
            <td>".htmlspecialchars($reg['type'])."</td>
            <td>".htmlspecialchars($reg['jis_id'])."</td>
            <td>".htmlspecialchars($reg['department'])."</td>
            <td>".htmlspecialchars($reg['mobile'])."</td>
            <td>".htmlspecialchars($reg['email'])."</td>
            <td>".htmlspecialchars($reg['receipt_number'] ?? 'N/A')."</td>
            <td>".htmlspecialchars(number_format((float)$reg['paid_amount'], 2))."</td>
            <td>".(!empty($reg['payment_update_timestamp']) ? date('d M Y, h:i A', strtotime($reg['payment_update_timestamp'])) : 'N/A')."</td>
            <td>".htmlspecialchars($reg['payment_updated_by'] ?? 'N/A')."</td>
        </tr>";
    }

    echo "</tbody>
        </table>
    </body>
    </html>";

} catch (PDOException $e) {
    // If error, redirect back with error message
    $_SESSION['error_message'] = "Export failed: " . $e->getMessage();
    header('Location: paid_registrations.php');
    exit;
}
?>
