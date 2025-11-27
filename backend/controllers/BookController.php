<?php
require_once __DIR__ . "/../models/book.php";

class BookController {
    private $book;

    public function __construct() {
        $this->book = new Book();
    }

    // Set common headers
    private function setHeaders() {
        header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
        header("Access-Control-Allow-Headers: Content-Type");
        header("Content-Type: application/json");
    }

    // Handle preflight requests
    private function handleOptions() {
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            $this->setHeaders();
            http_response_code(200);
            exit();
        }
    }

    public function createBook() {
        $this->setHeaders();
        $this->handleOptions();

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
            $insertData = [
                "title" => $title,
                "author" => $author,
                "year" => (int)$year,
                "status" => "available",
                "created_at" => new MongoDB\BSON\UTCDateTime()
            ];

            // ✅ FIXED: Use $this->book instead of undefined $book
            $result = $this->book->create($insertData);
            
            echo json_encode([
                "success" => true, 
                "message" => "Book added successfully", 
                "id" => (string)$result->getInsertedId()
            ]);
        } catch (Exception $e) {
            echo json_encode(["success" => false, "message" => "Error: " . $e->getMessage()]);
        }
    }

    public function getBooks() {
        $this->setHeaders();
        $this->handleOptions();

        try {
            // ✅ FIXED: Use $this->book instead of creating new instance
            $books = $this->book->getAll();
            
            // Convert MongoDB objects to array format
            $booksArray = array_map(function($book) {
                return [
                    'id' => (string)$book['_id'],
                    'title' => isset($book['title']) ? (string)$book['title'] : '',
                    'author' => isset($book['author']) ? (string)$book['author'] : '',
                    'year' => isset($book['year']) ? (string)$book['year'] : '',
                    'status' => isset($book['status']) ? (string)$book['status'] : 'available',
                    'created_at' => isset($book['created_at']) ? (string)$book['created_at'] : null
                ];
            }, $books);

            echo json_encode(["success" => true, "data" => $booksArray]);
        } catch (Exception $e) {
            echo json_encode(["success" => false, "message" => "Error: " . $e->getMessage()]);
        }
    }

    public function deleteBook() {
        $this->setHeaders();
        $this->handleOptions();
    
        $id = $_GET["id"] ?? null;
    
        if (!$id) {
            echo json_encode(["success" => false, "message" => "ID not provided"]);
            return;
        }
    
        try {
            $result = $this->book->delete($id);
        
            echo json_encode([
                "success" => $result,
                "message" => $result ? "Book deleted successfully" : "Delete failed"
            ]);
        } catch (Exception $e) {
            echo json_encode(["success" => false, "message" => "Error: " . $e->getMessage()]);
        }
    }

    public function getBookById($id) {
        $this->setHeaders();
        $this->handleOptions();

        if (!$id || empty($id)) {
            echo json_encode(["success" => false, "message" => "Book ID is required"]);
            return;
        }

        try {
            // ✅ This now returns {"success": true, "data": {...}} from the model
            $result = $this->book->getBookById($id);
            echo json_encode($result);
        } catch (Exception $e) {
            echo json_encode(["success" => false, "message" => "Error: " . $e->getMessage()]);
        }
    }

    public function updateBook() {
        $this->setHeaders();
        $this->handleOptions();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(["success" => false, "message" => "Method not allowed"]);
            return;
        }

        $input = file_get_contents("php://input");
        $data = json_decode($input, true);

        if (!isset($data['id']) || empty($data['id'])) {
            echo json_encode(["success" => false, "message" => "Invalid book ID"]);
            return;
        }

        $id = $data['id'];
        
        // Remove id from data before update
        unset($data['id']);

        if (empty($data)) {
            echo json_encode(["success" => false, "message" => "No data to update"]);
            return;
        }

        try {
            $result = $this->book->update($id, $data);

            if ($result->getModifiedCount() > 0) {
                echo json_encode(["success" => true, "message" => "Book updated successfully"]);
            } else {
                echo json_encode(["success" => false, "message" => "No changes detected or book not found"]);
            }
        } catch (Exception $e) {
            echo json_encode(["success" => false, "message" => "Error: " . $e->getMessage()]);
        }
    }
}