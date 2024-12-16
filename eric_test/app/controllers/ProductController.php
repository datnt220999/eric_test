<?php
// app/controllers/ProductController.php

require_once __DIR__ . '/../repositories/ProductRepository.php';

class ProductController
{
    protected $productRepository;

    // Constructor nhận đối tượng ProductRepository
    public function __construct()
    {
        $this->productRepository = new ProductRepository();
    }

    public function create()
    {
        $data = $_POST;

        if (empty($data['price']) || empty($data['name']) || empty($data['description']) || empty($_FILES['image'])) {
            Response::send(400, "All fields are required.");
            return;
        }

        $image = $_FILES['image'];

        if ($image['error'] != 0) {
            Response::send(400, "Error uploading image.");
            return;
        }

        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        if (!in_array($image['type'], $allowedTypes)) {
            Response::send(400, "Invalid image type. Only JPG, PNG, and GIF are allowed.");
            return;
        }

        $uploadDir = __DIR__.'/../../public/uploads/images/';
        $filename = $uploadDir . basename($image['name']);
        if (!move_uploaded_file($image['tmp_name'], $filename)) {
            Response::send(500, "Failed to move uploaded image.");
            return;
        }

        // Sử dụng repository để tạo sản phẩm
        $this->productRepository->createProduct($data['name'], basename($image['name']), $data['description'], $data['price'], $_SESSION['user_id']);
        Response::send(201, "Product created successfully.");
    }

    public function detail()
    {
        $data = $_GET;

        try {
            $productId = $_GET['id'];
            $product = $this->productRepository->getProductById($productId, $_SESSION['user_id']);
            Response::send(200,'Success',  $product);
        } catch (\Exception $e) {
            Response::send(404, "Product not found.");
        }
    }

    public function listProduct()
    {
        $data = $_GET;

        // Lấy tham số phân trang từ query string (default page = 1, limit = 12)
        $page = isset($data['page']) ? (int)$data['page'] : 1;
        $limit = isset($data['limit']) ? (int)$data['limit'] : 12;

        try {
            // Gọi phương thức getAllProducts() của repository để lấy danh sách sản phẩm phân trang
            $listProduct = $this->productRepository->getAllProducts($page, $limit);

            // Lấy tổng số sản phẩm (để tính tổng số trang)
            $totalProducts = $this->productRepository->getTotalProducts();
            $totalPages = ceil($totalProducts / $limit);

            // Trả về kết quả với thông tin phân trang
            Response::send(200, 'Success',[
                'data' => $listProduct,
                'pagination' => [
                    'current_page' => $page,
                    'total_pages' => $totalPages,
                    'total_products' => $totalProducts,
                    'limit' => $limit
                ]
            ]);
        } catch (\Exception $e) {
            Response::send(404, "Products not found.");
        }
    }

}
