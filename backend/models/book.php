<?php
// Use absolute path to avoid any directory traversal issues
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
        try {
            $db = new Database();
            $this->collection = $db->getDB()->books;
        } catch (Exception $e) {
            throw new Exception("Book model initialization failed: " . $e->getMessage());
        }
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
                ['_id' => new MongoDB\BSON\ObjectId($id)],
                ['$set' => $data]
            );
        } catch (Exception $e) {
            throw new Exception("Failed to update book: " . $e->getMessage());
        }
    }

    public function delete($id) {
        try {
            return $this->collection->deleteOne(
                ['_id' => new MongoDB\BSON\ObjectId($id)]
            );
        } catch (Exception $e) {
            throw new Exception("Failed to delete book: " . $e->getMessage());
        }
    }
}