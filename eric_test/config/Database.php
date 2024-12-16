<?php


class Database {
    private static $instance = null;
    private $connection;

    private function __construct() {
        // Dùng PDO để kết nối cơ sở dữ liệu
        try {
            $this->connection = new PDO("mysql:host=localhost;dbname=eric_test", "root", "");
            $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            die("Connection failed: " . $e->getMessage());
        }
    }

    public static function getInstance() {
        if (self::$instance == null) {
            self::$instance = new Database();
        }
        return self::$instance->connection;
    }
}
?>
