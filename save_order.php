<?php
require_once __DIR__ . '/config.php';

// Set secure headers
header("Content-Type: application/json");
header("X-Content-Type-Options: nosniff");

if (!$link) {
    http_response_code(500);
    exit(json_encode(['status' => 'error', 'message' => 'System error. Please try again.']));
}

try {
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);

    if (json_last_error() !== JSON_ERROR_NONE || !$data) {
        throw new Exception("Invalid JSON data received.");
    }

    // --- Sanitize Customer Data ---
    $customer = $data['customer'] ?? [];
    $customer_name = substr(sanitize_input($customer['name'] ?? ''), 0, 100);
    $customer_phone = preg_replace('/[^0-9]/', '', $customer['phone'] ?? '');
    $customer_address = substr(sanitize_input($customer['address'] ?? ''), 0, 255);

    if (strlen($customer_phone) < 7 || strlen($customer_phone) > 15) {
        throw new Exception("Invalid phone number format.");
    }

    // --- Sanitize Cart and Summary ---
    $cart_json = json_encode($data['cart'] ?? []);
    $summary_json = json_encode($data['summary'] ?? []);
    $grand_total = floatval($data['summary']['grandTotal'] ?? 0);

    // --- Database Transaction ---
    mysqli_begin_transaction($link);

    // USE PARAMETERIZED QUERY
    $stmt = mysqli_prepare($link, "INSERT INTO orders (customer_name, customer_phone, customer_address, cart_details, summary_details, grand_total) VALUES (?, ?, ?, ?, ?, ?)");
    if (!$stmt) {
        throw new Exception('Database statement preparation failed.');
    }

    mysqli_stmt_bind_param($stmt, "sssssd", $customer_name, $customer_phone, $customer_address, $cart_json, $summary_json, $grand_total);
    mysqli_stmt_execute($stmt);

    $order_id = mysqli_insert_id($link);
    mysqli_stmt_close($stmt);

    mysqli_commit($link);

    // Securely write to CSV
    $csv_data = [$order_id, $customer_name, $customer_phone, $grand_total, date('Y-m-d H:i:s')];
    write_to_csv('orders.csv', $csv_data);

    echo json_encode(['status' => 'success', 'order_id' => $order_id]);

} catch (Exception $e) {
    mysqli_rollback($link); // Roll back transaction on error
    error_log("Order Save Error: " . $e->getMessage());
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Could not process your order.']);
}

mysqli_close($link);
?>
