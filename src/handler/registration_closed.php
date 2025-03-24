<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrations Closed | maJIStic 2K25</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        :root {
            --primary-color: #6c63ff;
            --secondary-color: #f50057;
            --dark-color: #2c3e50;
            --light-color: #f8f9fa;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background-image: url('../../images/pageback.png');
            background-repeat: repeat-y !important;
            background-size: 100% !important;
            background-position: top center !important;
            background-attachment: fixed !important; /* Changed from initial to fixed */

            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .card {
            border-radius: 20px;
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1);
            background: white;
            overflow: hidden;
            max-width: 800px;
            width: 100%;
            margin: 0 auto;
        }
        
        .card-header {
            background-color: var(--primary-color);
            color: white;
            text-align: center;
            padding: 2rem 1rem;
            position: relative;
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
        
        .card-body {
            padding: 3rem 2rem;
            text-align: center;
        }
        
        .status-icon {
            font-size: 5rem;
            margin-bottom: 2rem;
            animation: pulse 2s infinite;
            color: var(--secondary-color);
        }
        
        .message-title {
            font-size: 2rem;
            font-weight: bold;
            margin-bottom: 1rem;
            color: var(--dark-color);
        }
        
        .message-text {
            font-size: 1.1rem;
            color: #666;
            margin-bottom: 2rem;
            line-height: 1.6;
        }
        
        .btn-explore {
            background-color: var(--primary-color);
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 50px;
            font-size: 1.1rem;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(108, 99, 255, 0.3);
        }
        
        .btn-explore:hover {
            background-color: #5a52d5;
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(108, 99, 255, 0.4);
            color: white;
        }
        
        .logo {
            max-width: 200px;
            margin-bottom: 1rem;
        }
        
        .wave {
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 100px;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 320"><path fill="%23ffffff" fill-opacity="1" d="M0,96L48,112C96,128,192,160,288,154.7C384,149,480,107,576,112C672,117,768,171,864,197.3C960,224,1056,224,1152,197.3C1248,171,1344,117,1392,90.7L1440,64L1440,320L1392,320C1344,320,1248,320,1152,320C1056,320,960,320,864,320C768,320,672,320,576,320C480,320,384,320,288,320C192,320,96,320,48,320L0,320Z"></path></svg>') repeat-x;
            background-size: 1440px 100px;
        }
        
        @keyframes pulse {
            0% {
                transform: scale(1);
                opacity: 1;
            }
            50% {
                transform: scale(1.1);
                opacity: 0.8;
            }
            100% {
                transform: scale(1);
                opacity: 1;
            }
        }
        
        @keyframes float {
            0% {
                transform: translateY(0px);
            }
            50% {
                transform: translateY(-10px);
            }
            100% {
                transform: translateY(0px);
            }
        }
        
        .float-animation {
            animation: float 3s ease-in-out infinite;
        }
        
        .countdown {
            display: flex;
            justify-content: center;
            margin: 2rem 0;
        }
        
        .countdown-item {
            margin: 0 10px;
            text-align: center;
        }
        
        .countdown-number {
            font-size: 2rem;
            font-weight: bold;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        
        .countdown-label {
            font-size: 0.8rem;
            color: #666;
            text-transform: uppercase;
        }
        
        /* Responsive adjustments */
        @media (max-width: 576px) {
            .card-body {
                padding: 2rem 1rem;
            }
            
            .message-title {
                font-size: 1.5rem;
            }
            
            .status-icon {
                font-size: 4rem;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <div class="card-header">
                <img src="../../images/majisticlogo.png" alt="maJIStic Logo" class="logo float-animation">
                <h1>maJIStic 2K25</h1>
            </div>
            <div class="card-body">
                <i class="bi bi-hourglass-split status-icon"></i>
                
                <h2 class="message-title">We're Not Accepting Registrations Right Now</h2>
                
                <p class="message-text">
                    Registration is temporarily paused. It will be resumed shortly. 
                    Keep an eye on the portal for updates. Meanwhile, feel free to explore 
                    our website and check out all the exciting events lined up for maJIStic 2K25!
                </p>
                
                <div class="countdown">
                    <div class="countdown-item">
                        <div class="countdown-number">11-12</div>
                        <div class="countdown-label">April</div>
                    </div>
                    <div class="countdown-item">
                        <div class="countdown-number">2025</div>
                        <div class="countdown-label">Year</div>
                    </div>
                </div>
                
                <a href="../../index.php" class="btn btn-explore">
                    <i class="bi bi-arrow-left-circle me-2"></i>Back to Home Page
                </a>
            </div>
        </div>
    </div>
</body>
</html>
