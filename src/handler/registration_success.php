<?php
include '../../includes/db_config.php';

// Include alumni coordinator configuration
$alumni_config_path = __DIR__ . '/../../src/config/alumni_coordinator_config.php';
if (file_exists($alumni_config_path)) {
    include_once $alumni_config_path;
}

// Initialize variables
$jis_id = isset($_GET['jis_id']) ? $_GET['jis_id'] : '';
$student_data = null;
$is_alumni = isset($_GET['alumni']) && $_GET['alumni'] == '1';

// If JIS ID is provided, fetch student details
if (!empty($jis_id)) {
    try {
        if ($is_alumni) {
            $student_doc = $alumni_registrations->findOne(['jis_id' => $jis_id]);
            if ($student_doc) {
                // Convert MongoDB document to array and format fields consistently with old MySQL output
                $student_data = [
                    'student_name' => $student_doc['alumni_name'],
                    'department' => $student_doc['department'],
                    'email' => $student_doc['email'],
                    'mobile' => $student_doc['mobile'],
                    'registration_date' => $student_doc['registration_date'],
                    'passout_year' => $student_doc['passout_year'] // For alumni
                ];
            }
        } else {
            $student_doc = $registrations->findOne(['jis_id' => $jis_id]);
            if ($student_doc) {
                // Convert MongoDB document to array and format fields
                $student_data = [
                    'student_name' => $student_doc['student_name'],
                    'department' => $student_doc['department'],
                    'email' => $student_doc['email'],
                    'mobile' => $student_doc['mobile'],
                    'registration_date' => $student_doc['registration_date']
                ];
            }
        }
    } catch (Exception $e) {
        error_log("MongoDB query error: " . $e->getMessage());
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration Successful - maJIStic 2k25</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap">
    <?php include '../../includes/links.php'; ?>
    <link rel="stylesheet" href="../../style.css">
    <style>
        body {
            background-image: url('../../images/pageback.png');
            background-size: cover;
            background-attachment: fixed;
            min-height: 100vh;
            font-family: 'Poppins', sans-serif;
            color: #ffffff;
            position: relative;
            overflow-x: hidden;
        }
        
        .success-container {
            max-width: 1000px;
            margin: 40px auto;
            padding: 0 20px;
        }
        
        .success-card {
            background: rgba(10, 10, 30, 0.75);
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.6);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            padding-bottom: 30px;
            transform: translateY(0);
            transition: transform 0.3s ease;
        }
        
        .success-card:hover {
            transform: translateY(-5px);
        }
        
        .card-header {
            background: linear-gradient(135deg, #6e48aa, #9d50bb, #6e48aa);
            padding: 25px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        
        .card-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 200%;
            height: 100%;
            background: linear-gradient(45deg, transparent, rgba(255, 255, 255, 0.3), transparent);
            animation: shine 3s infinite;
        }
        
        @keyframes shine {
            0% { transform: translateX(-100%); }
            100% { transform: translateX(100%); }
        }
        
        .logo {
            width: 180px;
            margin-bottom: 15px;
            filter: drop-shadow(0 0 10px rgba(255, 255, 255, 0.5));
            animation: pulsate 2s infinite;
        }
        
        @keyframes pulsate {
            0% { transform: scale(1); filter: brightness(1); }
            50% { transform: scale(1.05); filter: brightness(1.2); }
            100% { transform: scale(1); filter: brightness(1); }
        }
        
        .card-body {
            padding: 40px;
            text-align: center;
        }
        
        .thank-you {
            font-size: 3rem;
            margin-bottom: 20px;
            font-weight: 700;
            background: linear-gradient(to right, #c471ed, #f64f59);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            animation: colorShift 8s infinite alternate;
            text-shadow: 0 0 30px rgba(196, 113, 237, 0.3);
        }
        
        @keyframes colorShift {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }
        
        .message {
            font-size: 1.3rem;
            line-height: 1.7;
            margin-bottom: 30px;
            color: #e0e0e0;
        }
        
        .highlight {
            color: #00ffcc;
            font-weight: 600;
            position: relative;
            padding: 0 5px;
        }
        
        .highlight::after {
            content: '';
            position: absolute;
            width: 100%;
            height: 30%;
            background-color: rgba(0, 255, 204, 0.2);
            bottom: 0;
            left: 0;
            z-index: -1;
            border-radius: 2px;
        }
        
        .note {
            background-color: rgba(46, 204, 113, 0.15);
            border-left: 4px solid #2ecc71;
            padding: 20px;
            margin: 25px 0;
            text-align: left;
            border-radius: 6px;
            box-shadow: 0 4px 15px rgba(46, 204, 113, 0.1);
        }
        
        .warning {
            background-color: rgba(231, 76, 60, 0.15);
            border-left: 4px solid #e74c3c;
            padding: 20px;
            margin: 25px 0;
            text-align: left;
            border-radius: 6px;
            box-shadow: 0 4px 15px rgba(231, 76, 60, 0.1);
        }
        
        .action-buttons {
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            gap: 20px;
            margin-top: 40px;
        }
        
        .btn {
            padding: 14px 28px;
            border: none;
            border-radius: 50px;
            font-size: 1.05rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 10px;
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.2);
            min-width: 200px;
            justify-content: center;
            position: relative;
            overflow: hidden;
            z-index: 1;
        }
        
        .btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.1);
            transition: all 0.4s ease;
            z-index: -1;
        }
        
        .btn:hover::before {
            left: 0;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #6a11cb, #2575fc);
            color: white;
        }
        
        .btn-secondary {
            background: linear-gradient(135deg, #9733EE, #DA22FF);
            color: white;
        }
        
        .btn-accent {
            background: linear-gradient(135deg, #11998e, #38ef7d);
            color: white;
        }
        
        .btn:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.3);
        }
        
        .btn:active {
            transform: translateY(-2px);
        }
        
        .student-details {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 16px;
            padding: 30px;
            margin: 30px 0;
            text-align: left;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
            backdrop-filter: blur(8px);
            border: 1px solid rgba(255, 255, 255, 0.08);
            transition: all 0.3s ease;
        }
        
        .student-details:hover {
            box-shadow: 0 12px 32px rgba(0, 0, 0, 0.3);
            transform: translateY(-5px);
        }
        
        .student-details h3 {
            border-bottom: 2px solid rgba(255, 255, 255, 0.1);
            padding-bottom: 15px;
            margin-bottom: 20px;
            color: #00bfff;
            font-size: 1.5rem;
            font-weight: 600;
        }
        
        .detail-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
            font-size: 1.05rem;
            padding: 8px 0;
            border-bottom: 1px dashed rgba(255, 255, 255, 0.1);
        }
        
        .detail-row:last-child {
            border-bottom: none;
        }
        
        .detail-label {
            font-weight: 500;
            color: #a0aec0;
        }
        
        .detail-value {
            font-weight: 500;
            color: #f8f9fa;
        }
        
        .confetti {
            position: fixed;
            top: -20px;
            width: 15px;
            height: 15px;
            background-color: #f0f;
            animation: confetti-fall 5s ease-in-out forwards;
            z-index: 100;
            opacity: 0.9;
            pointer-events: none;
        }
        
        @keyframes confetti-fall {
            0% { transform: translateY(0) rotate(0deg); opacity: 1; }
            80% { opacity: 1; }
            100% { transform: translateY(100vh) rotate(720deg); opacity: 0; }
        }
        
        .social-share {
            margin-top: 30px;
            padding: 25px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            background: linear-gradient(to right, rgba(25, 25, 50, 0.3), rgba(40, 40, 80, 0.3));
            border-radius: 12px;
        }
        
        .social-share h4 {
            margin-bottom: 15px;
            color: #e0e0e0;
            font-size: 1.2rem;
        }
        
        .social-icons {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin-top: 15px;
        }
        
        .social-icon {
            text-decoration: none;
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            transition: all 0.4s ease;
            font-size: 24px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
        }
        
        .social-icon:hover {
            text-decoration: none;
            transform: translateY(-8px) scale(1.1);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.4);
        }
        
        .countdown {
            margin: 40px 0;
            background: linear-gradient(135deg, rgba(0, 0, 0, 0.4), rgba(10, 10, 40, 0.6));
            padding: 25px;
            border-radius: 16px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.05);
            display: flex;
            flex-direction: column;
        }
        
        .countdown h4 {
            margin-bottom: 15px;
            color: #ffc107;
            font-size: 1.3rem;
            font-weight: 600;
        }
        
        .countdown-timer {
            display: flex;
            justify-content: center;
            gap: 20px;
        }
        
        .time-unit {
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        
        .time-value {
            font-size: 2.2rem;
            font-weight: bold;
            color: #ffffff;
            background: linear-gradient(145deg, #192841, #1e3a8a);
            border-radius: 12px;
            min-width: 80px;
            text-align: center;
            padding: 10px 0;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.3);
            position: relative;
            overflow: hidden;
        }
        
        .time-value::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(rgba(255, 255, 255, 0.1), rgba(255, 255, 255, 0));
        }
        
        .time-label {
            font-size: 0.85rem;
            margin-top: 10px;
            color: #d0d0d0;
            font-weight: 500;
            letter-spacing: 1px;
        }
        
        .inspiration-quote {
            background: rgba(0, 0, 0, 0.2);
            padding: 20px;
            border-radius: 12px;
            margin: 30px 0;
            position: relative;
            font-style: italic;
        }
        
        .inspiration-quote::before {
            content: '"';
            font-size: 4rem;
            position: absolute;
            top: -10px;
            left: 10px;
            color: rgba(255, 255, 255, 0.1);
            font-family: Georgia, serif;
        }
        
        .celebration-badge {
            position: relative;
            background: linear-gradient(135deg, #ff9a9e, #fad0c4);
            color: #333;
            padding: 10px 25px;
            border-radius: 50px;
            font-weight: 600;
            display: inline-block;
            margin: 20px 0;
            box-shadow: 0 5px 15px rgba(255, 154, 158, 0.4);
        }
        
        .success-emoji {
            font-size: 2.5rem;
            margin: 10px 0;
            animation: bounce 2s infinite;
        }
        
        @keyframes bounce {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-20px); }
        }
        
        @media (max-width: 768px) {
            .thank-you {
                font-size: 2rem;
            }
            
            .success-container {
                padding: 0 15px;
            }
            
            .action-buttons {
                flex-direction: column;
                align-items: center;
                gap: 15px;
            }
            
            .btn {
                width: 100%;
                max-width: 280px;
            }
            
            .detail-row {
                flex-direction: column;
                margin-bottom: 20px;
            }
            
            .detail-value {
                margin-top: 5px;
                word-break: break-all;
            }
            
            .countdown-timer {
                flex-wrap: wrap;
                gap: 15px;
            }
            
            .time-value {
                min-width: 60px;
                font-size: 1.8rem;
            }
            
            .card-body {
                padding: 30px 20px;
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
        
        /* Party elements animation */
        .party-element {
            position: fixed;
            top: -50px;
            width: 20px;
            height: 20px;
            opacity: 0;
            z-index: 1000;
            pointer-events: none;
            animation: party-fall 8s ease-in-out forwards;
        }
        
        @keyframes party-fall {
            0% { 
                transform: translateY(0) rotate(0deg); 
                opacity: 1; 
            }
            80% { opacity: 0.8; }
             100% { 
                transform: translateY(100vh) rotate(720deg); 
                opacity: 0; 
            }
        }
        
        /* Glass separator */
        .glass-separator {
            height: 4px;
            background: linear-gradient(to right, transparent, rgba(255, 255, 255, 0.2), transparent);
            margin: 30px 0;
            border-radius: 2px;
        }

        .coordinator-card {
            background: rgba(0, 0, 0, 0.3);
            border-radius: 16px;
            padding: 30px;
            margin: 30px 0;
            text-align: left;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
            backdrop-filter: blur(8px);
            border: 1px solid rgba(255, 255, 255, 0.08);
            transition: all 0.3s ease;
            border-left: 4px solid #3498db;
        }
        
        .coordinator-card:hover {
            box-shadow: 0 12px 32px rgba(0, 0, 0, 0.3);
            transform: translateY(-5px);
        }
        
        .coordinator-card h3 {
            color: #3498db;
            margin-bottom: 15px;
            font-size: 1.4rem;
            font-weight: 600;
        }
        
        .coordinator-info {
            background: rgba(255, 255, 255, 0.05);
            padding: 15px;
            border-radius: 10px;
            margin-top: 15px;
        }
        
        .coordinator-info p {
            margin: 8px 0;
            color: #f8f9fa;
        }
        
        .coordinator-label {
            color: #a0aec0;
            font-weight: 500;
            display: inline-block;
            width: 100px;
        }
        
        .coordinator-value {
            font-weight: 600;
            color: #ffc107;
        }
        
        .coordinator-contact {
            display: flex;
            align-items: center;
            margin-top: 10px;
        }
        
        .coordinator-contact a {
            display: inline-flex;
            align-items: center;
            padding: 8px 15px;
            background: rgba(52, 152, 219, 0.2);
            border-radius: 50px;
            color: #3498db;
            text-decoration: none;
            margin-right: 10px;
            transition: all 0.3s ease;
        }
        
        .coordinator-contact a:hover {
            background: rgba(52, 152, 219, 0.4);
            transform: translateY(-2px);
        }
        
        .coordinator-contact i {
            margin-right: 5px;
        }
        
        .coordinator-loading {
            text-align: center;
            padding: 20px;
            font-style: italic;
            color: #a0aec0;
        }
        
        .default-coordinator {
            border-top: 1px dashed rgba(255, 255, 255, 0.1);
            padding-top: 15px;
            margin-top: 15px;
        }
        
        .default-coordinator:first-of-type {
            border-top: none;
            padding-top: 5px;
            margin-top: 5px;
        }

        /* Alumni QR Code Payment Card */
        .alumni-payment-card {
            background: rgba(124, 58, 237, 0.15);
            border-radius: 16px;
            padding: 30px;
            margin: 30px 0;
            text-align: left;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
            backdrop-filter: blur(8px);
            border: 1px solid rgba(255, 255, 255, 0.08);
            transition: all 0.3s ease;
            border-left: 4px solid #7c3aed;
        }
        
        .alumni-payment-card:hover {
            box-shadow: 0 12px 32px rgba(0, 0, 0, 0.3);
            transform: translateY(-5px);
        }
        
        .alumni-payment-card h3 {
            color: #7c3aed;
            margin-bottom: 15px;
            font-size: 1.4rem;
            font-weight: 600;
        }
        
        .payment-amount {
            font-weight: bold;
            font-size: 20px;
            color: #7c3aed;
            margin: 20px 0;
            text-align: center;
        }
        
        .payment-qr-container {
            background-color: white;
            padding: 15px;
            border-radius: 8px;
            margin: 15px auto;
            width: fit-content;
            box-shadow: 0 4px 10px rgba(0,0,0,0.2);
        }
        
        .payment-qr-container img {
            max-width: 200px;
            height: auto;
        }
        
        .payment-instructions {
            text-align: left;
            font-size: 14px;
            background-color: rgba(255, 255, 255, 0.1);
            padding: 15px;
            border-radius: 5px;
            margin-top: 15px;
        }

        .alumni-coordinator-info {
            margin-top: 20px;
            background-color: rgba(255, 255, 255, 0.1);
            padding: 15px;
            border-radius: 8px;
        }
        
        .alumni-coordinator-info h4 {
            color: #7c3aed;
            margin-top: 0;
            margin-bottom: 10px;
            font-size: 16px;
        }
        
        .alumni-coordinator-info p {
            margin: 5px 0;
        }
        
        .alumni-coordinator-contact {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 10px;
        }
        
        .alumni-coordinator-contact a {
            display: inline-flex;
            align-items: center;
            padding: 8px 15px;
            border-radius: 5px;
            text-decoration: none;
            font-size: 14px;
            gap: 5px;
            transition: all 0.3s ease;
        }
        
        .alumni-coordinator-contact a.call {
            background: rgba(52, 152, 219, 0.2);
            color: #3498db;
        }
        
        .alumni-coordinator-contact a.whatsapp {
            background: rgba(37, 211, 102, 0.2);
            color: #25d366;
        }
        
        .alumni-coordinator-contact a.email {
            background: rgba(231, 76, 60, 0.2);
            color: #e74c3c;
        }
        
        .alumni-coordinator-contact a:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        }
        
        .payment-status-note {
            background-color: rgba(246, 229, 141, 0.2);
            border-left: 4px solid #f6e58d;
            padding: 15px;
            margin-top: 20px;
            border-radius: 5px;
            text-align: left;
        }
        
        .payment-status-note h4 {
            color: #be9e44;
            font-weight: 600;
            margin-top: 0;
            margin-bottom: 10px;
            font-size: 16px;
        }

        .btn-whatsapp {
            background: linear-gradient(135deg, #25d366, #128c7e);
            color: white;
        }
        
        .btn-whatsapp:hover {
            background: linear-gradient(135deg, #128c7e, #075e54);
        }
    </style>
</head>
<body>
    <?php include '../../includes/header.php'; ?>
    
    <div class="success-container">
        <div class="success-card animate-in">
            <div class="card-header">
                <img src="../../images/majisticlogo.png" alt="maJIStic Logo" class="logo">
            </div>
            
            <div class="card-body">
                <h1 class="thank-you">Thank You for Registering!</h1>
                
                <p class="message">
                    Congratulations! Your spot for <span class="highlight">maJIStic 2k25</span> has been successfully reserved!
                </p>
                
                <div class="celebration-badge">We're thrilled to have you join us for this incredible cultural fest of JISCE.</div>
                
                <?php if ($student_data): ?>
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
                    <div class="detail-row">
                        <span class="detail-label">Department:</span>
                        <span class="detail-value"><?php echo htmlspecialchars($student_data['department']); ?></span>
                    </div>
                    <?php if ($is_alumni && isset($student_data['passout_year'])): ?>
                    <div class="detail-row">
                        <span class="detail-label">Passout Year:</span>
                        <span class="detail-value"><?php echo htmlspecialchars($student_data['passout_year']); ?></span>
                    </div>
                    <?php endif; ?>
                    <div class="detail-row">
                        <span class="detail-label">Registration Date:</span>
                        <span class="detail-value"><?php echo htmlspecialchars($student_data['registration_date']); ?></span>
                    </div>
                </div>

                <?php if ($is_alumni): ?>
                <!-- Alumni Payment Card -->
                <div class="alumni-payment-card">
                    <h3>Alumni Payment Information</h3>
                    <p>Please complete your payment using the QR code below to confirm your participation in maJIStic 2k25.</p>
                    
                    <div class="payment-amount">₹1000</div>
                    
                    <?php if (defined('ALUMNI_PAYMENT_QR') && !empty(ALUMNI_PAYMENT_QR)): ?>
                    <div class="payment-qr-container">
                        <img src="<?php echo htmlspecialchars(ALUMNI_PAYMENT_QR); ?>" alt="Payment QR Code">
                    </div>
                    
                    <div class="payment-instructions">
                        <p><strong>Instructions:</strong></p>
                        <p><?php echo defined('ALUMNI_PAYMENT_INSTRUCTIONS') ? nl2br(htmlspecialchars(ALUMNI_PAYMENT_INSTRUCTIONS)) : 'Scan the QR code with any UPI app to pay the alumni registration fee (Rs. 1000). After payment, please send a screenshot to the coordinator via WhatsApp for verification.'; ?></p>
                    </div>
                    <?php else: ?>
                    <div style="background-color: rgba(231, 76, 60, 0.1); color: #e74c3c; padding: 15px; border-radius: 5px; text-align: center; margin: 20px 0;">
                        <p style="font-weight: bold; margin-bottom: 0;">QR code not available. Please contact the alumni coordinator directly using the contact information below.</p>
                    </div>
                    <?php endif; ?>
                    
                    <div class="payment-status-note">
                        <h4><i class="fas fa-clock" style="margin-right: 8px;"></i> Payment Status Update</h4>
                        <p>After making your payment, please allow some time for your payment status to be updated. Our team is diligently verifying all payments and will update your status as soon as possible.</p>
                        <p>If you've already made the payment and status is not updated within 48 hours, please contact the alumni coordinator.</p>
                    </div>
                    
                    <div class="alumni-coordinator-info">
                        <h4>Alumni Coordinator</h4>
                        <p><strong>Name:</strong> <?php echo defined('ALUMNI_COORDINATOR_NAME') ? htmlspecialchars(ALUMNI_COORDINATOR_NAME) : 'Dr. Proloy Ghosh'; ?></p>
                        <p><strong>Contact:</strong> <?php echo defined('ALUMNI_COORDINATOR_CONTACT') ? htmlspecialchars(ALUMNI_COORDINATOR_CONTACT) : '7980532913'; ?></p>
                        <?php if (defined('ALUMNI_COORDINATOR_EMAIL') && !empty(ALUMNI_COORDINATOR_EMAIL)): ?>
                        <p><strong>Email:</strong> <?php echo htmlspecialchars(ALUMNI_COORDINATOR_EMAIL); ?></p>
                        <?php endif; ?>
                        
                        <div class="alumni-coordinator-contact">
                            <?php if (defined('ALUMNI_COORDINATOR_CONTACT') && !empty(ALUMNI_COORDINATOR_CONTACT)): ?>
                            <a href="tel:+91<?php echo ALUMNI_COORDINATOR_CONTACT; ?>" class="call">
                                <i class="fas fa-phone-alt"></i> Call
                            </a>
                            
                            <a href="https://wa.me/91<?php echo ALUMNI_COORDINATOR_CONTACT; ?>?text=Hello,%20I%20have%20registered%20for%20maJIStic%202025%20as%20an%20alumnus%20(JIS%20ID:%20<?php echo urlencode($jis_id); ?>).%20I%20would%20like%20to%20complete%20my%20payment." target="_blank" class="whatsapp">
                                <i class="fab fa-whatsapp"></i> WhatsApp
                            </a>
                            <?php endif; ?>
                            
                            <?php if (defined('ALUMNI_COORDINATOR_EMAIL') && !empty(ALUMNI_COORDINATOR_EMAIL)): ?>
                            <a href="mailto:<?php echo ALUMNI_COORDINATOR_EMAIL; ?>?subject=Alumni%20Registration%20Payment%20for%20maJIStic%202025&body=Hello,%0A%0AI%20have%20registered%20for%20maJIStic%202025%20as%20an%20alumnus%20(JIS%20ID:%20<?php echo $jis_id; ?>).%0A%0AI%20would%20like%20to%20complete%20my%20payment.%0A%0AThank%20you." class="email">
                                <i class="fas fa-envelope"></i> Email
                            </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php else: ?>
                <!-- Regular Student Coordinator Card -->
                <div class="coordinator-card" id="coordinatorCard">
                    <h3>Payment Information</h3>
                    <p>For payment of the Ticket Price, Please contact the following coordinator at the respective department:</p>
                    <div id="coordinatorDetails">
                        <div class="coordinator-loading">
                            <i class="fas fa-spinner fa-spin"></i> Loading coordinator details...
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                <?php endif; ?>
                
                <div class="inspiration-quote">
                    "The future belongs to those who believe in the beauty of their dreams. Your journey with maJIStic 2k25 starts now!"
                </div>
                
                <div class="glass-separator"></div>
                
                <div class="note">
                    <p><strong>Important Note:</strong> 
                    <?php if ($is_alumni): ?>
                        Please complete the payment using the QR code above or contact the alumni coordinator to confirm your participation.
                    <?php else: ?>
                        Please contact your department coordinator at the department to pay the registration fee and secure your spot.
                    <?php endif; ?>
                    </p>
                </div>
                
                <div class="warning">
                    <p><strong>Remember:</strong> 
                    <?php if ($is_alumni): ?>
                        Please bring your College ID or any Government ID for verification on the event day.
                    <?php else: ?>
                        Your college ID will be mandatory for check-in on the event day. Please ensure you bring it along!
                    <?php endif; ?>
                    </p>
                </div>
                
                <div class="glass-separator"></div>
                
                <div class="countdown">
                    <h4>Countdown to maJIStic 2k25</h4>
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
                    <a href="../../check_status.php" class="btn btn-secondary">
                        <i class="fas fa-search"></i> Check Status
                    </a>
                    <a href="../../merchandise.php" class="btn btn-accent">
                        <i class="fas fa-tshirt"></i> Book Merchandise
                    </a>
                    <a href="https://chat.whatsapp.com/JyDMUAA3zw9KfbPvWhXQ1l" target="_blank" class="btn btn-whatsapp">
                        <i class="fab fa-whatsapp"></i> Join WhatsApp Community
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
    
    <script>
        function createCelebrationElements() {
            const partyIcons = ['🎉', '🎊', '✨', '⭐', '🥳', '🎈', '🎆', '🎇', '🌟', '💫'];
            const colors = ['#f39c12', '#2ecc71', '#3498db', '#e74c3c', '#9b59b6', '#1abc9c', '#ff758c', '#ff7eb3', '#00f2fe', '#8a2be2'];
            
            // Create confetti elements
            for (let i = 0; i < 100; i++) {
                const confetti = document.createElement('div');
                confetti.classList.add('confetti');
                confetti.style.backgroundColor = colors[Math.floor(Math.random() * colors.length)];
                confetti.style.left = Math.random() * 100 + 'vw';
                confetti.style.width = (Math.random() * 10 + 5) + 'px';
                confetti.style.height = (Math.random() * 10 + 5) + 'px';
                confetti.style.borderRadius = Math.random() > 0.5 ? '50%' : '0';
                confetti.style.animationDelay = Math.random() * 5 + 's';
                confetti.style.animationDuration = (Math.random() * 3 + 3) + 's';
                document.body.appendChild(confetti);
                
                // Remove confetti after animation completes
                setTimeout(() => {
                    confetti.remove();
                }, 5000);
            }
            
            // Create emoji party elements
            for (let i = 0; i < 20; i++) {
                const party = document.createElement('div');
                party.classList.add('party-element');
                party.textContent = partyIcons[Math.floor(Math.random() * partyIcons.length)];
                party.style.left = Math.random() * 100 + 'vw';
                party.style.fontSize = (Math.random() * 20 + 20) + 'px';
                party.style.animationDelay = (Math.random() * 2) + 's';
                party.style.animationDuration = (Math.random() * 3 + 2) + 's';
                document.body.appendChild(party);
                
                // Remove party element after animation completes
                setTimeout(() => {
                    party.remove();
                }, 5000);
            }
        }
        
        // Countdown timer
        function updateCountdown() {
            const eventDate = new Date('2025-04-11T10:00:00'); // Set your event date here
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
            createCelebrationElements();
            addAnimations();
            updateCountdown();
            setInterval(updateCountdown, 1000);
            
            // Add pulse effect to student details on hover
            const studentDetails = document.querySelector('.student-details');
            if (studentDetails) {
                studentDetails.addEventListener('mouseenter', function() {
                    this.style.transform = 'translateY(-8px)';
                });
                studentDetails.addEventListener('mouseleave', function() {
                    this.style.transform = 'translateY(-5px)';
                });
            }
            
            // Fetch coordinator details for the student's department
            <?php if (!$is_alumni): ?>
            fetchCoordinatorInfo();
            <?php endif; ?>
        });
        
        function fetchCoordinatorInfo() {
            // Get student department
            <?php if ($student_data && isset($student_data['department'])): ?>
            const studentDept = "<?php echo addslashes($student_data['department']); ?>";
            
            // Always fetch coordinator details based on the user's department
            fetch('../../src/api/get_coordinators.php?department=' + encodeURIComponent(studentDept))
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success' && data.data.length > 0) {
                        displayCoordinatorInfo(data.data);
                    } else {
                        displayGenericCoordinatorInfo();
                    }
                })
                .catch(error => {
                    console.error('Error fetching coordinator data:', error);
                    displayGenericCoordinatorInfo();
                });
            <?php else: ?>
            displayGenericCoordinatorInfo();
            <?php endif; ?>
        }
        
        function displayCoordinatorInfo(coordinators) {
            const coordinatorDetailsDiv = document.getElementById('coordinatorDetails');
            
            let html = '';
            coordinators.forEach(coordinator => {
                // Add time information - if not available, use default time
                const availableTime = coordinator.available_time || '10:00 AM - 5:00 PM (Monday-Friday)';
                
                html += `<div class="coordinator-info animate-in">
                    <p><span class="coordinator-label">Name:</span> <span class="coordinator-value">${coordinator.name}</span></p>
                    <p><span class="coordinator-label">Department:</span> <span class="coordinator-value">${coordinator.department}</span></p>
                    <p><span class="coordinator-label">Contact:</span> <span class="coordinator-value">${coordinator.contact}</span></p>
                    <p><span class="coordinator-label">Time:</span> <span class="coordinator-value">${availableTime}</span></p>
                    <div class="coordinator-contact">
                        <a href="tel:${coordinator.contact}"><i class="fas fa-phone"></i> Call</a>
                        <a href="https://wa.me/91${coordinator.contact}" target="_blank"><i class="fab fa-whatsapp"></i> WhatsApp</a>
                    </div>
                </div>`;
            });
            
            coordinatorDetailsDiv.innerHTML = html;
        }
        
        function displayGenericCoordinatorInfo() {
            const coordinatorDetailsDiv = document.getElementById('coordinatorDetails');
            
            coordinatorDetailsDiv.innerHTML = `<div class="coordinator-info animate-in default-coordinator">
                <h4>Department Coordinator Not Found</h4>
                <p>Please contact any of the following coordinators for payment:</p>
                
                <div class="default-coordinator">
                    <p><span class="coordinator-label">Name:</span> <span class="coordinator-value">Priyanshu Nayan</span></p>
                    <p><span class="coordinator-label">Contact:</span> <span class="coordinator-value">7004706722</span></p>
                    <p><span class="coordinator-label">Time:</span> <span class="coordinator-value">10:00 AM - 5:00 PM (Monday-Friday)</span></p>
                    <div class="coordinator-contact">
                        <a href="tel:7004706722"><i class="fas fa-phone"></i> Call 7004706722</a>
                        <a href="https://wa.me/917004706722" target="_blank"><i class="fab fa-whatsapp"></i> WhatsApp 7004706722</a>
                    </div>
                </div>
                
                <div class="default-coordinator">
                    <p><span class="coordinator-label">Name:</span> <span class="coordinator-value">Dr. Proloy Ghosh</span></p>
                    <p><span class="coordinator-label">Contact:</span> <span class="coordinator-value">7980532913</span></p>
                    <p><span class="coordinator-label">Time:</span> <span class="coordinator-value">10:00 AM - 5:00 PM (Monday-Friday)</span></p>
                    <div class="coordinator-contact">
                        <a href="tel:7980532913"><i class="fas fa-phone"></i> Call 7980532913</a>
                        <a href="https://wa.me/917980532913" target="_blank"><i class="fab fa-whatsapp"></i> WhatsApp 7980532913</a>
                    </div>
                </div>
                
                <div class="default-coordinator">
                    <p><span class="coordinator-label">Name:</span> <span class="coordinator-value">Dr. Madhura Chakraborty</span></p>
                    <p><span class="coordinator-label">Contact:</span> <span class="coordinator-value">7980979789</span></p>
                    <p><span class="coordinator-label">Time:</span> <span class="coordinator-value">10:00 AM - 5:00 PM (Monday-Friday)</span></p>
                    <div class="coordinator-contact">
                        <a href="tel:7980979789"><i class="fas fa-phone"></i> Call 7980979789</a>
                        <a href="https://wa.me/917980979789" target="_blank"><i class="fab fa-whatsapp"></i> WhatsApp 7980979789</a>
                    </div>
                </div>
            </div>`;
        }
    </script>
</body>
</html>
