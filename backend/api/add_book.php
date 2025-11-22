<?php
require_once "../config/db.php";
require_once "../controllers/BookController.php";

$controller = new BookController($db);

$data = [
    "title" => $_POST['title'],
    "author" => $_POST['author'],
    "year" => $_POST['year']
];

$result = $controller->create($data);

echo json_encode(["success" => true, "inserted_id" => (string)$result->getInsertedId()]);
