<?php
require_once __DIR__ . '/../includes/db_config.php';
require_once 'config.php';
require_once 'certificate_functions.php';

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
            consoleDebug('Input data', ['jis_id' => $jis_id, 'name' => $input_name]);
            
            // Debug: Log the JIS ID being searched for
            error_log("Looking up JIS ID: $jis_id");
            
            // Verify database connection
            if (!$db) {
                consoleDebug('Database connection', 'Failed', 'error');
                throw new Exception("Database connection not established");
            }
            consoleDebug('Database connection', 'Successful');
            
            // Verify registrations table exists
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
            
            // Get registration data from database
            $registration = getRegistrationByJisId($db, $jis_id);
            
            // Debug: Log the database query result
            error_log("Registration lookup result: " . ($registration ? "Found" : "Not found"));
            
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
                
                consoleDebug('Name validation', [
                    'input_name' => $input_name,
                    'db_name' => $db_name,
                    'input_lower' => $input_name_lower,
                    'db_lower' => $db_name_lower,
                    'match' => ($input_name_lower === $db_name_lower)
                ]);
                
                if ($input_name_lower !== $db_name_lower) {
                    $error = "The provided name doesn't match our records for this JIS ID. Please check your spelling.";
                    error_log("Name mismatch for JIS ID: $jis_id - Input: '$input_name', DB: '$db_name'");
                    consoleDebug('Name mismatch', ['input' => $input_name, 'db' => $db_name], 'warn');
                } else {
                    // Check payment status first
                    $paymentStatus = $registration['payment_status'] ?? '';
                    
                    // Log payment status
                    if (function_exists('consoleDebug')) {
                        consoleDebug('Payment Status Check', [
                            'status' => $paymentStatus,
                            'jis_id' => $jis_id,
                            'student_name' => $registration['student_name'] ?? 'Unknown'
                        ]);
                    }
                    
                    if (strtolower($paymentStatus) === 'not paid') {
                        $error = "Certificate cannot be issued as Payment Pending/Not Updated. Please Contact Team.";
                        error_log("Certificate denied - Payment pending for JIS ID: $jis_id, Name: " . ($registration['student_name'] ?? 'Unknown'));
                    } else {
                        // Check if competition_name is empty
                        $competitionName = $registration['competition_name'] ?? '';
                        
                        if (empty(trim($competitionName))) {
                            $error = "No event participation recorded for your JIS ID. Certificates are only issued for event participants.";
                            error_log("Certificate denied - No competition/event found for JIS ID: $jis_id, Name: " . ($registration['student_name'] ?? 'Unknown'));
                            consoleDebug('Certificate eligibility', ['error' => 'No competition name found', 'jis_id' => $jis_id], 'error');
                        } else {
                            // Get student's name from student_name column
                            $studentName = $registration['student_name'] ?? '';
                            consoleDebug('Student name', $studentName);
                            
                            // Generate a unique certificate ID for verification
                            $certificateId = generateCertificateId($jis_id);
                            consoleDebug('Certificate ID', $certificateId);
                            
                            // Record certificate generation in database for future verification
                            $token = recordCertificateGeneration($db, $jis_id, $registration);
                            if (!$token) {
                                consoleDebug('Certificate recording', 'Failed to record in database', 'warn');
                            } else {
                                consoleDebug('Certificate token', ['created' => true, 'length' => strlen($token)]);
                            }
                            
                            if (empty($studentName)) {
                                $error = "Student name not found in registration data";
                                consoleDebug('Error', $error, 'error');
                            } else {
                                // Determine the appropriate role based on competition_name
                                $role = '';
                                
                                // Competition name from database
                                $competitionName = $registration['competition_name'] ?? '';
                                consoleDebug('Competition name', $competitionName);
                                
                                // Convert to lowercase for case-insensitive matching
                                $competitionString = strtolower($competitionName);
                                
                                // Check for Crew Member first
                                if (strpos($competitionString, 'crew member') !== false) {
                                    $role = 'Crew Member';
                                    consoleDebug('Role match', [
                                        'matched' => 'Crew Member',
                                        'pattern' => 'crew member',
                                        'found_at' => strpos($competitionString, 'crew member')
                                    ]);
                                } 
                                // Then check for Volunteer
                                else if (strpos($competitionString, 'volunteer') !== false) {
                                    $role = 'Volunteer';
                                    consoleDebug('Role match', [
                                        'matched' => 'Volunteer',
                                        'pattern' => 'volunteer',
                                        'found_at' => strpos($competitionString, 'volunteer')
                                    ]);
                                } 
                                // Otherwise, check for participant events
                                else {
                                    $participantEvents = [
                                        'jam room', 'band', 'taal se taal mila', 'dance', 
                                        'fashion fiesta', 'fashion show', 'actomania', 'drama', 
                                        'poetry slam', 'recitation', 'mic hunters', 'anchoring'
                                    ];
                                    
                                    $matchDetails = ['matched' => false, 'event' => '', 'search_string' => $competitionString];
                                    
                                    foreach ($participantEvents as $event) {
                                        if (strpos($competitionString, $event) !== false) {
                                            $role = 'Participant';
                                            $matchDetails = [
                                                'matched' => true,
                                                'event' => $event,
                                                'pattern' => $event,
                                                'found_at' => strpos($competitionString, $event)
                                            ];
                                            break;
                                        }
                                    }
                                    
                                    // If no specific event is found but payment is made, consider as Participant
                                    if (empty($role) && isset($registration['payment_status']) && $registration['payment_status'] === 'Paid') {
                                        $role = 'Participant';
                                        $matchDetails = [
                                            'matched' => true,
                                            'via' => 'payment_status',
                                            'payment_status' => $registration['payment_status']
                                        ];
                                    }
                                    
                                    consoleDebug('Event matching', $matchDetails);
                                }
                                
                                consoleDebug('Final role determination', ['role' => $role]);
                                
                                if (empty($role)) {
                                    $error = "Could not determine eligibility for certificate. Please contact the organizers.";
                                    consoleDebug('Role error', 'No eligible role found', 'warn');
                                } else {
                                    // Generate certificate based on role
                                    consoleDebug('Generating certificate', ['type' => $role, 'for' => $studentName]);
                                    
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
                                    
                                    consoleDebug('Certificate generation result', [
                                        'success' => !empty($certificate_path),
                                        'path' => $certificate_path
                                    ]);
                                    
                                    if ($certificate_path) {
                                        // Serve file for download
                                        $file_name = "maJIStic_certificate_" . str_replace(' ', '_', $studentName) . ".pdf";
                                        consoleDebug('Serving file', [
                                            'filename' => $file_name,
                                            'content_type' => 'application/pdf',
                                            'filesize' => filesize($certificate_path)
                                        ]);
                                        
                                        if ($debug) {
                                            echo "<div style='padding:20px;background:#e8f5e9;margin:20px;border-radius:5px;'>";
                                            echo "<h3>Certificate Generation Success</h3>";
                                            echo "<p>Certificate has been generated for: <strong>{$studentName}</strong></p>";
                                            echo "<p>Role: <strong>{$role}</strong></p>";
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
                                    } else {
                                        $error = "Error generating certificate. Please try again later.";
                                        consoleDebug('Certificate error', 'Generation failed', 'error');
                                    }
                                }
                            }
                        }
                    }
                }
            }
        } catch (Exception $e) {
            $error = "An error occurred: " . $e->getMessage();
            consoleDebug('Exception', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ], 'error');
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
    
    // If there's an error, redirect back to the form
    if ($error) {
        header("Location: index.php?error=" . urlencode($error));
        exit;
    }
}

// Handle direct download requests (used in debug mode)
if (isset($_GET['download']) && $_GET['download'] == '1' && isset($_GET['jis_id'])) {
    // Implementation for direct downloads
    // This would process the JIS ID again and generate a downloadable certificate
}

// If script execution reaches here without redirection, show error page
header("Location: index.php?error=Invalid request");
exit;
