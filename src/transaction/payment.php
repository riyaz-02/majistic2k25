<?php
// Set timezone to IST at the beginning of the file
date_default_timezone_set('Asia/Kolkata');
include '../../includes/db_config.php';

// Get the registration ID from the URL
$registration_id = isset($_GET['jis_id']) ? $_GET['jis_id'] : null;
// Check if this is an alumni registration
$is_alumni = isset($_GET['alumni']) && $_GET['alumni'] == '1';

// Get client IP for tracking
function getClientIP() {
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        return $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ipList = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
        foreach ($ipList as $ip) {
            if (filter_var($ip, FILTER_VALIDATE_IP)) {
                return trim($ip);
            }
        }
    }
    return $_SERVER['REMOTE_ADDR'];
}

$client_ip = getClientIP();

if (!$registration_id) {
    die("Invalid registration ID.");
}

// Fetch the registration details
if ($is_alumni) {
    // Alumni registration
    $query = $conn->prepare("SELECT * FROM alumni_registrations WHERE jis_id = ?");
    $query->bind_param("s", $registration_id);
    $amount = 1000; // Set amount for alumni registration
} else {
    // Regular student registration
    $query = $conn->prepare("SELECT * FROM registrations WHERE jis_id = ?");
    $query->bind_param("s", $registration_id);
    $amount = 500; // Amount for inhouse registration
}

$query->execute();
$result = $query->get_result();
$registration = $result->fetch_assoc();

if (!$registration) {
    die("Registration not found.");
}

// For alumni, use different field names
$student_name = $is_alumni ? $registration['alumni_name'] : $registration['student_name'];

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
        'payment_capture' => 1, // Auto capture
        'notes' => [
            'registration_id' => $registration_id,
            'is_alumni' => $is_alumni ? 'Yes' : 'No',
            'client_ip' => $client_ip
        ]
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
    
    // Record order creation in payment_attempts
    try {
        $stmt = $conn->prepare("INSERT INTO payment_attempts (registration_id, registration_type, status, transaction_reference, attempt_time, ip_address, payment_processor) VALUES (?, ?, 'initiated', ?, NOW(), ?, 'Razorpay')");
        $registration_type = $is_alumni ? 'alumni' : 'inhouse';
        $stmt->bind_param("ssss", $registration_id, $registration_type, $order->id, $client_ip);
        $stmt->execute();
    } catch (Exception $e) {
        error_log("Error recording payment attempt: " . $e->getMessage());
        // Continue as this is not a critical error
    }
}

