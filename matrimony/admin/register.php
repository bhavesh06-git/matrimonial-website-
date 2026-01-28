<?php
/*
-- FILE: admin/register.php
-- This page allows a new admin user to be created.
-- It includes self-submitting logic with validation and password hashing.
*/

// --- 1. PHP Logic First ---
// Correct path: up one level, then down to includes/
require_once '../includes/db.php'; 

$error_msg = '';
$success_msg = '';

// Check if the request is a POST request (form submission)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    // Sanitize inputs
    $full_name = trim($_POST['full_name']);
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);

    // --- Validation ---
    if (empty($full_name) || empty($username) || empty($password) || empty($confirm_password)) {
        $error_msg = "All fields are required.";
    } elseif (strlen($password) < 8) {
        $error_msg = "Password must be at least 8 characters long.";
    } elseif ($password !== $confirm_password) {
        $error_msg = "Passwords do not match.";
    } else {
        
        // --- Check if username already exists ---
        $stmt = $conn->prepare("SELECT admin_id FROM admin_users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->store_result();
        
        if ($stmt->num_rows > 0) {
            $error_msg = "Username already exists. Please choose another one.";
        } else {
            // --- All checks passed, create the admin ---
            
            // Hash the password securely
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            // Insert into the database
            $insert_stmt = $conn->prepare("INSERT INTO admin_users (username, full_name, password) VALUES (?, ?, ?)");
            $insert_stmt->bind_param("sss", $username, $full_name, $hashed_password);
            
            if ($insert_stmt->execute()) {
                $success_msg = "New admin user created successfully! You can now <a href='index.php'>login</a>.";
            } else {
                $error_msg = "Database error: Could not create admin user.";
            }
            $insert_stmt->close();
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Registration - SoulMate</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Font Awesome for Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"/>
    
    <style>
        :root {
            --primary-color: #A12C2F;
            --light-bg: #FCF8F3;
            --shadow: 0 10px 30px rgba(0, 0, 0, 0.07);
        }
        body {
            font-family: 'Poppins', sans-serif;
            background-color: var(--light-bg);
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 2rem 0;
        }
        .login-card {
            width: 100%;
            max-width: 450px;
            background-color: #fff;
            border-radius: 12px;
            box-shadow: var(--shadow);
            border: none;
            overflow: hidden;
        }
        .login-card-header {
            background-color: var(--primary-color);
            color: #fff;
            padding: 2rem;
            text-align: center;
        }
        .login-card-header h3 {
            font-weight: 600;
            margin-bottom: 0;
        }
        .login-card-body {
            padding: 2.5rem;
        }
        .form-label {
            font-weight: 500;
        }
        .btn-primary-custom {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            color: #fff;
            font-weight: 600;
            padding: 0.75rem;
            border-radius: 8px;
            transition: all 0.3s ease;
        }
        .btn-primary-custom:hover {
            background-color: #802225;
            border-color: #802225;
        }
    </style>
</head>
<body>
    <div class="login-card">
        <div class="login-card-header">
            <h3><i class="fas fa-user-plus me-2"></i> Create Admin User</h3>
        </div>
        <div class="login-card-body">
            
            <?php if ($error_msg): ?>
                <div class="alert alert-danger"><?php echo $error_msg; ?></div>
            <?php endif; ?>
            <?php if ($success_msg): ?>
                <div class="alert alert-success"><?php echo $success_msg; ?></div>
            <?php endif; ?>

            <?php if (empty($success_msg)): // Only show the form if we haven't just succeeded ?>
            <form action="register.php" method="POST" id="registerForm">
                <div class="mb-3">
                    <label for="full_name" class="form-label">Full Name</label>
                    <input type="text" class="form-control" id="full_name" name="full_name" required>
                </div>
                <div class="mb-3">
                    <label for="username" class="form-label">Username</label>
                    <input type="text" class="form-control" id="username" name="username" required>
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" class="form-control" id="password" name="password" required>
                    <div class="form-text">Must be at least 8 characters long.</div>
                </div>
                <div class="mb-3">
                    <label for="confirm_password" class="form-label">Confirm Password</label>
                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                </div>
                
                <!-- This div is used by admin.js for the live password check -->
                <div id="password-match-error" class="text-danger small mt-2" style="display: none;">Passwords do not match.</div>
                
                <button typeD="submit" class="btn btn-primary-custom w-100 mt-3">Register Admin</button>
            </form>
            <?php endif; ?>

            <p class="text-center mt-3 mb-0">
                <a href="index.php"><i class="fas fa-arrow-left me-1"></i> Back to Login</a>
            </p>

        </div>
    </div>

    <!-- Bootstrap JS Bundle -->a
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Admin JS for password matching -->
    <!-- Correct path: up one level, then down to assests/js/ -->
    <script src="../assests/js/admin.js"></script>
</body>
</html>