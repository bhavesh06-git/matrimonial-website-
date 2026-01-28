<?php
// We need the header to start the session and connect to the DB
require_once 'includes/header.php';

// If user is not logged in, redirect to login page
if (!isLoggedIn()) {
    redirect('login.php');
}

$user_id = $_SESSION['user_id'];

// --- Fetch Current User Data (for sidebar and current image) ---
$user = getLoggedInUser($conn);
if (!$user) {
    // This should not happen if isLoggedIn() passed, but as a safeguard
    redirect('logout.php');
}

// Handle Image Path
$image_path = 'uploads/profiles/default.png'; // Default
if (!empty($user['profile_image'])) {
    if (filter_var($user['profile_image'], FILTER_VALIDATE_URL)) {
        $image_path = $user['profile_image'];
    } else if (file_exists('uploads/profiles/' . $user['profile_image'])) {
        $image_path = 'uploads/profiles/' . $user['profile_image'];
    }
}
if ($image_path == 'uploads/profiles/default.png' && !file_exists($image_path)) {
     $image_path = 'https://placehold.co/400x400/F7E7CE/A12C2F?text=Photo';
}

// --- Fetch Partner Preferences ---
$pref_stmt = $conn->prepare("SELECT * FROM partner_preferences WHERE user_id = ?");
$pref_stmt->bind_param("i", $user_id);
$pref_stmt->execute();
$pref_result = $pref_stmt->get_result();
$preferences = $pref_result->fetch_assoc();
$pref_stmt->close();

$matches = [];
$has_preferences = false;

if ($preferences) {
    $has_preferences = true;
    
    // --- Build the Dynamic Match Query ---
    // This logic is now corrected to match the columns in your
    // partner_preferences table from matrimony_schema.sql
    
    // Start with the base query
    $sql = "SELECT * FROM users WHERE 
                id != ? 
                AND profile_status = 'Active'
            ";
    
    // We will dynamically build the parameters
    // $params[0] = type string (e.g., "iss")
    // $params[1...] = values
    $params = ["i", $user_id];

    
    // --- Add preferences to the query ---

    // 1. Age Range (Required)
    // Calculate DOB ranges from age preferences
    $max_age_date = (new DateTime("today -{$preferences['min_age']} years"))->format('Y-m-d');
    $min_age_date = (new DateTime("today -{$preferences['max_age']} years"))->format('Y-m-d');
    
    $sql .= " AND dob <= ? AND dob >= ?";
    $params[0] .= "ss";
    $params[] = $max_age_date;
    $params[] = $min_age_date;

    // 2. Gender (Optional)
    if (!empty($preferences['gender'])) {
        $sql .= " AND gender = ?";
        $params[0] .= "s";
        $params[] = $preferences['gender'];
    }

    // 3. Country (Optional)
    if (!empty($preferences['country'])) {
        $sql .= " AND country LIKE ?";
        $params[0] .= "s";
        $params[] = '%' . $preferences['country'] . '%';
    }

    // 4. Marital Status (Optional)
    if (!empty($preferences['marital_status'])) {
        $sql .= " AND marital_status = ?";
        $params[0] .= "s";
        $params[] = $preferences['marital_status'];
    }

    // 5. Religion (Optional)
    if (!empty($preferences['religion'])) {
        $sql .= " AND religion LIKE ?";
        $params[0] .= "s";
        $params[] = '%' . $preferences['religion'] . '%';
    }

    // 6. Caste (Optional)
    if (!empty($preferences['caste'])) {
        $sql .= " AND caste LIKE ?";
        $params[0] .= "s";
        $params[] = '%' . $preferences['caste'] . '%';
    }

    // 7. Education (Optional)
    if (!empty($preferences['education'])) {
        $sql .= " AND education LIKE ?";
        $params[0] .= "s";
        $params[] = '%' . $preferences['education'] . '%';
    }

    // 8. Occupation (Optional)
    if (!empty($preferences['occupation'])) {
        $sql .= " AND occupation LIKE ?";
        $params[0] .= "s";
        $params[] = '%' . $preferences['occupation'] . '%';
    }

    // Add ordering and limit
    $sql .= " ORDER BY registration_date DESC LIMIT 20";

    $match_stmt = $conn->prepare($sql);
    
    if ($match_stmt) {
        // Use the "splat" operator (...) to pass the dynamic array of parameters
        $match_stmt->bind_param(...$params); 
        $match_stmt->execute();
        $match_result = $match_stmt->get_result();
        $matches = $match_result->fetch_all(MYSQLI_ASSOC);
        $match_stmt->close();
    } else {
        // This will help debug if the SQL itself is wrong
        echo "Error preparing match query: " . $conn->error;
    }
}

