<?php
$modelPath = dirname(__DIR__) . "/models/user.php";
if (!file_exists($modelPath)) {
    die(json_encode(["success" => false, "message" => "User model not found"]));
}
require_once $modelPath;

class UserController {

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

        // Validate email format
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(["success" => false, "message" => "Invalid email format"]);
            return;
        }

        try {
            $user = new User();
            
            // Check if user already exists
            $existingUser = $user->findByEmail($email);
            if ($existingUser) {
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
            echo json_encode(["success" => false, "message" => "Error: " . $e->getMessage()]);
        }
    }

    public function loginUser() {
        header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
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
                echo json_encode(["success" => false, "message" => "Invalid email or password"]);
                return;
            }

            // Verify password
            if (!password_verify($password, $userData['password'])) {
                echo json_encode(["success" => false, "message" => "Invalid email or password"]);
                return;
            }

            // Return user data (without password)
            echo json_encode([
                "success" => true,
                "message" => "Login successful",
                "user" => [
                    "id" => (string)$userData['_id'],
                    "name" => $userData['name'],
                    "email" => $userData['email'],
                    "role" => $userData['role']
                ]
            ]);
        } catch (Exception $e) {
            echo json_encode(["success" => false, "message" => "Error: " . $e->getMessage()]);
        }
    }

    public function getAllUsers() {
        header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
        header("Access-Control-Allow-Headers: Content-Type");
        header("Content-Type: application/json");

        try {
            $user = new User();
            $users = $user->getAll();
            
            $usersArray = array_map(function($user) {
                return [
                    'id' => (string)$user['_id'],
                    'name' => $user['name'],
                    'email' => $user['email'],
                    'role' => $user['role'],
                    'created_at' => isset($user['created_at']) ? (string)$user['created_at'] : null
                ];
            }, $users);

            echo json_encode(["success" => true, "data" => $usersArray]);
        } catch (Exception $e) {
            echo json_encode(["success" => false, "message" => "Error: " . $e->getMessage()]);
        }
    }
}