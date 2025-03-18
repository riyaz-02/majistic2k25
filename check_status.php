<?php
include 'includes/db_config.php';

$message = '';
$student_data = null;
$registration_type = '';
$payment_status = false; // Default to false, will update if payment found

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $jis_id = isset($_POST['jis_id']) ? $_POST['jis_id'] : '';
    $student_name = isset($_POST['student_name']) ? $_POST['student_name'] : '';
    
    if (!empty($jis_id) && !empty($student_name)) {
        // Check regular registrations table
        $stmt = $conn->prepare("SELECT student_name, department, email, mobile, registration_date, inhouse_competition, competition_name, payment_status FROM registrations WHERE jis_id = ?");
        $stmt->bind_param("s", $jis_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $student_data = $result->fetch_assoc();
            $registration_type = 'student';
            
            // Check if name matches
            if (strtolower(trim($student_data['student_name'])) != strtolower(trim($student_name))) {
                $message = 'The provided name does not match with the registration. Please check your information.';
                $student_data = null;
            } else {
                // Check payment status
                $payment_status = ($student_data['payment_status'] == 'Paid');
            }
        } else {
            // If not found in registrations table, check alumni table
            $stmt->close();
            // Fix: Changed 'name' to 'alumni_name' and added 'passout_year' instead of 'batch'
            $stmt = $conn->prepare("SELECT alumni_name as student_name, department, email, mobile, passout_year, registration_date, payment_status FROM alumni_registrations WHERE jis_id = ?");
            $stmt->bind_param("s", $jis_id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $student_data = $result->fetch_assoc();
                $registration_type = 'alumni';
                
                // Check if name matches
                if (strtolower(trim($student_data['student_name'])) != strtolower(trim($student_name))) {
                    $message = 'The provided name does not match with the registration. Please check your information.';
                    $student_data = null;
                } else {
                    // Check payment status
                    $payment_status = ($student_data['payment_status'] == 'Paid');
                }
            } else {
                $message = 'No registration found for the provided JIS ID.';
            }
        }
        
        if (isset($stmt)) {
            $stmt->close();
        }
    } else {
        $message = 'Please enter your Name and JIS ID.';
    }
}

