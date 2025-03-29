<?php
// This is a simple diagnostic tool to check if your database connection works

// Start error logging
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<h1>maJIStic Integration Diagnostic Tool</h1>";

// 1. Check if required files exist
echo "<h2>1. Checking Required Files</h2>";
$required_files = [
    __DIR__ . '/../../includes/db_config.php',
    __DIR__ . '/update_checkin_external.php',
    __DIR__ . '/webhook.php'
];

foreach ($required_files as $file) {
    echo "Checking $file: ";
    if (file_exists($file)) {
        echo "<span style='color:green'>FOUND</span><br>";
    } else {
        echo "<span style='color:red'>MISSING</span><br>";
    }
}

// 2. Test database connection
echo "<h2>2. Testing Database Connection</h2>";
try {
    require_once __DIR__ . '/../../includes/db_config.php';
    echo "DB Config included successfully<br>";
    
    if (!isset($db)) {
        echo "<span style='color:red'>ERROR: \$db variable not found in db_config.php</span><br>";
    } else {
        echo "Database connection: <span style='color:green'>SUCCESS</span><br>";
        
        // 3. Test database queries
        echo "<h2>3. Testing Database Queries</h2>";
        
        // Check if registrations table exists
        $tables = ["registrations", "alumni_registrations"];
        foreach ($tables as $table) {
            try {
                $stmt = $db->query("SHOW TABLES LIKE '$table'");
                $tableExists = $stmt->rowCount() > 0;
                
                if ($tableExists) {
                    echo "Table $table: <span style='color:green'>EXISTS</span><br>";
                    
                    // Count records
                    $stmt = $db->query("SELECT COUNT(*) FROM $table");
                    $count = $stmt->fetchColumn();
                    echo "Total records in $table: $count<br>";
                    
                    // Count paid records
                    $stmt = $db->query("SELECT COUNT(*) FROM $table WHERE payment_status = 'Paid'");
                    $paidCount = $stmt->fetchColumn();
                    echo "Paid records in $table: $paidCount<br>";
                    
                    // List columns
                    $stmt = $db->query("DESCRIBE $table");
                    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
                    echo "Columns in $table: " . implode(", ", $columns) . "<br><br>";
                    
                    if ($paidCount > 0) {
                        echo "<strong>Sample Data from $table:</strong><br>";
                        $stmt = $db->query("SELECT * FROM $table WHERE payment_status = 'Paid' LIMIT 1");
                        $row = $stmt->fetch(PDO::FETCH_ASSOC);
                        
                        echo "<pre>";
                        print_r($row);
                        echo "</pre>";
                    }
                } else {
                    echo "Table $table: <span style='color:red'>DOES NOT EXIST</span><br>";
                }
            } catch (PDOException $e) {
                echo "Error checking table $table: " . $e->getMessage() . "<br>";
            }
        }
    }
} catch (Exception $e) {
    echo "<span style='color:red'>ERROR: " . $e->getMessage() . "</span><br>";
}

// 4. Check log file permissions
echo "<h2>4. Checking Log Directory and Permissions</h2>";
$log_dir = __DIR__ . '/logs';
echo "Log directory path: $log_dir<br>";

if (!file_exists($log_dir)) {
    echo "Log directory: <span style='color:orange'>DOES NOT EXIST, will try to create it</span><br>";
    try {
        mkdir($log_dir, 0755, true);
        echo "Created log directory: <span style='color:green'>SUCCESS</span><br>";
    } catch (Exception $e) {
        echo "Failed to create log directory: <span style='color:red'>" . $e->getMessage() . "</span><br>";
    }
} else {
    echo "Log directory: <span style='color:green'>EXISTS</span><br>";
}

if (file_exists($log_dir)) {
    echo "Log directory writable: ";
    if (is_writable($log_dir)) {
        echo "<span style='color:green'>YES</span><br>";
        
        // Try to write a test log
        $test_file = "$log_dir/test_" . time() . ".log";
        try {
            file_put_contents($test_file, "Test log write at " . date('Y-m-d H:i:s'));
            echo "Test log write: <span style='color:green'>SUCCESS</span><br>";
            unlink($test_file); // Remove test file
        } catch (Exception $e) {
            echo "Test log write: <span style='color:red'>FAILED - " . $e->getMessage() . "</span><br>";
        }
    } else {
        echo "<span style='color:red'>NO</span> - Please set proper permissions<br>";
    }
}

// 5. Show PHP info summary
echo "<h2>5. PHP Environment</h2>";
echo "PHP Version: " . phpversion() . "<br>";
echo "PDO Drivers: " . implode(", ", PDO::getAvailableDrivers()) . "<br>";
echo "Extensions: " . implode(", ", get_loaded_extensions()) . "<br>";
echo "Memory Limit: " . ini_get('memory_limit') . "<br>";
echo "Max Execution Time: " . ini_get('max_execution_time') . " seconds<br>";
echo "Display Errors: " . ini_get('display_errors') . "<br>";
echo "Error Reporting: " . ini_get('error_reporting') . "<br>";
?>
