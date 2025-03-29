<?php
/**
 * Test script for checking webhook functionality
 * This script simulates what your external MongoDB portal would do
 */

// Your API key
$apiKey = 'd2bf07f0b99d6e139c36d3ee09beb762fbfb516dacc0193e215299583e96e30f';

// Configuration
$baseUrl = 'https://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']);
// Remove any trailing slash
$baseUrl = rtrim($baseUrl, '/');
$webhookUrl = $baseUrl . '/webhook.php';

// Test JIS ID - REPLACE THIS WITH A VALID JIS ID FROM YOUR DATABASE
$testJisId = isset($_POST['jis_id']) ? $_POST['jis_id'] : 'JIS/2024/2563'; 

// Create a test function to try both GET and POST operations
function runTests($apiKey, $baseUrl, $webhookUrl, $testJisId) {
    echo "<h2>Testing Webhook Integration</h2>";
    
    // Test 1: Check if a student/alumni exists with the given JIS ID
    echo "<h3>Test 1: Checking if student/alumni exists</h3>";
    $statusUrl = $baseUrl . '/update_checkin_external.php';
    
    // Setup curl with proper options to handle redirects
    $ch = curl_init($statusUrl . '?jis_id=' . urlencode($testJisId));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); // Follow redirects
    curl_setopt($ch, CURLOPT_MAXREDIRS, 5);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "X-API-KEY: $apiKey"
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    echo "<p>GET Request to: $statusUrl?jis_id=" . htmlspecialchars($testJisId) . "</p>";
    
    if ($error) {
        echo "<p><strong>cURL Error:</strong> " . htmlspecialchars($error) . "</p>";
    }
    
    echo "<p>HTTP Response Code: $httpCode</p>";
    
    $responseData = json_decode($response, true);
    echo "<pre>" . htmlspecialchars(json_encode($responseData, JSON_PRETTY_PRINT) ?: $response) . "</pre>";
    
    // If the student doesn't exist, prompt to update the JIS ID
    if ($httpCode !== 200 || !isset($responseData['success']) || $responseData['success'] !== true) {
        echo '<div style="color: red; padding: 10px; background-color: #ffeeee; border: 1px solid #ffcccc; margin: 10px 0;">';
        echo '<strong>Error:</strong> The test JIS ID does not exist in the database or is not in "Paid" status.';
        echo '<p>Please enter a valid JIS ID from your database in the form above.</p>';
        echo '</div>';
        return;
    }
    
    // If student exists, continue with Test 2
    echo "<h3>Test 2: Updating check-in status for Day 1</h3>";
    
    // Create the payload for the check-in request
    $payload = [
        'jis_id' => $testJisId,
        'checkin_day' => 1,
        'checkin_status' => true,
        'timestamp' => date('c')
    ];
    
    $ch = curl_init($webhookUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Content-Type: application/json",
        "X-API-KEY: $apiKey"
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    echo "<p>POST Request to: $webhookUrl</p>";
    echo "<p>Payload:</p>";
    echo "<pre>" . htmlspecialchars(json_encode($payload, JSON_PRETTY_PRINT)) . "</pre>";
    
    if ($error) {
        echo "<p><strong>cURL Error:</strong> " . htmlspecialchars($error) . "</p>";
    }
    
    echo "<p>HTTP Response Code: $httpCode</p>";
    
    $responseData = json_decode($response, true);
    echo "<pre>" . htmlspecialchars(json_encode($responseData, JSON_PRETTY_PRINT) ?: $response) . "</pre>";
    
    if ($httpCode !== 200 || !isset($responseData['success']) || $responseData['success'] !== true) {
        echo '<div style="color: red; padding: 10px; background-color: #ffeeee; border: 1px solid #ffcccc; margin: 10px 0;">';
        echo '<strong>Error:</strong> Failed to update check-in status.';
        echo '</div>';
        return;
    }
    
    // Test 3: Verify the check-in was updated
    echo "<h3>Test 3: Verifying check-in status was updated</h3>";
    
    $ch = curl_init($statusUrl . '?jis_id=' . urlencode($testJisId));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "X-API-KEY: $apiKey"
    ]);
    
    $response = curl_exec($ch);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($error) {
        echo "<p><strong>cURL Error:</strong> " . htmlspecialchars($error) . "</p>";
    }
    
    $responseData = json_decode($response, true);
    echo "<pre>" . htmlspecialchars(json_encode($responseData, JSON_PRETTY_PRINT) ?: $response) . "</pre>";
    
    // Check if day 1 is now marked as checked in
    if (isset($responseData['check_in_status']['day1']['checked_in']) && 
        $responseData['check_in_status']['day1']['checked_in'] === true) {
        echo '<div style="color: green; padding: 10px; background-color: #eeffee; border: 1px solid #ccffcc; margin: 10px 0;">';
        echo '<strong>Success!</strong> Day 1 check-in was successfully updated.';
        echo '</div>';
    } else {
        echo '<div style="color: red; padding: 10px; background-color: #ffeeee; border: 1px solid #ffcccc; margin: 10px 0;">';
        echo '<strong>Error:</strong> Day 1 check-in was not successfully updated.';
        echo '</div>';
    }
}

// HTML output
?>
<!DOCTYPE html>
<html>
<head>
    <title>Webhook Integration Test</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            padding: 20px;
            max-width: 1000px;
            margin: 0 auto;
        }
        h1, h2, h3 {
            color: #2c3e50;
        }
        pre {
            background-color: #f8f9fa;
            padding: 10px;
            border-radius: 5px;
            overflow-x: auto;
        }
        .test-form {
            background-color: #f0f0f0;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .test-form input[type="text"] {
            width: 250px;
            padding: 5px;
        }
        .test-form input[type="submit"] {
            background-color: #4CAF50;
            color: white;
            padding: 8px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .info-panel {
            background-color: #e6f7ff;
            border: 1px solid #91d5ff;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <h1>maJIStic 2K25 Webhook Integration Test</h1>
    
    <div class="info-panel">
        <p><strong>Current Configuration:</strong></p>
        <p>Base URL: <?= htmlspecialchars($baseUrl) ?></p>
        <p>Webhook URL: <?= htmlspecialchars($webhookUrl) ?></p>
        <p>API Key: <?= substr($apiKey, 0, 8) . '...' ?></p>
    </div>
    
    <div class="test-form">
        <form method="post">
            <p>Enter a valid JIS ID to test (must exist in your database and have paid status):</p>
            <input type="text" name="jis_id" value="<?= htmlspecialchars($testJisId) ?>" placeholder="Enter JIS ID">
            <input type="submit" value="Run Tests">
        </form>
    </div>
    
    <?php
    // If form is submitted, use the provided JIS ID
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['jis_id'])) {
        $testJisId = trim($_POST['jis_id']);
        runTests($apiKey, $baseUrl, $webhookUrl, $testJisId);
    } else {
        echo "<p>Enter a valid JIS ID from your database and click 'Run Tests' to begin testing the webhook integration.</p>";
    }
    ?>
    
    <h3>Troubleshooting Tips</h3>
    <ul>
        <li>Make sure the JIS ID exists in your database and has payment_status = 'Paid'</li>
        <li>Check if the JIS ID format matches exactly what's in your database (e.g., "JIS/2024/2563")</li>
        <li>Verify that your server allows connections to itself</li>
        <li>Check HTTPS vs HTTP - if using HTTPS, make sure your certificate is valid</li>
        <li>Look for 301/302 redirects - they could indicate URL path issues</li>
        <li>Check the logs directory for detailed error messages</li>
        <li>Make sure your API key is correct in all files</li>
    </ul>
</body>
</html>
