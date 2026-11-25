<?php

class Database
{
<<<<<<< HEAD
    private static ?PDO $connexion = null;

    private function __construct()
    {
    }

    private function __clone()
    {
    }

    public function __wakeup()
    {
        throw new RuntimeException('Cannot unserialize a singleton.');
    }

    public static function getConnexion(): PDO
    {
        if (self::$connexion === null) {
            try {
                self::$connexion = new PDO(
                    'mysql:host=localhost;dbname=agrieco;charset=utf8mb4',
                    'root',
                    '',
                    [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                        PDO::ATTR_EMULATE_PREPARES => false,
                    ]
                );
            } catch (PDOException $e) {
                throw new RuntimeException('Database connection failed.');
            }
        }

        return self::$connexion;
    }
}
=======
    private static $instance = null;
    private $connection;

    private function __construct()
    {
        try {
            $this->connection = new PDO(
                "mysql:host=localhost;dbname=agrieco;charset=utf8mb4",
                "root",
                ""
            );

            $this->connection->setAttribute(
                PDO::ATTR_ERRMODE,
                PDO::ERRMODE_EXCEPTION
            );

            $this->connection->setAttribute(
                PDO::ATTR_EMULATE_PREPARES,
                false
            );

        } catch (PDOException $e) {
            die("Database connection failed.");
        }
    }

    public static function getInstance()
    {
        if (self::$instance == null) {
            self::$instance = new Database();
        }

        return self::$instance;
    }

    public function getConnection()
    {
        return $this->connection;
    }
}


>>>>>>> origin/main
