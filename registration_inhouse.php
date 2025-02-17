<?php
include 'src/main/registration_handler.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Registration</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <?php include 'includes/links.php'; ?>
    <link rel="stylesheet" href="style.css">
    <style>
        /* Modal styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgb(0,0,0);
            background-color: rgba(0, 0, 0, 0.49);
            padding-top: 60px;
        }
        .modal-content {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: rgba(54, 52, 52, 0.32); /* Semi-transparent background */
            backdrop-filter: blur(10px); /* Blur effect */
            margin: 1% auto;
            padding: 50px;
            border: 1px solid #888;
            width: 90%;
            max-width: 600px;
            text-align: center;
        }
        .modal-buttons {
            margin-top: 20px;
        }
        .modal-buttons button {
            margin: 0 10px;
            padding: 10px 20px;
            font-size: 16px;
            background-color: black; /* Black background */
            color: white; /* White text */
            border: none; /* Remove border */
            border-radius: 5px; /* Rounded corners */
            cursor: pointer; /* Pointer cursor */
        }
        .modal-buttons button:hover {
            background-color: #333; /* Darker background on hover */
        }
        .form-container {
            margin: 40px; /* Add margin to create gap */
            height: auto; /* Make height flexible */
            max-width: 40rem; /* Ensure it doesn't exceed the container */
            max-height: 100%; /* Ensure it doesn't exceed the container */
        }
        .banner-container{
            margin: 40px;
        }
        .form-group input, .form-group select {
            width: 100%; /* Increase input fields width */
        }
        .form-group select option {
            background-color:rgba(16, 66, 45, 0.73); /* Adsd background color to dropdown options */
        }
        .payment-modal {
            display: none;
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgb(0,0,0);
            background-color: rgba(0, 0, 0, 0.49);
            padding-top: 60px;
        }
        .payment-modal-content {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: rgba(54, 52, 52, 0.32); /* Semi-transparent background */
            backdrop-filter: blur(10px); /* Blur effect */
            margin: 1% auto;
            padding: 50px;
            border: 1px solid #888;
            width: 90%;
            max-width: 600px;
            text-align: center;
        }
        .majisticheadlogo {
            max-width: 100%;
            height: auto;
        }
        .loading-spinner {
            display: none; /* Hidden by default */
            position: fixed;
            z-index: 1000;
            top:0;
            padding-top: 42vh;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.73); /* Semi-black and semi-transparent background */
            justify-content: center;
            align-items: center;
        }
        #spinnerContent {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }.loading-spinner img {
            width: 200px;
            height: auto;
            animation: pulse 0.8s infinite;
        }
        .loading-spinner p {
            color: white;
            font-size: 1rem;
            margin-top: 20px;
        }
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.1); }
            100% { transform: scale(1); }
        }

        /* CSS for Register Button */
#registerButton {
  position: relative;
  overflow: hidden;
  border: 1px solid #18181a;
  color: #18181a;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  font-size: 15px;
  padding: 12px 24px;
  cursor: pointer;
  background: #fff;
  user-select: none;
  border-radius: 8px;
  min-width: 150px;
  transition: color 0.3s ease;
}

#registerButton span:first-child {
  position: relative;
  transition: opacity 0.3s ease;
  z-index: 20;
}

#registerButton span:last-child {
  position: absolute;
  color: white;
  z-index: 20;
  opacity: 0;
  transform: translateY(20px);
  transition: all 0.4s ease;
}

#registerButton:after {
  content: "";
  position: absolute;
  top: 100%;
  left: 0;
  width: 100%;
  height: 100%;
  background: #4CAF50;
  transition: all 0.4s cubic-bezier(0.48, 0, 0.12, 1);
  z-index: 10;
}

#registerButton:hover:after {
  top: 0;
}

#registerButton:hover span:first-child {
  opacity: 0;
}

#registerButton:hover span:last-child {
  opacity: 1;
  transform: translateY(0);
}


