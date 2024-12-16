<?php

class Cart {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function addToCart($userId, $productId, $quantity)
    {
        $stmt = $this->db->prepare("
            INSERT INTO carts (user_id, product_id, quantity) 
            VALUES (:user_id, :product_id, :quantity)
            ON DUPLICATE KEY UPDATE quantity = quantity + :quantity
        ");
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindParam(':product_id', $productId, PDO::PARAM_INT);
        $stmt->bindParam(':quantity', $quantity, PDO::PARAM_INT);

        return $stmt->execute();
    }

    public function getCartItems($user_id) {
        $sql = "SELECT c.*, p.name, p.image,p.price FROM carts c 
                JOIN products p ON c.product_id = p.id 
                WHERE c.user_id = :user_id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function getCartItemByProductAndUser($user_id, $product_id) {
        $sql = "SELECT * FROM carts WHERE user_id = :user_id AND product_id = :product_id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->bindParam(':product_id', $product_id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC); // Trả về dữ liệu giỏ hàng hoặc null nếu không có
    }
    public function updateCartItemQuantity($user_id, $product_id, $new_quantity) {
        $sql = "UPDATE carts SET quantity = :quantity WHERE user_id = :user_id AND product_id = :product_id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':quantity', $new_quantity, PDO::PARAM_INT);
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->bindParam(':product_id', $product_id, PDO::PARAM_INT);
        return $stmt->execute();
    }
    public function clearCart($userId)
    {
        try {
            $stmt = $this->db->prepare("DELETE FROM carts WHERE user_id = :user_id");
            $stmt->bindParam(':user_id', $userId);
            $stmt->execute();
        } catch (PDOException $e) {
            throw new Exception("Database error: " . $e->getMessage());
        }
    }



}