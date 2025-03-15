<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit();
}

// Include backend.php which contains all the data processing logic
include 'backend.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MaJIStic 2K25 Admin Panel</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <nav class="navbar">
        <div class="navbar-title">
            <img src="../assets/images/logo.png" alt="MaJIStic Logo" class="navbar-logo">
            <h1>MaJIStic 2K25 Admin Panel</h1>
        </div>
        <a href="logout.php" class="logout-btn">
            <i class="fas fa-sign-out-alt"></i>
            Logout
        </a>
    </nav>

    <div class="container">
        <div class="tabs">
            <div class="tab <?php echo $tab == 'inhouse' ? 'active' : ''; ?>" data-tab="inhouse">
                <i class="fas fa-school"></i>
                Inhouse Registrations
            </div>
            <div class="tab <?php echo $tab == 'outhouse' ? 'active' : ''; ?>" data-tab="outhouse">
                <i class="fas fa-university"></i>
                Outhouse Registrations
            </div>
        </div>

        <!-- Inhouse Content -->
        <div id="inhouse" class="tab-content <?php echo $tab == 'inhouse' ? 'active' : ''; ?>">
            <!-- Statistics Cards -->
            <div class="stats">
                <div class="card blue">
                    <i class="fas fa-users card-icon"></i>
                    <h3><?php echo $inhouse_total_result['count']; ?></h3>
                    <p>Total Registrations</p>
                </div>
                <div class="card green">
                    <i class="fas fa-check-circle card-icon"></i>
                    <h3><?php echo $inhouse_paid_result['count']; ?></h3>
                    <p>Paid Registrations</p>
                </div>
                <div class="card red">
                    <i class="fas fa-times-circle card-icon"></i>
                    <h3><?php echo $inhouse_not_paid_result['count']; ?></h3>
                    <p>Unpaid Registrations</p>
                </div>
                <div class="card blue">
                    <i class="fas fa-rupee-sign card-icon"></i>
                    <h3>₹<?php echo number_format($inhouse_revenue); ?></h3>
                    <p>Total Revenue</p>
                </div>
            </div>

            <!-- Filters -->
            <div class="filters-container">
                <div class="filters">
                    <div class="filter-group">
                        <label for="gender-inhouse"><i class="fas fa-venus-mars"></i> Gender</label>
                        <select id="gender-inhouse">
                            <option value="">All Genders</option>
                            <option value="Male" <?php echo $gender_filter == 'Male' ? 'selected' : ''; ?>>Male</option>
                            <option value="Female" <?php echo $gender_filter == 'Female' ? 'selected' : ''; ?>>Female</option>
                            <option value="Other" <?php echo $gender_filter == 'Other' ? 'selected' : ''; ?>>Other</option>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label for="competition-inhouse"><i class="fas fa-trophy"></i> Competition</label>
                        <select id="competition-inhouse">
                            <option value="">All Competitions</option>
                            <?php if($inhouse_competitions): while($comp = $inhouse_competitions->fetch_assoc()): ?>
                            <option value="<?php echo $comp['competition_name']; ?>" <?php echo $competition_filter == $comp['competition_name'] ? 'selected' : ''; ?>><?php echo $comp['competition_name']; ?></option>
                            <?php endwhile; endif; ?>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label for="department"><i class="fas fa-building"></i> Department</label>
                        <select id="department">
                            <option value="">All Departments</option>
                            <?php if($departments): while($dept = $departments->fetch_assoc()): ?>
                            <option value="<?php echo $dept['department']; ?>" <?php echo $department_filter == $dept['department'] ? 'selected' : ''; ?>><?php echo $dept['department']; ?></option>
                            <?php endwhile; endif; ?>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label for="payment_status-inhouse"><i class="fas fa-money-bill-wave"></i> Payment Status</label>
                        <select id="payment_status-inhouse">
                            <option value="">All Payment Status</option>
                            <option value="Paid" <?php echo $payment_status_filter == 'Paid' ? 'selected' : ''; ?>>Paid</option>
                            <option value="Not Paid" <?php echo $payment_status_filter == 'Not Paid' ? 'selected' : ''; ?>>Not Paid</option>
                        </select>
                    </div>
                </div>
                <button class="btn btn-filter" onclick="applyFilters('inhouse')">
                    <i class="fas fa-filter"></i> Apply Filters
                </button>
            </div>

            <!-- Table -->
            <div class="table-header">
                <h2><i class="fas fa-list"></i> Inhouse Registrations</h2>
                <button class="btn btn-download" onclick="downloadCSV('inhouse')">
                    <i class="fas fa-download"></i> Download CSV
                </button>
            </div>
            
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Sl. No.</th>
                            <th>Student Name</th>
                            <th>JIS ID</th>
                            <th>Gender</th>
                            <th>Department</th>
                            <th>Competition</th>
                            <th>Payment Status</th>
                            <th>Payment ID</th>
                            <th>Amount Paid</th>
                            <th>Registration Date</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $sl_no = ($page - 1) * $items_per_page + 1;
                        if ($inhouse_result && $inhouse_result->num_rows > 0) {
                            while ($row = $inhouse_result->fetch_assoc()):
                        ?>
                        <tr>
                            <td><?php echo $sl_no++; ?></td>
                            <td><?php echo isset($row['student_name']) ? $row['student_name'] : ''; ?></td>
                            <td><?php echo isset($row['jis_id']) ? $row['jis_id'] : ''; ?></td>
                            <td><?php echo isset($row['gender']) ? $row['gender'] : ''; ?></td>
                            <td><?php echo isset($row['department']) ? $row['department'] : ''; ?></td>
                            <td><?php echo isset($row['competition_name']) ? $row['competition_name'] : ''; ?></td>
                            <td>
                                <span class="status-badge <?php echo isset($row['payment_status']) && $row['payment_status'] == 'Paid' ? 'paid' : 'not-paid'; ?>">
                                    <?php echo isset($row['payment_status']) ? $row['payment_status'] : ''; ?>
                                </span>
                            </td>
                            <td><?php echo isset($row['payment_id']) ? $row['payment_id'] : ''; ?></td>
                            <td><?php echo isset($row['amount_paid']) ? '₹'.$row['amount_paid'] : ''; ?></td>
                            <td><?php echo isset($row['registration_date']) ? date('d M Y', strtotime($row['registration_date'])) : ''; ?></td>
                            <td>
                                <button class="btn-view" onclick="viewDetails('inhouse', '<?php echo $row['jis_id']; ?>')">
                                    <i class="fas fa-eye"></i> View
                                </button>
                            </td>
                        </tr>
                        <?php 
                            endwhile;
                        } else {
                            echo '<tr><td colspan="11" style="text-align: center;">No registrations found</td></tr>';
                        }
                        ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination for inhouse -->
            <?php if ($inhouse_total_pages > 0): ?>
            <div class="pagination">
                <?php if ($page > 1): ?>
                <a href="?tab=inhouse&page=1<?php echo $gender_filter ? '&gender='.$gender_filter : ''; ?><?php echo $competition_filter ? '&competition='.$competition_filter : ''; ?><?php echo $payment_status_filter ? '&payment_status='.$payment_status_filter : ''; ?><?php echo $department_filter ? '&department='.$department_filter : ''; ?>" class="pagination-btn">
                    <i class="fas fa-angle-double-left"></i>
                </a>
                <a href="?tab=inhouse&page=<?php echo $page-1; ?><?php echo $gender_filter ? '&gender='.$gender_filter : ''; ?><?php echo $competition_filter ? '&competition='.$competition_filter : ''; ?><?php echo $payment_status_filter ? '&payment_status='.$payment_status_filter : ''; ?><?php echo $department_filter ? '&department='.$department_filter : ''; ?>" class="pagination-btn">
                    <i class="fas fa-angle-left"></i>
                </a>
                <?php endif; ?>
                
                <?php
                $start_page = max(1, $page - 2);
                $end_page = min($inhouse_total_pages, $page + 2);
                
                for ($i = $start_page; $i <= $end_page; $i++):
                ?>
                <a href="?tab=inhouse&page=<?php echo $i; ?><?php echo $gender_filter ? '&gender='.$gender_filter : ''; ?><?php echo $competition_filter ? '&competition='.$competition_filter : ''; ?><?php echo $payment_status_filter ? '&payment_status='.$payment_status_filter : ''; ?><?php echo $department_filter ? '&department='.$department_filter : ''; ?>" class="pagination-btn <?php echo ($page == $i) ? 'active' : ''; ?>">
                    <?php echo $i; ?>
                </a>
                <?php endfor; ?>
                
                <?php if ($page < $inhouse_total_pages): ?>
                <a href="?tab=inhouse&page=<?php echo $page+1; ?><?php echo $gender_filter ? '&gender='.$gender_filter : ''; ?><?php echo $competition_filter ? '&competition='.$competition_filter : ''; ?><?php echo $payment_status_filter ? '&payment_status='.$payment_status_filter : ''; ?><?php echo $department_filter ? '&department='.$department_filter : ''; ?>" class="pagination-btn">
                    <i class="fas fa-angle-right"></i>
                </a>
                <a href="?tab=inhouse&page=<?php echo $inhouse_total_pages; ?><?php echo $gender_filter ? '&gender='.$gender_filter : ''; ?><?php echo $competition_filter ? '&competition='.$competition_filter : ''; ?><?php echo $payment_status_filter ? '&payment_status='.$payment_status_filter : ''; ?><?php echo $department_filter ? '&department='.$department_filter : ''; ?>" class="pagination-btn">
                    <i class="fas fa-angle-double-right"></i>
                </a>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>

        <!-- Outhouse Content -->
        <div id="outhouse" class="tab-content <?php echo $tab == 'outhouse' ? 'active' : ''; ?>">
            <!-- Statistics Cards -->
            <div class="stats">
                <div class="card blue">
                    <i class="fas fa-users card-icon"></i>
                    <h3><?php echo $outhouse_total_result['count']; ?></h3>
                    <p>Total Registrations</p>
                </div>
                <div class="card green">
                    <i class="fas fa-check-circle card-icon"></i>
                    <h3><?php echo $outhouse_paid_result['count']; ?></h3>
                    <p>Paid Registrations</p>
                </div>
                <div class="card red">
                    <i class="fas fa-times-circle card-icon"></i>
                    <h3><?php echo $outhouse_not_paid_result['count']; ?></h3>
                    <p>Unpaid Registrations</p>
                </div>
                <div class="card blue">
                    <i class="fas fa-rupee-sign card-icon"></i>
                    <h3>₹<?php echo number_format($outhouse_revenue); ?></h3>
                    <p>Total Revenue</p>
                </div>
            </div>

            <!-- Filters -->
            <div class="filters-container">
                <div class="filters">
                    <div class="filter-group">
                        <label for="gender-outhouse"><i class="fas fa-venus-mars"></i> Gender</label>
                        <select id="gender-outhouse">
                            <option value="">All Genders</option>
                            <option value="Male" <?php echo $gender_filter == 'Male' ? 'selected' : ''; ?>>Male</option>
                            <option value="Female" <?php echo $gender_filter == 'Female' ? 'selected' : ''; ?>>Female</option>
                            <option value="Other" <?php echo $gender_filter == 'Other' ? 'selected' : ''; ?>>Other</option>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label for="competition-outhouse"><i class="fas fa-trophy"></i> Competition</label>
                        <select id="competition-outhouse">
                            <option value="">All Competitions</option>
                            <?php if($outhouse_competitions): while($comp = $outhouse_competitions->fetch_assoc()): ?>
                            <option value="<?php echo $comp['competition_name']; ?>" <?php echo $competition_filter == $comp['competition_name'] ? 'selected' : ''; ?>><?php echo $comp['competition_name']; ?></option>
                            <?php endwhile; endif; ?>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label for="college"><i class="fas fa-university"></i> College</label>
                        <select id="college">
                            <option value="">All Colleges</option>
                            <?php if($colleges): while($coll = $colleges->fetch_assoc()): ?>
                            <option value="<?php echo $coll['college_name']; ?>" <?php echo $college_filter == $coll['college_name'] ? 'selected' : ''; ?>><?php echo $coll['college_name']; ?></option>
                            <?php endwhile; endif; ?>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label for="payment_status-outhouse"><i class="fas fa-money-bill-wave"></i> Payment Status</label>
                        <select id="payment_status-outhouse">
                            <option value="">All Payment Status</option>
                            <option value="Paid" <?php echo $payment_status_filter == 'Paid' ? 'selected' : ''; ?>>Paid</option>
                            <option value="Not Paid" <?php echo $payment_status_filter == 'Not Paid' ? 'selected' : ''; ?>>Not Paid</option>
                        </select>
                    </div>
                </div>
                <button class="btn btn-filter" onclick="applyFilters('outhouse')">
                    <i class="fas fa-filter"></i> Apply Filters
                </button>
            </div>

            <!-- Table -->
            <div class="table-header">
                <h2><i class="fas fa-list"></i> Outhouse Registrations</h2>
                <button class="btn btn-download" onclick="downloadCSV('outhouse')">
                    <i class="fas fa-download"></i> Download CSV
                </button>
            </div>
            
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Sl. No.</th>
                            <th>Leader Name</th>
                            <th>Gender</th>
                            <th>Contact Number</th>
                            <th>College Name</th>
                            <th>Competition</th>
                            <th>Team Name</th>
                            <th>Team Size</th>
                            <th>Payment Status</th>
                            <th>Payment ID</th>
                            <th>Amount Paid</th>
                            <th>Registration Date</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $sl_no = ($page - 1) * $items_per_page + 1;
                        if ($outhouse_result && $outhouse_result->num_rows > 0) {
                            while ($row = $outhouse_result->fetch_assoc()):
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
                            <td>
                                <button class="btn-view" onclick="viewDetails('outhouse', '<?php echo $row['college_id']; ?>')">
                                    <i class="fas fa-eye"></i> View
                                </button>
                            </td>
                        </tr>
                        <?php 
                            endwhile;
                        } else {
                            echo '<tr><td colspan="13" style="text-align: center;">No registrations found</td></tr>';
                        }
                        ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination for outhouse -->
            <?php if ($outhouse_total_pages > 0): ?>
            <div class="pagination">
                <?php if ($page > 1): ?>
                <a href="?tab=outhouse&page=1<?php echo $gender_filter ? '&gender='.$gender_filter : ''; ?><?php echo $competition_filter ? '&competition='.$competition_filter : ''; ?><?php echo $payment_status_filter ? '&payment_status='.$payment_status_filter : ''; ?><?php echo $college_filter ? '&college='.$college_filter : ''; ?>" class="pagination-btn">
                    <i class="fas fa-angle-double-left"></i>
                </a>
                <a href="?tab=outhouse&page=<?php echo $page-1; ?><?php echo $gender_filter ? '&gender='.$gender_filter : ''; ?><?php echo $competition_filter ? '&competition='.$competition_filter : ''; ?><?php echo $payment_status_filter ? '&payment_status='.$payment_status_filter : ''; ?><?php echo $college_filter ? '&college='.$college_filter : ''; ?>" class="pagination-btn">
                    <i class="fas fa-angle-left"></i>
                </a>
                <?php endif; ?>
                
                <?php
                $start_page = max(1, $page - 2);
                $end_page = min($outhouse_total_pages, $page + 2);
                
                for ($i = $start_page; $i <= $end_page; $i++):
                ?>
                <a href="?tab=outhouse&page=<?php echo $i; ?><?php echo $gender_filter ? '&gender='.$gender_filter : ''; ?><?php echo $competition_filter ? '&competition='.$competition_filter : ''; ?><?php echo $payment_status_filter ? '&payment_status='.$payment_status_filter : ''; ?><?php echo $college_filter ? '&college='.$college_filter : ''; ?>" class="pagination-btn <?php echo ($page == $i) ? 'active' : ''; ?>">
                    <?php echo $i; ?>
                </a>
                <?php endfor; ?>
                
                <?php if ($page < $outhouse_total_pages): ?>
                <a href="?tab=outhouse&page=<?php echo $page+1; ?><?php echo $gender_filter ? '&gender='.$gender_filter : ''; ?><?php echo $competition_filter ? '&competition='.$competition_filter : ''; ?><?php echo $payment_status_filter ? '&payment_status='.$payment_status_filter : ''; ?><?php echo $college_filter ? '&college='.$college_filter : ''; ?>" class="pagination-btn">
                    <i class="fas fa-angle-right"></i>
                </a>
                <a href="?tab=outhouse&page=<?php echo $outhouse_total_pages; ?><?php echo $gender_filter ? '&gender='.$gender_filter : ''; ?><?php echo $competition_filter ? '&competition='.$competition_filter : ''; ?><?php echo $payment_status_filter ? '&payment_status='.$payment_status_filter : ''; ?><?php echo $college_filter ? '&college='.$college_filter : ''; ?>" class="pagination-btn">
                    <i class="fas fa-angle-double-right"></i>
                </a>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Modal for viewing registration details -->
    <div id="detailsModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Registration Details</h2>
                <span class="close" onclick="closeModal()">&times;</span>
            </div>
            <div class="modal-body">
                <div class="modal-loading">
                    <i class="fas fa-spinner fa-spin"></i> Loading...
                </div>
                <div class="registration-details">
                    <!-- Registration details will be populated here via AJAX -->
                </div>
            </div>
        </div>
    </div>

    <script>
        // Tab switching functionality
        document.querySelectorAll('.tab').forEach(tab => {
            tab.addEventListener('click', function() {
                document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
                document.querySelectorAll('.tab-content').forEach(tc => tc.classList.remove('active'));
                this.classList.add('active');
                document.getElementById(this.getAttribute('data-tab')).classList.add('active');
            });
        });

        // Filter application
        function applyFilters(tab) {
            const params = new URLSearchParams();
            params.append('tab', tab);
            
            // Get filter values based on active tab
            const gender = document.getElementById(`gender-${tab}`).value;
            const competition = document.getElementById(`competition-${tab}`).value;
            const payment_status = document.getElementById(`payment_status-${tab}`).value;
            
            if (gender) params.append('gender', gender);
            if (competition) params.append('competition', competition);
            if (payment_status) params.append('payment_status', payment_status);
            
            if (tab === 'inhouse') {
                const department = document.getElementById('department').value;
                if (department) params.append('department', department);
            } else {
                const college = document.getElementById('college').value;
                if (college) params.append('college', college);
            }
            
            window.location.href = `madm.php?${params.toString()}`;
        }

        // CSV download functionality
        function downloadCSV(tab) {
            const params = new URLSearchParams(window.location.search);
            params.set('download', 'csv');
            params.set('tab', tab);
            window.location.href = `madm.php?${params.toString()}`;
        }

        // Modal functionality
        const modal = document.getElementById('detailsModal');
        
        function viewDetails(type, id) {
            // Show modal
            modal.style.display = "block";
            document.body.classList.add('modal-open');
            
            // Show loading state
            document.querySelector('.modal-loading').style.display = "block";
            document.querySelector('.registration-details').innerHTML = "";
            
            // Fetch registration details via AJAX
            fetch(`get_registration_details.php?type=${type}&id=${id}`)
                .then(response => response.json())
                .then(data => {
                    document.querySelector('.modal-loading').style.display = "none";
                    
                    if (data.error) {
                        document.querySelector('.registration-details').innerHTML = `
                            <div class="error-message">
                                <i class="fas fa-exclamation-circle"></i> ${data.error}
                            </div>
                        `;
                        return;
                    }
                    
                    // Check registration type and display accordingly
                    if (type === 'inhouse') {
                        displayInhouseDetails(data);
                    } else {
                        displayOuthouseDetails(data);
                    }
                })
                .catch(error => {
                    document.querySelector('.modal-loading').style.display = "none";
                    document.querySelector('.registration-details').innerHTML = `
                        <div class="error-message">
                            <i class="fas fa-exclamation-circle"></i> An error occurred while fetching data. Please try again.
                        </div>
                    `;
                    console.error('Error:', error);
                });
        }
        
        function closeModal() {
            modal.style.display = "none";
            document.body.classList.remove('modal-open');
        }
        
        // Close modal when clicking outside
        window.onclick = function(event) {
            if (event.target === modal) {
                closeModal();
            }
        }
        
        // Function to display inhouse registration details
        function displayInhouseDetails(data) {
            const registration = data.registration;
            const paymentAttempts = data.payment_attempts || [];
            
            let html = `
                <div class="detail-section">
                    <h3>Student Information</h3>
                    <div class="detail-grid">
                        <div class="detail-item"><span>Name:</span> ${registration.student_name}</div>
                        <div class="detail-item"><span>Gender:</span> ${registration.gender}</div>
                        <div class="detail-item"><span>JIS ID:</span> ${registration.jis_id}</div>
                        <div class="detail-item"><span>Roll No:</span> ${registration.roll_no}</div>
                        <div class="detail-item"><span>Email:</span> ${registration.email}</div>
                        <div class="detail-item"><span>Mobile:</span> ${registration.mobile}</div>
                        <div class="detail-item"><span>Department:</span> ${registration.department}</div>
                    </div>
                </div>
                
                <div class="detail-section">
                    <h3>Competition Information</h3>
                    <div class="detail-grid">
                        <div class="detail-item"><span>Competition:</span> ${registration.competition_name}</div>
                        <div class="detail-item"><span>Inhouse Competition:</span> ${registration.inhouse_competition || 'N/A'}</div>
                    </div>
                </div>
                
                <div class="detail-section">
                    <h3>Payment Information</h3>
                    <div class="detail-grid">
                        <div class="detail-item"><span>Payment Status:</span> <span class="status-badge ${registration.payment_status === 'Paid' ? 'paid' : 'not-paid'}">${registration.payment_status}</span></div>
                        <div class="detail-item"><span>Amount:</span> ₹${registration.amount}</div>
                        <div class="detail-item"><span>Amount Paid:</span> ₹${registration.amount_paid}</div>
                        <div class="detail-item"><span>Payment ID:</span> ${registration.payment_id || 'N/A'}</div>
                        <div class="detail-item"><span>Registration Date:</span> ${new Date(registration.registration_date).toLocaleString()}</div>
                        <div class="detail-item"><span>Payment Date:</span> ${registration.payment_date ? new Date(registration.payment_date).toLocaleString() : 'N/A'}</div>
                    </div>
                </div>`;
                
            if (paymentAttempts.length > 0) {
                html += `
                <div class="detail-section">
                    <h3>Payment Attempts</h3>
                    <div class="attempts-table-wrapper">
                        <table class="attempts-table">
                            <thead>
                                <tr>
                                    <th>Attempt Time</th>
                                    <th>Status</th>
                                    <th>Payment ID</th>
                                    <th>Amount</th>
                                    <th>IP Address</th>
                                    <th>Error Message</th>
                                </tr>
                            </thead>
                            <tbody>`;
                
                paymentAttempts.forEach(attempt => {
                    html += `
                        <tr>
                            <td>${new Date(attempt.attempt_time).toLocaleString()}</td>
                            <td><span class="status-badge ${attempt.status === 'completed' ? 'paid' : (attempt.status === 'failed' ? 'not-paid' : '')}">${attempt.status}</span></td>
                            <td>${attempt.payment_id || 'N/A'}</td>
                            <td>${attempt.amount ? '₹' + attempt.amount : 'N/A'}</td>
                            <td>${attempt.ip_address}</td>
                            <td>${attempt.error_message || 'N/A'}</td>
                        </tr>`;
                });
                
                html += `
                            </tbody>
                        </table>
                    </div>
                </div>`;
            }
            
            document.querySelector('.registration-details').innerHTML = html;
        }
        
        // Function to display outhouse registration details
        function displayOuthouseDetails(data) {
            const registration = data.registration;
            const paymentAttempts = data.payment_attempts || [];
            const teamMembers = registration.team_members ? JSON.parse(registration.team_members) : [];
            const teamMembersContact = registration.team_members_contact ? JSON.parse(registration.team_members_contact) : [];
            
            let html = `
                <div class="detail-section">
                    <h3>Leader Information</h3>
                    <div class="detail-grid">
                        <div class="detail-item"><span>Name:</span> ${registration.leader_name}</div>
                        <div class="detail-item"><span>Gender:</span> ${registration.gender}</div>
                        <div class="detail-item"><span>Email:</span> ${registration.email}</div>
                        <div class="detail-item"><span>Contact Number:</span> ${registration.contact_number}</div>
                        <div class="detail-item"><span>College Name:</span> ${registration.college_name}</div>
                        <div class="detail-item"><span>College ID:</span> ${registration.college_id}</div>
                        <div class="detail-item"><span>Course:</span> ${registration.course_name}</div>
                    </div>
                </div>
                
                <div class="detail-section">
                    <h3>Team Information</h3>
                    <div class="detail-grid">
                        <div class="detail-item"><span>Team Name:</span> ${registration.team_name || 'N/A'}</div>
                        <div class="detail-item"><span>Competition:</span> ${registration.competition_name}</div>
                    </div>`;
                    
            if (teamMembers.length > 0) {
                html += `
                    <div class="team-members-table-wrapper">
                        <h4>Team Members</h4>
                        <table class="team-members-table">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Contact</th>
                                </tr>
                            </thead>
                            <tbody>`;
                            
                for (let i = 0; i < teamMembers.length; i++) {
                    html += `
                        <tr>
                            <td>${teamMembers[i]}</td>
                            <td>${teamMembersContact[i] || 'N/A'}</td>
                        </tr>`;
                }
                
                html += `
                            </tbody>
                        </table>
                    </div>`;
            }
            
            html += `
                </div>
                
                <div class="detail-section">
                    <h3>Payment Information</h3>
                    <div class="detail-grid">
                        <div class="detail-item"><span>Payment Status:</span> <span class="status-badge ${registration.payment_status === 'Paid' ? 'paid' : 'not-paid'}">${registration.payment_status}</span></div>
                        <div class="detail-item"><span>Amount Paid:</span> ₹${registration.amount_paid}</div>
                        <div class="detail-item"><span>Payment ID:</span> ${registration.payment_id || 'N/A'}</div>
                        <div class="detail-item"><span>Registration Date:</span> ${new Date(registration.registration_date).toLocaleString()}</div>
                        <div class="detail-item"><span>Payment Date:</span> ${registration.payment_date ? new Date(registration.payment_date).toLocaleString() : 'N/A'}</div>
                    </div>
                </div>`;
                
            if (paymentAttempts.length > 0) {
                html += `
                <div class="detail-section">
                    <h3>Payment Attempts</h3>
                    <div class="attempts-table-wrapper">
                        <table class="attempts-table">
                            <thead>
                                <tr>
                                    <th>Attempt Time</th>
                                    <th>Status</th>
                                    <th>Payment ID</th>
                                    <th>Amount</th>
                                    <th>IP Address</th>
                                    <th>Error Message</th>
                                </tr>
                            </thead>
                            <tbody>`;
                
                paymentAttempts.forEach(attempt => {
                    html += `
                        <tr>
                            <td>${new Date(attempt.attempt_time).toLocaleString()}</td>
                            <td><span class="status-badge ${attempt.status === 'completed' ? 'paid' : (attempt.status === 'failed' ? 'not-paid' : '')}">${attempt.status}</span></td>
                            <td>${attempt.payment_id || 'N/A'}</td>
                            <td>${attempt.amount ? '₹' + attempt.amount : 'N/A'}</td>
                            <td>${attempt.ip_address}</td>
                            <td>${attempt.error_message || 'N/A'}</td>
                        </tr>`;
                });
                
                html += `
                            </tbody>
                        </table>
                    </div>
                </div>`;
            }
            
            document.querySelector('.registration-details').innerHTML = html;
        }
    </script>
</body>
</html>
