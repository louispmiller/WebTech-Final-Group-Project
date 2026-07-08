<?php
// Author: Ojong Bessong NKONGHO
// Adapted from Rachid Djamal's temporary placeholder.
// Main change: credentials now come from config.php instead of being
// hardcoded here. Everything else kept the same because his singleton
// pattern was already the right approach.

require_once __DIR__ . '/../core/Response.php';

class Database
{
    // single PDO instance shared across the whole request
    private static $instance = null;

    public static function getConnection()
    {
        if (self::$instance === null) {
            $config = require __DIR__ . '/config.php';
            $db     = $config['db'];

            try {
                self::$instance = new PDO(
                    "mysql:host={$db['host']};dbname={$db['dbname']};charset={$db['charset']}",
                    $db['user'],
                    $db['pass'],
                    [
                        // throw exceptions on SQL errors instead of silent failures
                        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                        // return rows as associative arrays by default
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    ]
                );
            } catch (PDOException $e) {
                // we deliberately don't expose the actual error message to the client
                // because it could leak database structure or credentials
                Response::error('Database connection failed', 500);
                exit;
            }
        }

        return self::$instance;
    }
}