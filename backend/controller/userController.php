<?php
require_once __DIR__ . "/../models/User.php";

class UserController {

    public function registerUser() {
        $data = [
            "name" => $_POST["name"],
            "email" => $_POST["email"],
            "password" => password_hash($_POST["password"], PASSWORD_BCRYPT),
            "role" => "user"
        ];

        $user = new User();
        $user->register($data);

        echo json_encode(["success" => true, "message" => "User registered successfully"]);
    }
}
