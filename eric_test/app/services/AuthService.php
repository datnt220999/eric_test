<?php
require_once __DIR__ . '/../repositories/UserRepository.php';
class AuthService
{
    private $userRepository;
    private $secretKey;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
        $this->secretKey = KEY; // Secret key for JWT
    }

    public function register($data)
    {
        // Validate input
        if (empty($data['username']) || strlen($data['username']) < 3) {
            return ['status' => false, 'message' => 'Username must be at least 3 characters.'];
        }

        if (empty($data['password']) || strlen($data['password']) < 8 ||
            !preg_match("/[A-Za-z]/", $data['password']) || !preg_match("/[0-9]/", $data['password'])) {
            return ['status' => false, 'message' => 'Password must be at least 8 characters, with letters and numbers.'];
        }

        if (empty($data['email']) || !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            return ['status' => false, 'message' => 'A valid email is required.'];
        }

        // Check for existing user
        if ($this->userRepository->findUserByEmail($data['email'])) {
            return ['status' => false, 'message' => 'Email is already taken.'];
        }

        // Create user
        $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);
        $createUser = $this->userRepository->createUser($data['username'], $data['email'], $hashedPassword);

        if (!$createUser) {
            return ['status' => false, 'message' => 'Failed to create user.'];
        }

        // Generate token
        $payload = [
            'sub' => $createUser['id'],
            'username' => $data['username'],
            'email' => $data['email'],
            'iat' => time(),
            'exp' => time() + 3600
        ];
        $token = JWT::encode($payload, $this->secretKey);

        return ['status' => true, 'token' => $token];
    }

    public function login($data)
    {
        if (empty($data['email']) || empty($data['password'])) {
            return ['status' => false, 'message' => 'Email and password are required.'];
        }

        $user = $this->userRepository->findUserByEmail($data['email']);
        if (!$user || !password_verify($data['password'], $user['password'])) {
            return ['status' => false, 'message' => 'Invalid email or password.'];
        }

        // Generate token
        $payload = [
            'sub' => $user['id'],
            'email' => $user['email'],
            'iat' => time(),
            'exp' => time() + 3600
        ];
        $token = JWT::encode($payload, $this->secretKey);

        return ['status' => true, 'token' => $token];
    }
}
