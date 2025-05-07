<?php
$host = "localhost";
$db = "recycle_db";
$user = "root";
$pass = "";

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Connect to MySQL
$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("فشل الاتصال: " . $conn->connect_error);
}

// Get form data
$first_name     = $_POST['first_name'] ?? '';
$last_name      = $_POST['last_name'] ?? '';
$phone_number   = $_POST['phone_number'] ?? '';
$delivery_date  = $_POST['delivery_date'] ?? '';
$product_name   = $_POST['product_name'] ?? '';
$product_price  = floatval($_POST['product_price'] ?? 0);
$product_weight = floatval($_POST['product_weight'] ?? 0);
$payment_method = $_POST['payment_method'] ?? 'cash';

// Get additional Visa-specific fields
$card_number     = $_POST['card_number'] ?? '';
$expiration_date = $_POST['expiration_date'] ?? '';
$cvv             = $_POST['cvv'] ?? '';

// Debugging: Log received POST data
error_log("Received POST data: " . print_r($_POST, true));

// Check if required fields are set
$missing_fields = [];
if (empty($first_name)) $missing_fields[] = 'first_name';
if (empty($last_name)) $missing_fields[] = 'last_name';
if (empty($phone_number)) $missing_fields[] = 'phone_number';
if (empty($delivery_date)) $missing_fields[] = 'delivery_date';
if (empty($product_name)) $missing_fields[] = 'product_name';
if ($payment_method === 'visa' && (empty($card_number) || empty($expiration_date) || empty($cvv))) {
    if (empty($card_number)) $missing_fields[] = 'card_number';
    if (empty($expiration_date)) $missing_fields[] = 'expiration_date';
    if (empty($cvv)) $missing_fields[] = 'cvv';
}

if (!empty($missing_fields)) {
    die("يرجى تعبئة جميع الحقول المطلوبة: " . implode(', ', $missing_fields));
}

// Insert into store table
$stmt = $conn->prepare("INSERT INTO store (first_name, last_name, phone_number, delivery_date, product_name, product_price, product_weight, payment_method, card_number, expiration_date, cvv) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param("sssssdsssss", $first_name, $last_name, $phone_number, $delivery_date, $product_name, $product_price, $product_weight, $payment_method, $card_number, $expiration_date, $cvv);

if ($stmt->execute()) {
    header("Location: thank_you.html");
    exit();
} else {
    error_log("Database Error: " . $stmt->error); // Log error
    echo "<script>alert('حدث خطأ أثناء إدخال البيانات. يرجى المحاولة مرة أخرى لاحقًا.'); window.history.back();</script>";
}

$stmt->close();
$conn->close();
?>
