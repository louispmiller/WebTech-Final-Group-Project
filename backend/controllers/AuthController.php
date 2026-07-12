<?php
// Author: Hugo Morais
// Integrated by: Ojong Bessong NKONGHO
// Adapted constructor to match the team's router pattern
// and updated responses to use the shared Response class.
// Hugo's validation logic and password handling are completely untouched.

require_once __DIR__ . '/../models/UserModel.php';

class AuthController
{
    private $userModel;

    public function __construct($db)
    {
        // create the model here so the router doesn't need to know about it
        $this->userModel = new UserModel($db);
    }

    // POST /api/register
    public function register()
    {
        $body = json_decode(file_get_contents('php://input'), true) ?? [];

        $username = trim($body['username'] ?? '');
        $email    = trim($body['email'] ?? '');
        $password = trim($body['password'] ?? '');

        if (!$username || !$email || !$password) {
            Response::error('All fields are required', 400);
            return;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            Response::error('Invalid email format', 400);
            return;
        }

        if (strlen($password) < 8) {
            Response::error('Password must be at least 8 characters', 400);
            return;
        }

        if ($this->userModel->findByEmail($email)) {
            Response::error('An account with this email already exists', 409);
            return;
        }

        $hashed = password_hash($password, PASSWORD_BCRYPT);
        $userId = $this->userModel->insertUser($username, $email, $hashed);

        Response::success(['user_id' => $userId], 201);
    }

    // POST /api/login
    public function login()
    {
        $body = json_decode(file_get_contents('php://input'), true) ?? [];

        $email    = trim($body['email'] ?? '');
        $password = trim($body['password'] ?? '');

        if (!$email || !$password) {
            Response::error('Email and password are required', 400);
            return;
        }

        $user = $this->userModel->findByEmail($email);

        // intentionally the same error message for wrong email and wrong password
        // giving different messages would let attackers enumerate valid emails
        if (!$user || !password_verify($password, $user['password'])) {
            Response::error('Invalid credentials', 401);
            return;
        }

        $token   = bin2hex(random_bytes(32));
        $expires = date('Y-m-d H:i:s', strtotime('+2 hours'));

        $this->userModel->updateToken($user['id'], $token, $expires);

        Response::success([
            'token'    => $token,
            'user_id'  => $user['id'],
            'username' => $user['username']
        ]);
    }
}