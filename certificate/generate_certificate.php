<?php
require_once __DIR__ . '/../includes/db_config.php';
require_once 'config.php';
require_once 'certificate_functions.php';

// Start session for secure error handling
session_start();

// Enable debugging
$debug = isset($_POST['debug_mode']) && $_POST['debug_mode'] == '1';

// Initialize variables
$error = '';
$certificate_path = '';

/**
 * Console debug logger function
 * Outputs debug info to browser console if debug mode is enabled
 */
function consoleDebug($label, $data, $type = 'log') {
    global $debug;
    if (!$debug) return;
    
    // Format data for console output
    $json = json_encode($data);
    $escaped = str_replace("'", "\\'", $json);
    
    // Output to console
    echo "<script>console.{$type}('{$label}:', {$escaped});</script>\n";
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Debug header
    if ($debug) {
        echo "<!DOCTYPE html><html><head><title>Certificate Generation Debug</title></head><body>";
        echo "<script>console.group('Certificate Generation Process');</script>";
        consoleDebug('Form submission', $_POST);
    }
    
    // Validate inputs
    if (empty($_POST['jis_id'])) {
        $error = "JIS ID is required";
        consoleDebug('Validation error', ['message' => $error], 'error');
    } elseif (empty($_POST['student_name'])) {
        $error = "Student name is required";
        consoleDebug('Validation error', ['message' => $error], 'error');
    } else {
        try {
            // Clean inputs
            $jis_id = trim($_POST['jis_id']);
            $input_name = trim($_POST['student_name']);
            
            // Only log in debug mode
            if ($debug) {
                consoleDebug('Input data', ['jis_id' => $jis_id, 'name' => $input_name]);
            }
            
            // Verify database connection
            if (!$db) {
                throw new Exception("Database connection not established");
            }
            
            // Verify registrations table exists (only in debug mode)
            if ($debug) {
                try {
                    $checkTable = $db->query("SHOW TABLES LIKE 'registrations'");
                    $tableExists = $checkTable->rowCount() > 0;
                    consoleDebug('Table check', ['table' => 'registrations', 'exists' => $tableExists]);
                    
                    if (!$tableExists) {
                        throw new Exception("registrations table does not exist");
                    }
                } catch (Exception $e) {
                    consoleDebug('Table check error', $e->getMessage(), 'error');
                    throw $e;
                }
            }
            
            // Get registration data from database
            $registration = getRegistrationByJisId($db, $jis_id);
            
            if (!$registration) {
                // In debug mode, try a direct query to see the results
                if ($debug) {
                    try {
                        $stmt = $db->prepare("SELECT * FROM registrations WHERE jis_id = ?");
                        $stmt->execute([$jis_id]);
                        $direct_result = $stmt->fetch(PDO::FETCH_ASSOC);
                        consoleDebug('Direct query result', $direct_result ?: 'No results found');
                        
                        // Try a broader search
                        $stmt = $db->prepare("SELECT * FROM registrations WHERE jis_id LIKE ?");
                        $stmt->execute(['%' . $jis_id . '%']);
                        $broader_search = $stmt->fetchAll(PDO::FETCH_ASSOC);
                        consoleDebug('Broader search results', [
                            'count' => count($broader_search),
                            'results' => $broader_search
                        ]);
                    } catch (Exception $e) {
                        consoleDebug('Direct query error', $e->getMessage(), 'error');
                    }
                }
                
                $error = "No registration found for JIS ID: $jis_id";
            } else {
                // Check if provided name matches the database name (case insensitive)
                $db_name = trim($registration['student_name'] ?? '');
                
                // Convert both names to lowercase for comparison
                $input_name_lower = strtolower($input_name);
                $db_name_lower = strtolower($db_name);
                
                if ($debug) {
                    consoleDebug('Name validation', [
                        'input_name' => $input_name,
                        'db_name' => $db_name,
                        'match' => ($input_name_lower === $db_name_lower)
                    ]);
                }
                
                if ($input_name_lower !== $db_name_lower) {
                    $error = "The provided name doesn't match our records for this JIS ID. Please check your spelling.";
                } else {
                    // Check payment status first
                    $paymentStatus = $registration['payment_status'] ?? '';
                    
                    if (strtolower($paymentStatus) === 'not paid') {
                        $error = "Certificate cannot be issued as Payment Pending/Not Updated. Please Contact Team.";
                    } else {
                        // Initialize roles array to track certificate types to generate
                        $rolesToGenerate = [];
                        
                        // Check competition_name for participant role
                        $competitionName = $registration['competition_name'] ?? '';
                        
                        // Check the dedicated role column in the database
                        $staffRole = $registration['role'] ?? '';
                        
                        // Flag to track if we have at least one role
                        $hasRole = false;
                        
                        // Check for participant role through competition_name
                        if (!empty(trim($competitionName))) {
                            $hasRole = true;
                            $rolesToGenerate[] = 'Participant';
                        }
                        
                        // Check for staff role from the role column
                        if (!empty(trim($staffRole))) {
                            $hasRole = true;
                            
                            // Check which staff role they have
                            if (stripos($staffRole, 'volunteer') !== false) {
                                $rolesToGenerate[] = 'Volunteer';
                            } 
                            
                            if (stripos($staffRole, 'crew') !== false) {
                                $rolesToGenerate[] = 'Crew Member';
                            }
                        }
                        
                        // If no roles detected, show error message
                        if (!$hasRole) {
                            $error = "No event participation or staff role found for your JIS ID. Certificates are only issued for participants, volunteers, or crew members.";
                        } else {
                            // Get student's name from student_name column
                            $studentName = $registration['student_name'] ?? '';
                            
                            // Generate a unique certificate ID for verification
                            $certificateId = generateCertificateId($jis_id);
                            
                            // Record certificate generation in database for future verification
                            $token = recordCertificateGeneration($db, $jis_id, $registration);
                            
                            if (empty($studentName)) {
                                $error = "Student name not found in registration data";
                            } else {
                                // Handle multiple certificates if needed
                                if (count($rolesToGenerate) > 1) {
                                    // Multiple roles - generate combined certificate
                                    $certificate_path = generateMultipleRoleCertificates($studentName, $jis_id, $token, $rolesToGenerate);
                                } else {
                                    // Single role - normal certificate generation
                                    $role = $rolesToGenerate[0];
                                    
                                    switch ($role) {
                                        case 'Participant':
                                            $certificate_path = generateParticipantCertificate($studentName, $jis_id, $token);
                                            break;
                                        case 'Volunteer':
                                            $certificate_path = generateVolunteerCertificate($studentName, $jis_id, $token);
                                            break;
                                        case 'Crew Member':
                                            $certificate_path = generateCrewCertificate($studentName, $jis_id, $token);
                                            break;
                                    }
                                }
                                
                                if ($certificate_path) {
                                    // Serve file for download
                                    $extension = pathinfo($certificate_path, PATHINFO_EXTENSION);
    
                                    // Use different handling for ZIP files vs PDF files
                                    if ($extension === 'zip') {
                                        $file_name = "maJIStic_certificates_" . str_replace(' ', '_', $studentName) . ".zip";
                                        
                                        if ($debug) {
                                            consoleDebug('Serving ZIP file', [
                                                'filename' => $file_name,
                                                'content_type' => 'application/zip',
                                                'filesize' => filesize($certificate_path),
                                                'type' => 'Multiple certificates'
                                            ]);
                                            
                                            echo "<div style='padding:20px;background:#e8f5e9;margin:20px;border-radius:5px;'>";
                                            echo "<h3>Certificate Generation Success</h3>";
                                            echo "<p>Multiple certificates have been generated for: <strong>{$studentName}</strong></p>";
                                            echo "<p>Roles: <strong>" . implode(", ", $rolesToGenerate) . "</strong></p>";
                                            echo "<p><em>Your certificates have been bundled into a ZIP file because each role has a separate certificate.</em></p>";
                                            echo "<p>Certificate path: <code>{$certificate_path}</code></p>";
                                            echo "<a href='{$certificate_path}' download='{$file_name}' style='padding:10px;background:#4caf50;color:#fff;text-decoration:none;border-radius:4px;display:inline-block;margin-top:10px;'>Download ZIP</a>";
                                            echo "<a href='index.php' style='padding:10px;background:#2196f3;color:#fff;text-decoration:none;border-radius:4px;display:inline-block;margin-top:10px;margin-left:10px;'>Back to Form</a>";
                                            echo "</div>";
                                            echo "<script>console.groupEnd();</script>";
                                            echo "</body></html>";
                                            exit;
                                        } else {
                                            header('Content-Type: application/zip');
                                            header('Content-Disposition: attachment; filename="' . $file_name . '"');
                                            header('Content-Length: ' . filesize($certificate_path));
                                            readfile($certificate_path);
                                            
                                            // Cleanup - delete temp file after download
                                            @unlink($certificate_path);
                                            exit;
                                        }
                                    } else {
                                        // Standard PDF handling
                                        $file_name = "maJIStic_certificate_" . str_replace(' ', '_', $studentName) . ".pdf";
                                        
                                        if ($debug) {
                                            consoleDebug('Serving file', [
                                                'filename' => $file_name,
                                                'content_type' => 'application/pdf',
                                                'filesize' => filesize($certificate_path)
                                            ]);
                                            
                                            echo "<div style='padding:20px;background:#e8f5e9;margin:20px;border-radius:5px;'>";
                                            echo "<h3>Certificate Generation Success</h3>";
                                            echo "<p>Certificate has been generated for: <strong>{$studentName}</strong></p>";
                                            
                                            if (count($rolesToGenerate) > 1) {
                                                // Show all roles when multiple certificates are generated
                                                echo "<p>Roles: <strong>" . implode(", ", $rolesToGenerate) . "</strong></p>";
                                                echo "<p><em>A combined certificate with all your roles has been generated.</em></p>";
                                            } else {
                                                // Single role
                                                echo "<p>Role: <strong>{$rolesToGenerate[0]}</strong></p>";
                                            }
                                            
                                            echo "<p>Certificate path: <code>{$certificate_path}</code></p>";
                                            echo "<a href='generate_certificate.php?jis_id={$jis_id}&download=1' style='padding:10px;background:#4caf50;color:#fff;text-decoration:none;border-radius:4px;display:inline-block;margin-top:10px;'>Download Certificate</a>";
                                            echo "<a href='index.php' style='padding:10px;background:#2196f3;color:#fff;text-decoration:none;border-radius:4px;display:inline-block;margin-top:10px;margin-left:10px;'>Back to Form</a>";
                                            echo "</div>";
                                            echo "<script>console.groupEnd();</script>";
                                            echo "</body></html>";
                                            exit;
                                        } else {
                                            header('Content-Type: application/pdf');
                                            header('Content-Disposition: attachment; filename="' . $file_name . '"');
                                            header('Content-Length: ' . filesize($certificate_path));
                                            readfile($certificate_path);
                                            
                                            // Cleanup - delete temp file after download
                                            @unlink($certificate_path);
                                            exit;
                                        }
                                    }
                                } else {
                                    $error = "Error generating certificate. Please try again later.";
                                }
                            }
                        }
                    }
                }
            }
        } catch (Exception $e) {
            $error = "An error occurred: " . $e->getMessage();
            if ($debug) {
                consoleDebug('Exception', [
                    'message' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ], 'error');
            }
        }
    }
    
    // If there's an error and in debug mode, show detailed error
    if ($error && $debug) {
        echo "<div style='padding:20px;background:#ffebee;margin:20px;border-radius:5px;'>";
        echo "<h3>Certificate Generation Error</h3>";
        echo "<p><strong>{$error}</strong></p>";
        echo "<a href='index.php' style='padding:10px;background:#f44336;color:#fff;text-decoration:none;border-radius:4px;display:inline-block;margin-top:10px;'>Back to Form</a>";
        echo "</div>";
        echo "<script>console.groupEnd();</script>";
        echo "</body></html>";
        exit;
    }
    
    // If there's an error, store in session instead of URL parameter
    if ($error) {
        $_SESSION['certificate_error'] = $error;
        header("Location: index.php");
        exit;
    }
}

// Handle direct download requests (used in debug mode)
if (isset($_GET['download']) && $_GET['download'] == '1' && isset($_GET['jis_id'])) {
    // Implementation for direct downloads
    // This would process the JIS ID again and generate a downloadable certificate
}

// If script execution reaches here without redirection, show error page
$_SESSION['certificate_error'] = "Invalid request";
header("Location: index.php");
exit;
