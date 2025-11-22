<?php
require_once __DIR__ . "/../controllers/BookController.php";
require_once __DIR__ . "/../controllers/UserController.php";

$bookController = new BookController();
$userController = new UserController();

// Get action from GET parameter
$action = $_GET['action'] ?? '';

switch ($action) {
    case "addBook":
        $bookController->createBook();
        break;
    case "getBooks":
        $bookController->getBooks();
        break;
    case "registerUser":
        $userController->registerUser();
        break;
    case "loginUser":
        $userController->loginUser();
        break;
    case "getUsers":
        $userController->getAllUsers();
        break;
    default:
        header("Content-Type: application/json");
        echo json_encode(["success" => false, "message" => "Invalid API request. Available actions: addBook, getBooks, registerUser, loginUser, getUsers"]);
}