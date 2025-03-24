<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us - maJIStic 2k25</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-image: url('images/pageback.png');
            background-repeat: repeat-y !important;
            background-size: 100% !important;
            background-position: top center !important;
            background-attachment: fixed !important;
            min-height: 100vh;
            width: 100%;
            overflow-x: hidden;
            color: white;
            margin: 0;
        }

        html {
            height: 100%;
            width: 100%;
            overflow-x: hidden;
            scroll-behavior: smooth;
        }
        
        @media (max-width: 768px) {
            body {
                background-size: cover !important;
            }
        }
        
        @media (max-width: 576px) {
            body {
                background-size: cover !important;
            }
        }
        
        .content-container {
            max-width: 1250px;
            margin: 0 auto;
            color: white;
            padding: 20px;
            box-sizing: border-box;
        }
        
        .container-header {
            padding: 40px 0 20px;
            text-align: center;
        }
        
        .contact-section {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 40px;
            padding: 20px 0 40px;
            width: 100%;
        }
        
        .contact-form {
            width: 100%;
            max-width: 600px;
            padding: 30px;
            background-color: rgba(40, 40, 60, 0.7);
            backdrop-filter: blur(10px);
            border-radius: 16px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
            border: 1px solid rgba(255, 255, 255, 0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .contact-form:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 40px rgba(0, 0, 0, 0.4);
        }
        
        .form-header {
            margin-bottom: 20px;
            text-align: center;
        }
        
        .form-header h2 {
            font-size: 1.8em;
            font-weight: 600;
            margin-bottom: 5px;
            background: linear-gradient(90deg, #e2c5ff, #a38fff);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }
        
        .form-header p {
            color: #ccc;
            font-size: 0.9em;
        }
        
        .input-group {
            margin-bottom: 15px;
            position: relative;
        }
        
        .input-group label {
            display: block;
            margin-bottom: 6px;
            font-size: 0.9em;
            color: #ddd;
        }
        
        .input-group i {
            position: absolute;
            left: 12px;
            top: 40px;
            color: #666;
        }
        
        .contact-form input, .contact-form textarea, .contact-form select {
            width: 100%;
            padding: 12px 15px 12px 40px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 8px;
            background-color: rgba(255, 255, 255, 0.95);
            color: #333;
            font-size: 14px;
            transition: all 0.3s ease;
        }
        
        .contact-form input:focus, .contact-form textarea:focus, .contact-form select:focus {
            outline: none;
            border-color: #a38fff;
            box-shadow: 0 0 0 2px rgba(163, 143, 255, 0.3);
        }
        
        .contact-form textarea {
            height: 120px;
            resize: vertical;
            padding-left: 15px;
        }
        
        .contact-form button {
            background: linear-gradient(135deg, #6e45e2, #88afd0);
            color: white;
            padding: 12px 25px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 500;
            align-self: center;
            margin-top: 15px;
            transition: all 0.3s ease;
            width: 100%;
            max-width: 200px;
        }
        
        .contact-form button:hover {
            background: linear-gradient(135deg, #5c3cbe, #7698b8);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }
        
        .success-message {
            padding: 15px;
            background-color: rgba(40, 167, 69, 0.2);
            border: 1px solid rgba(40, 167, 69, 0.3);
            border-radius: 8px;
            color: #98ff98;
            margin-bottom: 20px;
            text-align: center;
        }
        
        .error-message {
            padding: 15px;
            background-color: rgba(220, 53, 69, 0.2);
            border: 1px solid rgba(220, 53, 69, 0.3);
            border-radius: 8px;
            color: #ff9898;
            margin-bottom: 20px;
            text-align: center;
        }
        
        .map-section {
            width: 100%;
            max-width: 600px;
            display: flex;
            flex-direction: column;
            align-items: center;
            background-color: rgba(40, 40, 60, 0.7);
            backdrop-filter: blur(10px);
            border-radius: 16px;
            padding: 30px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
            border: 1px solid rgba(255, 255, 255, 0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .map-section:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 40px rgba(0, 0, 0, 0.4);
        }
        
        .map-section h2 {
            font-size: 1.8em;
            font-weight: 600;
            margin-bottom: 15px;
            text-align: center;
            background: linear-gradient(90deg, #e2c5ff, #a38fff);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }
        
        .map-info {
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 20px;
        }
        
        .map-info i {
            font-size: 24px;
            margin-right: 15px;
            color: #a38fff;
        }
        
        .map-info p {
            text-align: center;
            color: #ddd;
            line-height: 1.6;
        }
        
        .map-section iframe {
            width: 100%;
            height: 300px;
            border: 0;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
        }
        
        .policy-section {
            width: 100%;
            max-width: 800px;
            margin: 0;
            padding: 30px;
            background-color: rgba(40, 40, 60, 0.7);
            backdrop-filter: blur(10px);
            border-radius: 16px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
            border: 1px solid rgba(255, 255, 255, 0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .policy-section:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 40px rgba(0, 0, 0, 0.4);
        }
        
        .policy-section h2 {
            font-size: 1.8em;
            font-weight: 600;
            margin-bottom: 15px;
            text-align: center;
            background: linear-gradient(90deg, #e2c5ff, #a38fff);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }
        
        .policy-section h3 {
            font-size: 1.3em;
            font-weight: 500;
            margin: 20px 0 10px;
            color: #a38fff;
        }
        
        .policy-section p, .policy-section ul {
            font-size: 0.95em;
            color: #ddd;
            line-height: 1.7;
            text-align: justify;
        }
        
        .policy-section ul {
            list-style-type: none;
            padding-left: 5px;
        }
        
        .policy-section ul li {
            margin-bottom: 8px;
            position: relative;
            padding-left: 25px;
        }
        
        .policy-section ul li:before {
            content: "\f00c";
            font-family: "Font Awesome 6 Free";
            font-weight: 900;
            position: absolute;
            left: 0;
            color: #a38fff;
        }
        
        @media (min-width: 768px) {
            .contact-form, .map-section, .policy-section {
                padding: 40px;
            }
            
            .map-section iframe {
                height: 350px;
            }
        }
        
        @media (max-width: 767px) {
            .contact-form, .map-section, .policy-section {
                max-width: 100%;
            }
            
            .contact-form button {
                width: 100%;
                max-width: none;
            }
        }
        
        /* Desktop layout */
        @media (min-width: 992px) {
            .contact-section {
                display: grid;
                grid-template-columns: 1fr 1fr;
                align-items: start;
                gap: 30px;
            }
            
            .contact-form, .map-section {
                max-width: 100%;
                height: 100%;
                margin: 0;
            }
            
            .policy-section {
                grid-column: span 2;
                max-width: 100%;
            }
        }
        
        @media (max-width: 991px) {
            .contact-form, .map-section, .policy-section {
                max-width: 700px;
                width: 100%;
            }
        }
        
        /* Animation for page elements */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {top) -->
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .animated {
            animation: fadeInUp 0.6s ease-out forwards;
        }
        
        .delay-1 {
            animation-delay: 0.1s;
        }
        
        .delay-2 {
            animation-delay: 0.2s;
        }
        
        .delay-3 {
            animation-delay: 0.3s;
        }
        
        /* FAQ Section Styling */
        .faq-section {
            width: 100%;
            max-width: 800px;
            margin: 0;
            padding: 30px;
            background-color: rgba(40, 40, 60, 0.7);
            backdrop-filter: blur(10px);
            border-radius: 16px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
            border: 1px solid rgba(255, 255, 255, 0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .faq-section:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 40px rgba(0, 0, 0, 0.4);
        }
        
        .faq-section h2 {
            font-size: 1.8em;
            font-weight: 600;
            margin-bottom: 25px;
            text-align: center;
            background: linear-gradient(90deg, #e2c5ff, #a38fff);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }
        
        .faq-container {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }
        
        .faq-item {
            border-radius: 8px;
            overflow: hidden;
            transition: all 0.3s ease;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .faq-question {
            padding: 15px 20px;
            background-color: rgba(60, 60, 80, 0.6);
            cursor: pointer;
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: all 0.3s ease;
            font-weight: 500;
            color: #e2c5ff;
        }
        
        .faq-question:hover {
            background-color: rgba(80, 80, 100, 0.6);
        }
        
        .faq-question i {
            transition: transform 0.3s ease;
        }
        
        .faq-answer {
            padding: 0;
            max-height: 0;
            overflow: hidden;
            transition: all 0.3s ease;
            background-color: rgba(50, 50, 70, 0.4);
        }
        
        .faq-answer-content {
            padding: 0 20px;
            color: #ddd;
            line-height: 1.6;
        }
        
        .faq-item.active .faq-question {
            background-color: rgba(90, 80, 120, 0.7);
        }
        
        .faq-item.active .faq-question i {
            transform: rotate(180deg);
        }
        
        .faq-item.active .faq-answer {
            padding: 15px 0;
            max-height: 300px;
            overflow: auto;
        }
        
        /* Desktop layout */
        @media (min-width: 992px) {
            .contact-section {
                display: grid;
                grid-template-columns: 1fr 1fr;
                align-items: start;
                gap: 30px;
            }
            
            .contact-form, .map-section {
                max-width: 100%;
                height: 100%;
                margin: 0;
            }
            
            .faq-section, .policy-section {
                grid-column: span 2;
                max-width: 100%;
            }
        }
    </style>
</head>
<body class="bg-gray-100">
    <?php include 'includes/header.php'; ?>
    <?php include 'includes/links.php'; ?>

    <div class="content-container">
        <div class="container-header animated">
            <h1 class="text-4xl font-bold bg-gradient-to-r from-purple-300 to-blue-300 bg-clip-text text-transparent">Contact Us</h1>
            <p class="mt-4 text-lg text-gray-300">Have questions? We're here to help you!</p>
        </div>

        <!-- Contact Section with Form and Map (Single Column) -->
        <div class="contact-section">
            <!-- Contact Form -->
            <div class="contact-form animated delay-1">
                <div class="form-header">
                    <h2>Send us a message</h2>
                    <p>Fill out the form below and we'll get back to you soon</p>
                </div>
                
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
                        $mail->addAddress('majisticjisce@gmail.com');
                        $mail->addCC($email);

                        // Content
                        $mail->Subject = "New Contact Form Submission from $name - $problem_type";
                        $mail->Body = "Name: $name\nEmail: $email\nUniversity Roll No: $roll_no\nJIS ID: $jis_id\nMobile No: $mobile\nProblem Type: $problem_type\nMessage:\n$message";

                        $mail->send();
                        echo "<div class='success-message'><i class='fas fa-check-circle mr-2'></i>Message sent successfully! A copy has been sent to your email.</div>";
                    } catch (Exception $e) {
                        echo "<div class='error-message'><i class='fas fa-exclamation-circle mr-2'></i>Failed to send message. Error: {$mail->ErrorInfo}</div>";
                    }
                }
                ?>
                
                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST">
                    <div class="input-group">
                        <label for="name">Your Name</label>
                        <i class="fas fa-user"></i>
                        <input type="text" id="name" name="name" placeholder="Enter your full name" required>
                    </div>
                    
                    <div class="input-group">
                        <label for="email">Your Email</label>
                        <i class="fas fa-envelope"></i>
                        <input type="email" id="email" name="email" placeholder="Enter your email address" required>
                    </div>
                    
                    <div class="input-group">
                        <label for="roll_no">University Roll No.</label>
                        <i class="fas fa-id-card"></i>
                        <input type="text" id="roll_no" name="roll_no" placeholder="Enter your university roll number" required>
                    </div>
                    
                    <div class="input-group">
                        <label for="jis_id">JIS ID</label>
                        <i class="fas fa-id-badge"></i>
                        <input type="text" id="jis_id" name="jis_id" placeholder="Enter your JIS ID" required>
                    </div>
                    
                    <div class="input-group">
                        <label for="mobile">Mobile No.</label>
                        <i class="fas fa-phone"></i>
                        <input type="text" id="mobile" name="mobile" placeholder="Enter your mobile number" required>
                    </div>
                    
                    <div class="input-group">
                        <label for="problem_type">Problem Type</label>
                        <select id="problem_type" name="problem_type" required>
                            <option value="" disabled selected>Select Problem Type</option>
                            <option value="Payment Issue">Payment Issue</option>
                            <option value="Registration">Registration</option>
                            <option value="Cultural">Cultural</option>
                            <option value="Sponsor">Sponsor</option>
                            <option value="Marketing">Marketing</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                    
                    <div class="input-group">
                        <label for="message">Your Message</label>
                        <textarea id="message" name="message" placeholder="Describe your issue or question in detail..." required></textarea>
                    </div>
                    
                    <button type="submit"><i class="fas fa-paper-plane mr-2"></i>Send Message</button>
                </form>
            </div>

            <!-- Map Section -->
            <div class="map-section animated delay-2">
                <h2>Our Location</h2>
                <div class="map-info">
                    <i class="fas fa-map-marker-alt"></i>
                    <p>JIS College of Engineering<br>Kalyani Block A, Phase III<br>Kalyani, Nadia - 741235, West Bengal</p>
                </div>
                <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3673.746095513457!2d88.44517707531195!3d22.95957617921794!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3a027730302f6e25%3A0xe50dfccae21e1fc!2sJIS%20College%20of%20Engineering!5e0!3m2!1sen!2sin!4v1742024298934!5m2!1sen!2sin" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
            </div>
            
            <!-- FAQ Section -->
            <div class="faq-section animated delay-2">
                <h2>Frequently Asked Questions</h2>
                <div class="faq-container">
                    <div class="faq-item">
                        <div class="faq-question">
                            <span>What is maJIStic 2k25?</span>
                            <i class="fas fa-chevron-down"></i>
                        </div>
                        <div class="faq-answer">
                            <div class="faq-answer-content">
                                <p>maJIStic 2k25 is an annual cultural fest organized by JIS College of Engineering, Kalyani. It features a variety of events, performances, and activities for students to participate in and enjoy.</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="faq-item">
                        <div class="faq-question">
                            <span>When and where is maJIStic 2k25 being held?</span>
                            <i class="fas fa-chevron-down"></i>
                        </div>
                        <div class="faq-answer">
                            <div class="faq-answer-content">
                                <p>maJIStic 2k25 will be held on April 11-12, 2025, at JIS College of Engineering, Kalyani, West Bengal. The event will run from 10:00 AM to 8:00 PM each day, with evening performances on select days.</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="faq-item">
                        <div class="faq-question">
                            <span>Who can attend maJIStic 2k25?</span>
                            <i class="fas fa-chevron-down"></i>
                        </div>
                        <div class="faq-answer">
                            <div class="faq-answer-content">
                                <p>Only JIS College of Engineering students are allowed to attend maJIStic 2k25. No outhouse students, friends, or external visitors will be permitted entry.</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="faq-item">
                        <div class="faq-question">
                            <span>How can I register for maJIStic 2k25?</span>
                            <i class="fas fa-chevron-down"></i>
                        </div>
                        <div class="faq-answer">
                            <div class="faq-answer-content">
                                <p>You can register for maJIStic 2k25 through our official website. Follow the registration link, fill out the required information, and complete the payment process to secure your spot.</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="faq-item">
                        <div class="faq-question">
                            <span>Is the registration fee refundable?</span>
                            <i class="fas fa-chevron-down"></i>
                        </div>
                        <div class="faq-answer">
                            <div class="faq-answer-content">
                                <p>No, the registration fee for maJIStic 2k25 is non-refundable under any circumstances.</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="faq-item">
                        <div class="faq-question">
                            <span>What events and activities will be featured?</span>
                            <i class="fas fa-chevron-down"></i>
                        </div>
                        <div class="faq-answer">
                            <div class="faq-answer-content">
                                <p>maJIStic 2k25 will feature various events including cultural showcases, performances (dance, music, drama), literary events, art and proshows. The festival will also host performances and guest lectures.</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="faq-item">
                        <div class="faq-question">
                            <span>Will food be available at the venue?</span>
                            <i class="fas fa-chevron-down"></i>
                        </div>
                        <div class="faq-answer">
                            <div class="faq-answer-content">
                                <p>Yes, there will be food stalls offering a variety of food options, but these will be paid services. The maJIStic committee will not provide any free food. All food purchases will be the responsibility of the attendees.</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="faq-item">
                        <div class="faq-question">
                            <span>How do I purchase official maJIStic 2k25 merchandise?</span>
                            <i class="fas fa-chevron-down"></i>
                        </div>
                        <div class="faq-answer">
                            <div class="faq-answer-content">
                                <p>Official merchandise can be purchased through our website's Merchandise section. After purchase, merchandise can be collected from the Main Building one week before the event. The exact pickup location will be emailed to you.</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="faq-item">
                        <div class="faq-question">
                            <span>Can I change the size of my merchandise after booking?</span>
                            <i class="fas fa-chevron-down"></i>
                        </div>
                        <div class="faq-answer">
                            <div class="faq-answer-content">
                                <p>Yes, size corrections can be requested within 2 days of booking by contacting the maJIStic Merchandise Team through the Contact Us section.</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="faq-item">
                        <div class="faq-question">
                            <span>How can I get more information about maJIStic 2k25?</span>
                            <i class="fas fa-chevron-down"></i>
                        </div>
                        <div class="faq-answer">
                            <div class="faq-answer-content">
                                <p>For more information, you can contact us through the form on this page or email us directly at majistic@jiscollege.ac.in. Follow our social media accounts for regular updates and announcements.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Terms of Service Section -->
            <div class="policy-section animated delay-3">
                <h2>Terms of Service</h2>
                <p>By registering for maJIStic 2k25 or purchasing merchandise, you agree to these terms:</p>
                
                <h3>Registration and Entry Tickets</h3>
                <ul>
                    <li>The registration fee for maJIStic 2k25 is non-refundable under any circumstances.</li>
                    <li>A QR-based entry ticket will be emailed two days before the event to your registered email ID.</li>
                    <li>Tickets are non-transferable and cannot be used by anyone other than the registered student.</li>
                    <li>QR codes are time-bound; failure to check in at the specified time on the event day will result in automatic ticket cancellation.</li>
                </ul>
                
                <h3>Merchandise Purchases</h3>
                <ul>
                    <li>Merchandise payments are non-refundable once booked, under any circumstances.</li>
                    <li>Collect your merchandise one week before the fest from the Main Building; the exact location will be emailed to you.</li>
                    <li>Size corrections can be requested within 2 days of booking by contacting the maJIStic Merchandise Team.</li>
                </ul>
                
                <h3>Conduct Rules</h3>
                <ul>
                    <li>Any mischievous behavior during maJIStic 2k25 will result in suspension from JIS College of Engineering and a fine.</li>
                    <li>Smoking and drinking are strictly prohibited on campus. Violators will face suspension and fines.</li>
                </ul>
                
                <p>We reserve the right to cancel registrations, tickets, or orders at our discretion.</p>
            </div>

            <!-- Refund and Shipments Policy Section -->
            <div class="policy-section animated delay-3">
                <h2>Refund and Shipments Policy</h2>
                <p>We aim to ensure a smooth experience for maJIStic 2k25 participants and merchandise buyers:</p>
                
                <h3>Event Registration Fees</h3>
                <p>The fee you pay to register for maJIStic 2k25 cannot be refunded for any reason, including but not limited to non-attendance, late arrival resulting in ticket loss, or event cancellation due to unforeseen circumstances.</p>
                
                <h3>Merchandise Purchases</h3>
                <p>Payments for maJIStic 2k25 merchandise are non-refundable once booked. Size corrections are allowed within 2 days of booking via the Contact Us section; no refunds or exchanges are permitted thereafter.</p>
                
                <h3>Shipments</h3>
                <p>maJIStic 2k25 does not offer shipping for merchandise. All purchases must be collected in person from the designated pickup location. Failure to collect within the specified period will result in forfeiture of the merchandise without refund.</p>
            </div>

            <!-- Privacy Policy Section -->
            <div class="policy-section animated delay-3">
                <h2>Privacy Policy</h2>
                <p>maJIStic 2k25, organized by JIS College of Engineering, Kalyani, is committed to protecting your privacy:</p>

                <h3>Information We Collect</h3>
                <ul>
                    <li>Personal information such as name, email, roll number, JIS ID, and mobile number</li>
                    <li>Payment information processed through secure third-party payment processors</li>
                    <li>Usage data including IP address, browser type, and device information</li>
                    <li>Event participation data such as QR ticket usage and attendance records</li>
                </ul>

                <h3>How We Use Your Information</h3>
                <ul>
                    <li>To register you for maJIStic 2k25 events and issue entry tickets</li>
                    <li>To process and fulfill merchandise orders</li>
                    <li>To respond to your inquiries submitted via the Contact Us section</li>
                    <li>To improve our website, services, and event planning</li>
                </ul>

                <p>For the complete Privacy Policy details, please contact us at <a href="mailto:majisticjisce@gmail.com" class="text-purple-300 hover:text-purple-100">majisticjisce@gmail.com</a>.</p>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
    
    <script>
        // Simple script to ensure animations work when elements come into view
        document.addEventListener('DOMContentLoaded', function() {
            // Add animation classes when the page loads
            const elements = document.querySelectorAll('.animated');
            elements.forEach(function(el) {
                setTimeout(function() {
                    el.style.opacity = '1';
                }, 100);
            });
            
            // Add smooth scroll for anchor links
            document.querySelectorAll('a[href^="#"]').forEach(anchor => {
                anchor.addEventListener('click', function(e) {
                    e.preventDefault();
                    document.querySelector(this.getAttribute('href')).scrollIntoView({
                        behavior: 'smooth'
                    });
                });
            });
            
            // FAQ toggle functionality
            const faqItems = document.querySelectorAll('.faq-item');
            
            faqItems.forEach(item => {
                item.querySelector('.faq-question').addEventListener('click', () => {
                    // Toggle active class
                    item.classList.toggle('active');
                });
            });
        });
    </script>
</body>
</html>