<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>
<nav class="navbar">
    <div class="nav-content">
        <div class="nav-brand">
            <a href="index.php">CycyStore</a>
        </div>
        <div class="nav-links">
            <a href="index.php" <?php echo $current_page === 'index.php' ? 'class="active"' : ''; ?>>
                <i class="fas fa-home"></i>
                Home
            </a>
            <a href="products.php" <?php echo $current_page === 'products.php' ? 'class="active"' : ''; ?>>
                <i class="fas fa-shopping-bag"></i>
                Products
            </a>
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="cart.php" <?php echo $current_page === 'cart.php' ? 'class="active"' : ''; ?>>
                    <i class="fas fa-shopping-cart"></i>
                    Cart
                </a>
                <?php if ($_SESSION['role'] === 'admin'): ?>
                    <a href="admin/dashboard.php" <?php echo $current_page === 'dashboard.php' ? 'class="active"' : ''; ?>>
                        <i class="fas fa-tachometer-alt"></i>
                        Dashboard
                    </a>
                <?php endif; ?>
                <a href="logout.php">
                    <i class="fas fa-sign-out-alt"></i>
                    Logout
                </a>
            <?php else: ?>
                <a href="login.php" <?php echo $current_page === 'login.php' ? 'class="active"' : ''; ?>>
                    <i class="fas fa-sign-in-alt"></i>
                    Login
                </a>
                <a href="register.php" <?php echo $current_page === 'register.php' ? 'class="active"' : ''; ?>>
                    <i class="fas fa-user-plus"></i>
                    Register
                </a>
            <?php endif; ?>
        </div>
    </div>
</nav> 