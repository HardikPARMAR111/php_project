<?php
$modelPath = dirname(__DIR__) . "/models/user.php";
if (!file_exists($modelPath)) {
    die(json_encode(["success" => false, "message" => "User model not found"]));
}
require_once $modelPath;

class UserController {

    private function setHeaders() {
        header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
        header("Access-Control-Allow-Headers: Content-Type");
        header("Content-Type: application/json");
    }

    private function allowOptions() {
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            $this->setHeaders();
            http_response_code(200);
            exit();
        }
    }

    public function __construct() {
        $this->allowOptions();
        $this->setHeaders();
    }

    // -------------------------------
    // REGISTER USER
    // -------------------------------
    public function registerUser() {

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(["success" => false, "message" => "Method not allowed"]);
            return;
        }

        $data = json_decode(file_get_contents("php://input"), true);

        $name = $data["name"] ?? null;
        $email = $data["email"] ?? null;
        $password = $data["password"] ?? null;

        if (!$name || !$email || !$password) {
            echo json_encode(["success" => false, "message" => "All fields are required"]);
            return;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(["success" => false, "message" => "Invalid email"]);
            return;
        }

        try {
            $user = new User();

            if ($user->findByEmail($email)) {
                echo json_encode(["success" => false, "message" => "Email already exists"]);
                return;
            }

            $result = $user->register([
                "name" => $name,
                "email" => $email,
                "password" => password_hash($password, PASSWORD_BCRYPT),
                "role" => "user",
                "created_at" => new MongoDB\BSON\UTCDateTime()
            ]);

            echo json_encode([
                "success" => true,
                "message" => "User registered",
                "id" => (string) $result->getInsertedId()
            ]);

        } catch (Exception $e) {
            echo json_encode(["success" => false, "message" => $e->getMessage()]);
        }
    }

    // -------------------------------
    // LOGIN USER
    // -------------------------------
    public function loginUser() {

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(["success" => false, "message" => "Method not allowed"]);
            return;
        }

        $data = json_decode(file_get_contents("php://input"), true);

        $email = $data["email"] ?? null;
        $password = $data["password"] ?? null;

        if (!$email || !$password) {
            echo json_encode(["success" => false, "message" => "Email & password required"]);
            return;
        }

        try {
            $user = new User();
            $found = $user->findByEmail($email);

            if (!$found || !password_verify($password, $found['password'])) {
                echo json_encode(["success" => false, "message" => "Invalid credentials"]);
                return;
            }

            echo json_encode([
                "success" => true,
                "message" => "Login successful",
                "data" => [
                    "id" => (string)$found['_id'],
                    "name" => $found['name'],
                    "email" => $found['email'],
                    "role" => $found['role']
                ]
            ]);

        } catch (Exception $e) {
            echo json_encode(["success" => false, "message" => $e->getMessage()]);
        }
    }

    // -------------------------------
    // GET USER BY ID
    // -------------------------------
    public function getUser() {

        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            echo json_encode(["success" => false, "message" => "Method not allowed"]);
            return;
        }

        $id = $_GET['id'] ?? null;

        if (!$id) {
            echo json_encode(["success" => false, "message" => "User ID required"]);
            return;
        }

        try {
            $user = new User();
            $found = $user->findById($id);

            if (!$found) {
                echo json_encode(["success" => false, "message" => "User not found"]);
                return;
            }

            echo json_encode([
                "success" => true,
                "data" => [
                    "id" => (string)$found['_id'],
                    "name" => $found['name'],
                    "email" => $found['email']
                ]
            ]);

        } catch (Exception $e) {
            echo json_encode(["success" => false, "message" => $e->getMessage()]);
        }
    }

    // -------------------------------
    // UPDATE USER
    // -------------------------------
    public function updateUser() {

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(["success" => false, "message" => "Method not allowed"]);
            return;
        }

        $data = json_decode(file_get_contents("php://input"), true);

        $id = $data["id"] ?? null;
        $name = $data["name"] ?? null;
        $email = $data["email"] ?? null;
        $password = $data["password"] ?? null;

        if (!$id || !$name || !$email) {
            echo json_encode(["success" => false, "message" => "Missing fields"]);
            return;
        }

        try {
            $user = new User();
            $updateData = ["name" => $name, "email" => $email];

            if ($password) {
                $updateData["password"] = password_hash($password, PASSWORD_BCRYPT);
            }

            $user->update($id, $updateData);

            echo json_encode(["success" => true, "message" => "User updated"]);

        } catch (Exception $e) {
            echo json_encode(["success" => false, "message" => $e->getMessage()]);
        }
    }


    // -------------------------------
    // GET ALL USERS
    // -------------------------------
    public function getAllUsers() {

        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            echo json_encode(["success" => false, "message" => "Method not allowed"]);
            return;
        }

        try {
            $user = new User();
            $users = $user->getAll();

            $mapped = array_map(function($u) {
                return [
                    "id" => (string)$u['_id'],
                    "name" => $u["name"],
                    "email" => $u["email"],
                    "role" => $u["role"],
                    "created_at" => isset($u["created_at"]) ? $u["created_at"]->toDateTime()->format(DATE_ATOM) : null
                ];
            }, $users);

            echo json_encode(["success" => true, "data" => $mapped]);

        } catch (Exception $e) {
            echo json_encode(["success" => false, "message" => $e->getMessage()]);
        }
    }

    // -------------------------------
    // DELETE USER
    // -------------------------------
    public function deleteUser() {

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(["success" => false, "message" => "Method not allowed"]);
            return;
        }

        $data = json_decode(file_get_contents("php://input"), true);
        $id = $data["id"] ?? null;

        if (!$id) {
            echo json_encode(["success" => false, "message" => "User ID required"]);
            return;
        }

        try {
            $user = new User();
            $res = $user->delete($id);

            if ($res->getDeletedCount() > 0) {
                echo json_encode(["success" => true, "message" => "User deleted"]);
            } else {
                echo json_encode(["success" => false, "message" => "User not found"]);
            }

        } catch (Exception $e) {
            echo json_encode(["success" => false, "message" => $e->getMessage()]);
        }
    }
}
