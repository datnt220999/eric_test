<?php
// app/controllers/ProductController.php

require_once __DIR__ . '/../repositories/ProductRepository.php';
require_once __DIR__ . '/../utils/Response.php';

class ProductController
{
    protected $productRepository;
    private $uploadDir;
    private $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];

    // Constructor nhận đối tượng ProductRepository
    public function __construct()
    {
        $this->productRepository = new ProductRepository();
        $this->uploadDir = __DIR__.'/../../public/uploads/images/';
    }

    public function create()
    {
        try {
            $data = $_POST;

            $this->validateCreateInput($data); // Validate input

            $image = $_FILES['image'];
            $this->validateImage($image); // Validate image

            $filename = $this->uploadImage($image); // Upload image

            // Sử dụng repository để tạo sản phẩm
            $this->productRepository->createProduct($data['name'], basename($filename), $data['description'], $data['price'], $_SESSION['user_id']);
            Response::send(201, "Product created successfully.");
        } catch (InvalidArgumentException $e) {
            Response::send(400, $e->getMessage());
        }
        catch (Exception $e) {
            Response::send(500, "An error occurred while creating product: " . $e->getMessage());
        }
    }

    private function validateCreateInput($data){
        if (empty($data['price']) || empty($data['name']) || empty($data['description']) || empty($_FILES['image'])) {
            throw new InvalidArgumentException("All fields are required.");
        }
    }
    private function validateImage($image)
    {
        if ($image['error'] != 0) {
            throw new InvalidArgumentException("Error uploading image.");
        }


        if (!in_array($image['type'], $this->allowedTypes)) {
            throw new InvalidArgumentException("Invalid image type. Only JPG, PNG, and GIF are allowed.");
        }
    }

    private function uploadImage($image)
    {
        $filename = $this->uploadDir . basename($image['name']);
        if (!move_uploaded_file($image['tmp_name'], $filename)) {
            throw new Exception("Failed to move uploaded image.");
        }
        return $filename;
    }


    public function detail()
    {
        try {
            $productId = $_GET['id'];
            if(!isset($productId)){
                Response::send(400, "Product ID is required.");
                return;
            }
            $product = $this->productRepository->getProductById($productId, $_SESSION['user_id']);
            if (!$product) {
                Response::send(404, "Product not found");
                return;
            }
            Response::send(200,'Success',  $product);
        }  catch (Exception $e) {
            Response::send(500, "An error occurred: " . $e->getMessage());
        }
    }

    public function listProduct()
    {
        try {
            $data = $_GET;

            // Lấy tham số phân trang từ query string (default page = 1, limit = 12)
            $page = isset($data['page']) ? (int)$data['page'] : 1;
            $limit = isset($data['limit']) ? (int)$data['limit'] : 12;


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
            Response::send(500, "An error occurred: " . $e->getMessage());
        }
    }
}