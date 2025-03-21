<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: ../login.php');
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
    <title>Admin Panel</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <nav class="navbar">
        <div class="navbar-title">
            <img src="../../images/majisticlogo.png" alt="MaJIStic Logo" class="navbar-logo">
            <h1>Admin Panel</h1>
        </div>
        <a href="../logout.php" class="logout-btn">
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
            <div class="tab <?php echo $tab == 'alumni' ? 'active' : ''; ?>" data-tab="alumni">
                <i class="fas fa-user-graduate"></i>
                Alumni Registrations
            </div>
        </div>

        <!-- Inhouse Content -->
        <div id="inhouse" class="tab-content <?php echo $tab == 'inhouse' ? 'active' : ''; ?>">
            <!-- Statistics Cards -->
            <div class="stats">
                <div class="card blue">
                    <i class="fas fa-users card-icon"></i>
                    <h3><?php echo $stats['inhouse']['total']; ?></h3>
                    <p>Total Registrations</p>
                </div>
                <div class="card green">
                    <i class="fas fa-check-circle card-icon"></i>
                    <h3><?php echo $stats['inhouse']['paid']; ?></h3>
                    <p>Paid Registrations</p>
                </div>
                <div class="card red">
                    <i class="fas fa-times-circle card-icon"></i>
                    <h3><?php echo $stats['inhouse']['not_paid']; ?></h3>
                    <p>Unpaid Registrations</p>
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
                            <?php foreach ($inhouse_competitions as $comp): ?>
                            <option value="<?php echo htmlspecialchars($comp['competition_name']); ?>" <?php echo $competition_filter == $comp['competition_name'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($comp['competition_name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label for="department"><i class="fas fa-building"></i> Department</label>
                        <select id="department">
                            <option value="">All Departments</option>
                            <?php foreach ($departments as $dept): ?>
                            <option value="<?php echo htmlspecialchars($dept['department']); ?>" <?php echo $department_filter == $dept['department'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($dept['department']); ?></option>
                            <?php endforeach; ?>
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
                <div class="filters-actions">
                    <button class="btn btn-filter" onclick="applyFilters('<?php echo $tab; ?>')">
                        <i class="fas fa-filter"></i> Apply Filters
                    </button>
                    <button class="btn btn-reset" onclick="resetFilters('<?php echo $tab; ?>')">
                        <i class="fas fa-undo"></i> Reset Filters
                    </button>
                    <div class="search-box">
                        <input type="text" id="search-<?php echo $tab; ?>" placeholder="Search by name, JIS ID, or department...">
                        <i class="fas fa-search"></i>
                    </div>
                </div>
            </div>

            <!-- Table -->
            <div class="table-header">
                <h2><i class="fas fa-list"></i> Inhouse Registrations</h2>
                <div class="table-actions">
                    <div class="column-control">
                        <button class="btn btn-columns" id="column-control-inhouse">
                            <i class="fas fa-columns"></i> Columns
                        </button>
                        <div class="column-dropdown" id="column-dropdown-inhouse">
                            <!-- Toggle all option -->
                            <div class="column-checkbox toggle-all">
                                <input type="checkbox" id="toggle-all-inhouse">
                                <label for="toggle-all-inhouse"><strong>Toggle All Optional</strong></label>
                            </div>
                            <div class="column-divider"></div>
                            <!-- Optional columns that can be toggled -->
                            <div class="column-checkbox">
                                <input type="checkbox" id="col-gender-inhouse">
                                <label for="col-gender-inhouse">Gender</label>
                            </div>
                            <div class="column-checkbox">
                                <input type="checkbox" id="col-department-inhouse" checked>
                                <label for="col-department-inhouse">Department</label>
                            </div>
                            <div class="column-checkbox">
                                <input type="checkbox" id="col-competition-inhouse">
                                <label for="col-competition-inhouse">Competition</label>
                            </div>
                            <div class="column-checkbox">
                                <input type="checkbox" id="col-regdate-inhouse">
                                <label for="col-regdate-inhouse">Registration Date</label>
                            </div>
                        </div>
                    </div>
                    <button class="btn btn-download" onclick="downloadCSV('inhouse')">
                        <i class="fas fa-download"></i> Download CSV
                    </button>
                </div>
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
                            <th>Registration Date</th>
                            <th>Payment Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $sl_no = ($page - 1) * $items_per_page + 1;
                        if (!empty($result) && $tab == 'inhouse') {
                            foreach ($result as $row):
                                // Convert registration date to IST if it exists
                                $registration_date = isset($row['registration_date']) ? 
                                    date('d-m-Y h:i A', strtotime($row['registration_date'])) : 'N/A';
                        ?>
                        <tr>
                            <td><?php echo $sl_no++; ?></td>
                            <td><?php echo $row['student_name'] ?? ''; ?></td>
                            <td><?php echo $row['jis_id'] ?? ''; ?></td>
                            <td><?php echo $row['gender'] ?? ''; ?></td>
                            <td><?php echo $row['department'] ?? ''; ?></td>
                            <td><?php echo $row['competition_name'] ?? ''; ?></td>
                            <td><?php echo $registration_date; ?></td>
                            <td>
                                <span class="status-badge <?php echo ($row['payment_status'] ?? '') == 'Paid' ? 'paid' : 'not-paid'; ?>">
                                    <?php echo $row['payment_status'] ?? 'Not Paid'; ?>
                                </span>
                            </td>
                            <td>
                                <button class="btn-view" onclick="viewDetails('inhouse', '<?php echo $row['jis_id']; ?>')">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <?php if (($row['payment_status'] ?? '') != 'Paid'): ?>
                                <button class="btn-payment" onclick="openPaymentModal('inhouse', '<?php echo $row['jis_id']; ?>', '<?php echo addslashes($row['student_name'] ?? ''); ?>')">
                                    <i class="fas fa-money-bill-wave"></i> Payment
                                </button>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php 
                            endforeach;
                        } else {
                            echo '<tr><td colspan="9" style="text-align: center;">No registrations found</td></tr>';            
                        }
                        ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Remove pagination for inhouse -->
            <?php /* Remove the inhouse pagination code */ ?>
        </div>

        <!-- Alumni Content -->
        <div id="alumni" class="tab-content <?php echo $tab == 'alumni' ? 'active' : ''; ?>">
            <!-- Statistics Cards -->
            <div class="stats">
                <div class="card blue">
                    <i class="fas fa-users card-icon"></i>
                    <h3><?php echo $stats['alumni']['total']; ?></h3>
                    <p>Total Registrations</p>
                </div>
                <div class="card green">
                    <i class="fas fa-check-circle card-icon"></i>
                    <h3><?php echo $stats['alumni']['paid']; ?></h3>
                    <p>Paid Registrations</p>
                </div>
                <div class="card red">
                    <i class="fas fa-times-circle card-icon"></i>
                    <h3><?php echo $stats['alumni']['not_paid']; ?></h3>
                    <p>Unpaid Registrations</p>
                </div>
            </div>

            <!-- Filters -->
            <div class="filters-container">
                <div class="filters">
                    <div class="filter-group">
                        <label for="gender-alumni"><i class="fas fa-venus-mars"></i> Gender</label>
                        <select id="gender-alumni">
                            <option value="">All Genders</option>
                            <option value="Male" <?php echo $gender_filter == 'Male' ? 'selected' : ''; ?>>Male</option>
                            <option value="Female" <?php echo $gender_filter == 'Female' ? 'selected' : ''; ?>>Female</option>
                            <option value="Other" <?php echo $gender_filter == 'Other' ? 'selected' : ''; ?>>Other</option>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label for="department-alumni"><i class="fas fa-building"></i> Department</label>
                        <select id="department-alumni">
                            <option value="">All Departments</option>
                            <?php foreach ($departments as $dept): ?>
                            <option value="<?php echo htmlspecialchars($dept['department']); ?>" <?php echo $department_filter == $dept['department'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($dept['department']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label for="passout_year"><i class="fas fa-graduation-cap"></i> Passout Year</label>
                        <select id="passout_year">
                            <option value="">All Years</option>
                            <?php foreach ($passout_years as $year): ?>
                            <option value="<?php echo htmlspecialchars($year['passout_year']); ?>" <?php echo $passout_year_filter == $year['passout_year'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($year['passout_year']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label for="payment_status-alumni"><i class="fas fa-money-bill-wave"></i> Payment Status</label>
                        <select id="payment_status-alumni">
                            <option value="">All Payment Status</option>
                            <option value="Paid" <?php echo $payment_status_filter == 'Paid' ? 'selected' : ''; ?>>Paid</option>
                            <option value="Not Paid" <?php echo $payment_status_filter == 'Not Paid' ? 'selected' : ''; ?>>Not Paid</option>
                        </select>
                    </div>
                </div>
                <div class="filters-actions">
                    <button class="btn btn-filter" onclick="applyFilters('<?php echo $tab; ?>')">
                        <i class="fas fa-filter"></i> Apply Filters
                    </button>
                    <button class="btn btn-reset" onclick="resetFilters('<?php echo $tab; ?>')">
                        <i class="fas fa-undo"></i> Reset Filters
                    </button>
                    <div class="search-box">
                        <input type="text" id="search-<?php echo $tab; ?>" placeholder="Search by name, JIS ID, or department...">
                        <i class="fas fa-search"></i>
                    </div>
                </div>
            </div>

            <!-- Table -->
            <div class="table-header">
                <h2><i class="fas fa-list"></i> Alumni Registrations</h2>
                <div class="table-actions">
                    <div class="column-control">
                        <button class="btn btn-columns" id="column-control-alumni">
                            <i class="fas fa-columns"></i> Columns
                        </button>
                        <div class="column-dropdown" id="column-dropdown-alumni">
                            <!-- Toggle all option -->
                            <div class="column-checkbox toggle-all">
                                <input type="checkbox" id="toggle-all-alumni">
                                <label for="toggle-all-alumni"><strong>Toggle All Optional</strong></label>
                            </div>
                            <div class="column-divider"></div>
                            <!-- Optional columns that can be toggled -->
                            <div class="column-checkbox">
                                <input type="checkbox" id="col-gender-alumni">
                                <label for="col-gender-alumni">Gender</label>
                            </div>
                            <div class="column-checkbox">
                                <input type="checkbox" id="col-email-alumni" checked>
                                <label for="col-email-alumni">Email</label>
                            </div>
                            <div class="column-checkbox">
                                <input type="checkbox" id="col-mobile-alumni" checked>
                                <label for="col-mobile-alumni">Mobile</label>
                            </div>
                            <div class="column-checkbox">
                                <input type="checkbox" id="col-department-alumni" checked>
                                <label for="col-department-alumni">Department</label>
                            </div>
                            <div class="column-checkbox">
                                <input type="checkbox" id="col-passoutyear-alumni" checked>
                                <label for="col-passoutyear-alumni">Passout Year</label>
                            </div>
                            <div class="column-checkbox">
                                <input type="checkbox" id="col-regdate-alumni">
                                <label for="col-regdate-alumni">Registration Date</label>
                            </div>
                        </div>
                    </div>
                    <button class="btn btn-download" onclick="downloadCSV('alumni')">
                        <i class="fas fa-download"></i> Download CSV
                    </button>
                </div>
            </div>
            
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Sl. No.</th>
                            <th>Name</th>
                            <th>JIS ID</th>
                            <th>Gender</th>
                            <th>Email</th>
                            <th>Mobile</th>
                            <th>Department</th>
                            <th>Passout Year</th>
                            <th>Registration Date</th>
                            <th>Payment Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $sl_no = ($page - 1) * $items_per_page + 1;
                        if (!empty($result) && $tab == 'alumni') {
                            foreach ($result as $row):
                                // Convert registration date to IST if it exists
                                $registration_date = isset($row['registration_date']) ? 
                                    date('d-m-Y h:i A', strtotime($row['registration_date'])) : 'N/A';
                        ?>
                        <tr>
                            <td><?php echo $sl_no++; ?></td>
                            <td><?php echo $row['alumni_name'] ?? ''; ?></td>
                            <td><?php echo $row['jis_id'] ?? ''; ?></td>
                            <td><?php echo $row['gender'] ?? ''; ?></td>
                            <td><?php echo $row['email'] ?? ''; ?></td>
                            <td><?php echo $row['mobile'] ?? ''; ?></td>
                            <td><?php echo $row['department'] ?? ''; ?></td>
                            <td><?php echo $row['passout_year'] ?? ''; ?></td>
                            <td><?php echo $registration_date; ?></td>
                            <td>
                                <span class="status-badge <?php echo ($row['payment_status'] ?? '') == 'Paid' ? 'paid' : 'not-paid'; ?>">
                                    <?php echo $row['payment_status'] ?? 'Not Paid'; ?>
                                </span>
                            </td>
                            <td>
                                <button class="btn-view" onclick="viewDetails('alumni', '<?php echo $row['jis_id']; ?>')">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <?php if (($row['payment_status'] ?? '') != 'Paid'): ?>
                                <button class="btn-payment" onclick="openPaymentModal('alumni', '<?php echo $row['jis_id']; ?>', '<?php echo addslashes($row['alumni_name'] ?? ''); ?>')">
                                    <i class="fas fa-money-bill-wave"></i> Payment
                                </button>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php 
                            endforeach;
                        } else {
                            echo '<tr><td colspan="11" style="text-align: center;">No registrations found</td></tr>';
                        }
                        ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Remove pagination for alumni -->
            <?php /* Remove the alumni pagination code */ ?>
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

    <!-- Payment Modal -->
    <div id="paymentModal" class="modal payment-modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Process Payment</h2>
                <span class="close" onclick="closePaymentModal()">&times;</span>
            </div>
            <div class="modal-body">
                <div class="payment-details">
                    <div class="payment-field">
                        <label>JIS ID:</label>
                        <div id="payment-jis-id"></div>
                    </div>
                    <div class="payment-field">
                        <label>Name:</label>
                        <div id="payment-name"></div>
                    </div>
                    <div class="payment-amount">
                        Amount: ₹<span id="payment-amount">500</span>
                    </div>
                    <div class="payment-field">
                        <label for="receipt-number">Receipt Number:</label>
                        <input type="text" id="receipt-number" placeholder="Enter receipt number" required>
                    </div>
                    <div class="payment-message">
                        <p>This payment will be marked as paid after confirmation. The amount should be collected in cash.</p>
                    </div>
                </div>
                <div class="payment-buttons">
                    <button class="btn-cancel-payment" onclick="closePaymentModal()">
                        <i class="fas fa-times"></i> Cancel
                    </button>
                    <button class="btn-confirm-payment" onclick="confirmPayment()">
                        <i class="fas fa-check"></i> Confirm Payment
                    </button>
                </div>
                
                <div id="payment-success" class="payment-success">
                    <i class="fas fa-check-circle"></i> Payment processed successfully!
                </div>
                <div id="payment-error" class="payment-error">
                    <i class="fas fa-exclamation-circle"></i> <span id="payment-error-message"></span>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Tab switching functionality
        document.querySelectorAll('.tab').forEach(tab => {
            tab.addEventListener('click', function() {
                const tabId = this.getAttribute('data-tab');
                
                // Update tabs and content visibility
                document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
                document.querySelectorAll('.tab-content').forEach(tc => tc.classList.remove('active'));
                this.classList.add('active');
                document.getElementById(tabId).classList.add('active');
                
                // Update URL without reloading
                const currentUrl = new URL(window.location.href);
                currentUrl.searchParams.set('tab', tabId);
                window.history.pushState({}, '', currentUrl);
            });
        });

        // Handle browser back/forward buttons
        window.addEventListener('popstate', function() {
            const params = new URLSearchParams(window.location.search);
            const currentTab = params.get('tab') || 'inhouse';
            
            // Update tabs and content visibility
            document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
            document.querySelectorAll('.tab-content').forEach(tc => tc.classList.remove('active'));
            document.querySelector(`.tab[data-tab="${currentTab}"]`).classList.add('active');
            document.getElementById(currentTab).classList.add('active');
        });

        // Keep the filter application function as is but make it preserve the current tab
        function applyFilters(tab) {
            const params = new URLSearchParams(window.location.search);
            params.delete('page'); // Reset to first page
            params.set('tab', tab);
            
            // Get filter values
            const gender = document.getElementById(`gender-${tab}`).value;
            const payment_status = document.getElementById(`payment_status-${tab}`).value;
            
            // Clear existing filters
            ['gender', 'payment_status', 'department', 'competition', 'passout_year'].forEach(param => {
                params.delete(param);
            });
            
            // Add new filter values if they exist
            if (gender) params.set('gender', gender);
            if (payment_status) params.set('payment_status', payment_status);
            
            if (tab === 'inhouse') {
                const department = document.getElementById('department').value;
                const competition = document.getElementById('competition-inhouse').value;
                if (department) params.set('department', department);
                if (competition) params.set('competition', competition);
            } else if (tab === 'alumni') {
                const passout_year = document.getElementById('passout_year').value;
                const department = document.getElementById('department-alumni').value;
                if (passout_year) params.set('passout_year', passout_year);
                if (department) params.set('department', department);
            }
            
            window.location.href = `madm.php?${params.toString()}`;
        }

        function resetFilters(tab) {
            // Reset all filter dropdowns
            document.getElementById(`gender-${tab}`).value = '';
            document.getElementById(`payment_status-${tab}`).value = '';
            
            if (tab === 'inhouse') {
                document.getElementById('department').value = '';
                document.getElementById('competition-inhouse').value = '';
            } else {
                document.getElementById('department-alumni').value = '';
                document.getElementById('passout_year').value = '';
            }
            
            // Clear search box
            document.getElementById(`search-${tab}`).value = '';
            
            // Redirect with only the tab parameter
            window.location.href = `madm.php?tab=${tab}`;
        }

        // Add search functionality
        function setupSearch(tab) {
            const searchInput = document.getElementById(`search-${tab}`);
            const tbody = document.querySelector(`#${tab} .table-container tbody`);
            const rows = tbody.getElementsByTagName('tr');
            searchInput.addEventListener('keyup', function() {
                const searchTerm = this.value.toLowerCase();
                
                Array.from(rows).forEach(row => {
                    const text = row.textContent.toLowerCase();
                    row.style.display = text.includes(searchTerm) ? '' : 'none';
                });
            });
        }

        // Initialize search for both tabs
        document.addEventListener('DOMContentLoaded', function() {
            setupSearch('inhouse');
            setupSearch('alumni');
        });

        // Update the tab switching to preserve search term
        document.querySelectorAll('.tab').forEach(tab => {
            tab.addEventListener('click', function() {
                const tabId = this.getAttribute('data-tab');
                const searchTerm = document.getElementById(`search-${tabId}`).value;
                
                // Update tabs and content visibility
                document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
                document.querySelectorAll('.tab-content').forEach(tc => tc.classList.remove('active'));
                this.classList.add('active');
                document.getElementById(tabId).classList.add('active');
                
                // Update table data
                updateTableData(tabId);
                
                // Apply search if there's a term
                if (searchTerm) {
                    const event = new Event('keyup');
                    document.getElementById(`search-${tabId}`).dispatchEvent(event);
                }
            });
        });

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
            
            // Debug information
            console.log(`Fetching details for ${type} with ID: ${id}`);
            
            // Fetch registration details via AJAX with error handling
            fetch(`get_registration_details.php?type=${type}&id=${id}`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! Status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    document.querySelector('.modal-loading').style.display = "none";
                    
                    console.log("Response data:", data); // Debug the response
                    
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
                    } else if (type === 'alumni') {
                        displayAlumniDetails(data);
                    }
                })
                .catch(error => {
                    document.querySelector('.modal-loading').style.display = "none";
                    document.querySelector('.registration-details').innerHTML = `
                        <div class="error-message">
                            <i class="fas fa-exclamation-circle"></i> An error occurred while fetching data: ${error.message || 'Unknown error'}
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

        // Function to display inhouse registration details with IST timezone adjustment
        function displayInhouseDetails(data) {
            const registration = data.registration;
            
            let html = `
                <div class="detail-section">
                    <h3>Student Information</h3>
                    <div class="detail-grid">
                        <div class="detail-item"><span>Name:</span> ${registration.student_name || 'N/A'}</div>
                        <div class="detail-item"><span>Gender:</span> ${registration.gender || 'N/A'}</div>
                        <div class="detail-item"><span>JIS ID:</span> ${registration.jis_id || 'N/A'}</div>
                        <div class="detail-item"><span>Email:</span> ${registration.email || 'N/A'}</div>
                        <div class="detail-item"><span>Mobile:</span> ${registration.mobile || 'N/A'}</div>
                        <div class="detail-item"><span>Department:</span> ${registration.department || 'N/A'}</div>
                    </div>
                </div>
                
                <div class="detail-section">
                    <h3>Competition Information</h3>
                    <div class="detail-grid">
                        <div class="detail-item"><span>Competition:</span> ${registration.competition_name || 'N/A'}</div>
                        <div class="detail-item"><span>Inhouse Competition:</span> ${registration.inhouse_competition || 'N/A'}</div>
                    </div>
                </div>
                
                <div class="detail-section">
                    <h3>Registration Information</h3>
                    <div class="detail-grid">
                        <div class="detail-item"><span>Payment Status:</span> <span class="status-badge ${registration.payment_status === 'Paid' ? 'paid' : 'not-paid'}">${registration.payment_status || 'Not Paid'}</span></div>
                        <div class="detail-item"><span>Registration Date:</span> ${registration.registration_date ? formatDateToIST(registration.registration_date) : 'N/A'}</div>
                    </div>
                </div>`;
                
            // Add payment details if paid
            if (registration.payment_status === 'Paid') {
                // Format payment date correctly from MongoDB timestamp
                let paymentDate = 'N/A';
                if (registration.payment_timestamp) {
                    // Format in IST timezone
                    paymentDate = formatDateToIST(registration.payment_timestamp);
                }
                
                html += `
                <div class="detail-section payment-info">
                    <h3>Payment Information</h3>
                    <div class="payment-field-modal"><span>Amount Paid:</span> ₹${registration.payment_amount || '500'}</div>
                    <div class="payment-field-modal"><span>Receipt Number:</span> ${registration.receipt_number || 'N/A'}</div>
                    <div class="payment-field-modal"><span>Payment Date:</span> ${paymentDate}</div>
                    <div class="payment-field-modal"><span>Payment Updated By:</span> ${registration.payment_updated_by || 'N/A'}</div>
                </div>`;
            }
            
            document.querySelector('.registration-details').innerHTML = html;
        }

        // Function to display alumni registration details with IST timezone adjustment
        function displayAlumniDetails(data) {
            const registration = data.registration;
            
            let html = `
                <div class="detail-section">
                    <h3>Alumni Information</h3>
                    <div class="detail-grid">
                        <div class="detail-item"><span>Name:</span> ${registration.alumni_name || 'N/A'}</div>
                        <div class="detail-item"><span>JIS ID:</span> ${registration.jis_id || 'N/A'}</div>
                        <div class="detail-item"><span>Gender:</span> ${registration.gender || 'N/A'}</div>
                        <div class="detail-item"><span>Email:</span> ${registration.email || 'N/A'}</div>
                        <div class="detail-item"><span>Mobile:</span> ${registration.mobile || 'N/A'}</div>
                        <div class="detail-item"><span>Passout Year:</span> ${registration.passout_year || 'N/A'}</div>
                        <div class="detail-item"><span>Department:</span> ${registration.department || 'N/A'}</div>
                        <div class="detail-item"><span>Current Organization:</span> ${registration.current_organization || 'N/A'}</div>
                    </div>
                </div>
                
                <div class="detail-section">
                    <h3>Registration Information</h3>
                    <div class="detail-grid">
                        <div class="detail-item"><span>Payment Status:</span> <span class="status-badge ${registration.payment_status === 'Paid' ? 'paid' : 'not-paid'}">${registration.payment_status || 'Not Paid'}</span></div>
                        <div class="detail-item"><span>Registration Date:</span> ${registration.registration_date ? formatDateToIST(registration.registration_date) : 'N/A'}</div>
                    </div>
                </div>`;
                
            // Add payment details if paid
            if (registration.payment_status === 'Paid') {
                // Format payment date correctly from MongoDB timestamp
                let paymentDate = 'N/A';
                if (registration.payment_timestamp) {
                    // Format in IST timezone
                    paymentDate = formatDateToIST(registration.payment_timestamp);
                }
                
                html += `
                <div class="detail-section payment-info">
                    <h3>Payment Information</h3>
                    <div class="payment-field-modal"><span>Amount Paid:</span> ₹${registration.payment_amount || '1000'}</div>
                    <div class="payment-field-modal"><span>Receipt Number:</span> ${registration.receipt_number || 'N/A'}</div>
                    <div class="payment-field-modal"><span>Payment Date:</span> ${paymentDate}</div>
                    <div class="payment-field-modal"><span>Payment Updated By:</span> ${registration.payment_updated_by || 'N/A'}</div>
                </div>`;
            }
            
            document.querySelector('.registration-details').innerHTML = html;
        }

        // Helper function to format dates to IST timezone
        function formatDateToIST(dateString) {
            try {
                // Check if it's MongoDB format with $date property
                if (typeof dateString === 'object' && dateString.$date) {
                    dateString = dateString.$date;
                }
                
                const date = new Date(dateString);
                
                // Check if date is valid
                if (isNaN(date.getTime())) {
                    return 'Invalid Date';
                }
                
                // Format to IST (UTC+5:30)
                return new Date(date.getTime()).toLocaleString('en-IN', {
                    timeZone: 'Asia/Kolkata',
                    year: 'numeric',
                    month: 'numeric',
                    day: 'numeric',
                    hour: 'numeric',
                    minute: 'numeric',
                    second: 'numeric',
                    hour12: true
                });
            } catch (e) {
                console.error('Error formatting date:', e);
                return 'Date Error';
            }
        }

        function updateTableData(tabId) {
            // Get the appropriate data based on tab
            const data = tabId === 'inhouse' ? <?php echo json_encode($inhouse_data); ?> : <?php echo json_encode($alumni_data); ?>;
            const tbody = document.querySelector(`#${tabId} .table-container tbody`);
            let html = '';
            
            if (data && data.length > 0) {
                data.forEach((row, index) => {
                    // Format registration date
                    const registrationDate = row.registration_date ? 
                        new Date(row.registration_date).toLocaleString('en-IN', {
                            timeZone: 'Asia/Kolkata',
                            day: '2-digit',
                            month: '2-digit',
                            year: 'numeric',
                            hour: '2-digit',
                            minute: '2-digit',
                            hour12: true
                        }) : 'N/A';
                    
                    if (tabId === 'inhouse') {
                        html += `
                            <tr>
                                <td>${index + 1}</td>
                                <td>${row.student_name || ''}</td>
                                <td>${row.jis_id || ''}</td>
                                <td>${row.gender || ''}</td>
                                <td>${row.department || ''}</td>
                                <td>${row.competition_name || ''}</td>
                                <td>${registrationDate}</td>
                                <td>
                                    <span class="status-badge ${(row.payment_status || '') === 'Paid' ? 'paid' : 'not-paid'}">
                                        ${row.payment_status || 'Not Paid'}
                                    </span>
                                </td>
                                <td>
                                    <button class="btn-view" onclick="viewDetails('${tabId}', '${row.jis_id}')">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    ${(row.payment_status || '') !== 'Paid' ? `
                                    <button class="btn-payment" onclick="openPaymentModal('${tabId}', '${row.jis_id}', '${row.student_name ? row.student_name.replace(/'/g, "\\'") : ''}')">
                                        <i class="fas fa-money-bill-wave"></i> Payment
                                    </button>
                                    ` : ''}
                                </td>
                            </tr>
                        `;
                    } else {
                        html += `
                            <tr>
                                <td>${index + 1}</td>
                                <td>${row.alumni_name || ''}</td>
                                <td>${row.jis_id || ''}</td>
                                <td>${row.gender || ''}</td>
                                <td>${row.email || ''}</td>
                                <td>${row.mobile || ''}</td>
                                <td>${row.department || ''}</td>
                                <td>${row.passout_year || ''}</td>
                                <td>${registrationDate}</td>
                                <td>
                                    <span class="status-badge ${(row.payment_status || '') === 'Paid' ? 'paid' : 'not-paid'}">
                                        ${row.payment_status || 'Not Paid'}
                                    </span>
                                </td>
                                <td>
                                    <button class="btn-view" onclick="viewDetails('${tabId}', '${row.jis_id}')">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    ${(row.payment_status || '') !== 'Paid' ? `
                                    <button class="btn-payment" onclick="openPaymentModal('${tabId}', '${row.jis_id}', '${row.alumni_name ? row.alumni_name.replace(/'/g, "\\'") : ''}')">
                                        <i class="fas fa-money-bill-wave"></i> Payment
                                    </button>
                                    ` : ''}
                                </td>
                            </tr>
                        `;
                    }
                });
            } else {
                html = `<tr><td colspan="${tabId === 'inhouse' ? '9' : '11'}" style="text-align: center;">No registrations found</td></tr>`;
            }
            
            tbody.innerHTML = html;
        }

        // Update tab switching functionality
        document.querySelectorAll('.tab').forEach(tab => {
            tab.addEventListener('click', function() {
                const tabId = this.getAttribute('data-tab');
                
                // Update tabs and content visibility
                document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
                document.querySelectorAll('.tab-content').forEach(tc => tc.classList.remove('active'));
                this.classList.add('active');
                document.getElementById(tabId).classList.add('active');
                
                // Update table data for the selected tab
                updateTableData(tabId);
                
                // Update URL without reloading
                const currentUrl = new URL(window.location.href);
                currentUrl.searchParams.set('tab', tabId);
                window.history.pushState({}, '', currentUrl);
            });
        });

        // Initialize table data for current tab
        const currentTab = '<?php echo $tab; ?>';
        updateTableData(currentTab);

        // Payment Modal functionality
        const paymentModal = document.getElementById('paymentModal');
        let currentPaymentType = '';
        let currentPaymentJisId = '';

        function openPaymentModal(type, jisId, name) {
            // Set current payment details
            currentPaymentType = type;
            currentPaymentJisId = jisId;
            
            // Update modal content
            document.getElementById('payment-jis-id').textContent = jisId;
            document.getElementById('payment-name').textContent = name;
            document.getElementById('receipt-number').value = '';
            
            // Set amount based on type
            const amount = type === 'alumni' ? 1000 : 500;
            document.getElementById('payment-amount').textContent = amount;
            
            // Hide any previous messages
            document.getElementById('payment-success').style.display = 'none';
            document.getElementById('payment-error').style.display = 'none';
            
            // Show modal
            paymentModal.style.display = 'block';
            document.body.classList.add('modal-open');
        }
        
        function closePaymentModal() {
            paymentModal.style.display = 'none';
            document.body.classList.remove('modal-open');
        }

        function confirmPayment() {
            const receiptNumber = document.getElementById('receipt-number').value.trim();
            if (!receiptNumber) {
                document.getElementById('payment-error-message').textContent = 'Please enter a receipt number';
                document.getElementById('payment-error').style.display = 'block';
                return;
            }
            
            // Disable buttons during processing
            const confirmBtn = document.querySelector('.btn-confirm-payment');
            const cancelBtn = document.querySelector('.btn-cancel-payment');
            confirmBtn.disabled = true;
            cancelBtn.disabled = true;
            confirmBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
            
            // Hide any previous messages
            document.getElementById('payment-success').style.display = 'none';
            document.getElementById('payment-error').style.display = 'none';
            
            // Get amount based on type
            const amount = currentPaymentType === 'alumni' ? 1000 : 500;
            
            // Send AJAX request to process payment
            fetch('process_payment.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    type: currentPaymentType,
                    jis_id: currentPaymentJisId,
                    receipt_number: receiptNumber,
                    amount: amount
                }),
            })
            .then(response => response.json())
            .then(data => {
                // Re-enable buttons
                confirmBtn.disabled = false;
                cancelBtn.disabled = false;
                confirmBtn.innerHTML = '<i class="fas fa-check"></i> Confirm Payment';
                
                if (data.success) {
                    // Show success message
                    document.getElementById('payment-success').style.display = 'block';
                    
                    // Auto close modal after delay and refresh page
                    setTimeout(() => {
                        closePaymentModal();
                        window.location.reload();
                    }, 2000);
                } else {
                    // Show error message
                    document.getElementById('payment-error-message').textContent = data.error || 'An error occurred while processing payment';
                    document.getElementById('payment-error').style.display = 'block';
                }
            })
            .catch(error => {
                // Re-enable buttons
                confirmBtn.disabled = false;
                cancelBtn.disabled = false;
                confirmBtn.innerHTML = '<i class="fas fa-check"></i> Confirm Payment';
                
                // Show error message
                document.getElementById('payment-error-message').textContent = 'An error occurred while processing payment';
                document.getElementById('payment-error').style.display = 'block';
                console.error('Error:', error);
            });
        }

        // Close payment modal when clicking outside
        window.onclick = function(event) {
            if (event.target === modal) {
                closeModal();
            } else if (event.target === paymentModal) {
                closePaymentModal();
            }
        }

        // Column visibility control
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize column control for inhouse table
            initColumnControl('inhouse');
            
            // Initialize column control for alumni table
            initColumnControl('alumni');
            
            // Apply initial column visibility
            applyInitialColumnVisibility();
            
            // Close dropdowns when clicking outside
            document.addEventListener('click', function(event) {
                if (!event.target.closest('.column-control')) {
                    document.querySelectorAll('.column-dropdown').forEach(dropdown => {
                        dropdown.classList.remove('show');
                    });
                }
            });
        });
        
        function applyInitialColumnVisibility() {
            const inhouseTable = document.querySelector('#inhouse table');
            const alumniTable = document.querySelector('#alumni table');
            
            // Hide inhouse columns initially
            toggleColumnVisibility(inhouseTable, 3, false); // Gender
            toggleColumnVisibility(inhouseTable, 5, false); // Competition
            toggleColumnVisibility(inhouseTable, 6, false); // Registration Date
            
            // Hide alumni columns initially
            toggleColumnVisibility(alumniTable, 3, false); // Gender
            toggleColumnVisibility(alumniTable, 8, false); // Registration Date
            
            // Update toggle all checkboxes
            updateToggleAllCheckbox('inhouse');
            updateToggleAllCheckbox('alumni');
        }
        
        function initColumnControl(tabId) {
            const controlBtn = document.getElementById(`column-control-${tabId}`);
            const dropdown = document.getElementById(`column-dropdown-${tabId}`);
            const table = document.querySelector(`#${tabId} table`);
            const checkboxes = dropdown.querySelectorAll('input[type="checkbox"]:not(#toggle-all-' + tabId + ')');
            const toggleAllCheckbox = document.getElementById(`toggle-all-${tabId}`);
            
            // Toggle dropdown
            controlBtn.addEventListener('click', function(e) {
                e.stopPropagation();
                dropdown.classList.toggle('show');
            });
            
            // Setup toggle all functionality
            toggleAllCheckbox.addEventListener('change', function() {
                const isChecked = this.checked;
                checkboxes.forEach(checkbox => {
                    checkbox.checked = isChecked;
                    
                    // Apply the visibility change
                    const columnIndex = getColumnIndex(checkbox.id, tabId);
                    if (columnIndex !== -1) {
                        toggleColumnVisibility(table, columnIndex, isChecked);
                        sessionStorage.setItem(checkbox.id, isChecked);
                    }
                });
            });
            
            // Handle checkbox changes
            checkboxes.forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    const columnIndex = getColumnIndex(checkbox.id, tabId);
                    if (columnIndex !== -1) {
                        toggleColumnVisibility(table, columnIndex, this.checked);
                        
                        // Store the column visibility preference in session storage
                        sessionStorage.setItem(checkbox.id, this.checked);
                    }
                    
                    // Update toggle all checkbox state
                    updateToggleAllCheckbox(tabId);
                });
                
                // Initialize checkbox from saved state (only if previously saved)
                const savedState = sessionStorage.getItem(checkbox.id);
                if (savedState !== null) {
                    const isChecked = savedState === 'true';
                    checkbox.checked = isChecked;
                    
                    // Apply the visibility setting (will be overridden by applyInitialColumnVisibility for first load)
                    const columnIndex = getColumnIndex(checkbox.id, tabId);
                    if (columnIndex !== -1) {
                        toggleColumnVisibility(table, columnIndex, isChecked);
                    }
                }
            });
            
            // Initialize toggle all checkbox state
            updateToggleAllCheckbox(tabId);
        }
        
        function updateToggleAllCheckbox(tabId) {
            const dropdown = document.getElementById(`column-dropdown-${tabId}`);
            const toggleAllCheckbox = document.getElementById(`toggle-all-${tabId}`);
            const optionalCheckboxes = Array.from(dropdown.querySelectorAll('input[type="checkbox"]:not(#toggle-all-' + tabId + ')'));
            
            // Check if all optional checkboxes are checked
            const allChecked = optionalCheckboxes.every(checkbox => checkbox.checked);
            toggleAllCheckbox.checked = allChecked;
        }
        
        function getColumnIndex(checkboxId, tabId) {
            // Get the column type from the checkbox ID
            const parts = checkboxId.split('-');
            const columnType = parts[1];
            
            // Fixed column indices for each table based on their HTML structure
            const columnMaps = {
                'inhouse': {
                    'gender': 3,
                    'department': 4,
                    'competition': 5,
                    'regdate': 6
                },
                'alumni': {
                    'gender': 3,
                    'email': 4,
                    'mobile': 5,
                    'department': 6,
                    'passoutyear': 7,
                    'regdate': 8
                }
            };
            
            return columnMaps[tabId] && columnMaps[tabId][columnType] !== undefined 
                ? columnMaps[tabId][columnType] 
                : -1;
        }
        
        function toggleColumnVisibility(table, columnIndex, isVisible) {
            // Get all headers and rows
            const headers = table.querySelectorAll('th');
            const rows = table.querySelectorAll('tbody tr');
            
            // Toggle header visibility
            if (headers[columnIndex]) {
                headers[columnIndex].style.display = isVisible ? '' : 'none';
            }
            
            // Toggle cell visibility in each row
            rows.forEach(row => {
                const cells = row.querySelectorAll('td');
                if (cells[columnIndex]) {
                    cells[columnIndex].style.display = isVisible ? '' : 'none';
                }
            });
        }
    </script>
</body>
</html>
