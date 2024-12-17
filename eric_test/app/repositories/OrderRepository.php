<?php

require_once __DIR__ . '/../models/Order.php';

class OrderRepository
{
    public function createOrder($userId, $cartItems, $totalAmount)
    {
        // Logic to create an order
        $order = new Order();
        return $order->createOrder($userId, $cartItems, $totalAmount);
    }

    public function cancelOrder($orderId)
    {
        // Logic to cancel an order
        $order = new Order();
        return $order->cancelOrder($orderId);
    }
    public function getOrdersByUserId($userId,$page = 1, $limit = 12)
    {
        $order = new Order();
        return $order->getOrdersByUserId($userId,$page, $limit);
    }
    public function getTotalOrdersByUserId($userId)
    {
        $order = new Order();
        return $order->getTotalOrdersByUserId($userId);
    }
    public function updateOrderStatus($orderId, $status, $userId)
    {
        $order = new Order();
        return $order->updateOrderStatus($orderId, $status, $userId);
    }
}
