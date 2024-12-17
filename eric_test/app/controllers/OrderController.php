<?php
// app/controllers/OrderController.php

require_once __DIR__ . '/../repositories/OrderRepository.php';
require_once __DIR__ . '/../utils/Response.php';

class OrderController
{
    private $orderRepository;
    private $validStatuses = ['pending', 'processing', 'shipped', 'completed', 'cancelled'];

    public function __construct()
    {
        $this->orderRepository = new OrderRepository();
    }

    public function listOrders()
    {
        try {
            // Get orders from the OrderRepository
            $data = $_GET;
            $page = isset($data['page']) ? (int)$data['page'] : 1;
            $limit = isset($data['limit']) ? (int)$data['limit'] : 12;

            $userId = $this->getUserIdFromSession();

            $orders = $this->orderRepository->getOrdersByUserId($userId,$page, $limit);

//            if (empty($orders)) {
//                Response::send(404, "No orders found for this user");
//                return;
//            }

            // Lấy tổng số sản phẩm (để tính tổng số trang)
            $totalOrders = $this->orderRepository->getTotalOrdersByUserId($userId);
            $totalPages = ceil($totalOrders / $limit);
            // Return the list of orders
            Response::send(200, "Orders retrieved successfully", [
                "orders" => $orders,
                    'pagination' => [
                    'current_page' => $page,
                    'total_pages' => $totalPages,
                    'total_orders' => $totalOrders,
                    'limit' => $limit
                ]
            ]);
        }  catch (InvalidArgumentException $e) {
            Response::send(400, $e->getMessage());
        }
        catch (PDOException $e) {
            // Database error
            Response::send(500, "Database error: " . $e->getMessage());
        }
        catch (Exception $e) {
            // General error
            Response::send(500, "An error occurred: " . $e->getMessage());
        }
    }
    private function getUserIdFromSession(){
        if (!isset($_SESSION['user_id'])) {
            throw new InvalidArgumentException("User not authenticated");
        }
        return  $_SESSION['user_id'];
    }

    public function updateStatus()
    {
        try {
            $input = $this->getJsonInput();
            // Validate input
            $this->validateUpdateStatusInput($input);


            $orderId = $input['order_id'];
            $status = $input['status'];
            $userId = $this->getUserIdFromSession();

            // Update order status via the OrderRepository
            $updateResult = $this->orderRepository->updateOrderStatus($orderId, $status, $userId);

            if (!$updateResult) {
                Response::send(500, "Failed to update order status");
                return;
            }

            Response::send(200, "Order status updated successfully");
        } catch (InvalidArgumentException $e) {
            Response::send(400, $e->getMessage());
        }
        catch (PDOException $e) {
            // Database error
            Response::send(500, "Database error: " . $e->getMessage());
        }
        catch (Exception $e) {
            // General error
            Response::send(500, "An error occurred: " . $e->getMessage());
        }
    }
    private function getJsonInput()
    {
        $input = json_decode(file_get_contents('php://input'), true);
        if (!is_array($input)) {
            throw new InvalidArgumentException("Invalid input. Must be a JSON.");
        }
        return $input;
    }
    private function validateUpdateStatusInput($input)
    {
        if (!isset($input['order_id']) || !isset($input['status'])) {
            throw new InvalidArgumentException("Order ID and status are required");
        }

        if (!in_array($input['status'], $this->validStatuses)) {
            throw new InvalidArgumentException("Invalid status value");
        }
    }
}