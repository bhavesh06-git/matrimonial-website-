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
    $first_name = sanitize($_POST['first_name']);
    $last_name = sanitize($_POST['last_name']);
    $phone_number = sanitize($_POST['phone_number']);
    $dob = sanitize($_POST['dob']); // Already in YYYY-MM-DD format from <input type="date">
    $gender = sanitize($_POST['gender']);
    
    // Profile Details
    $marital_status = sanitize($_POST['marital_status']);
    $height_cm = intval($_POST['height_cm']);
    $about_me = sanitize($_POST['about_me']);
    
    // Location
    $city = sanitize($_POST['city']);
    $state = sanitize($_POST['state']);
    $country = sanitize($_POST['country']);

    // Professional & Religious
    $religion = sanitize($_POST['religion']);
    $caste = sanitize($_POST['caste']);
    $education = sanitize($_POST['education']);
    $occupation = sanitize($_POST['occupation']);
    $annual_income = sanitize($_POST['annual_income']);

    // Prepare the UPDATE statement
    $sql = "UPDATE users SET 
                first_name = ?, last_name = ?, phone_number = ?, dob = ?, gender = ?,
                marital_status = ?, height_cm = ?, about_me = ?,
                city = ?, state = ?, country = ?,
                religion = ?, caste = ?, education = ?, occupation = ?, annual_income = ?
            WHERE id = ?";
    
    $stmt = $conn->prepare($sql);
    
    if ($stmt) {
        // Bind parameters
        $stmt->bind_param("ssssssisssssssssi", 
            $first_name, $last_name, $phone_number, $dob, $gender,
            $marital_status, $height_cm, $about_me,
            $city, $state, $country,
            $religion, $caste, $education, $occupation, $annual_income,
            $user_id
        );
        
        // Execute the statement
        if ($stmt->execute()) {
            $success_msg = "Your profile has been updated successfully!";
        } else {
            $error_msg = "An error occurred. Please try again.";
        }
        $stmt->close();
    } else {
        $error_msg = "Database error. Please try again later.";
    }
}

// --- Fetch Current User Data (for sidebar and current image) ---
$user_stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$user_stmt->bind_param("i", $user_id);
$user_stmt->execute();
$user_result = $user_stmt->get_result();
$user = $user_result->fetch_assoc();
$user_stmt->close();

$current_image = $user['profile_image'];
// Handle online URLs vs. uploaded files
if (filter_var($current_image, FILTER_VALIDATE_URL)) {
    $image_path = $current_image;
} else {
    $image_path = 'uploads/profiles/' . $current_image;
}

if (!$user) {
    // This should not happen if user is logged in
    redirect('logout.php');
}
?>

