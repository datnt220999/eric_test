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
    public function getOrdersByUserId($userId)
    {
        $order = new Order();
        return $order->getOrdersByUserId($userId);
    }

    public function updateOrderStatus($orderId, $status, $userId)
    {
        $order = new Order();
        return $order->updateOrderStatus($orderId, $status, $userId);
    }
}
