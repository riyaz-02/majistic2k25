<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us - maJIStic 2k25</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-image: url('images/pageback.png');
            background-repeat: repeat-y !important;
            background-size: 100% !important;
            background-position: top center !important;
            background-attachment: initial !important;
            color: white;
            margin: 0;
        }
        .content-container {
            max-width: 800px;
            margin: 0 auto;
            color: white;
            padding: 0 20px;
            box-sizing: border-box;
        }
        .container-header {
            padding: 40px 0;
            text-align: center;
            display: none; /* Removed header text to match the image (optional, adjust if needed) */
        }
        .contact-section {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 40px;
            padding: 40px 0;
        }
        .contact-form {
            width: 100%;
            max-width: 500px;
            padding: 25px;
            background-color: rgba(255, 255, 255, 0.1);
            border-radius: 10px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        .contact-form input, .contact-form textarea, .contact-form select {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: none;
            border-radius: 5px;
            background-color: rgba(255, 255, 255, 0.9);
            color: #333;
            font-size: 14px;
        }
        .contact-form textarea {
            height: 120px;
            resize: vertical;
        }
        .contact-form button {
            background-color: #e53e3e;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            align-self: flex-end;
            margin-top: 10px;
        }
        .contact-form button:hover {
            background-color: #c53030;
        }
        .map-section {
            width: 100%;
            max-width: 500px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        .map-section h2 {
            font-size: 1.5em;
            margin-bottom: 15px;
            text-align: center;
        }
        .map-section p {
            text-align: center;
            margin-bottom: 15px;
        }
        .map-section iframe {
            width: 100%;
            height: 300px;
            border: 0;
            border-radius: 10px;
        }
        .policy-section {
            width: 100%;
            max-width: 500px;
            margin: 0;
            padding: 20px;
            background-color: rgba(255, 255, 255, 0.1);
            border-radius: 10px;
        }
        .policy-section h2 {
            font-size: 1.5em;
            margin-bottom: 15px;
            text-align: center;
        }
        .policy-section h3 {
            font-size: 1.2em;
            margin: 15px 0 10px;
        }
        .policy-section p, .policy-section ul {
            font-size: 0.9em;
            color: #ccc;
            line-height: 1.6;
            text-align: justify;
        }
        .policy-section ul {
            list-style-type: disc;
            padding-left: 20px;
        }
    </style>
</head>
<body class="bg-gray-100">
    <?php include 'includes/header.php'; ?>
    <?php include 'includes/links.php'; ?>

    <div class="content-container">
        <div class="container-header">
            <h1 class="text-4xl font-bold">Contact Us</h1>
            <p class="mt-4 text-lg">Have any queries? Reach out to us!</p>
        </div>

        <!-- Contact Section with Form and Map (Single Column) -->
        <div class="contact-section">
            <!-- Contact Form -->
            <div class="contact-form">
                <?php
                // Include PHPMailer
                use PHPMailer\PHPMailer\PHPMailer;
                use PHPMailer\PHPMailer\Exception;

                require 'vendor/autoload.php'; // Adjust path if needed

                if ($_SERVER["REQUEST_METHOD"] == "POST") {
                    $name = htmlspecialchars($_POST['name']);
                    $email = htmlspecialchars($_POST['email']);
                    $roll_no = htmlspecialchars($_POST['roll_no']);
                    $jis_id = htmlspecialchars($_POST['jis_id']);
                    $mobile = htmlspecialchars($_POST['mobile']);
                    $problem_type = htmlspecialchars($_POST['problem_type']);
                    $message = htmlspecialchars($_POST['message']);

                    $mail = new PHPMailer(true);
                    try {
                        // Server settings
                        $mail->isSMTP();
                        $mail->Host = 'smtp.gmail.com';
                        $mail->SMTPAuth = true;
                        $mail->Username = 'majisticjisce@gmail.com'; // Replace with your SMTP username
                        $mail->Password = 'shqlyefuuwkapqf'; // Replace with your SMTP password
                        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                        $mail->Port = 587;

                        // Recipients
                        $mail->setFrom('majisticjisce@gmail.com', 'maJIStic 2k25');
                        $mail->addAddress('');
                        $mail->addCC($email);

                        // Content
                        $mail->Subject = "New Contact Form Submission from $name - $problem_type";
                        $mail->Body = "Name: $name\nEmail: $email\nUniversity Roll No: $roll_no\nJIS ID: $jis_id\nMobile No: $mobile\nProblem Type: $problem_type\nMessage:\n$message";

                        $mail->send();
                        echo "<p class='text-green-500 text-center'>Message sent successfully! A copy has been sent to your email.</p>";
                    } catch (Exception $e) {
                        echo "<p class='text-red-500 text-center'>Failed to send message. Error: {$mail->ErrorInfo}</p>";
                    }
                }
                ?>
                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST">
                    <input type="text" name="name" placeholder="Your Name" required>
                    <input type="email" name="email" placeholder="Your Email" required>
                    <input type="text" name="roll_no" placeholder="University Roll No." required>
                    <input type="text" name="jis_id" placeholder="JIS ID" required>
                    <input type="text" name="mobile" placeholder="Mobile No." required>
                    <select name="problem_type" required>
                        <option value="" disabled selected>Select Problem Type</option>
                        <option value="Payment Issue">Payment Issue</option>
                        <option value="Registration">Registration</option>
                        <option value="Cultural">Cultural</option>
                        <option value="Sponsor">Sponsor</option>
                        <option value="Marketing">Marketing</option>
                        <option value="Other">Other</option>
                    </select>
                    <textarea name="message" placeholder="Your Message" required></textarea>
                    <button type="submit">Send Message</button>
                </form>
            </div>

            <!-- Map Section -->
            <div class="map-section">
                <h2>Our Location</h2>
                <p>JIS College of Engineering<br>Kalyani Block A, Phase III<br>Kalyani, Nadia - 741235, West Bengal</p>
                <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3673.746095513457!2d88.44517707531195!3d22.95957617921794!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3a027730302f6e25%3A0xe50dfccae21e1fc!2sJIS%20College%20of%20Engineering!5e0!3m2!1sen!2sin!4v1742024298934!5m2!1sen!2sin" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
            </div>

            <!-- Terms of Service Section -->
            <div class="policy-section">
                <h2>Terms of Service</h2>
                <p>By registering for maJIStic 2k25 or purchasing merchandise, you agree to these terms:</p>
                
                <h3>Registration and Entry Tickets</h3>
                <ul>
                    <li>The registration fee for maJIStic 2k25 is non-refundable under any circumstances.</li>
                    <li>A QR-based entry ticket will be emailed two days before the event to your registered email ID.</li>
                    <li>Tickets are non-transferable and cannot be used by anyone other than the registered student.</li>
                    <li>QR codes are time-bound; failure to check in at the specified time on the event day (maJIStic 2k25) will result in automatic ticket cancellation.</li>
                </ul>
                
                <h3>Merchandise Purchases</h3>
                <ul>
                    <li>Merchandise payments are non-refundable once booked, under any circumstances.</li>
                    <li>Collect your merchandise one week before the fest from the Main Building; the exact location will be emailed to you.</li>
                    <li>Size corrections can be requested within 2 days of booking by contacting the maJIStic Merchandise Team via the Contact Us section.</li>
                </ul>
                
                <h3>Conduct Rules</h3>
                <ul>
                    <li>Any mischievous behavior during maJIStic 2k25 will result in suspension from JIS College of Engineering and a fine.</li>
                    <li>Smoking and drinking are strictly prohibited on campus. Violators will face suspension and fines.</li>
                </ul>
                
                <p>We reserve the right to cancel registrations, tickets, or orders at our discretion.</p>
            </div>

            <!-- Refund and Shipments Policy Section -->
            <div class="policy-section">
                <h2>Refund and Shipments Policy</h2>
                <p>We aim to ensure a smooth experience for maJIStic 2k25 participants and merchandise buyers. Please review our policies below:</p>
                
                <h3>Event Registration Fees</h3>
                <p>The fee you pay to register for maJIStic 2k25 cannot be refunded for any reason, including but not limited to non-attendance, late arrival resulting in ticket loss, or event cancellation due to unforeseen circumstances.</p>
                
                <h3>Merchandise Purchases</h3>
                <p>Payments for maJIStic 2k25 merchandise are non-refundable once booked, regardless of circumstances. Size corrections are allowed within 2 days of booking via the Contact Us section; no refunds or exchanges are permitted thereafter. Merchandise will be available for pickup one week before the fest at the Main Building, with the exact location emailed to you upon confirmation.</p>
                
                <h3>Shipments</h3>
                <p>maJIStic 2k25 does not offer shipping for merchandise. All purchases must be collected in person from the designated pickup location. Failure to collect within the specified period will result in forfeiture of the merchandise without refund.</p>
            </div>

            <!-- Privacy Policy Section (Elaborated) -->
            <div class="policy-section">
                <h2>Privacy Policy</h2>
                <p>maJIStic 2k25, organized by JIS College of Engineering, Kalyani, is committed to protecting your privacy. This Privacy Policy outlines how we collect, use, disclose, and safeguard your personal information when you interact with our website, register for events, purchase merchandise, or use our services. By accessing or using our platform, you agree to the practices described in this policy.</p>

                <h3>1. Information We Collect</h3>
                <p>We collect the following types of information to provide and improve our services:</p>
                <ul>
                    <li><strong>Personal Information:</strong> Name, email address, University Roll No., JIS ID, mobile number, and other details you provide during registration, merchandise booking, contact form submissions, or other interactions with us.</li>
                    <li><strong>Payment Information:</strong> Credit card details, billing address, and other payment-related data necessary to process fees and merchandise purchases. We use secure third-party payment processors and do not store this information after transactions are completed.</li>
                    <li><strong>Usage Data:</strong> Information about your interactions with our website, including IP address, browser type, device information, pages visited, and timestamps, collected via cookies, web beacons, and analytics tools.</li>
                    <li><strong>Event Participation Data:</strong> Details about your participation in maJIStic 2k25 events, such as QR-based ticket usage and attendance records.</li>
                </ul>

                <h3>2. How We Use Your Information</h3>
                <p>We use your information for the following purposes:</p>
                <ul>
                    <li>To register you for maJIStic 2k25 events and issue QR-based entry tickets.</li>
                    <li>To process and fulfill merchandise orders, including notifying you of pickup locations and times.</li>
                    <li>To respond to your inquiries submitted via the Contact Us section.</li>
                    <li>To personalize your experience and provide tailored content or recommendations.</li>
                    <li>To analyze usage patterns and improve our website, services, and event planning.</li>
                    <li>To ensure compliance with legal obligations, including fraud prevention and safety measures.</li>
                    <li>To send you updates, newsletters, or promotional materials (with your consent, where required).</li>
                </ul>

                <h3>3. How We Share Your Information</h3>
                <p>We do not sell or rent your personal information to third parties. However, we may share your information with:</p>
                <ul>
                    <li><strong>Service Providers:</strong> Third-party vendors (e.g., payment processors, email service providers) who assist us in operating our platform and delivering services, under strict confidentiality agreements.</li>
                    <li><strong>Legal Authorities:</strong> When required by law or to protect the rights, property, or safety of maJIStic 2k25, its participants, or the public.</li>
                    <li><strong>Business Transfers:</strong> In the event of a merger, acquisition, or sale of assets, your information may be transferred as part of the transaction.</li>
                </ul>

                <h3>4. Data Security</h3>
                <p>We implement reasonable physical, technical, and administrative safeguards to protect your personal information from unauthorized access, use, or disclosure. These measures include encryption, secure server hosting, and restricted access to data. However, no online system is 100% secure, and we cannot guarantee absolute security. You are responsible for maintaining the confidentiality of your account credentials.</p>

                <h3>5. Your Rights and Choices</h3>
                <p>You have the following rights regarding your personal information:</p>
                <ul>
                    <li><strong>Access:</strong> Request a copy of the personal data we hold about you.</li>
                    <li><strong>Correction:</strong> Update or correct inaccurate or incomplete data.</li>
                    <li><strong>Deletion:</strong> Request the deletion of your data, subject to legal obligations.</li>
                    <li><strong>Opt-Out:</strong> Withdraw consent for marketing communications at any time by contacting us or using the unsubscribe link in emails.</li>
                </ul>
                <p>To exercise these rights, please email <a href="mailto:majisticjisce@gmail.com">majisticjisce@gmail.com</a>. We will respond within a reasonable timeframe, typically 30 days.</p>

                <h3>6. Cookies and Tracking Technologies</h3>
                <p>We use cookies and similar technologies to enhance your experience, analyze usage, and deliver personalized content. Cookies are small files stored on your device that help us remember your preferences and track site activity. You can manage cookie preferences through your browser settings, but disabling them may affect website functionality.</p>
                <ul>
                    <li><strong>Essential Cookies:</strong> Necessary for basic website operations.</li>
                    <li><strong>Analytics Cookies:</strong> Used to collect anonymous usage data (e.g., Google Analytics).</li>
                    <li><strong>Functional Cookies:</strong> Enable features like remembering your login details.</li>
                </ul>

                <h3>7. Data Retention</h3>
                <p>We retain your personal information only as long as necessary to fulfill the purposes outlined in this policy, unless a longer retention period is required or permitted by law (e.g., for tax or legal compliance). After this period, data is securely deleted or anonymized.</p>

                <h3>8. International Data Transfers</h3>
                <p>Your information may be transferred to and processed in countries outside your region, including India, where our servers or service providers are located. We ensure that such transfers comply with applicable data protection laws, including the use of standard contractual clauses where required.</p>

                <h3>9. Changes to This Privacy Policy</h3>
                <p>We may update this Privacy Policy to reflect changes in our practices or legal requirements. Any updates will be posted on this page with a revised "Last Updated" date. We encourage you to review this policy periodically. If significant changes are made, we will notify you via email or a prominent notice on our website.</p>
                <p><strong>Last Updated:</strong> March 14, 2025</p>

                <h3>10. Contact Us</h3>
                <p>If you have questions, concerns, or complaints about this Privacy Policy or our data practices, please contact us at <a href="mailto:majisticjisce@gmail.com">majisticjisce@gmail.com</a>. You may also file a complaint with a data protection authority if you believe your rights have been violated.</p>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
</body>
</html>