<?php
// Special debug file for hostinger environment
// Access this file directly to diagnose 500 errors

// Enable error display
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<h1>Hostinger Environment Debug</h1>";

// Basic environment information
echo "<h2>Server Information</h2>";
echo "<ul>";
echo "<li>PHP Version: " . phpversion() . "</li>";
echo "<li>Server: " . ($_SERVER['SERVER_SOFTWARE'] ?? 'Unknown') . "</li>";
echo "<li>Document Root: " . ($_SERVER['DOCUMENT_ROOT'] ?? 'Unknown') . "</li>";
echo "<li>Host: " . ($_SERVER['HTTP_HOST'] ?? 'Unknown') . "</li>";
echo "</ul>";

// Check temp directory
echo "<h2>Directory Permissions</h2>";
$tempDir = __DIR__ . '/temp';
$templatesDir = __DIR__ . '/templates';

echo "<h3>Temp Directory</h3>";
echo "Path: " . $tempDir . "<br>";
if (file_exists($tempDir)) {
    echo "Status: Exists<br>";
    echo "Writable: " . (is_writable($tempDir) ? "Yes" : "No") . "<br>";
    
    // Try to write a test file
    $testFile = $tempDir . '/test_' . time() . '.txt';
    $writeTest = @file_put_contents($testFile, 'Test content');
    if ($writeTest !== false) {
        echo "Write Test: Success<br>";
        @unlink($testFile);
    } else {
        echo "Write Test: Failed<br>";
        echo "Error: " . error_get_last()['message'] . "<br>";
    }
    
    // List permissions
    echo "Permissions: " . substr(sprintf('%o', fileperms($tempDir)), -4) . "<br>";
} else {
    echo "Status: Does not exist<br>";
    
    // Try to create it
    $mkdirResult = @mkdir($tempDir, 0755, true);
    if ($mkdirResult) {
        echo "Created Successfully<br>";
    } else {
        echo "Creation Failed<br>";
        echo "Error: " . error_get_last()['message'] . "<br>";
    }
}

echo "<h3>Templates Directory</h3>";
echo "Path: " . $templatesDir . "<br>";
if (file_exists($templatesDir)) {
    echo "Status: Exists<br>";
    
    // List template files
    echo "<h4>Template Files:</h4>";
    $files = scandir($templatesDir);
    echo "<ul>";
    foreach ($files as $file) {
        if ($file != '.' && $file != '..') {
            echo "<li>" . htmlspecialchars($file) . " - " . 
                 (is_readable($templatesDir . '/' . $file) ? "Readable" : "Not Readable") .
                 "</li>";
        }
    }
    echo "</ul>";
} else {
    echo "Status: Does not exist<br>";
}

// Check database connection
echo "<h2>Database Connection</h2>";
try {
    require_once __DIR__ . '/../includes/db_config.php';
    
    if (isset($db)) {
        echo "Connection: Success<br>";
        
        // Check certificate_records table
        $tableCheck = $db->query("SHOW TABLES LIKE 'certificate_records'");
        if ($tableCheck->rowCount() > 0) {
            echo "certificate_records table: Exists<br>";
            
            // Check structure
            echo "<h3>Table Structure:</h3>";
            $columns = $db->query("DESCRIBE certificate_records")->fetchAll(PDO::FETCH_ASSOC);
            echo "<table border='1' cellpadding='5'>";
            echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";
            
            foreach ($columns as $column) {
                echo "<tr>";
                echo "<td>" . $column['Field'] . "</td>";
                echo "<td>" . $column['Type'] . "</td>";
                echo "<td>" . $column['Null'] . "</td>";
                echo "<td>" . $column['Key'] . "</td>";
                echo "<td>" . $column['Default'] . "</td>";
                echo "</tr>";
            }
            echo "</table>";
            
            // Check sample records
            $recordCount = $db->query("SELECT COUNT(*) as count FROM certificate_records")->fetch(PDO::FETCH_ASSOC);
            echo "Record count: " . $recordCount['count'] . "<br>";
        } else {
            echo "certificate_records table: Does not exist<br>";
        }
    } else {
        echo "Connection: Failed - \$db variable not defined<br>";
    }
} catch (Exception $e) {
    echo "Connection: Failed<br>";
    echo "Error: " . htmlspecialchars($e->getMessage()) . "<br>";
}

