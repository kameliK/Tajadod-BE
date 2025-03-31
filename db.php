<?php
// Database credentials
$host = "localhost";
$username = "root";
$password = "";
$database = "recycle_site";

// Create a connection to the database
$conn = new mysqli($host, $username, $password, $database);

// Check for connection errors
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}
?>
