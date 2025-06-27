<?php
// Disable error display in production
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(0);

// Database credentials
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'shishustore_halfpants');
define('DB_PASSWORD', 'T5y)A-k0]e[.');
define('DB_NAME', 'shishustore_halfpants');

// Set secure session parameters
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.cookie_secure', 1);
    ini_set('session.use_only_cookies', 1);
    ini_set('session.cookie_samesite', 'Strict');
    session_start();
}

// Input validation function
function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    return $data;
}

try {
    // Create MySQLi connection
    $link = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
    
    if (!$link) {
        throw new Exception("Database connection failed");
    }
    
    // Set charset
    mysqli_set_charset($link, "utf8mb4");
    
} catch (Exception $e) {
    // Secure error logging
    error_log($e->getMessage(), 0);
    
    if (strpos($_SERVER['REQUEST_URI'], '.php') !== false) {
        die("<h1>Application Error</h1><p>Service temporarily unavailable. Please try again later.</p>");
    }
}

// Secure CSV writing function
function write_to_csv($filepath, $data) {
    // Validate file path
    $allowed_path = __DIR__ . '/';
    $full_path = realpath($allowed_path . $filepath);
    
    if (strpos($full_path, $allowed_path) !== 0) {
        error_log("Invalid file path: $filepath");
        return false;
    }
    
    // Sanitize data
    $sanitized_data = array_map(function($item) {
        return str_replace(['\r', '\n', '\t'], ' ', strip_tags($item));
    }, $data);
    
    // Write to file
    $file_handle = fopen($full_path, 'a');
    if ($file_handle) {
        fputcsv($file_handle, $sanitized_data);
        fclose($file_handle);
        return true;
    }
    return false;
}
?>