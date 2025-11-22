<?php
require_once __DIR__ . "/../config/database.php";

class Book {
    private $collection;

    public function  __construct() {
        $db = new Database();
        $this->collection = $db->getDB()->books;
    }

    public function create($data) {
        return $this->collection->insertOne($data);
    }

    public function getAll() {
        return $this->collection->find()->toArray();
    }

    public function update($id, $data) {
        return $this->collection->updateOne(
            ['_id' => new MongoDB\BSON\ObjectId($id)],
            ['$set' => $data]
        );
    }

    public function delete($id) {
        return $this->collection->deleteOne(['_id' => new MongoDB\BSON\ObjectId($id)]);
    }
}
