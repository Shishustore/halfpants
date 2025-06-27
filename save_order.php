<?php
require_once __DIR__ . '/config.php';

// Set secure headers
header("Content-Security-Policy: default-src 'self'");
header("X-Content-Type-Options: nosniff");
header("Strict-Transport-Security: max-age=31536000; includeSubDomains");
header("Referrer-Policy: strict-origin-when-cross-origin");

if (!$link) {
    http_response_code(500);
    exit(json_encode(['status' => 'error', 'message' => 'System error']));
}

try {
    // Validate content type
    $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
    if (stripos($contentType, 'application/json') === false) {
        throw new Exception("Invalid content type");
    }
    
    // Get and validate input
    $json = file_get_contents('php://input');
    if (strlen($json) > 100000) { // ~100KB max
        throw new Exception("Payload too large");
    }
    
    $data = json_decode($json, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception("Invalid JSON data");
    }

    // Validate required fields
    $required = ['customer', 'cart', 'summary'];
    foreach ($required as $field) {
        if (empty($data[$field])) {
            throw new Exception("Missing required field: $field");
        }
    }

    // Sanitize customer data
    $customer = [
        'name' => substr(sanitize_input($data['customer']['name']), 0, 100),
        'phone' => preg_replace('/[^0-9]/', '', $data['customer']['phone']),
        'address' => substr(sanitize_input($data['customer']['address']), 0, 255)
    ];
    
    // Validate phone number
    if (strlen($customer['phone']) < 7 || strlen($customer['phone']) > 15) {
        throw new Exception("Invalid phone number");
    }
    
    // Process cart items
    $cart = [];
    foreach ($data['cart'] as $item) {
        if (empty($item['name']) || empty($item['size']) || !isset($item['quantity']) || !isset($item['price'])) {
            continue; // Skip invalid items
        }
        
        $cart[] = [
            'name' => substr(sanitize_input($item['name']), 0, 100),
            'size' => substr(sanitize_input($item['size']), 0, 20),
            'quantity' => min(max((int)$item['quantity'], 1), 10), // 1-10 items
            'price' => min(max((float)$item['price'], 0), 10000) // 0-10,000 Tk
        ];
    }
    
    if (count($cart) === 0) {
        throw new Exception("Cart is empty");
    }
    
    // Process summary
    $summary = [
        'subtotal' => min(max((float)$data['summary']['subtotal'], 0), 100000),
        'discount' => min(max((float)$data['summary']['discount'], 0), 100000),
        'shipping' => min(max((float)$data['summary']['shipping'], 0), 1000),
        'grandTotal' => min(max((float)$data['summary']['grandTotal'], 0), 100000),
        'shipping_location' => substr(sanitize_input($data['summary']['shipping_location']), 0, 100)
    ];

    // ... rest of database transaction code from previous version ...
    // [Keep the database transaction code unchanged]

} catch (Exception $e) {
    // Log detailed error internally
    error_log("Order Error: " . $e->getMessage() . " | " . json_encode($_SERVER));
    
    // Generic message for client
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Could not process your request']);
    exit;
}
?>