<?php
session_start();
require_once '../config/database.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

// Handle product submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $quantity = $_POST['quantity'];
    $category = $_POST['category'];
    
    // Handle file upload
    $image_url = '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = '../uploads/products/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        $file_extension = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        $file_name = uniqid() . '.' . $file_extension;
        $target_path = $upload_dir . $file_name;
        
        if (move_uploaded_file($_FILES['image']['tmp_name'], $target_path)) {
            $image_url = 'uploads/products/' . $file_name;
        }
    }

    try {
        $stmt = $conn->prepare("INSERT INTO products (name, description, price, quantity, category, image_url) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$name, $description, $price, $quantity, $category, $image_url]);
        $success_message = "Product added successfully!";
    } catch(PDOException $e) {
        $error_message = "Error adding product: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Product - CycyStore</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .form-container {
            max-width: 800px;
            margin: 2rem auto;
            padding: 2rem;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .form-group {
            margin-bottom: 1.5rem;
        }
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: bold;
        }
        .form-group input,
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 0.5rem;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 1rem;
        }
        .form-group textarea {
            height: 150px;
            resize: vertical;
        }
        .alert {
            padding: 1rem;
            margin-bottom: 1rem;
            border-radius: 4px;
        }
        .alert.success {
            background-color: #d4edda;
            color: #155724;
        }
        .alert.error {
            background-color: #f8d7da;
            color: #721c24;
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="nav-brand">
            <a href="../index.php">CycyStore</a>
        </div>
        <div class="nav-links">
            <a href="dashboard.php">Dashboard</a>
            <a href="../products.php">Products</a>
            <a href="../logout.php">Logout</a>
        </div>
    </nav>

    <div class="form-container">
        <h1>Add New Product</h1>

        <?php if (isset($success_message)): ?>
            <div class="alert success"><?php echo $success_message; ?></div>
        <?php endif; ?>

        <?php if (isset($error_message)): ?>
            <div class="alert error"><?php echo $error_message; ?></div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label for="name">Product Name</label>
                <input type="text" id="name" name="name" required>
            </div>

            <div class="form-group">
                <label for="description">Description</label>
                <textarea id="description" name="description" required></textarea>
            </div>

            <div class="form-group">
                <label for="price">Price</label>
                <input type="number" id="price" name="price" step="0.01" min="0" required>
            </div>

            <div class="form-group">
                <label for="quantity">Quantity</label>
                <input type="number" id="quantity" name="quantity" min="0" required>
            </div>

            <div class="form-group">
                <label for="category">Category</label>
                <select id="category" name="category" required>
                    <option value="">Select a category</option>
                    <option value="Electronics">Electronics</option>
                    <option value="Clothing">Clothing</option>
                    <option value="Books">Books</option>
                    <option value="Home & Garden">Home & Garden</option>
                    <option value="Sports">Sports</option>
                    <option value="Other">Other</option>
                </select>
            </div>

            <div class="form-group">
                <label for="image">Product Image</label>
                <input type="file" id="image" name="image" accept="image/*" required>
            </div>

            <button type="submit" class="btn">Add Product</button>
            <a href="dashboard.php" class="btn" style="background-color: #6c757d;">Cancel</a>
        </form>
    </div>

    <script src="../assets/js/main.js"></script>
</body>
</html> 