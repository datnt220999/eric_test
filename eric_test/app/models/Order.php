<?php
// app/repositories/OrderRepository.php


class Order
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }


    public function createOrder($userId, $cartItems, $totalAmount)
    {
        try {
            // Bắt đầu giao dịch (transaction)
            $this->db->beginTransaction();

            // Tạo đơn hàng
            $sql = "INSERT INTO orders (user_id, total_amount, created_at) VALUES (:user_id, :total_amount, NOW())";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
            $stmt->bindParam(':total_amount', $totalAmount);
            if (!$stmt->execute()) {
                throw new Exception("Failed to create order");
            }

            // Lấy id đơn hàng vừa tạo
            $orderId = $this->db->lastInsertId();

            // Lưu thông tin các sản phẩm trong đơn hàng
            $this->createOrderItems($orderId, $cartItems);
            // Commit giao dịch
            $this->db->commit();
            return $orderId;
        }  catch (PDOException $e) {
            $this->db->rollBack();  // Rollback nếu có lỗi
            throw new Exception("Database error: " . $e->getMessage());
        } catch (Exception $e) {
            $this->db->rollBack();
            throw new Exception("Error creating order: " . $e->getMessage());
        }
    }
    private function createOrderItems($orderId, $cartItems)
    {
        foreach ($cartItems as $item) {
            $sql = "INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (:order_id, :product_id, :quantity, :price)";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':order_id', $orderId, PDO::PARAM_INT);
            $stmt->bindParam(':product_id', $item['id'], PDO::PARAM_INT);
            $stmt->bindParam(':quantity', $item['quantity'], PDO::PARAM_INT);
            $stmt->bindParam(':price', $item['price']);
            if (!$stmt->execute()) {
                throw new Exception("Failed to create order items");
            }
        }
    }



    public function getOrdersByUserId($userId, $page = 1, $limit = 12)
    {
        try {
            $offset = ($page - 1) * $limit;

            $sql = "SELECT o.id AS order_id, o.total_amount, o.created_at, oi.product_id, oi.quantity, oi.price 
                                    FROM orders o 
                                    JOIN order_items oi ON o.id = oi.order_id
                                    WHERE o.user_id = :user_id
                                    ORDER BY o.created_at DESC
                                    LIMIT :limit OFFSET :offset";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();

            $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if (!$orders) {
                return [];
            }
            // Group order items by order_id
            return $this->groupOrderItems($orders);
        }  catch (PDOException $e) {
            throw new Exception("Database error: " . $e->getMessage());
        }  catch (Exception $e) {
            throw new Exception("Error getting order by user id: " . $e->getMessage());
        }
    }

    private function groupOrderItems($orders) {
        $groupedOrders = [];
        foreach ($orders as $order) {
            $orderId = $order['order_id'];
            if (!isset($groupedOrders[$orderId])) {
                $groupedOrders[$orderId] = [
                    'order_id' => $orderId,
                    'total_amount' => $order['total_amount'],
                    'created_at' => $order['created_at'],
                    'items' => []
                ];
            }
            $groupedOrders[$orderId]['items'][] = [
                'product_id' => $order['product_id'],
                'quantity' => $order['quantity'],
                'price' => $order['price']
            ];
        }
        return array_values($groupedOrders);
    }
    public function getTotalOrdersByUserId($userId)
    {
        try {
            $sql = "SELECT COUNT(DISTINCT o.id) FROM orders o WHERE o.user_id = :user_id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['COUNT(DISTINCT o.id)'];
        } catch (PDOException $e) {
            throw new Exception("Database error: " . $e->getMessage());
        }  catch (Exception $e) {
            throw new Exception("Error getting total order by user id: " . $e->getMessage());
        }
    }


    public function updateOrderStatus($orderId, $status, $userId)
    {
        try {
            // Kiểm tra nếu đơn hàng thuộc về người dùng này
            $sql = "SELECT user_id FROM orders WHERE id = :order_id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':order_id', $orderId, PDO::PARAM_INT);
            $stmt->execute();
            $order = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$order || $order['user_id'] != $userId) {
                return false; // Nếu đơn hàng không thuộc người dùng, trả về false
            }
            // Cập nhật trạng thái của đơn hàng
            $sql = "UPDATE orders SET status = :status WHERE id = :order_id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':status', $status);
            $stmt->bindParam(':order_id', $orderId, PDO::PARAM_INT);
            if (!$stmt->execute()) {
                throw new Exception("Failed to update order status");
            }
            return true; // Nếu cập nhật thành công, trả về true
        }  catch (PDOException $e) {
            throw new Exception("Database error: " . $e->getMessage());
        } catch (Exception $e) {
            throw new Exception("Error updating order status: " . $e->getMessage());
        }
    }

}