<?php
// app/controllers/CartController.php

require_once __DIR__ . '/../repositories/ProductRepository.php';
require_once __DIR__ . '/../repositories/CartRepository.php';
require_once __DIR__ . '/../repositories/OrderRepository.php';
require_once __DIR__ . '/../utils/Response.php'; // Đảm bảo Response.php được include

class CartController
{
    private $cartRepository;
    private $productRepository;
    private $orderRepository;

    public function __construct()
    {
        $this->cartRepository = new CartRepository();
        $this->productRepository = new ProductRepository();
        $this->orderRepository = new OrderRepository();
    }

    public function addToCart()
    {
        try {
            $input = $this->getJsonInput(); // Lấy và decode JSON input

            $this->validateAddToCartInput($input); // Validate input

            $productId = $input['product_id'];
            $quantity = $input['quantity'];

            $product = $this->findProduct($productId); // Tìm sản phẩm
            // Thêm sản phẩm vào giỏ hoặc cập nhật số lượng
            $this->updateOrCreateCartItem($product, $productId, $quantity);
            Response::send(200,"Product add to cart success");

        } catch (InvalidArgumentException $e) {
            Response::send(400, $e->getMessage());
        } catch (Exception $e) {
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

    private function validateAddToCartInput($input)
    {
        if (!isset($input['product_id']) || !isset($input['quantity'])) {
            throw new InvalidArgumentException("Product ID and quantity are required");
        }

        if ($input['quantity'] <= 0) {
            throw new InvalidArgumentException("Quantity must be greater than zero");
        }
    }
    private function findProduct($productId)
    {
        $product = $this->productRepository->getProductById($productId);
        if (!$product) {
            throw new InvalidArgumentException("Product not found");
        }
        return $product;
    }
    private function updateOrCreateCartItem($product, $productId, $quantity)
    {
        $existingItem = $this->cartRepository->getCartItemByProductAndUser($_SESSION['user_id'], $productId);

        if ($existingItem) {
            $newQuantity = $existingItem['quantity'] + $quantity;
            $success = $this->cartRepository->updateCartItemQuantity($_SESSION['user_id'], $productId, $newQuantity);

            if (!$success) {
                throw new Exception("Failed to update product quantity in cart");
            }
        } else {
            $success = $this->cartRepository->addToCart($_SESSION['user_id'], $productId, $quantity);

            if (!$success) {
                throw new Exception("Failed to add product to cart");
            }
        }
    }

    public function viewCart()
    {
        try {
            $cartItems = $this->cartRepository->getCartItems($_SESSION['user_id']);

            if (empty($cartItems)) {
                Response::send(404, "Cart is empty");
                return;
            }

            Response::send(200, "Cart fetched successfully", $cartItems);

        } catch (Exception $e) {
            Response::send(500, "An error occurred: " . $e->getMessage());
        }
    }

    public function checkoutCart()
    {
        try {
            $cartItems = $this->cartRepository->getCartItems($_SESSION['user_id']);

            if (empty($cartItems)) {
                Response::send(400, "Cart is empty");
                return;
            }

            $totalAmount = $this->calculateTotalAmount($cartItems);

            $orderId = $this->orderRepository->createOrder($_SESSION['user_id'], $cartItems, $totalAmount);
            // Clear the cart after placing order
            $this->cartRepository->clearCart($_SESSION['user_id']);

            Response::send(201, "Order placed successfully", [
                "order_id" => $orderId,
                "total_amount" => $totalAmount,
            ]);

        } catch (Exception $e) {
            Response::send(500, "An error occurred: " . $e->getMessage());
        }
    }
    private function calculateTotalAmount($cartItems)
    {
        $totalAmount = 0;
        foreach ($cartItems as $item) {
            $totalAmount += $item['price'] * $item['quantity'];
        }
        return $totalAmount;
    }
}