<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Start or resume session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Authentication check - users with 'CheckIn' or 'Super Admin' role can access
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true || 
    !isset($_SESSION['admin_role']) || ($_SESSION['admin_role'] !== 'CheckIn' && $_SESSION['admin_role'] !== 'Super Admin')) {
    // Not logged in or not a CheckIn user, redirect to login page
    header('Location: ../user/login.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>maJIStic 2K25 - Check-In Day 2</title>
  <link rel="stylesheet" href="styles.css">
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
</head>
<body>
  <div class="container">
    <div class="header">
      <div class="logo-container">
        <img src="../images/majisticlogo.png" alt="maJIStic 2K25" class="logo" onerror="this.onerror=null; this.src='data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIyMDAiIGhlaWdodD0iODAiIHZpZXdCb3g9IjAgMCAyMDAgODAiPjxyZWN0IHg9IjAiIHk9IjAiIHdpZHRoPSIxMDAlIiBoZWlnaHQ9IjEwMCUiIGZpbGw9InJnYmEoNDQsNjIsODAsLjgpIi8+PHRleHQgeD0iNTAlIiB5PSI1MCUiIGZvbnQtc2l6ZT0iMjQiIHRleHQtYW5jaG9yPSJtaWRkbGUiIGFsaWdubWVudC1iYXNlbGluZT0ibWlkZGxlIiBmb250LWZhbWlseT0ic2Fucy1zZXJpZiIgZmlsbD0id2hpdGUiPm1hSklTdGljIDJLMjU8L3RleHQ+PC9zdmc+'">
      </div>
      <p class="subtitle">Participant Check-In Portal <span class="day-indicator">(Day 2)</span></p>
      <div class="user-info">
        <p>Welcome, <?php echo htmlspecialchars($_SESSION['admin_username']); ?> 
        <a href="../user/logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a></p>
      </div>
    </div>
    
    <div class="scanner-container" id="scanner-container">
      <h2 class="scanner-title"><i class="fas fa-qrcode"></i> Scan Participant QR Code</h2>
      <div id="reader"></div>
    </div>
    
    <div class="result-container" id="result-container" style="display: none;">
      <div id="result">
        <p class="scan-instruction"><i class="fas fa-info-circle"></i> Please scan a participant QR code to see details</p>
      </div>
      <div class="action-buttons">
        <button id="reset-button" class="reset-button"><i class="fas fa-redo-alt"></i> Reset Scanner</button>
      </div>
    </div>
    
    <footer class="footer">
      <p>&copy; 2025 Techternity Digital Solutions</p>
    </footer>
  </div>

  <script>
    // Show the result container and hide scanner when results are available
    function toggleContainers(showResults) {
      const scannerContainer = document.getElementById('scanner-container');
      const resultContainer = document.getElementById('result-container');
      
      if (showResults) {
        scannerContainer.style.display = 'none';
        resultContainer.style.display = 'flex';
      } else {
        scannerContainer.style.display = 'block';
        resultContainer.style.display = 'none';
      }
    }
    
    // Initialize
    toggleContainers(false);
    
    function onScanSuccess(decodedText) {
      const resultElement = document.getElementById("result");
      
      // Parse the QR code content - expecting format with name, email, jis_id
      // Extract the JIS ID
      let jis_id = '';
      
      try {
        // Check if it's JSON format
        let qrData = JSON.parse(decodedText);
        jis_id = qrData.jis_id || '';
      } catch (e) {
        // Not JSON, try other formats
        
        // Check for JIS/YEAR/NUMBER or JISU/YEAR/NUMBER pattern
        const jisIdRegex = /(JIS(?:U)?\/\d{4}\/\d+)/i;
        const jisIdMatch = decodedText.match(jisIdRegex);
        
        if (jisIdMatch) {
          jis_id = jisIdMatch[1];
        } else {
          // Try explicit labeling: "JIS ID: VALUE"
          const labeledMatch = decodedText.match(/JIS ID:?\s*([^,\s]+)/i) || 
                              decodedText.match(/jis_id:?\s*([^,\s]+)/i);
                              
          if (labeledMatch) {
            jis_id = labeledMatch[1];
          } else {
            // If all parsing fails, use the entire text as JIS ID
            jis_id = decodedText.trim();
          }
        }
      }
      
      // Clean up any extra whitespace or quotes
      jis_id = jis_id.replace(/["']/g, '').trim();
      
      // Make sure JIS ID is properly capitalized
      if (/^jis(u)?\/\d{4}\/\d+$/i.test(jis_id)) {
        jis_id = jis_id.replace(/^jis(u)?/i, function(match, p1) {
          return p1 ? 'JISU' : 'JIS';
        });
      }
      
      resultElement.innerHTML = `<p class="scanned-info">Scanned JIS ID: ${jis_id}</p><div class="loading"></div>`;
      toggleContainers(true); // Show results, hide scanner

      // Call fetch API to get student data using the extracted JIS ID
      fetch("fetch_user.php?jis_id=" + encodeURIComponent(jis_id))
        .then(res => res.text())
        .then(data => {
          resultElement.innerHTML = `<p class="scanned-info">Scanned JIS ID: ${jis_id}</p>${data}`;
          
          // More robust check for payment status
          // Look for any element containing payment information
          const paymentInfo = resultElement.innerHTML.toLowerCase();
          if (paymentInfo.includes('not paid') || paymentInfo.includes('payment status: not') || paymentInfo.includes('payment: not')) {
            console.log('Unpaid participant detected - disabling check-in buttons');
            // Find all check-in buttons and disable them
            const checkinButtons = resultElement.querySelectorAll('.checkin-button');
            checkinButtons.forEach(button => {
              button.disabled = true;
              button.style.opacity = '0.5';
              button.style.cursor = 'not-allowed';
              button.title = 'Cannot check-in: Payment pending';
              // Add a visual indicator
              button.innerHTML += ' <small>(Payment Required)</small>';
            });
          }
          
          // Re-attach event listeners for dynamically added buttons
          document.querySelectorAll('.checkin-button').forEach(button => {
            button.addEventListener('click', function() {
              if (!this.disabled) {
                checkinUser(this.getAttribute('data-jisid'), this.getAttribute('data-type'));
              }
            });
          });
        })
        .catch(error => {
          resultElement.innerHTML = `<p class="scanned-info">Scanned JIS ID: ${jis_id}</p><p class="error-message">Error: Unable to fetch participant data</p>`;
          console.error('Error:', error);
        });

      html5QrcodeScanner.clear();
    }

    function checkinUser(jis_id, type, day = 2) { // Default to day 2
      const btn = event.target;
      btn.disabled = true;
      btn.innerHTML = `<span class="loading"></span> Processing...`;
      
      fetch("checkin.php?jis_id=" + encodeURIComponent(jis_id) + "&type=" + encodeURIComponent(type) + "&day=" + encodeURIComponent(day))
        .then(res => res.text())
        .then(data => {
          alert(data);
          // Use setTimeout to ensure the alert is properly closed
          // before changing the page state
          setTimeout(() => {
            resetScanner();
          }, 500);
        })
        .catch(error => {
          alert("Error processing check-in. Please try again.");
          btn.disabled = false;
          btn.innerHTML = "Day " + day + " Check In";
        });
    }

    // Initialize the QR scanner
    let html5QrcodeScanner;
    
    function initScanner() {
      html5QrcodeScanner = new Html5QrcodeScanner("reader", { 
        fps: 10, 
        qrbox: { width: 250, height: 250 },
        formatsToSupport: [ Html5QrcodeSupportedFormats.QR_CODE ]
      });
      
      html5QrcodeScanner.render(onScanSuccess);
    }
    
    // Initialize scanner on page load
    initScanner();
    
    // Reset function to clear results and reinitialize scanner
    function resetScanner() {
      const resultElement = document.getElementById("result");
      resultElement.innerHTML = '<p class="scan-instruction"><i class="fas fa-info-circle"></i> Please scan a participant QR code to see details</p>';
      
      toggleContainers(false); // Show scanner, hide results
      
      // Clear HTML5 QR scanner if it exists
      if (html5QrcodeScanner) {
        html5QrcodeScanner.clear().then(() => {
          // Reinitialize scanner
          initScanner();
        }).catch(error => {
          console.error("Failed to clear scanner:", error);
          // Force reload as fallback
          location.reload();
        });
      } else {
        location.reload();
      }
    }
    
    // Add event listener for reset button
    document.getElementById('reset-button').addEventListener('click', resetScanner);
  </script>
</body>
</html>
