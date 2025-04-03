<?php
// Database Connection
$host = "localhost"; 
$username = "root";  
$password = "";      
$database = "recycle_site"; 

$conn = new mysqli($host, $username, $password, $database);

if ($conn->connect_error) {
    die(json_encode(["error" => "Connection failed: " . $conn->connect_error]));
}

// Ensure request is POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize inputs
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

    // Decode material and amount JSON
    $material = json_decode($_POST['material'], true);
    $amount = json_decode($_POST['amount'], true);

    if (!$material || !$amount || count($material) !== count($amount)) {
        die(json_encode(["error" => "Invalid material or amount data."]));
    }

    // Validate required fields based on provider type
    if ($provider_type === "Company") {
        if (empty($first_name) || empty($last_name)) {
            die(json_encode(["error" => "Company name and employee name are required."]));
        }
    } else if ($provider_type === "Individual") {
        if (empty($first_name) || empty($last_name)) {
            die(json_encode(["error" => "First name and last name are required."]));
        }
    } else {
        die(json_encode(["error" => "Invalid provider type."]));
    }

    // Convert arrays to strings for storage
    $material_str = implode(", ", array_map('htmlspecialchars', $material));
    $amount_str = implode(", ", array_map('htmlspecialchars', $amount));

    // Insert Data
    $sql = "INSERT INTO providers (first_name, last_name, country_code, phone, material, amount, collection_day, collection_time_from, collection_time_to, address, points, provider_type)
            VALUES ('$first_name', '$last_name', '$country_code', '$phone', '$material_str', '$amount_str', '$collection_day', '$collection_time_from', '$collection_time_to', '$address', '$points', '$provider_type')";

    if ($conn->query($sql) === TRUE) {
        echo json_encode(["success" => "تم إرسال الطلب بنجاح!"]); // Success message
    } else {
        // Debugging: Output the SQL error
        echo json_encode(["error" => "Database error: " . $conn->error, "sql" => $sql]);
    }
}

$conn->close();
?>
