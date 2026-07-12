<?php
// Author: Ojong Bessong NKONGHO
// Protects routes that require a logged-in user.
// Written as part of the backend architecture integration (Student 6).
// Works with Hugo's UserModel - token gets set on login
// and expires after 2 hours.
//
// Usage in any controller method:
//   $user = AuthMiddleware::require();
//   // if we reach this line the user is authenticated

class AuthMiddleware
{
    public static function require()
    {
        $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? '';

        // Authorization header must start with "Bearer "
        if (strpos($authHeader, 'Bearer ') !== 0) {
            Response::error('Unauthorised - missing or malformed token', 401);
        }

        $token = trim(substr($authHeader, 7));

        if (empty($token)) {
            Response::error('Unauthorised - token is empty', 401);
        }

        $db   = Database::getConnection();
        $stmt = $db->prepare(
            'SELECT id, username, email, expiration_token
             FROM users
             WHERE token = ?
             LIMIT 1'
        );
        $stmt->execute([$token]);
        $user = $stmt->fetch();

        if (!$user) {
            Response::error('Unauthorised - token not recognised', 401);
        }

        // check the token hasn't passed its expiry time
        // Hugo sets expiration to +2 hours from login time
        if (!empty($user['expiration_token'])) {
            if (strtotime($user['expiration_token']) < time()) {
                Response::error('Unauthorised - token has expired, please log in again', 401);
            }
        }

        // return the user row so the controller can use it if needed
        return $user;
    }
}