<?php
//require_once __DIR__ . '/../../includes/db_config.php';

// Fetch data from the database
$registrations = $registrations->find(['payment_status' => 'Paid'])->toArray();
$alumni_registrations = $alumni_registrations->find(['payment_status' => 'Paid'])->toArray();

// Combine data
$students = array_merge($registrations, $alumni_registrations);

// Calculate stats
$total_students = count($students);
$day1_checked_in = count(array_filter($students, fn($s) => isset($s['checkin_1']) && $s['checkin_1'] === "checkedin"));
$day2_checked_in = count(array_filter($students, fn($s) => isset($s['checkin_2']) && $s['checkin_2'] === "checkedin"));
$tickets_generated = count(array_filter($students, fn($s) => isset($s['ticket']) && $s['ticket'] === "generated"));
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Management</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .card {
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            border: none;
        }
        .table th, .table td {
            vertical-align: middle;
        }
        .btn {
            margin-right: 5px;
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <!-- Stat Cards -->
        <div class="row g-4">
            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-body">
                        <h5 class="card-title">Total Students</h5>
                        <p class="card-text display-6"><?= $total_students ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-body">
                        <h5 class="card-title">Tickets Generated</h5>
                        <p class="card-text display-6"><?= $tickets_generated ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-body">
                        <h5 class="card-title">Day 1 Checked In</h5>
                        <p class="card-text display-6"><?= $day1_checked_in ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-body">
                        <h5 class="card-title">Day 2 Checked In</h5>
                        <p class="card-text display-6"><?= $day2_checked_in ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Table -->
        <div class="mt-5">
            <table class="table table-striped table-hover">
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
                    <?php foreach ($students as $student): ?>
                        <tr>
                            <td><?= $student['name'] ?? 'N/A' ?></td>
                            <td><?= $student['jis_id'] ?? 'N/A' ?></td>
                            <td><?= $student['department'] ?? 'N/A' ?></td>
                            <td><?= $student['type'] ?? 'N/A' ?></td>
                            <td><?= $student['email'] ?? 'N/A' ?></td>
                            <td><?= $student['phone'] ?? 'N/A' ?></td>
                            <td>
                                <button class="btn btn-primary btn-sm view-btn" data-id="<?= $student['_id'] ?>">View</button>
                                <button class="btn btn-success btn-sm generate-ticket-btn" data-id="<?= $student['_id'] ?>">Generate Ticket</button>
                                <button class="btn btn-info btn-sm checkin1-btn" data-id="<?= $student['_id'] ?>">Checkin 1</button>
                                <button class="btn btn-warning btn-sm checkin2-btn" data-id="<?= $student['_id'] ?>">Checkin 2</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="viewModal" tabindex="-1" aria-labelledby="viewModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="viewModalLabel">Student Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- Details will be loaded dynamically -->
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            // View button click
            $('.view-btn').click(function() {
                const id = $(this).data('id');
                $.get('fetch_details.php', { id }, function(data) {
                    $('#viewModal .modal-body').html(data);
                    $('#viewModal').modal('show');
                });
            });

            // Generate ticket button click
            $('.generate-ticket-btn').click(function() {
                const id = $(this).data('id');
                $.post('update_ticket.php', { id, action: 'generate' }, function(response) {
                    alert(response.message);
                    location.reload();
                });
            });

            // Checkin 1 button click
            $('.checkin1-btn').click(function() {
                const id = $(this).data('id');
                $.post('update_checkin.php', { id, action: 'checkin_1' }, function(response) {
                    alert(response.message);
                    location.reload();
                });
            });

            // Checkin 2 button click
            $('.checkin2-btn').click(function() {
                const id = $(this).data('id');
                $.post('update_checkin.php', { id, action: 'checkin_2' }, function(response) {
                    alert(response.message);
                    location.reload();
                });
            });
        });
    </script>
</body>
</html>
