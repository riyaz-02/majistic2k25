<?php
include '../../includes/db_config.php';

// Initialize variables
$jis_id = isset($_GET['jis_id']) ? $_GET['jis_id'] : '';
$student_data = null;

// If JIS ID is provided, fetch student details
if (!empty($jis_id)) {
    $stmt = $conn->prepare("SELECT student_name, department, email, mobile, registration_date FROM registrations WHERE jis_id = ?");
    $stmt->bind_param("s", $jis_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $student_data = $result->fetch_assoc();
    }
    
    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration Successful - maJIStic 2k25</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap">
    <?php include '../../includes/links.php'; ?>
    <link rel="stylesheet" href="../../style.css">
    <style>
        body {
            background-image: url('../../images/pageback.png');
            background-size: cover;
            background-attachment: fixed;
            min-height: 100vh;
            font-family: 'Roboto', sans-serif;
            color: #ffffff;
        }
        
        .success-container {
            max-width: 800px;
            margin: 40px auto;
            padding: 0 20px;
        }
        
        .success-card {
            background-color: rgba(0, 0, 0, 0.7);
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(5px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            padding-bottom: 30px;
        }
        
        .card-header {
            background: linear-gradient(135deg, #3498db, #8e44ad);
            padding: 20px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        
        .card-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(45deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            animation: shine 3s infinite;
        }
        
        @keyframes shine {
            0% { transform: translateX(-100%); }
            100% { transform: translateX(100%); }
        }
        
        .logo {
            width: 150px;
            margin-bottom: 15px;
            animation: pulsate 2s infinite;
        }
        
        @keyframes pulsate {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }
        
        .card-body {
            padding: 30px;
            text-align: center;
        }
        
        .thank-you {
            font-size: 2.5rem;
            margin-bottom: 20px;
            background: linear-gradient(to right, #3498db, #2ecc71);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            animation: colorChange 5s infinite alternate;
        }
        
        @keyframes colorChange {
            0% { background-position: 0% 50%; }
            100% { background-position: 100% 50%; }
        }
        
        .message {
            font-size: 1.2rem;
            line-height: 1.6;
            margin-bottom: 30px;
        }
        
        .highlight {
            color: #2ecc71;
            font-weight: 500;
        }
        
        .note {
            background-color: rgba(46, 204, 113, 0.15);
            border-left: 4px solid #2ecc71;
            padding: 15px;
            margin: 20px 0;
            text-align: left;
            border-radius: 4px;
        }
        
        .warning {
            background-color: rgba(231, 76, 60, 0.15);
            border-left: 4px solid #e74c3c;
            padding: 15px;
            margin: 20px 0;
            text-align: left;
            border-radius: 4px;
        }
        
        .action-buttons {
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            gap: 15px;
            margin-top: 30px;
        }
        
        .btn {
            padding: 12px 25px;
            border: none;
            border-radius: 50px;
            font-size: 1rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
            min-width: 180px;
            justify-content: center;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #3498db, #2980b9);
            color: white;
        }
        
        .btn-secondary {
            background: linear-gradient(135deg, #9b59b6, #8e44ad);
            color: white;
        }
        
        .btn-accent {
            background: linear-gradient(135deg, #2ecc71, #27ae60);
            color: white;
        }
        
        .btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.3);
        }
        
        .btn:active {
            transform: translateY(-1px);
        }
        
        .student-details {
            background-color: rgba(255, 255, 255, 0.1);
            border-radius: 10px;
            padding: 20px;
            margin: 20px 0;
            text-align: left;
        }
        
        .student-details h3 {
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
            padding-bottom: 10px;
            margin-bottom: 15px;
            color: #3498db;
        }
        
        .detail-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            font-size: 0.95rem;
        }
        
        .detail-label {
            font-weight: 500;
            color: #95a5a6;
        }
        
        .detail-value {
            font-weight: 400;
            color: #ecf0f1;
        }
        
        .confetti {
            position: fixed;
            width: 10px;
            height: 10px;
            background-color: #f0f;
            animation: confetti-fall 5s ease-in-out infinite;
            z-index: -1;
        }
        
        @keyframes confetti-fall {
            0% { transform: translateY(-100px) rotate(0deg); opacity: 1; }
            100% { transform: translateY(100vh) rotate(720deg); opacity: 0; }
        }
        
        .social-share {
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .social-share h4 {
            margin-bottom: 15px;
            color: #bdc3c7;
        }
        
        .social-icons {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin-top: 10px;
        }
        
        .social-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(255, 255, 255, 0.1);
            color: white;
            transition: all 0.3s ease;
            font-size: 20px;
        }
        
        .social-icon:hover {
            transform: translateY(-5px);
        }
        
        .countdown {
            margin-top: 30px;
            background-color: rgba(0, 0, 0, 0.3);
            padding: 15px;
            border-radius: 10px;
        }
        
        .countdown h4 {
            margin-bottom: 10px;
            color: #f39c12;
        }
        
        .countdown-timer {
            display: flex;
            justify-content: center;
            gap: 15px;
        }
        
        .time-unit {
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        
        .time-value {
            font-size: 1.8rem;
            font-weight: bold;
            color: #e74c3c;
            background-color: rgba(0, 0, 0, 0.2);
            border-radius: 5px;
            min-width: 60px;
            text-align: center;
            padding: 5px 0;
        }
        
        .time-label {
            font-size: 0.8rem;
            margin-top: 5px;
            color: #bdc3c7;
        }
        
        @media (max-width: 768px) {
            .thank-you {
                font-size: 1.8rem;
            }
            
            .action-buttons {
                flex-direction: column;
                align-items: center;
                gap: 10px;
            }
            
            .btn {
                width: 100%;
                max-width: 250px;
            }
            
            .detail-row {
                flex-direction: column;
                margin-bottom: 15px;
            }
            
            .detail-value {
                margin-top: 5px;
                word-break: break-all;
            }
            
            .countdown-timer {
                flex-wrap: wrap;
            }
        }
        
        /* Animation for elements */
        .animate-in {
            animation: fadeIn 0.8s ease-out;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body>
    <?php include '../../includes/header.php'; ?>
    
    <div class="success-container">
        <div class="success-card animate-in">
            <div class="card-header">
                <img src="https://i.ibb.co/RGQ7Lj6K/majisticlogo.png" alt="maJIStic Logo" class="logo">
                <h2>maJIStic 2k25</h2>
            </div>
            
            <div class="card-body">
                <h1 class="thank-you">Thank You for Registering!</h1>
                
                <p class="message">
                    Congratulations! Your spot for <span class="highlight">maJIStic 2k25</span> has been successfully reserved! We're thrilled to have you join us for this incredible event filled with technology, innovation, and creativity.
                </p>
                
                <?php if ($student_data): ?>
                <div class="student-details">
                    <h3>Registration Details</h3>
                    <div class="detail-row">
                        <span class="detail-label">Name:</span>
                        <span class="detail-value"><?php echo htmlspecialchars($student_data['student_name']); ?></span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">JIS ID:</span>
                        <span class="detail-value"><?php echo htmlspecialchars($jis_id); ?></span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Department:</span>
                        <span class="detail-value"><?php echo htmlspecialchars($student_data['department']); ?></span>
                    </div>
                    <!-- Roll Number display removed -->
                    <div class="detail-row">
                        <span class="detail-label">Registration Date:</span>
                        <span class="detail-value"><?php echo htmlspecialchars($student_data['registration_date']); ?></span>
                    </div>
                </div>
                <?php endif; ?>
                
                <div class="note">
                    <p><strong>Important Note:</strong> You'll be notified soon about the payment details for your event ticket. Keep an eye on your email inbox and our social media channels for updates!</p>
                </div>
                
                <div class="warning">
                    <p><strong>Remember:</strong> Your college ID will be mandatory for check-in on the event day. Please ensure you bring it along!</p>
                </div>
                
                <div class="countdown">
                    <h4>Event Countdown</h4>
                    <div class="countdown-timer" id="countdown">
                        <div class="time-unit">
                            <div class="time-value" id="days">00</div>
                            <div class="time-label">DAYS</div>
                        </div>
                        <div class="time-unit">
                            <div class="time-value" id="hours">00</div>
                            <div class="time-label">HOURS</div>
                        </div>
                        <div class="time-unit">
                            <div class="time-value" id="minutes">00</div>
                            <div class="time-label">MINUTES</div>
                        </div>
                        <div class="time-unit">
                            <div class="time-value" id="seconds">00</div>
                            <div class="time-label">SECONDS</div>
                        </div>
                    </div>
                </div>
                
                <div class="action-buttons">
                    <a href="../../index.php" class="btn btn-primary">
                        <i class="fas fa-home"></i> Back to Home
                    </a>
                    <a href="check_status.php" class="btn btn-secondary">
                        <i class="fas fa-search"></i> Check Status
                    </a>
                    <a href="../../merchandise.php" class="btn btn-accent">
                        <i class="fas fa-tshirt"></i> Buy Merchandise
                    </a>
                </div>
                
                <div class="social-share">
                    <h4>Share your excitement!</h4>
                    <p>Let your friends know you're attending maJIStic 2k25</p>
                    <div class="social-icons">
                        <a href="https://www.facebook.com/sharer/sharer.php?u=https://majistic.jisgroup.org" target="_blank" class="social-icon" style="background-color: #3b5998;">
                            <i class="fab fa-facebook-f"></i>
                        </a>
                        <a href="https://twitter.com/intent/tweet?text=I%20just%20registered%20for%20maJIStic%202k25!%20Join%20me%20at%20this%20amazing%20tech%20fest!&url=https://majistic.jisgroup.org" target="_blank" class="social-icon" style="background-color: #1da1f2;">
                            <i class="fab fa-twitter"></i>
                        </a>
                        <a href="https://www.instagram.com/" target="_blank" class="social-icon" style="background: linear-gradient(45deg, #f09433, #e6683c, #dc2743, #cc2366, #bc1888);">
                            <i class="fab fa-instagram"></i>
                        </a>
                        <a href="https://api.whatsapp.com/send?text=Hey!%20I%20just%20registered%20for%20maJIStic%202k25.%20Join%20me%20at%20this%20amazing%20event!%20Register%20now%20at%20https://majistic.jisgroup.org" target="_blank" class="social-icon" style="background-color: #25d366;">
                            <i class="fab fa-whatsapp"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include '../../includes/footer.php'; ?>
    
    <script>
        // Create confetti elements
        function createConfetti() {
            const colors = ['#f39c12', '#2ecc71', '#3498db', '#e74c3c', '#9b59b6', '#1abc9c'];
            
            for (let i = 0; i < 50; i++) {
                const confetti = document.createElement('div');
                confetti.classList.add('confetti');
                confetti.style.backgroundColor = colors[Math.floor(Math.random() * colors.length)];
                confetti.style.left = Math.random() * 100 + 'vw';
                confetti.style.animationDelay = Math.random() * 5 + 's';
                confetti.style.animationDuration = Math.random() * 3 + 3 + 's';
                document.body.appendChild(confetti);
            }
        }
        
        // Countdown timer
        function updateCountdown() {
            const eventDate = new Date('2025-03-15T10:00:00'); // Set your event date here
            const now = new Date();
            const timeDifference = eventDate - now;
            
            if (timeDifference > 0) {
                const days = Math.floor(timeDifference / (1000 * 60 * 60 * 24));
                const hours = Math.floor((timeDifference % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                const minutes = Math.floor((timeDifference % (1000 * 60 * 60)) / (1000 * 60));
                const seconds = Math.floor((timeDifference % (1000 * 60)) / 1000);
                
                document.getElementById('days').textContent = days.toString().padStart(2, '0');
                document.getElementById('hours').textContent = hours.toString().padStart(2, '0');
                document.getElementById('minutes').textContent = minutes.toString().padStart(2, '0');
                document.getElementById('seconds').textContent = seconds.toString().padStart(2, '0');
            }
        }
        
        // Add animation to elements
        function addAnimations() {
            const elements = document.querySelectorAll('.card-body > *');
            
            elements.forEach((element, index) => {
                element.classList.add('animate-in');
                element.style.animationDelay = (0.1 * index) + 's';
            });
        }
        
        // When DOM is loaded
        document.addEventListener('DOMContentLoaded', function() {
            createConfetti();
            addAnimations();
            updateCountdown();
            setInterval(updateCountdown, 1000);
        });
    </script>
</body>
</html>
