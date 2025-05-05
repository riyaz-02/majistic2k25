<?php
// Configuration file for certificate generation

// Define the base directory and URLs
define('BASE_DIR', dirname(__FILE__));

// Define templates and assets directories
define('CERTIFICATE_TEMPLATES_DIR', BASE_DIR . '/templates');
define('CERTIFICATE_ASSETS_DIR', BASE_DIR . '/assets');
define('TEMP_DIR', BASE_DIR . '/temp');

// Create required directories if they don't exist
foreach ([CERTIFICATE_TEMPLATES_DIR, CERTIFICATE_ASSETS_DIR, TEMP_DIR] as $dir) {
    if (!file_exists($dir)) {
        @mkdir($dir, 0755, true);
    }
}

// Define paths to certificate templates
define('PARTICIPANT_TEMPLATE', CERTIFICATE_TEMPLATES_DIR . '/participant_template.jpg');
define('VOLUNTEER_TEMPLATE', CERTIFICATE_TEMPLATES_DIR . '/volunteer_template.jpg');
define('CREW_TEMPLATE', CERTIFICATE_TEMPLATES_DIR . '/crew_template.jpg');
define('FALLBACK_TEMPLATE', CERTIFICATE_TEMPLATES_DIR . '/default_template.jpg');

// Certificate text settings
$certificateSettings = [
    'participant' => [
        'title' => 'Certificate of Participation',
        'namePosition' => ['x' => 'center', 'y' => 140],
        'fontSize' => 30
    ],
    'volunteer' => [
        'title' => 'Certificate of Appreciation',
        'namePosition' => ['x' => 'center', 'y' => 140],
        'fontSize' => 30
    ],
    'crew' => [
        'title' => 'Certificate of Excellence',
        'namePosition' => ['x' => 'center', 'y' => 140],
        'fontSize' => 30
    ]
];

// Custom error handler function
function certificateErrorHandler($errno, $errstr, $errfile, $errline) {
    error_log("Certificate Error: [$errno] $errstr - $errfile:$errline");
    return true;
}

// Set the custom error handler
set_error_handler("certificateErrorHandler");

// Detect if we're in a production environment
$isProduction = (stripos($_SERVER['SERVER_NAME'] ?? '', 'majistic.org') !== false || 
                stripos($_SERVER['HTTP_HOST'] ?? '', 'majistic.org') !== false || 
                stripos($_SERVER['SERVER_NAME'] ?? '', 'hostinger') !== false);

// Specific error handling for production vs development
if ($isProduction) {
    // In production: hide errors from users but log them
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
    error_reporting(E_ALL);
    
    // Create a writable error log file
    $errorLogFile = __DIR__ . '/error_log.txt';
    if (!file_exists($errorLogFile)) {
        @touch($errorLogFile);
        @chmod($errorLogFile, 0666);
    }
    
    if (is_writable($errorLogFile)) {
        ini_set('error_log', $errorLogFile);
    }
} else {
    // In development: show all errors
    ini_set('display_errors', 1);
    error_reporting(E_ALL);
}

// Certificate generation configuration
$config = [
    // Whether to enforce payment status check
    'enforce_payment' => true,
    
    // Certificate templates directory
    'templates_dir' => __DIR__ . '/templates',
    
    // Temporary storage directory
    'temp_dir' => __DIR__ . '/temp',
    
    // Default certificate sizing
    'default_font_size' => 34,
    'default_font_family' => 'Times',
    'default_font_style' => 'BI', // Bold Italic
    
    // Certificate text color (RGB)
    'text_color' => [0, 51, 102], // Dark Navy Blue
    
    // Environment setting
    'is_production' => $isProduction
];

return $config;
?>
