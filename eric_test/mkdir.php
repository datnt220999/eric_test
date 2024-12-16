<?php
// Danh sách file và nội dung
$projectFiles = [
    // Helpers
    'app/helpers/Database.php' => <<<PHP
<?php
namespace App\Helpers;

use PDO;
use PDOException;

class Database {
    private static \$instance = null;
    private \$connection;

    private function __construct() {
        try {
            \$this->connection = new PDO(
                'mysql:host=localhost;dbname=ecommerce',
                'root',
                '',
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );
        } catch (PDOException \$e) {
            die("Database connection error: " . \$e->getMessage());
        }
    }

    public static function getInstance() {
        if (self::\$instance === null) {
            self::\$instance = new Database();
        }
        return self::\$instance->connection;
    }
}
PHP,
    // Models
    'app/models/User.php' => <<<PHP
<?php
namespace App\Models;

use App\Helpers\Database;
use PDO;

class User {
    private \$db;

    public function __construct() {
        \$this->db = Database::getInstance();
    }

    public function createUser(\$name, \$email, \$password) {
        \$sql = "INSERT INTO users (name, email, password) VALUES (:name, :email, :password)";
        \$stmt = \$this->db->prepare(\$sql);
        \$stmt->bindParam(':name', \$name);
        \$stmt->bindParam(':email', \$email);
        \$stmt->bindParam(':password', \$password);
        return \$stmt->execute();
    }

    public function findUserByEmail(\$email) {
        \$sql = "SELECT * FROM users WHERE email = :email LIMIT 1";
        \$stmt = \$this->db->prepare(\$sql);
        \$stmt->bindParam(':email', \$email);
        \$stmt->execute();
        return \$stmt->fetch(PDO::FETCH_ASSOC);
    }
}
PHP,
    'app/models/Product.php' => <<<PHP
<?php
namespace App\Models;

use App\Helpers\Database;
use PDO;

class Product {
    private \$db;

    public function __construct() {
        \$this->db = Database::getInstance();
    }

    public function createProduct(\$name, \$image, \$description, \$user_id) {
        \$sql = "INSERT INTO products (name, image, description, user_id) VALUES (:name, :image, :description, :user_id)";
        \$stmt = \$this->db->prepare(\$sql);
        \$stmt->bindParam(':name', \$name);
        \$stmt->bindParam(':image', \$image);
        \$stmt->bindParam(':description', \$description);
        \$stmt->bindParam(':user_id', \$user_id);
        return \$stmt->execute();
    }
}
PHP,
    // Controllers
    'app/controllers/UserController.php' => <<<PHP
<?php
namespace App\Controllers;

use App\Models\User;

class UserController {
    private \$userModel;

    public function __construct() {
        \$this->userModel = new User();
    }

    public function register(\$name, \$email, \$password) {
        if (\$this->userModel->findUserByEmail(\$email)) {
            return "Email already exists.";
        }
        return \$this->userModel->createUser(\$name, \$email, \$password);
    }
}
PHP,
    'app/controllers/ProductController.php' => <<<PHP
<?php
namespace App\Controllers;

use App\Models\Product;

class ProductController {
    private \$productModel;

    public function __construct() {
        \$this->productModel = new Product();
    }

    public function createProduct(\$name, \$image, \$description, \$user_id) {
        return \$this->productModel->createProduct(\$name, \$image, \$description, \$user_id);
    }
}
PHP,
    // Public folder (entry point)
    'public/index.php' => <<<PHP
<?php
require_once __DIR__ . '/../vendor/autoload.php';

use App\Controllers\UserController;

\$userController = new UserController();
echo \$userController->register('John Doe', 'john@example.com', '123456');
PHP,
    // Configuration
    'config/app.php' => <<<PHP
<?php
return [
    'app_name' => 'E-Commerce Platform',
    'version' => '1.0',
    'debug' => true,
];
PHP,
    // Database migrations
    'database/migrations/create_users_table.sql' => <<<SQL
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
SQL,
    'database/migrations/create_products_table.sql' => <<<SQL
CREATE TABLE products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    image VARCHAR(255) DEFAULT NULL,
    description TEXT,
    user_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
SQL,
];

// Hàm tạo file và thư mục
function createFile($filePath, $content) {
    $directory = dirname($filePath);

    // Tạo thư mục nếu chưa tồn tại
    if (!is_dir($directory)) {
        mkdir($directory, 0777, true);
    }

    // Tạo file
    file_put_contents($filePath, $content);
    echo "File created: {$filePath}\n";
}

// Tạo tất cả các file trong project
foreach ($projectFiles as $file => $content) {
    createFile($file, $content);
}

// Tạo file composer.json
$composerJson = <<<JSON
{
    "autoload": {
        "psr-4": {
            "App\\\\": "app/"
        }
    }
}
JSON;
file_put_contents('composer.json', $composerJson);
echo "File created: composer.json\n";

// Cài đặt autoload
exec('composer dump-autoload');
