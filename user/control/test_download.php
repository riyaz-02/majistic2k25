<?php
// This is a simple test file to debug download issues

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check for logged in status
$isLoggedIn = isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
$userRole = isset($_SESSION['admin_role']) ? $_SESSION['admin_role'] : 'None';

// Output headers
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Download Test</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-5">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5>Excel Download Test</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <p><strong>Session Status:</strong> <?php echo $isLoggedIn ? 'Logged In' : 'Not Logged In'; ?></p>
                    <p><strong>User Role:</strong> <?php echo htmlspecialchars($userRole); ?></p>
                </div>
                
                <div class="mb-3">
                    <label for="filter-type">Registration Type:</label>
                    <select id="filter-type" class="form-control">
                        <option value="all">All Registrations</option>
                        <option value="student">Students Only</option>
                        <option value="alumni">Alumni Only</option>
                    </select>
                </div>
                
                <div class="mb-3">
                    <label for="search-input">Search:</label>
                    <input type="text" id="search-input" class="form-control" placeholder="Search by name, email, ID...">
                </div>
                
                <div class="mb-3">
                    <label for="filter-payment">Payment Status:</label>
                    <select id="filter-payment" class="form-control">
                        <option value="">All</option>
                        <option value="Paid">Paid</option>
                        <option value="Unpaid">Unpaid</option>
                    </select>
                </div>
                
                <div class="mb-3">
                    <label for="filter-department">Department:</label>
                    <select id="filter-department" class="form-control">
                        <option value="">All Departments</option>
                        <option value="CSE">CSE</option>
                        <option value="CSE AI-ML">CSE AI-ML</option>
                        <option value="CST">CST</option>
                        <option value="IT">IT</option>
                        <option value="ECE">ECE</option>
                        <option value="EE">EE</option>
                        <option value="BME">BME</option>
                        <option value="CE">CE</option>
                        <option value="ME">ME</option>
                        <option value="AGE">AGE</option>
                        <option value="BBA">BBA</option>
                        <option value="MBA">MBA</option>
                        <option value="BCA">BCA</option>
                        <option value="MCA">MCA</option>
                        <option value="Diploma ME">Diploma ME</option>
                        <option value="Diploma CE">Diploma CE</option>
                        <option value="Diploma EE">Diploma EE</option>
                        <option value="B. Pharmacy">Pharmacy</option>
                    </select>
                </div>
                
                <div class="mt-4">
                    <button type="button" id="download-filtered-excel" class="btn btn-success">
                        <i class="fas fa-file-excel"></i> Download Excel
                    </button>
                    
                    <button type="button" id="direct-download" class="btn btn-primary ml-2">
                        Direct Download
                    </button>
                </div>
                
                <div id="debug-output" class="mt-4 alert alert-info">
                    Debug information will appear here
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const debugOutput = document.getElementById('debug-output');
        debugOutput.textContent = "Page loaded. Download buttons initialized.";
        
        // Regular download button
        const downloadButton = document.getElementById('download-filtered-excel');
        if (downloadButton) {
            downloadButton.addEventListener('click', function(e) {
                e.preventDefault();
                debugOutput.textContent += "\nDownload button clicked.";
                downloadFilteredExcel();
            });
        }
        
        // Direct download button
        const directButton = document.getElementById('direct-download');
        if (directButton) {
            directButton.addEventListener('click', function(e) {
                e.preventDefault();
                debugOutput.textContent += "\nDirect download button clicked.";
                
                const type = document.getElementById('filter-type').value;
                window.location.href = 'download_excel.php?type=' + type;
            });
        }
        
        function downloadFilteredExcel() {
            // Get all current filter values
            const type = document.getElementById('filter-type').value;
            const search = document.getElementById('search-input').value;
            const paymentStatus = document.getElementById('filter-payment').value;
            const department = document.getElementById('filter-department').value;
            
            // Build the query string with all filter parameters
            let queryParams = new URLSearchParams();
            
            if (type) {
                queryParams.append('type', type);
            }
            
            if (search) {
                queryParams.append('search', search);
            }
            
            if (paymentStatus) {
                queryParams.append('payment_status', paymentStatus);
            }
            
            if (department) {
                queryParams.append('department', department);
            }
            
            // Create the URL for the Excel download with the filter parameters
            const downloadUrl = 'download_excel.php?' + queryParams.toString();
            
            debugOutput.textContent += "\nAttempting download from: " + downloadUrl;
            
            // Trigger download
            window.location.href = downloadUrl;
        }
    });
    </script>
</body>
</html>
