<?php
// Connection settings
$host = "localhost";
$user = "root"; // Update if necessary
$pass = "";     // Update if necessary
$dbname = "recycle_db";

// Connect to MySQL
$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) {
    die("فشل الاتصال بقاعدة البيانات: " . $conn->connect_error);
}

// Receive and sanitize data
$selectedItem  = $conn->real_escape_string($_POST['selectedItem']);
$firstName     = $conn->real_escape_string($_POST['firstName']);
$lastName      = $conn->real_escape_string($_POST['lastName']);
$address       = $conn->real_escape_string($_POST['address']);
$contactNumber = $conn->real_escape_string($_POST['contactNumber']);

// Insert into table
$sql = "INSERT INTO cash_orders (selected_item, first_name, last_name, address, contact_number)
        VALUES ('$selectedItem', '$firstName', '$lastName', '$address', '$contactNumber')";

if ($conn->query($sql) === TRUE) {
    // Display success message as JavaScript alert
    echo "<script>alert('✅ تم إرسال طلبك بنجاح!'); window.location.href = 'Home.html';</script>";
} else {
    echo "<script>alert('❌ حدث خطأ أثناء إرسال البيانات: " . $conn->error . "'); window.location.href = 'CashPurchaseForm.html';</script>";
}

$conn->close();
?>
