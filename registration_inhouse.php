<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL & ~E_DEPRECATED); // Suppress deprecation notices

// Include registration configuration
include 'src/config/registration_config.php';

// Check if registration is enabled, redirect if disabled
if (!REGISTRATION_ENABLED) {
    header('Location: src/handler/registration_closed.php');
    exit;
}

// Include the registration handler that now uses MySQL
include 'src/main/registration_handler.php';
// Include payment configuration
if (file_exists('src/config/payment_config.php')) {
    include 'src/config/payment_config.php';
} else {
    define('PAYMENT_ENABLED', true); // Default to enabled if config doesn't exist
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Registration</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap">
    <?php include 'includes/links.php'; ?>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="css/registration.css">
    <style>
        .registration-heading h1 {
            font-family: 'Roboto', sans-serif;
            font-weight: 700;
            letter-spacing: 1px;
        }
        
        .success-preloader {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.85);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            z-index: 9999;
            color: white;
            text-align: center;
        }
        
        .success-preloader img {
            width: 150px;
            margin-bottom: 20px;
            animation: pulse 1.5s infinite;
        }
        
        .success-preloader h2 {
            font-size: 2rem;
            margin-bottom: 15px;
            color: #3498db;
        }
        
        .success-preloader p {
            font-size: 1.2rem;
            margin: 5px 0;
        }
        
        .success-preloader .progress-bar {
            width: 250px;
            height: 10px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 5px;
            margin: 25px 0;
            overflow: hidden;
        }
        
        .success-preloader .progress-fill {
            height: 100%;
            width: 0%;
            background: linear-gradient(90deg, #3498db, #2ecc71);
            border-radius: 5px;
            transition: width 2.5s ease;
        }
        
        @keyframes pulse {
            0% { transform: scale(0.95); opacity: 0.7; }
            50% { transform: scale(1.05); opacity: 1; }
            100% { transform: scale(0.95); opacity: 0.7; }
        }

        /* Container for background content */
        .content-container {
            transition: filter 0.3s ease; /* Smooth blur transition */
        }

        /* Blur only the background when modal is open */
        .content-container.blur {
            filter: blur(5px);
        }

        /* Modal styles */
        .modal {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%); /* Perfect centering */
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5); /* Semi-transparent overlay */
            z-index: 10000;
        }

        .modal-content {
            background:rgba(54, 51, 55, 0.6); /* Purple background */
            color: white; /* White text for contrast */
            padding: 20px;
            border-radius: 10px;
            max-width: 500px;
            width: 90%;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            text-align: left; /* Align text to the left for better readability */
        }
        
        /* Center text in confirmation modal */l */
        #confirmationModal .modal-content p {
            text-align: center;* Maximum height as percentage of viewport height */
            margin-bottom: 20px; Add vertical scrollbar when content overflows */
            line-height: 1.5;px; /* Add right padding for scrollbar */
        }
        
        /* Style for modal buttons container */
        .modal-buttons {icky;
            display: flex;
            justify-content: center; 55, 0.95); /* Slightly darker than the modal background */
            gap: 15px;0px 0;
            margin-top: 20px;
        }   z-index: 1;
        }
        /* Style for modal buttons */
        .modal-buttons button {ent h3 {
            min-width: 100px;
            padding: 8px 15px;;
        }   color: #3498db; /* Blue color for section headings */
            font-size: 1.1rem;
        .modal-content h2 {
            margin-bottom: 15px; /* Space below the heading */
            font-size: 1.5rem; /* Slightly smaller for better proportion */
        }   padding-bottom: 10px; /* Space for the button */
        }
        .rules-list {
            list-style-type: decimal; /* Use numbered list */
            padding-left: 25px; /* Indent the list */
            margin: 0 0 20px 0; /* Space below the list */
        }   margin-top: 15px;
            background-color: #e74c3c; /* Red color for close button */
        .rules-list li {;
            margin-bottom: 8px; /* Space between list items */
            line-height: 1.4; /* Tighter line height for compact look */
            word-spacing: normal; /* Reset word spacing */
            text-align: left; /* Ensure left alignment */
        }   width: 80px;
            display: block;
            margin-left: auto;
            margin-right: auto;
        }

        /* Center text in confirmation modal */
        #confirmationModal .modal-content p {
            text-align: center;
            margin-bottom: 20px;
            line-height: 1.5;
        }

        /* Style for modal buttons container */
        .modal-buttons {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin-top: 20px;
        }

        /* Style for modal buttons */
        .modal-buttons button {
            min-width: 100px;
            padding: 8px 15px;
        }
        
        .modal-content h2 {
            margin-bottom: 15px; /* Space below the heading */
            font-size: 1.5rem; /* Slightly smaller for better proportion */
        }

        .rules-list {
            list-style-type: decimal; /* Use numbered list */
            padding-left: 25px; /* Indent the list */
            margin: 0 0 20px 0; /* Space below the list */
        }

        .rules-list li {
            margin-bottom: 8px; /* Space between list items */
            line-height: 1.4; /* Tighter line height for compact look */
            word-spacing: normal; /* Reset word spacing */
            text-align: left; /* Ensure left alignment */
        }
        /* Prevent scrolling when modal is open */
        body.modal-open {
            overflow: hidden;
        }
    </style>
