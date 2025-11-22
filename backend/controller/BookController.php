<?php
require_once __DIR__ . "/../models/Book.php";

class BookController {

    public function createBook() {
        header("Content-Type: application/json");
        header("Access-Control-Allow-Origin: *");

        $data = json_decode(file_get_contents("php://input"), true);

        $title = $data["title"] ?? null;
        $author = $data["author"] ?? null;
        $year = $data["year"] ?? null; // keep year

        if (!$title || !$author || !$year) {
            echo json_encode(["success" => false, "message" => "All fields required"]);
            return;
        }

        $book = new Book();
        $insertData = [
            "title" => $title,
            "author" => $author,
            "year" => $year,
            "status" => "available",
            "created_at" => date("Y-m-d H:i:s")
        ];

        $book->create($insertData);
        echo json_encode(["success" => true, "message" => "Book added successfully"]);
    }

    public function getBooks() {
        header("Content-Type: application/json");
        header("Access-Control-Allow-Origin: *");

        $book = new Book();
        $books = $book->getAll();
        echo json_encode($books);
    }
}
