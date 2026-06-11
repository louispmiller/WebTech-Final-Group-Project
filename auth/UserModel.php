<?php
// Author: Hugo
// Model: UserModel
// Description: Handles all database operations for users

class UserModel {

    private $db;

    public function __construct($database) {
        $this->db = $database;
    }

    // Insère un nouvel utilisateur dans la base
    public function insertUser($username, $email, $hashedPassword) {
        $stmt = $this->db->prepare("
            INSERT INTO users (username, email, password)
            VALUES (?, ?, ?)
        ");
        $stmt->execute([$username, $email, $hashedPassword]);
        return $this->db->lastInsertId();
    }

    // Cherche un utilisateur par son email
    public function findByEmail($email) {
        $stmt = $this->db->prepare("
            SELECT * FROM users WHERE email = ?
        ");
        $stmt->execute([$email]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Cherche un utilisateur par son token
    public function findByToken($token) {
        $stmt = $this->db->prepare("
            SELECT * FROM users WHERE token = ?
            AND expiration_token > NOW()
        ");
        $stmt->execute([$token]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Met à jour le token d'un utilisateur après login
    public function updateToken($userId, $token, $expiration) {
        $stmt = $this->db->prepare("
            UPDATE users
            SET token = ?, expiration_token = ?
            WHERE id = ?
        ");
        $stmt->execute([$token, $expiration, $userId]);
    }
}
?>