<?php
// Add error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

echo "<h1>Session Test Page</h1>";
echo "<p>This is a test page to debug session issues</p>";

echo "<h2>Current Session Data:</h2>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

echo "<h2>Server Information:</h2>";
echo "<pre>";
print_r($_SERVER);
echo "</pre>";

echo "<p><a href='index.php'>Try to access Dashboard</a></p>";
echo "<p><a href='../../login.php'>Go to Login Page</a></p>";
?>
