<?php
$configPath = dirname(__DIR__) . "/config/db.php";

if (!file_exists($configPath)) {
    die(json_encode([
        "success" => false, 
        "message" => "Config file not found at: " . $configPath
    ]));
}

require_once $configPath;

class User {
    private $collection;

    public function __construct() {
        try {
            $db = new Database();
            $this->collection = $db->getDB()->users;
        } catch (Exception $e) {
            throw new Exception("User model initialization failed: " . $e->getMessage());
        }
    }

    public function register($data) {
        try {
            return $this->collection->insertOne($data);
        } catch (Exception $e) {
            throw new Exception("Failed to register user: " . $e->getMessage());
        }
    }

    public function findByEmail($email) {
        try {
            return $this->collection->findOne(['email' => $email]);
        } catch (Exception $e) {
            throw new Exception("Failed to find user: " . $e->getMessage());
        }
    }

    public function getAll() {
        try {
            return $this->collection->find()->toArray();
        } catch (Exception $e) {
            throw new Exception("Failed to fetch users: " . $e->getMessage());
        }
    }

    public function update($id, $data) {
        try {
            return $this->collection->updateOne(
                ['_id' => new MongoDB\BSON\ObjectId($id)],
                ['$set' => $data]
            );
        } catch (Exception $e) {
            throw new Exception("Failed to update user: " . $e->getMessage());
        }
    }

    public function delete($id) {
        try {
            return $this->collection->deleteOne([
                '_id' => new MongoDB\BSON\ObjectId($id)
            ]);
        } catch (Exception $e) {
            throw new Exception("Failed to delete user: " . $e->getMessage());
        }
    }
}