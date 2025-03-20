<?php
include 'src/main/registration_handler.php';
// Include payment configuration
if(file_exists('src/config/payment_config.php')) {
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
    </style>
</head>
<body class="<?php echo $registration_success ? 'registration-success' : ''; ?>">
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
        <p>Redirecting to confirmation page...</p>
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
                window.location.href = 'src/handler/registration_success.php?jis_id=<?php echo $jis_id; ?>'; // Redirect to success page
            }, 3500); // Just after the progress bar completes
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
                <li><i class="fas fa-ticket-alt"></i> Event Ticket Price is <del>Rs. 600</del> <strong>Rs. 500</strong> [Early Bird]</li>
                <li><i class="fas fa-money-bill-wave"></i> <strong>Payment must be made to your department coordinator.</strong></li>
                <li><i class="fas fa-id-card"></i> College ID is mandatory while checking in on the event day.</li>
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
                                <option value="female">Prefer not say</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="jis_id">JIS ID:</label>
                            <input type="text" id="jis_id" name="jis_id" pattern="JIS/\d{4}/\d{4}" placeholder="JIS/20XX/0000" required>                        
                        </div>
                        <!-- Roll number field removed -->
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
                        </div>
                        <!-- <button type="button" id="registerButton">Register</button> -->
                        <button type="button" id="registerButton">
                            <span>Register</span>
                            <span>Hurry Up!</span>
                        </button>
                        <p class="mt-2">Already registered but payment pending? <a href="#" id="paymentLink">Pay Now</a></p>
                        
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
        </div>
    </div>
    
    <?php include 'includes/footer.php'; ?>
    
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

    <!-- Payment Modal -->
    <div id="paymentModal" class="modal">
        <div class="modal-content">
            <h2>Enter Your Details</h2>
            <form id="paymentForm">
                <div class="form-group">
                    <label for="modal_jis_id">JIS ID:</label>
                    <input type="text" id="modal_jis_id" name="modal_jis_id" required>
                </div>
                <!-- Roll number field removed -->
                <div class="modal-buttons">
                    <button type="submit">Submit</button>
                    <button type="button" onclick="closeModal('paymentModal')">Cancel</button>
                </div>
            </form>
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
        function showPaymentMessage(message, isSuccess) {
            const messageBox = document.getElementById('popupMessageBox');
            messageBox.textContent = message;
            messageBox.className = 'message-box ' + (isSuccess ? 'success' : 'error');
            messageBox.style.display = 'block';
            setTimeout(() => {
                messageBox.style.display = 'none';
            }, 10000); // Hide after 10 seconds
        }

        function closeModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
        }

        document.getElementById('competition_group').style.display = 'none';
        document.getElementById('inhouse_yes').addEventListener('change', function() {
            document.getElementById('competition_group').style.display = 'block';
        });
        document.getElementById('inhouse_no').addEventListener('change', function() {
            document.getElementById('competition_group').style.display = 'none';
            document.getElementById('competition').value = ''; // Clear competition selection
        });

        var modal = document.getElementById('confirmationModal');
        var registerButton = document.getElementById('registerButton');
        var confirmButton = document.getElementById('confirmButton');
        var cancelButton = document.getElementById('cancelButton');
        var loadingSpinner = document.getElementById('loadingSpinner');

        registerButton.onclick = function() {
            if (validateForm()) {
                modal.style.display = 'block';
            }
        }

        confirmButton.onclick = function() {
            loadingSpinner.style.display = 'block'; // Show loading spinner
            document.getElementById('registrationForm').submit();
        }

        cancelButton.onclick = function() {
            modal.style.display = 'none';
        }

        window.onclick = function(event) {
            if (event.target == modal) {
                modal.style.display = 'none';
            } else if (event.target == paymentModal) {
                paymentModal.style.display = 'none';
            }
        }

        var paymentModal = document.getElementById('paymentModal');
        var paymentLink = document.getElementById('paymentLink');
        var paymentForm = document.getElementById('paymentForm');

        paymentLink.onclick = function() {
            paymentModal.style.display = 'block';
        }

        paymentForm.onsubmit = function(event) {
            event.preventDefault();
            var jis_id = document.getElementById('modal_jis_id').value;
            // Roll number reference removed
            
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

        // Update banner image based on selected competition
        document.getElementById('competition').addEventListener('change', function() {
            var selectedCompetition = this.value;
            var bannerImage = document.getElementById('bannerImage');
            switch (selectedCompetition) {
                case 'Taal Se Taal Mila (Dance Events)':
                    bannerImage.src = 'https://i.ibb.co/0V1Cxvnr/dance.png';
                    break;
                case 'Actomania (Drama)':
                    bannerImage.src = 'https://i.ibb.co/vvLXHMDF/drama.jpg';
                    break;
                case 'Jam Room (Band Events)':
                    bannerImage.src = 'https://i.ibb.co/5h1Kw4KB/band.jpg';
                    break;
                case 'Fashion Fiesta (Fashion Show)':
                    bannerImage.src = 'https://i.ibb.co/9drCNqN/fashion.jpg';
                    break;
                default:
                    bannerImage.src = 'https://i.ibb.co/VWLkTX5j/banner1.png';
                    break;
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
            // Remove roll number validation
            var department = document.getElementById('department').value.trim();
            var gender = document.getElementById('gender').value.trim();

            // Validate student name (no numeric values allowed and not empty)
            if (studentName === "" || /\d/.test(studentName)) {
                errors.push({id: 'student_name', message: 'Student name should not contain numbers'});
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

            // Roll number validation removed

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
            
            // Position adjustment for special cases
            if (inputId === 'inhouse_yes' || inputId === 'inhouse_no') {
                // For radio buttons, append to the radio group container
                const radioGroup = document.querySelector('.radio-group');
                radioGroup.appendChild(errorDiv);
            }
            
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