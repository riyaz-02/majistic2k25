<?php
// Start session for secure error handling
session_start();

require_once __DIR__ . '/../includes/db_config.php';
require_once __DIR__ . '/../certificate/certificate_functions.php';

// Initialize variables
$error = '';
$success = '';
$certificateData = null;
$token = '';
$debugLogs = [];

// Debug function for tracking verification steps
function debugLog($message, $data = null, $level = 'info') {
    global $debugLogs;
    $debugLogs[] = [
        'timestamp' => microtime(true),
        'level' => $level,
        'message' => $message,
        'data' => $data
    ];
    
    // Also log to PHP error log for server-side tracking
    $logMessage = "[Certificate Verification] $message";
    if ($data !== null) {
        $logMessage .= ": " . json_encode($data);
    }
    error_log($logMessage);
}

// Start verification process logs
debugLog('Verification page loaded', ['session_id' => session_id(), 'time' => date('Y-m-d H:i:s')]);
debugLog('Client info', [
    'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
    'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
    'referer' => $_SERVER['HTTP_REFERER'] ?? 'direct'
]);

// Check if a token is provided
if (isset($_GET['token']) && !empty($_GET['token'])) {
    $token = trim($_GET['token']);
    debugLog('Token received', ['token' => substr($token, 0, 10) . '...']);
    
    try {
        // Decrypt the verification token
        debugLog('Attempting to decrypt token');
        $tokenData = decryptVerificationToken($token);
        
        if ($tokenData === false) {
            $error = "Invalid verification code. This certificate cannot be verified.";
            debugLog('Token decryption failed', null, 'error');
        } else {
            debugLog('Token decrypted successfully', ['jis_id' => $tokenData['jis'], 'timestamp' => date('Y-m-d H:i:s', $tokenData['time'])]);
            
            // Extract JIS ID from token data
            $jis_id = $tokenData['jis'];
            
            // Check database for certificate record matching this token
            debugLog('Querying database for certificate record');
            $stmt = $db->prepare("SELECT * FROM certificate_records WHERE token = :token LIMIT 1");
            $stmt->execute([':token' => $token]);
            $certificateData = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($certificateData) {
                debugLog('Certificate record found', [
                    'id' => $certificateData['id'],
                    'student_name' => $certificateData['student_name'],
                    'role' => $certificateData['role'],
                    'issue_date' => $certificateData['generated_at']
                ]);
                
                // No longer updating verification count in DB as requested
                $success = "Certificate verified successfully!";
                
                // Get student data for additional verification
                debugLog('Fetching student registration data');
                $studentStmt = $db->prepare("SELECT * FROM registrations WHERE jis_id = :jis_id LIMIT 1");
                $studentStmt->execute([':jis_id' => $jis_id]);
                $studentData = $studentStmt->fetch(PDO::FETCH_ASSOC);
                
                if ($studentData) {
                    debugLog('Student registration data found', ['jis_id' => $jis_id]);
                } else {
                    debugLog('No student registration found for JIS ID', ['jis_id' => $jis_id], 'warning');
                }
            } else {
                debugLog('No certificate record found with token, trying JIS ID fallback');
                // Try to find by JIS ID instead of token as a fallback
                $stmt = $db->prepare("SELECT * FROM certificate_records WHERE jis_id = :jis_id ORDER BY generated_at DESC LIMIT 1");
                $stmt->execute([':jis_id' => $jis_id]);
                $certificateData = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($certificateData) {
                    debugLog('Certificate record found via JIS ID fallback', [
                        'id' => $certificateData['id'],
                        'student_name' => $certificateData['student_name']
                    ]);
                    
                    $success = "Certificate verified successfully!";
                    
                    // Get student data
                    debugLog('Fetching student registration data');
                    $studentStmt = $db->prepare("SELECT * FROM registrations WHERE jis_id = :jis_id LIMIT 1");
                    $studentStmt->execute([':jis_id' => $jis_id]);
                    $studentData = $studentStmt->fetch(PDO::FETCH_ASSOC);
                    
                    if ($studentData) {
                        debugLog('Student registration data found', ['jis_id' => $jis_id]);
                    } else {
                        debugLog('No student registration found for JIS ID', ['jis_id' => $jis_id], 'warning');
                    }
                } else {
                    $error = "Certificate record not found. This may not be an authentic certificate.";
                    debugLog('No certificate record found', ['jis_id' => $jis_id], 'error');
                }
            }
        }
    } catch (Exception $e) {
        $error = "Verification error. Please try again later.";
        debugLog('Exception during verification', [
            'message' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ], 'error');
    }
} else {
    $error = "No verification code provided. Please scan the QR code on the certificate.";
    debugLog('No token provided in request', $_GET, 'warning');
}

