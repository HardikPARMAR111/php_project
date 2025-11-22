<?php
// IMPORTANT: No whitespace or output before this line!

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Headers: Content-Type");
    header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
    http_response_code(200);
    exit();
}

// Turn ON error display temporarily to debug
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/error.log');
error_reporting(E_ALL);

// Set CORS headers
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

// Clean any output buffer
if (ob_get_level()) {
    ob_clean();
}

try {
    // Include the route handler - routes folder is in the same directory as index.php
    $routePath = __DIR__ . "/routes/route.php";
    
    if (!file_exists($routePath)) {
        throw new Exception("Route file not found at: " . $routePath);
    }
    
    require_once $routePath;
} catch (Exception $e) {
    // If there's an error, return it as JSON
    echo json_encode([
        "success" => false,
        "message" => "Server error: " . $e->getMessage(),
        "file" => $e->getFile(),
        "line" => $e->getLine()
    ]);
}