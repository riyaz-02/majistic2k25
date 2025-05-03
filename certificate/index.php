<?php
require_once __DIR__ . '/../includes/db_config.php';
require_once 'config.php';

// Error and success messages
$error = '';
$success = '';
$systemMessage = '';

// Check for success or error messages
if (isset($_GET['status']) && $_GET['status'] == 'success') {
    $success = "Certificate generated successfully!";
}

if (isset($_GET['error'])) {
    $error = htmlspecialchars($_GET['error']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MaJIStic 2K25 - Certificate Download</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">
    <?php include '../includes/links.php'; ?>
    <link rel="stylesheet" href="../style.css">
    <link rel="stylesheet" href="../css/check_status.css">
    <style>
        body {
            background-image: url('../images/pageback.png');
            background-size: cover;
            background-attachment: fixed;
            min-height: 100vh;
            font-family: 'Poppins', sans-serif;
            color: #ffffff;
        }

        /* Specific overrides for certificate page */
        .page-title {
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
            max-width: 700px;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.05);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
            width: 100%;
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
            box-sizing: border-box;
        }

        input[type="text"]:focus {
            outline: none;
            border-color: #3498db;
            box-shadow: 0 0 15px rgba(52, 152, 219, 0.4), inset 0 2px 5px rgba(0,0,0,0.2);
        }

        /* Custom animation for the card */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .form-container {
            animation: fadeIn 0.8s ease;
        }
    </style>
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="status-container">
        <div class="status-card">
            <div class="card-header">
                <img src="../images/majisticlogo.png" alt="MaJIStic Logo" class="logo">
                
                <div class="event-completion-banner">
                    <h3 class="completion-title">Your Achievement Awaits!</h3>
                    <p>Congratulations on being part of maJIStic 2k25! Download your personalized certificate and showcase your talent & participation in this unforgettable cultural extravaganza.</p>
                </div>
            </div>
            
            <div class="card-body">
                <h2 class="page-title">Certificate Download Portal</h2>

                <?php if ($systemMessage): ?>
                <div class="message-box system">
                    <i class="fas fa-exclamation-triangle"></i>
                    <?php echo $systemMessage; ?>
                </div>
                <?php endif; ?>
                
                <?php if ($error): ?>
                <div class="message-box error">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo $error; ?>
                </div>
                <?php endif; ?>
                
                <?php if ($success): ?>
                <div class="message-box success">
                    <i class="fas fa-check-circle"></i>
                    <?php echo $success; ?>
                </div>
                <?php endif; ?>
                
                <div class="form-container">
                    <form method="POST" action="/certificate/generate_certificate.php" id="certificateForm">
                        <div class="form-group">
                            <label for="jis_id">JIS ID</label>
                            <input type="text" id="jis_id" name="jis_id" required placeholder="Enter your JIS ID">
                        </div>
                        
                        <div class="form-group">
                            <label for="student_name">Your Name</label>
                            <input type="text" id="student_name" name="student_name" required placeholder="Enter your full name">
                        </div>
                        
                        <!-- Admin debug mode checkbox -->
                        <?php if (isset($_GET['admin'])): ?>
                        <div class="form-group">
                            <label>
                                <input type="checkbox" name="debug_mode" value="1" checked> 
                                Enable Debug Mode
                            </label>
                            <small style="display: block; margin-top: 5px; color: #666;">
                                Debug information will be printed to browser console (F12)
                            </small>
                        </div>
                        
                        <!-- Visual Debug Panel -->
                        <div class="form-group">
                            <label>
                                <input type="checkbox" name="visual_debug" value="1"> 
                                Enable Visual Debug Panel
                            </label>
                            <small style="display: block; margin-top: 5px; color: #666;">
                                Shows a visual representation of the certificate generation process
                            </small>
                        </div>
                        <?php endif; ?>
                        
                        <div style="text-align: center;">
                            <button type="submit" class="btn btn-primary" id="downloadBtn" style="background: linear-gradient(135deg,rgb(146, 46, 204),rgb(43, 22, 136)); color: white;">
                                <i class="fas fa-download"></i> Download Certificate
                            </button>
                        </div>
                    </form>
                </div>
                
                <!-- Important notes for users -->
                <div class="note" style="background-color: rgba(52, 152, 219, 0.15); border-left: 4px solid #3498db; padding: 15px; margin: 20px 0; text-align: left; border-radius: 4px;">
                    <p><strong>Note:</strong> Certificate generation requires exact matching of your JIS ID and registered name.</p>
                    <p>The certificate will be generated only for registered participants who attended the event.</p>
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
                                For certificate related support, contact our Support Team
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
                
                <!-- Admin debug info section -->
                <?php if (isset($_GET['admin']) && isset($_GET['debug']) && $_GET['debug'] == '1'): ?>
                <div class="debug-section" style="margin-top: 20px; padding: 10px; background: #f0f0f0; border-radius: 5px;">
                    <h4>Debug Information</h4>
                    <p><small>Check browser console (F12) for detailed debugging output</small></p>
                    
                    <!-- Certificate Generator Status -->
                    <div class="debug-panel" style="margin-top: 15px; border: 1px solid #ddd; border-radius: 4px; overflow: hidden;">
                        <div style="background: #e9e9e9; padding: 8px 12px; border-bottom: 1px solid #ddd;">
                            <h5 style="margin: 0; font-size: 14px;">Certificate Generator Status</h5>
                        </div>
                        <div style="padding: 12px;">
                            <ul style="list-style-type: none; padding: 0; margin: 0;">
                                <?php
                                $fpdfPath = __DIR__ . '/../vendor/fpdf/fpdf.php';
                                $fpdiPath = __DIR__ . '/../vendor/fpdi/src/autoload.php';
                                $templatesDir = __DIR__ . '/templates';
                                $tempDir = __DIR__ . '/temp';
                                
                                $status = [
                                    'FPDF Library' => [
                                        'status' => file_exists($fpdfPath) ? 'OK' : 'MISSING',
                                        'path' => $fpdfPath,
                                        'color' => file_exists($fpdfPath) ? '#4caf50' : '#f44336'
                                    ],
                                    'FPDI Library' => [
                                        'status' => file_exists($fpdiPath) ? 'OK' : 'MISSING',
                                        'path' => $fpdiPath,
                                        'color' => file_exists($fpdiPath) ? '#4caf50' : '#f44336'
                                    ],
                                    'Templates Directory' => [
                                        'status' => is_dir($templatesDir) ? 'OK' : 'MISSING',
                                        'path' => $templatesDir,
                                        'color' => is_dir($templatesDir) ? '#4caf50' : '#f44336'
                                    ],
                                    'Templates' => [
                                        'status' => is_dir($templatesDir) ? (count(glob("$templatesDir/*_template.pdf")) > 0 ? 'OK' : 'NO FILES') : 'N/A',
                                        'path' => $templatesDir,
                                        'color' => (is_dir($templatesDir) && count(glob("$templatesDir/*_template.pdf")) > 0) ? '#4caf50' : '#ff9800'
                                    ],
                                    'Temp Directory' => [
                                        'status' => is_dir($tempDir) ? (is_writable($tempDir) ? 'OK' : 'NOT WRITABLE') : 'MISSING',
                                        'path' => $tempDir,
                                        'color' => (is_dir($tempDir) && is_writable($tempDir)) ? '#4caf50' : '#f44336'
                                    ],
                                    'Fallback Mode' => [
                                        'status' => (!file_exists($fpdfPath) || !file_exists($fpdiPath)) ? 'ACTIVE' : 'INACTIVE',
                                        'info' => (!file_exists($fpdfPath) || !file_exists($fpdiPath)) ? 'Using template copy method' : 'Using PDF text overlay',
                                        'color' => (!file_exists($fpdfPath) || !file_exists($fpdiPath)) ? '#ff9800' : '#4caf50'
                                    ]
                                ];
                                
                                foreach ($status as $key => $item):
                                ?>
                                <li style="padding: 8px 0; border-bottom: 1px solid #eee; display: flex; justify-content: space-between;">
                                    <span><?php echo $key; ?>:</span>
                                    <span style="color: <?php echo $item['color']; ?>; font-weight: 500;">
                                        <?php echo $item['status']; ?>
                                        <?php if (isset($item['info'])): ?>
                                            <small style="display: block; font-weight: normal; font-size: 11px;">
                                                <?php echo $item['info']; ?>
                                            </small>
                                        <?php endif; ?>
                                    </span>
                                </li>
                                <?php endforeach; ?>
                            </ul>
                            
                            <!-- Template Files List -->
                            <?php if (is_dir($templatesDir)): ?>
                            <div style="margin-top: 15px; padding-top: 10px; border-top: 1px solid #ddd;">
                                <h5 style="margin: 0 0 10px; font-size: 14px;">Available Templates</h5>
                                <ul style="margin: 0; padding: 0 0 0 20px; font-size: 13px;">
                                <?php
                                $templateFiles = glob("$templatesDir/*.pdf");
                                if (!empty($templateFiles)):
                                    foreach ($templateFiles as $file):
                                        $fileName = basename($file);
                                ?>
                                    <li><?php echo $fileName; ?></li>
                                <?php
                                    endforeach;
                                else:
                                ?>
                                    <li style="color: #f44336;">No PDF template files found</li>
                                <?php endif; ?>
                                </ul>
                            </div>
                            <?php endif; ?>
                            
                            <!-- Setup Instructions -->
                            <?php if (!file_exists($fpdfPath) || !file_exists($fpdiPath)): ?>
                            <div style="margin-top: 15px; padding: 10px; background: #fff3e0; border-radius: 4px;">
                                <h5 style="margin: 0 0 10px; font-size: 14px;">Setup Required</h5>
                                <p style="margin: 0; font-size: 13px; line-height: 1.4;">
                                    PDF libraries are missing. For optimal certificate generation, please install:
                                </p>
                                <code style="display: block; margin: 8px 0; padding: 8px; background: #f5f5f5; border-radius: 3px; font-size: 12px;">
                                    composer require setasign/fpdf<br>
                                    composer require setasign/fpdi
                                </code>
                                <p style="margin: 0; font-size: 13px; line-height: 1.4;">
                                    Until then, certificates will be generated using the fallback copy method.
                                </p>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Console Logger -->
                    <div class="console-output" style="margin-top: 15px; border: 1px solid #ddd; border-radius: 4px; overflow: hidden; height: 200px; position: relative;">
                        <div style="background: #272822; color: #f8f8f2; padding: 8px 12px; border-bottom: 1px solid #000; display: flex; justify-content: space-between; align-items: center;">
                            <h5 style="margin: 0; font-size: 14px; color: #f8f8f2;">Certificate Generator Console</h5>
                            <button id="clearConsole" style="background: none; border: none; color: #f8f8f2; cursor: pointer; font-size: 12px;">Clear</button>
                        </div>
                        <div id="consoleOutput" style="padding: 10px; background: #1e1f1c; color: #f8f8f2; font-family: monospace; font-size: 12px; height: 152px; overflow-y: auto;">
                            <div class="log-entry"><span style="color: #75715e;">// Console initialized</span></div>
                        </div>
                    </div>
                    
                    <script>
                        // Initialize Console Logger
                        console.group('Certificate Generator Debug');
                        console.info('Debug mode active - data will be logged here during certificate generation');
                        console.info('Time: <?php echo date("Y-m-d H:i:s"); ?>');
                        
                        // Add custom console logger
                        (function() {
                            const consoleOutput = document.getElementById('consoleOutput');
                            
                            // Store original console methods
                            const originalConsole = {
                                log: console.log,
                                info: console.info,
                                warn: console.warn,
                                error: console.error
                            };
                            
                            // Override console methods
                            console.log = function() {
                                originalConsole.log.apply(console, arguments);
                                logToElement('log', arguments[0], arguments[1] || '');
                            };
                            
                            console.info = function() {
                                originalConsole.info.apply(console, arguments);
                                logToElement('info', arguments[0], arguments[1] || '');
                            };
                            
                            console.warn = function() {
                                originalConsole.warn.apply(console, arguments);
                                logToElement('warn', arguments[0], arguments[1] || '');
                            };
                            
                            console.error = function() {
                                originalConsole.error.apply(console, arguments);
                                logToElement('error', arguments[0], arguments[1] || '');
                            };
                            
                            function logToElement(type, msg, data) {
                                const entry = document.createElement('div');
                                entry.className = 'log-entry';
                                
                                let color = '#f8f8f2'; // default color
                                let prefix = '';
                                
                                switch(type) {
                                    case 'info':
                                        color = '#66d9ef';
                                        prefix = '[INFO] ';
                                        break;
                                    case 'warn':
                                        color = '#e6db74';
                                        prefix = '[WARN] ';
                                        break;
                                    case 'error':
                                        color = '#f92672';
                                        prefix = '[ERROR] ';
                                        break;
                                }
                                
                                entry.innerHTML = `<span style="color: ${color}">${prefix}${msg}</span>`;
                                
                                if (data && typeof data === 'object') {
                                    entry.innerHTML += ` <span style="color: #75715e">${JSON.stringify(data)}</span>`;
                                } else if (data) {
                                    entry.innerHTML += ` <span style="color: #a6e22e">${data}</span>`;
                                }
                                
                                consoleOutput.appendChild(entry);
                                consoleOutput.scrollTop = consoleOutput.scrollHeight;
                            }
                            
                            // Clear console button
                            document.getElementById('clearConsole').addEventListener('click', function() {
                                consoleOutput.innerHTML = '<div class="log-entry"><span style="color: #75715e;">// Console cleared</span></div>';
                            });
                        })();
                        
                        // Log system status
                        console.info('Certificate system status', {
                            'FPDF': '<?php echo file_exists($fpdfPath) ? "OK" : "MISSING" ?>',
                            'FPDI': '<?php echo file_exists($fpdiPath) ? "OK" : "MISSING" ?>',
                            'Mode': '<?php echo (!file_exists($fpdfPath) || !file_exists($fpdiPath)) ? "Fallback (Copy)" : "Full (Text Overlay)" ?>'
                        });
                    </script>
                </div>
                <?php endif; ?>
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

            // Add focus effects to form inputs
            const inputs = document.querySelectorAll('input[type="text"]');
            inputs.forEach(input => {
                input.addEventListener('focus', function() {
                    this.style.borderColor = '#3498db';
                    this.style.boxShadow = '0 0 15px rgba(52, 152, 219, 0.4), inset 0 2px 5px rgba(0,0,0,0.2)';
                });
                
                input.addEventListener('blur', function() {
                    this.style.borderColor = 'rgba(255, 255, 255, 0.2)';
                    this.style.boxShadow = 'inset 0 2px 5px rgba(0,0,0,0.2)';
                });
            });
            
            // Certificate form submission and button loading state
            const certificateForm = document.getElementById('certificateForm');
            const downloadBtn = document.getElementById('downloadBtn');
            let originalBtnText = downloadBtn.innerHTML;
            
            certificateForm.addEventListener('submit', function(e) {
                // Validate form
                const jisId = document.getElementById('jis_id').value.trim();
                const studentName = document.getElementById('student_name').value.trim();
                
                if (!jisId || !studentName) {
                    // Don't proceed if validation fails
                    return;
                }
                
                // Disable button and show loading state
                downloadBtn.disabled = true;
                downloadBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Loading...';
                downloadBtn.style.opacity = '0.7';
                
                // After 2 seconds, change to "Generating..."
                setTimeout(() => {
                    downloadBtn.innerHTML = '<i class="fas fa-cog fa-spin"></i> Generating...';
                }, 2000);
                
                // After 4 seconds, change to "Downloading...(3s)"
                setTimeout(() => {
                    downloadBtn.innerHTML = '<i class="fas fa-file-download"></i> Downloading...(3s)';
                }, 4000);
                
                // Set timeout to revert button state if the form submission takes too long
                setTimeout(() => {
                    if (downloadBtn.disabled) {
                        downloadBtn.disabled = false;
                        downloadBtn.innerHTML = originalBtnText;
                        downloadBtn.style.opacity = '1';
                    }
                }, 5000); // 5 seconds timeout
            });
            
            // Check for URL error parameters and display appropriate messages
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.has('error')) {
                // Scroll to the error message
                const errorElement = document.querySelector('.message-box.error');
                if (errorElement) {
                    errorElement.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    
                    // Add highlight animation
                    errorElement.style.animation = 'none';
                    setTimeout(() => {
                        errorElement.style.animation = 'highlight 1.5s ease';
                    }, 10);
                }
            }
        });
    </script>

    <style>
        /* Button states */
        button:disabled {
            cursor: not-allowed;
        }
        
        /* Error message highlight animation */
        @keyframes highlight {
            0% { transform: translateY(0); }
            10% { transform: translateY(-5px); }
            20% { transform: translateY(5px); }
            30% { transform: translateY(0); }
            100% { transform: translateY(0); }
        }
        
        .message-box {
            padding: 18px;
            margin: 25px 0;
            border-radius: 10px;
            text-align: center;
            font-weight: 500;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.15);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            position: relative;  /* Add position relative */
            z-index: 1;          /* Lower z-index than card-header */
            top: -20px;
        }

        .card-header {
            z-index: 2;         /* Higher z-index to ensure it's above message boxes */
            position: relative;  /* Ensure z-index works */
        }

        .message-box.error {
            background-color: rgba(231, 76, 60, 0.2);
            border-left: 4px solid #e74c3c;
            color: #e74c3c;
        }
        
        .message-box.success {
            background-color: rgba(46, 204, 113, 0.2);
            border-left: 4px solid #2ecc71;
            color: #2ecc71;
        }
        
        .message-box.system {
            background-color: rgba(241, 196, 15, 0.2);
            border-left: 4px solid #f1c40f;
            color: #f1c40f;
        }
        
        /* Icons in message boxes */
        .message-box i {
            font-size: 20px;
        }
        
        /* Add additional styling for better visibility */
        .card-body {
            position: relative;  /* Establish a stacking context */
            z-index: 1;          /* Lower than card-header */
        }
        
        /* Ensure form is above message boxes if they overlap */
        .form-container {
            position: relative;
            z-index: 2;
        }

        /* Improved message box styles with better responsiveness */
        .message-box {
            padding: 18px;
            margin: 25px auto;
            max-width: 90%;
            width: 100%;
            border-radius: 10px;
            text-align: center;
            font-weight: 500;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.15);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            position: relative;
            z-index: 5;
            top: 0;
        }

        .card-header {
            z-index: 10;
            position: relative;
        }

        .message-box.error {
            background-color: rgba(231, 76, 60, 0.2);
            border-left: 4px solid #e74c3c;
            color: #e74c3c;
        }
        
        .message-box.success {
            background-color: rgba(46, 204, 113, 0.2);
            border-left: 4px solid #2ecc71;
            color: #2ecc71;
        }
        
        .message-box.system {
            background-color: rgba(241, 196, 15, 0.2);
            border-left: 4px solid #f1c40f;
            color: #f1c40f;
        }
        
        /* Icons in message boxes */
        .message-box i {
            font-size: 20px;
            flex-shrink: 0;
        }
        
        /* Add additional styling for better visibility */
        .card-body {
            position: relative;
            z-index: 1;
            padding-top: 30px;
        }
        
        /* Ensure form is above message boxes */
        .form-container {
            position: relative;
            z-index: 2;
        }
        
        /* Responsive styles for message boxes */
        @media (max-width: 768px) {
            .message-box {
                padding: 15px;
                margin: 20px auto;
                font-size: 15px;
            }
            
            .message-box i {
                font-size: 18px;
            }
        }
        
        @media (max-width: 480px) {
            .message-box {
                padding: 12px 15px;
                margin: 15px auto;
                font-size: 14px;
                max-width: 95%;
                flex-direction: column;
                gap: 8px;
            }
            
            .message-box i {
                font-size: 22px;
                margin-bottom: 5px;
            }
        }
        
        @media (max-width: 360px) {
            .message-box {
                padding: 10px;
                margin: 10px auto;
                font-size: 13px;
            }
        }
    </style>
</body>
</html>
