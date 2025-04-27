<?php
session_start();
require_once 'config/database.php';

// Get product ID from URL
$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

try {
    // Fetch product details
    $stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$product_id]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$product) {
        header("HTTP/1.0 404 Not Found");
        die("Product not found");
    }
} catch(PDOException $e) {
    die("Error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($product['name']); ?> - CycyStore</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        .breadcrumb {
            margin: 1rem 0;
        }

        .breadcrumb a {
            color: #64748b;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.875rem;
            transition: all 0.2s;
        }

        .breadcrumb a:hover {
            color: #2563eb;
        }

        .product-details {
            max-width: 1000px;
            margin: 1rem auto;
            padding: 1.5rem;
            background: white;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }

        .product-grid {
            display: grid;
            grid-template-columns: minmax(200px, 1fr) 1.5fr;
            gap: 1.5rem;
            align-items: start;
        }

        .product-image-container {
            position: relative;
            width: 100%;
            max-width: 300px;
            padding-top: 100%;
            border-radius: 8px;
            overflow: hidden;
            background: #f8fafc;
            margin: 0 auto;
        }

        .product-image {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            object-fit: contain;
            background: #f8fafc;
        }

        .product-info {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .product-info h1 {
            font-size: 1.5rem;
            font-weight: 600;
            color: #1e293b;
            margin: 0;
        }

        .product-meta {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 1rem;
            padding: 1rem;
            background: #f8fafc;
            border-radius: 8px;
        }

        .product-meta > div {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.875rem;
            color: #64748b;
        }

        .price {
            font-size: 1.25rem !important;
            font-weight: 600;
            color: #2563eb !important;
        }

        .product-description {
            background: #f8fafc;
            padding: 1rem;
            border-radius: 8px;
            color: #475569;
            line-height: 1.6;
        }

        .product-description h2 {
            font-size: 1rem;
            font-weight: 600;
            color: #1e293b;
            margin: 0 0 0.75rem 0;
        }

        .product-description p {
            margin: 0;
            font-size: 0.875rem;
        }

        .add-to-cart-section {
            background: #f8fafc;
            padding: 1rem;
            border-radius: 8px;
            margin-top: 0.5rem;
        }

        .quantity-controls {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 1rem;
        }

        .quantity-input {
            width: 60px;
            height: 32px;
            padding: 0.25rem;
            text-align: center;
            border: 1px solid #e2e8f0;
            border-radius: 4px;
            font-size: 0.875rem;
        }

        .btn {
            height: 32px;
            padding: 0 0.75rem;
            border-radius: 4px;
            border: none;
            cursor: pointer;
            font-size: 0.875rem;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.2s;
        }

        .btn-secondary {
            background: #f1f5f9;
            color: #475569;
            border: 1px solid #e2e8f0;
            width: 32px;
            padding: 0;
            justify-content: center;
        }

        .btn-primary {
            background: #2563eb;
            color: white;
            font-weight: 500;
        }

        .btn-large {
            height: 40px;
            width: 100%;
            justify-content: center;
        }

        @media (max-width: 768px) {
            .product-details {
                margin: 0.5rem;
                padding: 1rem;
            }

            .product-grid {
                grid-template-columns: 1fr;
                gap: 1.5rem;
            }

            .product-image-container {
                max-width: 250px;
                margin: 0 auto 1.5rem;
            }

            .product-info {
                gap: 1.5rem;
            }

            .product-description {
                display: block;
                width: 100%;
                margin-bottom: 1rem;
            }
        }

        @media (max-width: 480px) {
            .product-image-container {
                max-width: 200px;
            }

            .product-meta {
                grid-template-columns: 1fr;
            }

            .product-description {
                padding: 0.875rem;
            }
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="nav-content">
            <div class="nav-brand">
                <a href="index.php">CycyStore</a>
            </div>
            <div class="nav-links">
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
                        <a href="cart.php">
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
        <div class="breadcrumb">
            <a href="products.php">
                <i class="fas fa-arrow-left"></i> Back to Products
            </a>
        </div>

        <div class="product-details">
            <div class="product-grid">
                <div class="product-image-container">
                    <img src="<?php echo !empty($product['image_url']) ? htmlspecialchars($product['image_url']) : 'uploads/no-image.jpg'; ?>" 
                         alt="<?php echo htmlspecialchars($product['name']); ?>" 
                         class="product-image">
                </div>

                <div class="product-info">
                    <h1><?php echo htmlspecialchars($product['name']); ?></h1>
                    
                    <div class="product-meta">
                        <div class="price">
                            <i class="fas fa-tag"></i>
                            ₱<?php echo number_format($product['price'], 2); ?>
                        </div>
                        <div>
                            <i class="fas fa-box"></i>
                            <?php
                            if ($product['quantity'] <= 0) {
                                echo '<span style="color: #ef4444;">Out of Stock</span>';
                            } elseif ($product['quantity'] <= 5) {
                                echo '<span style="color: #f59e0b;">Low Stock (' . $product['quantity'] . ' left)</span>';
                            } else {
                                echo '<span style="color: #10b981;">In Stock</span>';
                            }
                            ?>
                        </div>
                        <div>
                            <i class="fas fa-folder"></i>
                            <?php echo htmlspecialchars($product['category']); ?>
                        </div>
                    </div>

                    <div class="product-description">
                        <h2>Description</h2>
                        <?php echo nl2br(htmlspecialchars($product['description'])); ?>
                    </div>

                    <?php if ($product['quantity'] > 0 && isset($_SESSION['user_id']) && $_SESSION['role'] !== 'admin'): ?>
                        <div class="add-to-cart-section">
                            <div class="quantity-controls">
                                <button type="button" class="btn btn-secondary" onclick="updateQuantity(-1)">
                                    <i class="fas fa-minus"></i>
                                </button>
                                <input type="number" id="quantity" name="quantity" 
                                       class="quantity-input" value="1" min="1" 
                                       max="<?php echo $product['quantity']; ?>">
                                <button type="button" class="btn btn-secondary" onclick="updateQuantity(1)">
                                    <i class="fas fa-plus"></i>
                                </button>
                            </div>
                            <button type="button" class="btn btn-primary btn-large" onclick="addToCart(this)">
                                <i class="fas fa-cart-plus"></i> Add to Cart
                            </button>
                        </div>
                    <?php elseif (!isset($_SESSION['user_id'])): ?>
                        <div class="add-to-cart-section">
                            <p style="text-align: center; margin: 0;">
                                Please <a href="login.php" style="color: #2563eb; text-decoration: none;">log in</a> to add items to your cart.
                            </p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>

    <script src="assets/js/main.js"></script>
    <script>
        function showAlert(message, type = 'success') {
            const alert = document.createElement('div');
            alert.className = `alert ${type === 'success' ? 'alert-success' : 'alert-error'}`;
            alert.innerHTML = `
                <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'}"></i>
                ${message}
            `;
            alert.style.position = 'fixed';
            alert.style.top = '20px';
            alert.style.right = '20px';
            alert.style.zIndex = '1000';
            document.body.appendChild(alert);
            
            setTimeout(() => {
                alert.style.opacity = '0';
                setTimeout(() => alert.remove(), 300);
            }, 3000);
        }

        function updateQuantity(change) {
            const input = document.getElementById('quantity');
            const newValue = Math.max(1, Math.min(parseInt(input.value) + change, parseInt(input.max)));
            input.value = newValue;
        }

        async function addToCart(button) {
            if (button.disabled) return;
            
            button.disabled = true;
            button.classList.add('loading');
            const originalText = button.innerHTML;
            button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Adding to Cart...';

            try {
                const form = document.getElementById('add-to-cart-form');
                const formData = new FormData(form);
                const searchParams = new URLSearchParams();
                
                for (const [key, value] of formData) {
                    searchParams.append(key, value);
                }

                const response = await fetch('add_to_cart.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: searchParams.toString()
                });

                const data = await response.json();
                showAlert(data.message, data.success ? 'success' : 'error');
            } catch (error) {
                console.error('Error:', error);
                showAlert('Error adding product to cart', 'error');
            } finally {
                button.disabled = false;
                button.classList.remove('loading');
                button.innerHTML = originalText;
            }
        }
    </script>
</body>
</html> 