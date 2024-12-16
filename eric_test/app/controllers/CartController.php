<?php
require_once __DIR__ . '/../repositories/ProductRepository.php';
require_once __DIR__ . '/../repositories/CartRepository.php';
require_once __DIR__ . '/../repositories/OrderRepository.php';
class CartController
{
    private $cartRepository;
    private $productRepository;
    private $orderRepository;

    public function __construct()
    {
        $this->cartRepository = new CartRepository();
        $this->productRepository = new ProductRepository();
        $this->orderRepository =  new OrderRepository();
    }

    public function addToCart()
    {
        try {
            $input = json_decode(file_get_contents('php://input'), true);

            if (!isset($input['product_id']) || !isset($input['quantity'])) {
                Response::send(400, "Product ID and quantity are required");
                return;
            }

            $productId = $input['product_id'];
            $quantity = $input['quantity'];

            if ($quantity <= 0) {
                Response::send(400, "Quantity must be greater than zero");
                return;
            }

            $product = $this->productRepository->getProductById($productId);
            if (!$product) {
                Response::send(404, "Product not found");
                return;
            }

            $existingItem = $this->cartRepository->getCartItemByProductAndUser($_SESSION['user_id'], $productId);

            if ($existingItem) {
                $newQuantity = $existingItem['quantity'] + $quantity;
                $success = $this->cartRepository->updateCartItemQuantity($_SESSION['user_id'], $productId, $newQuantity);

                if ($success) {
                    Response::send(200, "Product quantity updated in cart successfully");
                } else {
                    throw new Exception("Failed to update product quantity in cart");
                }
            } else {
                $success = $this->cartRepository->addToCart($_SESSION['user_id'], $productId, $quantity);

                if ($success) {
                    Response::send(201, "Product added to cart successfully");
                } else {
                    throw new Exception("Failed to add product to cart");
                }
            }

        } catch (Exception $e) {
            Response::send(500, "An error occurred: " . $e->getMessage());
        }
    }

    public function viewCart()
    {
        try {
            $cartItems = $this->cartRepository->getCartItems($_SESSION['user_id']);

            if (!$cartItems) {
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

            $totalAmount = 0;
            foreach ($cartItems as $item) {
                $totalAmount += $item['price'] * $item['quantity'];
            }

            $orderId = $this->orderRepository->createOrder($_SESSION['user_id'], $cartItems, $totalAmount);

            $this->cartRepository->clearCart($_SESSION['user_id']);

            Response::send(201, "Order placed successfully", [
                "order_id" => $orderId,
                "total_amount" => $totalAmount,
            ]);

        } catch (Exception $e) {
            Response::send(500, "An error occurred: " . $e->getMessage());
        }
    }
}
