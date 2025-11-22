<?php
require_once __DIR__ . "/../controllers/bookController.php";

$bookController = new BookController();

$action = $_GET['action'] ?? '';

switch ($action) {
    case "addBook":
        $bookController->createBook();
        break;
    case "getBooks":
        $bookController->getBooks();
        break;
    default:
        echo json_encode(["message" => "Invalid API request"]);
}
