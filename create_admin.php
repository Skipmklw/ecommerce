<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<pre>"; // For better output formatting
echo "Starting admin user creation process...<br>";

require_once 'config/database.php';

try {
    echo "Checking if users table exists...<br>";
    // Create users table if not exists
    $sql = "CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) NOT NULL UNIQUE,
        email VARCHAR(100) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        role VARCHAR(20) NOT NULL DEFAULT 'user',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    $conn->exec($sql);
    echo "Users table verified/created successfully<br>";

    // Check if admin user already exists
    echo "Checking if admin user exists...<br>";
    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
    $adminUsername = 'admin';
    $adminEmail = 'admin@cycystore.com';
    $stmt->execute([$adminUsername, $adminEmail]);
    
    if ($stmt->rowCount() > 0) {
        echo "Admin user already exists!<br>";
        echo "You can login with:<br>";
        echo "Username: admin<br>";
        echo "Password: admin123<br>";
        echo "Please change your password after first login.<br>";
    } else {
        echo "Creating new admin user...<br>";
        // Create admin user
        $adminPassword = password_hash('admin123', PASSWORD_DEFAULT);
        
        $sql = "INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, 'admin')";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$adminUsername, $adminEmail, $adminPassword]);
        
        echo "Admin user created successfully!<br>";
        echo "You can now login with:<br>";
        echo "Username: admin<br>";
        echo "Password: admin123<br>";
        echo "Please change your password after first login.<br>";
    }
} catch(PDOException $e) {
    die("Error: " . $e->getMessage() . "<br>");
}

echo "</pre>";
?> 