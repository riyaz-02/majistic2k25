<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit();
}

require_once __DIR__ . '/../../includes/db_config.php';

// Function to get collection info
function getCollectionInfo($collection) {
    return [
        'count' => $collection->countDocuments(),
        'sample' => iterator_to_array($collection->find([], ['limit' => 1])),
        'indexes' => iterator_to_array($collection->listIndexes())
    ];
}

// Get MongoDB database and collections info
$db_info = [
    'registrations' => getCollectionInfo($registrations),
    'alumni_registrations' => getCollectionInfo($alumni_registrations)
];

// Get server information
$server_info = [
    "PHP Version" => PHP_VERSION,
    "MongoDB PHP Driver Version" => phpversion('mongodb'),
    "Server Time" => date('Y-m-d H:i:s')
];

// Output as JSON if requested
if (isset($_GET['json'])) {
    header('Content-Type: application/json');
    echo json_encode([
        'server_info' => $server_info,
        'database_info' => $db_info
    ], JSON_PRETTY_PRINT);
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
            <img src="../images/majisticlogo.png" alt="MaJIStic Logo" class="navbar-logo">
            <h1>MongoDB Debug Information</h1>
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
            <h2><i class="fas fa-database"></i> MongoDB Collections</h2>
            <?php foreach ($db_info as $collection_name => $info): ?>
            <div class="collection-info">
                <h3><?php echo htmlspecialchars($collection_name); ?></h3>
                <p>Document Count: <?php echo $info['count']; ?></p>
                
                <?php if (!empty($info['sample'])): ?>
                <div class="sample-data">
                    <h4>Sample Document Structure:</h4>
                    <pre><?php echo json_encode($info['sample'], JSON_PRETTY_PRINT); ?></pre>
                </div>
                <?php endif; ?>

                <div class="indexes">
                    <h4>Indexes:</h4>
                    <pre><?php echo json_encode($info['indexes'], JSON_PRETTY_PRINT); ?></pre>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <script>
        // ... existing JavaScript code ...
    </script>
</body>
</html>
