<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'config/database.php';

try {
    // Drop the cart table if it exists (to ensure a clean creation)
    $sql = "DROP TABLE IF EXISTS cart";
    $conn->exec($sql);
    echo "Removed old cart table if existed<br>";

    // Create cart table
    $sql = "CREATE TABLE cart (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        product_id INT NOT NULL,
        quantity INT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
    ) ENGINE=InnoDB";
    
    $conn->exec($sql);
    echo "Cart table created successfully!<br>";
    echo "<a href='products.php'>Go back to products</a>";

} catch(PDOException $e) {
    die("Error creating cart table: " . $e->getMessage() . "<br>");
}
?> 