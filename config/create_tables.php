<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'database.php';

try {
    // Create users table first (since it's referenced by other tables)
    $sql = "CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) NOT NULL UNIQUE,
        email VARCHAR(100) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        role VARCHAR(20) NOT NULL DEFAULT 'user',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    $conn->exec($sql);
    echo "Users table created successfully<br>";

    // Create products table
    $sql = "CREATE TABLE IF NOT EXISTS products (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        description TEXT,
        price DECIMAL(10,2) NOT NULL,
        quantity INT NOT NULL DEFAULT 0,
        category VARCHAR(100) NOT NULL,
        image_url VARCHAR(255),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";
    $conn->exec($sql);
    echo "Products table created successfully<br>";

    // Create cart table (depends on users and products)
    $sql = "CREATE TABLE IF NOT EXISTS cart (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        product_id INT NOT NULL,
        quantity INT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
    )";
    $conn->exec($sql);
    echo "Cart table created successfully<br>";

    // Create categories table
    $sql = "CREATE TABLE IF NOT EXISTS categories (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL UNIQUE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    $conn->exec($sql);
    echo "Categories table created successfully<br>";

    // Create orders table (depends on users)
    $sql = "CREATE TABLE IF NOT EXISTS orders (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        total_amount DECIMAL(10,2) NOT NULL,
        status VARCHAR(50) NOT NULL DEFAULT 'pending',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )";
    $conn->exec($sql);
    echo "Orders table created successfully<br>";

    // Create order_items table (depends on orders and products)
    $sql = "CREATE TABLE IF NOT EXISTS order_items (
        id INT AUTO_INCREMENT PRIMARY KEY,
        order_id INT NOT NULL,
        product_id INT NOT NULL,
        quantity INT NOT NULL,
        price DECIMAL(10,2) NOT NULL,
        FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
        FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
    )";
    $conn->exec($sql);
    echo "Order items table created successfully<br>";

    // Insert default categories if they don't exist
    $default_categories = ['Bikes', 'Accessories', 'Parts', 'Clothing'];
    $stmt = $conn->prepare("INSERT IGNORE INTO categories (name) VALUES (?)");
    
    foreach ($default_categories as $category) {
        $stmt->execute([$category]);
    }
    echo "Default categories added successfully<br>";

    echo "<br>All tables have been created successfully! You can now:<br>";
    echo "1. <a href='../login.php'>Login</a> to your account<br>";
    echo "2. <a href='../products.php'>Browse products</a><br>";
    echo "3. Add items to cart<br>";

} catch(PDOException $e) {
    die("Error: " . $e->getMessage() . "<br>");
}
?> 