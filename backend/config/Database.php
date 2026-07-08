<?php
// Author: Rachid Djamal
// TEMPORARY placeholder shared PDO connection helper.
// Nothing existed in the repo yet, so this was added only so the Current
// Weather Module could be tested locally. This is Student 6's
// responsibility (Backend Architecture & Integration) — please replace or
// adapt this file once you set up the real connection/config approach, and
// delete this note.

class Database
{
    private static ?PDO $instance = null;

    public static function getConnection(): PDO
    {
        if (self::$instance === null) {
            $host = 'localhost';
            $dbname = 'smart_city_dashboard';
            $user = 'root';
            $pass = '';

            try {
                self::$instance = new PDO(
                    "mysql:host={$host};dbname={$dbname};charset=utf8mb4",
                    $user,
                    $pass,
                    [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    ]
                );
            } catch (PDOException $e) {
                http_response_code(500);
                echo json_encode(['error' => 'Database connection failed']);
                exit;
            }
        }

        return self::$instance;
    }
}
