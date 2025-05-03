<?php
// Connect to database
$conn = new mysqli("localhost", "root", "", "recycle_db");

// Check connection
if ($conn->connect_error) {
    die("فشل الاتصال بقاعدة البيانات: " . $conn->connect_error);
}

// Get data from POST
$first = $_POST['first_name'];
$last = $_POST['last_name'];
$old = $_POST['old_phone'];
$new = $_POST['new_phone'];

// Normalize phone numbers
if (strpos($old, '0') === 0) {
    $old = '+962' . substr($old, 1);
}
if (strpos($new, '0') === 0) {
    $new = '+962' . substr($new, 1);
}

// Check if provider exists
$check_sql = "SELECT * FROM providers WHERE first_name = ? AND last_name = ? AND phone = ?";
$check_stmt = $conn->prepare($check_sql);
$check_stmt->bind_param("sss", $first, $last, $old);
$check_stmt->execute();
$result = $check_stmt->get_result();

if ($result->num_rows > 0) {
    // Update phone number
    $update_sql = "UPDATE providers SET phone = ? WHERE first_name = ? AND last_name = ? AND phone = ?";
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param("ssss", $new, $first, $last, $old);

    if ($update_stmt->execute()) {
        header("Location: customerService.html");
        exit();
    } else {
        echo "<script>alert('حدث خطأ أثناء التحديث.'); window.history.back();</script>";
    }
} else {
    echo "<script>alert('لم يتم العثور على معلومات مطابقة.'); window.history.back();</script>";
}

$conn->close();
?>
