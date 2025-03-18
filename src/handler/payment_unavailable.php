<?php
// Set timezone to IST at the beginning of the file
date_default_timezone_set('Asia/Kolkata');

// Get the JIS ID from the URL if available
$jis_id = isset($_GET['jis_id']) ? $_GET['jis_id'] : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Unavailable - maJIStic 2k25</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            font-family: 'Inter', -apple-system, sans-serif;
            background-image: url('../../images/pageback.png');
            background-repeat: repeat-y !important;
            background-size: 100% !important;
            background-position: top center !important;
            background-attachment: initial !important;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            color: #1a202c;
        }
        
        .container {
            max-width: 1200px;
            width: 100%;
            padding: 2rem;
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        
        .main-content {
            display: flex;
            flex-direction: column;
            width: 100%;
            gap: 2rem;
        }
        
        @media (min-width: 992px) {
            .main-content {
                flex-direction: row;
                align-items: flex-start;
            }
        }
        
        .card-wrapper {
            flex: 1.5;
            width: 100%;
        }
        
        .contact-wrapper {
            flex: 1;
            width: 100%;
        }
        
        .card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            overflow: hidden;
            text-align: center;
            margin-bottom: 2rem;
            transition: transform 0.3s ease;
        }
        
        .card:hover {
            transform: translateY(-5px);
        }
        
        .card-header {
            padding: 2rem 2rem 1.5rem;
            background: linear-gradient(135deg,rgb(202, 4, 252) 0%,rgb(67, 0, 112) 100%);
            color: white;
            position: relative;
        }
        
        .logo {
            width: 180px;
            margin-bottom: 1rem;
            filter: drop-shadow(0 2px 5px rgba(0,0,0,0.2));
        }
        
        .icon-container {
            background: white;
            width: 80px;
            height: 80px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto;
            margin-bottom: 1.5rem;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0% {
                box-shadow: 0 0 0 0 rgba(255, 255, 255, 0.7);
            }
            70% {
                box-shadow: 0 0 0 10px rgba(255, 255, 255, 0);
            }
            100% {
                box-shadow: 0 0 0 0 rgba(255, 255, 255, 0);
            }
        }
        
        .icon {
            color: #e53e3e;
            font-size: 2.5rem;
        }
        
        .card-body {
            padding: 2rem;
        }
        
        h1 {
            margin: 0 0 1rem;
            font-size: 1.5rem;
            color: #2d3748;
            font-weight: 700;
        }
        
        p {
            margin-bottom: 1.5rem;
            color: #4a5568;
            line-height: 1.6;
        }
        
        .info-box {
            background: #f7fafc;
            border: 1px solid #edf2f7;
            border-radius: 8px;
            padding: 1.25rem;
            margin-bottom: 1.5rem;
            text-align: left;
            transition: all 0.3s ease;
        }
        
        .info-box:hover {
            border-color: #cbd5e0;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        }
        
        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.75rem;
            font-size: 0.95rem;
        }
        
        .info-row:last-child {
            margin-bottom: 0;
        }
        
        .info-label {
            font-weight: 500;
            color: #4a5568;
        }
        
        .info-value {
            font-weight: 600;
            color: #2d3748;
        }
        
        .buttons-container {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            justify-content: center;
            margin-top: 1rem;
        }
        
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.75rem 1.5rem;
            background: #5a67d8;
            color: white;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s;
            min-width: 80px;
            box-shadow: 0 4px 6px rgba(90, 103, 216, 0.2);
        }
        
        .btn:hover {
            background: #4c51bf;
            transform: translateY(-2px);
            box-shadow: 0 6px 8px rgba(90, 103, 216, 0.25);
        }
        
        .btn-secondary {
            background: linear-gradient(135deg,rgb(255, 149, 62) 0%,rgb(219, 100, 21) 100%);
            color:rgb(255, 255, 255);
            border: 1px solid #e2e8f0;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        
        .btn-secondary:hover {
            background: linear-gradient(135deg,rgb(173, 105, 49) 0%,rgb(235, 119, 43) 100%);
            color:rgb(255, 255, 255);
        }
        
        .btn-accent {
            background: linear-gradient(135deg,rgb(202, 4, 252) 0%,rgb(67, 0, 112) 100%);
        }
        
        .btn-accent:hover {
            background: linear-gradient(135deg,rgb(222, 24, 272) 0%,rgb(87, 20, 132) 100%);
        }
        
        .btn i {
            margin-right: 0.5rem;
        }
        
        .footer {
            text-align: center;
            margin-top: 2rem;
            font-size: 0.875rem;
            color:rgb(255, 255, 255);
            width: 100%;
        }
        .footer p{
            color:rgb(255, 255, 255);
        }
        
        /* Contact Section Styles */
        .contact-section {
            background: white;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            padding: 1.5rem;
            width: 90%;
            transition: transform 0.3s ease;
        }
        
        .contact-section:hover {
            transform: translateY(-5px);
        }
        
        .contact-section-title {
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 1.25rem;
            color: #2d3748;
            text-align: center;
            position: relative;
            padding-bottom: 1rem;
        }
        
        .contact-section-title::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 60px;
            height: 3px;
            background: linear-gradient(135deg,rgb(202, 4, 252) 0%,rgb(67, 0, 112) 100%);
            border-radius: 3px;
        }
        
        .contact-description {
            text-align: center;
            font-size: 1rem;
            margin-bottom: 1.5rem;
            color: #4a5568;
            line-height: 1.6;
        }
        
        .contact-cards {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            justify-content: center;
        }
        
        .contact-card {
            display: flex;
            align-items: center;
            padding: 1rem;
            background: #f8fafc;
            border-radius: 10px;
            border: 1px solid #edf2f7;
            width: 100%;
            transition: all 0.3s ease;
        }
        
        .contact-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
            border-color: #e2e8f0;
            background: #f1f5f9;
        }
        
        @media (min-width: 768px) {
            .contact-card {
                width: 95%;
                margin: 0 auto;
            }
        }
        
        .contact-card .icon {
            display: flex;
            align-items: center;
            justify-content: center;
            min-width: 42px;
            height: 42px;
            background: #ebf4ff;
            color: #5a67d8;
            border-radius: 8px;
            margin-right: 1rem;
            font-size: 1.1rem;
        }
        
        .contact-card .info {
            flex: 1;
        }
        
        .contact-card .info h4 {
            margin: 0 0 0.25rem 0;
            font-size: 0.9375rem;
            font-weight: 600;
            color: #2d3748;
        }
        
        .contact-card .info a {
            color: #5a67d8;
            text-decoration: none;
            font-size: 0.875rem;
            display: inline-block;
            transition: color 0.2s;
        }
        
        .contact-card .info a:hover {
            color: #4c51bf;
            text-decoration: underline;
        }
        
        .contact-card-content {
            display: flex;
            flex: 1;
        }
        
        .contact-info-container {
            flex: 1;
        }
        .contact-info-container h4{
            margin: 0 0 0.5rem 0;
        }
        .contact-info-container a{
            text-decoration: none;
        }
        
        .whatsapp-container {
            display: flex;
            align-items: center;
            justify-content: center;
            margin-left: 10px;
        }
        
        .whatsapp-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: #075E54;
            color: white;
            border-radius: 50%;
            width: 42px;
            height: 42px;
            text-decoration: none;
            transition: all 0.2s;
        }
        
        .whatsapp-btn:hover {
            background: #128C7E;
            transform: scale(1.1);
            text-decoration: none;
        }
        
        .whatsapp-btn i {
            color: white;
            font-size: 24px;
        }
        
        @media (max-width: 1100px) {
            .main-content {
                flex-direction: column;
            }
            
            .card-wrapper, .contact-wrapper {
                width: 100%;
            }
        }
        
        @media (max-width: 640px) {
            .container {
                padding: 1rem;
            }
            
            .card-header, .card-body {
                padding: 1.5rem;
            }
            
            .buttons-container {
                flex-direction: column;
                align-items: stretch;
            }
            
            .btn {
                width: 60%;
                margin: 0 auto;
            }
        }
        
        @media (max-width: 480px) {
            .contact-links {
                flex-direction: column;
                align-items: flex-start;
                gap: 8px;
            }
            
            .contact-card {
                padding: 0.75rem;
            }
            
            .contact-card .icon {
                min-width: 36px;
                height: 36px;
            }
        }
        
        @media (max-width: 380px) {
            h1 {
                font-size: 1.25rem;
            }
            
            .card-header, .card-body {
                padding: 1.25rem;
            }
            
            .icon-container {
                width: 60px;
                height: 60px;
                margin-bottom: 1rem;
            }
            
            .icon {
                font-size: 1.75rem;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="main-content">
            <div class="card-wrapper">
                <div class="card">
                    <div class="card-header">
                        <img src="https://i.postimg.cc/02CTRDb2/majisticlogoblack.png" alt="maJIStic Logo" class="logo">
                        <div class="icon-container">
                            <i class="fas fa-clock icon"></i>
                        </div>
                    </div>
                    
                    <div class="card-body">
                        <h1>Payment Currently Unavailable</h1>
                        
                        <p>We're sorry, but the payment system is temporarily unavailable. Our team is working to reopen payment collection for maJIStic 2k25 registrations.</p>
                        
                        <?php if (!empty($jis_id)): ?>
                        <div class="info-box">
                            <div class="info-row">
                                <span class="info-label">Registration ID:</span>
                                <span class="info-value"><?php echo htmlspecialchars($jis_id); ?></span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Registration Status:</span>
                                <span class="info-value">Complete - Payment Pending</span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Current Time:</span>
                                <span class="info-value"><?php echo date('d M Y, h:i A'); ?></span>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <p>Please check back later or contact our support team for assistance. You can try the payment link again in the future.</p>
                        
                        <div class="buttons-container">
                            
                            <a href="../../merchandise.php" class="btn btn-secondary">
                                <i class="fas fa-tshirt"></i> Merchandise
                            </a>
                            <?php if (!empty($jis_id)): ?>
                            <a href="../../status.php?jis_id=<?php echo htmlspecialchars($jis_id); ?>" class="btn btn-accent">
                                <i class="fas fa-search"></i> Check Status
                            </a>
                            <?php else: ?>
                            <a href="../../status.php" class="btn btn-accent">
                                <i class="fas fa-search"></i> Check Status
                            </a>
                            <?php endif; ?>
                            <a href="../../index.php" class="btn">
                                <i class="fas fa-home"></i> Back to Home
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="contact-wrapper">
                <section class="contact-section">
                    <h2 class="contact-section-title">Tech Support Team</h2>
                    <p class="contact-description">For any kind of tech related issues and queries, feel free to contact our tech support team.</p>
                    <div class="contact-cards">
                        <div class="contact-card">
                            <div class="icon">
                                <i class="fas fa-user-cog"></i>
                            </div>
                            <div class="contact-card-content">
                                <div class="contact-info-container">
                                    <h4>Priyanshu Nayan</h4>
                                    <a href="tel:+917004706722">+91 7004706722</a>
                                </div>
                                <div class="whatsapp-container">
                                    <a href="https://wa.me/917004706722?text=<?php echo urlencode('Hello,' . (!empty($jis_id) ? "\nJIS ID: " . $jis_id : "") . "\nName:\nQuery:"); ?>" target="_blank" class="whatsapp-btn">
                                        <i class="fab fa-whatsapp"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="contact-card">
                            <div class="icon">
                                <i class="fas fa-user-cog"></i>
                            </div>
                            <div class="contact-card-content">
                                <div class="contact-info-container">
                                    <h4>Sk Riyaz</h4>
                                    <a href="tel:+917029621489">+91 7029621489</a>
                                </div>
                                <div class="whatsapp-container">
                                    <a href="https://wa.me/917029621489?text=<?php 
                                    $message = 'Hello,';
                                    if (!empty($jis_id)) {
                                        $message .= "\nJIS ID: " . $jis_id;
                                    }
                                    $message .= "\nName:\nQuery:";
                                    echo urlencode($message); 
                                    ?>" target="_blank" class="whatsapp-btn">
                                        <i class="fab fa-whatsapp"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="contact-card">
                            <div class="icon">
                                <i class="fas fa-user-cog"></i>
                            </div>
                            <div class="contact-card-content">
                                <div class="contact-info-container">
                                    <h4>Ronit Pal</h4>
                                    <a href="tel:+917501005155">+91 7501005155</a>
                                </div>
                                <div class="whatsapp-container">
                                    <a href="https://wa.me/917501005155?text=<?php 
                                    $message = 'Hello,';
                                    if (!empty($jis_id)) {
                                        $message .= "\nJIS ID: " . $jis_id;
                                    }
                                    $message .= "\nName:\nQuery:";
                                    echo urlencode($message); 
                                    ?>" target="_blank" class="whatsapp-btn">
                                        <i class="fab fa-whatsapp"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="contact-card">
                            <div class="icon">
                                <i class="fas fa-user-cog"></i>
                            </div>
                            <div class="contact-card-content">
                                <div class="contact-info-container">
                                    <h4>Mohit Kumar</h4>
                                    <a href="tel:+918016804158">+91 8016804158</a>
                                </div>
                                <div class="whatsapp-container">
                                    <a href="https://wa.me/918016804158?text=<?php 
                                    $message = 'Hello,';
                                    if (!empty($jis_id)) {
                                        $message .= "\nJIS ID: " . $jis_id;
                                    }
                                    $message .= "\nName:\nQuery:";
                                    echo urlencode($message); 
                                    ?>" target="_blank" class="whatsapp-btn">
                                        <i class="fab fa-whatsapp"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="contact-card">
                            <div class="icon">
                                <i class="fas fa-envelope"></i>
                            </div>
                            <div class="contact-info-container">
                                <h4>Email Support</h4>
                                <a href="mailto:majistic@jiscollege.ac.in">majistic@jiscollege.ac.in</a>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </div>
        
        <div class="footer">
            <p>&copy; <?php echo date('Y'); ?> maJIStic. All rights reserved.</p>
            <p>JIS College of Engineering, Kalyani, Nadia - 741235, West Bengal, India</p>
        </div>
    </div>
</body>
</html>
