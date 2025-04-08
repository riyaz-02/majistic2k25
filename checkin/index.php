<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>maJIStic 2K25 - Check-In</title>
  <link rel="stylesheet" href="styles.css">
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap">
  <script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
</head>
<body>
  <div class="container">
    <div class="header">
      <h1 class="title">maJIStic 2K25</h1>
      <p class="subtitle">Participant Check-In Portal</p>
    </div>
    
    <div class="scanner-container">
      <h2 class="scanner-title">Scan Participant QR Code</h2>
      <div id="reader"></div>
    </div>
    
    <div class="result-container" id="result-container">
      <div id="result">
        <p class="scan-instruction">Please scan a participant QR code to see details</p>
      </div>
    </div>
  </div>

  <script>
    // Show/hide result container based on content
    function updateResultVisibility() {
      const resultContainer = document.getElementById('result-container');
      const resultContent = document.getElementById('result').innerText.trim();
      
      if (resultContent === 'Please scan a participant QR code to see details') {
        resultContainer.style.opacity = '0.7';
      } else {
        resultContainer.style.opacity = '1';
      }
    }
    
    // Initialize
    updateResultVisibility();
    
    function onScanSuccess(decodedText) {
      const resultElement = document.getElementById("result");
      resultElement.innerHTML = `<p class="scanned-info">Scanned JIS ID: ${decodedText}</p><div class="loading"></div>`;
      updateResultVisibility();

      // Call fetch API to get student data
      fetch("fetch_user.php?jis_id=" + decodedText)
        .then(res => res.text())
        .then(data => {
          resultElement.innerHTML = `<p class="scanned-info">Scanned JIS ID: ${decodedText}</p>${data}`;
          updateResultVisibility();
        })
        .catch(error => {
          resultElement.innerHTML = `<p class="scanned-info">Scanned JIS ID: ${decodedText}</p><p class="error-message">Error: Unable to fetch participant data</p>`;
          console.error('Error:', error);
          updateResultVisibility();
        });

      html5QrcodeScanner.clear();
    }

    function checkinUser(jis_id) {
      const btn = event.target;
      btn.disabled = true;
      btn.innerHTML = `<span class="loading"></span> Processing...`;
      
      fetch("checkin.php?jis_id=" + jis_id)
        .then(res => res.text())
        .then(data => {
          alert(data);
          location.reload();
        })
        .catch(error => {
          alert("Error processing check-in. Please try again.");
          btn.disabled = false;
          btn.innerHTML = "Check In";
        });
    }

    const html5QrcodeScanner = new Html5QrcodeScanner("reader", { 
      fps: 10, 
      qrbox: { width: 250, height: 250 },
      formatsToSupport: [ Html5QrcodeSupportedFormats.QR_CODE ]
    });
    
    html5QrcodeScanner.render(onScanSuccess);

    // Add global event listener for checkin buttons
    document.addEventListener('click', function(event) {
      if (event.target.classList.contains('checkin-button')) {
        const jis_id = event.target.getAttribute('data-jisid');
        checkinUser(jis_id);
      }
    });
  </script>
</body>
</html>