// Calculate days remaining until the event
$event_date = new DateTime('2025-04-11');
$current_date = new DateTime();
$interval = $current_date->diff($event_date);
$days_remaining = $interval->format('%a');

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Check Registration Status - maJIStic 2k25</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap">
    <?php include 'includes/links.php'; ?>
    <link rel="stylesheet" href="style.css">
    <style>
        body {
            background-image: url('images/pageback.png');
            background-size: cover;
            background-attachment: fixed;
            min-height: 100vh;
            font-family: 'Roboto', sans-serif;
            color: #ffffff;
        }
        
        .status-container {
            max-width: 1200px; /* Increased from 1000px */
            margin: 40px auto;
            padding: 0 20px;
        }
        
        .status-card {
            background-color: rgba(0, 0, 0, 0.75);
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.6);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            padding-bottom: 30px;
        }
        
        .card-header {
            background: linear-gradient(135deg, #3498db, #9b59b6, #8e44ad);
            background-size: 300% 300%;
            animation: gradientBG 10s ease infinite;
            padding: 35px 20px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        
        @keyframes gradientBG {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }
        
        .card-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('images/pattern.png');
            opacity: 0.1;
            z-index: 0;
        }
        
        .logo {
            width: 140px; /* Increased from 120px */
            margin-bottom: 20px;
            position: relative;
            z-index: 1;
            filter: drop-shadow(0 5px 15px rgba(0,0,0,0.3));
        }
        
        /* Updated countdown styling for header placement */
        .countdown-container {
            max-width: 600px; /* Increased from 500px */
            margin: 20px auto 0;
            padding: 15px;
            background: rgba(0, 0, 0, 0.5);
            border-radius: 16px;
            box-shadow: 0 15px 25px rgba(0, 0, 0, 0.3);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            position: relative;
            z-index: 1;
        }
        
        .countdown-title {
            font-size: 18px;
            margin-bottom: 10px;
            color: white;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 2px;
            text-shadow: 0 2px 5px rgba(0,0,0,0.3);
        }
        
        .countdown {
            display: flex;
            justify-content: center;
            gap: 15px;
        }
        
        .countdown-item {
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        
        .countdown-value {
            font-size: 32px;
            font-weight: 700;
            color: white;
            background: linear-gradient(145deg, rgba(0,0,0,0.4), rgba(0,0,0,0.2));
            border-radius: 8px;
            min-width: 70px;
            padding: 12px;
            text-align: center;
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.25);
            text-shadow: 0 2px 5px rgba(0,0,0,0.3);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .countdown-label {
            font-size: 12px;
            color: rgba(255, 255, 255, 0.9);
            margin-top: 8px;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-weight: 500;
        }
        
        .card-body {
            padding: 40px;
            text-align: center;
            background: linear-gradient(to bottom, rgba(0,0,0,0.2), rgba(0,0,0,0));
        }

        .page-title {
            font-size: 32px;
            font-weight: 700;
            color: white;
            margin: 20px 0 25px;
            text-shadow: 0 3px 10px rgba(0,0,0,0.5);
            letter-spacing: 1px;
        }
        
        .form-container {
            background-color: rgba(255, 255, 255, 0.08);
            padding: 30px;
            border-radius: 15px;
            margin: 0 auto 30px;
            max-width: 700px; /* Decreased width */
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.05);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
        }
        
        .form-group {
            margin-bottom: 25px;
            text-align: left;
        }
        
        label {
            display: block;
            margin-bottom: 10px;
            font-weight: 500;
            color: #e0e0e0;
            font-size: 16px;
            letter-spacing: 0.5px;
        }
        
        input[type="text"] {
            width: 100%;
            padding: 15px;
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 8px;
            background-color: rgba(0, 0, 0, 0.3);
            color: white;
            font-size: 16px;
            transition: all 0.3s ease;
            box-shadow: inset 0 2px 5px rgba(0,0,0,0.2);
        }
        
        input[type="text"]:focus {
            outline: none;
            border-color: #3498db;
            box-shadow: 0 0 15px rgba(52, 152, 219, 0.4), inset 0 2px 5px rgba(0,0,0,0.2);
        }
        
        .btn {
            padding: 14px 30px;
            border: none;
            border-radius: 50px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.4s ease;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.2);
            letter-spacing: 0.5px;
        }
        
        .btn-primary {
            background: linear-gradient(135deg,rgb(168, 15, 240),rgb(105, 2, 119));
            color: white;
        }
        
        .btn-payment {
            background: linear-gradient(135deg, #f1c40f, #e67e22);
            color: white;
        }
        
        .btn:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 20px rgba(0, 0, 0, 0.3);
        }

        .btn:active {
            transform: translateY(-2px);
        }
        
        /* Results container styling */
        .results-container {
            animation: fadeIn 0.8s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .student-details {
            background-color: rgba(255, 255, 255, 0.08);
            border-radius: 15px;
            padding: 25px;
            margin: 20px 0;
            text-align: left;
            box-shadow: 0 15px 25px rgba(0, 0, 0, 0.2);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.05);
        }
        
        .student-details h3 {
            border-bottom: 2px solid rgba(255, 255, 255, 0.1);
            padding-bottom: 15px;
            margin-bottom: 20px;
            color: #3498db;
            font-size: 24px;
            font-weight: 600;
            letter-spacing: 0.5px;
        }
        
        .detail-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
            font-size: 16px;
            padding: 10px 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        }
        
        .detail-label {
            font-weight: 600;
            color: #bdc3c7;
            flex: 1;
        }
        
        .detail-value {
            font-weight: 400;
            color: #ecf0f1;
            flex: 2;
            text-align: right;
        }
        
        .message-box {
            padding: 18px;
            margin: 25px 0;
            border-radius: 10px;
            text-align: center;
            font-weight: 500;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.15);
        }
        
        .message-box.error {
            background-color: rgba(231, 76, 60, 0.2);
            border-left: 4px solid #e74c3c;
            color: #e74c3c;
        }
        
        .action-buttons {
            display: flex;
            flex-direction: column;
            justify-content: center; /* Centers buttons vertically within the div */
            align-items: center;     /* Centers buttons horizontally within the div */
            gap: 15px;
            margin-top: 35px;
            margin-left: auto;
            margin-right: auto;
            /* Optional: Set a width or max-width if you want to constrain the container */
            width: 100%; /* or a specific value like 300px, depending on your design */
        }

        .action-buttons .btn {
            width: 70%;              /* Keeps the button width at 80% of the parent */
            justify-content: center;      /* Centers the text/content inside the button */
            /* Remove justify-content here; it’s for flex containers, not buttons */
        }
        
        /* Enhanced timeline styling */
        .timeline-container {
            margin: 40px auto;
            max-width: 100%;
        }
        
        .timeline {
            display: flex;
            flex-direction: column;
            position: relative;
            margin: 0 auto;
            padding: 20px 0;
        }
        
        .timeline::before {
            content: '';
            position: absolute;
            top: 0;
            bottom: 0;
            left: 35px;
            width: 6px;
            background: linear-gradient(to bottom, #3498db, #9b59b6);
            border-radius: 6px;
            z-index: 1;
            box-shadow: 0 0 20px rgba(155, 89, 182, 0.6);
            animation: glowingLine 3s infinite alternate;
        }
        
        @keyframes glowingLine {
            from { box-shadow: 0 0 10px rgba(52, 152, 219, 0.5); }
            to { box-shadow: 0 0 25px rgba(155, 89, 182, 0.8); }
        }
        
        .step {
            position: relative;
            margin: 35px 0;
            padding-left: 90px;
            z-index: 2;
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            width: 100%;
            transform: translateX(-20px);
            opacity: 0;
            animation: stepAppear 0.8s forwards;
            animation-delay: calc(var(--index) * 0.2s);
        }
        
        @keyframes stepAppear {
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }
        
        .step::before {
            content: '';
            position: absolute;
            left: 28px;
            top: 50%;
            transform: translateY(-50%);
            width: 0;
            height: 0;
            background-color: #fff;
            border-radius: 50%;
            z-index: 3;
            box-shadow: 0 0 15px white;
            animation: pulse 2s infinite;
        }
        
        .step-icon {
            position: absolute;
            left: 13px;
            top: 50%;
            transform: translateY(-50%);
            width: 50px;
            height: 50px;
            background: linear-gradient(145deg, rgba(0,0,0,0.6), rgba(0,0,0,0.8));
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 3px solid rgba(255, 255, 255, 0.2);
            transition: all 0.5s ease;
            z-index: 2;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.3);
        }
        
        .step.active .step-icon {
            background: linear-gradient(145deg, #3498db, #2980b9);
            border-color: rgba(255, 255, 255, 0.6);
            transform: translateY(-50%) scale(1.2);
            box-shadow: 0 0 25px rgba(52, 152, 219, 0.9);
        }
        
        .step.completed .step-icon {
            background: linear-gradient(145deg, #2ecc71, #27ae60);
            border-color: rgba(255, 255, 255, 0.6);
            box-shadow: 0 0 20px rgba(46, 204, 113, 0.7);
        }
        
        .step-content {
            background: rgba(255, 255, 255, 0.08);
            border-radius: 15px;
            padding: 25px;
            width: 100%;
            transition: all 0.4s ease;
            border-left: 4px solid rgba(255, 255, 255, 0.1);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
            backdrop-filter: blur(10px);
        }
        
        .step.active .step-content {
            border-left-color: #3498db;
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.3);
            background: rgba(52, 152, 219, 0.15);
            transform: translateX(8px) scale(1.03);
        }
        
        .step.completed .step-content {
            border-left-color: #2ecc71;
            background: rgba(46, 204, 113, 0.1);
        }
        
        .step-icon .icon {
            color: white;
            font-size: 20px;
            filter: drop-shadow(0 2px 3px rgba(0,0,0,0.3));
        }
        
        .step-title {
            font-weight: 700;
            font-size: 20px;
            color: #ecf0f1;
            margin-bottom: 10px;
            letter-spacing: 0.5px;
        }
        
        .step-description {
            font-size: 15px;
            color: #bdc3c7;
            line-height: 1.6;
        }

        /* Event day message styling */
        .event-message {
            display: flex;
            flex-direction: column;
            width: 100%;
            margin-top: 10px;
        }

        @media (min-width: 992px) {
            .event-message {
                flex-direction: row;
                gap: 50px;
                justify-content: space-between;
            }
            
            .event-day-message {
                flex: 1;
                margin: 10px 0;
            }
        }

        .event-day-message {
            background: linear-gradient(145deg, rgba(155, 89, 182, 0.8), rgba(107, 123, 201, 0.8));
            border-radius: 15px;
            padding: 30px 25px;
            margin: 20px 0;
            text-align: center;
            font-weight: 500;
            color: white;
            box-shadow: 0 15px 30px rgba(107, 123, 201, 0.3);
            animation: pulse 2.5s infinite;
            backdrop-filter: blur(5px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        /* Message container styling */
        .no-results-message {
            background: rgba(0, 0, 0, 0.5);
            border-radius: 15px;
            padding: 40px 30px;
            margin: 30px auto;
            max-width: 600px;
            text-align: center;
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.3);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.05);
            animation: fadeIn 0.8s ease;
        }
        
        .no-results-message i {
            font-size: 60px;
            color: #e74c3c;
            margin-bottom: 25px;
            display: block;
            animation: wobble 2s infinite;
        }
        
        @keyframes wobble {
            0%, 100% { transform: translateX(0); }
            15% { transform: translateX(-15px) rotate(-5deg); }
            30% { transform: translateX(10px) rotate(3deg); }
            45% { transform: translateX(-10px) rotate(-3deg); }
            60% { transform: translateX(5px) rotate(2deg); }
            75% { transform: translateX(-5px) rotate(-1deg); }
        }
        
        .no-results-message h3 {
            color: #e74c3c;
            margin-bottom: 20px;
            font-size: 28px;
            font-weight: 700;
            text-shadow: 0 2px 5px rgba(0,0,0,0.3);
        }
        
        .no-results-message p {
            color: #ecf0f1;
            font-size: 17px;
            line-height: 1.7;
            margin-bottom: 25px;
        }

        /* Improved layout */
        .status-layout {
            display: flex;
            flex-direction: row;
            flex-wrap: wrap;
            gap: 40px;
            margin-top: 30px;
        }
        
        .details-column {
            flex: 1;
            min-width: 360px;
        }
        
        .timeline-column {
            flex: 1.2;
            min-width: 400px;
        }
        
        .timeline-box {
            background: rgba(0, 0, 0, 0.4);
            border-radius: 15px;
            padding: 35px 25px;
            height: 100%;
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.3);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.05);
        }
        
        .timeline-box h3 {
            text-align: center;
            margin-bottom: 30px;
            color: #3498db;
            border-bottom: 2px solid rgba(255, 255, 255, 0.1);
            padding-bottom: 15px;
            font-size: 24px;
            font-weight: 700;
            letter-spacing: 1px;
            text-shadow: 0 2px 5px rgba(0,0,0,0.3);
        }

        .note {
            border-radius: 15px !important;
            padding: 25px !important;
            margin: 25px 0 !important;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
            backdrop-filter: blur(8px);
        }

        @media (max-width: 768px) {
            .card-body {
                padding: 30px 20px;
            }
            
            .detail-row {
                flex-direction: column;
                margin-bottom: 15px;
            }
            
            .detail-value {
                margin-top: 8px;
                word-break: break-all;
                text-align: left;
            }
            
            .timeline::before {
                left: 25px;
            }
            
            .step-icon {
                left: 8px;
                width: 40px;
                height: 40px;
            }
            
            .step {
                padding-left: 70px;
            }
            
            .step::before {
                left: 23px;
                width: 0;
                height: 0;
            }
            
            .countdown {
                flex-wrap: wrap;
                justify-content: center;
            }
            
            .countdown-item {
                margin-bottom: 15px;
            }
            
            .status-layout {
                flex-direction: column;
                gap: 30px;
            }
            
            .timeline-box {
                padding: 25px 15px;
            }

            .page-title {
                font-size: 26px;
            }
            
            .countdown-value {
                font-size: 24px;
                min-width: 60px;
                padding: 10px;
            }
            
            .step-content {
                padding: 20px;
            }
            
            .step-title {
                font-size: 18px;
            }
        }

        @media (max-width: 480px) {
            .card-body {
                padding: 20px 15px;
            }
            
            .form-container {
                padding: 20px 15px;
            }
            
            .action-buttons {
                flex-direction: column;
                gap: 15px;
            }
            
            .btn {
                width: 80%;
                margin: 0 auto;
                justify-content: center;
                padding: 12px 20px;
            }
            
            .countdown-container {
                padding: 10px;
            }
            
            .countdown-value {
                font-size: 20px;
                min-width: 50px;
                padding: 8px;
            }
            
            .countdown-title {
                font-size: 14px;
            }
            
            .logo {
                width: 100px;
            }
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="status-container">
        <div class="status-card">
            <div class="card-header">
                <img src="images/majisticlogo.png" alt="maJIStic Logo" class="logo">
                
                <div class="countdown-container">
                    <h3 class="countdown-title">maJIStic 2k25 Begins In</h3>
                    <div class="countdown" id="countdown">
                        <div class="countdown-item">
                            <div class="countdown-value" id="days"><?php echo $days_remaining; ?></div>
                            <div class="countdown-label">Days</div>
                        </div>
                        <div class="countdown-item">
                            <div class="countdown-value" id="hours">00</div>
                            <div class="countdown-label">Hours</div>
                        </div>
                        <div class="countdown-item">
                            <div class="countdown-value" id="minutes">00</div>
                            <div class="countdown-label">Minutes</div>
                        </div>
                        <div class="countdown-item">
                            <div class="countdown-value" id="seconds">00</div>
                            <div class="countdown-label">Seconds</div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="card-body">
                <h2 class="page-title">Check Registration Status</h2>

                <?php if (!empty($message)): ?>
                    <div class="message-box error"><?php echo $message; ?></div>
                <?php endif; ?>
                
                <?php if (!$student_data): ?>
                    <div class="form-container">
                        <form method="post">
                            <div class="form-group">
                                <label for="student_name">Full Name</label>
                                <input type="text" id="student_name" name="student_name" placeholder="Enter your name as per registration" required>
                            </div>
                            <div class="form-group">
                                <label for="jis_id">JIS ID</label>
                                <input type="text" id="jis_id" name="jis_id" placeholder="JIS/20XX/0000" required>
                            </div>
                            <div style="text-align: center;">
                                <button type="submit" class="btn btn-primary" style="background: linear-gradient(135deg,rgb(146, 46, 204),rgb(43, 22, 136)); color: white;">
                                    <i class="fas fa-search"></i> Check Status
                                </button>
                            </div>
                        </form>
                    </div>
                    
                    <?php if ($_SERVER["REQUEST_METHOD"] == "POST" && !empty($message)): ?>
                        <div class="no-results-message">
                            <i class="fas fa-exclamation-circle"></i>
                            <h3>Registration Not Found</h3>
                            <p>We couldn't find any registration matching the details you provided. Please verify your JIS ID and name, then try again.</p>
                            <p>If you haven't registered yet, please visit the registration page to participate in maJIStic 2k25.</p>
                            <a href="registration_inhouse.php" class="btn btn-primary">
                                Register Now
                            </a>
                        </div>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="results-container">
                        <div class="status-layout">
                            <div class="details-column">
                                <div class="student-details">
                                    <h3>Your Registration Details</h3>
                                    <div class="detail-row">
                                        <span class="detail-label">Name:</span>
                                        <span class="detail-value"><?php echo htmlspecialchars($student_data['student_name']); ?></span>
                                    </div>
                                    <div class="detail-row">
                                        <span class="detail-label">JIS ID:</span>
                                        <span class="detail-value"><?php echo htmlspecialchars($jis_id); ?></span>
                                    </div>
                                    <?php if ($registration_type === 'student'): ?>
                                        <div class="detail-row">
                                            <span class="detail-label">Department:</span>
                                            <span class="detail-value"><?php echo htmlspecialchars($student_data['department']); ?></span>
                                        </div>
                                    <?php elseif ($registration_type === 'alumni'): ?>
                                        <div class="detail-row">
                                            <span class="detail-label">Department:</span>
                                            <span class="detail-value"><?php echo htmlspecialchars($student_data['department']); ?></span>
                                        </div>
                                        <div class="detail-row">
                                            <span class="detail-label">Passout Year:</span>
                                            <span class="detail-value"><?php echo htmlspecialchars($student_data['passout_year']); ?></span>
                                        </div>
                                    <?php endif; ?>
                                    <div class="detail-row">
                                        <span class="detail-label">Email:</span>
                                        <span class="detail-value"><?php echo htmlspecialchars($student_data['email']); ?></span>
                                    </div>
                                    <div class="detail-row">
                                        <span class="detail-label">Mobile:</span>
                                        <span class="detail-value"><?php echo htmlspecialchars($student_data['mobile']); ?></span>
                                    </div>
                                    <div class="detail-row">
                                        <span class="detail-label">Registration Date:</span>
                                        <span class="detail-value"><?php echo htmlspecialchars($student_data['registration_date']); ?></span>
                                    </div>
                                    <?php if ($registration_type === 'student' && !empty($student_data['inhouse_competition']) && $student_data['inhouse_competition'] === 'Yes'): ?>
                                        <div class="detail-row">
                                            <span class="detail-label">Inhouse Competition:</span>
                                            <span class="detail-value">Yes</span>
                                        </div>
                                        <?php if (!empty($student_data['competition_name'])): ?>
                                            <div class="detail-row">
                                                <span class="detail-label">Competition:</span>
                                                <span class="detail-value"><?php echo htmlspecialchars($student_data['competition_name']); ?></span>
                                            </div>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </div>

                                <?php if (!$payment_status): ?>
                                    <div class="note" style="background-color: rgba(241, 196, 15, 0.15); border-left: 4px solid #f1c40f; padding: 15px; margin: 20px 0; text-align: left; border-radius: 4px;">
                                        <p><strong>Important:</strong> Please complete your payment to confirm your participation in maJIStic 2k25.</p>
                                        <div style="text-align: center; margin-top: 15px;">
                                            <a href="src/transaction/payment.php?jis_id=<?php echo urlencode($jis_id); ?>" class="btn btn-payment">
                                                <i class="fas fa-credit-card"></i> Complete Payment
                                            </a>
                                        </div>
                                    </div>
                                <?php else: ?>
                                    <div class="note" style="background-color: rgba(46, 204, 113, 0.15); border-left: 4px solid #2ecc71; padding: 15px; margin: 20px 0; text-align: left; border-radius: 4px;">
                                        <p><strong>Thank you!</strong> Your payment has been completed. Your event ticket will be generated soon.</p>
                                    </div>
                                <?php endif; ?>

                                <div class="action-buttons">
                                    <a href="index.php" class="btn btn-primary">
                                        <i class="fas fa-home"></i> Back to Home
                                    </a>
                                    <a href="merchandise.php" class="btn btn-accent" style="background: linear-gradient(135deg, #2ecc71, #27ae60); color: white;">
                                        <i class="fas fa-tshirt"></i> Buy Merchandise
                                    </a>
                                    <a href="check_status.php" class="btn" style="background: linear-gradient(135deg,rgb(96, 93, 97),rgb(46, 34, 51)); color: white;">
                                        <i class="fas fa-search"></i> New Search
                                    </a>
                                </div>
                            </div>

                            <div class="timeline-column">
                                <div class="timeline-box">
                                    <h3>Your maJIStic Journey</h3>
                                    <div class="timeline-container">
                                        <div class="timeline">
                                            <div class="step completed" style="--index: 1;">
                                                <div class="step-icon">
                                                    <i class="icon fas fa-check"></i>
                                                </div>
                                                <div class="step-content">
                                                    <div class="step-title">Registration Complete</div>
                                                    <div class="step-description">You're officially registered for maJIStic 2k25! Your details have been saved in our system and you're on your way to an amazing experience.</div>
                                                </div>
                                            </div>
                                            
                                            <div class="step <?php echo $payment_status ? 'completed' : 'active'; ?>" style="--index: 2;">
                                                <div class="step-icon">
                                                    <i class="icon fas <?php echo $payment_status ? 'fa-check' : 'fa-credit-card'; ?>"></i>
                                                </div>
                                                <div class="step-content">
                                                    <div class="step-title"><?php echo $payment_status ? 'Payment Complete' : 'Payment Pending'; ?></div>
                                                    <div class="step-description"><?php echo $payment_status ? 'You\'ve successfully paid for your tickets. Thanks for your support! Your participation is confirmed.' : 'Complete your payment to secure your spot at the event. Don\'t miss out on this exciting opportunity!'; ?></div>
                                                </div>
                                            </div>
                                            
                                            <div class="step" style="--index: 3;">
                                                <div class="step-icon">
                                                    <i class="icon fas fa-ticket-alt"></i>
                                                </div>
                                                <div class="step-content">
                                                    <div class="step-title">Ticket Generation</div>
                                                    <div class="step-description">Your digital tickets will be generated soon. You'll receive them via email with all the details you need for entry to the event.</div>
                                                </div>
                                            </div>
                                            
                                            <div class="step" style="--index: 4;">
                                                <div class="step-icon">
                                                    <i class="icon fas fa-calendar-day"></i>
                                                </div>
                                                <div class="step-content">
                                                    <div class="step-title">Event Day 1 - April 11, 2025</div>
                                                    <div class="step-description">Join us for exciting performances! The first day is packed with overwelming events and entertainment.</div>
                                                </div>
                                            </div>
                                            
                                            <div class="step" style="--index: 5;">
                                                <div class="step-icon">
                                                    <i class="icon fas fa-calendar-check"></i>
                                                </div>
                                                <div class="step-content">
                                                    <div class="step-title">Event Day 2 - April 12, 2025</div>
                                                    <div class="step-description">Experience the final day with stellar performances, and closing celebrations! Don't miss the culmination of maJIStic 2k25.</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                </div>
                            </div>
                            <div class="event-message" style="margin-top: 10px;">
                                <?php if ($payment_status): ?>
                                    <div class="event-day-message">
                                        <i class="fas fa-ticket-alt"></i>
                                        <h4>Ticket Status</h4>
                                        <p>Your event tickets will be generated soon! Stay tuned for updates in your email inbox and on our website.</p>
                                    </div>
                                    
                                    <div class="event-day-message">
                                        <i class="fas fa-star"></i>
                                        <h4>Event Day Experience</h4>
                                        <p>Get ready for an unforgettable experience at maJIStic 2k25! Exciting performances, tech exhibitions, networking sessions, and amazing memories await you!</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Add animation to form
            const formContainer = document.querySelector('.form-container');
            if (formContainer) {
                formContainer.style.opacity = '0';
                formContainer.style.transform = 'translateY(20px)';
                
                setTimeout(() => {
                    formContainer.style.transition = 'opacity 0.8s ease, transform 0.8s ease';
                    formContainer.style.opacity = '1';
                    formContainer.style.transform = 'translateY(0)';
                }, 100);
            }
            
            // Format JIS ID input
            const jisIdInput = document.getElementById('jis_id');
            if (jisIdInput) {
                jisIdInput.addEventListener('input', function(e) {
                    let value = e.target.value.toUpperCase();
                    
                    // Auto format with slashes
                    if (value.length > 0 && value.indexOf('JIS/') !== 0 && !value.startsWith('JIS')) {
                        value = 'JIS/' + value;
                    } else if (value.length > 0 && value === 'JIS') {
                        value = 'JIS/';
                    }
                    
                    // Add second slash automatically
                    if (value.length === 8 && value.charAt(7) !== '/') {
                        value = value + '/';
                    }
                    
                    e.target.value = value;
                });
            }
            
            // Countdown timer
            const eventDate = new Date("April 11, 2025 00:00:00").getTime();
            
            function updateCountdown() {
                const now = new Date().getTime();
                const timeLeft = eventDate - now;
                
                const days = Math.floor(timeLeft / (1000 * 60 * 60 * 24));
                const hours = Math.floor((timeLeft % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                const minutes = Math.floor((timeLeft % (1000 * 60 * 60)) / (1000 * 60));
                const seconds = Math.floor((timeLeft % (1000 * 60)) / 1000);
                
                document.getElementById("days").innerHTML = days;
                document.getElementById("hours").innerHTML = hours < 10 ? "0" + hours : hours;
                document.getElementById("minutes").innerHTML = minutes < 10 ? "0" + minutes : minutes;
                document.getElementById("seconds").innerHTML = seconds < 10 ? "0" + seconds : seconds;
            }
            
            updateCountdown();
            setInterval(updateCountdown, 1000);
            
            // Highlight active step with pulsing effect
            const activeStep = document.querySelector('.step.active');
            if (activeStep) {
                setInterval(() => {
                    activeStep.querySelector('.step-icon').style.boxShadow = '0 0 30px rgba(52, 152, 219, 0.9)';
                    setTimeout(() => {
                        activeStep.querySelector('.step-icon').style.boxShadow = '0 0 15px rgba(52, 152, 219, 0.7)';
                    }, 1000);
                }, 2000);
            }
            
            // Apply parallax effect to header
            window.addEventListener('scroll', function() {
                const header = document.querySelector('.card-header');
                const scrollPosition = window.scrollY;
                
                if (header) {
                    header.style.backgroundPosition = `0% ${scrollPosition * 0.05}%`;
                }
            });
        });
    </script>
</body>
</html>
