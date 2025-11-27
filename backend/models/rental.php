<?php
require_once __DIR__ . "/../config/db.php"; // adjust path if needed
use MongoDB\BSON\ObjectId;

class Rental {
    private $collection;

    public function __construct() {
        $db = (new Database())->getDB();
        $this->collection = $db->rentals;
    }

    public function create($data) {
        return $this->collection->insertOne($data);
    }

    public function findById($id) {
        return $this->collection->findOne(['_id' => new ObjectId($id)]);
    }

    public function findByUserId($userId) {
        $cursor = $this->collection->find(['user_id' => $userId]);
        return iterator_to_array($cursor);
    }

    public function getAll() {
        $cursor = $this->collection->find();
        return iterator_to_array($cursor);
    }

    public function update($id, $data) {
        return $this->collection->updateOne(
            ['_id' => new ObjectId($id)],
            ['$set' => $data]
        );
    }
}