?>

<!-- Main Content Area -->
<div class="page-section">
    <div class="container">
        <div class="row">

            <!-- Sidebar -->
            <!-- Sidebar -->
            <div class="col-lg-3">
                <div class="dashboard-sidebar p-4">
                    <div class="profile-summary text-center mb-4">
                        <img src="<?php echo htmlspecialchars($image_path); ?>" 
                            onerror="this.onerror=null; this.src='https://placehold.co/400x400/F7E7CE/A12C2F?text=Photo';"
                            alt="Profile Picture" 
                            style="width: 90px; height: 90px; object-fit: cover; border-radius: 50%;">
                        <h5 class="mt-2 mb-0"><?php echo htmlspecialchars($user['first_name']); ?></h5>
                        <p class="text-muted small">ID: <?php echo $user['id']; ?></p>
                    </div>
                    <!-- Dashboard Navigation -->
                    <ul class="nav flex-column dashboard-nav">
                        <li class="nav-item">
                            <a class="nav-link active" href="dashboard.php">
                                <i class="fas fa-tachometer-alt"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="edit_profile.php">
                                <i class="fas fa-user-edit"></i> Edit Profile
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link " href="edit_preferences.php">
                                <i class="fas fa-user-cog"></i> Partner Preferences
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="manage_photos.php">
                                <i class="fas fa-camera"></i> Manage Photos
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="interests.php">
                                <i class="fas fa-heart"></i> Interests
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="chat.php">
                                <i class="fas fa-comments"></i> Chat
                            </a>
                        </li>
                    </ul>
                </div>
            </div>


            <!-- Main Content -->
            <div class="col-lg-9">

                <!-- Welcome Banner -->
                <div class="dashboard-welcome-banner" data-aos="fade-up">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h2>Hello, <?php echo htmlspecialchars($user['first_name']); ?>!</h2>
                            <p class="mb-0">Welcome back to your dashboard. Here are the latest matches we've found based on your preferences.</p>
                        </div>
                        <div class="col-md-4 text-md-end mt-3 mt-md-0">
                            <a href="search.php" class="btn btn-light-custom">
                                <i class="fas fa-search me-2"></i> Refine Search
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Main Dashboard Content -->
                <div class="profile-details-main p-4">

                    <?php if (!$has_preferences): ?>
                        <!-- User has not set preferences yet -->
                        <div class="text-center p-5">
                            <i class="fas fa-user-cog fa-4x text-muted mb-4"></i>
                            <h4 class="text-primary">Set Your Preferences to Find Matches</h4>
                            <p class="text-muted">You haven't set your partner preferences yet. Set them now to get personalized matches on this dashboard.</p>
                            <a href="edit_preferences.php" class="btn btn-primary-custom btn-lg">Set Preferences Now</a>
                        </div>

                    <?php elseif (empty($matches)): ?>
                        <!-- User has preferences, but no matches were found -->
                        <div class="text-center p-5">
                            <i class="fas fa-search-minus fa-4x text-muted mb-4"></i>
                            <h4 class="text-primary">No Matches Found</h4>
                            <p class="text-muted">We couldn't find any profiles that match your current preferences. You might want to broaden your criteria.</p>
                            <a href="edit_preferences.php" class="btn btn-primary-custom btn-lg">Edit Preferences</a>
                        </div>

                    <?php else: ?>
                        <!-- Matches Found! -->
                        <h3 class="mb-4" style="color: var(--primary-color);">New Matches For You</h3>
                        <div class="row">
                            <?php foreach ($matches as $profile): ?>
                                <?php
                                // We need to calculate age for the profile card
                                // The include expects $profile and $age
                                $age = calculateAge($profile['dob']);
                                include 'includes/profile_card.php';
                                ?>
                            <?php endforeach; ?>
                        </div>

                    <?php endif; ?>
                </div>

            </div>
        </div>
    </div>
</div>

<?php
// Finally, include the footer
require_once 'includes/footer.php';
?>

