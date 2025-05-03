<?php
// This file is intended for debugging database connection issues
// Use with caution and disable/remove in production once issues are resolved

session_start();

// Only allow access to admin users
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    http_response_code(403);
    echo "Access Denied";
    exit;
}

// Get server information
$server_info = array(
    'PHP Version' => phpversion(),
    'Server Software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
    'Document Root' => $_SERVER['DOCUMENT_ROOT'],
    'Script Filename' => $_SERVER['SCRIPT_FILENAME'],
    'Environment' => getenv('APP_ENV') ?: 'Not Set'
);

// Additional Hostinger specific information
$hostinger_info = array(
    'Provider' => 'Hostinger',
    'PHP Location' => PHP_BINARY,
    'PHP Mode' => (php_sapi_name() == 'cgi-fcgi' ? 'FastCGI' : php_sapi_name()),
    'MySQLi Available' => extension_loaded('mysqli') ? 'Yes' : 'No',
    'PDO Available' => extension_loaded('pdo_mysql') ? 'Yes' : 'No'
);

// Try loading config
$possible_paths = [
    __DIR__ . '/../includes/db_config.php', 
    $_SERVER['DOCUMENT_ROOT'] . '/includes/db_config.php',
    dirname($_SERVER['DOCUMENT_ROOT']) . '/includes/db_config.php',
    realpath(__DIR__ . '/../includes/db_config.php'),
    dirname(__DIR__) . '/includes/db_config.php',
    // Hostinger specific paths
    '/home/u901957751/domains/majistic.org/public_html/includes/db_config.php',
    '/home/u901957751/public_html/includes/db_config.php'
];

$db_loaded = false;
$found_path = '';
$tried_paths = [];

foreach ($possible_paths as $path) {
    $tried_paths[] = $path;
    if (file_exists($path)) {
        $found_path = $path;
        // Don't include the file yet, just note it exists
        $db_loaded = true;
        break;
    }
}

// Add check for the check-in specific config
$checkin_config_path = __DIR__ . '/db_config_checkin.php';
$checkin_config_exists = file_exists($checkin_config_path);

