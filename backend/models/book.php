<?php
// Use absolute path to avoid any directory traversal issues
use MongoDB\BSON\ObjectId;
use MongoDB\BSON\UTCDateTime;

$configPath = dirname(__DIR__) . "/config/db.php";

if (!file_exists($configPath)) {
    die(json_encode([
        "success" => false, 
        "message" => "Config file not found at: " . $configPath
    ]));
}

require_once $configPath;

class Book {
    private $collection;

    public function __construct() {
        $db = (new Database())->getDB();
        $this->collection = $db->books;
    }

    // ✅ Helper function to convert MongoDB document to plain array
    private function formatBook($book) {
        if (!$book) {
            return null;
        }
        
        return [
            "id" => (string)$book['_id'],
            "title" => isset($book['title']) ? (string)$book['title'] : '',
            "author" => isset($book['author']) ? (string)$book['author'] : '',
            "year" => isset($book['year']) ? (string)$book['year'] : '',
            "status" => isset($book['status']) ? (string)$book['status'] : 'available',
            "borrower_id" => isset($book['borrower_id']) ? (string)$book['borrower_id'] : null,
            "created_at" => isset($book['created_at']) ? $this->formatDate($book['created_at']) : null
        ];
    }

    // ✅ Helper function to format MongoDB date
    private function formatDate($date) {
        if ($date instanceof UTCDateTime) {
            return $date->toDateTime()->format('Y-m-d H:i:s');
        }
        return (string)$date;
    }

    public function create($data) {
        try {
            return $this->collection->insertOne($data);
        } catch (Exception $e) {
            throw new Exception("Failed to create book: " . $e->getMessage());
        }
    }

    public function getAll() {
        try {
            return $this->collection->find()->toArray();
        } catch (Exception $e) {
            throw new Exception("Failed to fetch books: " . $e->getMessage());
        }
    }

    public function update($id, $data) {
        try {
            return $this->collection->updateOne(
                ['_id' => new ObjectId($id)],
                ['$set' => $data]
            );
        } catch (Exception $e) {
            throw new Exception("Failed to update book: " . $e->getMessage());
        }
    }

    public function delete($id) {
        try {
            $result = $this->collection->deleteOne([
                '_id' => new ObjectId($id)
            ]);
            
            return $result->getDeletedCount() > 0;
        } catch (Exception $e) {
            throw new Exception("Failed to delete book: " . $e->getMessage());
        }
    }

    // ✅ FIXED: Returns formatted data with success wrapper
    public function getBookById($id) {
        try {
            $book = $this->collection->findOne([
                '_id' => new ObjectId($id)
            ]);

            if ($book) {
                return [
                    "success" => true,
                    "data" => $this->formatBook($book)
                ];
            } else {
                return [
                    "success" => false,
                    "message" => "Book not found"
                ];
            }
        } catch (Exception $e) {
            return [
                "success" => false,
                "message" => "Error: " . $e->getMessage()
            ];
        }
    }

    // ✅ Optional: Get raw book document (for internal use)
    public function findById($id) {
        try {
            $book = $this->collection->findOne([
                '_id' => new ObjectId($id)
            ]);
            return $this->formatBook($book);
        } catch (Exception $e) {
            return null;
        }
    }

    public function updateStatus($id, $status, $extra = []) {
        try {
            $set = array_merge(['status' => $status], $extra);
            return $this->collection->updateOne(
                ['_id' => new ObjectId($id)],
                ['$set' => $set]
            );
        } catch (Exception $e) {
            throw new Exception("Failed to update status: " . $e->getMessage());
        }
    }
}