<?php
/**
 * Database configuration helper
 * 
 * This file provides a robust way to locate and load the database configuration
 * that works in both local development and production environments.
 */

/**
 * Load database configuration
 * 
 * @return bool True if database config was loaded successfully, false otherwise
 */
function load_db_config() {
    // Try different possible paths for the database config
    $possible_paths = [
        __DIR__ . '/db_config.php',  // Direct path in includes folder
        dirname(__DIR__) . '/includes/db_config.php',  // From parent directory
        $_SERVER['DOCUMENT_ROOT'] . '/includes/db_config.php',  // From document root
        realpath(__DIR__ . '/db_config.php') // Using realpath
    ];

    $db_loaded = false;

    foreach ($possible_paths as $path) {
        if (file_exists($path)) {
            require_once $path;
            $db_loaded = true;
            break;
        }
    }

    return $db_loaded;
}

/**
 * Get details about database connection attempts
 * 
 * @return array Array containing tried paths and status
 */
function get_db_connection_details() {
    // Try different possible paths for the database config
    $possible_paths = [
        __DIR__ . '/db_config.php',  // Direct path in includes folder
        dirname(__DIR__) . '/includes/db_config.php',  // From parent directory
        $_SERVER['DOCUMENT_ROOT'] . '/includes/db_config.php',  // From document root
        realpath(__DIR__ . '/db_config.php') // Using realpath
    ];

    $tried_paths = [];
    $found_path = null;

    foreach ($possible_paths as $path) {
        $tried_paths[] = $path;
        if (file_exists($path)) {
            $found_path = $path;
            break;
        }
    }

    return [
        'tried_paths' => $tried_paths,
        'found_path' => $found_path,
        'server_info' => [
            'document_root' => $_SERVER['DOCUMENT_ROOT'],
            'script_filename' => $_SERVER['SCRIPT_FILENAME'],
            'PHP_SELF' => $_SERVER['PHP_SELF']
        ]
    ];
}
?>
