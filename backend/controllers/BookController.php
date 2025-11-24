<?php
require_once __DIR__ . "/../models/book.php";

class BookController {
    private $book;

    public function __construct() {
        $this->book = new Book();
    }

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

    public function deleteBook() {
        header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
        header("Access-Control-Allow-Headers: Content-Type");
        header("Content-Type: application/json");
    
        $id = $_GET["id"] ?? null;
    
        if (!$id) {
            echo json_encode(["success" => false, "message" => "ID not provided"]);
            return;
        }
    
        $result = $this->book->delete($id);
    
        echo json_encode([
            "success" => $result,
            "message" => $result ? "Book deleted successfully" : "Delete failed"
        ]);
    }
    public function getBookById($id)
{
    header("Access-Control-Allow-Origin: *");
    header("Content-Type: application/json");

    $result = $this->book->getBookById($id);
    echo json_encode($result);
}
public function updateBook() {
    header("Access-Control-Allow-Origin: *");
    header("Content-Type: application/json");

    $input = file_get_contents("php://input");
    $data = json_decode($input, true);

    if (!isset($data['id']) || empty($data['id'])) {
        echo json_encode(["success" => false, "message" => "Invalid book ID"]);
        return;
    }

    $id = $data['id'];
    
    // Remove id from data before update
    unset($data['id']);

    $result = $this->book->update($id, $data);

    if ($result->getModifiedCount() > 0) {
        echo json_encode(["success" => true, "message" => "Book updated successfully"]);
    } else {
        echo json_encode(["success" => false, "message" => "No changes detected or update failed"]);
    }
}


}