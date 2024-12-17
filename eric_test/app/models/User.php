<?php

class User {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function createUser($name, $email, $password) {
        // Câu lệnh SQL để chèn người dùng mới
        $sql = "INSERT INTO users (name, email, password) VALUES (:name, :email, :password)";

        // Chuẩn bị câu lệnh SQL
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':password', $password);

        // Thực thi câu lệnh SQL
        if ($stmt->execute()) {
            // Lấy ID của bản ghi mới được chèn
            $lastInsertId = $this->db->lastInsertId();

            // Truy vấn lại bản ghi mới được chèn vào dựa trên ID vừa lấy
            $sql = "SELECT * FROM users WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id', $lastInsertId);
            $stmt->execute();

            // Trả về bản ghi mới chèn
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } else {
            // Nếu không thành công, trả về false
            return false;
        }
    }


    public function findUserByEmail($email,$select = '*') {
        $sql = "SELECT {$select} FROM users WHERE email = :email LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}