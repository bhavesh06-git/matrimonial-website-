<?php
// We must start the session on every page
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// --- CRITICAL FIX ---
// Use __DIR__ to ensure PHP always finds these files
// no matter where header.php is included from.
require_once __DIR__ . '/db.php'; 
require_once __DIR__ . '/functions.php';

// Get the current page name to set the "active" class
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SoulMate | Find Your Perfect Match</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Font Awesome for Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"/>
    
    <!-- Animate On Scroll (AOS) CSS -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    
    <!-- --- CRITICAL FIX --- -->
    <!-- Corrected the typo from 'assests' to 'assests' -->
    <link rel="stylesheet" href="assests/css/style.css">
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-light fixed-top">
    <div class="container">
        <a class="navbar-brand" href="index.php">
            <i class="fas fa-heart-pulse"></i> SoulMate
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto align-items-center">
                <li class="nav-item">
                    <a class="nav-link <?php echo ($current_page == 'index.php') ? 'active' : ''; ?>" href="index.php">Home</a>
                </li>
                
                <?php if (isLoggedIn()): ?>
                    <!-- Show these links ONLY if logged in -->
                    <li class="nav-item">
                        <a class="nav-link <?php echo ($current_page == 'search.php') ? 'active' : ''; ?>" href="search.php">Search</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo ($current_page == 'interests.php') ? 'active' : ''; ?>" href="interests.php">Interests</a>
                    </li>

                    <!-- "My Account" Dropdown -->
                    <li class="nav-item dropdown ms-lg-2">
                        <a class="nav-link dropdown-toggle btn-register-outline" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-user-circle me-1"></i> My Account
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                            <li>
                                <a class="dropdown-item <?php echo ($current_page == 'dashboard.php') ? 'active' : ''; ?>" href="dashboard.php">
                                    <i class="fas fa-tachometer-alt me-2"></i> My Matches
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item <?php echo ($current_page == 'edit_profile.php') ? 'active' : ''; ?>" href="edit_profile.php">
                                    <i class="fas fa-user-edit me-2"></i> Edit Profile
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item <?php echo ($current_page == 'edit_preferences.php') ? 'active' : ''; ?>" href="edit_preferences.php">
                                    <i class="fas fa-user-cog me-2"></i> Partner Preferences
                                </a>
                            </li>
                             <li>
                                <a class="dropdown-item <?php echo ($current_page == 'manage_photos.php') ? 'active' : ''; ?>" href="manage_photos.php">
                                    <i class="fas fa-camera me-2"></i> Manage Photos
                                </a>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item dropdown-item-danger" href="logout.php">
                                    <i class="fas fa-sign-out-alt me-2"></i> Logout
                                </a>
                            </li>
                        </ul>
                    </li>

                <?php else: ?>
                    <!-- Show these links ONLY if NOT logged in -->
                    <li class="nav-item">
                        <a class="nav-link" href="login.php">Login</a>
                    </li>
                    <li class="nav-item ms-lg-2">
                        <a class="nav-link btn-register" href="register.php">Register Free</a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<main class="main-content">

