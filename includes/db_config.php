<?php
$servername = "localhost";
$username = "root"; // Replace with your MySQL username
$password = ""; // Replace with your MySQL password
$dbname = "majistic2k25";


// Create a connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Set the time zone to IST
$conn->query("SET time_zone = '+05:30';");

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
