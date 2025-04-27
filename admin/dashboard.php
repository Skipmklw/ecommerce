<?php
session_start();
require_once '../config/database.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

// Handle product deletion
if (isset($_POST['delete_product'])) {
    $product_id = $_POST['product_id'];
    try {
        $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
        $stmt->execute([$product_id]);
        $success_message = "Product deleted successfully!";
    } catch(PDOException $e) {
        $error_message = "Error deleting product: " . $e->getMessage();
    }
}

// Fetch dashboard statistics
try {
    // Total products
    $stmt = $conn->query("SELECT COUNT(*) FROM products");
    $total_products = $stmt->fetchColumn();

    // Total users
    $stmt = $conn->query("SELECT COUNT(*) FROM users WHERE role = 'user'");
    $total_users = $stmt->fetchColumn();

    // Low stock products
    $stmt = $conn->query("SELECT COUNT(*) FROM products WHERE quantity <= 5");
    $low_stock = $stmt->fetchColumn();

    // Recent products
    $stmt = $conn->query("SELECT * FROM products ORDER BY created_at DESC LIMIT 5");
    $recent_products = $stmt->fetchAll();

} catch(PDOException $e) {
    $error_message = "Error fetching statistics: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - CycyStore</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .dashboard {
            padding: var(--spacing-lg);
            background-color: var(--background-color);
            min-height: 100vh;
        }

        .dashboard-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: var(--spacing-xl);
            padding: var(--spacing-lg);
            background: linear-gradient(135deg, var(--primary-dark), var(--primary-color));
            border-radius: var(--border-radius-lg);
            color: var(--surface-color);
        }

        .dashboard-title {
            font-size: 1.75rem;
            font-weight: 700;
        }

        .dashboard-actions {
            display: flex;
            gap: var(--spacing-md);
        }

        .dashboard-btn {
            padding: 0.75rem 1.5rem;
            border-radius: var(--border-radius-md);
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.875rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .primary-btn {
            background-color: var(--secondary-color);
            color: var(--surface-color);
        }

        .primary-btn:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: var(--spacing-lg);
            margin-bottom: var(--spacing-xl);
        }

        .stat-card {
            background: var(--surface-color);
            padding: var(--spacing-lg);
            border-radius: var(--border-radius-lg);
            box-shadow: var(--shadow-sm);
            transition: all 0.3s ease;
            border: 1px solid rgba(87, 180, 186, 0.1);
        }

        .stat-card:hover {
            transform: translateY(-4px);
            box-shadow: var(--shadow-md);
        }

        .stat-header {
            display: flex;
            align-items: center;
            gap: var(--spacing-md);
            margin-bottom: var(--spacing-md);
        }

        .stat-icon {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
            color: var(--surface-color);
        }

        .stat-title {
            font-size: 1.1rem;
            color: var(--text-color);
            font-weight: 600;
        }

        .stat-value {
            font-size: 2rem;
            font-weight: 700;
            color: var(--primary-dark);
        }

        .recent-products {
            background: var(--surface-color);
            border-radius: var(--border-radius-lg);
            padding: var(--spacing-lg);
            box-shadow: var(--shadow-sm);
            border: 1px solid rgba(87, 180, 186, 0.1);
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: var(--spacing-lg);
            padding-bottom: var(--spacing-md);
            border-bottom: 2px solid var(--primary-light);
        }

        .section-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--primary-dark);
        }

        .product-table {
            width: 100%;
            border-collapse: collapse;
        }

        .product-table th,
        .product-table td {
            padding: var(--spacing-md);
            text-align: left;
            border-bottom: 1px solid rgba(87, 180, 186, 0.1);
        }

        .product-table th {
            font-weight: 600;
            color: var(--text-color);
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.5px;
        }

        .product-table tr:hover {
            background-color: rgba(87, 180, 186, 0.05);
        }

        .table-actions {
            display: flex;
            gap: var(--spacing-sm);
        }

        .action-btn {
            padding: 0.5rem;
            border-radius: var(--border-radius-sm);
            border: none;
            cursor: pointer;
            transition: all 0.3s ease;
            color: var(--surface-color);
        }

        .edit-btn {
            background-color: var(--primary-color);
        }

        .delete-btn {
            background-color: var(--secondary-color);
        }

        .action-btn:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-sm);
        }

        .stock-badge {
            padding: 0.25rem 0.5rem;
            border-radius: var(--border-radius-sm);
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
        }

        .low-stock {
            background-color: rgba(254, 79, 45, 0.1);
            color: var(--secondary-color);
        }

        .in-stock {
            background-color: rgba(87, 180, 186, 0.1);
            color: var(--primary-color);
        }

        @media (max-width: 768px) {
            .dashboard-header {
                flex-direction: column;
                gap: var(--spacing-md);
                text-align: center;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }

            .product-table {
                font-size: 0.875rem;
            }

            .table-actions {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <?php include '../includes/header.php'; ?>

    <main class="dashboard">
        <div class="dashboard-header">
            <h1 class="dashboard-title">Admin Dashboard</h1>
            <div class="dashboard-actions">
                <a href="add_product.php" class="dashboard-btn primary-btn">
                    <i class="fas fa-plus"></i>
                    Add Product
                </a>
            </div>
        </div>

        <?php if (isset($success_message)): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                <?php echo $success_message; ?>
            </div>
        <?php endif; ?>

        <?php if (isset($error_message)): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i>
                <?php echo $error_message; ?>
            </div>
        <?php endif; ?>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-header">
                    <div class="stat-icon">
                        <i class="fas fa-box"></i>
                    </div>
                    <h3 class="stat-title">Total Products</h3>
                </div>
                <div class="stat-value"><?php echo $total_products; ?></div>
            </div>

            <div class="stat-card">
                <div class="stat-header">
                    <div class="stat-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <h3 class="stat-title">Total Users</h3>
                </div>
                <div class="stat-value"><?php echo $total_users; ?></div>
            </div>

            <div class="stat-card">
                <div class="stat-header">
                    <div class="stat-icon">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <h3 class="stat-title">Low Stock Items</h3>
                </div>
                <div class="stat-value"><?php echo $low_stock; ?></div>
            </div>
        </div>

        <section class="recent-products">
            <div class="section-header">
                <h2 class="section-title">Recent Products</h2>
                <a href="products.php" class="dashboard-btn primary-btn">
                    <i class="fas fa-list"></i>
                    View All
                </a>
            </div>

            <table class="product-table">
                <thead>
                    <tr>
                        <th>Product Name</th>
                        <th>Price</th>
                        <th>Stock</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recent_products as $product): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($product['name']); ?></td>
                        <td>₱<?php echo number_format($product['price'], 2); ?></td>
                        <td><?php echo $product['quantity']; ?></td>
                        <td>
                            <span class="stock-badge <?php echo $product['quantity'] <= 5 ? 'low-stock' : 'in-stock'; ?>">
                                <?php echo $product['quantity'] <= 5 ? 'Low Stock' : 'In Stock'; ?>
                            </span>
                        </td>
                        <td class="table-actions">
                            <a href="edit_product.php?id=<?php echo $product['id']; ?>" class="action-btn edit-btn" title="Edit">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                <button type="submit" name="delete_product" class="action-btn delete-btn" title="Delete" 
                                        onclick="return confirm('Are you sure you want to delete this product?')">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </section>
    </main>

    <?php include '../includes/footer.php'; ?>
    <script src="../assets/js/main.js"></script>
</body>
</html> 