<?php

require_once __DIR__ . '/../app/helpers/Const.php';
require_once __DIR__ . '/../config/Database.php';

class DatabaseMigrator
{
    private PDO $connection;

    public function __construct(PDO $connection)
    {
        $this->connection = $connection;
    }

    public function migrate()
    {
        $this->createUsersTable();
        $this->createProductsTable();
        $this->createCartsTable();
        $this->createOrdersTable();
        $this->createOrderItemsTable();
        echo "Migration completed successfully.\n";
    }

    private function createUsersTable()
    {
        $sql = "
            CREATE TABLE IF NOT EXISTS `users` (
                `id` INT(11) NOT NULL AUTO_INCREMENT,
                `name` VARCHAR(255) NOT NULL,
                `email` VARCHAR(255) NOT NULL,
                `password` VARCHAR(255) NOT NULL,
                `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP(),
                PRIMARY KEY (`id`),
                UNIQUE KEY `email` (`email`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
        ";
        $this->executeStatement($sql, 'users');
    }


    private function createProductsTable()
    {
        $sql = "
            CREATE TABLE IF NOT EXISTS `products` (
                `id` INT(11) NOT NULL AUTO_INCREMENT,
                `user_id` INT(11) NOT NULL,
                `name` VARCHAR(255) NOT NULL,
                `image` VARCHAR(255) DEFAULT NULL,
                `description` TEXT DEFAULT NULL,
                `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP(),
                 `price` DOUBLE DEFAULT NULL,
                PRIMARY KEY (`id`),
                KEY `user_id` (`user_id`),
                CONSTRAINT `products_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
        ";

        $this->executeStatement($sql, 'products');
    }

    private function createCartsTable()
    {
        $sql = "
            CREATE TABLE IF NOT EXISTS `carts` (
                `id` INT(11) NOT NULL AUTO_INCREMENT,
                `user_id` INT(11) NOT NULL,
                `product_id` INT(11) NOT NULL,
                `quantity` INT(11) DEFAULT 1,
                `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP(),
                PRIMARY KEY (`id`),
                KEY `user_id` (`user_id`),
                KEY `product_id` (`product_id`),
                CONSTRAINT `carts_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
                CONSTRAINT `carts_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
        ";
        $this->executeStatement($sql, 'carts');
    }

    private function createOrdersTable()
    {
        $sql = "
            CREATE TABLE IF NOT EXISTS `orders` (
                `id` INT(11) NOT NULL AUTO_INCREMENT,
                `user_id` INT(11) NOT NULL,
                `total_amount` DECIMAL(10,2) NOT NULL,
                `status` ENUM('pending','completed','cancelled','shipped') DEFAULT 'pending',
                `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP(),
                PRIMARY KEY (`id`),
                KEY `user_id` (`user_id`),
                CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
        ";
        $this->executeStatement($sql, 'orders');
    }

    private function createOrderItemsTable()
    {
        $sql = "
            CREATE TABLE IF NOT EXISTS `order_items` (
                `id` INT(11) NOT NULL AUTO_INCREMENT,
                `order_id` INT(11) NOT NULL,
                `product_id` INT(11) NOT NULL,
                `quantity` INT(11) DEFAULT 1,
                 `price` DECIMAL(10,2) NOT NULL,
                PRIMARY KEY (`id`),
                KEY `order_id` (`order_id`),
                KEY `product_id` (`product_id`),
                CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
                CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
        ";
        $this->executeStatement($sql, 'order_items');
    }

    private function executeStatement(string $sql, string $table)
    {
        try {
            $this->connection->exec($sql);
            echo "Table '$table' created successfully.\n";
        } catch (PDOException $e) {
            die("Error creating table '$table': " . $e->getMessage() . "\n");
        }
    }
}

try {
    // Get connection from Database class
    $conn = Database::getInstance();

    $migrator = new DatabaseMigrator($conn);
    $migrator->migrate();

} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>