// Check for any previous failed transactions
$failed_transaction = false;
if (!$payment_done) {
    $check_failed = $conn->prepare("SELECT COUNT(*) as failed_count FROM payment_attempts WHERE registration_id = ? AND status = 'failed'");
    $check_failed->bind_param("s", $registration_id);
    $check_failed->execute();
    $failed_result = $check_failed->get_result();
    $failed_data = $failed_result->fetch_assoc();
    $failed_transaction = $failed_data['failed_count'] > 0;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $is_alumni ? 'Alumni' : 'Student'; ?> Payment - maJIStic 2k25</title>
    <?php include '../../includes/links.php'; ?>
    <link rel="stylesheet" href="../../style.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../css/payment.css">
    <style>
        /* Ensure payment loader works properly */
        #payment-loader {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: rgba(0,0,0,0.7);
            z-index: 9999;
            justify-content: center;
            align-items: center;
        }
        
        /* Modern redesign styles */
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, sans-serif;
            background-image: url('../../images/pageback.png');
            background-repeat: repeat-y !important;
            background-size: 100% !important;
            background-position: top center !important;
            background-attachment: initial !important;
            color: #1a202c;
        }
        
        .page-container {
            max-width: 1400px; /* Increased from 1200px */
            margin: 0 auto;
            padding: 2rem 1.5rem;
            display: flex;
            flex-direction: column;
        }
        
        .back-button-container {
            margin-bottom: 2rem;
            width: 100%;
        }
        
        .main-content-container {
            display: flex;
            flex-direction: column;
            gap: 2rem;
            width: 100%;
        }
        
        @media (min-width: 992px) {
            .main-content-container {
                flex-direction: row;
            }
        }
        
        .content-wrapper {
            flex: 1.5;
            width: 100%;
        }
        
        .side-container {
            flex: 1.5;
            width: 100%;
        }
        
        .back-button {
            display: inline-flex;
            align-items: center;
            padding: 0.5rem 1rem;
            background: white;
            border: 1px solid #e2e8f0;
            color: #4a5568;
            border-radius: 8px;
            font-weight: 500;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
            transition: all 0.2s;
            text-decoration: none;
        }
        
        .back-button:hover {
            background: #f9fafb;
            border-color: #cbd5e0;
            transform: translateY(-1px);
        }
        
        .back-button i {
            margin-right: 8px;
        }
        
        .payment-container {
            margin-bottom: 2rem;
            max-width: 100%; /* Allow container to take full width of parent */
        }
        
        .payment-card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            overflow: hidden;
        }
        
        .payment-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 1.5rem;
            background: linear-gradient(135deg,rgb(202, 4, 252) 0%,rgb(67, 0, 112) 100%);
            border-bottom: 1px solid #e2e8f0;
        }
        
        .payment-logo {
            height: 48px;
        }
        
        .badge {
            padding: 0.35rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        
        .badge-primary {
            background: #5a67d8;
            color: white;
        }
        
        .payment-body {
            padding: 1.5rem;
        }
        
        .payment-details {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            margin-bottom: 1.5rem;
        }
        
        .payment-details th, 
        .payment-details td {
            padding: 0.75rem 1rem;
            text-align: left;
            vertical-align: middle;
        }
        
        .payment-details th {
            font-weight: 500;
            color: #4a5568;
            width: 40%;
        }
        
        .payment-details td {
            font-weight: 500;
        }
        
        .payment-details tr:not(:last-child) {
            border-bottom: 1px solid #edf2f7;
        }
        
        .payment-details tr:nth-child(even) {
            background-color: #f9fafb;
        }
        
        .payment-status {
            display: inline-flex;
            align-items: center;
            padding: 0.375rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.875rem;
            font-weight: 600;
        }
        
        .status-paid {
            background-color: #c6f6d5;
            color: #22543d;
        }
        
        .status-pending {
            background-color: #fed7aa;
            color: #7b341e;
        }
        
        .payment-status i {
            margin-right: 6px;
        }
        
        .payment-amount {
            font-size: 2.5rem;
            font-weight: 700;
            color: #1a202c;
            text-align: center;
            margin: 1.5rem 0;
        }
        
        .payment-button {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            padding: 0.875rem 1.5rem;
            background: #5a67d8;
            color: white;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.2s;
            box-shadow: 0 4px 6px rgba(90, 103, 216, 0.2);
        }
        
        .payment-button:hover {
            background: #4c51bf;
            transform: translateY(-1px);
            box-shadow: 0 6px 8px rgba(90, 103, 216, 0.25);
        }
        
        .payment-button:active {
            transform: translateY(0);
        }
        
        .payment-footer {
            flex: 1.5;
            padding: 1.5rem;
            text-align: center;
            background: #f8fafc;
            border-top: 1px solid #e2e8f0;
        }
        
        .secure-badge {
            display: inline-flex;
            align-items: center;
            font-size: 0.875rem;
            color: #718096;
        }
        
        .secure-badge i {
            color: #68d391;
            margin-right: 8px;
        }
        
        /* FAQ Section */
        .faq-section {
            background: white;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            padding: 1.5rem;
            margin-bottom: 2rem;
            width: 100%;
        }
        
        .faq-title {
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 1.25rem;
            color: #2d3748;
        }
        
        .faq-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .faq-list-item {
            border-bottom: 1px solid #edf2f7;
        }
        
        .faq-list-item:last-child {
            border-bottom: none;
        }
        
        .faq-toggle {
            display: flex;
            justify-content: space-between;
            align-items: center;
            width: 100%;
            text-align: left;
            padding: 1rem 1rem;
            background: transparent;
            border: none;
            cursor: pointer;
            font-weight: 500;
            color: #2d3748;
            font-size: 1rem;
            transition: color 0.2s;
        }
        
        .faq-toggle:hover {
            color: #5a67d8;
        }
        
        .faq-toggle-icon {
            font-size: 0.875rem;
            transition: transform 0.2s;
        }
        
        .faq-toggle[aria-expanded="true"] .faq-toggle-icon {
            transform: rotate(180deg);
        }
        
        .faq-content {
            max-height: 0;
            overflow: hidden;
            opacity: 0;
            transition: max-height 0.3s ease, opacity 0.3s ease, padding 0.3s ease;
        }
        
        .faq-content-inner {
            padding: 0 0.5rem;
            padding-bottom: 1rem;
            color: #4a5568;
            line-height: 1.6;
        }
        
        /* Contact Section */
        .contact-section {
            background: white;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            padding: 1.5rem;
            width: 100%;
        }
        
        .contact-section-title {
            font-size: 1.25rem;
            font-weight: 600;
            margin-bottom: 1.25rem;
            color: #2d3748;
        }
        
        .contact-tabs {
            display: flex;
            border-bottom: 1px solid #edf2f7;
            margin-bottom: 1.25rem;
        }
        
        .contact-tab {
            padding: 0.75rem 1.25rem;
            background: transparent;
            border: none;
            border-bottom: 2px solid transparent;
            font-weight: 500;
            color: #718096;
            cursor: pointer;
            transition: all 0.2s;
        }
        
        .contact-tab.active {
            border-color: #5a67d8;
            color: #5a67d8;
        }
        
        .contact-panel {
            display: none;
        }
        
        .contact-panel.active {
            display: block;
        }
        
        .contact-description {
            margin-bottom: 1rem;
            color: #4a5568;
            font-size: 0.95rem;
        }
        
        .contact-cards {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            justify-content: space-between;
        }
        
        .contact-card {
            display: flex;
            align-items: center;
            padding: 1rem;
            background: #f8fafc;
            border-radius: 10px;
            border: 1px solid #edf2f7;
            width: 100%;
            transition: all 0.2s ease;
        }
        
        .contact-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
            border-color: #e2e8f0;
        }
        
        @media (min-width: 768px) {
            .contact-card {
                width: calc(50% - 0.5rem);  /* Take up half the container width minus half the gap */
                margin-bottom: 0.5rem;
            }
            
            /* Ensure the last card centers when there's an odd number of cards */
            .contact-cards > .contact-card:last-child:nth-child(odd) {
                margin-right: auto;
                margin-left: auto;
            }
        }
        
        .contact-card .icon {
            display: flex;
            align-items: center;
            justify-content: center;
            min-width: 42px;
            height: 42px;
            background: #ebf4ff;
            color: #5a67d8;
            border-radius: 8px;
            margin-right: 1rem;
            font-size: 1.1rem;
        }
        
        .contact-card .info {
            flex: 1;
        }
        
        .contact-card .info h4 {
            margin: 0 0 0.25rem 0;
            font-size: 0.9375rem;
            font-weight: 600;
            color: #2d3748;
        }
        
        .contact-card .info a {
            color: #5a67d8;
            text-decoration: none;
            font-size: 0.875rem;
            display: inline-block;
        }
        
        .contact-card .info a:hover {
            text-decoration: underline;
        }
        
        .loader {
            border: 4px solid rgba(0, 0, 0, 0.1);
            border-radius: 50%;
            border-top: 4px solid #5a67d8;
            width: 40px;
            height: 40px;
            animation: spin 1s linear infinite;
            margin: 0 auto;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>

<body>
    <div class="page-container">
        <div class="back-button-container">
            <a href="../../index.php" class="back-button">
                <i class="fas fa-arrow-left"></i> Back to Home
            </a>
        </div>
        
        <div class="main-content-container">
            <div class="content-wrapper">
                <div class="payment-container">
                    <div class="payment-card">
                        <div class="payment-header">
                            <img src="https://i.postimg.cc/02CTRDb2/majisticlogoblack.png" alt="maJIStic Logo" class="payment-logo">
                            <?php if ($is_alumni): ?>
                                <div class="badge badge-primary">Alumni Registration</div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="payment-body">
                            <table class="payment-details">
                                <tr>
                                    <th>Registration ID</th>
                                    <td><?php echo htmlspecialchars($registration_id); ?></td>
                                </tr>
                                <tr>
                                    <th><?php echo $is_alumni ? 'Alumni' : 'Participant'; ?></th>
                                    <td><?php echo htmlspecialchars($student_name); ?></td>
                                </tr>
                                <?php if ($is_alumni && isset($registration['passout_year'])): ?>
                                <tr>
                                    <th>Passout Year</th>
                                    <td><?php echo htmlspecialchars($registration['passout_year']); ?></td>
                                </tr>
                                <?php endif; ?>
                                <?php if ($payment_done): ?>
                                    <tr>
                                        <th>Payment Status</th>
                                        <td>
                                            <span class="payment-status status-paid">
                                                <i class="fas fa-check-circle"></i> Paid
                                            </span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Amount</th>
                                        <td>₹<?php echo htmlspecialchars($amount_paid); ?></td>
                                    </tr>
                                    <tr>
                                        <th>Payment ID</th>
                                        <td><?php echo htmlspecialchars($payment_id); ?></td>
                                    </tr>
                                    <tr>
                                        <th>Payment Date</th>
                                        <td><?php echo htmlspecialchars($payment_date); ?></td>
                                    </tr>
                                <?php else: ?>
                                    <tr>
                                        <th>Payment Status</th>
                                        <td>
                                            <span class="payment-status status-pending">
                                                <i class="fas fa-clock"></i> Pending
                                            </span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Amount</th>
                                        <td>₹<?php echo htmlspecialchars($amount); ?></td>
                                    </tr>
                                <?php endif; ?>
                            </table>

                            <?php if (!$payment_done): ?>
                                <div class="payment-amount">
                                    ₹<?php echo htmlspecialchars($amount); ?>
                                </div>
                                
                                <button type="button" id="rzp-button" class="payment-button">
                                    Pay Now <i class="fas fa-arrow-right ml-2"></i>
                                </button>
                            <?php endif; ?>
                        </div>
                    
                    
                    <div class="payment-footer">
                        <?php if ($payment_done): ?>
                            <p>Your registration is confirmed. An email with details has been sent to you.</p>
                            <button onclick="window.location.href='../../index.php'" class="payment-button">
                                Back to Home
                            </button>
                        <?php else: ?>
                            <div class="secure-badge">
                                <i class="fas fa-lock"></i> Secure payment powered by Razorpay
                            </div>
                        <?php endif; ?>
                    </div>
                    </div>
                </div>
            </div>
            
            <div class="side-container">
                <!-- New FAQ section with button-based toggling -->
                <section class="faq-section">
                    <h2 class="faq-title">Frequently Asked Questions</h2>
                    <ul class="faq-list">
                        <li class="faq-list-item">
                            <button class="faq-toggle" aria-expanded="false" aria-controls="faq1">
                                <span>What happens if my payment fails?</span>
                                <i class="fas fa-chevron-down faq-toggle-icon"></i>
                            </button>
                            <div class="faq-content" id="faq1">
                                <div class="faq-content-inner">
                                    <p>If your payment fails, you can attempt to pay again. Your registration information is saved, and you can complete the payment later. If you continue to face issues, please contact our support team.</p>
                                </div>
                            </div>
                        </li>
                        
                        <li class="faq-list-item">
                            <button class="faq-toggle" aria-expanded="false" aria-controls="faq2">
                                <span>How do I get my receipt after payment?</span>
                                <i class="fas fa-chevron-down faq-toggle-icon"></i>
                            </button>
                            <div class="faq-content" id="faq2">
                                <div class="faq-content-inner">
                                    <p>A receipt will be automatically sent to the email address you provided during registration. You can also check your payment status on the maJIStic website by entering your registration ID.</p>
                                </div>
                            </div>
                        </li>
                        
                        <li class="faq-list-item">
                            <button class="faq-toggle" aria-expanded="false" aria-controls="faq3">
                                <span>Can I get a refund if I can't attend?</span>
                                <i class="fas fa-chevron-down faq-toggle-icon"></i>
                            </button>
                            <div class="faq-content" id="faq3">
                                <div class="faq-content-inner">
                                    <p>We understand that plans can change, but please note that all ticket sales for Majistic 2K25 are final and non-refundable. This helps us ensure a seamless event experience for everyone.</p>
                                </div>
                            </div>
                        </li>
                        
                        <li class="faq-list-item">
                            <button class="faq-toggle" aria-expanded="false" aria-controls="faq4">
                                <span>When will I receive my event pass?</span>
                                <i class="fas fa-chevron-down faq-toggle-icon"></i>
                            </button>
                            <div class="faq-content" id="faq4">
                                <div class="faq-content-inner">
                                    <p>Event passes will be sent to your registered email 3 days before the event. Please make sure to check your email, including spam folders.</p>
                                </div>
                            </div>
                        </li>
                    </ul>
                </section>

                <!-- Consolidated contact section with tabs -->
                <section class="contact-section">
                    <h2 class="contact-section-title">Need Help?</h2>
                    
                    <div class="contact-tabs">
                        <button class="contact-tab active" data-target="tech-team">Tech Team</button>
                        <button class="contact-tab" data-target="support-team">Support Team</button>
                    </div>
                    
                    <div class="contact-panel active" id="tech-team">
                        <p class="contact-description">
                            In case of any technical issues, feel free to contact our Tech Team
                        </p>
                        <div class="contact-cards">
                            <div class="contact-card">
                                <div class="icon">
                                    <i class="fas fa-user-cog"></i>
                                </div>
                                <div class="info">
                                    <h4>Priyanshu Nayan</h4>
                                    <a href="tel:+917004706722">+91 7004706722</a>
                                </div>
                            </div>
                            <div class="contact-card">
                                <div class="icon">
                                    <i class="fas fa-user-cog"></i>
                                </div>
                                <div class="info">
                                    <h4>Sk Riyaz</h4>
                                    <a href="tel:+917029621489">+91 7029621489</a>
                                </div>
                            </div>
                            <div class="contact-card">
                                <div class="icon">
                                    <i class="fas fa-user-cog"></i>
                                </div>
                                <div class="info">
                                    <h4>Ronit Pal</h4>
                                    <a href="tel:+917501005155">+91 7501005155</a>
                                </div>
                            </div>
                            <div class="contact-card">
                                <div class="icon">
                                    <i class="fas fa-user-cog"></i>
                                </div>
                                <div class="info">
                                    <h4>Mohit Kumar</h4>
                                    <a href="tel:+918016804158">+91 8016804158</a>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="contact-panel" id="support-team">
                        <p class="contact-description">
                            For payment and registration support, contact our Support Team
                        </p>
                        <div class="contact-cards">
                            <div class="contact-card">
                                <div class="icon">
                                    <i class="fas fa-headset"></i>
                                </div>
                                <div class="info">
                                    <h4>Dr. Madhura Chakraborty</h4>
                                    <a href="tel:+917980979789">+91 7980979789</a>
                                </div>
                            </div>
                            <div class="contact-card">
                                <div class="icon">
                                    <i class="fas fa-headset"></i>
                                </div>
                                <div class="info">
                                    <h4>Dr. Proloy Ghosh</h4>
                                    <a href="tel:+91xxxxxxxxxxx">+91 xxxxxxxxxx</a>
                                </div>
                            </div>
                            <div class="contact-card">
                                <div class="icon">
                                    <i class="fas fa-headset"></i>
                                </div>
                                <div class="info">
                                    <h4>Anamitra Mondal</h4>
                                    <a href="tel:+916289654490">+91 6289654490</a>
                                </div>
                            </div>
                            <div class="contact-card">
                                <div class="icon">
                                    <i class="fas fa-envelope"></i>
                                </div>
                                <div class="info">
                                    <h4>Email Support</h4>
                                    <a href="mailto:majistic@jiscollege.ac.in">majistic@jiscollege.ac.in</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </div>

    <div id="payment-loader">
        <div style="text-align: center; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 10px 25px rgba(0,0,0,0.2);">
            <div class="loader"></div>
            <p style="margin-top: 15px; font-weight: 500;">Processing payment...</p>
            <p style="margin-top: 5px; font-size: 0.8rem; color: #666;">Please don't close this window</p>
        </div>
    </div>

    <?php if (!$payment_done): ?>
        <script>
            // Format the date in IST
            var paymentDate = new Date().toLocaleString('en-IN', { 
                timeZone: 'Asia/Kolkata',
                year: 'numeric',
                month: 'numeric',
                day: 'numeric',
                hour: 'numeric',
                minute: 'numeric',
                second: 'numeric',
                hour12: true
            });
            
            var options = {
                "key": "<?php echo htmlspecialchars($keyId); ?>",
                "amount": "<?php echo htmlspecialchars($order->amount); ?>",
                "currency": "INR",
                "name": "maJIStic 2k25",
                "description": "<?php echo $is_alumni ? 'Alumni' : 'Student'; ?> Registration Fee",
                "image": "https://i.postimg.cc/02CTRDb2/majisticlogoblack.png",
                "order_id": "<?php echo htmlspecialchars($order->id); ?>",
                "handler": function (response) {
                    // Show loader during processing
                    document.getElementById('payment-loader').style.display = 'flex';
                    
                    // Record successful payment
                    var xhr = new XMLHttpRequest();
                    xhr.open("POST", "update_payment_status.php", true);
                    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
                    xhr.onreadystatechange = function() {
                        if (xhr.readyState === 4) {
                            try {
                                var result = JSON.parse(xhr.responseText);
                                if (result.success) {
                                    // Record successful transaction in payment_attempts
                                    recordPaymentAttempt('completed', response.razorpay_payment_id);
                                    
                                    // Show success message and redirect
                                    alert("Payment successful! You will receive a confirmation email shortly.");
                                    window.location.href = "payment.php?jis_id=<?php echo htmlspecialchars($registration_id); ?><?php echo $is_alumni ? '&alumni=1' : ''; ?>";
                                } else {
                                    // Record failed update in payment_attempts
                                    recordPaymentAttempt('error', response.razorpay_payment_id, 'Update failed: ' + result.message);
                                    
                                    alert("Payment processed but status update failed. Please contact support with your payment ID: " + response.razorpay_payment_id);
                                    document.getElementById('payment-loader').style.display = 'none';
                                }
                            } catch(e) {
                                console.error("Error parsing server response:", e);
                                alert("Payment may have been processed, but we encountered a system error. Please contact support with your payment ID: " + response.razorpay_payment_id);
                                document.getElementById('payment-loader').style.display = 'none';
                                
                                // Record error in payment_attempts
                                recordPaymentAttempt('error', response.razorpay_payment_id, 'Response parse error: ' + e.message);
                            }
                        }
                    };
                    
                    var params = "jis_id=<?php echo htmlspecialchars($registration_id); ?>" + 
                                "&payment_id=" + response.razorpay_payment_id + 
                                "&payment_status=SUCCESS" + 
                                "&amount=<?php echo htmlspecialchars($amount); ?>" +
                                "<?php echo $is_alumni ? '&alumni=1' : ''; ?>";
                    console.log("Sending params:", params);
                    xhr.send(params);
                },
                "prefill": {
                    "name": "<?php echo htmlspecialchars($student_name); ?>",
                    "email": "<?php echo htmlspecialchars($registration['email']); ?>",
                    "contact": "<?php echo htmlspecialchars($registration['mobile']); ?>"
                },
                "notes": {
                    "jis_id": "<?php echo htmlspecialchars($registration_id); ?>",
                    "is_alumni": "<?php echo $is_alumni ? 'Yes' : 'No'; ?>",
                    "client_ip": "<?php echo $client_ip; ?>"
                },
                "theme": {
                    "color": "#6366f1"
                },
                "modal": {
                    "ondismiss": function() {
                        // Record abandoned payment attempt
                        recordPaymentAttempt('abandoned');
                    }
                }
            };

            // Initialize Razorpay
            var rzp1 = new Razorpay(options);
            
            document.getElementById('rzp-button').onclick = function() {
                rzp1.open();
                
                // Record the payment attempt
                recordPaymentAttempt('initiated');
            };
            
            // Function to record payment attempts for better tracking
            function recordPaymentAttempt(status, payment_id, error_message) {
                var xhr = new XMLHttpRequest();
                xhr.open("POST", "record_payment_attempt.php", true);
                xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
                
                var params = "registration_id=<?php echo htmlspecialchars($registration_id); ?>" +
                            "&status=" + status + 
                            "<?php echo $is_alumni ? '&alumni=1' : ''; ?>" +
                            "&payment_processor=Razorpay";
                
                if (payment_id) {
                    params += "&payment_id=" + payment_id;
                }
                
                if (error_message) {
                    params += "&error_message=" + encodeURIComponent(error_message);
                }
                
                // Add order ID as transaction reference
                params += "&transaction_reference=<?php echo htmlspecialchars($order->id); ?>";
                
                // Add amount if available
                params += "&amount=<?php echo htmlspecialchars($amount); ?>";
                
                xhr.send(params);
            }

            // Fix FAQ and contact tab functionality
            document.addEventListener('DOMContentLoaded', function() {
                // Fix FAQ toggles
                const faqToggles = document.querySelectorAll('.faq-toggle');
                
                faqToggles.forEach(toggle => {
                    toggle.addEventListener('click', function() {
                        const contentId = this.getAttribute('aria-controls');
                        const content = document.getElementById(contentId);
                        const isExpanded = this.getAttribute('aria-expanded') === 'true';
                        
                        // First close all FAQs
                        faqToggles.forEach(otherToggle => {
                            const otherId = otherToggle.getAttribute('aria-controls');
                            const otherContent = document.getElementById(otherId);
                            otherToggle.setAttribute('aria-expanded', 'false');
                            if(otherContent) {
                                otherContent.style.maxHeight = '0px';
                                otherContent.style.opacity = '0';
                                otherContent.style.padding = '0px';
                            }
                        });
                        
                        // Then open the clicked one if it was closed
                        if (!isExpanded) {
                            this.setAttribute('aria-expanded', 'true');
                            if(content) {
                                const innerContent = content.querySelector('.faq-content-inner');
                                const contentHeight = innerContent ? innerContent.offsetHeight + 'px' : 'auto';
                                content.style.maxHeight = contentHeight;
                                content.style.opacity = '1';
                                content.style.padding = '0'; // Remove padding around the content container
                            }
                        }
                    });
                });
                
                // Open first FAQ by default
                if (faqToggles.length > 0) {
                    setTimeout(() => {
                        faqToggles[0].click();
                    }, 100);
                }
                
                // Fix contact tabs
                const contactTabs = document.querySelectorAll('.contact-tab');
                const contactPanels = document.querySelectorAll('.contact-panel');
                
                contactTabs.forEach(tab => {
                    tab.addEventListener('click', function() {
                        const target = this.getAttribute('data-target');
                        
                        // Remove active class from all tabs and panels
                        contactTabs.forEach(t => t.classList.remove('active'));
                        contactPanels.forEach(p => p.classList.remove('active'));
                        
                        // Add active class to clicked tab and its panel
                        this.classList.add('active');
                        const panel = document.getElementById(target);
                        if(panel) {
                            panel.classList.add('active');
                        }
                    });
                });
            });
            
            // Ensure the script runs even if the page is already loaded
            if (document.readyState === 'complete' || document.readyState === 'interactive') {
                setTimeout(function() {
                    const event = new Event('DOMContentLoaded');
                    document.dispatchEvent(event);
                }, 1);
            }
        </script>
    <?php else: ?>
        <script>
            // Add script for FAQ and contact tabs even when payment is done
            document.addEventListener('DOMContentLoaded', function() {
                // Fix FAQ toggles
                const faqToggles = document.querySelectorAll('.faq-toggle');
                
                faqToggles.forEach(toggle => {
                    toggle.addEventListener('click', function() {
                        const contentId = this.getAttribute('aria-controls');
                        const content = document.getElementById(contentId);
                        const isExpanded = this.getAttribute('aria-expanded') === 'true';
                        
                        // First close all FAQs
                        faqToggles.forEach(otherToggle => {
                            const otherId = otherToggle.getAttribute('aria-controls');
                            const otherContent = document.getElementById(otherId);
                            otherToggle.setAttribute('aria-expanded', 'false');
                            if(otherContent) {
                                otherContent.style.maxHeight = '0px';
                                otherContent.style.opacity = '0';
                                otherContent.style.padding = '0px';
                            }
                        });
                        
                        // Then open the clicked one if it was closed
                        if (!isExpanded) {
                            this.setAttribute('aria-expanded', 'true');
                            if(content) {
                                const innerContent = content.querySelector('.faq-content-inner');
                                const contentHeight = innerContent ? innerContent.offsetHeight + 'px' : 'auto';
                                content.style.maxHeight = contentHeight;
                                content.style.opacity = '1';
                                content.style.padding = '0'; // Remove padding around the content container
                            }
                        }
                    });
                });
                
                // Open first FAQ by default
                if (faqToggles.length > 0) {
                    setTimeout(() => {
                        faqToggles[0].click();
                    }, 100);
                }
                
                // Fix contact tabs
                const contactTabs = document.querySelectorAll('.contact-tab');
                const contactPanels = document.querySelectorAll('.contact-panel');
                
                contactTabs.forEach(tab => {
                    tab.addEventListener('click', function() {
                        const target = this.getAttribute('data-target');
                        
                        // Remove active class from all tabs and panels
                        contactTabs.forEach(t => t.classList.remove('active'));
                        contactPanels.forEach(p => p.classList.remove('active'));
                        
                        // Add active class to clicked tab and its panel
                        this.classList.add('active');
                        const panel = document.getElementById(target);
                        if(panel) {
                            panel.classList.add('active');
                        }
                    });
                });
            });
            
            // Ensure the script runs even if the page is already loaded
            if (document.readyState === 'complete' || document.readyState === 'interactive') {
                setTimeout(function() {
                    const event = new Event('DOMContentLoaded');
                    document.dispatchEvent(event);
                }, 1);
            }
        </script>
    <?php endif; ?>
</body>
</html>