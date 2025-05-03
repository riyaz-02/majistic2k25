<?php
require_once __DIR__ . '/../includes/db_config.php';

// Capture token if available
$token = isset($_GET['token']) ? $_GET['token'] : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Certificate Verification - maJIStic 2k25</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap">
    <?php include '../includes/links.php'; ?>
    <link rel="stylesheet" href="../style.css">
    <link rel="stylesheet" href="../css/check_status.css">
    <style>
        body {
            background-image: url('../images/pageback.png');
            background-size: cover;
            background-attachment: fixed;
            min-height: 100vh;
            font-family: 'Roboto', sans-serif;
            color: #ffffff;
        }
        
        .coming-soon-container {
            background: rgba(0, 0, 0, 0.6);
            border-radius: 15px;
            padding: 40px 30px;
            margin: 30px auto;
            max-width: 800px;
            text-align: center;
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.3);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.05);
            animation: fadeIn 0.8s ease;
        }
        
        .coming-soon-icon {
            font-size: 60px;
            color: #3498db;
            margin-bottom: 20px;
            display: inline-block;
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0% { transform: scale(1); opacity: 1; }
            50% { transform: scale(1.1); opacity: 0.8; }
            100% { transform: scale(1); opacity: 1; }
        }
        
        .coming-soon-title {
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 15px;
            color: white;
        }
        
        .coming-soon-message {
            font-size: 17px;
            line-height: 1.6;
            margin-bottom: 30px;
            color: rgba(255, 255, 255, 0.9);
        }
        
        .dev-progress {
            width: 100%;
            height: 10px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 10px;
            margin: 20px auto;
            position: relative;
            overflow: hidden;
        }
        
        .dev-progress-bar {
            height: 100%;
            background: linear-gradient(90deg, #3498db, #9b59b6);
            width: 75%;
            border-radius: 10px;
            animation: progressAnimation 2s ease-in-out;
        }
        
        @keyframes progressAnimation {
            from { width: 0; }
            to { width: 75%; }
        }
        
        .token-info {
            background: rgba(52, 152, 219, 0.1);
            border-radius: 10px;
            padding: 15px;
            margin-top: 20px;
            text-align: left;
            border-left: 4px solid #3498db;
            word-break: break-all;
        }
        
        @media (max-width: 768px) {
            .coming-soon-container {
                padding: 30px 20px;
                margin: 20px auto;
            }
            
            .coming-soon-icon {
                font-size: 50px;
            }
            
            .coming-soon-title {
                font-size: 24px;
            }
            
            .coming-soon-message {
                font-size: 15px;
            }
        }
    </style>
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="status-container">
        <div class="status-card">
            <div class="card-header">
                <img src="../images/majisticlogo.png" alt="maJIStic Logo" class="logo">
                
                <div class="event-completion-banner">
                    <h3 class="completion-title">Certificate Verification System</h3>
                    <p>Verify the authenticity of maJIStic 2k25 certificates</p>
                </div>
            </div>
            
            <div class="card-body">
                <h2 class="page-title">Certificate Verification</h2>
                
                <div class="coming-soon-container">
                    <i class="fas fa-cogs coming-soon-icon"></i>
                    <h3 class="coming-soon-title">Verification System Under Development</h3>
                    <p class="coming-soon-message">We're currently building this certificate verification portal to ensure the authenticity of all maJIStic 2k25 certificates. The system will be available soon.</p>
                    
                    <div class="dev-progress">
                        <div class="dev-progress-bar"></div>
                    </div>
                    <p style="margin-top: 10px; color: #3498db; font-weight: 500;">75% Complete</p>
                    
                    <?php if (!empty($token)): ?>
                    <div class="token-info">
                        <p><strong>Your verification token has been received</strong></p>
                        <p>Token ID: <?php echo htmlspecialchars(substr($token, 0, 20) . '...'); ?></p>
                        <p>This token will be verified when the system is online.</p>
                    </div>
                    <?php endif; ?>
                    
                    <div class="note" style="background-color: rgba(52, 152, 219, 0.15); border-left: 4px solid #3498db; padding: 15px; margin: 30px 0; text-align: left; border-radius: 4px;">
                        <p><strong>Need an immediate certificate verification?</strong></p>
                        <p>Please contact our support team with your certificate details for manual verification until the automated system is ready.</p>
                    </div>
                </div>
                
                <!-- Support section -->
                <div class="support-container">
                    <div class="contact-section">
                        <h2 class="contact-section-title">Need Help?</h2>
                        
                        <div class="contact-tabs">
                            <button class="contact-tab active" data-target="tech-team">Tech Team</button>
                            <button class="contact-tab" data-target="support-team">Support Team</button>
                        </div>
                        
                        <div class="contact-panel active" id="tech-team">
                            <p class="contact-description">
                                In case of any technical issues, feel free to contact our Tech Team
                            </p>
                            <div class="contact-cards">
                                <div class="contact-card">
                                    <div class="icon">
                                        <i class="fas fa-user-cog"></i>
                                    </div>
                                    <div class="info">
                                        <h4>Priyanshu Nayan</h4>
                                        <a href="tel:+917004706722">+91 7004706722</a>
                                    </div>
                                </div>
                                <div class="contact-card">
                                    <div class="icon">
                                        <i class="fas fa-user-cog"></i>
                                    </div>
                                    <div class="info">
                                        <h4>Sk Riyaz</h4>
                                        <a href="tel:+917029621489">+91 7029621489</a>
                                    </div>
                                </div>
                                <div class="contact-card">
                                    <div class="icon">
                                        <i class="fas fa-user-cog"></i>
                                    </div>
                                    <div class="info">
                                        <h4>Ronit Pal</h4>
                                        <a href="tel:+917501005155">+91 7501005155</a>
                                    </div>
                                </div>
                                <div class="contact-card">
                                    <div class="icon">
                                        <i class="fas fa-user-cog"></i>
                                    </div>
                                    <div class="info">
                                        <h4>Mohit Kumar</h4>
                                        <a href="tel:+918016804158">+91 8016804158</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="contact-panel" id="support-team">
                            <p class="contact-description">
                                For certificate verification support, contact our Support Team
                            </p>
                            <div class="contact-cards">
                                <div class="contact-card">
                                    <div class="icon">
                                        <i class="fas fa-headset"></i>
                                    </div>
                                    <div class="info">
                                        <h4>Dr. Madhura Chakraborty</h4>
                                        <a href="tel:+917980979789">+91 7980979789</a>
                                    </div>
                                </div>
                                <div class="contact-card">
                                    <div class="icon">
                                        <i class="fas fa-headset"></i>
                                    </div>
                                    <div class="info">
                                        <h4>Dr. Proloy Ghosh</h4>
                                        <a href="tel:+917980532913">+91 7980532913</a>
                                    </div>
                                </div>
                                <div class="contact-card">
                                    <div class="icon">
                                        <i class="fas fa-envelope"></i>
                                    </div>
                                    <div class="info">
                                        <h4>Email Support</h4>
                                        <a href="mailto:majistic@jiscollege.ac.in">majistic@jiscollege.ac.in</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Return to certificate download -->
                <div style="text-align: center; margin-top: 30px;">
                    <a href="/certificate/" class="btn btn-primary" style="background: linear-gradient(135deg,rgb(146, 46, 204),rgb(43, 22, 136)); color: white;">
                        <i class="fas fa-arrow-left"></i> Back to Certificate Download
                    </a>
                </div>
                
            </div>
        </div>
    </div>
    
    <?php include '../includes/footer.php'; ?>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Support team tabs
            const contactTabs = document.querySelectorAll('.contact-tab');
            const contactPanels = document.querySelectorAll('.contact-panel');
            
            contactTabs.forEach(tab => {
                tab.addEventListener('click', function() {
                    const target = this.getAttribute('data-target');
                    
                    // Remove active class from all tabs and panels
                    contactTabs.forEach(t => t.classList.remove('active'));
                    contactPanels.forEach(p => p.classList.remove('active'));
                    
                    // Add active class to clicked tab and its panel
                    this.classList.add('active');
                    document.getElementById(target).classList.add('active');
                });
            });
            
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