// Function to test database connection
function test_database_connection($host, $user, $pass, $db) {
    $result = array('success' => false, 'message' => '');
    
    // Test MySQLi connection
    $mysqli = @new mysqli($host, $user, $pass, $db);
    if (!$mysqli->connect_error) {
        $result['success'] = true;
        $result['message'] = "Connection successful using MySQLi";
        $result['server_info'] = $mysqli->server_info;
        $mysqli->close();
        return $result;
    } else {
        $mysqli_error = $mysqli->connect_error;
    }
    
    // If MySQLi failed, try PDO
    try {
        $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
        $result['success'] = true;
        $result['message'] = "Connection successful using PDO";
        $result['server_info'] = $pdo->getAttribute(PDO::ATTR_SERVER_VERSION);
        return $result;
    } catch (PDOException $e) {
        $result['message'] = "Both connection methods failed.\n";
        $result['message'] .= "MySQLi Error: " . $mysqli_error . "\n";
        $result['message'] .= "PDO Error: " . $e->getMessage();
        return $result;
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Connection Debug</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 900px;
            margin: 20px auto;
            padding: 20px;
        }
        h1 { 
            color: #2c3e50;
            border-bottom: 2px solid #3498db;
            padding-bottom: 10px;
        }
        h2 {
            color: #2980b9;
            margin-top: 30px;
        }
        pre {
            background-color: #f5f5f5;
            border: 1px solid #ddd;
            padding: 15px;
            border-radius: 5px;
            overflow-x: auto;
        }
        .success {
            color: #27ae60;
            font-weight: bold;
        }
        .error {
            color: #c0392b;
            font-weight: bold;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        th, td {
            padding: 12px 15px;
            border: 1px solid #ddd;
            text-align: left;
        }
        th {
            background-color: #f8f9fa;
        }
        .test-form {
            margin-top: 30px;
            padding: 20px;
            background-color: #f8f9fa;
            border-radius: 5px;
        }
        .test-form input, .test-form button {
            padding: 8px 12px;
            margin: 5px 0;
        }
        .test-form button {
            background-color: #3498db;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <h1>Database Connection Debug</h1>
    
    <h2>Server Information</h2>
    <table>
        <?php foreach($server_info as $key => $value): ?>
        <tr>
            <th><?php echo htmlspecialchars($key); ?></th>
            <td><pre><?php echo htmlspecialchars($value); ?></pre></td>
        </tr>
        <?php endforeach; ?>
    </table>
    
    <h2>Hostinger Specific Information</h2>
    <table>
        <?php foreach($hostinger_info as $key => $value): ?>
        <tr>
            <th><?php echo htmlspecialchars($key); ?></th>
            <td><pre><?php echo htmlspecialchars($value); ?></pre></td>
        </tr>
        <?php endforeach; ?>
    </table>
    
    <h2>Database Configuration File</h2>
    <table>
        <tr>
            <th>Configuration Found</th>
            <td><?php echo $db_loaded ? '<span class="success">Yes</span>' : '<span class="error">No</span>'; ?></td>
        </tr>
        <?php if($db_loaded): ?>
        <tr>
            <th>Found At Path</th>
            <td><pre><?php echo htmlspecialchars($found_path); ?></pre></td>
        </tr>
        <?php endif; ?>
        <tr>
            <th>Paths Tried</th>
            <td>
                <ul>
                <?php foreach($tried_paths as $path): ?>
                    <li>
                        <?php 
                        echo htmlspecialchars($path); 
                        if($path === $found_path) echo ' <span class="success">(FOUND)</span>';
                        ?>
                    </li>
                <?php endforeach; ?>
                </ul>
            </td>
        </tr>
    </table>
    
    <?php
    // Include the file now to get configuration
    if($db_loaded) {
        include_once $found_path;
    }
    ?>
    
    <h2>Connection Test</h2>
    <?php if(isset($db_host) && isset($db_name) && isset($db_user) && isset($db_pass)): ?>
        <p>Testing connection with values from config file:</p>
        <?php
        $test_result = test_database_connection($db_host, $db_user, $db_pass, $db_name);
        if($test_result['success']):
        ?>
            <p class="success"><?php echo htmlspecialchars($test_result['message']); ?></p>
            <p>MySQL Server Info: <?php echo htmlspecialchars($test_result['server_info']); ?></p>
        <?php else: ?>
            <p class="error">Connection Failed</p>
            <pre><?php echo htmlspecialchars($test_result['message']); ?></pre>
        <?php endif; ?>
    <?php else: ?>
        <p class="error">Database configuration variables not found in loaded config file.</p>
    <?php endif; ?>
    
    <h2>Check-in Specific Configuration</h2>
    <table>
        <tr>
            <th>Check-in Config Found</th>
            <td><?php echo $checkin_config_exists ? '<span class="success">Yes</span>' : '<span class="error">No</span>'; ?></td>
        </tr>
        <tr>
            <th>Path</th>
            <td><?php echo htmlspecialchars($checkin_config_path); ?></td>
        </tr>
    </table>

    <?php if($checkin_config_exists): ?>
    <p>Testing connection with Check-in specific config:</p>
    <?php
        // Include the file to test
        include_once $checkin_config_path;
        
        if(isset($conn) && !$conn->connect_error):
    ?>
        <p class="success">Connection successful using Check-in specific config</p>
        <p>MySQL Server Info: <?php echo htmlspecialchars($conn->server_info); ?></p>
    <?php else: ?>
        <p class="error">Connection Failed with Check-in specific config</p>
        <pre><?php echo isset($conn) ? htmlspecialchars($conn->connect_error) : 'Connection object not created'; ?></pre>
    <?php 
        endif;
    endif;
    ?>

    <div class="test-form">
        <h3>Test With Custom Values</h3>
        <form method="post">
            <div>
                <label for="host">Host:</label><br>
                <input type="text" id="host" name="host" value="<?php echo isset($db_host) ? htmlspecialchars($db_host) : 'localhost'; ?>">
            </div>
            <div>
                <label for="user">Username:</label><br>
                <input type="text" id="user" name="user" value="<?php echo isset($db_user) ? htmlspecialchars($db_user) : ''; ?>">
            </div>
            <div>
                <label for="pass">Password:</label><br>
                <input type="password" id="pass" name="pass" value="<?php echo isset($db_pass) ? htmlspecialchars($db_pass) : ''; ?>">
            </div>
            <div>
                <label for="dbname">Database Name:</label><br>
                <input type="text" id="dbname" name="dbname" value="<?php echo isset($db_name) ? htmlspecialchars($db_name) : ''; ?>">
            </div>
            <button type="submit" name="test_connection">Test Connection</button>
        </form>
        
        <?php
        if(isset($_POST['test_connection'])) {
            $host = $_POST['host'];
            $user = $_POST['user'];
            $pass = $_POST['pass'];
            $dbname = $_POST['dbname'];
            
            $custom_test = test_database_connection($host, $user, $pass, $dbname);
            echo '<h4>Custom Test Results:</h4>';
            if($custom_test['success']) {
                echo '<p class="success">' . htmlspecialchars($custom_test['message']) . '</p>';
                echo '<p>MySQL Server Info: ' . htmlspecialchars($custom_test['server_info']) . '</p>';
            } else {
                echo '<p class="error">Connection Failed</p>';
                echo '<pre>' . htmlspecialchars($custom_test['message']) . '</pre>';
            }
        }
        ?>
    </div>
</body>
</html>
