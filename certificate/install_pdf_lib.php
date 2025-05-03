<?php
/**
 * FPDF Library Installer Helper
 * 
 * This script helps download and install the FPDF library if not available through Composer
 */

// Define paths
$vendorDir = __DIR__ . '/../vendor';
$fpdfDir = $vendorDir . '/fpdf';
$fpdfFile = $fpdfDir . '/fpdf.php';

// Check if FPDF is already installed
if (file_exists($fpdfFile)) {
    echo "FPDF is already installed.\n";
    exit(0);
}

// Create vendor directory if it doesn't exist
if (!is_dir($vendorDir)) {
    if (!mkdir($vendorDir, 0755, true)) {
        die("Failed to create vendor directory: $vendorDir\n");
    }
    echo "Created vendor directory: $vendorDir\n";
}

// Create FPDF directory if it doesn't exist
if (!is_dir($fpdfDir)) {
    if (!mkdir($fpdfDir, 0755, true)) {
        die("Failed to create FPDF directory: $fpdfDir\n");
    }
    echo "Created FPDF directory: $fpdfDir\n";
}

// Download FPDF
$fpdfUrl = 'http://www.fpdf.org/en/dl.php?v=184&f=tgz';
$tempFile = $vendorDir . '/fpdf.tgz';

echo "Downloading FPDF...\n";
if (file_put_contents($tempFile, file_get_contents($fpdfUrl)) === false) {
    die("Failed to download FPDF from $fpdfUrl\n");
}

// Extract FPDF
echo "Extracting FPDF...\n";
$phar = new PharData($tempFile);
$phar->extractTo($vendorDir);

// Copy files from extracted directory to fpdf directory
$extractedDir = $vendorDir . '/fpdf184';
if (is_dir($extractedDir)) {
    foreach (scandir($extractedDir) as $file) {
        if ($file != '.' && $file != '..') {
            copy("$extractedDir/$file", "$fpdfDir/$file");
        }
    }
}

// Clean up
unlink($tempFile);
if (is_dir($extractedDir)) {
    foreach (scandir($extractedDir) as $file) {
        if ($file != '.' && $file != '..') {
            unlink("$extractedDir/$file");
        }
    }
    rmdir($extractedDir);
}

// Verify installation
if (file_exists($fpdfFile)) {
    echo "FPDF installed successfully.\n";
} else {
    echo "Failed to install FPDF.\n";
}
