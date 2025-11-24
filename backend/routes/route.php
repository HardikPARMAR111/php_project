<?php
require_once __DIR__ . "/../controllers/BookController.php";
require_once __DIR__ . "/../controllers/UserController.php";

$bookController = new BookController();
$userController = new UserController();

// Get action
$action = $_GET['action'] ?? '';

switch ($action) {

    case "addBook":
        $bookController->createBook();
        break;

    case "getBooks":
        $bookController->getBooks();
        break;

    case "deleteBook":
        $bookController->deleteBook();
        break;

    case "getBook":
        if (!isset($_GET['id'])) {
            echo json_encode(["success" => false, "message" => "ID is required"]);
            exit;
        }
        $id = $_GET['id'];
        $bookController->getBookById($id);
        break;

    case "updateBook":
        $bookController->updateBook();
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

    case "deleteUser":
        $userController->deleteUser();
        break;

    default:
        echo json_encode([
            "success" => false,
            "message" => "Invalid API request. Available actions: addBook, getBooks, getBook, deleteBook, updateBook, registerUser, loginUser, getUsers, deleteUser"
        ]);
        break;
}