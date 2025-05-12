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
    // Redirect to thank_you_store.html with payment method, date, and item name
    $paymentMethod = urlencode("الدفع عند الاستلام");
    $orderDate = urlencode(date("d F Y")); // Current date in "day month year" format
    $itemName = urlencode($selectedItem);
    header("Location: thank_you_store.html?payment_method=$paymentMethod&order_date=$orderDate&item_name=$itemName");
    exit;
} else {
    echo "<script>alert('❌ حدث خطأ أثناء إرسال البيانات: " . $conn->error . "'); window.location.href = 'CashPurchaseForm.html';</script>";
}

$conn->close();
?>
