<?php
// We need the header to start the session and connect to the DB
require_once 'includes/header.php';

// If user is not logged in, redirect to login page
if (!isLoggedIn()) {
    redirect('login.php');
}

$user_id = $_SESSION['user_id'];
$success_msg = '';
$error_msg = '';

// --- Handle Form Submission (POST Request) ---
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Sanitize and retrieve all form data
    $min_age = intval($_POST['min_age']);
    $max_age = intval($_POST['max_age']);
    $gender = sanitize($_POST['gender']); // <-- FIXED
    $country = sanitize($_POST['country']); // <-- FIXED
    $marital_status = sanitize($_POST['marital_status']);
    $religion = sanitize($_POST['religion']);
    $caste = sanitize($_POST['caste']);
    $education = sanitize($_POST['education']);
    $occupation = sanitize($_POST['occupation']);

    // Check if preferences already exist for this user
    $check_stmt = $conn->prepare("SELECT user_id FROM partner_preferences WHERE user_id = ?");
    $check_stmt->bind_param("i", $user_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    $check_stmt->close();

    if ($check_result->num_rows > 0) {
        // --- UPDATE existing preferences ---
        // FIX: SQL query now matches the database schema
        $sql = "UPDATE partner_preferences SET 
                    min_age = ?, max_age = ?, gender = ?, country = ?, 
                    marital_status = ?, religion = ?, caste = ?, education = ?, occupation = ?
                WHERE user_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iisssssssi", 
            $min_age, $max_age, $gender, $country,
            $marital_status, $religion, $caste, $education, $occupation,
            $user_id
        );
    } else {
        // --- INSERT new preferences ---
        // FIX: SQL query now matches the database schema
        $sql = "INSERT INTO partner_preferences 
                    (user_id, min_age, max_age, gender, country, marital_status, religion, caste, education, occupation) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iiisssssss", 
            $user_id, $min_age, $max_age, $gender, $country,
            $marital_status, $religion, $caste, $education, $occupation
        );
    }

    // Execute the statement
    if ($stmt && $stmt->execute()) { // Line 51/52 would have been here
        $success_msg = "Your partner preferences have been saved!";
    } else {
        // This will show the actual SQL error if it fails
        $error_msg = "An error occurred: " . $conn->error;
    }
    $stmt->close();
}

// --- Fetch Current User Data (for sidebar) ---
$user = getLoggedInUser($conn);
if (!$user) {
    // This should not happen if isLoggedIn() passed, but as a safeguard
    redirect('logout.php');
}


// --- Fetch Current Preferences (for pre-filling form) ---
$pref_stmt = $conn->prepare("SELECT * FROM partner_preferences WHERE user_id = ?");
$pref_stmt->bind_param("i", $user_id);
$pref_stmt->execute();
$pref_result = $pref_stmt->get_result();
$preferences = $pref_result->fetch_assoc();
$pref_stmt->close();

