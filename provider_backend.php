<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $input = json_decode(file_get_contents("php://input"), true);

    if (isset($input['action']) && $input['action'] === 'fetch_points') {
        $first_name = $conn->real_escape_string($input['first_name'] ?? '');
        $last_name = $conn->real_escape_string($input['last_name'] ?? '');
        $provider_type = $conn->real_escape_string($input['provider_type'] ?? 'Individual');
        $organization_name = $conn->real_escape_string($input['organization_name'] ?? '');

        if ($provider_type === 'Company') {
            $sql = "SELECT points FROM providers WHERE first_name = ? AND organization_name = ? AND provider_type = ? ORDER BY id DESC LIMIT 1";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sss", $first_name, $organization_name, $provider_type);
        } else {
            $sql = "SELECT points FROM providers WHERE first_name = ? AND last_name = ? AND provider_type = ? ORDER BY id DESC LIMIT 1";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sss", $first_name, $last_name, $provider_type);
        }

        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            echo json_encode(["success" => true, "points" => intval($row['points'])]);
        } else {
            echo json_encode(["success" => false, "message" => "No points found."]);
        }

        $stmt->close();
        exit;
    }

    $first_name = $conn->real_escape_string($_POST['first_name'] ?? '');
    $last_name = $conn->real_escape_string($_POST['last_name'] ?? '');
    $country_code = $conn->real_escape_string($_POST['country_code'] ?? '');
    $phone = $conn->real_escape_string($_POST['phone'] ?? '');
    $collection_day = $conn->real_escape_string($_POST['collection_day'] ?? '');
    $collection_time_from = $conn->real_escape_string($_POST['collection_time_from'] ?? '');
    $collection_time_to = $conn->real_escape_string($_POST['collection_time_to'] ?? '');
    $address = $conn->real_escape_string($_POST['address'] ?? '');
    $points = intval($_POST['points'] ?? 0);
    $provider_type = $conn->real_escape_string($_POST['provider_type'] ?? '');

    $material = json_decode($_POST['material'] ?? '[]', true);
    $amount = json_decode($_POST['amount'] ?? '[]', true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        die(json_encode(["error" => "Invalid JSON: " . json_last_error_msg()]));
    }

    if (!$material || !$amount || count($material) !== count($amount)) {
        die(json_encode(["error" => "Invalid material or amount data."]));
    }

    // Validate amount per provider type
    if ($provider_type === 'Company') {
        foreach ($amount as $qty) {
            if ($qty < 500 || $qty > 1000) {
                die(json_encode(["error" => "Invalid quantity for Company. Must be between 500kg and 1 ton."]));
            }
        }
    } elseif ($provider_type === 'Individual') {
        foreach ($amount as $qty) {
            if ($qty < 5 || $qty > 50) {
                die(json_encode(["error" => "Invalid quantity for Individual. Must be between 5kg and 50kg."]));
            }
        }
    } else {
        die(json_encode(["error" => "Invalid provider type."]));
    }

    $material_str = implode(", ", array_map('htmlspecialchars', $material));
    $amount_str = implode(", ", array_map('htmlspecialchars', $amount));

    // ✅ Check if user already exists
    $check_sql = "SELECT points FROM providers WHERE first_name = ? AND last_name = ? ORDER BY id DESC LIMIT 1";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("ss", $first_name, $last_name);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();

    if ($check_result && $check_result->num_rows > 0) {
        $existing = $check_result->fetch_assoc();
        $points += intval($existing['points']); // Add old points to new points
    }
    $check_stmt->close();

    // ✅ Insert new record with updated total points
    $sql = "INSERT INTO providers 
        (first_name, last_name, country_code, phone, material, amount, collection_day, collection_time_from, collection_time_to, address, points, provider_type) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        echo json_encode(["error" => "Database error: " . $conn->error]);
        exit;
    }

    $stmt->bind_param("ssssssssssis", 
        $first_name, $last_name, $country_code, $phone, 
        $material_str, $amount_str, $collection_day, $collection_time_from, 
        $collection_time_to, $address, $points, $provider_type);

    if ($stmt->execute()) {
        echo json_encode(["success" => "تم إرسال الطلب بنجاح! النقاط الإجمالية: $points", "redirect" => "thank_you.html"]);
    } else {
        echo json_encode(["error" => "Database error: " . $stmt->error]);
    }

    $stmt->close();
} else {
    echo json_encode(["error" => "Invalid request method."]);
}

$conn->close();
?>
