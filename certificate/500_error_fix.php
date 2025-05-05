<?php
/**
 * Certificate Error Diagnostic and Repair Tool
 * 
 * This script helps diagnose and fix issues with certificate generation system
 * especially focusing on the 500 errors occurring with multiple role certificates.
 */

// Start the session
session_start();

// Basic security - restricted to logged-in users or with an admin token
$allowAccess = false;

// Check for admin token in URL (you can set this to any secure random value)
$adminToken = 'majistic2k25_admin_token';
if (isset($_GET['token']) && $_GET['token'] === $adminToken) {
    $allowAccess = true;
}

// If not authorized, exit with access denied
if (!$allowAccess) {
    http_response_code(403);
    echo "<h1>Access Denied</h1>";
    echo "<p>You don't have permission to access this diagnostic tool.</p>";
    exit;
}

// Include necessary files
require_once __DIR__ . '/../includes/db_config.php';
require_once 'certificate_functions.php';

// Initialize results array
$results = [];

// Header
echo "<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Certificate System Diagnostics</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 20px;
            background: #f5f5f5;
            color: #333;
        }
        h1 {
            background: #4a6fa5;
            color: white;
            padding: 15px 20px;
            margin: -20px -20px 20px;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
        }
        .card {
            background: white;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            padding: 20px;
            margin-bottom: 20px;
        }
        .success { color: #2ecc71; }
        .warning { color: #f39c12; }
        .error { color: #e74c3c; }
        .info { color: #3498db; }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        th, td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #f8f9fa;
            font-weight: bold;
        }
        tr:hover {
            background-color: #f9f9f9;
        }
        .button {
            display: inline-block;
            padding: 8px 16px;
            background: #4a6fa5;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            border: none;
            cursor: pointer;
            font-size: 14px;
        }
        .button:hover {
            background: #3a5a80;
        }
        .tab {
            overflow: hidden;
            border: 1px solid #ccc;
            background-color: #f1f1f1;
            border-radius: 5px 5px 0 0;
        }
        .tab button {
            background-color: inherit;
            float: left;
            border: none;
            outline: none;
            cursor: pointer;
            padding: 14px 16px;
            transition: 0.3s;
            font-size: 16px;
        }
        .tab button:hover {
            background-color: #ddd;
        }
        .tab button.active {
            background-color: white;
            border-bottom: 2px solid #4a6fa5;
        }
        .tabcontent {
            display: none;
            padding: 20px;
            border: 1px solid #ccc;
            border-top: none;
            border-radius: 0 0 5px 5px;
            animation: fadeEffect 1s;
            background: white;
        }
        @keyframes fadeEffect {
            from {opacity: 0;}
            to {opacity: 1;}
        }
        pre {
            background: #f8f8f8;
            padding: 10px;
            border-radius: 5px;
            overflow: auto;
        }
        .action-buttons {
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <h1>Certificate System Diagnostics</h1>
    <div class='container'>";

// Check if a user ID has been provided for diagnostics
if (isset($_GET['test_jisid'])) {
    $jis_id = $_GET['test_jisid'];
    
    echo "<div class='card'>
        <h2>Running Diagnostic Tests for JIS ID: {$jis_id}</h2>";
    
    // Step 1: Check if user exists in database
    echo "<h3>Step 1: Database Lookup</h3>";
    try {
        $registration = getRegistrationByJisId($db, $jis_id);
        
        if ($registration) {
            echo "<p class='success'>✓ User found in database: " . htmlspecialchars($registration['student_name']) . "</p>";
            
            // Determine roles
            $rolesToGenerate = [];
            $competitionName = $registration['competition_name'] ?? '';
            $staffRole = $registration['role'] ?? '';
            
            if (!empty(trim($competitionName))) {
                $rolesToGenerate[] = 'Participant';
            }
            
            if (!empty(trim($staffRole))) {
                if (stripos($staffRole, 'volunteer') !== false) {
                    $rolesToGenerate[] = 'Volunteer';
                }
                
                if (stripos($staffRole, 'crew') !== false) {
                    $rolesToGenerate[] = 'Crew Member';
                }
            }
            
            if (empty($rolesToGenerate)) {
                echo "<p class='error'>✗ No eligible roles found for this user</p>";
            } else {
                echo "<p class='success'>✓ Detected roles: " . implode(", ", $rolesToGenerate) . "</p>";
                
                // Step 2: Test individual certificate generation for each role
                echo "<h3>Step 2: Individual Certificate Tests</h3>";
                
                $individualFiles = [];
                foreach ($rolesToGenerate as $role) {
                    echo "<h4>Testing {$role} Certificate</h4>";
                    
                    try {
                        $tempPath = '';
                        $studentName = $registration['student_name'] ?? '';
                        
                        switch ($role) {
                            case 'Participant':
                                $tempPath = generateParticipantCertificate($studentName, $jis_id);
                                break;
                            case 'Volunteer':
                                $tempPath = generateVolunteerCertificate($studentName, $jis_id);
                                break;
                            case 'Crew Member':
                                $tempPath = generateCrewCertificate($studentName, $jis_id);
                                break;
                        }
                        
                        if ($tempPath && file_exists($tempPath)) {
                            $fileSize = filesize($tempPath);
                            echo "<p class='success'>✓ {$role} certificate generated: {$tempPath} ({$fileSize} bytes)</p>";
                            $individualFiles[$role] = $tempPath;
                        } else {
                            echo "<p class='error'>✗ Failed to generate {$role} certificate</p>";
                        }
                    } catch (Exception $e) {
                        echo "<p class='error'>✗ Error generating {$role} certificate: " . htmlspecialchars($e->getMessage()) . "</p>";
                    }
                }
                
                // Step 3: Test combined certificate generation
                if (count($rolesToGenerate) > 1) {
                    echo "<h3>Step 3: Combined Certificate Test</h3>";
                    
                    try {
                        $combinedPath = generateMultipleRoleCertificates(
                            $registration['student_name'] ?? '',
                            $jis_id,
                            null,
                            $rolesToGenerate
                        );
                        
                        if ($combinedPath && file_exists($combinedPath)) {
                            $fileSize = filesize($combinedPath);
                            $fileType = mime_content_type($combinedPath);
                            echo "<p class='success'>✓ Combined certificate generated: {$combinedPath} ({$fileSize} bytes, {$fileType})</p>";
                            
                            if (pathinfo($combinedPath, PATHINFO_EXTENSION) === 'zip') {
                                echo "<p class='info'>ℹ Combined certificate was generated as ZIP file since PDF merging wasn't available</p>";
                            }
                            
                            echo "<div class='action-buttons'>
                                <a href='?token={$adminToken}&action=download&path=" . urlencode($combinedPath) . "' class='button'>Download Combined Certificate</a>
                            </div>";
                            
                            // Delete the generated file after testing if not needed
                            if (!isset($_GET['keep_files'])) {
                                @unlink($combinedPath);
                                echo "<p class='info'>ℹ Combined certificate file deleted after testing</p>";
                            }
                        } else {
                            echo "<p class='error'>✗ Failed to generate combined certificate</p>";
                        }
                    } catch (Exception $e) {
                        echo "<p class='error'>✗ Error generating combined certificate: " . htmlspecialchars($e->getMessage()) . "</p>";
                        echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
                    }
                }
                
                // Delete individual files if not keeping them
                if (!isset($_GET['keep_files'])) {
                    foreach ($individualFiles as $role => $path) {
                        if (file_exists($path)) {
                            @unlink($path);
                        }
                    }
                    echo "<p class='info'>ℹ Individual certificate files deleted after testing</p>";
                }
            }
        } else {
            echo "<p class='error'>✗ No user found with JIS ID: {$jis_id}</p>";
        }
    } catch (Exception $e) {
        echo "<p class='error'>✗ Database error: " . htmlspecialchars($e->getMessage()) . "</p>";
    }
    
    echo "</div>"; // Close the card
}

// Check if we need to download a generated file
if (isset($_GET['action']) && $_GET['action'] === 'download' && isset($_GET['path'])) {
    $path = urldecode($_GET['path']);
    
    // Basic security check
    if (!file_exists($path) || strpos($path, 'temp/') === false) {
        echo "<div class='card'>
            <p class='error'>Invalid or inaccessible file path.</p>
        </div>";
    } else {
        $filename = basename($path);
        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        // Determine content type
        switch ($extension) {
            case 'pdf':
                $contentType = 'application/pdf';
                break;
            case 'zip':
                $contentType = 'application/zip';
                break;
            default:
                $contentType = 'application/octet-stream';
        }
        
        // Send the file
        header('Content-Type: ' . $contentType);
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . filesize($path));
        readfile($path);
        exit;
    }
}

// System diagnostics section
echo "<div class='card'>
    <h2>System Diagnostics</h2>
    
    <div class='tab'>
        <button class='tablinks active' onclick='openTab(event, \"Environment\")'>Environment</button>
        <button class='tablinks' onclick='openTab(event, \"Libraries\")'>Libraries</button>
        <button class='tablinks' onclick='openTab(event, \"Files\")'>Files</button>
        <button class='tablinks' onclick='openTab(event, \"Memory\")'>Memory</button>
    </div>
    
    <div id='Environment' class='tabcontent' style='display: block;'>
        <h3>PHP Environment</h3>
        <table>
            <tr>
                <th>Setting</th>
                <th>Value</th>
                <th>Status</th>
            </tr>
            <tr>
                <td>PHP Version</td>
                <td>" . phpversion() . "</td>
                <td>" . (version_compare(PHP_VERSION, '7.0.0') >= 0 ? "<span class='success'>✓ OK</span>" : "<span class='warning'>⚠ Outdated</span>") . "</td>
            </tr>
            <tr>
                <td>Memory Limit</td>
                <td>" . ini_get('memory_limit') . "</td>
                <td>" . (return_bytes(ini_get('memory_limit')) >= 128 * 1024 * 1024 ? "<span class='success'>✓ OK</span>" : "<span class='warning'>⚠ Low</span>") . "</td>
            </tr>
            <tr>
                <td>max_execution_time</td>
                <td>" . ini_get('max_execution_time') . "</td>
                <td>" . (ini_get('max_execution_time') >= 30 ? "<span class='success'>✓ OK</span>" : "<span class='warning'>⚠ Low</span>") . "</td>
            </tr>
            <tr>
                <td>post_max_size</td>
                <td>" . ini_get('post_max_size') . "</td>
                <td></td>
            </tr>
            <tr>
                <td>upload_max_filesize</td>
                <td>" . ini_get('upload_max_filesize') . "</td>
                <td></td>
            </tr>
            <tr>
                <td>allow_url_fopen</td>
                <td>" . (ini_get('allow_url_fopen') ? 'On' : 'Off') . "</td>
                <td>" . (ini_get('allow_url_fopen') ? "<span class='success'>✓ Enabled</span>" : "<span class='error'>✗ Disabled</span>") . "</td>
            </tr>
        </table>
    </div>
    
    <div id='Libraries' class='tabcontent'>
        <h3>Required Libraries</h3>
        <table>
            <tr>
                <th>Library</th>
                <th>Status</th>
                <th>Location</th>
            </tr>";

// Check for FPDF
$fpdfPaths = [
    __DIR__ . '/../vendor/setasign/fpdf/fpdf.php',
    __DIR__ . '/vendor/setasign/fpdf/fpdf.php'
];

$fpdfFound = false;
$fpdfPath = '';

foreach ($fpdfPaths as $path) {
    if (file_exists($path)) {
        $fpdfFound = true;
        $fpdfPath = $path;
        break;
    }
}

echo "<tr>
    <td>FPDF</td>
    <td>" . ($fpdfFound ? "<span class='success'>✓ Found</span>" : "<span class='error'>✗ Missing</span>") . "</td>
    <td>" . htmlspecialchars($fpdfPath) . "</td>
</tr>";

// Check for FPDI
$fpdiPaths = [
    __DIR__ . '/../vendor/setasign/fpdi/src/autoload.php',
    __DIR__ . '/vendor/setasign/fpdi/src/autoload.php',
    __DIR__ . '/../vendor/autoload.php',
    __DIR__ . '/vendor/autoload.php'
];

$fpdiFound = false;
$fpdiPath = '';

foreach ($fpdiPaths as $path) {
    if (file_exists($path)) {
        $fpdiFound = true;
        $fpdiPath = $path;
        break;
    }
}

echo "<tr>
    <td>FPDI</td>
    <td>" . ($fpdiFound ? "<span class='success'>✓ Found</span>" : "<span class='error'>✗ Missing</span>") . "</td>
    <td>" . htmlspecialchars($fpdiPath) . "</td>
</tr>";

echo "<tr>
    <td>ZipArchive</td>
    <td>" . (class_exists('ZipArchive') ? "<span class='success'>✓ Available</span>" : "<span class='error'>✗ Missing</span>") . "</td>
    <td>PHP Core Extension</td>
</tr>";

// Check for command line tools
$pdftk = false;
$gs = false;
$gsCmd = '';

if (function_exists('exec') && !ini_get('safe_mode')) {
    // Check for pdftk
    @exec('which pdftk 2>/dev/null', $output, $returnVar);
    $pdftk = ($returnVar === 0);
    
    // Check for ghostscript
    @exec('which gs 2>/dev/null', $output, $returnVar);
    $gs = ($returnVar === 0);
    $gsCmd = 'gs';
    
    // On Windows, try different ghostscript versions
    if (!$gs && strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
        @exec('where gswin64c.exe', $output, $returnVar);
        $gs = ($returnVar === 0);
        $gsCmd = 'gswin64c.exe';
        
        if (!$gs) {
            @exec('where gswin32c.exe', $output, $returnVar);
            $gs = ($returnVar === 0);
            $gsCmd = 'gswin32c.exe';
        }
    }
}

echo "<tr>
    <td>pdftk</td>
    <td>" . ($pdftk ? "<span class='success'>✓ Installed</span>" : "<span class='warning'>⚠ Not found</span>") . "</td>
    <td>System Command</td>
</tr>";

echo "<tr>
    <td>GhostScript</td>
    <td>" . ($gs ? "<span class='success'>✓ Installed</span> ($gsCmd)" : "<span class='warning'>⚠ Not found</span>") . "</td>
    <td>System Command</td>
</tr>";

echo "</table>
    </div>
    
    <div id='Files' class='tabcontent'>
        <h3>Template Files</h3>";

$templatesDir = __DIR__ . "/templates";
if (!is_dir($templatesDir)) {
    echo "<p class='error'>✗ Templates directory does not exist: " . htmlspecialchars($templatesDir) . "</p>";
} else {
    echo "<p class='success'>✓ Templates directory found: " . htmlspecialchars($templatesDir) . "</p>";
    
    $templates = glob("$templatesDir/*_template.pdf");
    if (empty($templates)) {
        echo "<p class='error'>✗ No certificate templates found</p>";
    } else {
        echo "<table>
            <tr>
                <th>Template</th>
                <th>Size</th>
                <th>Status</th>
            </tr>";
            
        foreach ($templates as $template) {
            $filename = basename($template);
            $filesize = filesize($template);
            $readable = is_readable($template);
            
            echo "<tr>
                <td>" . htmlspecialchars($filename) . "</td>
                <td>" . formatBytes($filesize) . "</td>
                <td>" . ($readable ? "<span class='success'>✓ Readable</span>" : "<span class='error'>✗ Not readable</span>") . "</td>
            </tr>";
        }
        
        echo "</table>";
    }
}

// Check temp directory
$tempDir = __DIR__ . "/temp";
echo "<h3>Temporary Directory</h3>";

if (!is_dir($tempDir)) {
    echo "<p class='error'>✗ Temp directory does not exist: " . htmlspecialchars($tempDir) . "</p>";
    echo "<div class='action-buttons'>
        <a href='?token={$adminToken}&action=create_temp_dir' class='button'>Create Temp Directory</a>
    </div>";
} else {
    echo "<p class='success'>✓ Temp directory found: " . htmlspecialchars($tempDir) . "</p>";
    
    // Check permissions
    $isWritable = is_writable($tempDir);
    echo "<p>" . ($isWritable ? "<span class='success'>✓ Directory is writable</span>" : "<span class='error'>✗ Directory is not writable</span>") . "</p>";
    
    if (!$isWritable) {
        echo "<div class='action-buttons'>
            <a href='?token={$adminToken}&action=fix_permissions' class='button'>Fix Permissions</a>
        </div>";
    }
    
    // Show files in temp directory
    $tempFiles = glob("$tempDir/*");
    echo "<p>Temporary files: " . count($tempFiles) . "</p>";
    
    if (count($tempFiles) > 0) {
        echo "<table>
            <tr>
                <th>File</th>
                <th>Size</th>
                <th>Modified</th>
            </tr>";
            
        foreach (array_slice($tempFiles, 0, 10) as $file) {
            $filename = basename($file);
            $filesize = filesize($file);
            $modified = date("Y-m-d H:i:s", filemtime($file));
            
            echo "<tr>
                <td>" . htmlspecialchars($filename) . "</td>
                <td>" . formatBytes($filesize) . "</td>
                <td>" . $modified . "</td>
            </tr>";
        }
        
        if (count($tempFiles) > 10) {
            echo "<tr><td colspan='3'>... and " . (count($tempFiles) - 10) . " more files</td></tr>";
        }
        
        echo "</table>";
        
        echo "<div class='action-buttons'>
            <a href='?token={$adminToken}&action=clean_temp' class='button'>Clean Temp Directory</a>
        </div>";
    }
}

echo "</div>
    
    <div id='Memory' class='tabcontent'>
        <h3>Memory Usage</h3>";

$memoryLimit = ini_get('memory_limit');
$memoryUsage = memory_get_usage(true);
$peakMemory = memory_get_peak_usage(true);

echo "<p>Memory limit: " . $memoryLimit . "</p>";
echo "<p>Current memory usage: " . formatBytes($memoryUsage) . "</p>";
echo "<p>Peak memory usage: " . formatBytes($peakMemory) . "</p>";

// Show percentage of memory used
$memoryLimitBytes = return_bytes($memoryLimit);
$percentUsed = ($memoryUsage / $memoryLimitBytes) * 100;
$percentPeak = ($peakMemory / $memoryLimitBytes) * 100;

echo "<div style='background: #eee; border-radius: 5px; height: 20px; margin: 10px 0;'>
    <div style='background: #4a6fa5; width: {$percentUsed}%; height: 100%; border-radius: 5px;'></div>
</div>";
echo "<p>Current: " . number_format($percentUsed, 2) . "% of limit</p>";

echo "<div style='background: #eee; border-radius: 5px; height: 20px; margin: 10px 0;'>
    <div style='background: " . ($percentPeak > 80 ? '#e74c3c' : '#4a6fa5') . "; width: {$percentPeak}%; height: 100%; border-radius: 5px;'></div>
</div>";
echo "<p>Peak: " . number_format($percentPeak, 2) . "% of limit</p>";

echo "</div>
</div>";

// Test Form
echo "<div class='card'>
    <h2>Certificate Generation Test</h2>
    <p>Enter a JIS ID to test certificate generation:</p>
    
    <form action='' method='get'>
        <input type='hidden' name='token' value='{$adminToken}'>
        <input type='text' name='test_jisid' placeholder='Enter JIS ID' required style='padding: 8px; width: 250px;'>
        <label><input type='checkbox' name='keep_files' value='1'> Keep generated files</label>
        <button type='submit' class='button'>Run Test</button>
    </form>
</div>";

// Handle actions
if (isset($_GET['action'])) {
    switch ($_GET['action']) {
        case 'create_temp_dir':
            if (!is_dir($tempDir)) {
                if (mkdir($tempDir, 0755, true)) {
                    echo "<div class='card'><p class='success'>✓ Temp directory created successfully</p></div>";
                } else {
                    echo "<div class='card'><p class='error'>✗ Failed to create temp directory</p></div>";
                }
            }
            break;
            
        case 'fix_permissions':
            if (is_dir($tempDir)) {
                if (chmod($tempDir, 0755)) {
                    echo "<div class='card'><p class='success'>✓ Permissions updated successfully</p></div>";
                } else {
                    echo "<div class='card'><p class='error'>✗ Failed to update permissions</p></div>";
                }
            }
            break;
            
        case 'clean_temp':
            if (is_dir($tempDir)) {
                $files = glob("$tempDir/*");
                $count = 0;
                
                foreach ($files as $file) {
                    if (is_file($file)) {
                        if (unlink($file)) {
                            $count++;
                        }
                    }
                }
                
                echo "<div class='card'><p class='success'>✓ Cleaned {$count} temporary files</p></div>";
            }
            break;
    }
}

// Footer with JavaScript for tabs
echo "</div>
<script>
function openTab(evt, tabName) {
    var i, tabcontent, tablinks;
    tabcontent = document.getElementsByClassName('tabcontent');
    for (i = 0; i < tabcontent.length; i++) {
        tabcontent[i].style.display = 'none';
    }
    tablinks = document.getElementsByClassName('tablinks');
    for (i = 0; i < tablinks.length; i++) {
        tablinks[i].className = tablinks[i].className.replace(' active', '');
    }
    document.getElementById(tabName).style.display = 'block';
    evt.currentTarget.className += ' active';
}
</script>
</body>
</html>";

/**
 * Format bytes to human readable format
 */
function formatBytes($bytes, $precision = 2) {
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    
    $bytes /= pow(1024, $pow);
    
    return round($bytes, $precision) . ' ' . $units[$pow];
}

/**
 * Convert memory limit string to bytes
 */
function return_bytes($val) {
    $val = trim($val);
    $last = strtolower($val[strlen($val)-1]);
    $val = (int) $val;
    switch($last) {
        case 'g':
            $val *= 1024;
        case 'm':
            $val *= 1024;
        case 'k':
            $val *= 1024;
    }
    return $val;
}