</head>
<body class="<?php echo $registration_success ? 'registration-success' : ''; ?>">
    <!-- Wrap all content except modals in a container -->
    <div class="content-container">
        <?php include 'includes/header.php'; ?>
        
        <?php if (!empty($message) && !$registration_success): ?>
            <div class="message-box <?php echo $registration_success ? 'success' : 'error'; ?>" id="messageBox"><?php echo $message; ?></div>
            <script>
                document.getElementById('messageBox').style.display = 'block';
                setTimeout(function() {
                    document.getElementById('messageBox').style.display = 'none';
                }, 6000);
            </script>
        <?php endif; ?>
        
        <?php if ($registration_success): ?>
        <div class="success-preloader" id="successPreloader">
            <img src="images/majisticlogo.png" alt="maJIStic Logo">
            <h2>Registration Successful!</h2>
            <p>Congratulations! Your registration has been completed.</p>
            <p>Redirecting to confirmation page...</p>
            <div class="progress-bar">
                <div class="progress-fill" id="progressFill"></div>
            </div>
        </div>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                setTimeout(function() {
                    document.getElementById('progressFill').style.width = '100%';
                }, 100);
                setTimeout(function() {
                    window.location.href = 'src/handler/registration_success.php?jis_id=<?php echo $jis_id; ?>';
                }, 3500);
            });
        </script>
        <?php endif; ?>
        
        <section class="registration-container">
            <div class="registration-heading">
                <h1>In-house Students Registration</h1>
            </div>
            <div class="info-card">
                <ul>
                    <li><i class="fas fa-check-circle"></i> Please ensure that all your details are correct before submitting.</li>
                    <li><i class="fas fa-ticket-alt"></i> Event Ticket Price is <strong>Rs. 500 </strong></li>
                    <li><i class="fas fa-money-bill-wave"></i> <strong>Payment must be made to your department coordinator.</strong></li>
                    <li><i class="fas fa-id-card"></i> <a href="#" id="rulesLink">Rules and Regulations</a> </li>
                    <li><i class="fas fa-phone-alt"></i> For queries, contact <a href="mailto:majistic@jiscollege.ac.in"><strong>maJIStic support</strong></a></li>
                    <li><i class="fas fa-shopping-bag"></i> For Merchandise, visit <a href="/majistic/merchandise.php"><strong>Merchandise page</strong></a></li>
                </ul>
            </div>
            <div class="content-wrapper">
                <div class="banner-container">
                    <img id="bannerImage" src="https://i.ibb.co/VWLkTX5j/banner1.png" alt="Event Banner">
                </div>
                <div class="form-container" style="background: rgba(0, 0, 0, 0.5); padding: 20px; text-align: left; border-radius: 10px; border: 1px solid #888;">
                    <img class="majisticheadlogo" src="images/majistic2k25_white.png" alt="maJIStic Logo">
                    <?php if (!$registration_success): ?>
                        <form id="registrationForm" method="POST" action="registration_inhouse.php" onsubmit="return validateForm()">
                            <div class="form-group">
                                <label for="student_name">Student Name:</label>
                                <input type="text" id="student_name" name="student_name" required>
                            </div>
                            <div class="form-group">
                                <label for="gender">Gender:</label>
                                <select id="gender" name="gender" required>
                                    <option value="">--Select Gender--</option>
                                    <option value="male">Male</option>
                                    <option value="female">Female</option>
                                    <option value="prefer_not_say">Prefer not say</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="jis_id">JIS ID:</label>
                                <input type="text" id="jis_id" name="jis_id" pattern="JIS/\d{4}/\d{4}" placeholder="JIS/20XX/0000" required>                        
                            </div>
                            <div class="form-group">
                                <label for="department">Department:</label>
                                <select id="department" name="department" required>
                                    <option value="">--Select Department--</option>
                                    <option value="CSE">CSE</option>
                                    <option value="CSE AI-ML">CSE AI-ML</option>
                                    <option value="CST">CST</option>
                                    <option value="IT">IT</option>
                                    <option value="ECE">ECE</option>
                                    <option value="EE">EE</option>
                                    <option value="BME">BME</option>
                                    <option value="CE">CE</option>
                                    <option value="ME">ME</option>
                                    <option value="AGE">AGE</option>
                                    <option value="BBA">BBA</option>
                                    <option value="MBA">MBA</option>
                                    <option value="BCA">BCA</option>
                                    <option value="MCA">MCA</option>
                                    <option value="Diploma ME">Diploma ME</option>
                                    <option value="Diploma CE">Diploma CE</option>
                                    <option value="Diploma EE">Diploma EE</option>
                                    <option value="B. Pharmacy">Pharmacy</option>                            
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="mobile">Mobile Number:</label>
                                <input type="tel" id="mobile" name="mobile" pattern="\d{10}" placeholder="10-digit Mobile Number" required>
                            </div>
                            <div class="form-group">
                                <label for="email">Email ID:</label>
                                <input type="email" id="email" name="email" required>
                            </div>
                            <!-- <div class="form-group">
                                <label for="inhouse_competition">Do you want to take part in event? (optional)</label>
                            </div>
                            <div class="form-group radio-group">
                                <label><input type="radio" id="inhouse_yes" name="inhouse_competition" value="Yes" required> Yes</label>
                                <label><input type="radio" id="inhouse_no" name="inhouse_competition" value="No" required> No</label>
                            </div>
                            <div class="form-group hidden" id="competition_group">
                                <label for="competition">Select Event:</label>
                                <select id="competition" name="competition">
                                    <option value="">--Select Event--</option>
                                    <option value="Jam Room (Band)">Jam Room (Band)</option>
                                    <option value="Taal Se Taal Mila (Dance)">Taal Se Taal Mila (Dance)</option>
                                    <option value="Fashion Fiesta (Fashion Show)">Fashion Fiesta (Fashion Show)</option>
                                    <option value="Actomania (Drama)">Actomania (Drama)</option>
                                    <option value="The Poetry Slam (Recitation)">The Poetry Slam (Recitation)</option>
                                    <option value="Mic Hunters (Anchoring)">Mic Hunters (Anchoring)</option>
                                </select>
                            </div> -->
                            <input type="hidden" name="inhouse_competition" value="No">
                            <button type="button" id="registerButton">
                                <span>Register</span>
                                <span>Hurry Up!</span>
                            </button>
                            <!-- <p class="mt-2">
                                <a href="#" id="rulesLink">Rules and Regulations</a>
                            </p> -->
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </section>
        
        <div class="scroll-indicator">
            <i class="fas fa-arrow-down"></i> Scroll for registration form
        </div>
        
        <div class="tech-team-contact">
            <div class="contact-header">
                <i class="fas fa-headset"></i>
                <h3>In case of any discrepancy, feel free to contact maJIStic Tech Team</h3>
            </div>
            <div class="contact-cards">
                <div class="contact-card">
                    <div class="contact-card-inner">
                        <div class="contact-info">
                            <h4>Priyanshu Nayan</h4>
                            <p>+91 7004706722</p>
                        </div>
                        <a href="https://wa.me/917004706722" target="_blank" class="whatsapp-btn">
                    <i class="fab fa-whatsapp"></i>
                        </a>
                    </div>
                </div>
                <div class="contact-card">
                    <div class="contact-card-inner">
                        <div class="contact-info">
                            <h4>Sk Riyaz</h4>
                            <p>+91 7029621489</p>
                        </div>
                        <a href="https://wa.me/917029621489" target="_blank" class="whatsapp-btn">
                            <i class="fab fa-whatsapp"></i>
                        </a>
                    </div>
                </div>
                <div class="contact-card">
                    <div class="contact-card-inner">
                        <div class="contact-info">
                            <h4>Ronit Pal</h4>
                            <p>+91 7501005155</p>
                        </div>
                        <a href="https://wa.me/917501005155" target="_blank" class="whatsapp-btn">
                            <i class="fab fa-whatsapp"></i>
                        </a>
                    </div>
                </div>
                <div class="contact-card">
                    <div class="contact-card-inner">
                        <div class="contact-info">
                            <h4>Mohit Kumar</h4>
                            <p>+91 8016804158</p>
                        </div>
                        <a href="https://wa.me/918016804158" target="_blank" class="whatsapp-btn">
                            <i class="fab fa-whatsapp"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <?php include 'includes/footer.php'; ?>
    </div> <!-- End of content-container -->

    <!-- Modals outside content-container -->
    <div id="rulesModal" class="modal">
        <div class="modal-content">
            <h2>Rules and Regulations – maJIStic 2k25</h2>
            <div class="rules-content-wrapper">
                <div class="rules-content">
                    <p>By registering, you agree to the following terms:</p>
                    
                    <h3>1. Registration & Entry</h3>
                    <ul class="rules-list">
                        <li>Registration fees are non-refundable.</li>
                        <li>A QR-based entry ticket will be emailed 2 days before the event.</li>
                        <li>Tickets are non-transferable and valid only for the registered student.</li>
                        <li>Late check-ins will result in automatic ticket cancellation.</li>
                    </ul>
                    
                    <h3>2. Conduct & Disciplinary Actions</h3>
                    <ul class="rules-list">
                        <li>Misbehavior, misconduct, or disruptions will lead to expulsion and fines.</li>
                        <li>Smoking, drinking, and drug use are strictly prohibited.</li>
                        <li>The organizing team reserves the right to deny entry or remove violators.</li>
                    </ul>
                    
                    <h3>3. Merchandise Policies</h3>
                    <ul class="rules-list">
                        <li>No refunds on merchandise purchases.</li>
                        <li>Pick up merchandise one week before the fest (location details via email).</li>
                        <li>No exchanges after 2 days of booking. No shipping available.</li>
                    </ul>
                    
                    <h3>4. Liability & Safety</h3>
                    <ul class="rules-list">
                        <li>The organizers are not responsible for lost or stolen items.</li>
                        <li>Participants must follow safety protocols and event staff instructions.</li>
                        <li>For queries, contact majisticjisce@gmail.com.</li>
                    </ul>
                    
                    <p class="terms-agreement">By registering, you agree to these terms.</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" id="closeRulesModal">Close</button>
            </div>
        </div>
    </div>

    <style>
        /* Rules modal specific styles */
        #rulesModal .modal-content {
            display: flex;
            flex-direction: column;
            max-height: 80vh;
            padding-bottom: 10px;
        }
        
        #rulesModal h2 {
            margin-top: 0;
            position: sticky;
            top: 0;
            background: rgba(54, 51, 55, 0.95);
            padding: 10px 0;
            margin-bottom: 15px;
            z-index: 1;
        }
        
        .rules-content-wrapper {
            overflow-y: auto;
            margin-bottom: 10px;
            padding-right: 5px;
        }
        
        /* Customize scrollbar */
        .rules-content-wrapper::-webkit-scrollbar {
            width: 8px;
        }
        
        .rules-content-wrapper::-webkit-scrollbar-track {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 10px;
        }
        
        .rules-content-wrapper::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.3);
            border-radius: 10px;
        }
        
        .rules-content-wrapper::-webkit-scrollbar-thumb:hover {
            background: rgba(255, 255, 255, 0.5);
        }
        
        .rules-content {
            padding-right: 5px;
        }
        
        .rules-content h3 {
            margin-top: 15px;
            margin-bottom: 8px;
            color: #3498db;
            font-size: 1.1rem;
        }
        
        .terms-agreement {
            margin-top: 15px;
            font-weight: bold;
            text-align: center;
        }
        
        .modal-footer {
            margin-top: auto;
            text-align: center;
        }
        
        #closeRulesModal {
            background-color: #e74c3c;
            color: white;
            border: none;
            border-radius: 4px;
            padding: 8px 15px;
            cursor: pointer;
            width: 80px;
        }
    </style>
    
    <!-- The rest of the modals remain unchanged -->
    <div id="confirmationModal" class="modal">
        <div class="modal-content">
            <p>Please review your entered details carefully before proceeding. Once you confirm, no changes can be made to your registration.</p>
            <div class="modal-buttons">
                <button id="confirmButton">Confirm</button>
                <button id="cancelButton">Cancel</button>
            </div>
        </div>
    </div>

    <div id="paymentModal" class="modal">
        <div class="modal-content">
            <h2>Enter Your Details</h2>
            <form id="paymentForm">
                <div class="form-group">
                    <label for="modal_jis_id">JIS ID:</label>
                    <input type="text" id="modal_jis_id" name="modal_jis_id" required>
                </div>
                <div class="modal-buttons">
                    <button type="submit">Submit</button>
                    <button type="button" onclick="closeModal('paymentModal')">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <div class="loading-spinner display-flex" id="loadingSpinner">
        <div id="spinnerContent">
            <img src="images/majisticlogo.png" alt="maJIStic Logo">
            <p>Submitting...</p>
        </div>
    </div>

    <div id="popupMessageBox" class="message-box hidden"></div>

    <script>
        function showPaymentMessage(message, isSuccess) {
            const messageBox = document.getElementById('popupMessageBox');
            messageBox.textContent = message;
            messageBox.className = 'message-box ' + (isSuccess ? 'success' : 'error');
            messageBox.style.display = 'block';
            setTimeout(() => {
                messageBox.style.display = 'none';
            }, 10000);
        }

        function closeModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
            document.body.classList.remove('modal-open');
            document.querySelector('.content-container').classList.remove('blur');
        }

        // document.getElementById('competition_group').style.display = 'none';
        // document.getElementById('inhouse_yes').addEventListener('change', function() {
        //     document.getElementById('competition_group').style.display = 'block';
        //     // Make the competition field required when "Yes" is selected
        //     document.getElementById('competition').setAttribute('required', 'required');
        // });
        // document.getElementById('inhouse_no').addEventListener('change', function() {
        //     document.getElementById('competition_group').style.display = 'none';
        //     // Remove the required attribute when "No" is selected
        //     document.getElementById('competition').removeAttribute('required');
        //     document.getElementById('competition').value = '';
        // });

        var modal = document.getElementById('confirmationModal');
        var registerButton = document.getElementById('registerButton');
        var confirmButton = document.getElementById('confirmButton');
        var cancelButton = document.getElementById('cancelButton');
        var loadingSpinner = document.getElementById('loadingSpinner');

        registerButton.onclick = function() {
            if (validateForm()) {
                modal.style.display = 'block';
                document.body.classList.add('modal-open');
                document.querySelector('.content-container').classList.add('blur');
            }
        }

        confirmButton.onclick = function() {
            modal.style.display = 'none';
            document.body.classList.remove('modal-open');
            document.querySelector('.content-container').classList.remove('blur');
            loadingSpinner.style.display = 'block';
            document.getElementById('registrationForm').submit();
        }

        cancelButton.onclick = function() {
            modal.style.display = 'none';
            document.body.classList.remove('modal-open');
            document.querySelector('.content-container').classList.remove('blur');
        }

        window.onclick = function(event) {
            if (event.target == modal) {
                modal.style.display = 'none';
                document.body.classList.remove('modal-open');
                document.querySelector('.content-container').classList.remove('blur');
            } else if (event.target == paymentModal) {
                paymentModal.style.display = 'none';
                document.body.classList.remove('modal-open');
                document.querySelector('.content-container').classList.remove('blur');
            }
        }

        var paymentModal = document.getElementById('paymentModal');
        var paymentForm = document.getElementById('paymentForm');

        paymentForm.onsubmit = function(event) {
            event.preventDefault();
            var jis_id = document.getElementById('modal_jis_id').value;
            var xhr = new XMLHttpRequest();
            xhr.open("POST", "src/transaction/check_payment_status.php", true);
            xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
            xhr.onreadystatechange = function () {
                if (xhr.readyState === 4 && xhr.status === 200) {
                    var response = JSON.parse(xhr.responseText);
                    if (response.status === 'pending') {
                        window.location.href = 'src/transaction/payment.php?jis_id=' + jis_id;
                    } else {
                        showPaymentMessage(response.message, response.status === 'success');
                    }
                }
            };
            xhr.send("jis_id=" + jis_id);
        }

        // document.getElementById('competition').addEventListener('change', function() {
        //     var selectedCompetition = this.value;
        //     var bannerImage = document.getElementById('bannerImage');
        //     switch (selectedCompetition) {
        //         case 'Taal Se Taal Mila (Dance)':
        //             bannerImage.src = 'https://i.ibb.co/VWLkTX5j/banner1.png';
        //             break;
        //         case 'Actomania (Drama)':
        //             bannerImage.src = 'https://i.ibb.co/VWLkTX5j/banner1.png';
        //             break;
        //         case 'Jam Room (Band)':
        //             bannerImage.src = 'https://i.ibb.co/VWLkTX5j/banner1.png';
        //             break;
        //         case 'Fashion Fiesta (Fashion Show)':
        //             bannerImage.src = 'https://i.ibb.co/VWLkTX5j/banner1.png';
        //             break;
        //         default:
        //             bannerImage.src = 'https://i.ibb.co/VWLkTX5j/banner1.png';
        //             break;
        //     }
        // });

        // Rules and Regulations modal logic
        document.getElementById('rulesLink').addEventListener('click', function(event) {
            event.preventDefault();
            var rulesModal = document.getElementById('rulesModal');
            rulesModal.style.display = 'block';
            document.body.classList.add('modal-open');
            document.querySelector('.content-container').classList.add('blur');
        });

        document.getElementById('closeRulesModal').addEventListener('click', function() {
            document.getElementById('rulesModal').style.display = 'none';
            document.body.classList.remove('modal-open');
            document.querySelector('.content-container').classList.remove('blur');
        });

        window.addEventListener('click', function(event) {
            const rulesModal = document.getElementById('rulesModal');
            if (event.target === rulesModal) {
                rulesModal.style.display = 'none';
                document.body.classList.remove('modal-open');
                document.querySelector('.content-container').classList.remove('blur');
            }
        });

        function validateForm() {
            clearErrorMessages();
            let isValid = true;
            const errors = [];
            var studentName = document.getElementById('student_name').value.trim();
            var jisId = document.getElementById('jis_id').value.trim();
            var mobile = document.getElementById('mobile').value.trim();
            var email = document.getElementById('email').value.trim();
            var department = document.getElementById('department').value.trim();
            var gender = document.getElementById('gender').value.trim();
            // var inhouseYes = document.getElementById('inhouse_yes').checked;
            // var inhouseNo = document.getElementById('inhouse_no').checked;
            // var competition = document.getElementById('competition').value.trim();

            if (studentName === "" || /\d/.test(studentName)) {
                errors.push({id: 'student_name', message: 'Student name should not contain numbers'});
                isValid = false;
            }

            var jisIdPattern = /^(JISU|JIS)\/\d{4}\/\d{4}$/;
            if (jisId === "" || !jisIdPattern.test(jisId)) {
                errors.push({id: 'jis_id', message: 'Format should be JIS/20XX/0000'});
                isValid = false;
            }

            var mobilePattern = /^\d{10}$/;
            if (mobile === "" || !mobilePattern.test(mobile)) {
                errors.push({id: 'mobile', message: 'Enter 10-digit mobile number'});
                isValid = false;
            }

            var emailPattern = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
            if (email === "" || !emailPattern.test(email)) {
                errors.push({id: 'email', message: 'Please enter a valid email address'});
                isValid = false;
            }

            if (department === "") {
                errors.push({id: 'department', message: 'Please select a department'});
                isValid = false;
            }

            if (gender === "") {
                errors.push({id: 'gender', message: 'Please select gender'});
                isValid = false;
            }
            
            // Check if the user has selected at least one radio button (Yes or No)
            // if (!inhouseYes && !inhouseNo) {
            //     errors.push({id: 'inhouse_yes', message: 'Please select whether you want to participate in an event'});
            //     isValid = false;
            // }
            
            // // If "Yes" is selected, ensure an event is chosen
            // if (inhouseYes && competition === "") {
            //     errors.push({id: 'competition', message: 'Please select an event to participate in'});
            //     isValid = false;
            // }
            
            if (errors.length > 0) {
                errors.forEach((error, index) => {
                    setTimeout(() => {
                        showError(error.id, error.message);
                    }, index * 100);
                });
            }

            return isValid;
        }
        
        function showError(inputId, message) {
            const input = document.getElementById(inputId);
            input.classList.add('input-error');
            const errorDiv = document.createElement('div');
            errorDiv.className = 'error-message';
            errorDiv.textContent = message;
            input.parentElement.appendChild(errorDiv);
            if (inputId === 'inhouse_yes' || inputId === 'inhouse_no') {
                const radioGroup = document.querySelector('.radio-group');
                radioGroup.appendChild(errorDiv);
            }
            setTimeout(() => {
                if (errorDiv && errorDiv.parentNode) {
                    errorDiv.remove();
                }
                setTimeout(() => {
                    input.classList.remove('input-error');
                }, 1500);
            }, 3500);
        }
        
        function clearErrorMessages() {
            const errorMessages = document.querySelectorAll('.error-message');
            errorMessages.forEach(element => {
                element.remove();
            });
            const errorInputs = document.querySelectorAll('.input-error');
            errorInputs.forEach(input => {
                input.classList.remove('input-error');
            });
        }
        
        const formInputs = document.querySelectorAll('#registrationForm input, #registrationForm select');
        formInputs.forEach(input => {
            input.addEventListener('input', function() {
                this.classList.remove('input-error');
                const errorDiv = this.parentElement.querySelector('.error-message');
                if (errorDiv) {
                    errorDiv.remove();
                }
            });
        });

        window.onload = function() {
            document.getElementById('registrationForm').reset();
            const scrollIndicator = document.querySelector('.scroll-indicator');
            const formContainer = document.querySelector('.form-container');
            let indicatorShown = true;
            
            function checkScrollIndicator() {
                if (document.body.classList.contains('registration-success')) {
                    scrollIndicator.style.display = 'none';
                    return;
                }
                
                if (window.innerWidth <= 768) {
                    const formRect = formContainer.getBoundingClientRect();
                    if (formRect.top < window.innerHeight && formRect.bottom > 0) {
                        scrollIndicator.style.opacity = '0';
                        setTimeout(() => {
                            scrollIndicator.style.display = 'none';
                        }, 300);
                        indicatorShown = false;
                    } else if (!indicatorShown) {
                        scrollIndicator.style.display = 'flex';
                        setTimeout(() => {
                            scrollIndicator.style.opacity = '0.95';
                        }, 10);
                        indicatorShown = true;
                    }
                } else {
                    scrollIndicator.style.display = 'none';
                    indicatorShown = false;
                }
            }
            
            checkScrollIndicator();
            window.addEventListener('scroll', checkScrollIndicator);
            window.addEventListener('resize', checkScrollIndicator);
            
            scrollIndicator.addEventListener('click', function() {
                formContainer.scrollIntoView({ behavior: 'smooth', block: 'start' });
            });
            
            let userHasScrolledToForm = false;
            window.addEventListener('scroll', function() {
                const formRect = formContainer.getBoundingClientRect();
                if (formRect.top < window.innerHeight && formRect.bottom > 0) {
                    userHasScrolledToForm = true;
                }
                if (userHasScrolledToForm) {
                    scrollIndicator.style.display = 'none';
                }
            });
        };
    </script>
</body> 
</html>