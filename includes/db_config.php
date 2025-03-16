<?php
// Database connection parameters
// $servername = "localhost";
// $username = "root"; // Replace with your MySQL username
// $password = ""; // Replace with your MySQL password
// $dbname = "majistic2k25";

// Database connection parameters
$servername = "localhost";
$username = "u901957751_majistic2k25"; // Replace with your MySQL username
$password = "maJIStic@2k25"; // Replace with your MySQL password
$dbname = "u901957751_majistic2k25";


// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);


// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set character set to utf8mb4
$conn->set_charset("utf8mb4");

// Optional: Configure MySQL settings
$conn->query("SET SESSION sql_mode = ''");
$conn->query("SET SESSION time_zone = '+05:30'"); // Indian time zone
?>
