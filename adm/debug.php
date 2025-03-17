<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit();
}

include '../includes/db_config.php';

// Function to check if table exists
function tableExists($conn, $tableName) {
    $result = $conn->query("SHOW TABLES LIKE '$tableName'");
    return $result->num_rows > 0;
}

// Function to get table structure
function getTableStructure($conn, $tableName) {
    if (!tableExists($conn, $tableName)) {
        return "Table does not exist";
    }
    
    $result = $conn->query("DESCRIBE $tableName");
    $structure = [];
    while ($row = $result->fetch_assoc()) {
        $structure[] = $row;
    }
    return $structure;
}

// Function to count records in a table
function countRecords($conn, $tableName) {
    if (!tableExists($conn, $tableName)) {
        return "Table does not exist";
    }
    
    $result = $conn->query("SELECT COUNT(*) as count FROM $tableName");
    return $result->fetch_assoc()['count'];
}

// Get server information
$server_info = [
    "PHP Version" => PHP_VERSION,
    "MySQL Version" => $conn->server_info,
    "MySQL Connection Status" => $conn->stat,
];

// Get database tables info
$tables = [
    "registrations",
    "registrations_outhouse",
    "alumni_registrations",
    "payment_attempts"
];

$tables_info = [];
foreach ($tables as $table) {
    $tables_info[$table] = [
        "exists" => tableExists($conn, $table),
        "record_count" => countRecords($conn, $table),
        "structure" => getTableStructure($conn, $table)
    ];
}

// Check for recent errors in PHP error log
$error_log_path = ini_get('error_log');
$recent_errors = '';
if (file_exists($error_log_path)) {
    $recent_errors = shell_exec("tail -n 20 " . escapeshellarg($error_log_path));
}

// Generate debug output
$debug_output = [
    "server_info" => $server_info,
    "tables_info" => $tables_info,
    "recent_errors" => $recent_errors,
];

// Output as JSON if requested
if (isset($_GET['json'])) {
    header('Content-Type: application/json');
    echo json_encode($debug_output, JSON_PRETTY_PRINT);
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Database Debug</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">
    <link rel="stylesheet" href="styles.css">
    <style>
        .debug-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        .debug-section {
            background-color: #fff;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .debug-section h2 {
            margin-top: 0;
            color: #2c3e50;
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
        }
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 10px;
        }
        .info-item {
            padding: 10px;
            background: #f8f9fa;
            border-radius: 4px;
        }
        .info-item strong {
            display: block;
            margin-bottom: 5px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            padding: 8px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #f2f2f2;
        }
        .table-exists {
            color: green;
            font-weight: bold;
        }
        .table-missing {
            color: red;
            font-weight: bold;
        }
        pre {
            background: #f4f4f4;
            padding: 10px;
            border-radius: 4px;
            overflow-x: auto;
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="navbar-title">
            <img src="../assets/images/logo.png" alt="MaJIStic Logo" class="navbar-logo">
            <h1>Database Debug Information</h1>
        </div>
        <div>
            <a href="madm.php" class="logout-btn" style="background-color: #2ecc71; margin-right: 10px;">
                <i class="fas fa-arrow-left"></i>
                Back to Admin
            </a>
            <a href="logout.php" class="logout-btn">
                <i class="fas fa-sign-out-alt"></i>
                Logout
            </a>
        </div>
    </nav>

    <div class="debug-container">
        <div class="debug-section">
            <h2><i class="fas fa-server"></i> Server Information</h2>
            <div class="info-grid">
                <?php foreach ($server_info as $key => $value): ?>
                <div class="info-item">
                    <strong><?php echo htmlspecialchars($key); ?>:</strong>
                    <?php echo htmlspecialchars($value); ?>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="debug-section">
            <h2><i class="fas fa-database"></i> Database Tables</h2>
            <table>
                <thead>
                    <tr>
                        <th>Table Name</th>
                        <th>Status</th>
                        <th>Record Count</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($tables_info as $table => $info): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($table); ?></td>
                        <td>
                            <?php if ($info['exists']): ?>
                                <span class="table-exists">Exists</span>
                            <?php else: ?>
                                <span class="table-missing">Missing</span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo $info['exists'] ? htmlspecialchars($info['record_count']) : 'N/A'; ?></td>
                        <td>
                            <?php if ($info['exists']): ?>
                                <button class="btn-view" onclick="toggleTableStructure('<?php echo $table; ?>')">
                                    <i class="fas fa-table"></i> View Structure
                                </button>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr id="structure-<?php echo $table; ?>" style="display: none;">
                        <td colspan="4">
                            <?php if ($info['exists']): ?>
                                <table>
                                    <thead>
                                        <tr>
                                            <th>Field</th>
                                            <th>Type</th>
                                            <th>Null</th>
                                            <th>Key</th>
                                            <th>Default</th>
                                            <th>Extra</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($info['structure'] as $field): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($field['Field']); ?></td>
                                            <td><?php echo htmlspecialchars($field['Type']); ?></td>
                                            <td><?php echo htmlspecialchars($field['Null']); ?></td>
                                            <td><?php echo htmlspecialchars($field['Key']); ?></td>
                                            <td><?php echo $field['Default'] !== NULL ? htmlspecialchars($field['Default']) : 'NULL'; ?></td>
                                            <td><?php echo htmlspecialchars($field['Extra']); ?></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <?php if ($recent_errors): ?>
        <div class="debug-section">
            <h2><i class="fas fa-exclamation-triangle"></i> Recent Errors</h2>
            <pre><?php echo htmlspecialchars($recent_errors); ?></pre>
        </div>
        <?php endif; ?>
    </div>

    <script>
        function toggleTableStructure(tableName) {
            const row = document.getElementById(`structure-${tableName}`);
            if (row.style.display === 'none') {
                row.style.display = 'table-row';
            } else {
                row.style.display = 'none';
            }
        }
    </script>
</body>
</html>
