<?php
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
    <title>Alumni Registration</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <!-- Adding Roboto font -->
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
        
        /* Styles for the success preloader */
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
            transform: translate(-50%, -50%);
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
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
            text-align: left;
        }

        .modal-content h2 {
            margin-bottom: 15px;
            font-size: 1.5rem;
        }

        .rules-list {
            list-style-type: decimal;
            padding-left: 25px;
            margin: 0 0 20px 0;
        }

        .rules-list li {
            margin-bottom: 8px;
            line-height: 1.4;
            word-spacing: normal;
            text-align: left;
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
        
        <!-- Success preloader - shown only when registration is successful -->
        <?php if ($registration_success): ?>
        <div class="success-preloader" id="successPreloader">
            <img src="images/majisticlogo.png" alt="maJIStic Logo">
            <h2>Registration Successful!</h2>
            <p>Congratulations! Your registration has been completed.</p>
            <?php if (defined('PAYMENT_ENABLED') && PAYMENT_ENABLED): ?>
            <p>Redirecting to Payment page...</p>
            <?php else: ?>
            <p>Redirecting to confirmation page...</p>
            <?php endif; ?>
            <div class="progress-bar">
                <div class="progress-fill" id="progressFill"></div>
            </div>
        </div>
        <script>
            // Start the progress bar animation immediately
            document.addEventListener('DOMContentLoaded', function() {
                setTimeout(function() {
                    document.getElementById('progressFill').style.width = '100%';
                }, 100);
                
                // Redirect after the animation completes
                setTimeout(function() {
                    <?php if (defined('PAYMENT_ENABLED') && PAYMENT_ENABLED): ?>
                    window.location.href = 'src/transaction/payment.php?jis_id=<?php echo $jis_id; ?>&alumni=1'; // Payment redirect
                    <?php else: ?>
                    window.location.href = 'src/handler/registration_success.php?jis_id=<?php echo $jis_id; ?>&alumni=1'; // Redirect to success page
                    <?php endif; ?>
                }, 3500); // Just after the progress bar completes
            });
        </script>
        <?php endif; ?>
        
        <section class="registration-container">
            <div class="registration-heading">
                <h1>Alumni Registration</h1>
            </div>
            <div class="info-card">
                <ul>
                    <li><i class="fas fa-check-circle"></i> Please ensure that all your details are correct before submitting.</li>
                    <li><i class="fas fa-ticket-alt"></i> Event Ticket Price is <strong>Rs. 1000</strong> for Alumni</li>
                    <li><i class="fas fa-id-card"></i> <a href="#" id="rulesLink">Rules and Regulations</a></li>
                    <li><i class="fas fa-phone-alt"></i> For queries, contact <strong>maJIStic support</strong></li>
                    <li><i class="fas fa-shopping-bag"></i> For Merchandise, visit <a href="merchandise.php"><strong>Merchandise page</strong></a></li>
                </ul>
            </div>
            <div class="content-wrapper">
                <div class="banner-container">
                    <img id="bannerImage" src="https://i.ibb.co/VWLkTX5j/banner1.png" alt="Event Banner">
                </div>
                <div class="form-container" style="background: rgba(0, 0, 0, 0.5); padding: 20px; text-align: left; border-radius: 10px; border: 1px solid #888;">
                    <img class="majisticheadlogo" src="images/majistic2k25_white.png" alt="maJIStic Logo">
                    <?php if (!$registration_success): ?>
                        <form id="registrationForm" method="POST" action="registration_alumni.php" onsubmit="return validateForm()">
                            <input type="hidden" name="registration_type" value="alumni">
                            <div class="form-group">
                                <label for="student_name">Full Name:</label>
                                <input type="text" id="student_name" name="student_name" required>
                            </div>
                            <div class="form-group">
                                <label for="gender">Gender:</label>
                                <select id="gender" name="gender" required>
                                    <option value="">--Select Gender--</option>
                                    <option value="male">Male</option>
                                    <option value="female">Female</option>
                                    <option value="other">Prefer not say</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="jis_id">JIS ID:</label>
                                <input type="text" id="jis_id" name="jis_id" pattern="JIS/\d{4}/\d{4}" placeholder="JIS/20XX/0000" required>                        
                            </div>
                            <div class="form-group">
                                <label for="passout_year">Passout Year:</label>
                                <select id="passout_year" name="passout_year" required>
                                    <option value="">--Select Passout Year--</option>
                                    <?php
                                        for ($year = 2024; $year >= 2001; $year--) {
                                            echo "<option value=\"$year\">$year</option>";
                                        }
                                    ?>
                                </select>
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
                            <div class="form-group">
                                <label for="current_organization">Current Organization/Company (Optional):</label>
                                <input type="text" id="current_organization" name="current_organization">
                            </div>
                            <button type="button" id="registerButton">
                                <span>Register</span>
                                <span>Reconnect with JIS!</span>
                            </button>
                            <!-- <p class="mt-2">
                                <a href="#" id="rulesLink">Rules and Regulations</a>
                            </p> -->
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </section>
        
        <!-- Scroll indicator for mobile -->
        <div class="scroll-indicator">
            <i class="fas fa-arrow-down"></i> Scroll for registration form
        </div>
        
        <!-- Contact Box -->
        <div class="tech-team-contact">
            <div class="contact-header">
                <i class="fas fa-headset"></i>
                <h3>In case of any discrepancy, feel free to contact maJIStic Alumni Support Team</h3>
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
            </div>
        </div>
        
        <?php include 'includes/footer.php'; ?>
    </div> <!-- End of content-container -->

    <!-- Modals outside content-container -->
    <!-- Rules and Regulations Modal -->
    <div id="rulesModal" class="modal">
        <div class="modal-content">
            <h2>Rules and Regulations</h2>
            <ul class="rules-list">
                <li>Payment will be accepted after registration.</li>
                <li>You will receive a confirmation email upon successful registration.</li>
                <li>Only cash payments are accepted.</li>
                <li>Please bring your Alumni ID or any Government ID for verification on the event day.</li>
            </ul>
            <button type="button" id="closeRulesModal" style="background-color: Red; width: 80px; display: block; margin: 0 auto;">Close</button>
        </div>
    </div>

    <!-- Confirmation Modal -->
    <div id="confirmationModal" class="modal">
        <div class="modal-content">
            <p>Please review your entered details carefully before proceeding. Once you confirm, no changes can be made to your registration.</p>
            <div class="modal-buttons">
                <button id="confirmButton">Confirm</button>
                <button id="cancelButton">Cancel</button>
            </div>
        </div>
    </div>

    <!-- Loading Spinner -->
    <div class="loading-spinner display-flex" id="loadingSpinner">
        <div id="spinnerContent">
            <img src="images/majisticlogo.png" alt="maJIStic Logo">
            <p>Submitting...</p>
        </div>
    </div>

    <!-- Popup Message Box -->
    <div id="popupMessageBox" class="message-box hidden"></div>

    <script>
        function closeModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
            document.body.classList.remove('modal-open');
            document.querySelector('.content-container').classList.remove('blur');
        }

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
            loadingSpinner.style.display = 'block'; // Show loading spinner
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
            }
        }

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

        // Function to validate form
        function validateForm() {
            // Reset all error messages first
            clearErrorMessages();
            
            let isValid = true;
            const errors = [];
            var studentName = document.getElementById('student_name').value.trim();
            var jisId = document.getElementById('jis_id').value.trim();
            var mobile = document.getElementById('mobile').value.trim();
            var email = document.getElementById('email').value.trim();
            var passoutYear = document.getElementById('passout_year').value.trim();
            var department = document.getElementById('department').value.trim();
            var gender = document.getElementById('gender').value.trim();

            // Validate student name (no numeric values allowed and not empty)
            if (studentName === "" || /\d/.test(studentName)) {
                errors.push({id: 'student_name', message: 'Name should not contain numbers'});
                isValid = false;
            }

            // Validate JIS ID format
            var jisIdPattern = /^JIS\/\d{4}\/\d{4}$/;
            if (jisId === "" || !jisIdPattern.test(jisId)) {
                errors.push({id: 'jis_id', message: 'Format should be JIS/20XX/0000'});
                isValid = false;
            }

            // Validate mobile number (10 digits)
            var mobilePattern = /^\d{10}$/;
            if (mobile === "" || !mobilePattern.test(mobile)) {
                errors.push({id: 'mobile', message: 'Enter 10-digit mobile number'});
                isValid = false;
            }

            // Validate email
            var emailPattern = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
            if (email === "" || !emailPattern.test(email)) {
                errors.push({id: 'email', message: 'Please enter a valid email address'});
                isValid = false;
            }

            // Validate passout year
            if (passoutYear === "") {
                errors.push({id: 'passout_year', message: 'Please select passout year'});
                isValid = false;
            }

            // Validate department
            if (department === "") {
                errors.push({id: 'department', message: 'Please select a department'});
                isValid = false;
            }

            // Validate gender
            if (gender === "") {
                errors.push({id: 'gender', message: 'Please select gender'});
                isValid = false;
            }
            
            // Display errors with a slight delay between each to make them noticeable
            if (errors.length > 0) {
                errors.forEach((error, index) => {
                    setTimeout(() => {
                        showError(error.id, error.message);
                    }, index * 100); // 100ms delay between each error display
                });
            }

            return isValid;
        }
        
        // Function to show error message
        function showError(inputId, message) {
            const input = document.getElementById(inputId);
            input.classList.add('input-error');
            
            // Create error message element
            const errorDiv = document.createElement('div');
            errorDiv.className = 'error-message';
            errorDiv.textContent = message;
            
            // Add to parent and position properly
            input.parentElement.appendChild(errorDiv);
            
            // Automatically remove the error message and class after animation ends
            setTimeout(() => {
                if (errorDiv && errorDiv.parentNode) {
                    errorDiv.remove();
                }
                
                // Keep the error border a bit longer to ensure the user notices
                setTimeout(() => {
                    input.classList.remove('input-error');
                }, 1500);
            }, 3500); // Match this to the animation duration in CSS
        }
        
        // Function to clear all error messages immediately
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
        
        // Add input event listeners to clear errors when user starts typing
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

        // Clear the form when the user navigates back to the page
        window.onload = function() {
            document.getElementById('registrationForm').reset();
            
            // Show/hide scroll indicator based on scroll position and screen size
            const scrollIndicator = document.querySelector('.scroll-indicator');
            const formContainer = document.querySelector('.form-container');
            let indicatorShown = true;
            
            function checkScrollIndicator() {
                // Don't show scroll indicator if registration was successful
                if (document.body.classList.contains('registration-success')) {
                    scrollIndicator.style.display = 'none';
                    return;
                }
                
                if (window.innerWidth <= 768) {
                    const formRect = formContainer.getBoundingClientRect();
                    
                    // Only show indicator when form is not visible in viewport
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
            
            // Check initially and on scroll
            checkScrollIndicator();
            window.addEventListener('scroll', checkScrollIndicator);
            window.addEventListener('resize', checkScrollIndicator);
            
            // Smooth scroll to form when indicator is clicked
            scrollIndicator.addEventListener('click', function() {
                formContainer.scrollIntoView({ behavior: 'smooth', block: 'start' });
            });
            
            // Don't show the indicator again after user has scrolled to the form once
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