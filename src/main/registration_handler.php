<?php
include __DIR__ . '/../../includes/db_config.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/../../vendor/autoload.php'; // Include the PHPMailer autoload file

// Include the registration mailer with error handling
$mailerAvailable = false;
if (file_exists(__DIR__ . '/../mail/registration_mailer.php')) {
    try {
        require_once __DIR__ . '/../mail/registration_mailer.php';
        $mailerAvailable = function_exists('sendRegistrationConfirmationEmail');
    } catch (Exception $e) {
        error_log("Error loading registration mailer: " . $e->getMessage());
    }
}

$message = ""; // Variable to store messages
$registration_success = false; // Variable to track registration success
$email_option = false; // Variable to track if user wants to receive email
$payment_id = ""; // Define the payment_id variable

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST" && !isset($_POST['payment_check'])) {
    $student_name = isset($_POST['student_name']) ? $_POST['student_name'] : '';
    $jis_id = isset($_POST['jis_id']) ? $_POST['jis_id'] : '';
    $mobile = isset($_POST['mobile']) ? $_POST['mobile'] : '';
    $email = isset($_POST['email']) ? $_POST['email'] : '';
    $roll_no = isset($_POST['roll_no']) ? $_POST['roll_no'] : '';
    $gender = isset($_POST['gender']) ? $_POST['gender'] : '';
    $department = isset($_POST['department']) ? $_POST['department'] : '';
    $inhouse_competition = isset($_POST['inhouse_competition']) ? $_POST['inhouse_competition'] : null;
    $competition = isset($_POST['competition']) ? $_POST['competition'] : null;

    // First check if the JIS ID already exists
    $jis_check = $conn->prepare("SELECT * FROM registrations WHERE jis_id = ?");
    $jis_check->bind_param("s", $jis_id);
    $jis_check->execute();
    $jis_result = $jis_check->get_result();
    
    if ($jis_result->num_rows > 0) {
        // JIS ID exists - check if payment is pending
        $jis_row = $jis_result->fetch_assoc();
        if ($jis_row['payment_status'] == 'Not Paid') {
            // Retrieve existing details for display
            $student_name = $jis_row['student_name'];
            $email = $jis_row['email'];
            $mobile = $jis_row['mobile'];
            $roll_no = $jis_row['roll_no'];
            $department = $jis_row['department'];
            $inhouse_competition = $jis_row['inhouse_competition'];
            $competition = $jis_row['competition_name'];
            
            $message = "Dear $student_name, you have already registered for maJIStic 2k25 on " . $jis_row['registration_date'] . ". Please complete your payment to proceed.";
            $registration_success = true;
        } else {
            // Already registered and paid
            $message = "Registration failed! This JIS ID has already been registered and payment completed.";
        }
        $jis_check->close();
    } else {
        $jis_check->close();
        
        // If JIS ID is new, check if email or mobile already exists
        $email_check = $conn->prepare("SELECT email FROM registrations WHERE email = ?");
        $email_check->bind_param("s", $email);
        $email_check->execute();
        $email_result = $email_check->get_result();
        
        $mobile_check = $conn->prepare("SELECT mobile FROM registrations WHERE mobile = ?");
        $mobile_check->bind_param("s", $mobile);
        $mobile_check->execute();
        $mobile_result = $mobile_check->get_result();
        
        if ($email_result->num_rows > 0) {
            $message = "Registration failed! This email address is already registered for the event.";
        } elseif ($mobile_result->num_rows > 0) {
            $message = "Registration failed! This mobile number is already registered for the event.";
        } else {
            // All checks passed, proceed with registration
            $stmt = $conn->prepare("INSERT INTO registrations (student_name, gender, jis_id, mobile, email, roll_no, department, inhouse_competition, competition_name) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sssssssss", $student_name, $gender, $jis_id, $mobile, $email, $roll_no, $department, $inhouse_competition, $competition);

            if ($stmt->execute()) {
                $message = "Thank You for your Interest. Kindly make the Payment to Enjoy the event.";
                $registration_success = true;

                // Send email using our new professional template
                if ($mailerAvailable) {
                    // Set timezone to ensure consistent date format
                    date_default_timezone_set('Asia/Kolkata');
                    
                    // Prepare data for email
                    $emailData = [
                        'student_name' => $student_name,
                        'email' => $email,
                        'jis_id' => $jis_id,
                        'department' => $department,
                        'mobile' => $mobile,
                        'roll_no' => $roll_no,
                        'registration_date' => date('Y-m-d H:i:s'),
                        'competition' => isset($competition) ? $competition : '',
                        'gender' => $gender
                    ];
                    
                    try {
                        $emailSent = sendRegistrationConfirmationEmail($emailData);
                        error_log("Registration email to $email " . ($emailSent ? "sent successfully" : "failed to send"));
                    } catch (Exception $e) {
                        error_log("Error sending registration email: " . $e->getMessage());
                    }
                } else {
                    // Fallback to basic email if mailer module not available
                    $mail = new PHPMailer(true);
                    try {
                        // Server settings
                        $mail->isSMTP();
                        $mail->Host       = 'smtp.gmail.com';
                        $mail->SMTPAuth   = true;
                        $mail->Username   = 'priyanshunayan1150@gmail.com';
                        $mail->Password   = 'nrsbkynbvqkpgjhk';
                        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                        $mail->Port       = 587;

                        // Recipients
                        $mail->setFrom('priyanshunayan1150@gmail.com', 'maJIStic');
                        $mail->addAddress($email);

                        // Content
                        $mail->isHTML(true);
                        $mail->Subject = 'maJIStic 2k25 Registration Confirmation';
                        
                        // Use a simple email template as fallback
                        $mail->Body = '
                            <html>
                            <body>
                                <h2>Registration Confirmation</h2>
                                <p>Dear ' . $student_name . ',</p>
                                <p>Thank you for registering for maJIStic 2k25. Please proceed to payment to confirm your participation.</p>
                                <p><a href="https://skriyaz.com/majistic/src/transaction/payment.php?jis_id=' . $jis_id . '">Click here to complete payment</a></p>
                                <p>Regards,<br>maJIStic Team</p>
                                <p style="background-color: #ffeeee; border: 1px solid #ff6b6b; padding: 10px; color: #cc0000; font-weight: bold; text-align: center; margin: 15px 0;">
                                    <strong>IMPORTANT:</strong> College ID is MANDATORY for check-in on event day. No entry without ID.
                                </p>
                            </body>
                            </html>';
                        
                        $mail->send();
                    } catch (Exception $e) {
                        error_log("Email could not be sent. Mailer Error: {$mail->ErrorInfo}");
                    }
                }
            } else {
                $message = "Registration failed! Please try again.";
            }

            $stmt->close();
        }
        
        $email_check->close();
        $mobile_check->close();
    }
}

// Check if the payment form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['payment_check'])) {
    $jis_id = isset($_POST['jis_id']) ? $_POST['jis_id'] : '';
    $roll_no = isset($_POST['roll_no']) ? $_POST['roll_no'] : '';

    $check_payment_query = $conn->prepare("
        SELECT 
            payment_status, student_name, registration_date, payment_id 
        FROM 
            registrations 
        WHERE 
            jis_id = ? AND roll_no = ?
    ");
    $check_payment_query->bind_param("ss", $jis_id, $roll_no);
    $check_payment_query->execute();
    $payment_result = $check_payment_query->get_result();

    if ($payment_result->num_rows > 0) {
        $row = $payment_result->fetch_assoc();
        if ($row['payment_status'] == 'Not Paid') {
            header("Location: ../../transaction/payment.php?jis_id=$jis_id");
            exit();
        } else {
            $message = "Payment already completed for JIS ID: $jis_id.";
            // Add option to send email with registration details
            $email_option = true; // Set the email option to true
            $payment_id = $row['payment_id']; // Get the payment transaction ID
        }
    } else {
        $message = "No registration found for the provided JIS ID and Roll Number.";
    }

    $check_payment_query->close();
}

// Close the connection
$conn->close();
?>
