<?php
require_once __DIR__ . '/../models/User.php';
class AuthController
{
    public function register()
    {
        $data = json_decode(file_get_contents("php://input"), true);
        if (empty($data['username']) || empty($data['password'])) {
            Response::send(400, "Username and password are required.");
            return;
        }
        // Validate username
        if (empty($data['username']) || strlen($data['username']) < 3) {
            Response::send(400, "Username must be at least 3 characters.");
            return;
        }

        // Validate password
        if (empty($data['password']) || strlen($data['password']) < 8) {
            Response::send(400, "Password must be at least 8 characters.");
            return;
        }

        if (!preg_match("/[A-Za-z]/", $data['password']) || !preg_match("/[0-9]/", $data['password'])) {
            Response::send(400, "Password must contain both letters and numbers.");
            return;
        }
        if (empty($data['email']) || !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            Response::send(400, "A valid email is required.");
            return;
        }

        $userModel = new User();
        $existingUser = $userModel->findUserByEmail($data['email']);
        if ($existingUser) {
            Response::send(400, "Email is already taken.");
            return;
        }
        $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);
        $user = [
            'name' => $data['username'],
            'email' => $data['email'],
            'password' => $hashedPassword
        ];
        $createUser = $userModel->createUser($user['name'], $user['email'], $user['password']);
        if (!$createUser) {
            Response::send(500, "Failed to create user.");
            return;
        }
        $userId = $createUser['id'];
        $payload = [
            'sub' => $userId,
            'username' => $user['name'],
            'email' => $user['email'],
            'iat' => time(),
            'exp' => time() + 3600
        ];
        $secretKey = KEY;
        $jwt = JWT::encode($payload, $secretKey);
        Response::send(200,'Success', ['token' => $jwt]);
    }
    public function login() {
        $data = json_decode(file_get_contents("php://input"), true);
        if (empty($data['email']) || empty($data['password'])) {
            Response::send(400, "Email and password are required.");
            return;
        }
        $userModel = new User();
        $user = $userModel->findUserByEmail($data['email']);
        if (!$user || !password_verify($data['password'], $user['password'])) {
            Response::send(401, "Invalid email or password.");
            return;
        }
        $payload = [
            'sub' => $user['id'],
            'email' => $user['email'],
            'iat' => time(),
            'exp' => time() + 3600
        ];
        $secretKey = KEY;
        $jwt = JWT::encode($payload, $secretKey);
        Response::send(200,'Success', ['token' => $jwt]);
    }
}
