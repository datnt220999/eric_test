<?php
// config/Database.php


class Database
{
    private static $instance = null;
    private $connection;

    private function __construct()
    {
        // Use constants defined in Const.php
        $host = HOST;
        $dbname = DBNAME;
        $username = USERNAME;
        $password = PASSWORD;

        // Dùng PDO để kết nối cơ sở dữ liệu
        try {
            $this->connection = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
            $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->connection->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            die("Connection failed: " . $e->getMessage());
        }
    }

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new Database();
        }
        return self::$instance->connection;
    }
}