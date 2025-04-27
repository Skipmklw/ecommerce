<?php
session_start();
require_once 'config/database.php';

// Get categories for filter
try {
    $stmt = $conn->query("SELECT DISTINCT category FROM products ORDER BY category");
    $categories = $stmt->fetchAll(PDO::FETCH_COLUMN);
} catch(PDOException $e) {
    $error_message = "Error fetching categories: " . $e->getMessage();
}

// Handle filtering and searching
$category = isset($_GET['category']) ? $_GET['category'] : '';
$search = isset($_GET['search']) ? $_GET['search'] : '';
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'newest';

try {
    $where_conditions = [];
    $params = [];

    if ($category) {
        $where_conditions[] = "category = ?";
        $params[] = $category;
    }

    if ($search) {
        $where_conditions[] = "(name LIKE ? OR description LIKE ?)";
        $params[] = "%$search%";
        $params[] = "%$search%";
    }

    $where_clause = !empty($where_conditions) ? "WHERE " . implode(" AND ", $where_conditions) : "";

    $order_by = match($sort) {
        'price_low' => "ORDER BY price ASC",
        'price_high' => "ORDER BY price DESC",
        'oldest' => "ORDER BY created_at ASC",
        default => "ORDER BY created_at DESC"
    };

    $sql = "SELECT * FROM products $where_clause $order_by";
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $error_message = "Error fetching products: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Products - CycyStore</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        .filters {
            background-color: white;
            padding: 1rem;
            margin-bottom: 2rem;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .filters form {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
            align-items: center;
        }
        .filters select,
        .filters input[type="text"] {
            padding: 0.5rem;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 1rem;
        }
        .product-card {
            position: relative;
        }
        .stock-status {
            position: absolute;
            top: 1rem;
            right: 1rem;
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            font-size: 0.875rem;
            font-weight: bold;
        }
        .in-stock {
            background-color: #d4edda;
            color: #155724;
        }
        .low-stock {
            background-color: #fff3cd;
            color: #856404;
        }
        .out-of-stock {
            background-color: #f8d7da;
            color: #721c24;
        }
        .quantity-input {
            width: 60px;
            padding: 0.25rem;
            text-align: center;
            margin-right: 0.5rem;
        }
        .button-group {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
            margin-top: 1rem;
        }
        .view-details {
            background-color: #2c5282;
            color: white;
            text-decoration: none;
            text-align: center;
            padding: 0.5rem 1rem;
            border-radius: 4px;
            transition: background-color 0.3s;
        }
        .view-details:hover {
            background-color: #2d3748;
        }
        .add-to-cart-form {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }
        @media (max-width: 768px) {
            .filters form {
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
                <a href="products.php" class="active">
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
        <section class="filters-section">
            <h2>Browse Products</h2>
            <form method="GET" class="filters-grid">
                <div class="filter-group">
                    <label for="category">Category</label>
                    <select name="category" id="category" class="filter-input">
                        <option value="">All Categories</option>
                        <?php foreach($categories as $cat): ?>
                            <option value="<?php echo htmlspecialchars($cat); ?>" 
                                    <?php echo $category === $cat ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($cat); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="filter-group">
                    <label for="search">Search</label>
                    <div class="search-input-wrapper">
                        <i class="fas fa-search"></i>
                        <input type="text" name="search" id="search" 
                               class="filter-input"
                               value="<?php echo htmlspecialchars($search); ?>" 
                               placeholder="Search products...">
                    </div>
                </div>

                <div class="filter-group">
                    <label for="sort">Sort by</label>
                    <select name="sort" id="sort" class="filter-input">
                        <option value="newest" <?php echo $sort === 'newest' ? 'selected' : ''; ?>>Newest First</option>
                        <option value="oldest" <?php echo $sort === 'oldest' ? 'selected' : ''; ?>>Oldest First</option>
                        <option value="price_low" <?php echo $sort === 'price_low' ? 'selected' : ''; ?>>Price: Low to High</option>
                        <option value="price_high" <?php echo $sort === 'price_high' ? 'selected' : ''; ?>>Price: High to Low</option>
                    </select>
                </div>

                <div class="filter-group">
                    <label>&nbsp;</label>
                    <button type="submit" class="filter-button">
                        <i class="fas fa-filter"></i> Filter
                    </button>
                </div>
            </form>
        </section>

        <?php if (isset($error_message)): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i>
                <?php echo $error_message; ?>
            </div>
        <?php endif; ?>

        <section class="product-grid">
            <?php if (empty($products)): ?>
                <div class="no-products">
                    <i class="fas fa-box-open"></i>
                    <h2>No products found</h2>
                    <p>Try adjusting your filters or search terms</p>
                </div>
            <?php else: ?>
                <?php foreach($products as $product): ?>
                    <div class="product-card">
                        <?php
                        $stock_class = '';
                        $stock_text = '';
                        if ($product['quantity'] <= 0) {
                            $stock_class = 'out-of-stock';
                            $stock_text = 'Out of Stock';
                        } elseif ($product['quantity'] <= 5) {
                            $stock_class = 'low-stock';
                            $stock_text = 'Low Stock';
                        } else {
                            $stock_class = 'in-stock';
                            $stock_text = 'In Stock';
                        }
                        ?>
                        <div class="stock-status <?php echo $stock_class; ?>">
                            <i class="fas fa-circle"></i> <?php echo $stock_text; ?>
                        </div>
                        <div class="product-image-wrapper">
                            <img src="<?php echo htmlspecialchars($product['image_url']); ?>" 
                                 alt="<?php echo htmlspecialchars($product['name']); ?>"
                                 class="product-image"
                                 loading="lazy">
                        </div>
                        <div class="product-content">
                            <h3 class="product-title"><?php echo htmlspecialchars($product['name']); ?></h3>
                            <p class="product-description"><?php echo htmlspecialchars(substr($product['description'], 0, 100)); ?></p>
                            <div class="product-price">
                                <i class="fas fa-tag"></i> $<?php echo number_format($product['price'], 2); ?>
                            </div>
                            <div class="product-actions">
                                <a href="product.php?id=<?php echo $product['id']; ?>" class="view-details-btn">
                                    <i class="fas fa-eye"></i> View Details
                                </a>
                                <?php if (isset($_SESSION['user_id']) && $_SESSION['role'] !== 'admin' && $product['quantity'] > 0): ?>
                                    <div class="add-to-cart-form">
                                        <input type="number" class="quantity-input" 
                                               id="quantity-<?php echo $product['id']; ?>"
                                               name="quantity"
                                               value="1" min="1" max="<?php echo $product['quantity']; ?>">
                                        <button type="button" class="filter-button add-to-cart" 
                                                id="add-to-cart-<?php echo $product['id']; ?>"
                                                onclick="addToCart(<?php echo $product['id']; ?>)">
                                            <i class="fas fa-cart-plus"></i> Add
                                        </button>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </section>
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

        async function addToCart(productId) {
            const button = document.getElementById(`add-to-cart-${productId}`);
            const quantityInput = document.getElementById(`quantity-${productId}`);

            if (button.disabled) return;
            
            button.disabled = true;
            button.classList.add('loading');
            const originalText = button.innerHTML;
            button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Adding...';

            try {
                const quantity = quantityInput.value;
                console.log('Adding to cart:', { productId, quantity });

                const response = await fetch('add_to_cart.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `product_id=${productId}&quantity=${quantity}`
                });

                const data = await response.json();
                console.log('Server response:', data);
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