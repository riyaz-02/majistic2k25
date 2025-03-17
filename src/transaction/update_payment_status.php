<?php
session_start();
// Fix the database connection path - point to includes/db_config.php instead
require_once '../../includes/db_config.php';
error_log("Payment update started - processing request");

// Include payment mailers with error handling
$studentMailerAvailable = false;
$alumniMailerAvailable = false;

if (file_exists('../mail/payment_mailer.php')) {
    try {
        require_once '../mail/payment_mailer.php';
        $studentMailerAvailable = function_exists('sendPaymentConfirmationEmail');
    } catch (Exception $e) {
        error_log("Error loading payment mailer: " . $e->getMessage());
    }
} else {
    error_log("Warning: payment_mailer.php not found");
}

if (file_exists('../mail/alumni_mailer.php')) {
    try {
        require_once '../mail/alumni_mailer.php';
        $alumniMailerAvailable = function_exists('sendAlumniPaymentConfirmationEmail');
    } catch (Exception $e) {
        error_log("Error loading alumni payment mailer: " . $e->getMessage());
    }
} else {
    error_log("Warning: alumni_mailer.php not found");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the payment information from the POST data
    $jis_id = isset($_POST['jis_id']) ? $_POST['jis_id'] : '';
    $payment_id = isset($_POST['payment_id']) ? $_POST['payment_id'] : '';
    $payment_status = isset($_POST['payment_status']) ? $_POST['payment_status'] : '';
    $amount = isset($_POST['amount']) ? $_POST['amount'] : '';
    $is_alumni = isset($_POST['alumni']) && $_POST['alumni'] == '1';
    
    // Debug received data
    error_log("Received payment data: jis_id=$jis_id, payment_id=$payment_id, status=$payment_status, amount=$amount, alumni=$is_alumni");
    
    // Validate the data
    if (empty($jis_id) || empty($payment_id) || empty($payment_status)) {
        error_log("Missing required fields in payment update");
        echo json_encode(['success' => false, 'message' => 'Missing required fields']);
        exit;
    }
    
    try {
        // Check database connection
        if (!$conn) {
            throw new Exception("Database connection failed");
        }
        
        error_log("Database connection successful, proceeding with payment update");
        
        // Update the payment status in the database
        // Set timezone to IST before creating date
        date_default_timezone_set('Asia/Kolkata');
        $payment_date = date('Y-m-d H:i:s');
        
        if ($is_alumni) {
            // ALUMNI PAYMENT PROCESSING
            
            // Check if the table exists and has the expected structure
            $table_check = $conn->query("SHOW TABLES LIKE 'alumni_registrations'");
            if ($table_check->num_rows == 0) {
                error_log("Error: 'alumni_registrations' table does not exist");
                echo json_encode(['success' => false, 'message' => 'Database schema error: alumni_registrations table not found']);
                exit;
            }
            
            // Check if the specified jis_id exists in the alumni table
            $check_query = $conn->prepare("SELECT jis_id FROM alumni_registrations WHERE jis_id = ?");
            $check_query->bind_param("s", $jis_id);
            $check_query->execute();
            $check_result = $check_query->get_result();
            
            if ($check_result->num_rows == 0) {
                error_log("Error: No record found with jis_id = $jis_id in alumni_registrations table");
                echo json_encode(['success' => false, 'message' => 'No alumni registration record found for this JIS ID']);
                exit;
            }
            
            // Prepare and execute the update query for alumni
            $query = "UPDATE alumni_registrations SET 
                      payment_status = 'Paid',
                      payment_id = ?,
                      amount_paid = ?,
                      payment_date = ?
                    WHERE jis_id = ?";
            
            // Log the query and parameters for debugging
            error_log("Executing alumni query: $query | Parameters: $payment_id, $amount, $payment_date, $jis_id");
            
            $stmt = $conn->prepare($query);
            if (!$stmt) {
                error_log("Prepare failed for alumni: " . $conn->error);
                echo json_encode(['success' => false, 'message' => 'Database prepare error: ' . $conn->error]);
                exit;
            }
            
            $stmt->bind_param("ssss", $payment_id, $amount, $payment_date, $jis_id);
            $result = $stmt->execute();
            
            if (!$result) {
                error_log("Execute failed for alumni: " . $stmt->error);
                echo json_encode(['success' => false, 'message' => 'Database execute error: ' . $stmt->error]);
                exit;
            }
            
            // Check if any rows were affected
            error_log("Updated alumni rows: " . $stmt->affected_rows);
            
            if ($stmt->affected_rows > 0) {
                // Record successful payment in payment_attempts table for tracking
                try {
                    $payment_attempt = $conn->prepare("INSERT INTO payment_attempts (registration_id, status, payment_id, attempt_time, ip_address, is_alumni) VALUES (?, 'completed', ?, NOW(), ?, 1)");
                    $ip = $_SERVER['REMOTE_ADDR'];
                    $payment_attempt->bind_param("sss", $jis_id, $payment_id, $ip);
                    $payment_attempt->execute();
                } catch (Exception $e) {
                    error_log("Warning: Could not record successful alumni payment attempt: " . $e->getMessage());
                    // Continue since this is not critical
                }
                
                // Payment updated successfully
                if ($payment_status === 'SUCCESS') {
                    error_log("Alumni payment successful, fetching alumni details for email");
                    
                    // Fetch alumni details for the email
                    $query = "SELECT alumni_name, email, department, mobile, passout_year, current_organization 
                            FROM alumni_registrations 
                            WHERE jis_id = ?";
                    
                    $stmt = $conn->prepare($query);
                    $stmt->bind_param("s", $jis_id);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    $alumni = $result->fetch_assoc();
                    
                    if ($alumni) {
                        // Prepare data for alumni email
                        $emailData = [
                            'alumni_name' => $alumni['alumni_name'],
                            'email' => $alumni['email'],
                            'jis_id' => $jis_id,
                            'department' => $alumni['department'],
                            'payment_id' => $payment_id,
                            'amount' => $amount,
                            'payment_date' => $payment_date,
                            'mobile' => $alumni['mobile'],
                            'passout_year' => $alumni['passout_year'],
                            'current_organization' => $alumni['current_organization']
                        ];
                        
                        $emailSent = false;
                        // Try to send confirmation email if alumni mailer is available
                        if ($alumniMailerAvailable) {
                            error_log("Sending alumni confirmation email to " . $alumni['email']);
                            try {
                                $emailSent = sendAlumniPaymentConfirmationEmail($emailData);
                                error_log("Alumni email sent result: " . ($emailSent ? "Success" : "Failed"));
                            } catch (Exception $e) {
                                error_log("Alumni email sending error: " . $e->getMessage());
                            }
                        } else {
                            error_log("Alumni payment mailer not available, email not sent");
                        }
                        
                        echo json_encode([
                            'success' => true, 
                            'message' => 'Alumni payment status updated successfully', 
                            'email_sent' => $emailSent
                        ]);
                    } else {
                        // Alumni record not found
                        error_log("Alumni details not found, but payment was updated");
                        echo json_encode([
                            'success' => true, 
                            'message' => 'Alumni payment status updated successfully, but details not found for email'
                        ]);
                    }
                } else {
                    error_log("Alumni payment status updated with status: $payment_status");
                    echo json_encode(['success' => true, 'message' => 'Alumni payment status updated successfully']);
                }
            } else {
                // No rows updated but no error was thrown either
                error_log("No alumni rows updated for $jis_id but operation completed");
                echo json_encode(['success' => false, 'message' => 'No alumni payment record found for this ID']);
            }
        } else {
            // REGULAR STUDENT PAYMENT PROCESSING
            
            // CRITICAL FIX: Check if the table exists and has the expected structure
            $table_check = $conn->query("SHOW TABLES LIKE 'registrations'");
            if ($table_check->num_rows == 0) {
                error_log("Error: 'registrations' table does not exist");
                echo json_encode(['success' => false, 'message' => 'Database schema error: registrations table not found']);
                exit;
            }
            
            // Check if the specified jis_id exists in the table
            $check_query = $conn->prepare("SELECT jis_id FROM registrations WHERE jis_id = ?");
            $check_query->bind_param("s", $jis_id);
            $check_query->execute();
            $check_result = $check_query->get_result();
            
            if ($check_result->num_rows == 0) {
                error_log("Error: No record found with jis_id = $jis_id in registrations table");
                echo json_encode(['success' => false, 'message' => 'No registration record found for this JIS ID']);
                exit;
            }
            
            // Prepare and execute the update query
            $query = "UPDATE registrations SET 
                      payment_status = 'Paid',
                      payment_id = ?,
                      amount_paid = ?,
                      payment_date = ?
                    WHERE jis_id = ?";
            
            // Log the query and parameters for debugging
            error_log("Executing query: $query | Parameters: $payment_id, $amount, $payment_date, $jis_id");
            
            $stmt = $conn->prepare($query);
            if (!$stmt) {
                error_log("Prepare failed: " . $conn->error);
                echo json_encode(['success' => false, 'message' => 'Database prepare error: ' . $conn->error]);
                exit;
            }
            
            $stmt->bind_param("ssss", $payment_id, $amount, $payment_date, $jis_id);
            $result = $stmt->execute();
            
            if (!$result) {
                error_log("Execute failed: " . $stmt->error);
                echo json_encode(['success' => false, 'message' => 'Database execute error: ' . $stmt->error]);
                exit;
            }
            
            // Check if any rows were affected
            error_log("Updated rows: " . $stmt->affected_rows);
            
            if ($stmt->affected_rows > 0) {
                // Record successful payment in payment_attempts table for tracking
                try {
                    $payment_attempt = $conn->prepare("INSERT INTO payment_attempts (registration_id, status, payment_id, attempt_time, ip_address) VALUES (?, 'completed', ?, NOW(), ?)");
                    $ip = $_SERVER['REMOTE_ADDR'];
                    $payment_attempt->bind_param("sss", $jis_id, $payment_id, $ip);
                    $payment_attempt->execute();
                } catch (Exception $e) {
                    error_log("Warning: Could not record successful payment attempt: " . $e->getMessage());
                    // Continue since this is not critical
                }
                
                // Payment updated successfully
                if ($payment_status === 'SUCCESS') {
                    error_log("Payment successful, fetching student details for email");
                    
                    // Fetch student details for the email
                    $query = "SELECT student_name, email, department, mobile 
                            FROM registrations 
                            WHERE jis_id = ?";
                    
                    $stmt = $conn->prepare($query);
                    $stmt->bind_param("s", $jis_id);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    $student = $result->fetch_assoc();
                    
                    if ($student) {
                        // Prepare data for email
                        $emailData = [
                            'student_name' => $student['student_name'],
                            'email' => $student['email'],
                            'jis_id' => $jis_id,
                            'department' => $student['department'],
                            'payment_id' => $payment_id,
                            'amount' => $amount,
                            'payment_date' => $payment_date,
                            'mobile' => $student['mobile']
                        ];
                        
                        $emailSent = false;
                        // Try to send confirmation email if mailer is available
                        if ($studentMailerAvailable) {
                            error_log("Sending confirmation email to " . $student['email']);
                            try {
                                $emailSent = sendPaymentConfirmationEmail($emailData);
                                error_log("Email sent result: " . ($emailSent ? "Success" : "Failed"));
                            } catch (Exception $e) {
                                error_log("Email sending error: " . $e->getMessage());
                            }
                        } else {
                            error_log("Payment mailer not available, email not sent");
                        }
                        
                        echo json_encode([
                            'success' => true, 
                            'message' => 'Payment status updated successfully', 
                            'email_sent' => $emailSent
                        ]);
                    } else {
                        // Student not found
                        error_log("Student details not found, but payment was updated");
                        echo json_encode([
                            'success' => true, 
                            'message' => 'Payment status updated successfully, but student details not found for email'
                        ]);
                    }
                } else {
                    error_log("Payment status updated with status: $payment_status");
                    echo json_encode(['success' => true, 'message' => 'Payment status updated successfully']);
                }
            } else {
                // No rows updated but no error was thrown either
                error_log("No rows updated for $jis_id but operation completed");
                echo json_encode(['success' => false, 'message' => 'No payment record found for this ID']);
            }
        }
        
    } catch (Exception $e) {
        error_log("Payment update critical error: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
} else {
    error_log("Invalid request method: " . $_SERVER['REQUEST_METHOD']);
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>
