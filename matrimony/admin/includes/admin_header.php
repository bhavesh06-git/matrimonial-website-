<?php
/*
-- FILE: admin/includes/admin_header.php
-- This is the reusable header for the admin panel.
-- FIX: Removed all PHP logic. It now only prints HTML.
-- The parent file (e.g., dashboard.php) is responsible for all logic.
*/

// We assume the parent file has started the session and set this variable.
$admin_username = htmlspecialchars($_SESSION['admin_username'] ?? 'Admin');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - SoulMate</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Font Awesome for Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"/>
    
    <style>
        :root {
            --primary-color: #A12C2F;
            --secondary-color: #F7E7CE;
            --light-bg: #FCF8F3;
            --text-color: #333333;
            --white-color: #FFFFFF;
            --shadow: 0 4px 15px rgba(0, 0, 0, 0.07);
        }
        body {
            font-family: 'Poppins', sans-serif;
            background-color: var(--light-bg);
        }
        .admin-layout {
            display: flex;
            min-height: 100vh;
        }
        .admin-sidebar {
            width: 260px;
            background-color: var(--white-color);
            box-shadow: 0 0 30px rgba(0,0,0,0.05);
            padding: 1.5rem;
            position: fixed;
            height: 100%;
            border-right: 1px solid #eee;
        }
        .admin-main-content {
            margin-left: 260px;
            width: calc(100% - 260px);
            padding: 2rem;
        }
        .admin-brand {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--primary-color);
            text-decoration: none;
            display: block;
            text-align: center;
            padding-bottom: 1.5rem;
            border-bottom: 1px solid #eee;
            margin-bottom: 1.5rem;
        }
        .admin-nav .nav-link {
            color: #555;
            font-weight: 500;
            padding: 0.75rem 1rem;
            border-radius: 8px;
            margin-bottom: 0.25rem;
            transition: all 0.3s ease;
        }
        .admin-nav .nav-link i {
            margin-right: 10px;
            width: 20px;
            text-align: center;
        }
        .admin-nav .nav-link:hover,
        .admin-nav .nav-link.active {
            background-color: var(--primary-color);
            color: var(--white-color);
        }
        .admin-header {
            background-color: var(--white-color);
            padding: 1rem 2rem;
            border-radius: 12px;
            box-shadow: var(--shadow);
            margin-bottom: 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .admin-header h3 {
            margin-bottom: 0;
            font-weight: 600;
            color: var(--primary-color);
        }
        .admin-user-info {
            font-weight: 500;
        }
        .admin-user-info a {
            color: #dc3545;
            text-decoration: none;
            margin-left: 1rem;
            font-weight: 600;
        }
    </style>
</head>
<body>

<div class="admin-layout">
    <!-- Sidebar -->
    <nav class="admin-sidebar">
        <a class="admin-brand" href="dashboard.php">
            <i class="fas fa-heart-pulse"></i> SoulMate Admin
        </a>
        
        <ul class="nav flex-column admin-nav">
            <li class="nav-item">
                <!-- FIX: Added dynamic 'active' class check -->
                <a class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'dashboard.php') ? 'active' : ''; ?>" href="dashboard.php">
                    <i class="fas fa-tachometer-alt"></i> Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'manage_users.php') ? 'active' : ''; ?>" href="manage_users.php">
                    <i class="fas fa-users"></i> Manage Users
                </a>
            </li>
            <li class="nav-item">
                <!-- FIX: Corrected path to go up one level -->
                <a class="nav-link" href="../index.php" target="_blank">
                    <i class="fas fa-globe"></i> View Live Site
                </a>
            </li>
        </ul>
    </nav>

    <!-- Main Content -->
    <main class="admin-main-content">
        <!-- Content Header -->
        <header class="admin-header">
            <h3><?php echo $page_title ?? 'Dashboard'; ?></h3>
            <div class="admin-user-info">
                Welcome, <strong><?php echo $admin_username; ?></strong>
                <!-- FIX: Added logout.php -->
                <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </header>