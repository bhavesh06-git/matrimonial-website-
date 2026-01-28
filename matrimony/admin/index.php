<?php
/*
-- FILE: admin/index.php
-- This is the admin login page.
-- FIX: Corrected paths and added a check to redirect if already logged in.
*/

// --- Must include DB file first to start the session ---
// Correct path: up one level, then down to includes/
require_once '../includes/db.php'; 

// --- Redirect to dashboard if already logged in ---
// This must happen BEFORE any HTML is printed.
if (isset($_SESSION['admin_user_id'])) {
    header('Location: dashboard.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - SoulMate</title>
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
            <h3><i class="fas fa-user-shield me-2"></i> Admin Panel</h3>
        </div>
        <div class="login-card-body">
            
            <?php if (isset($_GET['error'])): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($_GET['error']); ?></div>
            <?php endif; ?>
             <?php if (isset($_GET['status']) && $_GET['status'] == 'loggedout'): ?>
                <div class="alert alert-success">You have been logged out.</div>
            <?php endif; ?>
            
            <form action="login_process.php" method="POST">
                <div class="mb-3">
                    <label for="username" class="form-label">Username</label>
                    <input type="text" class="form-control" id="username" name="username" required>
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" class="form-control" id="password" name="password" required>
                </div>
                <button type="submit" class="btn btn-primary-custom w-100 mt-3">Login</button>
            </form>
            
            <?php if (file_exists('register.php')): ?>
                 <p class="text-center mt-3 mb-0">
                    <a href="register.php">Create a new admin account</a>
                 </p>
            <?php endif; ?>

        </div>
    </div>

    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>