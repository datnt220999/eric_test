<?php

class Order {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }
    public function createOrder($userId, $cartItems, $totalAmount)
    {
        try {
            // Bắt đầu giao dịch (transaction)
            $this->db->beginTransaction();

            // Tạo đơn hàng
            $stmt = $this->db->prepare("INSERT INTO orders (user_id, total_amount, created_at) VALUES (:user_id, :total_amount, NOW())");
            $stmt->bindParam(':user_id', $userId);
            $stmt->bindParam(':total_amount', $totalAmount);
            $stmt->execute();

            // Lấy id đơn hàng vừa tạo
            $orderId = $this->db->lastInsertId();

            // Lưu thông tin các sản phẩm trong đơn hàng
            foreach ($cartItems as $item) {
                $stmt = $this->db->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (:order_id, :product_id, :quantity, :price)");
                $stmt->bindParam(':order_id', $orderId);
                $stmt->bindParam(':product_id', $item['id']);
                $stmt->bindParam(':quantity', $item['quantity']);
                $stmt->bindParam(':price', $item['price']);
                $stmt->execute();
            }

            // Commit giao dịch
            $this->db->commit();

            return $orderId;

        } catch (PDOException $e) {
            $this->db->rollBack();  // Rollback nếu có lỗi
            throw new Exception("Database error: " . $e->getMessage());
        }
    }
    public function getOrdersByUserId($userId)
    {
        try {
            $stmt = $this->db->prepare("SELECT o.id AS order_id, o.total_amount, o.created_at, oi.product_id, oi.quantity, oi.price 
                                    FROM orders o 
                                    JOIN order_items oi ON o.id = oi.order_id
                                    WHERE o.user_id = :user_id
                                    ORDER BY o.created_at DESC");

            $stmt->bindParam(':user_id', $userId);
            $stmt->execute();

            $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (!$orders) {
                return [];
            }

            // Group order items by order_id
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

            return array_values($groupedOrders);  // Return grouped orders

        } catch (PDOException $e) {
            throw new Exception("Database error: " . $e->getMessage());
        }
    }
    public function updateOrderStatus($orderId, $status, $userId)
    {
        try {
            // Kiểm tra nếu đơn hàng thuộc về người dùng này
            $stmt = $this->db->prepare("SELECT user_id FROM orders WHERE id = :order_id");
            $stmt->bindParam(':order_id', $orderId);
            $stmt->execute();
            $order = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$order || $order['user_id'] != $userId) {
                return false; // Nếu đơn hàng không thuộc người dùng, trả về false
            }

            // Cập nhật trạng thái của đơn hàng
            $stmt = $this->db->prepare("UPDATE orders SET status = :status WHERE id = :order_id");
            $stmt->bindParam(':status', $status);
            $stmt->bindParam(':order_id', $orderId);
            $stmt->execute();

            return true; // Nếu cập nhật thành công, trả về true

        } catch (PDOException $e) {
            throw new Exception("Database error: " . $e->getMessage());
        }
    }


}