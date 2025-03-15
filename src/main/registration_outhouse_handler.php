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
    $leader_name = isset($_POST['leader_name']) ? $_POST['leader_name'] : '';
    $gender = isset($_POST['gender']) ? $_POST['gender'] : '';
    $email = isset($_POST['email']) ? $_POST['email'] : '';
    $contact_number = isset($_POST['contact_number']) ? $_POST['contact_number'] : '';
    $college_name = isset($_POST['college_name']) ? $_POST['college_name'] : '';
    $college_id = isset($_POST['college_id']) ? $_POST['college_id'] : '';
    $course_name = isset($_POST['course_name']) ? $_POST['course_name'] : '';
    $competition_name = isset($_POST['competition_name']) ? $_POST['competition_name'] : '';
    $team_name = isset($_POST['team_name']) ? $_POST['team_name'] : '';
    $team_members = isset($_POST['team_members']) ? $_POST['team_members'] : [];
    $team_members_contact = isset($_POST['team_members_contact']) ? $_POST['team_members_contact'] : [];

    // Check if the email or contact_number already exists
    $check_query = $conn->prepare("
        SELECT 
            email, contact_number, payment_status, leader_name, registration_date 
        FROM 
            registrations_outhouse 
        WHERE 
            email = ? OR contact_number = ?
    ");
    $check_query->bind_param("ss", $email, $contact_number);
    $check_query->execute();
    $result = $check_query->get_result();

    if ($result->num_rows > 0) {
        $existing_fields = [];
        $already_registered = false;
        $payment_pending = false;
        $leader_name = "";
        $registration_date = "";

        while ($row = $result->fetch_assoc()) {
            if ($row['email'] === $email) {
                if ($row['contact_number'] !== $contact_number) {
                    $message = "Contact number Mismatch with your entry on " . $row['registration_date'] . ". Please enter correct contact number.";
                    $already_registered = true;
                    break;
                }
                if ($row['payment_status'] == 'Not Paid') {
                    $payment_pending = true;
                    $leader_name = $row['leader_name'];
                    $registration_date = $row['registration_date'];
                } else {
                    $already_registered = true;
                    $existing_fields[] = "Email";
                }
            } else {
                if ($row['contact_number'] === $contact_number) $existing_fields[] = "Contact Number";
            }
        }

        if ($payment_pending) {
            $message = "Dear $leader_name, You have already registered for maJIStic 2k25 on $registration_date but You have not made the payment. Kindly make the payment to get entry.";
            $registration_success = true;
        } elseif ($already_registered && empty($message)) {
            $message = "Registration failed! " . implode(" and ", $existing_fields) . " already registered for the event.";
        }
    } else {
        // Prepare an SQL statement
        $stmt = $conn->prepare("INSERT INTO registrations_outhouse (leader_name, gender, email, contact_number, college_name, college_id, course_name, competition_name, team_name, team_members, team_members_contact) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $team_members_json = json_encode($team_members);
        $team_members_contact_json = json_encode($team_members_contact);
        $stmt->bind_param("sssssssssss", $leader_name, $gender, $email, $contact_number, $college_name, $college_id, $course_name, $competition_name, $team_name, $team_members_json, $team_members_contact_json);

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
                <div style="font-family: Arial, sans-serif; color: #333; padding: 20px; border: 1px solid #ddd; border-radius: 10px;">
                    <div style="text-align: center;">
                        <img src="https://i.postimg.cc/tCKfbtGT/majisticlogo.png" alt="maJIStic Logo" style="max-width: 200px; margin-bottom: 20px;">
                    </div>
                    <h2 style="color: #4CAF50;">Event Registration Confirmation</h2>
                    <p>Dear ' . $leader_name . ',</p>
                    <p>Thank you for registering for maJIStic2k25! <br>Here are your details:</p>
                    <table style="width: 100%; border-collapse: collapse;">
                        <tr>
                            <th style="text-align: left; padding: 8px; background-color: #f2f2f2;">Field</th>
                            <th style="text-align: left; padding: 8px; background-color: #f2f2f2;">Details</th>
                        </tr>
                        <tr>
                            <td style="padding: 8px; border: 1px solid #ddd;">Name</td>
                            <td style="padding: 8px; border: 1px solid #ddd;">' . $leader_name . '</td>
                        </tr>
                        <tr>
                            <td style="padding: 8px; border: 1px solid #ddd;">Email</td>
                            <td style="padding: 8px; border: 1px solid #ddd;">' . $email . '</td>
                        </tr>
                        <tr>
                            <td style="padding: 8px; border: 1px solid #ddd;">Contact Number</td>
                            <td style="padding: 8px; border: 1px solid #ddd;">' . $contact_number . '</td>
                        </tr>
                        <tr>
                            <td style="padding: 8px; border: 1px solid #ddd;">College Name</td>
                            <td style="padding: 8px; border: 1px solid #ddd;">' . $college_name . '</td>
                        </tr>
                        <tr>
                            <td style="padding: 8px; border: 1px solid #ddd;">College ID</td>
                            <td style="padding: 8px; border: 1px solid #ddd;">' . $college_id . '</td>
                        </tr>
                        <tr>
                            <td style="padding: 8px; border: 1px solid #ddd;">Course Name</td>
                            <td style="padding: 8px; border: 1px solid #ddd;">' . $course_name . '</td>
                        </tr>
                        <tr>
                            <td style="padding: 8px; border: 1px solid #ddd;">Competition Name</td>
                            <td style="padding: 8px; border: 1px solid #ddd;">' . $competition_name . '</td>
                        </tr>
                        <tr>
                            <td style="padding: 8px; border: 1px solid #ddd;">Team Name</td>
                            <td style="padding: 8px; border: 1px solid #ddd;">' . $team_name . '</td>
                        </tr>
                        <tr>
                            <td style="padding: 8px; border: 1px solid #ddd;">Team Members</td>
                            <td style="padding: 8px; border: 1px solid #ddd;">' . implode(", ", $team_members) . '</td>
                        </tr>
                                            </table>
                    <p style="margin-top: 20px;">Please make payment if not done. You can check status of your registration <a href="https://majistic.com/registration_status">Here</a>.</p>
                    <p>You will receive your ticket for the event 3 days before the event, provided payment is successful.</p>
                    <p>For any queries, contact maJIStic support.</p>
                    <p>Regards,<br>maJIStic team</p>
                    <div class="email-footer" style="background-color: #f2f2f2; padding: 10px; text-align: center;">
                        <p>&copy; 2025 maJIStic. All rights reserved.</p>
                        <p>This is a Trial Mail. It does not confirm your registration at maJIStic 2k25.</p>
                    </div>
                </div>';
                $mail->AltBody = 'Dear ' . $leader_name . ', Thank you for registering for maJIStic 2k25! Your details: Name: ' . $leader_name . ', Email: ' . $email . ', Contact Number: ' . $contact_number . ', College Name: ' . $college_name . ', College ID: ' . $college_id . ', Course Name: ' . $course_name . ', Competition Name: ' . $competition_name . ', Team Name: ' . $team_name . ', Team Members: ' . implode(", ", $team_members) . ', Payment Transaction ID: ' . $payment_id . '. Please make payment if not done. You can check status of your registration Here. You will receive your ticket for the event 3 days before the event, provided payment is successful. For any queries, contact maJIStic support. Regards, maJIStic team';

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
    $email = isset($_POST['email']) ? $_POST['email'] : '';
    $contact_number = isset($_POST['contact_number']) ? $_POST['contact_number'] : '';

    $check_payment_query = $conn->prepare("
        SELECT 
            payment_status, leader_name, registration_date, payment_id 
        FROM 
            registrations_outhouse 
        WHERE 
            email = ? AND contact_number = ?
    ");
    $check_payment_query->bind_param("ss", $email, $contact_number);
    $check_payment_query->execute();
    $payment_result = $check_payment_query->get_result();

    if ($payment_result->num_rows > 0) {
        $row = $payment_result->fetch_assoc();
        if ($row['payment_status'] == 'Not Paid') {
            header("Location: ../../payment.php?email=$email");
            exit();
        } else {
            $message = "Payment already completed for Email: $email.";
            // Add option to send email with registration details
            $email_option = true; // Set the email option to true
            $payment_id = $row['payment_id']; // Get the payment transaction ID
        }
    } else {
        $message = "No registration found for the provided Email and Contact Number.";
    }

    $check_payment_query->close();
}

// Close the connection
$conn->close();
?>