// Set default values if no preferences are found
if (!$preferences) {
    $preferences = [
        'min_age' => 18, 'max_age' => 40, 'gender' => '', 'country' => '',
        'marital_status' => '', 'religion' => '', 'caste' => '', 'education' => '', 'occupation' => ''
    ];
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

?>

<!-- Main Content Area -->
<div class="page-section">
    <div class="container">
        <div class="row">

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
                            <a class="nav-link" href="dashboard.php">
                                <i class="fas fa-tachometer-alt"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="edit_profile.php">
                                <i class="fas fa-user-edit"></i> Edit Profile
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active" href="edit_preferences.php">
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
                <div class="profile-details-main p-4">
                    <h3 class="mb-4" style="color: var(--primary-color);">Partner Preferences</h3>
                    <p class="text-muted">Tell us what you are looking for in a partner. This will help us find the best matches for you on your dashboard.</p>
                    
                    <?php if ($success_msg): ?>
                        <div class="alert alert-success"><?php echo $success_msg; ?></div>
                    <?php endif; ?>
                    <?php if ($error_msg): ?>
                        <div class="alert alert-danger"><?php echo $error_msg; ?></div>
                    <?php endif; ?>

                    <form action="edit_preferences.php" method="POST">
                        
                        <!-- Section 1: Age & Height -->
                        <h5><i class="fas fa-user-friends me-2"></i>Basic Preferences</h5>
                        <hr class="my-3">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Age Range</label>
                                <div class="input-group">
                                    <input type="number" class="form-control" name="min_age" value="<?php echo htmlspecialchars($preferences['min_age']); ?>" placeholder="Min Age (e.g., 25)">
                                    <span class="input-group-text">to</span>
                                    <input type="number" class="form-control" name="max_age" value="<?php echo htmlspecialchars($preferences['max_age']); ?>" placeholder="Max Age (e.g., 32)">
                                </div>
                            </div>
                            <!-- FIX: Added Gender field -->
                            <div class="col-md-6">
                                <label for="gender" class="form-label">Gender</label>
                                <select id="gender" name="gender" class="form-select">
                                    <option value="" <?php echo ($preferences['gender'] == '') ? 'selected' : ''; ?>>Any</option>
                                    <option value="Male" <?php echo ($preferences['gender'] == 'Male') ? 'selected' : ''; ?>>Male</D option>
                                    <option value="Female" <?php echo ($preferences['gender'] == 'Female') ? 'selected' : ''; ?>>Female</option>
                                    <option value="Other" <?php echo ($preferences['gender'] == 'Other') ? 'selected' : ''; ?>>Other</option>
                                </select>
                            </div>
                        </div>

                        <!-- Section 2: Personal & Location Details -->
                        <h5 class="mt-5"><i class="fas fa-map-marker-alt me-2"></i>Location & Background</h5>
                        <hr class="my-3">
                        <div class="row g-3">
                             <!-- FIX: Added Country field -->
                            <div class="col-md-6">
                                <label for="country" class="form-label">Country (Leave blank for any)</label>
                                <input type="text" class="form-control" id="country" name="country" value="<?php echo htmlspecialchars($preferences['country']); ?>" placeholder="e.g., India">
                            </div>
                            <div class="col-md-6">
                                <label for="marital_status" class="form-label">Marital Status</label>
                                <select id="marital_status" name="marital_status" class="form-select">
                                    <option value="" <?php echo ($preferences['marital_status'] == '') ? 'selected' : ''; ?>>Any</orption>
                                    <option value="Never Married" <?php echo ($preferences['marital_status'] == 'Never Married') ? 'selected' : ''; ?>>Never Married</option>
                                    <option value="Divorced" <?php echo ($preferences['marital_status'] == 'Divorced') ? 'selected' : ''; ?>>Divorced</option>
                                    <option value="Widowed" <?php echo ($preferences['marital_status'] == 'Widowed') ? 'selected' : ''; ?>>Widowed</Goption>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="religion" class="form-label">Religion (Leave blank for any)</label>
                                <input type="text" class="form-control" id="religion" name="religion" value="<?php echo htmlspecialchars($preferences['religion']); ?>" placeholder="e.g., Hindu">
                            </div>
                            <div class="col-md-6">
                                <label for="caste" class="form-label">Caste / Community (Leave blank for any)</label>
                                <input type="text" class="form-control" id="caste" name="caste" value="<?php echo htmlspecialchars($preferences['caste']); ?>" placeholder="e.g., Brahmin, Maratha">
                            </div>
                        </div>

                        <!-- Section 3: Professional Details -->
                        <h5 class="mt-5"><i class="fas fa-briefcase me-2"></i>Education & Career</h5>
                        <hr class="my-3">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="education" class="form-label">Education (Leave blank for any)</label>
                                <input type="text" class="form-control" id="education" name="education" value="<?php echo htmlspecialchars($preferences['education']); ?>" placeholder="e.g., M.Tech, MBA">
                            </div>
                            <div class="col-md-6">
                                <label for="occupation" class="form-label">Occupation (Leave blank for any)</label>
                                <input type="text" class="form-control" id="occupation" name="occupation" value="<?php echo htmlspecialchars($preferences['occupation']); ?>" placeholder="e.g., Software Engineer">
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="mt-5 text-end">
                            <button type="submit" class="btn btn-primary-custom btn-lg">Save Preferences</button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Finally, include the footer
require_once 'includes/footer.php';
?>

