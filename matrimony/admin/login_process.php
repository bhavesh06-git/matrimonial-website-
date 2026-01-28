<?php
/*
-- FILE: admin/login_process.php
-- This file handles the admin login.
-- FIX: Corrected paths.
*/

// --- Must include DB file first to start the session ---
// Correct path: up one level, then down to includes/
require_once '../includes/db.php'; 

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    if (empty($username) || empty($password)) {
        header('Location: index.php?error=Username and password are required.');
        exit();
    }

    // Prepare and execute statement to find admin
    $stmt = $conn->prepare("SELECT * FROM admin_users WHERE username = ?");
    if (!$stmt) {
        // This will happen if the table or column doesn't exist
        header('Location: index.php?error=Database query failed. Check table/column names.');
        exit();
    }
    
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $admin = $result->fetch_assoc();
        
        // Verify password
        if (password_verify($password, $admin['password'])) {
            // Password is correct! Start the admin session.
            session_regenerate_id(true); // Security best practice
            $_SESSION['admin_user_id'] = $admin['admin_id'];
            $_SESSION['admin_username'] = $admin['username'];
            
            // Update last login time
            $update_stmt = $conn->prepare("UPDATE admin_users SET last_login = NOW() WHERE admin_id = ?");
            $update_stmt->bind_param("i", $admin['admin_id']);
            $update_stmt->execute();
            $update_stmt->close();
            
            // Redirect to the admin dashboard
            header('Location: dashboard.php');
            exit();
            
        } else {
            // Invalid password
            header('Location: index.php?error=Invalid username or password.');
            exit();
        }
    } else {
        // No user found
        header('Location: index.php?error=Invalid username or password.');
        exit();
    }
    
    $stmt->close();
    $conn->close();

} else {
    // Not a POST request
    header('Location: index.php');
    exit();
}
?>