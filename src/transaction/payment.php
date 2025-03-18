<?php
// Set timezone to IST at the beginning of the file
date_default_timezone_set('Asia/Kolkata');
include '../../includes/db_config.php';

// Get the registration ID from the URL
$registration_id = isset($_GET['jis_id']) ? $_GET['jis_id'] : null;
// Check if this is an alumni registration
$is_alumni = isset($_GET['alumni']) && $_GET['alumni'] == '1';

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
    </style>
</head>

<body>
    <div class="page-container">
        <a href="../../index.php" class="back-button">
            <i class="fas fa-arrow-left"></i> Back to Home
        </a>
        
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

@@ -156,239 +162,245 @@
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
                                    <h4>Rajesh Kumar</h4>
                                    <a href="tel:+917325846735">+91 7325846735</a>
                                </div>
                            </div>
                            <div class="contact-card">
                                <div class="icon">
                                    <i class="fas fa-headset"></i>
                                </div>
                                <div class="info">
                                    <h4>Ananya Sharma</h4>
                                    <a href="tel:+916204857639">+91 6204857639</a>
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


@@ -433,12 +445,13 @@ if (!$payment_done) {
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

@@ -451,175 +464,175 @@
                        var xhr = new XMLHttpRequest();
                        xhr.open("POST", "record_payment_attempt.php", true);
                        xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
                        xhr.send("registration_id=<?php echo htmlspecialchars($registration_id); ?>&status=abandoned<?php echo $is_alumni ? '&alumni=1' : ''; ?>");
                    }
                }
            };

            // Initialize Razorpay
            var rzp1 = new Razorpay(options);
            
            document.getElementById('rzp-button').onclick = function() {
                rzp1.open();
                
                // Record the payment attempt
                var xhr = new XMLHttpRequest();
                xhr.open("POST", "record_payment_attempt.php", true);
                xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
                xhr.send("registration_id=<?php echo htmlspecialchars($registration_id); ?>&status=initiated<?php echo $is_alumni ? '&alumni=1' : ''; ?>");
            };

            // Fixed FAQ and contact tab functionality
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