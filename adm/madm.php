<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit();
}

include '../includes/db_config.php';

// Fetch filtered data if filters are applied
$gender_filter = isset($_GET['gender']) ? $_GET['gender'] : '';
$competition_filter = isset($_GET['competition']) ? $_GET['competition'] : '';
$payment_status_filter = isset($_GET['payment_status']) ? $_GET['payment_status'] : '';
$tab = isset($_GET['tab']) ? $_GET['tab'] : 'inhouse';

$inhouse_query = "SELECT * FROM registrations WHERE 1=1";
if ($gender_filter) {
    $inhouse_query .= " AND gender = '$gender_filter'";
}
if ($competition_filter) {
    $inhouse_query .= " AND competition_name = '$competition_filter'";
}
if ($payment_status_filter) {
    $inhouse_query .= " AND payment_status = '$payment_status_filter'";
}
$inhouse_query .= " ORDER BY registration_date DESC";

$outhouse_query = "SELECT * FROM registrations_outhouse WHERE 1=1";
if ($gender_filter) {
    $outhouse_query .= " AND gender = '$gender_filter'";
}
if ($competition_filter) {
    $outhouse_query .= " AND competition_name = '$competition_filter'";
}
if ($payment_status_filter) {
    $outhouse_query .= " AND payment_status = '$payment_status_filter'";
}
$outhouse_query .= " ORDER BY registration_date DESC";

$inhouse_result = $conn->query($inhouse_query);
$outhouse_result = $conn->query($outhouse_query);

// Fetch statistics
$inhouse_paid_query = "SELECT COUNT(*) as count FROM registrations WHERE payment_status = 'Paid'";
$inhouse_not_paid_query = "SELECT COUNT(*) as count FROM registrations WHERE payment_status = 'Not Paid'";
$outhouse_paid_query = "SELECT COUNT(*) as count FROM registrations_outhouse WHERE payment_status = 'Paid'";
$outhouse_not_paid_query = "SELECT COUNT(*) as count FROM registrations_outhouse WHERE payment_status = 'Not Paid'";
$inhouse_total_query = "SELECT COUNT(*) as count FROM registrations";
$outhouse_total_query = "SELECT COUNT(*) as count FROM registrations_outhouse";

$inhouse_paid_result = $conn->query($inhouse_paid_query)->fetch_assoc();
$inhouse_not_paid_result = $conn->query($inhouse_not_paid_query)->fetch_assoc();
$outhouse_paid_result = $conn->query($outhouse_paid_query)->fetch_assoc();
$outhouse_not_paid_result = $conn->query($outhouse_not_paid_query)->fetch_assoc();
$inhouse_total_result = $conn->query($inhouse_total_query)->fetch_assoc();
$outhouse_total_result = $conn->query($outhouse_total_query)->fetch_assoc();

function array_to_csv_download($array, $filename = "export.csv", $delimiter = ",") {
    header('Content-Type: application/csv');
    header('Content-Disposition: attachment; filename="' . $filename . '";');

    $f = fopen('php://output', 'w');
    foreach ($array as $line) {
        fputcsv($f, $line, $delimiter);
    }
}

