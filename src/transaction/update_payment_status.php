<?php
include '../../includes/db_config.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/../../vendor/autoload.php'; // Include the PHPMailer autoload file

// Set the time zone to IST
$conn->query("SET time_zone = '+05:30';");

$registration_id = $_POST['registration_id'];
$payment_id = $_POST['payment_id'];
$amount_paid = $_POST['amount_paid'];
$payment_date = $_POST['payment_date'];
$is_inhouse = $_POST['is_inhouse'];

// Determine the table based on registration type
$table = $is_inhouse ? 'registrations' : 'registrations_outhouse';

// Update payment status in the database
$query = $conn->prepare("UPDATE $table SET payment_status = 'Paid', payment_id = ?, amount_paid = ?, payment_date = ? WHERE " . ($is_inhouse ? "jis_id" : "email") . " = ?");
$query->bind_param("ssss", $payment_id, $amount_paid, $payment_date, $registration_id);

if ($query->execute()) {
    // Fetch user details
    $user_query = $conn->prepare("SELECT * FROM $table WHERE " . ($is_inhouse ? "jis_id" : "email") . " = ?");
    $user_query->bind_param("s", $registration_id);
    $user_query->execute();
    $user_result = $user_query->get_result();
    $user = $user_result->fetch_assoc();

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
    $mail->setFrom('priyanshunayan1150@gmail.com', 'maJIStic 2k25');
    $mail->addAddress($user['email']);     // Add a recipient

    // Content
    $mail->isHTML(true);                                  // Set email format to HTML
    $mail->Subject = 'Payment Confirmation for maJIStic 2k25';
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
                    <p>Dear ' . $user['student_name'] . ',</p>
                    <p>Thank you for your payment for maJIStic 2k25!</p>
                    <p>Your payment details:</p>
                    <table class="details-table">
                        <tr>
                            <th>Registration ID</th>
                            <td>' . $registration_id . '</td>
                        </tr>
                        <tr>
                            <th>Payment Status</th>
                            <td>Paid</td>
                        </tr>
                        <tr>
                            <th>Amount</th>
                            <td>' . $amount_paid . ' INR</td>
                        </tr>
                        <tr>
                            <th>Payment ID</th>
                            <td>' . $payment_id . '</td>
                        </tr>
                        <tr>
                            <th>Payment Date</th>
                            <td>' . $payment_date . '</td>
                        </tr>
                    </table>
                    <p style="margin-top: 20px;">Thank you for the payment, see you at the event.</p>
                    <p>For any query, contact maJIStic support.</p>
                    <p>Please note the payment ID for future reference.</p>
                    <p>Regards,<br>maJIStic team<br>JIS College of Engineering</p>
                </div>
                <div class="email-footer">
                    <p>&copy; 2025 maJIStic. All rights reserved.</p>
                </div>
            </div>
        </body>
        </html>';
    $mail->AltBody = 'Dear ' . $user['student_name'] . ', Thank you for your payment for maJIStic 2k25! Your payment details: Registration ID: ' . $registration_id . ', Payment Status: Paid, Amount: ' . $amount_paid . ' INR, Payment ID: ' . $payment_id . ', Payment Date: ' . $payment_date . '. Thank you for the payment, see you at the event. For any query, contact maJIStic support. Please note the payment ID for future reference. Regards, maJIStic team, JIS College of Engineering';

    $mail->send();
    } catch (Exception $e) {
        error_log("Payment confirmation email could not be sent. Mailer Error: {$mail->ErrorInfo}");
    }

    echo "Payment status updated successfully.";
} else {
    echo "Failed to update payment status.";
}

$query->close();
$conn->close();
?>
