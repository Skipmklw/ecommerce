<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'config/database.php';

try {
    // Check if cart table exists
    $stmt = $conn->query("SHOW TABLES LIKE 'cart'");
    $cartTableExists = $stmt->rowCount() > 0;
    
    echo "Cart table exists: " . ($cartTableExists ? "Yes" : "No") . "<br>";
    
    if ($cartTableExists) {
        // Check cart table structure
        $stmt = $conn->query("DESCRIBE cart");
        echo "<h3>Cart Table Structure:</h3>";
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo "Field: " . $row['Field'] . ", Type: " . $row['Type'] . ", Key: " . $row['Key'] . "<br>";
        }
        
        // Check foreign keys
        $stmt = $conn->query("
            SELECT TABLE_NAME,COLUMN_NAME,CONSTRAINT_NAME,REFERENCED_TABLE_NAME,REFERENCED_COLUMN_NAME
            FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
            WHERE TABLE_NAME = 'cart' AND REFERENCED_TABLE_NAME IS NOT NULL
        ");
        echo "<h3>Foreign Keys:</h3>";
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo "Column: " . $row['COLUMN_NAME'] . " references " . 
                 $row['REFERENCED_TABLE_NAME'] . "(" . $row['REFERENCED_COLUMN_NAME'] . ")<br>";
        }
    }
    
    // Check if users table exists and has data
    $stmt = $conn->query("SHOW TABLES LIKE 'users'");
    $usersTableExists = $stmt->rowCount() > 0;
    echo "<br>Users table exists: " . ($usersTableExists ? "Yes" : "No") . "<br>";
    
    if ($usersTableExists) {
        $stmt = $conn->query("SELECT COUNT(*) as count FROM users");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        echo "Number of users: " . $result['count'] . "<br>";
    }
    
    // Check if products table exists and has data
    $stmt = $conn->query("SHOW TABLES LIKE 'products'");
    $productsTableExists = $stmt->rowCount() > 0;
    echo "<br>Products table exists: " . ($productsTableExists ? "Yes" : "No") . "<br>";
    
    if ($productsTableExists) {
        $stmt = $conn->query("SELECT COUNT(*) as count FROM products");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        echo "Number of products: " . $result['count'] . "<br>";
    }
    
} catch(PDOException $e) {
    die("Error: " . $e->getMessage() . "<br>");
}
?> 