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
    <style>
        /* Additional inline styles to increase width of container */
        .container {
            width: 100%;
            max-width: 100%;
            padding: 20px;
        }
        .table-container {
            width: 100%;
            overflow-x: auto;
        }
        /* Ensure modal has proper width */
        .modal-content {
            width: 90%;
            max-width: 1200px;
        }
        /* Add these styles to your existing styles */
        .tab-content {
            display: none;
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        
        .tab-content.active {
            display: block;
            opacity: 1;
        }
        
        .filters-container {
            margin-bottom: 20px;
        }
        
        .filters-actions {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-top: 15px;
        }
        
        .search-box {
            flex: 1;
            position: relative;
        }
        
        .search-box input {
            width: 100%;
            padding: 10px 35px 10px 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
        }
        
        .search-box i {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: #666;
        }
        
        .btn-reset {
            padding: 10px 20px;
            background: #e74c3c;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .btn-reset:hover {
            background: #c0392b;
        }

        /* Responsive filters improvements */
        .filters {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
            gap: 15px;
            margin-bottom: 15px;
        }
        
        .filter-group {
            margin-bottom: 0;
        }
        
        .filters-actions {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 10px;
            margin-top: 15px;
        }
        
        .search-box {
            flex: 1;
            min-width: 200px;
            position: relative;
        }
        
        /* Responsive buttons */
        .btn-filter, .btn-reset {
            padding: 10px 15px;
            white-space: nowrap;
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        /* Mobile optimizations */
        @media (max-width: 768px) {
            .filters-actions {
                flex-direction: column;
                align-items: stretch;
            }
            
            .search-box {
                order: -1;
                width: 100%;
            }
            
            .btn-filter, .btn-reset {
                width: 100%;
                justify-content: center;
            }
            
            .filter-group select,
            .filter-group input {
                width: 100%;
                box-sizing: border-box;
            }
        }
        
        /* Medium screens */
        @media (min-width: 769px) and (max-width: 1024px) {
            .filters {
                grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
            }
        }
        
        /* Very small screens */
        @media (max-width: 480px) {
            .filters {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="navbar-title">
            <img src="../images/majisticlogo.png" alt="MaJIStic Logo" class="navbar-logo">
            <h1>MaJIStic 2K25 Admin Panel</h1>
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
                            <th>Registration Date</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $sl_no = ($page - 1) * $items_per_page + 1;
                        if (!empty($result) && $tab == 'inhouse') {
                            foreach ($result as $row):
                        ?>
                        <tr>
                            <td><?php echo $sl_no++; ?></td>
                            <td><?php echo $row['student_name'] ?? ''; ?></td>
                            <td><?php echo $row['jis_id'] ?? ''; ?></td>
                            <td><?php echo $row['gender'] ?? ''; ?></td>
                            <td><?php echo $row['department'] ?? ''; ?></td>
                            <td><?php echo $row['competition_name'] ?? ''; ?></td>
                            <td>
                                <span class="status-badge <?php echo ($row['payment_status'] ?? '') == 'Paid' ? 'paid' : 'not-paid'; ?>">
                                    <?php echo $row['payment_status'] ?? 'Not Paid'; ?>
                                </span>
                            </td>
                            <td><?php echo isset($row['registration_date']) ? date('d M Y', strtotime($row['registration_date'])) : ''; ?></td>
                            <td>
                                <button class="btn-view" onclick="viewDetails('inhouse', '<?php echo $row['jis_id']; ?>')">
                                    <i class="fas fa-eye"></i> View
                                </button>
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
                <button class="btn btn-download" onclick="downloadCSV('alumni')">
                    <i class="fas fa-download"></i> Download CSV
                </button>
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
                            <th>Current Organization</th>
                            <th>Payment Status</th>
                            <th>Registration Date</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $sl_no = ($page - 1) * $items_per_page + 1;
                        if (!empty($result) && $tab == 'alumni') {
                            foreach ($result as $row):
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
                            <td><?php echo $row['current_organization'] ?? ''; ?></td>
                            <td>
                                <span class="status-badge <?php echo ($row['payment_status'] ?? '') == 'Paid' ? 'paid' : 'not-paid'; ?>">
                                    <?php echo $row['payment_status'] ?? 'Not Paid'; ?>
                                </span>
                            </td>
                            <td><?php echo isset($row['registration_date']) ? date('d M Y', strtotime($row['registration_date'])) : ''; ?></td>
                            <td>
                                <button class="btn-view" onclick="viewDetails('alumni', '<?php echo $row['jis_id']; ?>')">
                                    <i class="fas fa-eye"></i> View
                                </button>
                            </td>
                        </tr>
                        <?php 
                            endforeach;
                        } else {
                            echo '<tr><td colspan="12" style="text-align: center;">No registrations found</td></tr>';
                        }
                        ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination for alumni -->
            <?php if ($alumni_total_pages > 0): ?>
            <div class="pagination">
                <?php if ($page > 1): ?>
                <a href="?tab=alumni&page=1<?php echo $gender_filter ? '&gender='.$gender_filter : ''; ?><?php echo $payment_status_filter ? '&payment_status='.$payment_status_filter : ''; ?><?php echo $passout_year_filter ? '&passout_year='.$passout_year_filter : ''; ?><?php echo $department_filter ? '&department='.$department_filter : ''; ?>" class="pagination-btn">
                    <i class="fas fa-angle-double-left"></i>
                </a>
                <a href="?tab=alumni&page=<?php echo $page-1; ?><?php echo $gender_filter ? '&gender='.$gender_filter : ''; ?><?php echo $payment_status_filter ? '&payment_status='.$payment_status_filter : ''; ?><?php echo $passout_year_filter ? '&passout_year='.$passout_year_filter : ''; ?><?php echo $department_filter ? '&department='.$department_filter : ''; ?>" class="pagination-btn">
                    <i class="fas fa-angle-left"></i>
                </a>
                <?php endif; ?>
                
                <?php
                $start_page = max(1, $page - 2);
                $end_page = min($alumni_total_pages, $page + 2);
                
                for ($i = $start_page; $i <= $end_page; $i++):
                ?>
                <a href="?tab=alumni&page=<?php echo $i; ?><?php echo $gender_filter ? '&gender='.$gender_filter : ''; ?><?php echo $payment_status_filter ? '&payment_status='.$payment_status_filter : ''; ?><?php echo $passout_year_filter ? '&passout_year='.$passout_year_filter : ''; ?><?php echo $department_filter ? '&department='.$department_filter : ''; ?>" class="pagination-btn <?php echo ($page == $i) ? 'active' : ''; ?>">
                    <?php echo $i; ?>
                </a>
                <?php endfor; ?>
                
                <?php if ($page < $alumni_total_pages): ?>
                <a href="?tab=alumni&page=<?php echo $page+1; ?><?php echo $gender_filter ? '&gender='.$gender_filter : ''; ?><?php echo $payment_status_filter ? '&payment_status='.$payment_status_filter : ''; ?><?php echo $passout_year_filter ? '&passout_year='.$passout_year_filter : ''; ?><?php echo $department_filter ? '&department='.$department_filter : ''; ?>" class="pagination-btn">
                    <i class="fas fa-angle-right"></i>
                </a>
                <a href="?tab=alumni&page=<?php echo $alumni_total_pages; ?><?php echo $gender_filter ? '&gender='.$gender_filter : ''; ?><?php echo $payment_status_filter ? '&payment_status='.$payment_status_filter : ''; ?><?php echo $passout_year_filter ? '&passout_year='.$passout_year_filter : ''; ?><?php echo $department_filter ? '&department='.$department_filter : ''; ?>" class="pagination-btn">
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
            
            // Fetch registration details via AJAX
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
        
        // Function to display inhouse registration details
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
                        <div class="detail-item"><span>Registration Date:</span> ${registration.registration_date ? new Date(registration.registration_date).toLocaleString() : 'N/A'}</div>
                    </div>
                </div>`;
            
            document.querySelector('.registration-details').innerHTML = html;
        }
        
        // Function to display alumni registration details
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
                        <div class="detail-item"><span>Registration Date:</span> ${registration.registration_date ? new Date(registration.registration_date).toLocaleString() : 'N/A'}</div>
                    </div>
                </div>`;
            
            document.querySelector('.registration-details').innerHTML = html;
        }

        function updateTableData(tabId) {
            // Get the appropriate data based on tab
            const data = tabId === 'inhouse' ? <?php echo json_encode($inhouse_data); ?> : <?php echo json_encode($alumni_data); ?>;
            const tbody = document.querySelector(`#${tabId} .table-container tbody`);
            let html = '';

            if (data && data.length > 0) {
                data.forEach((row, index) => {
                    if (tabId === 'inhouse') {
                        html += `
                            <tr>
                                <td>${index + 1}</td>
                                <td>${row.student_name || ''}</td>
                                <td>${row.jis_id || ''}</td>
                                <td>${row.gender || ''}</td>
                                <td>${row.department || ''}</td>
                                <td>${row.competition_name || ''}</td>
                                <td>
                                    <span class="status-badge ${(row.payment_status || '') === 'Paid' ? 'paid' : 'not-paid'}">
                                        ${row.payment_status || 'Not Paid'}
                                    </span>
                                </td>
                                <td>${row.registration_date ? new Date(row.registration_date).toLocaleDateString() : ''}</td>
                                <td>
                                    <button class="btn-view" onclick="viewDetails('${tabId}', '${row.jis_id}')">
                                        <i class="fas fa-eye"></i> View
                                    </button>
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
                                <td>${row.current_organization || ''}</td>
                                <td>
                                    <span class="status-badge ${(row.payment_status || '') === 'Paid' ? 'paid' : 'not-paid'}">
                                        ${row.payment_status || 'Not Paid'}
                                    </span>
                                </td>
                                <td>${row.registration_date ? new Date(row.registration_date).toLocaleDateString() : ''}</td>
                                <td>
                                    <button class="btn-view" onclick="viewDetails('${tabId}', '${row.jis_id}')">
                                        <i class="fas fa-eye"></i> View
                                    </button>
                                </td>
                            </tr>
                        `;
                    }
                });
            } else {
                html = `<tr><td colspan="${tabId === 'inhouse' ? '9' : '12'}" style="text-align: center;">No registrations found</td></tr>`;
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
    </script>
</body>
</html>
