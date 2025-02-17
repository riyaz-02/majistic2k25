<?php
include 'src/main/registration_outhouse_handler.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Outhouse Students Registration</title>
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
            background-color:rgba(16, 66, 45, 0.73); /* Add background color to dropdown options */
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
        }
        .loading-spinner img {
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
        .team-member {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
            width: 100%; /* Increase width */
        }
        .team-member input {
            margin-right: 10px;
            flex: 1; /* Allow inputs to take available space */
        }
        .delete-member {
            cursor: pointer;
            color: red; /* Color for the trash icon */
            margin-left: 10px;
        }
        #addTeamMemberButton {
            float: right; /* Align to the right */
            padding: 5px 10px; /* Smaller padding */
            font-size: 14px; /* Smaller font size */
        }
        #teamMembersContainer {
            width: 100%; /* Ensure it has the same width as the form container */
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
        <h1 style="text-align: center; color: white; margin-bottom: 20px;">Outhouse Students Registration</h1>
        <div class="info-card">
            <ul>
                <li>📋 Please ensure that all your details are correct before submitting the registration form.</li>
                <li>🎟️ Registration fee is <del>Rs. 1200</del> Rs. 1000 [Early Bird]</li>
                <li>📧 For any queries, contact maJIStic support.</li>
                <li>💡 Make sure to double-check your email and contact number for accuracy.</li>
                <li>🕒 Registration closes 24 hours before the event.</li>
            </ul>
        </div>
        <div class="content-wrapper">
            <div class="banner-container">
                <img id="bannerImage" src="https://i.ibb.co/VWLkTX5j/banner1.png" alt="Event Banner">
            </div>
            <div class="form-container" style="background: rgba(0, 0, 0, 0.5); padding: 20px; text-align: left; border-radius: 10px; border: 1px solid #888;">
                <img class="majisticheadlogo" src="images/majistic2k25_white.png" alt="maJIStic Logo">
                <?php if ($registration_success): ?>
                    <div class="success-message">
                        <p><?php echo $message; ?></p>
                        <table class="verification-table">
                            <tr>
                                <th colspan="2">VERIFY YOUR DETAILS BEFORE PAYMENT</th>
                            </tr>
                            <tr>
                                <td>Leader Name</td>
                                <td><?php echo htmlspecialchars($leader_name); ?></td>
                            </tr>
                            <tr>
                                <td>Email</td>
                                <td><?php echo htmlspecialchars($email); ?></td>
                            </tr>
                            <tr>
                                <td>Contact Number</td>
                                <td><?php echo htmlspecialchars($contact_number); ?></td>
                            </tr>
                            <tr>
                                <td>College Name</td>
                                <td><?php echo htmlspecialchars($college_name); ?></td>
                            </tr>
                            <tr>
                                <td>College ID</td>
                                <td><?php echo htmlspecialchars($college_id); ?></td>
                            </tr>
                            <tr>
                                <td>Course Name</td>
                                <td><?php echo htmlspecialchars($course_name); ?></td>
                            </tr>
                            <tr>
                                <td>Competition Name</td>
                                <td><?php echo htmlspecialchars($competition_name); ?></td>
                            </tr>
                            <tr>
                                <td>Team Members</td>
                                <td>
                                    <ul>
                                        <?php foreach ($team_members as $index => $member): ?>
                                            <li><?php echo htmlspecialchars($member); ?> - <?php echo htmlspecialchars($team_members_contact[$index]); ?></li>
                                        <?php endforeach; ?>
                                    </ul>
                                </td>
                            </tr>
                        </table>
                        <?php if ($email_option): ?>
                            <p>Would you like to receive your registration details via email?</p>
                            <form method="POST" action="registration_outhouse.php">
                                <input type="hidden" name="email_option" value="1">
                                <input type="hidden" name="leader_name" value="<?php echo $leader_name; ?>">
                                <input type="hidden" name="email" value="<?php echo $email; ?>">
                                <input type="hidden" name="contact_number" value="<?php echo $contact_number; ?>">
                                <input type="hidden" name="college_name" value="<?php echo $college_name; ?>">
                                <input type="hidden" name="college_id" value="<?php echo $college_id; ?>">
                                <input type="hidden" name="course_name" value="<?php echo $course_name; ?>">
                                <input type="hidden" name="competition_name" value="<?php echo $competition_name; ?>">
                                <input type="hidden" name="payment_transaction_id" value="<?php echo $payment_transaction_id; ?>"> <!-- Add payment transaction ID -->
                                <button type="submit">Send Email</button>
                            </form>
                        <?php endif; ?>
                    </div>
                    <button id="finalPay" style="width: 150px;" onclick="window.location.href='src/transaction/payment.php?email=<?php echo $email; ?>'">Pay Now</button>
                <?php else: ?>
                    <form id="registrationForm" method="POST" action="registration_outhouse.php" onsubmit="return validateForm()">
                        <div class="form-group">
                            <label for="leader_name">Leader Name:</label>
                            <input type="text" id="leader_name" name="leader_name" required>
                        </div>
                        <div class="form-group">
                            <label for="gender">Gender:</label>
                            <select id="gender" name="gender" required>
                                <option value="">--Select Gender--</option>
                                <option value="Male">Male</option>
                                <option value="Female">Female</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="email">Email:</label>
                            <input type="email" id="email" name="email" required>
                        </div>
                        <div class="form-group">
                            <label for="contact_number">Contact Number:</label>
                            <input type="tel" id="contact_number" name="contact_number" pattern="\d{10}" placeholder="10-digit number" required>
                        </div>
                        <div class="form-group">
                            <label for="college_name">College Name:</label>
                            <input type="text" id="college_name" name="college_name" required>
                        </div>
                        <div class="form-group">
                            <label for="college_id">College ID:</label>
                            <input type="text" id="college_id" name="college_id" required>
                        </div>
                        <div class="form-group">
                            <label for="course_name">Course Name:</label>
                            <input type="text" id="course_name" name="course_name" required>
                        </div>
                        <div class="form-group">
                            <label for="competition_name">Competition Name:</label>
                            <select id="competition_name" name="competition_name" required onchange="updateBanner()">
                                <option value="">--Select Competition--</option>
                                <option value="dance">Dance Competition</option>
                                <option value="drama">Drama Competition</option>
                                <option value="band">Band Competition</option>
                                <option value="fashion">Fashion Show Competition</option>
                                <option value="recitation">Recitation Competition</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="team_name">Team Name:</label>
                            <input type="text" id="team_name" name="team_name" required>
                        </div>
                        <div class="form-group">
                            <label for="team_members">Team Members:</label>
                        </div>
                        <div id="teamMembersContainer">
                            <div class="team-member">
                                <input type="text" name="team_members[]" placeholder="Team Member Name" readonly required>
                                <input type="tel" name="team_members_contact[]" placeholder="10-digit Mobile Number" pattern="\d{10}" readonly required>
                                <span class="delete-member" style="visibility: hidden;"><i class="fas fa-trash"></i></span>
                            </div>
                        </div>
                        <button type="button" id="addTeamMemberButton">Add Member</button>
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
                    <label for="modal_email">Email:</label>
                    <input type="email" id="modal_email" name="modal_email" required>
                </div>
                <div class="form-group">
                    <label for="modal_contact_number">Contact Number:</label>
                    <input type="tel" id="modal_contact_number" name="modal_contact_number" required>
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
            var email = document.getElementById('modal_email').value;
            var contact_number = document.getElementById('modal_contact_number').value;

            var xhr = new XMLHttpRequest();
            xhr.open("POST", "src/transaction/check_payment_status.php", true);
            xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
            xhr.onreadystatechange = function () {
                if (xhr.readyState === 4 && xhr.status === 200) {
                    var response = JSON.parse(xhr.responseText);
                    if (response.status === 'pending') {
                        window.location.href = 'src/transaction/payment.php?email=' + email;
                    } else if (response.status === 'paid') {
                        showPaymentMessage(response.message, true);
                    } else {
                        showPaymentMessage(response.message, false);
                    }
                }
            };
            xhr.send("email=" + email + "&contact_number=" + contact_number);
        }

        // Add team member functionality
        var teamMembersContainer = document.getElementById('teamMembersContainer');
        var addTeamMemberButton = document.getElementById('addTeamMemberButton');
        var maxTeamMembers = 6;

        addTeamMemberButton.onclick = function() {
            var teamMemberCount = teamMembersContainer.getElementsByClassName('team-member').length;
            if (teamMemberCount < maxTeamMembers) {
                var newTeamMember = document.createElement('div');
                newTeamMember.className = 'team-member';
                newTeamMember.innerHTML = '<input type="text" name="team_members[]" placeholder="Team Member Name" style="flex: 1; margin-right: 10px;" required>' +
                                          '<input type="tel" name="team_members_contact[]" placeholder="10-digit number" pattern="\\d{10}" style="flex: 1; margin-right: 10px;" required>' +
                                          '<span class="delete-member" onclick="removeTeamMember(this)"><i class="fas fa-trash"></i></span>';
                teamMembersContainer.appendChild(newTeamMember);
            } else {
                alert('You can add up to ' + maxTeamMembers + ' team members.');
            }
        }

        // Auto-fill the first team member with leader's name and contact number
        document.getElementById('leader_name').addEventListener('input', function() {
            var firstTeamMemberName = document.querySelector('#teamMembersContainer .team-member input[name="team_members[]"]');
            firstTeamMemberName.value = this.value;
        });

        document.getElementById('contact_number').addEventListener('input', function() {
            var firstTeamMemberContact = document.querySelector('#teamMembersContainer .team-member input[name="team_members_contact[]"]');
            firstTeamMemberContact.value = this.value;
        });

        // Function to remove a team member
        function removeTeamMember(element) {
            var teamMember = element.parentElement;
            teamMembersContainer.removeChild(teamMember);
        }

        // Function to update the banner image based on the selected competition
        function updateBanner() {
            var competition = document.getElementById('competition_name').value;
            var bannerImage = document.getElementById('bannerImage');
            switch (competition) {
                case 'dance':
                    bannerImage.src = 'https://i.ibb.co/0V1Cxvnr/dance.png';
                    break;
                case 'drama':
                    bannerImage.src = 'https://i.ibb.co/vvLXHMDF/drama.jpg';
                    break;
                case 'band':
                    bannerImage.src = 'https://i.ibb.co/5h1Kw4KB/band.jpg';
                    break;
                case 'fashion':
                    bannerImage.src = 'https://i.ibb.co/9drCNqN/fashion.jpg';
                    break;
                case 'recitation':
                    bannerImage.src = 'https://i.ibb.co/VWLkTX5j/banner1.png';
                    break;
                default:
                    bannerImage.src = 'https://i.ibb.co/VWLkTX5j/banner1.png';
                    break;
            }
        }

        function validateForm() {
            var leaderName = document.getElementById('leader_name').value;
            var contactNumber = document.getElementById('contact_number').value;
            var teamMembersContact = document.getElementsByName('team_members_contact[]');

            // Check if leader name contains numeric values
            if (/\d/.test(leaderName)) {
                alert('Leader name should not contain numeric values.');
                return false;
            }

            // Check if contact number is 10 digits
            if (contactNumber.length !== 10) {
                alert('Contact number should be 10 digits.');
                return false;
            }

            // Check if team members' contact numbers are 10 digits
            for (var i = 0; i < teamMembersContact.length; i++) {
                if (teamMembersContact[i].value.length !== 10) {
                    alert('Team member contact number should be 10 digits.');
                    return false;
                }
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