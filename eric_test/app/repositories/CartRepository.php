<?php

require_once __DIR__ . '/../models/Cart.php';

class CartRepository
{
    public function addToCart($userId, $productId, $quantity)
    {
        // Logic to add product to cart
        $cart = new Cart();
        return $cart->addToCart($userId, $productId, $quantity);
    }

    public function getCartItemByProductAndUser($userId, $productId)
    {
        // Logic to get cart item by user and product
        $cart = new Cart();
        return $cart->getCartItemByProductAndUser($userId, $productId);
    }

    public function getCartItems($userId)
    {
        // Logic to get all cart items for the user
        $cart = new Cart();
        return $cart->getCartItems($userId);
    }

    public function updateCartItemQuantity($userId, $productId, $quantity)
    {
        // Logic to update the quantity of a cart item
        $cart = new Cart();
        return $cart->updateCartItemQuantity($userId, $productId, $quantity);
    }

    public function clearCart($userId)
    {
        // Logic to clear the user's cart
        $cart = new Cart();
        return $cart->clearCart($userId);
    }
}