/* CSS for Payment Button */
p.mt-2 {
  color: white; /* Color for the paragraph text */
  font-size: 16px; /* Font size for the paragraph */
  margin: 0; /* Remove default margin */
  display: flex; /* Use flexbox to align items in a row */
  align-items: center; /* Center align items vertically */
}

#paymentLink {
  align-self: center;
  background-color:rgb(86, 241, 92); /* Initial background color */
  border-radius: 15px 225px 255px 15px 15px 255px 225px 15px;
  border-style: solid;
  border-width: 2px;
  border-color: #41403e; /* Keep the border color the same */
  box-shadow: rgba(0, 0, 0, .2) 15px 28px 25px -18px;
  box-sizing: border-box;
  color: #41403e; /* Text color */
  cursor: pointer;
  display: inline-block;
  font-family: Neucha, sans-serif;
  font-size: 1rem;
  line-height: 23px;
  outline: none;
  padding: .75rem;
  text-decoration: none;
  transition: all 235ms ease-in-out; /* Smooth transition */
  user-select: none;
  -webkit-user-select: none;
  touch-action: manipulation;
}

#paymentLink:hover {
  background-color: #4CAF50; /* Change background color to green on hover */
  color: #fff; /* Change text color to white on hover */
  box-shadow: rgba(76, 175, 80, 0.5) 2px 8px 8px -5px; /* Slight green shadow */
  transform: translate3d(0, 2px, 0);
}

#paymentLink:focus {
  box-shadow: rgba(0, 0, 0, .3) 2px 8px 4px -6px; /* Focus shadow */
}


#finalPay {
  align-items: center;
  background-color: #4CAF50; /* A vibrant green for the button */
  border: 2px solid #FFD700; /* Yellow border for contrast */
  border-radius: 8px;
  box-sizing: border-box;
  color: #fff; /* White text color */
  cursor: pointer;
  display: flex;
  font-family: Inter, sans-serif;
  font-size: 16px;
  height: 48px;
  justify-content: center;
  line-height: 24px;
  max-width: 100%;
  padding: 0 25px;
  position: relative;
  text-align: center;
  text-decoration: none;
  user-select: none;
  -webkit-user-select: none;
  touch-action: manipulation;
  transition: background-color 0.3s ease; /* Smooth transition for background color */
  margin-top: 10px; /* Add margin to create space above the button */
}

#finalPay:after {
  background-color: rgba(203, 245, 88, 0.92); /* Light overlay for hover effect */
  border-radius: 8px;
  content: "";
  display: block;
  height: 48px;
  left: 0;
  width: 100%;
  position: absolute;
  top: -2px;
  transform: translate(8px, 8px);
  transition: transform 0.2s ease-out;
  z-index: -1;
}

#finalPay:hover:after {
  transform: translate(0, 0);
}

#finalPay:active {
  background-color: #3e8e41; /* Darker green when active */
  outline: 0;
}

#finalPay:hover {
  background-color: #45a049; /* Slightly lighter green on hover */
  outline: 0;
}

@media (min-width: 768px) {
  .button-56 {
    padding: 0 40px;
  }
}


/*  for this div class <div id="confirmationModal" class="modal"> */

.modal-buttons button {
    padding: 10px 20px; /* Add some padding for better appearance */
    border: none; /* Remove default border */
    border-radius: 5px; /* Rounded corners */
    font-size: 16px; /* Font size */
    cursor: pointer; /* Pointer cursor on hover */
    transition: transform 0.3s ease, background-color 0.3s ease; /* Smooth transition */
}

#confirmButton {
    background-color: #4CAF50; /* Green background for Confirm button */
    color: white; /* White text color */
}

#cancelButton {
    background-color: #f44336; /* Red background for Cancel button */
    color: white; /* White text color */
}

.modal-buttons button:hover {
    transform: scale(1.05); /* Zoom in effect */
}

#confirmButton:hover {
    background-color: #45a049; /* Darker green on hover */
}

#cancelButton:hover {
    background-color: #e53935; /* Darker red on hover */
}

