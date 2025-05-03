/**
 * Function to download filtered data as Excel
 * This function collects all current filter parameters and sends them to the export script
 */
function downloadFilteredExcel() {
    // Get all current filter values
    const type = document.getElementById('filter-type') ? document.getElementById('filter-type').value : 'all';
    const search = document.getElementById('search-input') ? document.getElementById('search-input').value : '';
    const paymentStatus = document.getElementById('filter-payment') ? document.getElementById('filter-payment').value : '';
    const department = document.getElementById('filter-department') ? document.getElementById('filter-department').value : '';
    
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
    
    // Determine the correct path to the download_excel.php file
    // We need to check if we're already in the control directory or not
    const currentPath = window.location.pathname;
    let downloadUrl;
    
    if (currentPath.includes('/control/')) {
        // Already in the control directory
        downloadUrl = 'download_excel.php?' + queryParams.toString();
    } else {
        // Need to navigate to the control directory
        downloadUrl = 'control/download_excel.php?' + queryParams.toString();
    }
    
    console.log("Downloading from URL:", downloadUrl);
    
    // Use direct location change for download
    window.location.href = downloadUrl;
}

/**
 * Initialize download button functionality when the page loads
 */
document.addEventListener('DOMContentLoaded', function() {
    console.log("DOM loaded, looking for download button");
    const downloadButton = document.getElementById('download-filtered-excel');
    
    if (downloadButton) {
        console.log("Download button found, attaching event listener");
        downloadButton.addEventListener('click', function(e) {
            e.preventDefault();
            console.log("Download button clicked");
            downloadFilteredExcel();
        });
    } else {
        console.log("Download button not found on page");
    }
});
