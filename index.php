<?php
session_start();
require_once 'config/database.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CycyStore - Your Online Shop</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
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
                    <a href="index.php" class="active">
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
        <section class="hero">
            <h1>Welcome to CycyStore</h1>
            <p>Discover amazing products at great prices!</p>
        </section>

        <section class="featured-products">
            <h2>Featured Products</h2>
            <div class="featured-grid">
                <?php
                try {
                    $stmt = $conn->query("SELECT * FROM products ORDER BY created_at DESC LIMIT 6");
                    while($product = $stmt->fetch(PDO::FETCH_ASSOC)) {
                        ?>
                        <div class="featured-card">
                            <div class="featured-image-wrapper">
                                <img src="<?php echo htmlspecialchars($product['image_url']); ?>" 
                                     alt="<?php echo htmlspecialchars($product['name']); ?>"
                                     class="featured-image"
                                     loading="lazy">
                            </div>
                            <div class="featured-content">
                                <h3 class="featured-title"><?php echo htmlspecialchars($product['name']); ?></h3>
                                <div class="featured-price">$<?php echo number_format($product['price'], 2); ?></div>
                                <a href="product.php?id=<?php echo $product['id']; ?>" class="featured-link">
                                    View Details
                                </a>
                            </div>
                        </div>
                        <?php
                    }
                } catch(PDOException $e) {
                    echo "Error: " . $e->getMessage();
                }
                ?>
            </div>
        </section>
    </main>

    <footer class="footer">
        <div class="footer-content">
            <div class="footer-section">
                <h3>About Us</h3>
                <p>CycyStore is your one-stop shop for all your needs. We offer quality products at competitive prices.</p>
            </div>
            <div class="footer-section">
                <h3>Quick Links</h3>
                <p><a href="index.php">Home</a></p>
                <p><a href="products.php">Products</a></p>
                <p><a href="contact.php">Contact</a></p>
            </div>
            <div class="footer-section">
                <h3>Connect With Us</h3>
                <div class="social-links">
                    <a href="#"><i class="fab fa-facebook-f"></i></a>
                    <a href="#"><i class="fab fa-twitter"></i></a>
                    <a href="#"><i class="fab fa-instagram"></i></a>
                </div>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; <?php echo date('Y'); ?> CycyStore. All rights reserved.</p>
        </div>
    </footer>
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