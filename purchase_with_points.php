<?php
$host = "localhost";
$username = "root";
$password = "";
$database = "recycle_db";

// Connect to the database
$conn = new mysqli($host, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");

// Get the form data
$first_name = trim($_POST['first_name']);
$last_name = trim($_POST['last_name']);
$item = $_POST['item'];
$required_points = (float) $_POST['points'];

// Check if the user exists
$sql = "SELECT points FROM providers WHERE LOWER(first_name) = LOWER(?) AND LOWER(last_name) = LOWER(?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $first_name, $last_name);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $current_points = (float) $row['points'];

    if ($current_points >= $required_points) {
        $new_points = $current_points - $required_points;

        // Update the user's points
        $update_sql = "UPDATE providers SET points = ? WHERE LOWER(first_name) = LOWER(?) AND LOWER(last_name) = LOWER(?)";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param("dss", $new_points, $first_name, $last_name);
        $update_stmt->execute();

        // Redirect to the store with success message
        echo "<script>alert('تمت العملية بنجاح! تم خصم النقاط من حسابك.'); window.location.href='Store.html';</script>";
        exit; // Stop further execution
    } else {
        // Redirect back with error message
        $error = urlencode("ليس لديك نقاط كافية!");
        header("Location: PointsForm.html?error=$error");
        exit; // Stop further execution
    }
} else {
    // Redirect back with error message
    $error = urlencode("المستخدم غير موجود!");
    header("Location: PointsForm.html?error=$error");
    exit; // Stop further execution
}

$stmt->close();
$conn->close();
?>