/* for this div class <div id="paymentModal" class="modal"> */
.modal-buttons button {
    padding: 10px 20px; /* Add some padding for better appearance */
    border: none; /* Remove default border */
    border-radius: 5px; /* Rounded corners */
    font-size: 16px; /* Font size */
    cursor: pointer; /* Pointer cursor on hover */
    transition: transform 0.3s ease, background-color 0.3s ease; /* Smooth transition */
}

.modal-buttons button[type="submit"] {
    background-color: #4CAF50; /* Green background for Submit button */
    color: white; /* White text color */
}

.modal-buttons button[type="button"] {
    background-color: #f44336; /* Red background for Cancel button */
    color: white; /* White text color */
}

.modal-buttons button:hover {
    transform: scale(1.05); /* Zoom in effect */
}

.modal-buttons button[type="submit"]:hover {
    background-color: #45a049; /* Darker green on hover */
}

.modal-buttons button[type="button"]:hover {
    background-color: #e53935; /* Darker red on hover */
}

.success-message {
    width: 100%;
    max-width: 500px;
    background: rgba(0, 0, 0, 0.5);
    color: white;
    padding: 20px;
    text-align: center;
    border-radius: 10px;
    border: 1px solid #888;
    margin: 0 auto;
}
@media (max-width: 768px) {
    .success-message {
        width: 95%;
        padding: 15px;
    }
}
.verification-table {
    width: 100%;
    margin-top: 20px;
    border-collapse: collapse;
}
.verification-table th, .verification-table td {
    border: 1px solid #ddd;
    padding: 8px;
    text-align: left;
}
.verification-table th {
    background-color:rgb(62, 143, 136);
    color: white;
    text-align: center; /* Center align the text */
}
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>
    <?php if (!empty($message)): ?>
        <div class="message-box <?php echo $registration_success ? 'success' : 'error'; ?>" id="messageBox"><?php echo $message; ?></div>
        <script>
            document.getElementById('messageBox').style.display = 'block';
            setTimeout(function() {
                document.getElementById('messageBox').style.display = 'none';
            }, 6000);
        </script>
    <?php endif; ?>
    <section class="registration-container">
    <h1 style="text-align: center; color: white; margin-bottom: 20px;">Inhouse Students Registration</h1>
        <div class="info-card">
            <ul>
                <li>✅ Please ensure that all your details are correct before submitting the registration form.</li>
                <li>🎟️ Event Ticket Price is <del>Rs. 400</del> Rs. 250 [Early Bird]</li>
                <li>📞 For any queries, contact <strong>maJIStic support</strong></li>
                <li>🛍️ For Merchandise, go to the <a href="merchandise.php"><strong>Merchandise page</strong></a></li>
            </ul>
        </div>
        <div class="content-wrapper">
            <div class="banner-container">
                <img id="bannerImage" src="https://i.ibb.co/VWLkTX5j/banner1.png" alt="Event Banner">
            </div>
            <div class="form-container" style="background: rgba(0, 0, 0, 0.5); padding: 20px; text-align: left; border-radius: 10px; border: 1px solid #888;">
                <img class="majisticheadlogo" src="images/majisticlogo.png" alt="maJIStic Logo">
                <?php if ($registration_success): ?>
                    <div class="success-message" style="width: 500px; background: rgba(0, 0, 0, 0.5); padding: 20px; text-align: center; border-radius: 10px; border: 1px solid #888;">                        <p><?php echo $message; ?></p>
                        <table class="verification-table">
                            <tr>
                                <th colspan="2">VERIFY YOUR DETAILS BEFORE PAYMENT</th>
                            </tr>
                            <tr>
                                <td>Student Name</td>
                                <td><?php echo htmlspecialchars($student_name); ?></td>
                            </tr>
                            <tr>
                                <td>JIS ID</td>
                                <td><?php echo htmlspecialchars($jis_id); ?></td>
                            </tr>
                            <tr>
                                <td>Mobile Number</td>
                                <td><?php echo htmlspecialchars($mobile); ?></td>
                            </tr>
                            <tr>
                                <td>Email</td>
                                <td><?php echo htmlspecialchars($email); ?></td>
                            </tr>
                            <tr>
                                <td>Roll Number</td>
                                <td><?php echo htmlspecialchars($roll_no); ?></td>
                            </tr>
                            <tr>
                                <td>Department</td>
                                <td><?php echo htmlspecialchars($department); ?></td>
                            </tr>
                            <tr>
                                <td>Inhouse Competition</td>
                                <td><?php echo htmlspecialchars($inhouse_competition); ?></td>
                            </tr>
                            <tr>
                                <td>Competition</td>
                                <td><?php echo htmlspecialchars($competition); ?></td>
                            </tr>
                        </table>
                        <?php if ($email_option): ?>
                            <p>Would you like to receive your registration details via email?</p>
                            <form method="POST" action="registration_inhouse.php">
                                <input type="hidden" name="email_option" value="1">
                                <input type="hidden" name="student_name" value="<?php echo $student_name; ?>">
                                <input type="hidden" name="jis_id" value="<?php echo $jis_id; ?>">
                                <input type="hidden" name="mobile" value="<?php echo $mobile; ?>">
                                <input type="hidden" name="email" value="<?php echo $email; ?>">
                                <input type="hidden" name="roll_no" value="<?php echo $roll_no; ?>">
                                <input type="hidden" name="payment_transaction_id" value="<?php echo $payment_transaction_id; ?>"> <!-- Add payment transaction ID -->
                                <button type="submit">Send Email</button>
                            </form>
                        <?php endif; ?>
                    </div>
                    <button id="finalPay" style="width: 150px;" onclick="window.location.href='src/transaction/payment.php?jis_id=<?php echo $jis_id; ?>'">Pay Now</button>
                <?php else: ?>
                    <form id="registrationForm" method="POST" action="registration_inhouse.php" onsubmit="return validateForm()">                        <div class="form-group">
                            <label for="student_name">Student Name:</label>
                            <input type="text" id="student_name" name="student_name" required>
                        </div>
                        <div class="form-group">
                            <label for="gender">Gender:</label>
                            <select id="gender" name="gender" required>
                                <option value="">--Select Gender--</option>
                                <option value="male">Male</option>
                                <option value="female">Female</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="jis_id">JIS ID:</label>
                            <input type="text" id="jis_id" name="jis_id" pattern="JIS/\d{4}/\d{4}" placeholder="JIS/2025/0001" required>                        </div>
                        <div class="form-group">
                            <label for="roll_no">Roll Number:</label>
                            <input type="text" id="roll_no" name="roll_no" pattern="\d+" required>
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
                                <option value="B. Pharmacy">B. Pharmacy</option>                            </select>
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
                            <label for="inhouse_competition">Do you want to take part in inhouse competitions?</label>
                        </div>
                        <div class="form-group radio-group">
                            <label><input type="radio" id="inhouse_yes" name="inhouse_competition" value="Yes" required> Yes</label>
                            <label><input type="radio" id="inhouse_no" name="inhouse_competition" value="No" required> No</label>
                        </div>
                        <div class="form-group hidden" id="competition_group">
                            <label for="competition">Select Competition:</label>
                            <select id="competition" name="competition">
                                <option value="">--Select Competition--</option>
                                <option value="Taal Se Taal Mila (Dance)">Taal Se Taal Mila (Dance Competition)</option>
                                <option value="Actomania (Drama)">Actomania (Drama Competition)</option>
                                <option value="Jam Room (Band)">Jam Room (Band Competition)</option>
                                <option value="Glam It Up(Fashion Show)">Fashion Show Competition</option>
                            </select>
                        </div>
                        <!-- <button type="button" id="registerButton">Register</button> -->
                        <button type="button" id="registerButton">
                            <span>Register</span>
                            <span>Hurry Up!</span>
                        </button>
                        <p class="mt-2">Already registered but payment pending?</p>
                        <a href="#" id="paymentLink">Pay Now</a>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </section>
    <?php include 'includes/footer.php'; ?>
    <div id="confirmationModal" class="modal">
        <div class="modal-content">
            <p>Please review your entered details carefully before proceeding. Once you confirm, no changes can be made to your registration.</p>
            <p>If everything looks correct, click 'Confirm' to complete your registration. If you need to make changes, click 'Cancel' to go back and edit your information.</p>
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
                <div class="form-group">
                    <label for="modal_roll_no">Roll Number:</label>
                    <input type="text" id="modal_roll_no" name="modal_roll_no" required>
                </div>
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
            }
        }

        var paymentModal = document.getElementById('paymentModal');
        var paymentLink = document.getElementById('paymentLink');
        var paymentForm = document.getElementById('paymentForm');

        paymentLink.onclick = function() {
            paymentModal.style.display = 'block';
        }

        window.onclick = function(event) {
            if (event.target == paymentModal) {
                paymentModal.style.display = 'none';
            }
        }

        paymentForm.onsubmit = function(event) {
            event.preventDefault();
            var jis_id = document.getElementById('modal_jis_id').value;
            var roll_no = document.getElementById('modal_roll_no').value;

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
            xhr.send("jis_id=" + jis_id + "&roll_no=" + roll_no);
        }

        // Update banner image based on selected competition
        document.getElementById('competition').addEventListener('change', function() {
            var selectedCompetition = this.value;
            var bannerImage = document.getElementById('bannerImage');
            switch (selectedCompetition) {
                case 'Taal Se Taal Mila (Dance)':
                    bannerImage.src = 'https://i.ibb.co/0V1Cxvnr/dance.png';
                    break;
                case 'Actomania (Drama)':
                    bannerImage.src = 'https://i.ibb.co/vvLXHMDF/drama.jpg';
                    break;
                case 'Jam Room (Band)':
                    bannerImage.src = 'https://i.ibb.co/5h1Kw4KB/band.jpg';
                    break;
                case 'Glam It Up(Fashion Show)':
                    bannerImage.src = 'https://i.ibb.co/9drCNqN/fashion.jpg';
                    break;
                default:
                    bannerImage.src = 'https://i.ibb.co/VWLkTX5j/banner1.png';
                    break;
            }
        });

        function validateForm() {
            var studentName = document.getElementById('student_name').value.trim();
            var jisId = document.getElementById('jis_id').value.trim();
            var mobile = document.getElementById('mobile').value.trim();
            var email = document.getElementById('email').value.trim();
            var rollNo = document.getElementById('roll_no').value.trim();
            var department = document.getElementById('department').value.trim();
            var gender = document.getElementById('gender').value.trim();

            // Validate student name (no numeric values allowed and not empty)
            if (studentName === "" || /\d/.test(studentName)) {
                alert('Student name should not be empty or contain numeric values.');
                return false;
            }

            // Validate JIS ID format
            var jisIdPattern = /^JIS\/\d{4}\/\d{4}$/;
            if (jisId === "" || !jisIdPattern.test(jisId)) {
                alert('JIS ID should be in the format JIS/2025/0001 and not be empty.');
                return false;
            }

            // Validate mobile number (10 digits)
            var mobilePattern = /^\d{10}$/;
            if (mobile === "" || !mobilePattern.test(mobile)) {
                alert('Mobile number should be 10 digits and not be empty.');
                return false;
            }

            // Validate email
            if (email === "") {
                alert('Email should not be empty.');
                return false;
            }

            // Validate roll number
            if (rollNo === "") {
                alert('Roll number should not be empty.');
                return false;
            }

            // Validate department
            if (department === "") {
                alert('Department should not be empty.');
                return false;
            }

            // Validate gender
            if (gender === "") {
                alert('Gender should not be empty.');
                return false;
            }

            return true;
        }

        // Clear the form when the user navigates back to the page
        window.onload = function() {
            document.getElementById('registrationForm').reset();
        }
    </script>
</body> 
</html>

