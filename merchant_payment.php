<?php
session_start();
include 'includes/db_config.php';

// Get the JIS_ID from the URL if provided
$jis_id = isset($_GET['jis_id']) ? $_GET['jis_id'] : null;
$payment_id = isset($_POST['payment_id']) ? $_POST['payment_id'] : null;

$show_payment_form = !$payment_id; // Show payment form if no payment_id is received
$payment_success = false;

if ($payment_id && $_SERVER["REQUEST_METHOD"] == "POST") {
    // Handle payment verification and database update
    $amount_paid = isset($_POST['amount_paid']) ? $_POST['amount_paid'] / 100 : 0;
    $payment_date = isset($_POST['payment_date']) ? $_POST['payment_date'] : date('Y-m-d H:i:s');

    // Update payment status in database
    $query = $conn->prepare("UPDATE merchant_pay SET payment_id = ?, amount_paid = ?, payment_date = ?, status = 'completed' WHERE jis_id = ?");
    $query->bind_param("sdss", $payment_id, $amount_paid, $payment_date, $_POST['jis_id']);
    
    if ($query->execute()) {
        $payment_success = true;
        $_SESSION['order_details'] = [
            'jis_id' => $_POST['jis_id'],
            'total_amount' => $amount_paid,
            'payment_id' => $payment_id
        ];
    } else {
        die("Error updating payment status: " . $conn->error);
    }
} elseif ($jis_id) {
    // Fetch the order details based on JIS_ID
    $query = $conn->prepare("SELECT * FROM merchant_pay WHERE jis_id = ?");
    $query->bind_param("s", $jis_id);
    $query->execute();
    $result = $query->get_result();
    $order = $result->fetch_assoc();

    if (!$order) {
        die("Order not found.");
    }

    // Razorpay API credentials
    $keyId = "rzp_test_5y6HDO2HsDx5lK";
    $keySecret = "sOQvnTPi8LdXe8JYYv0eGF2P";

    // Create an order using Razorpay API
    $api_url = "https://api.razorpay.com/v1/orders";
    $amount = $order['total_amount'] * 100; // Amount in paise
    $currency = "INR";

    $data = [
        'amount' => $amount,
        'currency' => $currency,
        'receipt' => $jis_id,
        'payment_capture' => 1
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

    $orderResponse = json_decode($response);
    if (json_last_error() !== JSON_ERROR_NONE) {
        die("Failed to parse JSON response.");
    }

    if (!isset($orderResponse->id)) {
        die("Failed to create Razorpay order: " . $response);
    }

    // Store order details in session
    $_SESSION['order_details'] = [
        'jis_id' => $jis_id,
        'total_amount' => $order['total_amount'],
        'razorpay_order_id' => $orderResponse->id
    ];
} else {
    die("JIS ID is required.");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment - maJIStic 2k25</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <?php include 'includes/links.php'; ?>
    <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <?php if ($payment_success): ?>
        <!-- Success Display -->
        <div class="container py-5 text-center">
            <div class="payment-card">
                <h1 class="text-success"><i class="fas fa-check-circle me-2"></i>Payment Successful!</h1>
                <p>Thank you for your purchase!</p>
                <div class="order-details mt-4">
                    <p><strong>JIS ID:</strong> <?php echo htmlspecialchars($_SESSION['order_details']['jis_id']); ?></p>
                    <p><strong>Total Amount:</strong> ₹<?php echo htmlspecialchars($_SESSION['order_details']['total_amount']); ?></p>
                    <p><strong>Payment ID:</strong> <?php echo htmlspecialchars($_SESSION['order_details']['payment_id']); ?></p>
                </div>
                <a href="merchandise.php" class="btn btn-primary mt-4">Back to Merchandise</a>
            </div>
        </div>
    <?php else: ?>
        <!-- Payment Form -->
        <header class="container page-header">
            <h1><i class="fas fa-credit-card me-2"></i>Payment Processing</h1>
            <p>Complete your purchase securely with Razorpay</p>
        </header>

        <div class="container py-5">
            <div class="row justify-content-center">
                <div class="col-md-6">
                    <div class="payment-card">
                        <h3 class="mb-4">Order Summary</h3>
                        <div class="order-details">
                            <p><strong>JIS ID:</strong> <?php echo htmlspecialchars($order['jis_id']); ?></p>
                            <p><strong>Total Amount:</strong> ₹<?php echo htmlspecialchars($order['total_amount']); ?></p>
                        </div>
                        <button id="pay-button" class="pay-btn w-100">
                            <i class="fas fa-credit-card me-2"></i>Proceed to Pay
                        </button>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <?php include 'includes/footer.php'; ?>
    <?php include 'includes/scripts.php'; ?>

    <?php if (!$payment_success): ?>
    <script>
        var options = {
            "key": "<?php echo htmlspecialchars($keyId); ?>",
            "amount": "<?php echo htmlspecialchars($orderResponse->amount); ?>",
            "currency": "INR",
            "name": "maJIStic 2k25",
            "description": "Payment for Merchandise",
            "image": "https://example.com/your_logo.jpg", // Replace with your logo URL
            "order_id": "<?php echo htmlspecialchars($orderResponse->id); ?>",
            "handler": function (response) {
                // Send payment details to this same page for processing
                var form = document.createElement('form');
                form.method = 'POST';
                form.action = 'merchant_payment.php';

                var fields = {
                    'jis_id': '<?php echo htmlspecialchars($jis_id); ?>',
                    'payment_id': response.razorpay_payment_id,
                    'amount_paid': '<?php echo htmlspecialchars($orderResponse->amount); ?>',
                    'payment_date': new Date().toISOString()
                };

                for (var key in fields) {
                    var input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = key;
                    input.value = fields[key];
                    form.appendChild(input);
                }

                document.body.appendChild(form);
                form.submit();

                showNotification("Payment successful! Processing...");
            },
            "theme": {
                "color": "#15a88a"
            }
        };

        document.getElementById('pay-button').onclick = function(e) {
            var rzp1 = new Razorpay(options);
            rzp1.open();
            e.preventDefault();
        };

        function showNotification(message) {
            const notification = document.createElement('div');
            notification.className = 'notification';
            notification.textContent = message;
            document.body.appendChild(notification);
            
            setTimeout(() => {
                notification.classList.add('show');
            }, 100);
            
            setTimeout(() => {
                notification.classList.remove('show');
                setTimeout(() => notification.remove(), 300);
            }, 3000);
        }
    </script>
    <?php endif; ?>

    <style>
        :root {
            --primary: #116757;
            --secondary: #15a88a;
            --accent: #f5a623;
            --dark: #121212;
            --light: #ffffff;
            --gray: #f8f9fa;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, var(--dark), rgb(87, 7, 81));
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            color: var(--light);
            position: relative;
        }

        .page-header {
            background: linear-gradient(90deg, rgba(195,194,123,1) 11%, rgba(236,237,168,1) 37%, rgba(205,109,213,0.5410539215686274) 95%);
            padding: 3rem 0;
            margin-top: 20px;
            text-align: center;
            border-radius: 0.5rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            color: black;
        }

        .payment-card {
            background: rgba(255, 255, 255, 0.07);
            border-radius: 1rem;
            padding: 2.5rem;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .payment-card h3 {
            color: var(--light);
            font-weight: 700;
            margin-bottom: 1.5rem;
        }

        .order-details p {
            margin-bottom: 0.8rem;
            color: rgba(255, 255, 255, 0.8);
        }

        .order-details strong {
            color: var(--light);
        }

        .pay-btn {
            background: linear-gradient(90deg, var(--accent), #d48e1e);
            border: none;
            color: var(--dark);
            font-weight: 700;
            padding: 1rem;
            border-radius: 0.5rem;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-top: 1rem;
        }

        .pay-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
        }

        .notification {
            position: fixed;
            top: 20px;
            right: 20px;
            background: #15a88a;
            color: #fff;
            padding: 1rem 1.5rem;
            border-radius: 0.5rem;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
            opacity: 0;
            transform: translateY(-20px);
            transition: all 0.3s ease;
            z-index: 1000;
        }

        .notification.show {
            opacity: 1;
            transform: translateY(0);
        }
    </style>
</body>
</html>