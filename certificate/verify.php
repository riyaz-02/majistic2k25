<?php
require_once __DIR__ . '/../includes/db_config.php';
require_once 'config.php';

// Initialize variables
$error = '';
$success = false;
$certificate = null;

// Process verification request
if (isset($_GET['id']) && !empty($_GET['id'])) {
    $certificate_id = trim($_GET['id']);
    $jis_id = isset($_GET['jis']) ? trim($_GET['jis']) : '';
    
    try {
        // Verify database connection
        if (!$db) {
            throw new Exception("Database connection not established");
        }
        
        // Query for certificate record
        $sql = "SELECT * FROM certificate_records WHERE certificate_id = :certificate_id";
        if (!empty($jis_id)) {
            $sql .= " AND jis_id = :jis_id";
        }
        
        $stmt = $db->prepare($sql);
        $params = [':certificate_id' => $certificate_id];
        
        if (!empty($jis_id)) {
            $params[':jis_id'] = $jis_id;
        }
        
        $stmt->execute($params);
        $certificate = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($certificate) {
            $success = true;
            
            // Update verification count
            $updateStmt = $db->prepare("
                UPDATE certificate_records 
                SET verification_count = verification_count + 1 
                WHERE certificate_id = :certificate_id
            ");
            $updateStmt->execute([':certificate_id' => $certificate_id]);
        } else {
            $error = "Certificate not found or invalid";
        }
    } catch (Exception $e) {
        $error = "Verification error: " . $e->getMessage();
        error_log("Certificate verification error: " . $e->getMessage());
    }
} else {
    $error = "Certificate ID is required";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MaJIStic 2K25 - Certificate Verification</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">
    <style>
        /* CSS styles similar to the certificate generation page */
        :root {
            --primary: #2c3e50;
            --secondary: rgb(35, 35, 36);
            --accent: #e74c3c;
            --success: #2ecc71;
            --light: #ecf0f1;
            --white: #ffffff;
            --shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            --transition: all 0.3s ease;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }
        
        body {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            padding: 20px;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            width: 100%;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
        }
        
        .verification-container {
            background-color: var(--white);
            border-radius: 20px;
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.2);
            overflow: hidden;
            width: 100%;
            max-width: 600px;
            padding: 0;
            margin-top: 50px;
        }
        
        .verification-header {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            padding: 30px 20px;
            text-align: center;
            color: var(--white);
        }
        
        .verification-header h1 {
            font-size: 1.8rem;
            margin-bottom: 10px;
        }
        
        .verification-content {
            padding: 30px;
        }
        
        .logo {
            width: 180px;
            height: auto;
            margin-bottom: 10px;
        }
        
        .verification-status {
            padding: 20px;
            text-align: center;
            margin-bottom: 20px;
            border-radius: 10px;
        }
        
        .verification-status.success {
            background-color: #e8f5e9;
            color: #2e7d32;
        }
        
        .verification-status.error {
            background-color: #ffebee;
            color: var(--accent);
        }
        
        .verification-details {
            margin-top: 20px;
        }
        
        .verification-details table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .verification-details table tr td {
            padding: 10px;
            border-bottom: 1px solid #eee;
        }
        
        .verification-details table tr td:first-child {
            font-weight: 500;
            width: 40%;
        }
        
        .verification-form {
            margin-top: 20px;
        }
        
        .form-group {
            margin-bottom: 15px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
        }
        
        .form-group input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        
        .btn {
            display: inline-block;
            padding: 10px 20px;
            background: var(--primary);
            color: var(--white);
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: 500;
            transition: var(--transition);
            text-decoration: none;
        }
        
        .btn:hover {
            background: var(--secondary);
            transform: translateY(-2px);
        }
        
        .footer {
            margin-top: 20px;
            text-align: center;
            font-size: 14px;
            color: rgba(255, 255, 255, 0.7);
            padding: 20px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="verification-container">
            <div class="verification-header">
                <img src="../images/majisticlogo.png" alt="MaJIStic Logo" class="logo">
                <h1>Certificate Verification</h1>
                <p>Verify the authenticity of MaJIStic 2K25 certificates</p>
            </div>
            
            <div class="verification-content">
                <?php if ($success): ?>
                    <div class="verification-status success">
                        <i class="fas fa-check-circle" style="font-size: 48px; margin-bottom: 10px;"></i>
                        <h2>Certificate Verified</h2>
                        <p>This is an authentic certificate issued by MaJIStic 2K25</p>
                    </div>
                    
                    <div class="verification-details">
                        <h3>Certificate Details</h3>
                        <table>
                            <tr>
                                <td>Certificate ID</td>
                                <td><?php echo htmlspecialchars($certificate['certificate_id']); ?></td>
                            </tr>
                            <tr>
                                <td>Name</td>
                                <td><?php echo htmlspecialchars($certificate['student_name']); ?></td>
                            </tr>
                            <tr>
                                <td>Role</td>
                                <td><?php echo htmlspecialchars($certificate['role']); ?></td>
                            </tr>
                            <tr>
                                <td>JIS ID</td>
                                <td><?php echo htmlspecialchars($certificate['jis_id']); ?></td>
                            </tr>
                            <tr>
                                <td>Issued On</td>
                                <td><?php echo date('F j, Y', strtotime($certificate['generated_at'])); ?></td>
                            </tr>
                            <tr>
                                <td>Verification Count</td>
                                <td><?php echo $certificate['verification_count']; ?></td>
                            </tr>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="verification-status error">
                        <i class="fas fa-times-circle" style="font-size: 48px; margin-bottom: 10px;"></i>
                        <h2>Verification Failed</h2>
                        <p><?php echo $error ?: 'Unable to verify this certificate'; ?></p>
                    </div>
                    
                    <div class="verification-form">
                        <h3>Verify Another Certificate</h3>
                        <form action="verify.php" method="GET">
                            <div class="form-group">
                                <label for="id">Certificate ID</label>
                                <input type="text" name="id" id="id" required placeholder="Enter certificate ID">
                            </div>
                            <div class="form-group">
                                <label for="jis">JIS ID (Optional)</label>
                                <input type="text" name="jis" id="jis" placeholder="Enter JIS ID for additional verification">
                            </div>
                            <button type="submit" class="btn">Verify Certificate</button>
                        </form>
                    </div>
                <?php endif; ?>
                
                <div style="text-align: center; margin-top: 30px;">
                    <a href="index.php" class="btn">Back to Certificate Generator</a>
                </div>
            </div>
        </div>
    </div>
    
    <div class="footer">
        &copy; <?php echo date('Y'); ?> maJIStic - All Rights Reserved
    </div>
    
    <!-- Add debug information for troubleshooting 500 errors -->
    <?php if(isset($_GET['debug']) && $_GET['debug'] == '1'): ?>
    <div style="margin-top: 20px; border-top: 1px solid #ddd; padding-top: 20px; color: #666; font-size: 12px;">
        <h4>Debug Information</h4>
        <p>PHP Version: <?php echo phpversion(); ?></p>
        <p>Date: <?php echo date('Y-m-d H:i:s'); ?></p>
        <p>Server: <?php echo $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown'; ?></p>
        <p>Certificate Parameters: ID=<?php echo isset($_GET['id']) ? htmlspecialchars($_GET['id']) : 'Not provided'; ?>, 
           JIS=<?php echo isset($_GET['jis']) ? htmlspecialchars($_GET['jis']) : 'Not provided'; ?></p>
    </div>
    <?php endif; ?>
</body>
</html>
