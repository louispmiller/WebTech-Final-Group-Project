<?php
// Author: Hugo Morais
// Integrated by: Ojong Bessong NKONGHO
// Model handles all database operations for the users table.
// Copied from Hugo's branch unchanged - his SQL logic was correct,
// no modifications needed here.

class UserModel
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    // insert a new user into the database after registration
    public function insertUser($username, $email, $hashedPassword)
    {
        $stmt = $this->db->prepare(
            'INSERT INTO users (username, email, password)
             VALUES (?, ?, ?)'
        );
        $stmt->execute([$username, $email, $hashedPassword]);
        return $this->db->lastInsertId();
    }

    // find a user by email - used during login and duplicate check
    public function findByEmail($email)
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM users WHERE email = ? LIMIT 1'
        );
        $stmt->execute([$email]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // find a user by their auth token - used by AuthMiddleware
    public function findByToken($token)
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM users
             WHERE token = ?
             AND expiration_token > NOW()
             LIMIT 1'
        );
        $stmt->execute([$token]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // update token after successful login
    public function updateToken($userId, $token, $expiration)
    {
        $stmt = $this->db->prepare(
            'UPDATE users
             SET token = ?, expiration_token = ?
             WHERE id = ?'
        );
        $stmt->execute([$token, $expiration, $userId]);
    }
}