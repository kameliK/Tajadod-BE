<?php
header('Content-Type: text/html; charset=utf-8');

// التحقق من المدخلات
if (!isset($_POST['first_name'], $_POST['last_name'], $_POST['phone'], $_POST['item_name'], $_POST['points'])) {
    echo "يرجى ملء جميع الحقول المطلوبة.";
    exit;
}

$firstName = trim($_POST['first_name']);
$lastName  = trim($_POST['last_name']);
$phone     = substr(trim($_POST['phone']), -9); // استخراج آخر 9 أرقام
$itemName  = trim($_POST['item_name']);
$pointsReq = $_POST['points'];

// تحقق من صحة البيانات
if ($firstName === '' || $lastName === '' || $phone === '' || $itemName === '' || !is_numeric($pointsReq) || (int)$pointsReq < 0) {
    echo "يرجى التأكد من صحة البيانات المدخلة.";
    exit;
}
$pointsReq = (int)$pointsReq;

// الاتصال بقاعدة البيانات
$conn = new mysqli("localhost", "root", "", "recycle_db");
if ($conn->connect_error) {
    echo "خطأ في الاتصال بقاعدة البيانات.";
    exit;
}
$conn->set_charset("utf8");

// استعلام للحصول على النقاط الحالية
$stmt = $conn->prepare(
    "SELECT points FROM providers WHERE first_name = ? AND last_name = ? AND phone = ? ORDER BY id DESC LIMIT 1"
);
$stmt->bind_param("sss", $firstName, $lastName, $phone);
$stmt->execute();
$stmt->bind_result($currentPoints);

if ($stmt->fetch()) {
    // تابع التنفيذ
} else {
    echo "المزود غير موجود.";
    $stmt->close();
    $conn->close();
    exit;
}
$stmt->close();

// التحقق من كفاية النقاط
if ((int)$currentPoints < $pointsReq) {
    echo "النقاط غير كافية.";
    $conn->close();
    exit;
}

// تحديث النقاط
$newPoints = $currentPoints - $pointsReq;
$updateStmt = $conn->prepare(
    "UPDATE providers SET points = ? WHERE first_name = ? AND last_name = ? AND phone = ?"
);
$updateStmt->bind_param("isss", $newPoints, $firstName, $lastName, $phone);

if ($updateStmt->execute()) {
    // إدخال الطلب في order_with_points
    $insertOrder = $conn->prepare(
        "INSERT INTO order_with_points (first_name, last_name, phone, item_name, item_points, remaining_points) 
         VALUES (?, ?, ?, ?, ?, ?)"
    );
    $insertOrder->bind_param("ssssii", $firstName, $lastName, $phone, $itemName, $pointsReq, $newPoints);
    $insertOrder->execute();
    $insertOrder->close();

    // إعادة التوجيه
    $paymentMethod = urlencode("الدفع بواسطة النقاط");
    $orderDate = urlencode(date("d F Y"));
    $itemNameEncoded = urlencode($itemName);
    $itemPrice = urlencode("$pointsReq نقطة"); // Pass the price in points
    $remainingPoints = urlencode($newPoints);
    header("Location: thank_you_store.html?payment_method=$paymentMethod&order_date=$orderDate&item_name=$itemNameEncoded&item_price=$itemPrice&remaining_points=$remainingPoints");
    exit;
} else {
    echo "حدث خطأ أثناء تحديث النقاط.";
}

$updateStmt->close();
$conn->close();
?>
