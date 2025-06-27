<?php
// Disable error display in production for security
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(0);

/**
 * DATABASE CREDENTIALS
 *
 * It is highly recommended to use environment variables for security.
 * In your hosting control panel (like cPanel), find "Environment Variables"
 * and set these values.
 */
define('DB_SERVER', getenv('DB_SERVER') ?: 'localhost');
define('DB_USERNAME', getenv('DB_USERNAME') ?: 'shishustore_halfpants');
define('DB_PASSWORD', getenv('DB_PASSWORD') ?: 'T5y)A-k0]e[.');
define('DB_NAME', getenv('DB_NAME') ?: 'shishustore_halfpants');


// Secure session parameters
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.cookie_secure', 1); // Set to 1 if you are using HTTPS
    ini_set('session.use_only_cookies', 1);
    ini_set('session.cookie_samesite', 'Strict');
    session_start();
}

/**
 * Improved Input Sanitization Function
 *
 * @param string $data The input data to sanitize.
 * @return string The sanitized data.
 */
function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    // ENT_QUOTES protects against both single and double quotes.
    $data = htmlspecialchars($data, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    return $data;
}

try {
    // Create MySQLi connection
    $link = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

    if (!$link) {
        throw new Exception("Database connection failed");
    }

    // Set charset for the connection
    mysqli_set_charset($link, "utf8mb4");

} catch (Exception $e) {
    // Log the actual error to a private server file instead of showing it to the user.
    error_log($e->getMessage(), 0);

    // Show a generic error message to the user
    if (strpos($_SERVER['REQUEST_URI'], '.php') !== false) {
        die("<h1>Application Error</h1><p>A problem has occurred. Please try again later.</p>");
    }
}


/**
 * Securely writes data to a CSV file.
 *
 * @param string $filepath The path to the CSV file (must be within the allowed directory).
 * @param array $data The data array to write.
 * @return bool True on success, false on failure.
 */
function write_to_csv($filepath, $data) {
    // Whitelist allowed characters for a filename
    $filename = preg_replace('/[^a-zA-Z0-9_.-]/', '', basename($filepath));
    $allowed_path = realpath(__DIR__);
    $full_path = $allowed_path . '/' . $filename;

    // Final security check to prevent path traversal
    if (strpos(realpath($full_path), $allowed_path) !== 0) {
        error_log("Attempted directory traversal: $filepath");
        return false;
    }

    // Sanitize data before writing to prevent CSV injection
    $sanitized_data = array_map(function($item) {
        return str_replace(['=', '+', '-', '@'], '', strip_tags($item));
    }, $data);

    $file_handle = fopen($full_path, 'a');
    if ($file_handle) {
        fputcsv($file_handle, $sanitized_data);
        fclose($file_handle);
        return true;
    }
    return false;
}
?>
