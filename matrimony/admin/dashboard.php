<?php
/*
-- FILE: admin/dashboard.php
-- This is the admin dashboard page.
-- FIX: Contains all logic (includes, security check) BEFORE printing the header.
*/

// --- 1. PHP Logic First ---
// Correct path: up one level, then down to includes/
require_once '../includes/db.php'; 
require_once '../includes/functions.php'; 

// Security check: If admin is not logged in, redirect to login page
if (!isset($_SESSION['admin_user_id']) || !isset($_SESSION['admin_username'])) {
    // This redirect is SAFE because no HTML has been sent.
    redirect('index.php?error=Please login to access the admin panel.');
}

// --- 2. Page-Specific Logic ---
// Fetch statistics
$total_users = $conn->query("SELECT COUNT(*) as count FROM users")->fetch_assoc()['count'];
$active_users = $conn->query("SELECT COUNT(*) as count FROM users WHERE profile_status = 'Active'")->fetch_assoc()['count'];
$pending_users = $conn->query("SELECT COUNT(*) as count FROM users WHERE profile_status = 'Pending'")->fetch_assoc()['count'];
$total_matches = $conn->query("SELECT COUNT(*) as count FROM matches")->fetch_assoc()['count'];


// --- 3. Now, Include HTML Header ---
$page_title = 'Dashboard';
require_once 'includes/admin_header.php';
?>

<!-- 4. Page Content -->
<div class="row">
    <div class="col-md-4 mb-4">
        <div class="card text-white bg-primary">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="card-title fs-2"><?php echo $total_users; ?></h5>
                    <p class="card-text mb-0">Total Users</p>
                </div>
                <i class="fas fa-users fa-3x"></i>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-4">
        <div class="card text-white bg-success">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="card-title fs-2"><?php echo $active_users; ?></h5>
                    <p class="card-text mb-0">Active Profiles</p>
                </div>
                <i class="fas fa-user-check fa-3x"></i>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-4">
        <div class="card text-white bg-warning">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="card-title fs-2"><?php echo $pending_users; ?></h5>
                    <p class="card-text mb-0">Pending Approval</p>
                </div>
                <i class="fas fa-user-clock fa-3x"></i>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-4">
        <div class="card text-white bg-info">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="card-title fs-2"><?php echo $total_matches; ?></h5>
                    <p class="card-text mb-0">Total Matches</p>
                </div>
                <i class="fas fa-heart fa-3x"></i>
            </div>
        </div>
    </div>
</div>

<div class="card mt-4" style="border: none; box-shadow: var(--shadow);">
    <div class="card-header" style="background-color: var(--white-color);">
        <h5 class="mb-0">Quick Actions</h5>
    </div>
    <div class="card-body">
        <p>Welcome to the SoulMate Admin Panel. From here you can manage all users on the platform.</p>
        <a href="manage_users.php" class="btn btn-primary-custom" style="background-color: var(--primary-color);">
            <i class="fas fa-users-cog me-2"></i> Manage All Users
        </a>
    </div>
</div>

<?php
// --- 5. Include HTML Footer ---
require_once 'includes/admin_footer.php';
?>