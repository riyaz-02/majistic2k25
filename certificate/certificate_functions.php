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
 * @return string|false Path to generated certificate or false on failure
 */
function generateParticipantCertificate($studentName) {
    return generateCertificate($studentName, 'participant');
}

/**
 * Generate certificate for volunteer
 * @param string $studentName
 * @return string|false Path to generated certificate or false on failure
 */
function generateVolunteerCertificate($studentName) {
    return generateCertificate($studentName, 'volunteer');
}

/**
 * Generate certificate for crew member
 * @param string $studentName
 * @return string|false Path to generated certificate or false on failure
 */
function generateCrewCertificate($studentName) {
    return generateCertificate($studentName, 'crew');
}

/**
 * Generate certificate with given name and role
 * @param string $name
 * @param string $role
 * @return string|false Path to generated certificate or false on failure
 */
function generateCertificate($name, $role) {
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
        
        // Create PDF using FPDI
        $pdf = new \setasign\Fpdi\Fpdi();
        
        // Get page count from source file
        $pageCount = $pdf->setSourceFile($templatePath);
        certLog("Source PDF has $pageCount page(s)", 'INFO');
        
        // Import first page
        $templateId = $pdf->importPage(1);
        $pdf->AddPage();
        $pdf->useTemplate($templateId);
        
        // Add name to certificate
        $pdf->SetFont('Helvetica', 'B', 24);
        $pdf->SetTextColor(0, 0, 0);
        
        // Position for name (adjust these coordinates based on template)
        $pdf->SetXY(10, 100); // These coordinates need to be adjusted
        $pdf->Cell(0, 10, $name, 0, 1, 'C');
        
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
        certLog("Certificate generation error", 'ERROR', $e);
        return false;
    }
}
