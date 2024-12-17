<?php
// app/repositories/UserRepository.php

class User
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function createUser($name, $email, $password)
    {
        try {
            // Câu lệnh SQL để chèn người dùng mới
            $sql = "INSERT INTO users (name, email, password) VALUES (:name, :email, :password)";

            // Chuẩn bị câu lệnh SQL
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':name', $name);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':password', $password);

            // Thực thi câu lệnh SQL
            if (!$stmt->execute()) {
                throw new Exception("Failed to create user");
            }

            // Lấy ID của bản ghi mới được chèn
            $lastInsertId = $this->db->lastInsertId();

            // Truy vấn lại bản ghi mới được chèn vào dựa trên ID vừa lấy
            $sql = "SELECT * FROM users WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id', $lastInsertId, PDO::PARAM_INT); // Bind id with INT type
            $stmt->execute();
            $user =  $stmt->fetch(PDO::FETCH_ASSOC);
            if(!$user){
                throw new Exception("Failed to fetch created user");
            }
            return $user;
        } catch (PDOException $e) {
            throw new Exception("Database error: " . $e->getMessage());
        }  catch (Exception $e) {
            throw new Exception("Error creating user: " . $e->getMessage());
        }
    }

    public function findUserByEmail($email, $select = '*')
    {
        try {
            $sql = "SELECT {$select} FROM users WHERE email = :email LIMIT 1";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':email', $email);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new Exception("Database error: " . $e->getMessage());
        }  catch (Exception $e) {
            throw new Exception("Error finding user by email: " . $e->getMessage());
        }
    }
    public function findUserById($id, $select = '*')
    {
        try {
            $sql = "SELECT {$select} FROM users WHERE id = :id LIMIT 1";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new Exception("Database error: " . $e->getMessage());
        }   catch (Exception $e) {
            throw new Exception("Error finding user by id: " . $e->getMessage());
        }
    }
}