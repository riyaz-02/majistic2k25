<?php
include '../../includes/db_config.php';

// Determine the type of registration and get the registration ID from the URL
$registration_id = isset($_GET['jis_id']) ? $_GET['jis_id'] : (isset($_GET['email']) ? $_GET['email'] : null);
$is_inhouse = isset($_GET['jis_id']);
$is_outhouse = isset($_GET['email']);

if (!$registration_id) {
    die("Invalid registration ID.");
}

// Fetch the registration details based on the type
if ($is_inhouse) {
    $query = $conn->prepare("SELECT * FROM registrations WHERE jis_id = ?");
    $query->bind_param("s", $registration_id);
    $amount = 250; // Amount for inhouse registration
} else if ($is_outhouse) {
    $query = $conn->prepare("SELECT * FROM registrations_outhouse WHERE email = ?");
    $query->bind_param("s", $registration_id);
    $amount = 1000; // Amount for outhouse registration
} else {
    die("Invalid registration type.");
}

$query->execute();
$result = $query->get_result();
$registration = $result->fetch_assoc();

if (!$registration) {
    die("Registration not found.");
}

// Check if payment is already done
if ($registration['payment_status'] == 'Paid') {
    $payment_done = true;
    $payment_id = $registration['payment_id'];
    $amount_paid = $registration['amount_paid'];
    $payment_date = $registration['payment_date'];
} else {
    $payment_done = false;

    // Razorpay API credentials
    $keyId = "rzp_test_5y6HDO2HsDx5lK"; // Replace with your Razorpay Key ID
    $keySecret = "sOQvnTPi8LdXe8JYYv0eGF2P"; // Replace with your Razorpay Key Secret

    // Create an order using Razorpay API
    $api_url = "https://api.razorpay.com/v1/orders";
    $amount_in_paise = $amount * 100; // Amount in paise (e.g., 50000 paise = 500 INR)
    $currency = "INR";

    $data = [
        'amount' => $amount_in_paise,
        'currency' => $currency,
        'receipt' => $registration_id,
        'payment_capture' => 1 // Auto capture
    ];

    $ch = curl_init($api_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_USERPWD, $keyId . ':' . $keySecret);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));

    $response = curl_exec($ch);
    if (curl_errno($ch)) {
        die("Curl error: " . curl_error($ch));
    }
    curl_close($ch);

    $order = json_decode($response);
    if (json_last_error() !== JSON_ERROR_NONE) {
        die("Failed to parse JSON response.");
    }

    if (!isset($order->id)) {
        die("Failed to create Razorpay order.");
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment</title>
    <?php include '../../includes/links.php'; ?>
    <link rel="stylesheet" href="../../style.css"> <!-- Link to your custom CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
    <style>
        .payment-box {
            background-color:rgb(255, 255, 255);
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            text-align: center;
            width: 400px;
            margin: 0 auto;
        }
        .payment-box img {
            margin: 0 auto;
            width: 100px;
            margin-bottom: 20px;
        }
        .payment-box table {
            width: 100%;
            margin-bottom: 20px;
            border-collapse: collapse;
        }
        .payment-box th, .payment-box td {
            padding: 10px;
            border: 1px solid #ddd;
        }
        .payment-box th {
            background-color: #f4f4f4;
        }
        .payment-box p {
            margin: 10px 0;
        }
        .payment-box button {
            padding: 10px 20px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .payment-box button:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body class="bg-gray-100 flex justify-center items-center h-screen">
    <div class="payment-box" id="payment-box">
        
        <img src="https://i.ibb.co/jkxgTqcz/majisticlogob.png" alt="maJIStic Logo">
        <p text-align="center">Loading...</p>
        <p text-align="center">Please do not refresh the page.</p>
        <?php if ($payment_done): ?>
            <h2 class="text-2xl font-bold mb-6">Payment Successful!</h2>
            <table>
                <tr>
                    <th>Registration ID</th>
                    <td><?php echo htmlspecialchars($registration_id); ?></td>
                </tr>
                <tr>
                    <th>Payment Status</th>
                    <td>Paid</td>
                </tr>
                <tr>
                    <th>Amount</th>
                    <td><?php echo htmlspecialchars($amount_paid); ?> INR</td>
                </tr>
                <tr>
                    <th>Payment ID</th>
                    <td><?php echo htmlspecialchars($payment_id); ?></td>
                </tr>
                <tr>
                    <th>Payment Date</th>
                    <td><?php echo htmlspecialchars($payment_date); ?></td>
                </tr>
            </table>
            <p>Thank you for the payment, see you at the event.</p>
            <p>For any query, contact maJIStic support.</p>
            <p>Please note the payment ID for future reference.</p>
            <button onclick="window.location.href='../../index.php'">Back to Home</button>
        <?php else: ?>
            <script>
                var paymentDate = new Date().toLocaleString('en-IN', { timeZone: 'Asia/Kolkata' });
                var options = {
                    "key": "<?php echo htmlspecialchars($keyId); ?>", // Enter the Key ID generated from the Dashboard
                    "amount": "<?php echo htmlspecialchars($order->amount); ?>", // Amount is in currency subunits. Default currency is INR. Hence, 50000 refers to 500 INR
                    "currency": "INR",
                    "name": "maJIStic 2k25",
                    "description": "Payment for Event Registration",
                    "image": "https://i.ibb.co/jkxgTqcz/majisticlogob.png", // Replace with your logo
                    "order_id": "<?php echo htmlspecialchars($order->id); ?>", // Pass the order ID generated by Razorpay
                    "handler": function (response){
                        // Update payment status in the database
                        var xhr = new XMLHttpRequest();
                        xhr.open("POST", "update_payment_status.php", true);
                        xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
                        xhr.onreadystatechange = function () {
                            if (xhr.readyState === 4) {
                                if (xhr.status === 200) {
                                    document.getElementById('payment-box').innerHTML = `
                                        <img src="https://i.ibb.co/jkxgTqcz/majisticlogob.png" alt="maJIStic Logo">
                                        <h2 class="text-2xl font-bold mb-6">Payment Successful!</h2>
                                        <table>
                                            <tr>
                                                <th>Registration ID</th>
                                                <td><?php echo htmlspecialchars($registration_id); ?></td>
                                            </tr>
                                            <tr>
                                                <th>Payment Status</th>
                                                <td>Paid</td>
                                            </tr>
                                            <tr>
                                                <th>Amount</th>
                                                <td><?php echo htmlspecialchars($order->amount / 100); ?> INR</td>
                                            </tr>
                                            <tr>
                                                <th>Payment ID</th>
                                                <td>${response.razorpay_payment_id}</td>
                                            </tr>
                                             <tr>
                                                <th>Payment Date</th>
                                                <td>${paymentDate}</td>
                                            </tr>
                                        </table>
                                        <p>Thank you for the payment, see you at the event.</p>
                                        <p>For any query, contact maJIStic support.</p>
                                        <p>Please note the payment ID for future reference.</p>
                                        <button onclick="window.location.href='../../index.php'">Back to Home</button>
                                    `;
                                } else {
                                    alert("Failed to update payment status. Please contact support.");
                                }
                            }
                        };
                        xhr.send("registration_id=<?php echo htmlspecialchars($registration_id); ?>&payment_id=" + response.razorpay_payment_id + "&amount_paid=" + <?php echo htmlspecialchars($order->amount / 100); ?> + "&payment_date=" + paymentDate + "&is_inhouse=<?php echo $is_inhouse ? '1' : '0'; ?>");                    },
                    "theme": {
                        "color": "#F37254"
                    }
                };

                // Automatically open Razorpay payment form
                var rzp1 = new Razorpay(options);
                rzp1.open();

                // Prevent page reload
                window.addEventListener('beforeunload', function (e) {
                    e.preventDefault();
                    e.returnValue = '';
                });
            </script>
        <?php endif; ?>
    </div>
</body>
</html>
