<?php
// This is a debugging script to check database connectivity and table structure

// Set error reporting for maximum information
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Load database configuration
require_once __DIR__ . '/../includes/db_config.php';

echo "<h1>Database Connection Test</h1>";

if (!isset($db)) {
    echo "<p style='color:red'>Database connection variable \$db is not defined!</p>";
    exit;
}

try {
    // Try a simple query to verify connection
    $result = $db->query("SELECT 1");
    echo "<p style='color:green'>✅ Database connection successful!</p>";
    
    // Check if registrations exists
    $tables = $db->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    echo "<h2>Tables in database:</h2>";
    echo "<ul>";
    foreach ($tables as $table) {
        echo "<li>" . htmlspecialchars($table) . "</li>";
    }
    echo "</ul>";
    
    // If registrations exists, show its structure
    if (in_array('registrations', $tables)) {
        echo "<h2>registrations structure:</h2>";
        $columns = $db->query("DESCRIBE registrations")->fetchAll(PDO::FETCH_ASSOC);
        echo "<table border='1'>";
        echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
        foreach ($columns as $column) {
            echo "<tr>";
            foreach ($column as $key => $value) {
                echo "<td>" . htmlspecialchars($value ?? 'NULL') . "</td>";
            }
            echo "</tr>";
        }
        echo "</table>";
        
        // Show a few sample records
        echo "<h2>Sample records:</h2>";
        $samples = $db->query("SELECT * FROM registrations LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
        if (count($samples) > 0) {
            echo "<table border='1'>";
            // Headers
            echo "<tr>";
            foreach (array_keys($samples[0]) as $header) {
                echo "<th>" . htmlspecialchars($header) . "</th>";
            }
            echo "</tr>";
            
            // Data
            foreach ($samples as $row) {
                echo "<tr>";
                foreach ($row as $value) {
                    echo "<td>" . htmlspecialchars($value ?? 'NULL') . "</td>";
                }
                echo "</tr>";
            }
            echo "</table>";
            
            // Show JIS ID test
            echo "<h2>JIS ID search test:</h2>";
            echo "<form method='get'>";
            echo "<input type='text' name='test_jis_id' placeholder='Enter JIS ID to test'>";
            echo "<input type='submit' value='Test'>";
            echo "</form>";
            
            if (isset($_GET['test_jis_id'])) {
                $test_jis_id = $_GET['test_jis_id'];
                echo "<p>Testing JIS ID: " . htmlspecialchars($test_jis_id) . "</p>";
                
                $stmt = $db->prepare("SELECT * FROM registrations WHERE jis_id = ?");
                $stmt->execute([$test_jis_id]);
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($result) {
                    echo "<p style='color:green'>✅ JIS ID found!</p>";
                    echo "<pre>" . print_r($result, true) . "</pre>";
                } else {
                    echo "<p style='color:red'>❌ JIS ID not found with exact match.</p>";
                    
                    // Try LIKE search
                    $stmt = $db->prepare("SELECT * FROM registrations WHERE jis_id LIKE ?");
                    $stmt->execute(["%$test_jis_id%"]);
                    $result = $stmt->fetch(PDO::FETCH_ASSOC);
                    
                    if ($result) {
                        echo "<p style='color:green'>✅ JIS ID found with LIKE search!</p>";
                        echo "<pre>" . print_r($result, true) . "</pre>";
                    } else {
                        echo "<p style='color:red'>❌ JIS ID not found with LIKE search either.</p>";
                    }
                }
            }
        } else {
            echo "<p>No records found in registrations table.</p>";
        }
    } else {
        echo "<p style='color:red'>❌ registrations table not found!</p>";
    }
} catch (PDOException $e) {
    echo "<p style='color:red'>Database error: " . htmlspecialchars($e->getMessage()) . "</p>";
}
?>
