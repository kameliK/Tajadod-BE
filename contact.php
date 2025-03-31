<?php
// Show detailed errors for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database connection
$host = "localhost";
$user = "root"; // Default XAMPP MySQL username
$pass = ""; // Leave blank if no password
$dbname = "recycle_db";

// Create connection
$conn = new mysqli($host, $user, $pass, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Initialize response array
$response = array();

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!isset($_POST['name'], $_POST['email'], $_POST['phone'], $_POST['subject'], $_POST['message'])) {
        $response['message'] = "Missing form fields!";
        echo json_encode($response);
        exit;
    }

    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $subject = $_POST['subject'];
    $message = $_POST['message'];

    // Insert data into database
    $sql = "INSERT INTO messages (name, email, phone, subject, message) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        $response['message'] = "SQL Error: " . $conn->error;
        echo json_encode($response);
        exit;
    }

    // Bind parameters to the prepared statement
    $stmt->bind_param("sssss", $name, $email, $phone, $subject, $message);

    if ($stmt->execute()) {
        // If successful, send success message
        $response['message'] = 'تم إرسال الرسالة بنجاح!';
    } else {
        // If failed, send error message
        $response['message'] = 'حدث خطأ يرجى المحاولة لاحقاً: ' . $conn->error;
    }

    $stmt->close();
}

$conn->close();

// Send the response as JSON
echo json_encode($response);
?>
