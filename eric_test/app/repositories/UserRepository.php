<?php
require_once __DIR__ . '/../models/User.php';

class UserRepository
{
    private $userModel;

    public function __construct()
    {
        $this->userModel = new User();
    }

    public function findUserByEmail($email)
    {
        return $this->userModel->findUserByEmail($email);
    }

    public function createUser($username, $email, $password)
    {
        return $this->userModel->createUser($username, $email, $password);
    }
}
