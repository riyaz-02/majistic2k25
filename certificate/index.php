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
    <style>
        :root {
            --primary: #2c3e50;
            --secondary: rgb(35, 35, 36);
            --accent: #e74c3c;
            --light: #ecf0f1;
            --white: #ffffff;
            --shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            --transition: all 0.3s ease;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }
        
        body {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            padding: 20px;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            width: 100%;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
        }
        
        .certificate-container {
            background-color: var(--white);
            border-radius: 20px;
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.2);
            overflow: hidden;
            width: 100%;
            max-width: 600px;
            padding: 0;
            margin-top: 50px;
        }
        
        .certificate-header {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            padding: 30px 20px;
            text-align: center;
            color: var(--white);
        }
        
        .certificate-header h1 {
            font-size: 1.8rem;
            margin-bottom: 10px;
        }
        
        .certificate-header p {
            opacity: 0.8;
        }
        
        .logo {
            width: 180px;
            height: auto;
            margin-bottom: 10px;
        }
        
        .certificate-form {
            padding: 30px;
        }
        
        .form-group {
            margin-bottom: 25px;
            position: relative;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: var(--primary);
        }
        
        .form-group input, .form-group select {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 16px;
            transition: var(--transition);
        }
        
        .form-group input:focus, .form-group select:focus {
            outline: none;
            border-color: var(--secondary);
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.2);
        }
        
        .form-group .icon {
            position: absolute;
            right: 15px;
            top: 40px;
            color: var(--primary);
        }
        
        .btn-download {
            display: block;
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: var(--white);
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
            margin-top: 10px;
        }
        
        .btn-download:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }
        
        .error-message {
            background-color: #ffebee;
            color: var(--accent);
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .error-message i {
            font-size: 18px;
        }

        .success-message {
            background-color: #e8f5e9;
            color: #2e7d32;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .success-message i {
            font-size: 18px;
        }
        
        .system-message {
            background-color: #fff3e0;
            color: #e65100;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .system-message i {
            font-size: 18px;
        }
        
        .footer {
            margin-top: 20px;
            text-align: center;
            font-size: 14px;
            color: rgba(255, 255, 255, 0.7);
            padding: 20px 0;
        }
        
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            10%, 30%, 50%, 70%, 90% { transform: translateX(-5px); }
            20%, 40%, 60%, 80% { transform: translateX(5px); }
        }
        
        .shake {
            animation: shake 0.6s;
        }

        .certificate-preview {
            width: 100%;
            padding: 15px;
            background-color: #f9f9f9;
            border-radius: 8px;
            text-align: center;
            margin-bottom: 20px;
        }

        .certificate-preview img {
            max-width: 100%;
            height: auto;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        .preview-note {
            font-size: 12px;
            color: #666;
            margin-top: 8px;
        }
        
        @media (max-width: 768px) {
            .certificate-container {
                max-width: 100%;
                margin-top: 30px;
            }
            
            .certificate-header {
                padding: 20px;
            }
            
            .certificate-form {
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="certificate-container <?php echo $error ? 'shake' : ''; ?>">
            <div class="certificate-header">
                <img src="../images/majisticlogo.png" alt="MaJIStic Logo" class="logo">
                <h1>MaJIStic 2K25 Certificate</h1>
                <p>Download your participation certificate</p>
            </div>
            
            <div class="certificate-form">
                <?php if ($systemMessage): ?>
                <div class="system-message">
                    <i class="fas fa-exclamation-triangle"></i>
                    <?php echo $systemMessage; ?>
                </div>
                <?php endif; ?>
                
                <?php if ($error): ?>
                <div class="error-message">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo $error; ?>
                </div>
                <?php endif; ?>
                
                <?php if ($success): ?>
                <div class="success-message">
                    <i class="fas fa-check-circle"></i>
                    <?php echo $success; ?>
                </div>
                <?php endif; ?>
                
                <form method="POST" action="generate_certificate.php">
                    <div class="form-group">
                        <label for="jis_id">JIS ID</label>
                        <input type="text" id="jis_id" name="jis_id" required placeholder="Enter your JIS ID">
                        <i class="fas fa-id-card icon"></i>
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
                    
                    <button type="submit" class="btn-download">
                        <i class="fas fa-download"></i> Generate Certificate
                    </button>
                </form>
                
                <!-- Debug info for administrators -->
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
    
    <div class="footer">
        &copy; <?php echo date('Y'); ?> maJIStic - All Rights Reserved
    </div>
    
    <script>
        // Add focus effects
        const inputs = document.querySelectorAll('input, select');
        inputs.forEach(input => {
            input.addEventListener('focus', function() {
                this.parentElement.querySelector('.icon').style.color = '#3498db';
            });
            
            input.addEventListener('blur', function() {
                this.parentElement.querySelector('.icon').style.color = '#2c3e50';
            });
        });

        // Preview certificate based on role selection
        const roleSelect = document.getElementById('role');
        const previewContainer = document.getElementById('previewContainer');
        const previewImage = document.getElementById('previewImage');

        roleSelect.addEventListener('change', function() {
            const selectedRole = this.value;
            
            if (selectedRole) {
                // Show preview container
                previewContainer.style.display = 'block';
                
                // Set preview image based on role
                if (selectedRole === 'Participant') {
                    previewImage.src = 'assets/participant_preview.jpg';
                } else if (selectedRole === 'Volunteer') {
                    previewImage.src = 'assets/volunteer_preview.jpg';
                } else if (selectedRole === 'Crew Member') {
                    previewImage.src = 'assets/crew_preview.jpg';
                }
            } else {
                previewContainer.style.display = 'none';
            }
        });
    </script>
</body>
</html>
