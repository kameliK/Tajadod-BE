<?php
$servername = "localhost";
$username = "root";
$password = ""; // default for XAMPP
$dbname = "recycle_db"; // This database contains both 'messages' and 'providers' tables

$conn = new mysqli($servername, $username, $password, $dbname);

// Check for DB connection errors
if ($conn->connect_error) {
    error_log("Connection failed: " . $conn->connect_error);
    die("Connection failed: " . $conn->connect_error);
}
?>