<?php
// This file allows authorized administrators to update payment configuration settings

// Start session for authentication
session_start();

// Check if user is authenticated as admin
$is_admin = isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true;

// If not authenticated, redirect to login
if (!$is_admin) {
    header('Location: admin_login.php?redirect=manage_payment_config.php');
    exit;
}

include '../../includes/db_config.php';

// Default values
$config = [
    'razorpay_key_id' => 'rzp_test_5y6HDO2HsDx5lK',
    'razorpay_key_secret' => 'sOQvnTPi8LdXe8JYYv0eGF2P',
    'student_amount' => 500,
    'alumni_amount' => 1000,
    'payment_enabled' => true
];

// Check if payment config exists in database
$configTable = 'payment_config';
$checkTable = $conn->query("SHOW TABLES LIKE '$configTable'");

// Create table if it doesn't exist
if ($checkTable->num_rows == 0) {
    $conn->query("
    CREATE TABLE $configTable (
        id INT PRIMARY KEY AUTO_INCREMENT,
        config_key VARCHAR(50) NOT NULL UNIQUE,
        config_value TEXT NOT NULL,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )");
    
    // Insert default values
    foreach ($config as $key => $value) {
        $stmt = $conn->prepare("INSERT INTO $configTable (config_key, config_value) VALUES (?, ?)");
        if (is_bool($value)) {
            $value = $value ? '1' : '0';
        }
        $stmt->bind_param("ss", $key, $value);
        $stmt->execute();
    }
} else {
    // Load existing values
    $result = $conn->query("SELECT config_key, config_value FROM $configTable");
    while ($row = $result->fetch_assoc()) {
        $config[$row['config_key']] = $row['config_value'];
    }
}

// Process form submission
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_config'])) {
    $newConfig = [
        'razorpay_key_id' => $_POST['razorpay_key_id'],
        'razorpay_key_secret' => $_POST['razorpay_key_secret'],
        'student_amount' => intval($_POST['student_amount']),
        'alumni_amount' => intval($_POST['alumni_amount']),
        'payment_enabled' => isset($_POST['payment_enabled']) ? true : false
    ];
    
    // Update each value
    foreach ($newConfig as $key => $value) {
        if (is_bool($value)) {
            $value = $value ? '1' : '0';
        }
        $stmt = $conn->prepare("UPDATE $configTable SET config_value = ? WHERE config_key = ?");
        $stmt->bind_param("ss", $value, $key);
        $stmt->execute();
    }
    
    $message = 'Payment configuration updated successfully.';
    
    // Reload values
    $config = $newConfig;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Payment Configuration</title>
    <link rel="stylesheet" href="../../css/admin.css">
    <style>
        .config-form {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .config-section {
            margin-bottom: 20px;
            padding-bottom: 20px;
            border-bottom: 1px solid #eee;
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
        }
        .form-control {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .checkbox-group {
            display: flex;
            align-items: center;
        }
        .checkbox-group input {
            margin-right: 10px;
        }
        .btn-primary {
            background: #6366f1;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            cursor: pointer;
        }
        .alert {
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <h1>Manage Payment Configuration</h1>
        
        <?php if (!empty($message)): ?>
            <div class="alert alert-success">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>
        
        <form class="config-form" method="post">
            <div class="config-section">
                <h2>Razorpay API Configuration</h2>
                <div class="form-group">
                    <label for="razorpay_key_id">API Key ID</label>
                    <input type="text" id="razorpay_key_id" name="razorpay_key_id" class="form-control" 
                           value="<?php echo htmlspecialchars($config['razorpay_key_id']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="razorpay_key_secret">API Key Secret</label>
                    <input type="password" id="razorpay_key_secret" name="razorpay_key_secret" class="form-control" 
                           value="<?php echo htmlspecialchars($config['razorpay_key_secret']); ?>" required>
                </div>
            </div>
            
            <div class="config-section">
                <h2>Payment Amounts</h2>
                <div class="form-group">
                    <label for="student_amount">Student Registration Amount (₹)</label>
                    <input type="number" id="student_amount" name="student_amount" class="form-control" 
                           value="<?php echo htmlspecialchars($config['student_amount']); ?>" required min="0">
                </div>
                
                <div class="form-group">
                    <label for="alumni_amount">Alumni Registration Amount (₹)</label>
                    <input type="number" id="alumni_amount" name="alumni_amount" class="form-control" 
                           value="<?php echo htmlspecialchars($config['alumni_amount']); ?>" required min="0">
                </div>
            </div>
            
            <div class="config-section">
                <h2>Payment Settings</h2>
                <div class="checkbox-group">
                    <input type="checkbox" id="payment_enabled" name="payment_enabled" 
                           <?php echo $config['payment_enabled'] == '1' ? 'checked' : ''; ?>>
                    <label for="payment_enabled">Enable Payment Processing</label>
                </div>
                <p class="text-muted">Uncheck this box to temporarily disable all payment processing.</p>
            </div>
            
            <div class="form-actions">
                <button type="submit" name="update_config" class="btn-primary">
                    Save Configuration
                </button>
            </div>
        </form>
        
        <p><a href="index.php">Back to Dashboard</a></p>
    </div>
</body>
</html>