<!-- Main Content Area -->
<div class="dashboard-container" style="padding: 3rem 0;">
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
                            <a class="nav-link" href="dashboard.php">
                                <i class="fas fa-tachometer-alt"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active" href="edit_profile.php">
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
                <div class="dashboard-content-card p-4">
                    <h3 class="mb-4" style="color: var(--primary-color);">Edit Your Profile</h3>
                    
                    <?php if ($success_msg): ?>
                        <div class="alert alert-success"><?php echo $success_msg; ?></div>
                    <?php endif; ?>
                    <?php if ($error_msg): ?>
                        <div class="alert alert-danger"><?php echo $error_msg; ?></div>
                    <?php endif; ?>

                    <form action="edit_profile.php" method="POST">
                        
                        <!-- Section 1: Basic Details -->
                        <h5><i class="fas fa-user-alt me-2"></i>Basic Details</h5>
                        <hr class="my-3">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="first_name" class="form-label">First Name</label>
                                <input type="text" class="form-control" id="first_name" name="first_name" value="<?php echo htmlspecialchars($user['first_name']); ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label for="last_name" class="form-label">Last Name</label>
                                <input type="text" class="form-control" id="last_name" name="last_name" value="<?php echo htmlspecialchars($user['last_name']); ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label for="email" class="form-label">Email Address (Cannot be changed)</label>
                                <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" disabled>
                            </div>
                             <div class="col-md-6">
                                <label for="phone_number" class="form-label">Phone Number</label>
                                <input type="text" class="form-control" id="phone_number" name="phone_number" value="<?php echo htmlspecialchars($user['phone_number']); ?>">
                            </div>
                            <div class="col-md-6">
                                <label for="dob" class="form-label">Date of Birth</label>
                                <input type="date" class="form-control" id="dob" name="dob" value="<?php echo htmlspecialchars($user['dob']); ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label for="gender" class="form-label">Gender</label>
                                <select id="gender" name="gender" class="form-select" required>
                                    <option value="Male" <?php echo ($user['gender'] == 'Male') ? 'selected' : ''; ?>>Male</option>
                                    <option value="Female" <?php echo ($user['gender'] == 'Female') ? 'selected' : ''; ?>>Female</option>
                                    <option value="Other" <?php echo ($user['gender'] == 'Other') ? 'selected' : ''; ?>>Other</option>
                                </select>
                            </div>
                        </div>

                        <!-- Section 2: Personal & Location Details -->
                        <h5 class="mt-5"><i class="fas fa-info-circle me-2"></i>Personal & Location Details</h5>
                        <hr class="my-3">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label for="marital_status" class="form-label">Marital Status</label>
                                <select id="marital_status" name="marital_status" class="form-select" required>
                                    <option value="Never Married" <?php echo ($user['marital_status'] == 'Never Married') ? 'selected' : ''; ?>>Never Married</option>
                                    <option value="Divorced" <?php echo ($user['marital_status'] == 'Divorced') ? 'selected' : ''; ?>>Divorced</option>
                                    <option value="Widowed" <?php echo ($user['marital_status'] == 'Widowed') ? 'selected' : ''; ?>>Widowed</option>
                                    <option value="Awaiting Divorce" <?php echo ($user['marital_status'] == 'Awaiting Divorce') ? 'selected' : ''; ?>>Awaiting Divorce</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label for="height_cm" class="form-label">Height (in cm)</label>
                                <input type="number" class="form-control" id="height_cm" name="height_cm" value="<?php echo htmlspecialchars($user['height_cm']); ?>">
                            </div>
                            <div class="col-md-4">
                                <label for="city" class="form-label">City</label>
                                <input type="text" class="form-control" id="city" name="city" value="<?php echo htmlspecialchars($user['city']); ?>">
                            </div>
                            <div class="col-md-6">
                                <label for="state" class="form-label">State</label>
                                <input type="text" class="form-control" id="state" name="state" value="<?php echo htmlspecialchars($user['state']); ?>">
                            </div>
                            <div class="col-md-6">
                                <label for="country" class="form-label">Country</label>
                                <input type="text" class="form-control" id="country" name="country" value="<?php echo htmlspecialchars($user['country']); ?>">
                            </div>
                            <div class="col-12">
                                <label for="about_me" class="form-label">About Me (A few words about yourself)</label>
                                <textarea class="form-control" id="about_me" name="about_me" rows="4"><?php echo htmlspecialchars($user['about_me']); ?></textarea>
                            </div>
                        </div>

                        <!-- Section 3: Religious & Professional Details -->
                        <h5 class="mt-5"><i class="fas fa-book-reader me-2"></i>Religious & Professional Details</h5>
                        <hr class="my-3">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="religion" class="form-label">Religion</label>
                                <input type="text" class="form-control" id="religion" name="religion" value="<?php echo htmlspecialchars($user['religion']); ?>">
                            </div>
                            <div class="col-md-6">
                                <label for="caste" class="form-label">Caste / Community</label>
                                <input type="text" class="form-control" id="caste" name="caste" value="<?php echo htmlspecialchars($user['caste']); ?>">
                            </div>
                            <div class="col-md-6">
                                <label for="education" class="form-label">Highest Education</label>
                                <input type="text" class="form-control" id="education" name="education" value="<?php echo htmlspecialchars($user['education']); ?>" placeholder="e.g., M.Tech, MBA, MBBS">
                            </div>
                            <div class="col-md-6">
                                <label for="occupation" class="form-label">Occupation</label>
                                <input type="text" class="form-control" id="occupation" name="occupation" value="<?php echo htmlspecialchars($user['occupation']); ?>" placeholder="e.g., Software Engineer, Doctor">
                            </div>
                            <div class="col-md-6">
                                <label for="annual_income" class="form-label">Annual Income</label>
                                <input type="text" class="form-control" id="annual_income" name="annual_income" value="<?php echo htmlspecialchars($user['annual_income']); ?>" placeholder="e.g., 10-15 Lakhs">
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="mt-5 text-end">
                            <button type="submit" class="btn btn-primary-custom btn-lg">Save Changes</button>
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
