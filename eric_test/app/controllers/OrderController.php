<?php
require_once __DIR__ . '/../repositories/OrderRepository.php';
class OrderController
{
    private $orderRepository;

    // Constructor to inject the OrderRepository
    public function __construct()
    {
        $this->orderRepository = new OrderRepository();
    }

    public function listOrders()
    {
        try {
            // Get orders from the OrderRepository
            $orders = $this->orderRepository->getOrdersByUserId($_SESSION['user_id']);

            if (empty($orders)) {
                Response::send(404, "No orders found for this user");
                return;
            }

            // Return the list of orders
            Response::send(200, "Orders retrieved successfully", [
                "orders" => $orders
            ]);
        } catch (PDOException $e) {
            // Database error
            Response::send(500, "Database error: " . $e->getMessage());
        } catch (Exception $e) {
            // General error
            Response::send(500, "An error occurred: " . $e->getMessage());
        }
    }

    public function updateStatus()
    {
        try {
            // Get input data
            $input = json_decode(file_get_contents('php://input'), true);

            // Validate input
            if (!isset($input['order_id']) || !isset($input['status'])) {
                Response::send(400, "Order ID and status are required");
                return;
            }

            $orderId = $input['order_id'];
            $status = $input['status'];

            // Check if the status is valid
            $validStatuses = ['pending', 'processing', 'shipped', 'completed', 'cancelled'];
            if (!in_array($status, $validStatuses)) {
                Response::send(400, "Invalid status value");
                return;
            }

            // Update order status via the OrderRepository
            $updateResult = $this->orderRepository->updateOrderStatus($orderId, $status, $_SESSION['user_id']);

            if (!$updateResult) {
                Response::send(500, "Failed to update order status");
                return;
            }

            Response::send(200, "Order status updated successfully");
        } catch (PDOException $e) {
            // Database error
            Response::send(500, "Database error: " . $e->getMessage());
        } catch (Exception $e) {
            // General error
            Response::send(500, "An error occurred: " . $e->getMessage());
        }
    }
}
