<?php
// app/repositories/ProductRepository.php
class Product
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function createProduct($name, $image, $description, $price, $user_id)
    {
        try {
            $sql = "INSERT INTO products (name, image, description, price, user_id) VALUES (:name, :image, :description, :price, :user_id)";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':name', $name);
            $stmt->bindParam(':image', $image);
            $stmt->bindParam(':description', $description);
            $stmt->bindParam(':price', $price);
            $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
            if (!$stmt->execute()) {
                throw new Exception("Failed to create product");
            }
            return true;
        } catch (PDOException $e) {
            throw new Exception("Database error: " . $e->getMessage());
        }   catch (Exception $e) {
            throw new Exception("Error creating product: " . $e->getMessage());
        }
    }

    public function getAllProducts($page = 1, $limit = 12)
    {
        try {
            $offset = ($page - 1) * $limit;
            $sql = "SELECT * FROM products LIMIT :offset, :limit";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }   catch (PDOException $e) {
            throw new Exception("Database error: " . $e->getMessage());
        } catch (Exception $e) {
            throw new Exception("Error getting all products: " . $e->getMessage());
        }

    }
    public function getTotalProducts()
    {
        try {
            $sql = "SELECT COUNT(*) AS total FROM products";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['total'];
        }  catch (PDOException $e) {
            throw new Exception("Database error: " . $e->getMessage());
        }   catch (Exception $e) {
            throw new Exception("Error getting total products: " . $e->getMessage());
        }
    }

    public function getProductById($id, $user_id = null)
    {
        try {
            // Nếu user_id không được cung cấp, chỉ kiểm tra theo id
            if ($user_id === null) {
                $sql = "SELECT * FROM products WHERE id = :id";
                $stmt = $this->db->prepare($sql);
                $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            } else {
                // Nếu user_id có giá trị, kiểm tra cả id và user_id
                $sql = "SELECT * FROM products WHERE id = :id AND user_id = :user_id";
                $stmt = $this->db->prepare($sql);
                $stmt->bindParam(':id', $id, PDO::PARAM_INT);
                $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
            }
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new Exception("Database error: " . $e->getMessage());
        }   catch (Exception $e) {
            throw new Exception("Error getting product by id: " . $e->getMessage());
        }
    }
}