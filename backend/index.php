<?php
// -------- CORS + PRE-FLIGHT --------
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Content-Type: application/json");

// Handle OPTIONS request (preflight)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// -------- DEBUG MODE --------
ini_set('display_errors', 1);
error_reporting(E_ALL);

// -------- INCLUDE ROUTER --------
$routePath = __DIR__ . "/routes/route.php";

if (!file_exists($routePath)) {
    echo json_encode([
        "success" => false,
        "message" => "Route file not found",
        "path" => $routePath
    ]);
    exit();
}

require_once $routePath;
?>
