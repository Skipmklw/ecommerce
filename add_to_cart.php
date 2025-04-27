<?php
session_start();
require_once 'config/database.php';

// Set the response header to JSON
header('Content-Type: application/json');

// Enable error logging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please login to add items to cart']);
    exit();
}

// Check if request is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

// Get product ID and quantity from POST data
$product_id = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
$quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;

// Debug log
error_log("Raw POST data: " . print_r($_POST, true));
error_log("Adding to cart - Product ID: $product_id, Quantity: $quantity");

// Validate input
if ($product_id <= 0 || $quantity <= 0) {
    echo json_encode([
        'success' => false, 
        'message' => 'Invalid product ID or quantity',
        'debug' => ['product_id' => $product_id, 'quantity' => $quantity]
    ]);
    exit();
}

try {
    // Start transaction
    $conn->beginTransaction();

    // Check product existence and stock
    $stmt = $conn->prepare("SELECT quantity, name FROM products WHERE id = ? FOR UPDATE");
    $stmt->execute([$product_id]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$product) {
        throw new Exception('Product not found');
    }

    // Check if product already in cart
    $stmt = $conn->prepare("SELECT id, quantity FROM cart WHERE user_id = ? AND product_id = ? FOR UPDATE");
    $stmt->execute([$_SESSION['user_id'], $product_id]);
    $cart_item = $stmt->fetch(PDO::FETCH_ASSOC);

    // Debug log
    error_log("Current cart item: " . ($cart_item ? json_encode($cart_item) : "not in cart"));

    if ($cart_item) {
        // Update existing cart item
        $new_quantity = $cart_item['quantity'] + $quantity;
        error_log("Updating quantity: {$cart_item['quantity']} + {$quantity} = {$new_quantity}");
        
        if ($new_quantity > $product['quantity']) {
            throw new Exception("Cannot add more items than available in stock (Available: {$product['quantity']})");
        }

        $stmt = $conn->prepare("UPDATE cart SET quantity = ? WHERE id = ? AND user_id = ?");
        $stmt->execute([$new_quantity, $cart_item['id'], $_SESSION['user_id']]);
        $message = "Updated quantity of {$product['name']} to {$new_quantity}";
    } else {
        // Add new cart item
        if ($quantity > $product['quantity']) {
            throw new Exception("Cannot add more items than available in stock (Available: {$product['quantity']})");
        }

        $stmt = $conn->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?)");
        $stmt->execute([$_SESSION['user_id'], $product_id, $quantity]);
        $message = "Added {$quantity} {$product['name']} to cart";
    }

    // Commit transaction
    $conn->commit();

    // Verify the final quantity
    $stmt = $conn->prepare("SELECT quantity FROM cart WHERE user_id = ? AND product_id = ?");
    $stmt->execute([$_SESSION['user_id'], $product_id]);
    $final_quantity = $stmt->fetch(PDO::FETCH_ASSOC)['quantity'];
    error_log("Final quantity in cart: $final_quantity");

    echo json_encode([
        'success' => true, 
        'message' => $message,
        'debug' => [
            'product_id' => $product_id,
            'quantity_added' => $quantity,
            'final_quantity' => $final_quantity
        ]
    ]);

} catch (Exception $e) {
    $conn->rollBack();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
} catch (PDOException $e) {
    $conn->rollBack();
    echo json_encode(['success' => false, 'message' => 'Database error occurred']);
} 