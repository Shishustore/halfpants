<?php
require_once __DIR__ . '/config.php';

// Set secure headers
header("Content-Type: application/json");
header("X-Content-Type-Options: nosniff");

if (!$link) {
    http_response_code(500);
    exit(json_encode(['status' => 'error', 'message' => 'System error. Please try again.']));
}

$json = file_get_contents('php://input');
$data = json_decode($json, true);

if (json_last_error() !== JSON_ERROR_NONE || !$data) {
    http_response_code(400);
    exit(json_encode(['status' => 'error', 'message' => 'Invalid data provided.']));
}

// Sanitize and validate inputs
$phone = preg_replace('/[^0-9]/', '', $data['phone'] ?? '');
$name = substr(sanitize_input($data['name'] ?? ''), 0, 100);
$address = substr(sanitize_input($data['address'] ?? ''), 0, 255);

if (strlen($phone) < 7 || strlen($phone) > 15) {
    http_response_code(400);
    exit(json_encode(['status' => 'error', 'message' => 'Invalid phone number format.']));
}

try {
    // USE PARAMETERIZED QUERY TO PREVENT SQL INJECTION
    $stmt = mysqli_prepare($link, "INSERT INTO leads (phone, name, address) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE name = VALUES(name), address = VALUES(address)");

    if (!$stmt) {
        throw new Exception("Database statement preparation failed.");
    }

    mysqli_stmt_bind_param($stmt, "sss", $phone, $name, $address);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    // Securely write to CSV
    write_to_csv('leads.csv', [$name, $phone, $address, date('Y-m-d H:i:s')]);

    echo json_encode(['status' => 'success']);

} catch (Exception $e) {
    error_log("Lead Save Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Could not save your information.']);
}

mysqli_close($link);
?>
