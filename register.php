<?php
session_start();
require_once 'config/database.php';

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

// Handle registration form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Validate input
    $errors = [];
    
    if (strlen($username) < 3) {
        $errors[] = "Username must be at least 3 characters long";
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format";
    }
    
    if (strlen($password) < 6) {
        $errors[] = "Password must be at least 6 characters long";
    }
    
    if ($password !== $confirm_password) {
        $errors[] = "Passwords do not match";
    }

    // Check if username or email already exists
    try {
        $stmt = $conn->prepare("SELECT COUNT(*) FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$username, $email]);
        if ($stmt->fetchColumn() > 0) {
            $errors[] = "Username or email already exists";
        }
    } catch(PDOException $e) {
        $errors[] = "Error checking user existence: " . $e->getMessage();
    }

    // If no errors, create the user
    if (empty($errors)) {
        try {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, 'user')");
            $stmt->execute([$username, $email, $hashed_password]);

            // Log the user in
            $_SESSION['user_id'] = $conn->lastInsertId();
            $_SESSION['username'] = $username;
            $_SESSION['role'] = 'user';

            header('Location: index.php');
            exit();
        } catch(PDOException $e) {
            $errors[] = "Error creating user: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - CycyStore</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .register-container {
            max-width: 400px;
            margin: 2rem auto;
            padding: 2rem;
            background: rgba(255, 255, 240, 0.9);
            border-radius: var(--border-radius-lg);
            box-shadow: var(--shadow-lg);
            border: 1px solid var(--primary-light);
        }

        .register-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .register-header h1 {
            color: var(--primary-dark);
            font-size: 2rem;
            margin-bottom: 0.5rem;
        }

        .register-header p {
            color: var(--primary-color);
            opacity: 0.8;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: var(--primary-dark);
            font-weight: 500;
            font-size: 0.875rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .form-group input {
            width: 100%;
            padding: 0.8rem;
            border: 1px solid rgba(99, 102, 241, 0.2);
            border-radius: var(--border-radius-md);
            font-size: 1rem;
            transition: all 0.3s ease;
            background: var(--surface-color);
        }

        .form-group input:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
            outline: none;
        }

        .register-btn {
            width: 100%;
            padding: 1rem;
            background: var(--primary-color);
            color: var(--surface-color);
            border: none;
            border-radius: var(--border-radius-md);
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-size: 0.875rem;
        }

        .register-btn:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
        }

        .login-link {
            text-align: center;
            margin-top: 1.5rem;
        }

        .login-link a {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .login-link a:hover {
            color: var(--primary-dark);
        }

        .error-message {
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid var(--error);
            color: var(--error);
            padding: 1rem;
            border-radius: var(--border-radius-md);
            margin-bottom: 1.5rem;
            text-align: center;
            font-size: 0.875rem;
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="container">
        <div class="register-container">
            <div class="register-header">
                <h1>Create Account</h1>
                <p>Join our community today</p>
            </div>

            <?php if (!empty($errors)): ?>
                <div class="error-message">
                    <?php foreach ($errors as $error): ?>
                        <p><?php echo $error; ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" required minlength="3" value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>">
                </div>

                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" required value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required minlength="6">
                </div>

                <div class="form-group">
                    <label for="confirm_password">Confirm Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" required minlength="6">
                </div>

                <button type="submit" class="register-btn">Create Account</button>

                <div class="login-link">
                    <p>Already have an account? <a href="login.php">Login here</a></p>
                </div>
            </form>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
    <script src="assets/js/main.js"></script>
</body>
</html> 