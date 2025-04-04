<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $first_name = $conn->real_escape_string($_POST['first_name'] ?? '');
    $last_name = $conn->real_escape_string($_POST['last_name'] ?? '');
    $country_code = $conn->real_escape_string($_POST['country_code'] ?? '');
    $phone = $conn->real_escape_string($_POST['phone'] ?? '');
    $collection_day = $conn->real_escape_string($_POST['collection_day'] ?? '');
    $collection_time_from = $conn->real_escape_string($_POST['collection_time_from'] ?? '');
    $collection_time_to = $conn->real_escape_string($_POST['collection_time_to'] ?? '');
    $address = $conn->real_escape_string($_POST['address'] ?? '');
    $points = intval($_POST['points'] ?? 0); // Ensure points are received and processed
    $provider_type = $conn->real_escape_string($_POST['provider_type'] ?? '');

    $material = json_decode($_POST['material'] ?? '[]', true);
    $amount = json_decode($_POST['amount'] ?? '[]', true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        error_log("JSON error: " . json_last_error_msg());
        die(json_encode(["error" => "Invalid JSON: " . json_last_error_msg()]));
    }

    if (empty($first_name) || empty($last_name) || empty($country_code) || empty($phone) || empty($address)) {
        error_log("Missing required fields: " . json_encode([
            "first_name" => $first_name,
            "last_name" => $last_name,
            "country_code" => $country_code,
            "phone" => $phone,
            "address" => $address
        ]));
        // Proceed without stopping execution
    }

    if (!$material || !$amount || count($material) !== count($amount)) {
        error_log("Invalid material or amount data: " . json_encode([
            "material" => $material,
            "amount" => $amount
        ]));
        // Proceed without stopping execution
    }

    // Validate material and amount based on provider type
    if ($provider_type === 'Company') {
        foreach ($amount as $qty) {
            if ($qty < 500 || $qty > 1000) {
                die(json_encode(["error" => "Invalid quantity for Company. Must be between 500kg and 1 ton."]));
            }
        }
    } else { // Individual
        foreach ($amount as $qty) {
            if ($qty < 5 || $qty > 50) {
                die(json_encode(["error" => "Invalid quantity for Individual. Must be between 5kg and 50kg."]));
            }
        }
    }

    $material_str = implode(", ", array_map('htmlspecialchars', $material));
    $amount_str = implode(", ", array_map('htmlspecialchars', $amount));

    $sql = "INSERT INTO providers 
        (first_name, last_name, country_code, phone, material, amount, collection_day, collection_time_from, collection_time_to, address, points, provider_type) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        error_log("Database error: " . $conn->error);
        echo json_encode(["error" => "Database error: " . $conn->error]);
        exit;
    }

    $stmt->bind_param("ssssssssssis", 
        $first_name, $last_name, $country_code, $phone, 
        $material_str, $amount_str, $collection_day, $collection_time_from, 
        $collection_time_to, $address, $points, $provider_type);

    if ($stmt->execute()) {
        // Redirect to thank_you.html after successful insertion
        echo json_encode(["success" => "تم إرسال الطلب بنجاح!", "redirect" => "thank_you.html"]);
    } else {
        error_log("Statement error: " . $stmt->error);
        echo json_encode(["error" => "Database error: " . $stmt->error]);
    }

    $stmt->close();
} else {
    echo json_encode(["error" => "Invalid request method."]);
}

$conn->close();
?>
