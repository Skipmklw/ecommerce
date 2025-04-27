<?php
session_start();
require_once 'config/database.php';

// Initialize variables
$cart_items = [];
$total = 0;
$error_message = null;
$success_message = null;

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Handle quantity updates
if (isset($_POST['update_quantity'])) {
    $cart_id = $_POST['cart_id'];
    $quantity = $_POST['quantity'];
    
    try {
        $stmt = $conn->prepare("UPDATE cart SET quantity = ? WHERE id = ? AND user_id = ?");
        $stmt->execute([$quantity, $cart_id, $_SESSION['user_id']]);
        $success_message = "Cart updated successfully!";
    } catch(PDOException $e) {
        $error_message = "Error updating quantity: " . $e->getMessage();
    }
}

// Handle item removal
if (isset($_POST['remove_item'])) {
    $cart_id = $_POST['cart_id'];
    
    try {
        $stmt = $conn->prepare("DELETE FROM cart WHERE id = ? AND user_id = ?");
        $stmt->execute([$cart_id, $_SESSION['user_id']]);
        $success_message = "Item removed from cart!";
    } catch(PDOException $e) {
        $error_message = "Error removing item: " . $e->getMessage();
    }
}

// Get cart items
try {
    // First check if cart table exists
    $stmt = $conn->query("SHOW TABLES LIKE 'cart'");
    if ($stmt->rowCount() == 0) {
        // Cart table doesn't exist, create it
        $sql = "CREATE TABLE IF NOT EXISTS cart (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            product_id INT NOT NULL,
            quantity INT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
        ) ENGINE=InnoDB";
        $conn->exec($sql);
    }

    $stmt = $conn->prepare("
        SELECT c.id as cart_id, p.*, c.quantity as cart_quantity 
        FROM cart c 
        JOIN products p ON c.product_id = p.id 
        WHERE c.user_id = ?
        ORDER BY c.created_at DESC
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $cart_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Calculate total
    $total = 0;
    foreach ($cart_items as $item) {
        $total += $item['price'] * $item['cart_quantity'];
    }
} catch(PDOException $e) {
    $error_message = "Error fetching cart items: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart - CycyStore</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        .cart-container {
            max-width: 1000px;
            margin: 2rem auto;
            padding: 2rem;
            background-color: var(--surface-color);
            border-radius: var(--border-radius-lg);
            box-shadow: var(--shadow-md);
        }
        .cart-item {
            display: grid;
            grid-template-columns: 100px 2fr 1fr 1fr 1fr auto;
            gap: var(--spacing-lg);
            align-items: center;
            padding: var(--spacing-lg);
            border-bottom: 1px solid var(--text-lighter);
        }
        .cart-item img {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: var(--border-radius-md);
        }
        .quantity-controls {
            display: flex;
            align-items: center;
            gap: var(--spacing-sm);
            white-space: nowrap;
        }
        .quantity-input {
            width: 60px;
            height: 36px;
            padding: var(--spacing-xs) var(--spacing-sm);
            border: 1px solid var(--text-lighter);
            border-radius: var(--border-radius-sm);
            text-align: center;
            font-size: 0.875rem;
        }
        .update-btn {
            height: 36px;
            padding: 0 var(--spacing-md);
            background-color: var(--primary-color);
            color: white;
            border: none;
            border-radius: var(--border-radius-sm);
            cursor: pointer;
            font-size: 0.875rem;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: var(--spacing-xs);
            transition: all 0.2s ease;
            white-space: nowrap;
            min-width: 80px;
        }
        .update-btn:hover {
            background-color: var(--primary-dark);
            transform: translateY(-1px);
        }
        .remove-btn {
            height: 36px;
            padding: 0 var(--spacing-md);
            background-color: var(--error);
            color: white;
            border: none;
            border-radius: var(--border-radius-sm);
            cursor: pointer;
            font-size: 0.875rem;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: var(--spacing-xs);
            transition: all 0.2s ease;
        }
        .remove-btn:hover {
            background-color: #dc2626;
            transform: translateY(-1px);
        }
        .cart-summary {
            margin-top: var(--spacing-2xl);
            text-align: right;
            padding-top: var(--spacing-lg);
            border-top: 1px solid var(--text-lighter);
        }
        .cart-total {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--primary-color);
            margin: var(--spacing-lg) 0;
        }
        .empty-cart {
            text-align: center;
            padding: var(--spacing-2xl);
        }
        .empty-cart h2 {
            color: var(--text-color);
            margin-bottom: var(--spacing-md);
        }
        .empty-cart p {
            color: var(--text-light);
            margin-bottom: var(--spacing-lg);
        }
        .cart-buttons {
            display: flex;
            gap: var(--spacing-xs);
            align-items: center;
            justify-content: flex-end;
        }
        .cart-btn {
            height: 36px;
            padding: 0 var(--spacing-md);
            font-size: 0.875rem;
            font-weight: 500;
            border: none;
            border-radius: var(--border-radius-sm);
            cursor: pointer;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: var(--spacing-xs);
            text-decoration: none;
            color: white;
        }
        .cart-btn-primary {
            background: var(--primary-color);
        }
        .cart-btn-primary:hover {
            background: var(--primary-dark);
            transform: translateY(-1px);
        }
        .cart-btn-secondary {
            background: var(--secondary-color);
        }
        .cart-btn-secondary:hover {
            background: var(--text-color);
            transform: translateY(-1px);
        }
        .cart-btn-danger {
            background: var(--error);
        }
        .cart-btn-danger:hover {
            background: #dc2626;
            transform: translateY(-1px);
        }
        @media (max-width: 768px) {
            .cart-item {
                grid-template-columns: 80px 1fr;
                gap: var(--spacing-sm);
                padding: var(--spacing-md);
            }
            .cart-item > * {
                grid-column: 2;
            }
            .cart-item img {
                grid-row: 1 / span 4;
                grid-column: 1;
                width: 80px;
                height: 80px;
            }
            .cart-buttons {
                flex-direction: column;
                width: 100%;
            }
            .cart-btn {
                width: 100%;
                justify-content: center;
            }
            .quantity-controls {
                flex-direction: column;
                align-items: stretch;
            }
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="nav-content">
            <div class="nav-brand">
                <a href="index.php">CycyStore</a>
                <button class="mobile-menu-btn" aria-label="Toggle menu">
                    <i class="fas fa-bars"></i>
                </button>
            </div>
            <div class="nav-links" id="navLinks">
                <?php if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin'): ?>
                    <a href="index.php">
                        <i class="fas fa-home"></i> Home
                    </a>
                <?php endif; ?>
                <a href="products.php">
                    <i class="fas fa-shopping-bag"></i> Products
                </a>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <?php if($_SESSION['role'] === 'admin'): ?>
                        <a href="admin/dashboard.php">
                            <i class="fas fa-tachometer-alt"></i> Dashboard
                        </a>
                    <?php else: ?>
                        <a href="cart.php" class="active">
                            <i class="fas fa-shopping-cart"></i> Cart
                        </a>
                    <?php endif; ?>
                    <a href="logout.php">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                <?php else: ?>
                    <a href="login.php">
                        <i class="fas fa-sign-in-alt"></i> Login
                    </a>
                    <a href="register.php">
                        <i class="fas fa-user-plus"></i> Register
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <main class="container">
        <div class="cart-container">
            <h1>Shopping Cart</h1>

            <?php if (isset($success_message)): ?>
                <div class="alert success"><?php echo $success_message; ?></div>
            <?php endif; ?>

            <?php if (isset($error_message)): ?>
                <div class="alert error"><?php echo $error_message; ?></div>
            <?php endif; ?>

            <?php if (empty($cart_items)): ?>
                <div class="empty-cart">
                    <h2>Your cart is empty</h2>
                    <p>Browse our products and add some items to your cart!</p>
                    <a href="products.php" class="cart-btn cart-btn-primary">
                        <i class="fas fa-shopping-bag"></i> Continue Shopping
                    </a>
                </div>
            <?php else: ?>
                <?php foreach ($cart_items as $item): ?>
                    <div class="cart-item">
                        <img src="<?php echo htmlspecialchars($item['image_url']); ?>" 
                             alt="<?php echo htmlspecialchars($item['name']); ?>">
                        <div>
                            <h3><?php echo htmlspecialchars($item['name']); ?></h3>
                            <p><?php echo htmlspecialchars($item['description']); ?></p>
                        </div>
                        <div>
                            <p>Price: $<?php echo number_format($item['price'], 2); ?></p>
                        </div>
                        <form method="POST" class="quantity-controls">
                            <input type="hidden" name="cart_id" value="<?php echo $item['cart_id']; ?>">
                            <input type="number" name="quantity" value="<?php echo $item['cart_quantity']; ?>" 
                                   min="1" max="<?php echo $item['quantity']; ?>" class="quantity-input">
                            <button type="submit" name="update_quantity" class="update-btn">
                                <i class="fas fa-sync-alt"></i> Update
                            </button>
                        </form>
                        <div>
                            <p>Subtotal: $<?php echo number_format($item['price'] * $item['cart_quantity'], 2); ?></p>
                        </div>
                        <form method="POST">
                            <input type="hidden" name="cart_id" value="<?php echo $item['cart_id']; ?>">
                            <button type="submit" name="remove_item" class="remove-btn">
                                <i class="fas fa-trash-alt"></i> Remove
                            </button>
                        </form>
                    </div>
                <?php endforeach; ?>

                <div class="cart-summary">
                    <div class="cart-total">
                        Total: $<?php echo number_format($total, 2); ?>
                    </div>
                    <div class="cart-buttons">
                        <a href="products.php" class="cart-btn cart-btn-secondary">
                            <i class="fas fa-arrow-left"></i> Continue Shopping
                        </a>
                        <a href="checkout.php" class="cart-btn cart-btn-primary">
                            <i class="fas fa-shopping-cart"></i> Proceed to Checkout
                        </a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <script src="assets/js/main.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const menuBtn = document.querySelector('.mobile-menu-btn');
            const navLinks = document.querySelector('.nav-links');
            
            menuBtn.addEventListener('click', function() {
                navLinks.classList.toggle('active');
            });

            // Close menu when clicking outside
            document.addEventListener('click', function(event) {
                if (!navLinks.contains(event.target) && !menuBtn.contains(event.target)) {
                    navLinks.classList.remove('active');
                }
            });
        });
    </script>
</body>
</html> 