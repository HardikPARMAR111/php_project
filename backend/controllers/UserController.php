<?php
$modelPath = dirname(__DIR__) . "/models/user.php";
if (!file_exists($modelPath)) {
    die(json_encode(["success" => false, "message" => "User model not found"]));
}
require_once $modelPath;

class UserController {

    // Register User
    public function registerUser() {
        header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
        header("Access-Control-Allow-Headers: Content-Type");
        header("Content-Type: application/json");

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
            echo json_encode(["success" => false, "message" => "Invalid email format"]);
            return;
        }

        try {
            $user = new User();
            if ($user->findByEmail($email)) {
                echo json_encode(["success" => false, "message" => "Email already registered"]);
                return;
            }

            $userData = [
                "name" => $name,
                "email" => $email,
                "password" => password_hash($password, PASSWORD_BCRYPT),
                "role" => "user",
                "created_at" => new MongoDB\BSON\UTCDateTime()
            ];

            $result = $user->register($userData);

            echo json_encode([
                "success" => true,
                "message" => "User registered successfully",
                "id" => (string)$result->getInsertedId()
            ]);

        } catch (Exception $e) {
            echo json_encode(["success" => false, "message" => $e->getMessage()]);
        }
    }

    // Login User
    public function loginUser() {
        header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Methods: POST, OPTIONS");
        header("Access-Control-Allow-Headers: Content-Type");
        header("Content-Type: application/json");

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(["success" => false, "message" => "Method not allowed"]);
            return;
        }

        $data = json_decode(file_get_contents("php://input"), true);
        $email = $data["email"] ?? null;
        $password = $data["password"] ?? null;

        if (!$email || !$password) {
            echo json_encode(["success" => false, "message" => "Email and password are required"]);
            return;
        }

        try {
            $user = new User();
            $userData = $user->findByEmail($email);

            if (!$userData) {
                echo json_encode(["success" => false, "message" => "Invalid credentials"]);
                return;
            }

            if (!password_verify($password, $userData['password'])) {
                echo json_encode(["success" => false, "message" => "Invalid credentials"]);
                return;
            }

            echo json_encode([
                "success" => true,
                "message" => "Login successful",
                "data" => [
                    "id" => (string)$userData['_id'],
                    "name" => $userData['name'],
                    "email" => $userData['email'],
                    "role" => $userData['role']
                ]
            ]);

        } catch (Exception $e) {
            echo json_encode(["success" => false, "message" => $e->getMessage()]);
        }
    }

    // Get all users
    public function getAllUsers() {
        header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
        header("Access-Control-Allow-Headers: Content-Type");
        header("Content-Type: application/json");

        try {
            $user = new User();
            $users = $user->getAll();

            $usersArray = array_map(function($user) {
                $createdAt = isset($user['created_at']) ? $user['created_at']->toDateTime()->format(DATE_ATOM) : null;

                return [
                    'id' => (string)$user['_id'],
                    'name' => $user['name'],
                    'email' => $user['email'],
                    'role' => $user['role'],
                    'created_at' => $createdAt
                ];
            }, $users);

            echo json_encode(["success" => true, "data" => $usersArray]);

        } catch (Exception $e) {
            echo json_encode(["success" => false, "message" => $e->getMessage()]);
        }
    }

    // Delete user
    public function deleteUser() {
        header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Methods: POST, OPTIONS");
        header("Access-Control-Allow-Headers: Content-Type");
        header("Content-Type: application/json");

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(["success" => false, "message" => "Method not allowed"]);
            return;
        }

        $data = json_decode(file_get_contents("php://input"), true);
        $id = $data['id'] ?? null;

        if (!$id) {
            echo json_encode(["success" => false, "message" => "User ID is required"]);
            return;
        }

        try {
            $user = new User();
            $result = $user->delete($id);

            if ($result->getDeletedCount() > 0) {
                echo json_encode(["success" => true, "message" => "User deleted successfully"]);
            } else {
                echo json_encode(["success" => false, "message" => "User not found"]);
            }

        } catch (Exception $e) {
            echo json_encode(["success" => false, "message" => $e->getMessage()]);
        }
    }
}