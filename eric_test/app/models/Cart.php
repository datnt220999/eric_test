<?php
// app/repositories/CartRepository.php


class Cart
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }


    public function addToCart($userId, $productId, $quantity)
    {
        try {
            $sql = "
                    INSERT INTO carts (user_id, product_id, quantity) 
                    VALUES (:user_id, :product_id, :quantity)
                    ON DUPLICATE KEY UPDATE quantity = quantity + :quantity
                ";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
            $stmt->bindParam(':product_id', $productId, PDO::PARAM_INT);
            $stmt->bindParam(':quantity', $quantity, PDO::PARAM_INT);
            if (!$stmt->execute()) {
                throw new Exception("Failed to add product to cart");
            }
            return true;
        } catch (PDOException $e) {
            throw new Exception("Database error: " . $e->getMessage());
        } catch (Exception $e) {
            throw new Exception("Error adding product to cart: " . $e->getMessage());
        }
    }


    public function getCartItems($user_id)
    {
        try {
            $sql = "SELECT c.*, p.name, p.image,p.price FROM carts c 
                JOIN products p ON c.product_id = p.id 
                WHERE c.user_id = :user_id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new Exception("Database error: " . $e->getMessage());
        }  catch (Exception $e) {
            throw new Exception("Error getting cart items: " . $e->getMessage());
        }
    }
    public function getCartItemByProductAndUser($user_id, $product_id)
    {
        try {
            $sql = "SELECT * FROM carts WHERE user_id = :user_id AND product_id = :product_id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
            $stmt->bindParam(':product_id', $product_id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new Exception("Database error: " . $e->getMessage());
        }  catch (Exception $e) {
            throw new Exception("Error getting cart item by product and user: " . $e->getMessage());
        }
    }
    public function updateCartItemQuantity($user_id, $product_id, $new_quantity)
    {
        try {
            $sql = "UPDATE carts SET quantity = :quantity WHERE user_id = :user_id AND product_id = :product_id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':quantity', $new_quantity, PDO::PARAM_INT);
            $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
            $stmt->bindParam(':product_id', $product_id, PDO::PARAM_INT);
            if (!$stmt->execute()) {
                throw new Exception("Failed to update cart item quantity");
            }
            return true;
        } catch (PDOException $e) {
            throw new Exception("Database error: " . $e->getMessage());
        }  catch (Exception $e) {
            throw new Exception("Error updating cart item quantity: " . $e->getMessage());
        }
    }
    public function clearCart($userId)
    {
        try {
            $stmt = $this->db->prepare("DELETE FROM carts WHERE user_id = :user_id");
            $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
            if (!$stmt->execute()) {
                throw new Exception("Failed to clear cart");
            }
            return true;
        } catch (PDOException $e) {
            throw new Exception("Database error: " . $e->getMessage());
        }  catch (Exception $e) {
            throw new Exception("Error clearing cart: " . $e->getMessage());
        }
    }
}