// Test certificate template rendering
echo "<h2>Certificate Template Test</h2>";
try {
    // Create a simple test to see if we can render HTML to an image
    $testHtml = '<html><body><h1 style="color:blue;">Test Certificate</h1><p>This is a test to see if HTML can be rendered</p></body></html>';
    $testFile = __DIR__ . '/temp/test_html_' . time() . '.html';
    
    // Save test HTML
    if (@file_put_contents($testFile, $testHtml)) {
        echo "Test HTML file created successfully<br>";
    } else {
        echo "Failed to create test HTML file<br>";
    }
    
    // Check if we have the GD library
    if (function_exists('imagecreatetruecolor')) {
        echo "GD Library: Available<br>";
        
        // Test if we can create a basic image
        $img = @imagecreatetruecolor(400, 200);
        if ($img) {
            echo "Image Creation: Success<br>";
            
            // Text and background colors
            $white = imagecolorallocate($img, 255, 255, 255);
            $black = imagecolorallocate($img, 0, 0, 0);
            
            // Fill background
            imagefill($img, 0, 0, $white);
            
            // Add text
            imagestring($img, 5, 100, 100, 'Test Image Generated', $black);
            
            // Save image
            $testImagePath = __DIR__ . '/temp/test_image_' . time() . '.png';
            if (imagepng($img, $testImagePath)) {
                echo "Image Saved: Success<br>";
                echo "<img src='temp/" . basename($testImagePath) . "' alt='Test Image'>";
            } else {
                echo "Image Saved: Failed<br>";
            }
            
            // Clean up
            imagedestroy($img);
        } else {
            echo "Image Creation: Failed<br>";
        }
    } else {
        echo "GD Library: Not available<br>";
    }
} catch (Exception $e) {
    echo "Template Test Failed:<br>";
    echo "Error: " . htmlspecialchars($e->getMessage()) . "<br>";
}

echo "<h2>Error Log</h2>";
$errorLogPath = __DIR__ . '/error_log.txt';

if (file_exists($errorLogPath)) {
    echo "Error Log File: Exists<br>";
    echo "Size: " . filesize($errorLogPath) . " bytes<br>";
    echo "Last Modified: " . date('Y-m-d H:i:s', filemtime($errorLogPath)) . "<br>";
    
    if (is_readable($errorLogPath)) {
        echo "<h3>Recent Errors</h3>";
        echo "<pre>";
        // Show last 20 lines
        $lines = file($errorLogPath);
        $lastLines = array_slice($lines, -20);
        foreach ($lastLines as $line) {
            echo htmlspecialchars($line);
        }
        echo "</pre>";
    } else {
        echo "Error log is not readable<br>";
    }
} else {
    echo "Error Log File: Does not exist<br>";
}

// PHP modules and extensions
echo "<h2>PHP Extensions</h2>";
$requiredExtensions = ['pdo', 'pdo_mysql', 'gd', 'zip', 'mbstring'];
echo "<ul>";
foreach ($requiredExtensions as $ext) {
    echo "<li>{$ext}: " . (extension_loaded($ext) ? "Loaded" : "Not loaded") . "</li>";
}
echo "</ul>";

echo "<p><strong>All Loaded Extensions:</strong> " . implode(', ', get_loaded_extensions()) . "</p>";

// Memory and upload limits
echo "<h2>PHP Limits</h2>";
echo "<ul>";
echo "<li>memory_limit: " . ini_get('memory_limit') . "</li>";
echo "<li>post_max_size: " . ini_get('post_max_size') . "</li>";
echo "<li>upload_max_filesize: " . ini_get('upload_max_filesize') . "</li>";
echo "<li>max_execution_time: " . ini_get('max_execution_time') . " seconds</li>";
echo "</ul>";
?>
