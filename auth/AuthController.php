<?php
// Author: Hugo
// Controller: AuthController
// Description: Handles register and login logic

class AuthController {

    private $userModel;

    public function __construct($userModel) {
        $this->userModel = $userModel;
    }

    // POST /api/register
    public function register() {

        $data = json_decode(file_get_contents("php://input"), true);

        $username = trim($data['username'] ?? '');
        $email    = trim($data['email'] ?? '');
        $password = trim($data['password'] ?? '');

        if (empty($username) || empty($email) || empty($password)) {
            http_response_code(400);
            echo json_encode(['error' => 'All fields are required']);
            return;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid email format']);
            return;
        }

        if (strlen($password) < 8) {
            http_response_code(400);
            echo json_encode(['error' => 'Password must be at least 8 characters']);
            return;
        }

        $existingUser = $this->userModel->findByEmail($email);
        if ($existingUser) {
            http_response_code(409);
            echo json_encode(['error' => 'Email already exists']);
            return;
        }

        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
        $userId = $this->userModel->insertUser($username, $email, $hashedPassword);

        http_response_code(201);
        echo json_encode([
            'success' => true,
            'data' => [
                'user_id' => $userId
            ]
        ]);
    }

    // POST /api/login
    public function login() {

        $data = json_decode(file_get_contents("php://input"), true);

        $email    = trim($data['email'] ?? '');
        $password = trim($data['password'] ?? '');

        if (empty($email) || empty($password)) {
            http_response_code(400);
            echo json_encode(['error' => 'All fields are required']);
            return;
        }

        $user = $this->userModel->findByEmail($email);
        if (!$user) {
            http_response_code(401);
            echo json_encode(['error' => 'Invalid credentials']);
            return;
        }

        if (!password_verify($password, $user['password'])) {
            http_response_code(401);
            echo json_encode(['error' => 'Invalid credentials']);
            return;
        }

        $token      = bin2hex(random_bytes(32));
        $expiration = date('Y-m-d H:i:s', strtotime('+2 hours'));

        $this->userModel->updateToken($user['id'], $token, $expiration);

        http_response_code(200);
        echo json_encode([
            'success' => true,
            'data' => [
                'token'    => $token,
                'user_id'  => $user['id'],
                'username' => $user['username']
            ]
        ]);
    }
}
?>