
<?php
require_once __DIR__ . '/../services/AuthService.php';
require_once __DIR__ . '/../repositories/UserRepository.php';
require_once __DIR__ . '/../utils/Response.php';

class AuthController
{
    private $authService;

    public function __construct()
    {
        $userRepository = new UserRepository();
        $this->authService = new AuthService($userRepository);
    }

    public function register()
    {
        try {
            $data = json_decode(file_get_contents("php://input"), true);

             if (!is_array($data)) {
                Response::send(400, 'Invalid input data. Please provide JSON data.');
                return;
            }
            $result = $this->authService->register($data);

            if ($result['status']) {
                Response::send(200, 'Registration successful.', ['token' => $result['token']]);
            } else {
                Response::send(400, $result['message']);
            }
        } catch (Exception $e) {
             Response::send(500, 'An error occurred during registration: ' . $e->getMessage());
        }
    }

    public function login()
    {
        try {
            $data = json_decode(file_get_contents("php://input"), true);
            if (!is_array($data)) {
               Response::send(400, 'Invalid input data. Please provide JSON data.');
                return;
            }
            $result = $this->authService->login($data);

            if ($result['status']) {
                Response::send(200, 'Login successful.', ['token' => $result['token']]);
            } else {
                Response::send(401, $result['message']);
            }
        } catch (Exception $e) {
             Response::send(500, 'An error occurred during login: ' . $e->getMessage());
        }
    }
}
