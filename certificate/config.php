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

// Certificate generation configuration

// Set certificate generation options
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
    'text_color' => [0, 51, 102] // Dark Navy Blue
];

return $config;
?>
