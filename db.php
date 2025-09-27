<?php
$host = "localhost";   // Database host (usually localhost)
$user = "root";        // Database username (default for XAMPP is root)
$pass = "";            // Database password (default for XAMPP is empty)
$dbname = "login"; // Your database name

// Create connection
$conn = new mysqli($host, $user, $pass, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
