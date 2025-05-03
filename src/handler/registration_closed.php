<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>maJIStic 2K25 | Event Concluded</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap">
    <style>
        :root {
            --primary-color: #6c63ff;
            --secondary-color: #f50057;
            --dark-color: #2c3e50;
            --light-color: #f8f9fa;
            --success-color: #28a745;
            --gold-color: #ffc107;
            --accent-color: #00bcd4;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background-image: url('../../images/pageback.png');
            background-repeat: repeat-y !important;
            background-size: 100% !important;
            background-position: top center !important;
            background-attachment: fixed !important;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            position: relative;
            overflow-x: hidden;
        }
        
        /* Decorative elements */
        body::before, body::after {
            content: '';
            position: absolute;
            width: 300px;
            height: 300px;
            border-radius: 50%;
            z-index: -1;
            filter: blur(80px);
            opacity: 0.4;
        }
        
        body::before {
            background: var(--primary-color);
            top: -100px;
            left: -100px;
        }
        
        body::after {
            background: var(--secondary-color);
            bottom: -100px;
            right: -100px;
        }
        
        .container {
            position: relative;
            z-index: 1;
        }
        
        .card {
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1), 0 5px 15px rgba(0, 0, 0, 0.05);
            background: white;
            overflow: hidden;
            max-width: 1000px;
            width: 100%;
            margin: 0 auto;
            position: relative;
            border: none;
        }
        
        /* Card border glow effect */
        .card::before {
            content: '';
            position: absolute;
            top: -2px;
            left: -2px;
            right: -2px;
            bottom: -2px;
            background: linear-gradient(45deg, var(--primary-color), var(--secondary-color), var(--accent-color));
            z-index: -1;
            border-radius: 22px;
            filter: blur(10px);
            opacity: 0.7;
            animation: glowPulse 3s infinite alternate;
        }
        
        .card-header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            text-align: center;
            padding: 2.5rem 1rem 3rem;
            position: relative;
            border: none;
        }
        
        .card-header::after {
            content: '';
            position: absolute;
            bottom: -20px;
            left: 0;
            right: 0;
            height: 40px;
            background: white;
            clip-path: ellipse(50% 60% at 50% 0%);
        }
        
        .card-header h1 {
            font-weight: 700;
            margin-top: 0.5rem;
            font-size: 2.2rem;
            text-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
            letter-spacing: 1px;
        }
        
        .card-body {
            padding: 3.5rem 2.5rem;
            text-align: center;
        }
        
        /* Improved celebration icons */
        .celebration-icons {
            position: relative;
            height: 7rem;
            margin-bottom: 2.5rem;
        }
        
        .status-icon {
            font-size: 5.5rem;
            margin-bottom: 2rem;
            background: linear-gradient(45deg, var(--gold-color), #ff9800);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            animation: celebration 3s infinite;
            filter: drop-shadow(0 2px 5px rgba(255, 193, 7, 0.5));
        }
        
        .celebration-icon {
            position: absolute;
            font-size: 2.5rem;
            animation: float-icons 4s infinite;
            filter: drop-shadow(0 3px 5px rgba(0, 0, 0, 0.2));
        }
        
        .celebration-icon:nth-child(1) {
            color: var(--primary-color);
            left: 25%;
            animation-delay: 0s;
            font-size: 2.8rem;
        }
        
        .celebration-icon:nth-child(2) {
            color: var(--secondary-color);
            left: 50%;
            animation-delay: 0.7s;
        }
        
        .celebration-icon:nth-child(3) {
            color: var(--gold-color);
            left: 75%;
            animation-delay: 1.4s;
            font-size: 2.2rem;
        }
        
        /* Decorative confetti effect */
        .confetti {
            position: absolute;
            width: 10px;
            height: 10px;
            opacity: 0.7;
            z-index: 1;
        }
        
        .confetti:nth-child(1) {
            background-color: var(--primary-color);
            top: 10%;
            left: 10%;
            animation: confetti-fall 4s linear infinite;
        }
        
        .confetti:nth-child(2) {
            background-color: var(--secondary-color);
            top: 20%;
            left: 80%;
            animation: confetti-fall 6s linear infinite;
        }
        
        .confetti:nth-child(3) {
            background-color: var(--gold-color);
            top: 30%;
            left: 20%;
            animation: confetti-fall 5s linear infinite;
        }
        
        .confetti:nth-child(4) {
            background-color: var(--accent-color);
            top: 40%;
            left: 70%;
            animation: confetti-fall 7s linear infinite;
        }
        
        /* Enhanced message title */
        .message-title {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 2rem;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            position: relative;
            display: inline-block;
            text-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            letter-spacing: 1px;
        }
        
        .message-title::after {
            content: '';
            position: absolute;
            bottom: -12px;
            left: 50%;
            transform: translateX(-50%);
            width: 100px;
            height: 4px;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            border-radius: 4px;
        }
        
        /* Enhanced message text */
        .message-text {
            font-size: 1.15rem;
            color: #444;
            margin-bottom: 3rem;
            line-height: 1.8;
            max-width: 90%;
            margin-left: auto;
            margin-right: auto;
        }
        
        .highlight-text {
            color: var(--secondary-color);
            font-weight: 600;
            padding: 0 4px;
            position: relative;
            z-index: 1;
        }
        
        .highlight-text::before {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 30%;
            background-color: rgba(245, 0, 87, 0.1);
            z-index: -1;
            border-radius: 4px;
        }
        
        /* Improved event stats section */
        .event-stats {
            display: flex;
            justify-content: center;
            margin: 0 auto 3rem;
            flex-wrap: wrap;
            gap: 20px;
            width: 100%;
            max-width: 600px;
        }
        
        .stat-item {
            background: linear-gradient(to bottom, #f8f9fa, #ffffff);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.05);
            border-radius: 15px;
            padding: 1.5rem;
            flex: 1;
            min-width: 150px;
            border: 1px solid rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .stat-item::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 5px;
            background: linear-gradient(90deg, var(--primary-color), var(--secondary-color));
        }
        
        .stat-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1);
        }
        
        .stat-number {
            font-size: 2.5rem;
            font-weight: bold;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 5px;
            line-height: 1;
        }
        
        .stat-label {
            font-size: 1rem;
            color: #555;
            font-weight: 500;
        }
        
        .btn-explore {
            background: linear-gradient(135deg, var(--primary-color), #5a52d5);
            color: white;
            border: none;
            padding: 14px 35px;
            border-radius: 50px;
            font-size: 1.1rem;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 10px 20px rgba(108, 99, 255, 0.3);
            margin-top: 1.5rem;
            position: relative;
            overflow: hidden;
            z-index: 1;
        }
        
        .btn-explore::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 0%;
            height: 100%;
            background: linear-gradient(135deg, #5a52d5, var(--primary-color));
            transition: all 0.5s ease;
            z-index: -1;
            border-radius: 50px;
        }
        
        .btn-explore:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 30px rgba(108, 99, 255, 0.4);
            color: white;
        }
        
        .btn-explore:hover::before {
            width: 100%;
        }
        
        .btn-explore i {
            position: relative;
            top: -1px;
        }
        
        .logo {
            max-width: 200px;
            margin-bottom: 1.5rem;
            filter: drop-shadow(0 10px 15px rgba(0, 0, 0, 0.2));
            animation: float 4s ease-in-out infinite;
        }
        
        /* Enhanced social media section */
        .community-container {
            margin-top: 3rem;
            padding-top: 2rem;
            border-top: 1px solid rgba(0, 0, 0, 0.08);
            position: relative;
        }
        
        .community-container::before {
            content: '';
            position: absolute;
            top: -1px;
            left: 50%;
            transform: translateX(-50%);
            width: 100px;
            height: 2px;
            background: linear-gradient(90deg, transparent, var(--primary-color), transparent);
        }
        
        .community-text {
            font-size: 1.1rem;
            color: #555;
            margin-bottom: 1.5rem;
            font-weight: 500;
        }
        
        .social-links {
            display: flex;
            justify-content: center;
            gap: 20px;
        }
        
        .btn-social {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 55px;
            height: 55px;
            border-radius: 50%;
            font-size: 1.3rem;
            color: white;
            transition: all 0.4s ease;
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.1);
            position: relative;
            overflow: hidden;
        }
        
        .btn-social::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.1);
            transform: scale(0);
            transition: all 0.4s ease;
            border-radius: 50%;
        }
        
        .btn-social:hover {
            transform: translateY(-8px) scale(1.05);
            box-shadow: 0 15px 25px rgba(0, 0, 0, 0.2);
            color: white;
        }
        
        .btn-social:hover::before {
            transform: scale(1.5);
        }
        
        .btn-facebook {
            background-color: #3b5998;
        }
        
        .btn-instagram {
            background: linear-gradient(45deg, #f09433, #e6683c, #dc2743, #cc2366, #bc1888);
        }
        
        .btn-linkedin {
            background-color: #0077b5;
        }
        
        .btn-whatsapp {
            background-color: #25d366;
        }
        
        @keyframes celebration {
            0% {
                transform: scale(1) rotate(0deg);
                opacity: 1;
            }
            25% {
                transform: scale(1.1) rotate(5deg);
                opacity: 0.9;
            }
            50% {
                transform: scale(1) rotate(0deg);
                opacity: 1;
            }
            75% {
                transform: scale(1.1) rotate(-5deg);
                opacity: 0.9;
            }
            100% {
                transform: scale(1) rotate(0deg);
                opacity: 1;
            }
        }
        
        @keyframes float {
            0% {
                transform: translateY(0px);
            }
            50% {
                transform: translateY(-15px);
            }
            100% {
                transform: translateY(0px);
            }
        }
        
        @keyframes float-icons {
            0% {
                transform: translateY(0px) rotate(0deg);
            }
            50% {
                transform: translateY(-20px) rotate(10deg);
            }
            100% {
                transform: translateY(0px) rotate(0deg);
            }
        }
        
        @keyframes glowPulse {
            0% {
                opacity: 0.5;
            }
            100% {
                opacity: 0.8;
            }
        }
        
        @keyframes confetti-fall {
            0% {
                transform: translateY(-100px) rotate(0deg) scale(0.7);
                opacity: 1;
            }
            100% {
                transform: translateY(500px) rotate(360deg) scale(1);
                opacity: 0;
            }
        }
        
        .float-animation {
            animation: float 5s ease-in-out infinite;
        }
        
        /* Hiding elements as requested */
        .testimonial, .memory-text, .memory-gallery {
            display: none;
        }
        
        /* Responsive adjustments */
        @media (max-width: 768px) {
            .card-body {
                padding: 2.5rem 1.5rem;
            }
            
            .message-title {
                font-size: 2rem;
            }
            
            .status-icon {
                font-size: 4.5rem;
            }
            
            .event-stats {
                flex-direction: row;
                flex-wrap: wrap;
                gap: 15px;
            }
            
            .stat-item {
                flex: 0 0 calc(50% - 15px);
                min-width: calc(50% - 15px);
                padding: 1.2rem;
            }
            
            .message-text {
                max-width: 100%;
            }
            
            .celebration-icons {
                height: 6rem;
            }
        }
        
        @media (max-width: 576px) {
            .card-body {
                padding: 2rem 1.2rem;
            }
            
            .message-title {
                font-size: 1.8rem;
            }
            
            .message-title::after {
                width: 80px;
            }
            
            .message-text {
                font-size: 1rem;
            }
            
            .status-icon {
                font-size: 4rem;
            }
            
            .event-stats {
                flex-direction: column;
                gap: 15px;
            }
            
            .stat-item {
                width: 100%;
                min-width: 100%;
            }
            
            .social-links {
                gap: 15px;
            }
            
            .btn-social {
                width: 50px;
                height: 50px;
                font-size: 1.2rem;
            }
            
            .community-text {
                font-size: 1rem;
            }
        }
    </style>
</head>
<body>
    <!-- Decorative confetti elements -->
    <div class="confetti"></div>
    <div class="confetti"></div>
    <div class="confetti"></div>
    <div class="confetti"></div>
    <div class="confetti"></div>
    <div class="confetti"></div>
    <div class="confetti"></div>
    <div class="confetti"></div>
    
    <div class="container">
        <div class="card">
            <div class="card-header">
                <img src="../../images/majistic2k25_white.png" alt="maJIStic Logo" class="logo float-animation">
            </div>
            <div class="card-body">
                <div class="celebration-icons">
                    <i class="bi bi-trophy status-icon"></i>
                    <i class="bi bi-stars celebration-icon"></i>
                    <i class="bi bi-emoji-smile-fill celebration-icon"></i>
                    <i class="bi bi-balloon-heart-fill celebration-icon"></i>
                </div>
                
                <h2 class="message-title">Successfully Concluded!</h2>
                
                <p class="message-text">
                    We're thrilled to announce that <span class="highlight-text">maJIStic 2K25</span> has successfully concluded!
                    Thank you to all participants, volunteers, sponsors, and attendees who made this event a remarkable success.
                    The memories we created together will be cherished for years to come.
                </p>
                
                <div class="event-stats">
                    <div class="stat-item">
                        <div class="stat-number">2500+</div>
                        <div class="stat-label">Footfalls</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-number">10+</div>
                        <div class="stat-label">Events</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-number">2</div>
                        <div class="stat-label">Amazing Days</div>
                    </div>
                </div>
                
                <a href="../../index.php" class="btn btn-explore">
                    <i class="bi bi-arrow-left-circle me-2"></i>Back to Home Page
                </a>
                
                <div class="community-container mt-4">
                    <p class="community-text">Stay connected and get updates about our upcoming events!</p>
                    <div class="social-links">
                        <a href="https://www.facebook.com/profile.php?id=100090087469753" target="_blank" class="btn btn-social btn-facebook">
                            <i class="bi bi-facebook"></i>
                        </a>
                        <a href="https://www.instagram.com/majistic_jisce/" target="_blank" class="btn btn-social btn-instagram">
                            <i class="bi bi-instagram"></i>
                        </a>
                        <a href="https://www.linkedin.com/company/majistic-jisce/" target="_blank" class="btn btn-social btn-linkedin">
                            <i class="bi bi-linkedin"></i>
                        </a>
                        <a href="https://chat.whatsapp.com/JyDMUAA3zw9KfbPvWhXQ1l" target="_blank" class="btn btn-social btn-whatsapp">
                            <i class="bi bi-whatsapp"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
