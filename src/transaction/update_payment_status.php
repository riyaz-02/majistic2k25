<?php
session_start();
// Fix the database connection path - point to includes/db_config.php instead
require_once '../../includes/db_config.php';
error_log("Payment update started - processing request");

// Include payment mailer only if needed, with error handling
$mailerAvailable = false;
if (file_exists('../mail/payment_mailer.php')) {
    try {
        require_once '../mail/payment_mailer.php';
        $mailerAvailable = function_exists('sendPaymentConfirmationEmail');
    } catch (Exception $e) {
        error_log("Error loading payment mailer: " . $e->getMessage());
    }
} else {
    error_log("Warning: payment_mailer.php not found");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the payment information from the POST data
    $jis_id = isset($_POST['jis_id']) ? $_POST['jis_id'] : '';
    $payment_id = isset($_POST['payment_id']) ? $_POST['payment_id'] : '';
    $payment_status = isset($_POST['payment_status']) ? $_POST['payment_status'] : '';
    $amount = isset($_POST['amount']) ? $_POST['amount'] : '';
    
    // Debug received data
    error_log("Received payment data: jis_id=$jis_id, payment_id=$payment_id, status=$payment_status, amount=$amount");
    
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
        
        // First check if this is an inhouse or outhouse registration
        $is_inhouse = preg_match('/^JIS\/\d{4}\/\d{4}$/', $jis_id);
        error_log("Registration type: " . ($is_inhouse ? "inhouse" : "outhouse"));
        
        // CRITICAL FIX: Check if the table exists and has the expected structure
        if ($is_inhouse) {
            // This is an inhouse registration (JIS ID format)
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
        } else {
            // This is an outhouse registration (Email format)
            $table_check = $conn->query("SHOW TABLES LIKE 'registrations_outhouse'");
            if ($table_check->num_rows == 0) {
                error_log("Error: 'registrations_outhouse' table does not exist");
                echo json_encode(['success' => false, 'message' => 'Database schema error: registrations_outhouse table not found']);
                exit;
            }
            
            // Check if the specified email exists in the table
            $check_query = $conn->prepare("SELECT email FROM registrations_outhouse WHERE email = ?");
            $check_query->bind_param("s", $jis_id);
            $check_query->execute();
            $check_result = $check_query->get_result();
            
            if ($check_result->num_rows == 0) {
                error_log("Error: No record found with email = $jis_id in registrations_outhouse table");
                echo json_encode(['success' => false, 'message' => 'No registration record found for this email']);
                exit;
            }
            
            // Prepare and execute the update query
            $query = "UPDATE registrations_outhouse SET 
                      payment_status = 'Paid',
                      payment_id = ?,
                      amount_paid = ?,
                      payment_date = ?
                    WHERE email = ?";
        }
        
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
                if ($is_inhouse) {
                    $query = "SELECT student_name, email, department, mobile, roll_no 
                            FROM registrations 
                            WHERE jis_id = ?";
                } else {
                    $query = "SELECT student_name, email, college as department, mobile, '' as roll_no 
                            FROM registrations_outhouse 
                            WHERE email = ?";
                }
                
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
                        'mobile' => $student['mobile'],
                        'roll_no' => $student['roll_no']
                    ];
                    
                    $emailSent = false;
                    // Try to send confirmation email if mailer is available
                    if ($mailerAvailable) {
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
        
    } catch (Exception $e) {
        error_log("Payment update critical error: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
} else {
    error_log("Invalid request method: " . $_SERVER['REQUEST_METHOD']);
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>
