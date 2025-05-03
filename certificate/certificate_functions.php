<?php
// Certificate generation utility functions

/**
 * Get registration data by JIS ID
 * 
 * @param object $db PDO database connection
 * @param string $jis_id The JIS ID to look up
 * @return array|false Registration data or false if not found
 */
function getRegistrationByJisId($db, $jis_id) {
    try {
        // Format check and cleanup for JIS ID
        $jis_id = trim($jis_id);
        
        // Console debug if function exists
        if (function_exists('consoleDebug')) {
            consoleDebug('getRegistrationByJisId', ['jis_id' => $jis_id]);
        }
        
        // Direct exact match query
        $stmt = $db->prepare("SELECT * FROM registrations WHERE jis_id = :jis_id");
        $stmt->execute([':jis_id' => $jis_id]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (function_exists('consoleDebug')) {
            consoleDebug('Exact match query', [
                'query' => "SELECT * FROM registrations WHERE jis_id = '" . $jis_id . "'",
                'result' => $data ? 'Found' : 'Not found'
            ]);
        }
        
        // If not found, try a more flexible search with LIKE
        if (!$data) {
            $stmt = $db->prepare("SELECT * FROM registrations WHERE jis_id LIKE :jis_id_pattern");
            $stmt->execute([':jis_id_pattern' => "%$jis_id%"]);
            $data = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (function_exists('consoleDebug')) {
                consoleDebug('LIKE pattern query', [
                    'query' => "SELECT * FROM registrations WHERE jis_id LIKE '%" . $jis_id . "%'",
                    'result' => $data ? 'Found' : 'Not found'
                ]);
            }
        }
        
        // Debug logging
        if ($data) {
            error_log("JIS ID found: " . $data['jis_id'] . " for student: " . $data['student_name']);
        } else {
            // Check if the table exists and has records
            $stmt = $db->query("SELECT COUNT(*) as count FROM registrations");
            $count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
            error_log("Total records in registrations: $count");
            
            // Show some sample records to understand the data format
            $stmt = $db->query("SELECT jis_id, student_name FROM registrations LIMIT 5");
            $samples = $stmt->fetchAll(PDO::FETCH_ASSOC);
            error_log("Sample JIS IDs in database: " . print_r($samples, true));
            
            error_log("Could not find JIS ID: $jis_id in the database");
        }
        
        return $data;
    } catch (Exception $e) {
        error_log("Database error in getRegistrationByJisId: " . $e->getMessage() . "\n" . $e->getTraceAsString());
        
        if (function_exists('consoleDebug')) {
            consoleDebug('Database error', [
                'function' => 'getRegistrationByJisId',
                'message' => $e->getMessage()
            ], 'error');
        }
        return false;
    }
}

/**
 * Check if user is eligible for the selected role
 * 
 * @param array $registrationData Registration data from database
 * @param string $role The role to check (Participant, Crew Member, Volunteer)
 * @return bool True if eligible, false otherwise
 */
function isEligibleForRole($registrationData, $role) {
    // If data not found or competition_name is not set
    if (!$registrationData || !isset($registrationData['competition_name'])) {
        return false;
    }
    
    // Get the competition string and convert to lowercase for case-insensitive matching
    $competitionString = strtolower($registrationData['competition_name']);
    
    switch ($role) {
        case 'Participant':
            // Define participant events (lowercase for case-insensitive matching)
            $participantEvents = [
                'jam room',
                'band',
                'taal se taal mila',
                'dance',
                'fashion fiesta',
                'fashion show',
                'actomania',
                'drama',
                'poetry slam',
                'recitation',
                'mic hunters',
                'anchoring'
            ];
            
            // Check if any participant event keyword is in the competition string
            foreach ($participantEvents as $event) {
                if (strpos($competitionString, $event) !== false) {
                    return true;
                }
            }
            
            // Also check if user has paid (they qualify as participants even without a specific event)
            if (isset($registrationData['payment_status']) && $registrationData['payment_status'] === 'Paid') {
                return true;
            }
            
            return false;
            
        case 'Crew Member':
            // Check for "Crew Member" in competition_name (case insensitive)
            return (strpos($competitionString, 'crew member') !== false);
            
        case 'Volunteer':
            // Check for "Volunteer" in competition_name (case insensitive)
            return (strpos($competitionString, 'volunteer') !== false);
            
        default:
            return false;
    }
}

/**
 * Custom error logger for certificate generation
 * Logs errors to both error_log and outputs to console if in debug mode
 * 
 * @param string $message Error message
 * @param string $level Error level (ERROR, WARNING, INFO)
 * @param Exception|null $exception Optional exception object
 */
function certLog($message, $level = 'ERROR', $exception = null) {
    $timestamp = date('Y-m-d H:i:s');
    $logMessage = "[$timestamp] [$level] Certificate: $message";
    
    // Log to PHP error log
    error_log($logMessage);
    
    // If exception provided, log its details
    if ($exception instanceof Exception) {
        error_log("[$timestamp] [$level] Exception: " . $exception->getMessage());
        error_log("[$timestamp] [$level] Trace: " . $exception->getTraceAsString());
    }
    
    // Add console logging if we're in a web environment and debugging is enabled
    global $debug;
    if (isset($debug) && $debug === true && php_sapi_name() !== 'cli') {
        echo "<!-- Certificate Debug: " . htmlspecialchars($logMessage) . " -->\n";
        
        if ($level == 'ERROR') {
            // Output visible error for admins when debugging
            echo "<script>console.error(" . json_encode($logMessage) . ");</script>\n";
            
            if ($exception instanceof Exception) {
                echo "<script>console.error(" . json_encode("Exception: " . $exception->getMessage()) . ");</script>\n";
                echo "<script>console.debug(" . json_encode("Trace: " . $exception->getTraceAsString()) . ");</script>\n";
            }
        } else {
            echo "<script>console.log(" . json_encode($logMessage) . ");</script>\n";
        }
    }
}

/**
 * Generate certificate for participant
 * @param string $studentName
 * @param string $jis_id JIS ID for QR code 
 * @param string $token Encrypted verification token
 * @return string|false Path to generated certificate or false on failure
 */
function generateParticipantCertificate($studentName, $jis_id = '', $token = null) {
    return generateCertificate($studentName, 'participant', $jis_id, $token);
}

/**
 * Generate certificate for volunteer
 * @param string $studentName
 * @param string $jis_id JIS ID for QR code
 * @param string $token Encrypted verification token
 * @return string|false Path to generated certificate or false on failure
 */
function generateVolunteerCertificate($studentName, $jis_id = '', $token = null) {
    return generateCertificate($studentName, 'volunteer', $jis_id, $token);
}

/**
 * Generate certificate for crew member
 * @param string $studentName
 * @param string $jis_id JIS ID for QR code
 * @param string $token Encrypted verification token
 * @return string|false Path to generated certificate or false on failure
 */
function generateCrewCertificate($studentName, $jis_id = '', $token = null) {
    return generateCertificate($studentName, 'crew', $jis_id, $token);
}

/**
 * Generate certificate with given name and role
 * @param string $name
 * @param string $role
 * @param string $jis_id JIS ID for QR code
 * @param string $token Encrypted verification token
 * @return string|false Path to generated certificate or false on failure
 */
function generateCertificate($name, $role, $jis_id = '', $token = null) {
    try {
        // Define template path based on role
        $templateFile = "{$role}_template.pdf";
        $templatePath = __DIR__ . "/templates/$templateFile";
        
        certLog("Starting certificate generation for '$name' as '$role'", 'INFO');
        certLog("Looking for template: $templatePath", 'INFO');
        
        if (!file_exists($templatePath)) {
            certLog("Template file not found: $templatePath", 'ERROR');
            
            // Check if templates directory exists
            $templatesDir = __DIR__ . "/templates";
            if (!is_dir($templatesDir)) {
                certLog("Templates directory doesn't exist at: $templatesDir", 'ERROR');
            } else {
                // List available templates
                $files = scandir($templatesDir);
                $templates = array_filter($files, function($file) {
                    return $file !== '.' && $file !== '..' && pathinfo($file, PATHINFO_EXTENSION) === 'pdf';
                });
                
                if (empty($templates)) {
                    certLog("No PDF templates found in templates directory", 'ERROR');
                } else {
                    certLog("Available templates: " . implode(", ", $templates), 'INFO');
                }
            }
            
            return false;
        }
        
        // Create output file path
        $outputPath = __DIR__ . "/temp/" . uniqid('cert_') . ".pdf";
        
        // Ensure temp directory exists
        $tempDir = __DIR__ . "/temp";
        if (!is_dir($tempDir)) {
            certLog("Creating temp directory: $tempDir", 'INFO');
            if (!mkdir($tempDir, 0755, true)) {
                certLog("Failed to create temp directory: $tempDir", 'ERROR');
                return false;
            }
        }
        
        // Check for FPDF and FPDI library
        $fpdfPath = __DIR__ . '/vendor/setasign/fpdf/fpdf.php';
        $fpdiPath = __DIR__ . '/vendor/autoload.php';
        $hasLibraries = file_exists($fpdfPath) && file_exists($fpdiPath);
        
        certLog("FPDF Path ($fpdfPath): " . (file_exists($fpdfPath) ? "Found" : "Not Found"), 'INFO');
        certLog("FPDI Path ($fpdiPath): " . (file_exists($fpdiPath) ? "Found" : "Not Found"), 'INFO');
        
        if (!$hasLibraries) {
            // Fallback - simply copy the template as we can't add text without the libraries
            certLog("PDF libraries not found - falling back to template copy", 'WARNING');
            
            if (copy($templatePath, $outputPath)) {
                certLog("Certificate generated (template copied) for: $name", 'INFO');
                return $outputPath;
            } else {
                certLog("Failed to copy certificate template for: $name", 'ERROR');
                return false;
            }
        }
        
        // If we reach here, libraries are available - try to generate the PDF
        require_once $fpdfPath;
        require_once $fpdiPath;
        
        certLog("PDF libraries loaded successfully", 'INFO');
        
        try {
            // Create PDF using FPDI
            $pdf = new \setasign\Fpdi\Fpdi();
            
            // Get page count from source file and import the first page
            $pageCount = $pdf->setSourceFile($templatePath);
            certLog("Source PDF has $pageCount page(s)", 'INFO');
            
            // Get the imported page size and orientation
            $templateId = $pdf->importPage(1);
            $templateSize = $pdf->getTemplateSize($templateId);
            
            certLog("Template dimensions: " . $templateSize['width'] . " x " . $templateSize['height'] . " pts", 'INFO');
            
            $orientation = ($templateSize['width'] > $templateSize['height']) ? 'L' : 'P';
            
            // Add page with same orientation and size as template
            // We're using 0 for the margins to maximize usable area
            $pdf->AddPage($orientation, [$templateSize['width'], $templateSize['height']]);
            
            // Use the imported template, scaling to fit the page
            $pdf->useTemplate($templateId, 0, 0, $templateSize['width'], $templateSize['height']);
            
            // Add name to certificate with an elegant cursive/italic font style
            $pdf->SetFont('Times', 'BI', 34); // 'BI' for Bold Italic style
            $pdf->SetTextColor(0, 51, 102); // Rich navy blue color
            
            // Log the student name being added to certificate
            certLog("Adding name to certificate: $name", 'INFO');
            
            // Determine positions based on orientation
            if ($orientation === 'L') {
                // Position for name - for landscape certificate
                $nameX = $templateSize['width'] / 2; // Center horizontally
                $nameY = ($templateSize['height'] / 2) - 10; // Move slightly up from center
                
                // Set position and center text
                $pdf->SetXY($nameX - 100, $nameY);
                $pdf->Cell(200, 10, $name, 0, 1, 'C');
            } 
            // For portrait certificates
            else {
                // Position for name - for portrait certificate
                $nameX = $templateSize['width'] / 2; // Center horizontally
                $nameY = ($templateSize['height'] / 2) - 10; // Move slightly up from center
                
                // Set position and center text
                $pdf->SetXY($nameX - 100, $nameY);
                $pdf->Cell(200, 10, $name, 0, 1, 'C');
            }
            
            // Add QR code if JIS ID is provided
            if (!empty($jis_id)) {
                // Generate QR code with encrypted token
                $qrPath = generateQRCode($jis_id, $token);
                certLog("QR code generated, path: " . ($qrPath ?: "Failed"), 'INFO');
                
                if ($qrPath && file_exists($qrPath)) {
                    try {
                        // Log QR code details before placing on certificate
                        certLog("Adding QR code to certificate from path: $qrPath", 'INFO');
                        certLog("QR file size: " . filesize($qrPath) . " bytes", 'INFO');
                        
                        // Add QR code to the PDF at bottom left with REDUCED SIZE
                        if ($orientation === 'L') {
                            // Landscape - bottom left corner
                            $qrX = 15;
                            $qrY = $templateSize['height'] - 40; // Move higher up (was 30)
                            $qrSize = 20; // Same size as before
                            
                            // Add image with error handling
                            $pdf->Image($qrPath, $qrX, $qrY, $qrSize, $qrSize);
                            certLog("QR code added at X: $qrX, Y: $qrY, size: $qrSize", 'INFO');
                            
                            // Remove verification text - no JIS ID or verify URL text shown
                        } else {
                            // Portrait - bottom left corner
                            $qrX = 15;
                            $qrY = $templateSize['height'] - 40; // Move higher up (was 30)
                            $qrSize = 20; // Same size as before
                            
                            // Add image with error handling
                            $pdf->Image($qrPath, $qrX, $qrY, $qrSize, $qrSize);
                            certLog("QR code added at X: $qrX, Y: $qrY, size: $qrSize", 'INFO');
                            
                            // Remove verification text - no JIS ID or verify URL text shown
                        }
                    } catch (Exception $e) {
                        certLog("Error adding QR to PDF: " . $e->getMessage(), 'ERROR', $e);
                    }
                    
                    // Delete the temporary QR file
                    @unlink($qrPath);
                } else {
                    certLog("QR code file not found or generation failed", 'WARNING');
                }
            }
            
            // Save the PDF to output path
            $pdf->Output('F', $outputPath);
            
            if (file_exists($outputPath)) {
                certLog("Certificate successfully generated at: $outputPath", 'INFO');
                return $outputPath;
            } else {
                certLog("Failed to create output PDF file: $outputPath", 'ERROR');
                return false;
            }
        } catch (Exception $e) {
            certLog("PDF generation error: " . $e->getMessage(), 'ERROR');
            
            // Fallback to copying if PDF generation fails
            certLog("Falling back to template copy after PDF generation error", 'WARNING');
            if (copy($templatePath, $outputPath)) {
                certLog("Certificate fallback (template copied) for: $name", 'INFO');
                return $outputPath;
            } else {
                certLog("Failed to copy certificate template for: $name", 'ERROR');
                return false;
            }
        }
    } catch (Exception $e) {
        certLog("Certificate generation error", 'ERROR', $e);
        return false;
    }
}

/**
 * Generate a unique certificate ID based on JIS ID and timestamp
 * 
 * @param string $jis_id The JIS ID
 * @return string Certificate ID
 */
function generateCertificateId($jis_id) {
    $timestamp = time();
    $random = rand(1000, 9999);
    $hash = substr(md5($jis_id . $timestamp . $random), 0, 8);
    return strtoupper($hash);
}

/**
 * Create a secure verification token for a JIS ID
 * 
 * @param string $jis_id The JIS ID to encrypt
 * @return string Encrypted token
 */
function createVerificationToken($jis_id) {
    // Create a unique token combining JIS ID with timestamp (for uniqueness)
    $data = [
        'jis' => $jis_id,
        'time' => time(),
        'random' => bin2hex(random_bytes(8))
    ];
    
    // Convert to JSON
    $json = json_encode($data);
    
    // Encrypt the token
    $token = encryptData($json);
    
    // Return URL-safe token
    return $token;
}

/**
 * Encrypt data using AES-256-CBC encryption
 * 
 * @param string $data The data to encrypt
 * @return string Base64-encoded encrypted data
 */
function encryptData($data) {
    // Secret key and initialization vector - these should be stored securely in config
    $secret_key = 'majistic2k25_secret_key'; // Change this to a random string
    $secret_iv = 'majistic2k25_iv';          // Change this to a random string
    
    // Hash the key and iv
    $key = hash('sha256', $secret_key);
    $iv = substr(hash('sha256', $secret_iv), 0, 16);
    
    // Encrypt the data
    $encrypted = openssl_encrypt($data, 'AES-256-CBC', $key, 0, $iv);
    
    // Make the encrypted string URL-safe
    $urlSafe = strtr(base64_encode($encrypted), '+/=', '-_.');
    
    return $urlSafe;
}

/**
 * Decrypt a verification token
 * 
 * @param string $token The encrypted token
 * @return array|false Decrypted data or false on failure
 */
function decryptVerificationToken($token) {
    try {
        // Convert from URL-safe format
        $encrypted = base64_decode(strtr($token, '-_.', '+/='));
        
        // Secret key and initialization vector - must match the encryption
        $secret_key = 'majistic2k25_secret_key';
        $secret_iv = 'majistic2k25_iv';
        
        // Hash the key and iv
        $key = hash('sha256', $secret_key);
        $iv = substr(hash('sha256', $secret_iv), 0, 16);
        
        // Decrypt
        $decrypted = openssl_decrypt($encrypted, 'AES-256-CBC', $key, 0, $iv);
        
        if ($decrypted === false) {
            return false;
        }
        
        // Parse the JSON data
        $data = json_decode($decrypted, true);
        
        if (!is_array($data) || !isset($data['jis'])) {
            return false;
        }
        
        return $data;
    } catch (Exception $e) {
        return false;
    }
}

/**
 * Record certificate generation in database
 * 
 * @param PDO $db Database connection
 * @param string $jis_id JIS ID
 * @param array $registration Registration data
 * @return string|bool Token if successful, false on failure
 */
function recordCertificateGeneration($db, $jis_id, $registration) {
    try {
        // Generate verification token
        $token = createVerificationToken($jis_id);
        
        // Check if certificates table exists
        $checkTable = $db->query("SHOW TABLES LIKE 'certificate_records'");
        if ($checkTable->rowCount() == 0) {
            // Create certificates table if it doesn't exist
            $db->exec("
                CREATE TABLE certificate_records (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    jis_id VARCHAR(50) NOT NULL,
                    token VARCHAR(255) NOT NULL,
                    student_name VARCHAR(255) NOT NULL,
                    role VARCHAR(50) NOT NULL,
                    generated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    verification_count INT DEFAULT 0,
                    ip_address VARCHAR(45) NULL,
                    user_agent TEXT NULL,
                    referer TEXT NULL,
                    device_info TEXT NULL,
                    INDEX (jis_id),
                    UNIQUE (jis_id),
                    UNIQUE (token)
                )
            ");
            certLog("Created certificate_records table with tracking fields", 'INFO');
        } else {
            // Check for tracking columns and add them if they don't exist
            $requiredColumns = [
                'token' => "ALTER TABLE certificate_records ADD COLUMN token VARCHAR(255) NOT NULL AFTER jis_id",
                'ip_address' => "ALTER TABLE certificate_records ADD COLUMN ip_address VARCHAR(45) NULL AFTER verification_count",
                'user_agent' => "ALTER TABLE certificate_records ADD COLUMN user_agent TEXT NULL AFTER ip_address",
                'referer' => "ALTER TABLE certificate_records ADD COLUMN referer TEXT NULL AFTER user_agent",
                'device_info' => "ALTER TABLE certificate_records ADD COLUMN device_info TEXT NULL AFTER referer"
            ];
            
            foreach ($requiredColumns as $column => $sql) {
                $checkColumn = $db->query("SHOW COLUMNS FROM certificate_records LIKE '$column'");
                if ($checkColumn->rowCount() == 0) {
                    $db->exec($sql);
                    certLog("Added $column column to certificate_records table", 'INFO');
                    
                    if ($column == 'token') {
                        $db->exec("ALTER TABLE certificate_records ADD UNIQUE (token)");
                    }
                }
            }
        }
        
        // Determine role based on competition name
        $role = 'Participant'; // Default
        $competitionString = strtolower($registration['competition_name'] ?? '');
        
        if (strpos($competitionString, 'crew member') !== false) {
            $role = 'Crew Member';
        } elseif (strpos($competitionString, 'volunteer') !== false) {
            $role = 'Volunteer';
        }
        
        // Get current IST timestamp explicitly
        $currentTimeIST = new DateTime('now', new DateTimeZone('Asia/Kolkata'));
        $istTimestamp = $currentTimeIST->format('Y-m-d H:i:s');
        
        // Collect tracking information
        $ipAddress = $_SERVER['REMOTE_ADDR'] ?? '';
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        $referer = $_SERVER['HTTP_REFERER'] ?? '';
        
        // Extract more detailed device info from user agent
        $deviceInfo = [];
        
        // Try to get browser info
        if (preg_match('/(firefox|msie|chrome|safari|trident|edge|opera|vivaldi)/i', $userAgent, $browser)) {
            $deviceInfo['browser'] = ucfirst($browser[1]);
        }
        
        // Try to get OS info
        if (preg_match('/(windows|macintosh|linux|android|iphone|ipad)/i', $userAgent, $os)) {
            $deviceInfo['os'] = ucfirst($os[1]);
        }
        
        // Try to detect if mobile
        $isMobile = preg_match('/(mobile|android|iphone|ipad|phone)/i', $userAgent);
        $deviceInfo['is_mobile'] = $isMobile ? 'Yes' : 'No';
        
        $deviceInfoJson = json_encode($deviceInfo);
        
        // Log the tracking information
        certLog("Certificate download tracking", [
            'ip' => $ipAddress,
            'user_agent' => substr($userAgent, 0, 50) . '...',
            'device' => $deviceInfo
        ], 'INFO');
        
        // Insert record with IST timestamp and tracking info
        $stmt = $db->prepare("
            INSERT INTO certificate_records 
                (jis_id, token, student_name, role, generated_at, ip_address, user_agent, referer, device_info)
            VALUES 
                (:jis_id, :token, :student_name, :role, :generated_at, :ip_address, :user_agent, :referer, :device_info)
            ON DUPLICATE KEY UPDATE 
                token = :token,
                student_name = :student_name,
                role = :role,
                generated_at = :generated_at,
                verification_count = verification_count + 1,
                ip_address = :ip_address,
                user_agent = :user_agent,
                referer = :referer,
                device_info = :device_info
        ");
        
        $result = $stmt->execute([
            ':jis_id' => $jis_id,
            ':token' => $token,
            ':student_name' => $registration['student_name'] ?? '',
            ':role' => $role,
            ':generated_at' => $istTimestamp,
            ':ip_address' => $ipAddress,
            ':user_agent' => $userAgent,
            ':referer' => $referer,
            ':device_info' => $deviceInfoJson
        ]);
        
        certLog("Certificate recorded with tracking info for JIS ID: $jis_id", 'INFO');
        return $token;
    } catch (Exception $e) {
        certLog("Failed to record certificate", 'ERROR', $e);
        return false;
    }
}

/**
 * Generate QR code for certificate verification using encrypted token
 * 
 * @param string $jis_id JIS ID for verification
 * @param string $token Encrypted verification token
 * @return string|false Path to generated QR code image or false on failure
 */
function generateQRCode($jis_id, $token = null) {
    try {
        // Create QR code directory if it doesn't exist
        $qrDir = __DIR__ . "/temp/qr";
        if (!is_dir($qrDir)) {
            if (!mkdir($qrDir, 0755, true)) {
                certLog("Failed to create QR code directory: $qrDir", 'ERROR');
                return false;
            }
            certLog("Created QR code directory: $qrDir", 'INFO');
        }
        
        // If no token is provided, create one
        if ($token === null) {
            $token = createVerificationToken($jis_id);
        }
        
        // Generate verification URL with encrypted token instead of JIS ID
        $verifyUrl = "https://majistic.org/verify?token=" . urlencode($token);
        certLog("Generated secure verification URL with token", 'INFO');
        
        // Create a filename based on JIS ID
        $safeJisId = preg_replace('/[^a-zA-Z0-9]/', '', $jis_id); // Remove special characters
        $qrPath = $qrDir . "/" . $safeJisId . "_" . uniqid() . ".png";
        
        // Try to use Google Chart API for QR generation
        $googleChartUrl = 'https://chart.googleapis.com/chart?chs=300x300&cht=qr&chl=' . urlencode($verifyUrl) . '&choe=UTF-8';
        certLog("Attempting to generate QR code using Google Chart API", 'INFO');
        
        $imageData = @file_get_contents($googleChartUrl);
        if ($imageData === false) {
            certLog("Failed to get QR code from Google Chart API, trying alternative", 'WARNING');
            
            // Try alternative QR code service
            $altUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=' . urlencode($verifyUrl);
            $imageData = @file_get_contents($altUrl);
            
            if ($imageData === false) {
                certLog("All QR code generation attempts failed", 'ERROR');
                return false;
            }
        }
        
        // Save the QR code image to file
        if (file_put_contents($qrPath, $imageData)) {
            certLog("QR code saved to: $qrPath", 'INFO');
            return $qrPath;
        }
        
        certLog("Failed to write QR code to file", 'ERROR');
        return false;
    } catch (Exception $e) {
        certLog("QR code generation error: " . $e->getMessage(), 'ERROR', $e);
        return false;
    }
}