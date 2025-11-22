<?php
require_once __DIR__ . "/../models/book.php";

class BookController {

    public function createBook() {
        // Set headers first
        header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
        header("Access-Control-Allow-Headers: Content-Type");
        header("Content-Type: application/json");

        // Check if it's POST request
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(["success" => false, "message" => "Method not allowed"]);
            return;
        }

        $data = json_decode(file_get_contents("php://input"), true);

        $title = $data["title"] ?? null;
        $author = $data["author"] ?? null;
        $year = $data["year"] ?? null;

        if (!$title || !$author || !$year) {
            echo json_encode(["success" => false, "message" => "All fields required"]);
            return;
        }

        try {
            $book = new Book();
            $insertData = [
                "title" => $title,
                "author" => $author,
                "year" => (int)$year,
                "status" => "available",
                "created_at" => new MongoDB\BSON\UTCDateTime()
            ];

            $result = $book->create($insertData);
            echo json_encode(["success" => true, "message" => "Book added successfully", "id" => (string)$result->getInsertedId()]);
        } catch (Exception $e) {
            echo json_encode(["success" => false, "message" => "Error: " . $e->getMessage()]);
        }
    }

    public function getBooks() {
        header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
        header("Access-Control-Allow-Headers: Content-Type");
        header("Content-Type: application/json");

        try {
            $book = new Book();
            $books = $book->getAll();
            
            // Convert MongoDB objects to array format
            $booksArray = array_map(function($book) {
                return [
                    'id' => (string)$book['_id'],
                    'title' => $book['title'],
                    'author' => $book['author'],
                    'year' => $book['year'],
                    'status' => $book['status'] ?? 'available',
                    'created_at' => isset($book['created_at']) ? (string)$book['created_at'] : null
                ];
            }, $books);

            echo json_encode(["success" => true, "data" => $booksArray]);
        } catch (Exception $e) {
            echo json_encode(["success" => false, "message" => "Error: " . $e->getMessage()]);
        }
    }
}