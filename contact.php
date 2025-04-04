<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'db.php';

header('Content-Type: application/json');
$response = [];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $required_fields = ['name', 'email', 'phone', 'subject', 'message'];
    foreach ($required_fields as $field) {
        if (!isset($_POST[$field]) || empty(trim($_POST[$field]))) {
            $response['error'] = "يرجى ملء جميع الحقول المطلوبة!";
            echo json_encode($response);
            exit;
        }
    }

    $name = htmlspecialchars(trim($_POST['name']), ENT_QUOTES, 'UTF-8');
    $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
    $phone = htmlspecialchars(trim($_POST['phone']), ENT_QUOTES, 'UTF-8');
    $subject = htmlspecialchars(trim($_POST['subject']), ENT_QUOTES, 'UTF-8');
    $message = htmlspecialchars(trim($_POST['message']), ENT_QUOTES, 'UTF-8');

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $response['error'] = "يرجى إدخال بريد إلكتروني صالح!";
        echo json_encode($response);
        exit;
    }

    $sql = "INSERT INTO messages (name, email, phone, subject, message) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        error_log("Database error: " . $conn->error);
        $response['error'] = "خطأ في قاعدة البيانات: " . $conn->error;
        echo json_encode($response);
        exit;
    }

    $stmt->bind_param("sssss", $name, $email, $phone, $subject, $message);

    if ($stmt->execute()) {
        $response['success'] = 'تم إرسال الرسالة بنجاح!';
    } else {
        error_log("Statement error: " . $stmt->error);
        $response['error'] = 'حدث خطأ يرجى المحاولة لاحقاً: ' . $stmt->error;
    }

    $stmt->close();
} else {
    $response['error'] = "طلب غير صالح!";
}

$conn->close();
echo json_encode($response);
?>
