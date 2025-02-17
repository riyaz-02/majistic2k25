<?php
include __DIR__ . '/../../includes/db_config.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/../../vendor/autoload.php'; // Include the PHPMailer autoload file

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

    // Check if the jis_id, mobile, email, or roll_no already exists
    $check_query = $conn->prepare("
        SELECT 
            jis_id, mobile, email, roll_no, payment_status, student_name, registration_date 
        FROM 
            registrations 
        WHERE 
            jis_id = ? OR mobile = ? OR email = ? OR roll_no = ?
    ");
    $check_query->bind_param("ssss", $jis_id, $mobile, $email, $roll_no);
    $check_query->execute();
    $result = $check_query->get_result();

    if ($result->num_rows > 0) {
        $existing_fields = [];
        $already_registered = false;
        $payment_pending = false;
        $student_name = "";
        $registration_date = "";

        while ($row = $result->fetch_assoc()) {
            if ($row['jis_id'] === $jis_id) {
                if ($row['mobile'] !== $mobile) {
                    $message = "Mobile number Mismatch with your entry on " . $row['registration_date'] . ". Please enter correct mobile number.";
                    $already_registered = true;
                    break;
                }
                if ($row['email'] !== $email) {
                    $message = "Email Mismatch with your entry on " . $row['registration_date'] . ". Please enter correct email.";
                    $already_registered = true;
                    break;
                }
                if ($row['roll_no'] !== $roll_no) {
                    $message = "Roll number Mismatch with your entry on " . $row['registration_date'] . ". Please enter correct roll number.";
                    $already_registered = true;
                    break;
                }
                if ($row['payment_status'] == 'Not Paid') {
                    $payment_pending = true;
                    $student_name = $row['student_name'];
                    $registration_date = $row['registration_date'];
                } else {
                    $already_registered = true;
                    $existing_fields[] = "JIS ID";
                }
            } else {
                if ($row['mobile'] === $mobile) $existing_fields[] = "Mobile Number";
                if ($row['email'] === $email) $existing_fields[] = "Email";
                if ($row['roll_no'] === $roll_no) $existing_fields[] = "Roll Number";
            }
        }

        if ($payment_pending) {
            $message = "Dear $student_name, You have already registered for maJIStic 2k25 on $registration_date but You have not made the payment. Kindly make the payment to get entry.";
            $registration_success = true;
        } elseif ($already_registered && empty($message)) {
            $message = "Registration failed! " . implode(" and ", $existing_fields) . " already registered for the event.";
        }
    } else {
        // Prepare an SQL statement
        $stmt = $conn->prepare("INSERT INTO registrations (student_name, gender, jis_id, mobile, email, roll_no, department, inhouse_competition, competition_name) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssssss", $student_name, $gender, $jis_id, $mobile, $email, $roll_no, $department, $inhouse_competition, $competition);

        if ($stmt->execute()) {
            $message = "Thank You for your Interest. Kindly make the Payment to Enjoy the event.";
            $registration_success = true;

            // Send confirmation email
            $mail = new PHPMailer(true);
            try {
                //Server settings
                $mail->isSMTP();                                            // Send using SMTP
                $mail->Host       = 'smtp.gmail.com';                     // Set the SMTP server to send through
                $mail->SMTPAuth   = true;                                   // Enable SMTP authentication
                $mail->Username   = 'priyanshunayan1150@gmail.com';                // SMTP username
                $mail->Password   = 'nrsbkynbvqkpgjhk';                       // SMTP password
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;         // Enable TLS encryption
                $mail->Port       = 587;                                    // TCP port to connect to

                //Recipients
                $mail->setFrom('priyanshunayan1150@gmail.com', 'Event Registration');
                $mail->addAddress($email);     // Add a recipient

                // Content
                $mail->isHTML(true);                                  // Set email format to HTML
                $mail->Subject = 'Event Registration Confirmation [TRIAL]';
                $mail->Body    = '
                    <html>
                    <head>
                        <style>
                            .email-container {
                                font-family: Arial, sans-serif;
                                line-height: 1.6;
                            }
                            .email-header {
                                background-color: #f2f2f2;
                                padding: 10px;
                                text-align: center;
                            }
                            .email-body {
                                padding: 20px;
                            }
                            .email-footer {
                                background-color: #f2f2f2;
                                padding: 10px;
                                text-align: center;
                            }
                            .details-table {
                                width: 100%;
                                border-collapse: collapse;
                                margin-top: 20px;
                            }
                            .details-table th, .details-table td {
                                border: 1px solid #ddd;
                                padding: 8px;
                                text-align: left;
                            }
                            .details-table th {
                                background-color: #f4f4f4;
                            }
                        </style>
                    </head>
                    <body>
                        <div class="email-container">
                            <div class="email-header">
                                <img src="https://i.ibb.co/rKWPJMBS/majisticlogo.png" alt="maJIStic Logo" width="100">
                            </div>
                            <div class="email-body">
                                <p>Dear ' . $student_name . ',</p>
                                <p>Thank you for registering for maJIStic 2k25!</p>
                                <p>Your details:</p>
                                <table class="details-table">
                                    <tr>
                                        <th>Name</th>
                                        <td>' . $student_name . '</td>
                                    </tr>
                                    <tr>
                                        <th>JIS ID</th>
                                        <td>' . $jis_id . '</td>
                                    </tr>
                                    <tr>
                                        <th>Mobile</th>
                                        <td>' . $mobile . '</td>
                                    </tr>
                                    <tr>
                                        <th>Email</th>
                                        <td>' . $email . '</td>
                                    </tr>
                                    <tr>
                                        <th>Roll No</th>
                                        <td>' . $roll_no . '</td>
                                    </tr>
                                    <tr>
                                        <th>Gender</th>
                                        <td>' . $gender . '</td>
                                    </tr>
                                    <tr>
                                        <th>Department</th>
                                        <td>' . $department . '</td>
                                    </tr>
                                    <tr>
                                        <th>Inhouse Competition</th>
                                        <td>' . $inhouse_competition . '</td>
                                    </tr>
                                    <tr>
                                        <th>Competition</th>
                                        <td>' . $competition . '</td>
                                    </tr>
                                </table>
                                <p style="margin-top: 20px;">Please make payment if not done. You can check status of your registration <a href="https://majistic.com/registration_status">Here</a>.</p>
                                <p>You will receive your ticket for the event 3 days before the event, provided payment is successful.</p>
                                <p>For any queries, contact maJIStic support.</p>
                                <p>We look forward to seeing you at the event.</p>
                                <p>Regards,<br>maJIStic team<br>JIS College of Engineering</p>
                            </div>
                            <div class="email-footer">
                                <p>&copy; 2025 maJIStic. All rights reserved.</p>
                                <p>This is a Trial Mail. It does not confirm your registration at maJIStic 2k25.</p>
                            </div>
                        </div>
                    </body>
                    </html>';
                $mail->AltBody = 'Dear ' . $student_name . ', Thank you for registering for the event! Your details: Name: ' . $student_name . ', JIS ID: ' . $jis_id . ', Mobile: ' . $mobile . ', Email: ' . $email . ', Roll No: ' . $roll_no . ', Payment Transaction ID: ' . $payment_id . '. Best regards, Event Team';

                $mail->send();
            } catch (Exception $e) {
                $message = "Registration successful, but email could not be sent. Mailer Error: {$mail->ErrorInfo}";
            }
        } else {
            $message = "Registration failed! Please try again.";
        }

        // Close the statement
        $stmt->close();
    }
    // Close the check query
    $check_query->close();
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
