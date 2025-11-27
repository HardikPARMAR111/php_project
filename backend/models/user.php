<?php
require_once dirname(__DIR__) . "/config/db.php";
use MongoDB\BSON\ObjectId;

class User {

    private $collection;

    public function __construct() {
        $db = (new Database())->getDB();
        $this->collection = $db->users;
    }

    public function register($data) {
        return $this->collection->insertOne($data);
    }

    public function findByEmail($email) {
        return $this->collection->findOne(["email" => $email]);
    }

    public function findById($id) {
        return $this->collection->findOne(["_id" => new ObjectId($id)]);
    }

    public function update($id, $data) {
        return $this->collection->updateOne(
            ["_id" => new ObjectId($id)],
            ['$set' => $data]
        );
    }

    public function getAll() {
        $cursor = $this->collection->find();
        return iterator_to_array($cursor);
    }

    public function delete($id) {
        return $this->collection->deleteOne(["_id" => new ObjectId($id)]);
    }
}
