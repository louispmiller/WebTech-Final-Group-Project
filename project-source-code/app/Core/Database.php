<?php

namespace App\Core;

use PDO;
use PDOException;

class Database
{
    private static ?PDO $instance = null;

    public static function connection(): PDO
    {
        if (self::$instance !== null) {
            return self::$instance;
        }

        $driver = Env::get('DB_DRIVER', 'mysql');

        try {
            if ($driver === 'sqlite') {
                $path = Env::get('DB_PATH', ':memory:');
                $pdo = new PDO("sqlite:{$path}");
            } else {
                $host = Env::get('DB_HOST', '127.0.0.1');
                $port = Env::get('DB_PORT', '3306');
                $name = Env::get('DB_NAME', 'smart_city_dashboard');
                $dsn = "mysql:host={$host};port={$port};dbname={$name};charset=utf8mb4";
                $pdo = new PDO($dsn, Env::get('DB_USER', 'root'), Env::get('DB_PASS', ''));
            }
        } catch (PDOException $e) {
            throw new PDOException('Database connection failed: ' . $e->getMessage(), (int) $e->getCode());
        }

        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

        self::$instance = $pdo;
        return self::$instance;
    }

    public static function reset(): void
    {
        self::$instance = null;
    }
}