debugLog('Verification process completed', [
    'success' => !empty($success),
    'error' => $error,
    'has_certificate_data' => !empty($certificateData)
]);

// Helper function to format date for display
function formatDate($dateString) {
    $date = new DateTime($dateString);
    return $date->format('F j, Y');
}

// Check if we're running in debug mode
$isDebugMode = isset($_GET['debug']) && $_GET['debug'] === '1';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Certificate Verification | maJIStic 2k25</title>
    <!-- Add favicon/icon -->
    <link rel="icon" href="../images/majisticlogo.png" type="image/x-icon">
    <link rel="shortcut icon" href="../images/majisticlogo.png" type="image/x-icon">
    <!-- Alternative icon source using the logo image (as backup) -->
    <link rel="apple-touch-icon" href="../images/majisticlogo.png">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">
    <?php include '../includes/links.php'; ?>
    <link rel="stylesheet" href="../style.css">
    <style>
        body {
            background-image: url('../images/pageback.png');
            background-size: cover;
            background-attachment: fixed;
            min-height: 100vh;
            font-family: 'Poppins', sans-serif;
            color: #ffffff;
        }

        .verification-container {
            max-width: 900px;
            margin: 50px auto;
            padding: 20px;
        }

        .verification-card {
            background-color: rgba(255, 255, 255, 0.08);
            border-radius: 15px;
            overflow: hidden;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
        }

        .card-header {
            background: linear-gradient(135deg, #6a11cb, #2575fc);
            color: white;
            padding: 25px 20px;
            text-align: center;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .card-body {
            padding: 30px;
        }

        .verification-result {
            text-align: center;
            padding: 20px;
            margin-bottom: 20px;
            border-radius: 10px;
        }

        .verification-result.success {
            background-color: rgba(46, 204, 113, 0.15);
            border: 1px solid rgba(46, 204, 113, 0.5);
            position: relative;
            overflow: hidden;
        }
        
        .verification-result.success::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: radial-gradient(circle at top right, rgba(46, 204, 113, 0.3), transparent 70%);
            z-index: 0;
        }
        
        .verification-result.success .verification-icon,
        .verification-result.success .verification-title,
        .verification-result.success .verification-message {
            position: relative;
            z-index: 1;
        }
        
        .verification-result.success .verification-icon {
            color: #2ecc71;
            text-shadow: 0 0 15px rgba(46, 204, 113, 0.5);
            animation: pulse-green 2s infinite;
        }
        
        @keyframes pulse-green {
            0% {
                transform: scale(1);
                text-shadow: 0 0 15px rgba(46, 204, 113, 0.5);
            }
            50% {
                transform: scale(1.1);
                text-shadow: 0 0 20px rgba(46, 204, 113, 0.8);
            }
            100% {
                transform: scale(1);
                text-shadow: 0 0 15px rgba(46, 204, 113, 0.5);
            }
        }
        
        .verification-success-pattern {
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 5px;
            background: linear-gradient(90deg, #2ecc71, #27ae60, #2ecc71);
            z-index: 0;
        }

        .verification-result.error {
            background-color: rgba(231, 76, 60, 0.2);
            border: 1px solid rgba(231, 76, 60, 0.5);
        }

        .verification-details {
            background-color: rgba(0, 0, 0, 0.2);
            border-radius: 10px;
            padding: 20px;
        }

        .detail-row {
            display: flex;
            margin-bottom: 15px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            padding-bottom: 15px;
        }

        .detail-row:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }

        .detail-label {
            flex: 0 0 40%;
            color: #adb5bd;
            font-size: 0.95rem;
        }

        .detail-value {
            flex: 0 0 60%;
            color: #ffffff;
            font-weight: 500;
        }

        .certificate-status {
            display: inline-block;
            padding: 5px 12px;
            border-radius: 50px;
            font-weight: 600;
            font-size: 0.9rem;
        }

        .status-valid {
            background-color: rgba(46, 204, 113, 0.2);
            color: #2ecc71;
            border: 1px solid #2ecc71;
        }

        .status-invalid {
            background-color: rgba(231, 76, 60, 0.2);
            color: #e74c3c;
            border: 1px solid #e74c3c;
        }

        .verification-icon {
            font-size: 60px;
            margin-bottom: 20px;
        }

        .verification-title {
            margin-bottom: 10px;
            font-size: 1.8rem;
        }

        .verification-message {
            margin-bottom: 20px;
            font-size: 1.1rem;
        }
        
        .certificate-holder-name {
            display: block;
            font-size: 1.6rem;
            font-weight: 700;
            color: #2ecc71;
            margin: 10px 0;
            text-shadow: 0 0 10px rgba(46, 204, 113, 0.3);
            letter-spacing: 0.5px;
        }

        .info-alert {
            background-color: rgba(52, 152, 219, 0.2);
            border-left: 5px solid #3498db;
            padding: 15px;
            margin-top: 20px;
            border-radius: 5px;
        }

        .search-form {
            margin-top: 30px;
            text-align: center;
        }

        .search-form input {
            width: 100%;
            max-width: 400px;
            padding: 12px 15px;
            border-radius: 50px;
            border: 1px solid rgba(255, 255, 255, 0.2);
            background-color: rgba(0, 0, 0, 0.3);
            color: white;
            font-size: 1rem;
        }

        .search-form button {
            background: linear-gradient(135deg, #6a11cb, #2575fc);
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: 50px;
            font-weight: 600;
            margin-top: 10px;
            cursor: pointer;
            transition: all 0.3s;
        }

        .search-form button:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(106, 17, 203, 0.4);
        }

        .logo {
            width: 120px;
            margin-bottom: 10px;
        }
        
        /* For animations */
        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .verification-card {
            animation: fadeUp 0.8s ease;
        }
        
        /* For responsive design */
        @media (max-width: 768px) {
            .verification-container {
                padding: 10px;
                margin: 30px auto;
            }
            
            .detail-row {
                flex-direction: column;
            }
            
            .detail-label, .detail-value {
                flex: 0 0 100%;
            }
            
            .detail-label {
                margin-bottom: 5px;
            }
        }

        /* Security badge styles */
        .security-badge {
            position: relative;
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }

        .security-badge-icon {
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 10px;
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: linear-gradient(135deg, #11998e, #38ef7d);
            color: white;
            font-size: 24px;
        }

        .security-badge p {
            font-size: 0.9rem;
            color: rgba(255, 255, 255, 0.7);
            margin: 0;
        }

        /* Debug console styles */
        .debug-console {
            margin-top: 30px;
            background-color: rgba(0, 0, 0, 0.7);
            border-radius: 10px;
            overflow: hidden;
        }
        
        .debug-header {
            background-color: #2c3e50;
            color: white;
            padding: 8px 15px;
            font-size: 14px;
            font-family: monospace;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .debug-body {
            max-height: 300px;
            overflow-y: auto;
            padding: 10px;
        }
        
        .debug-log {
            margin-bottom: 6px;
            padding: 4px 0;
            font-family: monospace;
            font-size: 12px;
            border-bottom: 1px solid rgba(255,255,255,0.05);
        }
        
        .debug-log:last-child {
            border-bottom: none;
        }
        
        .debug-timestamp {
            color: #7f8c8d;
            display: inline-block;
            width: 70px;
        }
        
        .debug-level {
            display: inline-block;
            padding: 0px 5px;
            border-radius: 3px;
            margin-right: 5px;
            font-size: 10px;
            text-transform: uppercase;
        }
        
        .debug-level.info {
            background-color: #3498db;
            color: white;
        }
        
        .debug-level.warning {
            background-color: #f39c12;
            color: white;
        }
        
        .debug-level.error {
            background-color: #e74c3c;
            color: white;
        }
        
        .debug-message {
            color: #ecf0f1;
        }
        
        .debug-data {
            color: #95a5a6;
            font-size: 11px;
            padding-left: 79px;
            margin-top: 3px;
            word-break: break-all;
        }
    </style>
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="verification-container">
        <div class="verification-card">
            <div class="card-header">
                <img src="../images/majisticlogo.png" alt="MaJIStic Logo" class="logo">
                <h1>Certificate Verification</h1>
                <p>Verify the authenticity of maJIStic 2k25 certificates</p>
            </div>
            
            <div class="card-body">
                <?php if ($error): ?>
                <div class="verification-result error">
                    <i class="fas fa-times-circle verification-icon"></i>
                    <h2 class="verification-title">Verification Failed</h2>
                    <p class="verification-message"><?php echo $error; ?></p>
                    
                    <!-- Show form to verify a different certificate -->
                    <div class="search-form">
                        <form method="GET" action="index.php">
                            <input type="text" name="token" placeholder="Enter verification code or token" required>
                            <button type="submit"><i class="fas fa-search"></i> Verify Certificate</button>
                        </form>
                    </div>
                </div>
                
                <?php elseif ($success && $certificateData): ?>
                <div class="verification-result success">
                    <i class="fas fa-check-circle verification-icon"></i>
                    <h2 class="verification-title">Certificate Verified</h2>
                    <p class="verification-message">
                        This is an authentic certificate issued to<br>
                        <span class="certificate-holder-name"><?php echo htmlspecialchars($certificateData['student_name']); ?></span>
                        <br>by maJIStic 2k25.
                    </p>
                    <div class="verification-success-pattern"></div>
                </div>
                
                <div class="verification-details">
                    <div class="detail-row">
                        <div class="detail-label">Status</div>
                        <div class="detail-value">
                            <span class="certificate-status status-valid">
                                <i class="fas fa-shield-alt"></i> Verified
                            </span>
                        </div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label">Certificate Holder</div>
                        <div class="detail-value"><?php echo htmlspecialchars($certificateData['student_name']); ?></div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label">Event</div>
                        <div class="detail-value">maJIStic 2k25</div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label">Role/Position</div>
                        <div class="detail-value"><?php echo htmlspecialchars($certificateData['role']); ?></div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label">Issue Date</div>
                        <div class="detail-value"><?php echo formatDate($certificateData['generated_at']); ?></div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label">Verification Code</div>
                        <div class="detail-value">
                            <span style="font-family: monospace; letter-spacing: 1px;"><?php echo substr($token, 0, 16) . '...'; ?></span>
                        </div>
                    </div>
                </div>
                
                <div class="info-alert">
                    <p><i class="fas fa-info-circle"></i> This certificate has been verified in our database as authentic. If you need any additional verification, please contact maJIStic organizing committee.</p>
                </div>
                
                <div class="security-badge">
                    <div class="security-badge-icon">
                        <i class="fas fa-fingerprint"></i>
                    </div>
                    <p>This certificate is protected by secure encryption verification</p>
                    <p><small>Verified on <?php echo date('Y-m-d H:i:s'); ?> | Reference ID: <?php echo bin2hex(random_bytes(4)); ?></small></p>
                </div>
                
                <?php else: ?>
                <div class="verification-result error">
                    <i class="fas fa-search verification-icon"></i>
                    <h2 class="verification-title">Certificate Verification</h2>
                    <p class="verification-message">Please provide a verification code to check certificate authenticity.</p>
                    
                    <!-- Form to verify certificate -->
                    <div class="search-form">
                        <form method="GET" action="index.php">
                            <input type="text" name="token" placeholder="Enter verification code or token" required>
                            <button type="submit"><i class="fas fa-search"></i> Verify Certificate</button>
                        </form>
                    </div>
                </div>
                <?php endif; ?>
                
                <?php if ($isDebugMode && !empty($debugLogs)): ?>
                <!-- Debug console for administrators -->
                <div class="debug-console">
                    <div class="debug-header">
                        <span>Certificate Verification Logs</span>
                        <span><?php echo count($debugLogs); ?> entries</span>
                    </div>
                    <div class="debug-body">
                        <?php foreach ($debugLogs as $log): 
                            $time = date('H:i:s', (int)$log['timestamp']); 
                            $ms = sprintf(".%03d", ($log['timestamp'] - floor($log['timestamp'])) * 1000);
                        ?>
                            <div class="debug-log">
                                <span class="debug-timestamp"><?php echo $time . $ms; ?></span>
                                <span class="debug-level <?php echo $log['level']; ?>"><?php echo $log['level']; ?></span>
                                <span class="debug-message"><?php echo htmlspecialchars($log['message']); ?></span>
                                <?php if ($log['data'] !== null): ?>
                                    <div class="debug-data"><?php echo htmlspecialchars(json_encode($log['data'])); ?></div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <?php include '../includes/footer.php'; ?>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Add focus effect to search input
            const searchInput = document.querySelector('.search-form input');
            if (searchInput) {
                searchInput.addEventListener('focus', function() {
                    this.style.boxShadow = '0 0 15px rgba(106, 17, 203, 0.4)';
                });
                
                searchInput.addEventListener('blur', function() {
                    this.style.boxShadow = 'none';
                });
            }
            
            // Pulsing animation for success icon
            const successIcon = document.querySelector('.verification-result.success .verification-icon');
            if (successIcon) {
                setInterval(() => {
                    successIcon.style.transform = 'scale(1.1)';
                    setTimeout(() => {
                        successIcon.style.transform = 'scale(1)';
                    }, 500);
                }, 2000);
            }
            
            // Console logging for debugging
            <?php if (!empty($debugLogs)): ?>
            console.group('Certificate Verification Debug Log');
            <?php foreach ($debugLogs as $log): ?>
            console.<?php echo $log['level']; ?>('<?php echo addslashes($log['message']); ?>', <?php echo json_encode($log['data']); ?>);
            <?php endforeach; ?>
            console.groupEnd();
            <?php endif; ?>
            
            // Scroll debug console to bottom
            const debugBody = document.querySelector('.debug-body');
            if (debugBody) {
                debugBody.scrollTop = debugBody.scrollHeight;
            }
        });
    </script>
</body>
</html>
