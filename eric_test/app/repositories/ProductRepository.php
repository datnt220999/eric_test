<?php

require_once __DIR__ . '/../models/Product.php';

class ProductRepository
{
    public function createProduct($name, $image, $description, $price, $userId)
    {
        // Logic to insert product into database
        $product = new Product();
        $product->createProduct($name, $image, $description, $price, $userId);
    }

    public function getProductById($productId, $user_id = null)
    {
        // Logic to get product details by ID
        $product = new Product();
        return $product->getProductById($productId, $user_id);
    }

    public function getAllProducts($page = 1, $limit = 12)
    {
        $product = new Product();
        return $product->getAllProducts($page , $limit);
    }
    public function getTotalProducts()
    {
        $product = new Product();
        return $product->getTotalProducts();
    }
}
