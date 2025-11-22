<?php
require_once __DIR__ . "/../config/database.php";

class User {
    private $collection;

    public function __construct() {
        $db = new Database();
        $this->collection = $db->getDB()->users;
    }

    public function register($data) {
        return $this->collection->insertOne($data);
    }

    public function getAll() {
        return $this->collection->find()->toArray();
    }
}
