<?php
require_once __DIR__ . "/../../vendor/autoload.php";

class Database {
    private $db;
    private $collection;

    public function __construct() {
        try {
            $client = new MongoDB\Client("mongodb://localhost:27017");
            $this->db = $client->library_db;
        } catch (Exception $e) {
            die("Database connection failed: " . $e->getMessage());
        }
    }

    public function getDB() {
        return $this->db;
    }
}
