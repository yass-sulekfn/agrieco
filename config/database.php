<?php

class Database
{
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