if (isset($_GET['download']) && $_GET['download'] == 'csv') {
    $data = [];
    if ($tab == 'inhouse') {
        $data[] = ['Sl. No.', 'Student Name', 'JIS ID', 'Mobile', 'Email', 'Roll No', 'Gender', 'Department', 'Inhouse Competition', 'Competition', 'Payment Status', 'Payment ID', 'Amount', 'Amount Paid', 'Payment Date', 'Registration Date'];
        $sl_no = 1;
        while ($row = $inhouse_result->fetch_assoc()) {
            $data[] = [
                $sl_no++,
                $row['student_name'],
                $row['jis_id'],
                $row['mobile'],
                $row['email'],
                $row['roll_no'],
                $row['gender'],
                $row['department'],
                $row['inhouse_competition'],
                $row['competition_name'],
                $row['payment_status'],
                $row['payment_id'],
                $row['amount'],
                $row['amount_paid'],
                $row['payment_date'],
                $row['registration_date']
            ];
        }
    } else {
        $data[] = ['Sl. No.', 'Leader Name', 'Gender', 'Email', 'Contact Number', 'College Name', 'College ID', 'Course Name', 'Competition Name', 'Team Name', 'Team Members', 'Team Members Contact', 'Payment Status', 'Payment ID', 'Amount Paid', 'Payment Date', 'Registration Date'];
        $sl_no = 1;
        while ($row = $outhouse_result->fetch_assoc()) {
            $data[] = [
                $sl_no++,
                $row['leader_name'],
                $row['gender'],
                $row['email'],
                $row['contact_number'],
                $row['college_name'],
                $row['college_id'],
                $row['course_name'],
                $row['competition_name'],
                $row['team_name'],
                isset($row['team_members']) ? implode(", ", json_decode($row['team_members'])) : '',
                isset($row['team_members_contact']) ? implode(", ", json_decode($row['team_members_contact'])) : '',
                $row['payment_status'],
                $row['payment_id'],
                $row['amount_paid'],
                $row['payment_date'],
                $row['registration_date']
            ];
        }
    }
    array_to_csv_download($data);
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        .navbar {
            background-color: #333;
            color: white;
            padding: 15px;
            text-align: center;
        }
        .tabs {
            display: flex;
            justify-content: center;
            margin: 20px 0;
        }
        .tab {
            padding: 10px 20px;
            cursor: pointer;
            background-color: #ddd;
            margin: 0 5px;
            border-radius: 5px;
        }
        .tab.active {
            background-color: #333;
            color: white;
        }
        .tab-content {
            display: none;
        }
        .tab-content.active {
            display: block;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        table, th, td {
            border: 1px solid #ddd;
        }
        th, td {
            padding: 10px;
            text-align: left;
        }
        th {
            background-color: #333;
            color: white;
        }
        .container {
            width: 90%;
            margin: 0 auto;
        }
        .stats {
            display: flex;
            justify-content: space-around;
            margin: 20px 0;
        }
        .card {
            background-color: #fff;
            border-radius: 5px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            padding: 20px;
            text-align: center;
            width: 200px;
        }
        .card.green {
            background-color: #4CAF50;
            color: white;
        }
        .card.red {
            background-color: #f44336;
            color: white;
        }
        .card.blue {
            background-color: #2196F3;
            color: white;
        }
        .card h3 {
            margin: 0;
            font-size: 24px;
        }
        .card p {
            margin: 5px 0 0;
            font-size: 18px;
        }
        @media (max-width: 768px) {
            .stats {
                flex-direction: column;
                align-items: center;
            }
            .card {
                width: 90%;
                margin-bottom: 20px;
            }
            table, th, td {
                font-size: 12px;
            }
        }
        .filters {
            display: flex;
            justify-content: flex-start;
            align-items: center;
            margin: 20px 0;
        }
        .filters select {
            padding: 5px;
            font-size: 16px;
            margin-right: 10px;
        }
        .download-buttons {
            display: flex;
            justify-content: flex-end;
            align-items: center;
            margin: 20px 0;
        }
        .download-buttons button {
            padding: 10px 20px;
            margin-left: 10px;
            cursor: pointer;
            border: none;
            border-radius: 5px;
            background-color: #333;
            color: white;
        }
        .table-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
    </style>
</head>
<body>
    <div class="navbar">
        <h1>Admin Panel</h1>
        <a href="logout.php" style="color: white;">Logout</a>
    </div>
    <div class="tabs">
        <div class="tab active" data-tab="inhouse">Inhouse Registrations</div>
        <div class="tab" data-tab="outhouse">Outhouse Registrations</div>
    </div>
    <div class="container">
        <div id="inhouse" class="tab-content active">
            <div class="stats">
                <div class="card blue">
                    <h3><?php echo $inhouse_total_result['count']; ?></h3>
                    <p>Total Inhouse</p>
                </div>
                <div class="card green">
                    <h3><?php echo $inhouse_paid_result['count']; ?></h3>
                    <p>Inhouse Paid</p>
                </div>
                <div class="card red">
                    <h3><?php echo $inhouse_not_paid_result['count']; ?></h3>
                    <p>Inhouse Not Paid</p>
                </div>
            </div>
            <div class="table-header">
                <h2>Inhouse Registrations</h2>
                <div class="filters">
                    <label for="gender">Gender:</label>
                    <select id="gender" name="gender" onchange="applyFilters()">
                        <option value="">All</option>
                        <option value="Male" <?php if ($gender_filter == 'Male') echo 'selected'; ?>>Male</option>
                        <option value="Female" <?php if ($gender_filter == 'Female') echo 'selected'; ?>>Female</option>
                    </select>
                    <label for="competition">Competition:</label>
                    <select id="competition" name="competition" onchange="applyFilters()">
                        <option value="">All</option>
                        <!-- Add competition options dynamically if needed -->
                    </select>
                    <label for="payment_status">Payment Status:</label>
                    <select id="payment_status" name="payment_status" onchange="applyFilters()">
                        <option value="">All</option>
                        <option value="Paid" <?php if ($payment_status_filter == 'Paid') echo 'selected'; ?>>Paid</option>
                        <option value="Not Paid" <?php if ($payment_status_filter == 'Not Paid') echo 'selected'; ?>>Not Paid</option>
                    </select>
                </div>
                <div class="download-buttons">
                    <button onclick="downloadCSV('inhouse')">Download CSV</button>
                </div>
            </div>
            <table>
                <tr>
                    <th>Sl. No.</th>
                    <th>Student Name</th>
                    <th>JIS ID</th>
                    <th>Mobile</th>
                    <th>Email</th>
                    <th>Roll No</th>
                    <th>Gender</th>
                    <th>Department</th>
                    <th>Inhouse Competition</th>
                    <th>Competition</th>
                    <th>Payment Status</th>
                    <th>Payment ID</th>
                    <th>Amount</th>
                    <th>Amount Paid</th>
                    <th>Payment Date</th>
                    <th>Registration Date</th>
                </tr>
                <?php $sl_no = 1; ?>
                <?php while ($row = $inhouse_result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $sl_no++; ?></td>
                    <td><?php echo isset($row['student_name']) ? $row['student_name'] : ''; ?></td>
                    <td><?php echo isset($row['jis_id']) ? $row['jis_id'] : ''; ?></td>
                    <td><?php echo isset($row['mobile']) ? $row['mobile'] : ''; ?></td>
                    <td><?php echo isset($row['email']) ? $row['email'] : ''; ?></td>
                    <td><?php echo isset($row['roll_no']) ? $row['roll_no'] : ''; ?></td>
                    <td><?php echo isset($row['gender']) ? $row['gender'] : ''; ?></td>
                    <td><?php echo isset($row['department']) ? $row['department'] : ''; ?></td>
                    <td><?php echo isset($row['inhouse_competition']) ? $row['inhouse_competition'] : ''; ?></td>
                    <td><?php echo isset($row['competition_name']) ? $row['competition_name'] : ''; ?></td>
                    <td><?php echo isset($row['payment_status']) ? $row['payment_status'] : ''; ?></td>
                    <td><?php echo isset($row['payment_id']) ? $row['payment_id'] : ''; ?></td>
                    <td><?php echo isset($row['amount']) ? $row['amount'] : ''; ?></td>
                    <td><?php echo isset($row['amount_paid']) ? $row['amount_paid'] : ''; ?></td>
                    <td><?php echo isset($row['payment_date']) ? $row['payment_date'] : ''; ?></td>
                    <td><?php echo isset($row['registration_date']) ? $row['registration_date'] : ''; ?></td>
                </tr>
                <?php endwhile; ?>
            </table>
        </div>
        <div id="outhouse" class="tab-content">
            <div class="stats">
                <div class="card blue">
                    <h3><?php echo $outhouse_total_result['count']; ?></h3>
                    <p>Total Outhouse</p>
                </div>
                <div class="card green">
                    <h3><?php echo $outhouse_paid_result['count']; ?></h3>
                    <p>Outhouse Paid</p>
                </div>
                <div class="card red">
                    <h3><?php echo $outhouse_not_paid_result['count']; ?></h3>
                    <p>Outhouse Not Paid</p>
                </div>
            </div>
            <div class="table-header">
                <h2>Outhouse Registrations</h2>
                <div class="filters">
                    <label for="gender">Gender:</label>
                    <select id="gender" name="gender" onchange="applyFilters()">
                        <option value="">All</option>
                        <option value="Male" <?php if ($gender_filter == 'Male') echo 'selected'; ?>>Male</option>
                        <option value="Female" <?php if ($gender_filter == 'Female') echo 'selected'; ?>>Female</option>
                    </select>
                    <label for="competition">Competition:</label>
                    <select id="competition" name="competition" onchange="applyFilters()">
                        <option value="">All</option>
                        <!-- Add competition options dynamically if needed -->
                    </select>
                    <label for="payment_status">Payment Status:</label>
                    <select id="payment_status" name="payment_status" onchange="applyFilters()">
                        <option value="">All</option>
                        <option value="Paid" <?php if ($payment_status_filter == 'Paid') echo 'selected'; ?>>Paid</option>
                        <option value="Not Paid" <?php if ($payment_status_filter == 'Not Paid') echo 'selected'; ?>>Not Paid</option>
                    </select>
                </div>
                <div class="download-buttons">
                    <button onclick="downloadCSV('outhouse')">Download CSV</button>
                </div>
            </div>
            <table>
                <tr>
                    <th>Sl. No.</th>
                    <th>Leader Name</th>
                    <th>Gender</th>
                    <th>Email</th>
                    <th>Contact Number</th>
                    <th>College Name</th>
                    <th>College ID</th>
                    <th>Course Name</th>
                    <th>Competition Name</th>
                    <th>Team Name</th>
                    <th>Team Members</th>
                    <th>Team Members Contact</th>
                    <th>Payment Status</th>
                    <th>Payment ID</th>
                    <th>Amount Paid</th>
                    <th>Payment Date</th>
                    <th>Registration Date</th>
                </tr>
                <?php $sl_no = 1; ?>
                <?php while ($row = $outhouse_result->fetch_assoc()): ?>
                <?php
                    $team_members = isset($row['team_members']) ? json_decode($row['team_members'], true) : [];
                    $team_size = is_array($team_members) ? count($team_members) + 1 : 1;
                ?>
                <tr>
                    <td><?php echo $sl_no++; ?></td>
                    <td><?php echo isset($row['leader_name']) ? $row['leader_name'] : ''; ?></td>
                    <td><?php echo isset($row['gender']) ? $row['gender'] : ''; ?></td>
                    <td><?php echo isset($row['contact_number']) ? $row['contact_number'] : ''; ?></td>
                    <td><?php echo isset($row['college_name']) ? $row['college_name'] : ''; ?></td>
                    <td><?php echo isset($row['competition_name']) ? $row['competition_name'] : ''; ?></td>
                    <td><?php echo isset($row['team_name']) ? $row['team_name'] : ''; ?></td>
                    <td><?php echo $team_size; ?></td>
                    <td>
                        <span class="status-badge <?php echo isset($row['payment_status']) && $row['payment_status'] == 'Paid' ? 'paid' : 'not-paid'; ?>">
                            <?php echo isset($row['payment_status']) ? $row['payment_status'] : ''; ?>
                        </span>
                    </td>
                    <td><?php echo isset($row['payment_id']) ? $row['payment_id'] : ''; ?></td>
                    <td><?php echo isset($row['amount_paid']) ? '₹'.$row['amount_paid'] : ''; ?></td>
                    <td><?php echo isset($row['registration_date']) ? date('d M Y', strtotime($row['registration_date'])) : ''; ?></td>
                </tr>
                <?php endwhile; ?>
            </table>
        </div>
    </div>
    <script>
        document.querySelectorAll('.tab').forEach(tab => {
            tab.addEventListener('click', function() {
                document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
                document.querySelectorAll('.tab-content').forEach(tc => tc.classList.remove('active'));
                this.classList.add('active');
                document.getElementById(this.getAttribute('data-tab')).classList.add('active');
            });
        });

        function applyFilters() {
            const gender = document.getElementById('gender').value;
            const competition = document.getElementById('competition').value;
            const payment_status = document.getElementById('payment_status').value;
            window.location.href = `madm.php?gender=${gender}&competition=${competition}&payment_status=${payment_status}&tab=${document.querySelector('.tab.active').getAttribute('data-tab')}`;
        }

        function downloadCSV(tab) {
            window.location.href = `madm.php?download=csv&gender=${document.getElementById('gender').value}&competition=${document.getElementById('competition').value}&payment_status=${document.getElementById('payment_status').value}&tab=${tab}`;
        }
    </script>
</body>
</html>
