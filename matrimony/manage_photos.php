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

// --- Handle File Upload (POST Request) ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['profile_image_file'])) {
    
    // Define the upload directory
    $upload_dir = "uploads/profiles/";
    // Create a unique file name to prevent overwriting
    $file_extension = pathinfo($_FILES['profile_image_file']['name'], PATHINFO_EXTENSION);
    $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
    
    // Create a unique file name, e.g., user_1_timestamp.jpg
    $new_file_name = "user_" . $user_id . "_" . time() . "." . $file_extension;
    $target_file = $upload_dir . $new_file_name;
    
    // --- Validation ---
    // 1. Check if file is a real image
    $check = getimagesize($_FILES['profile_image_file']['tmp_name']);
    if ($check === false) {
        $error_msg = "File is not a valid image.";
    }
    // 2. Check file size (e.g., max 5MB)
    elseif ($_FILES['profile_image_file']['size'] > 5000000) {
        $error_msg = "Sorry, your file is too large (Max 5MB).";
    }
    // 3. Check file type
    elseif (!in_array(strtolower($file_extension), $allowed_extensions)) {
        $error_msg = "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
    }
    
    // If no errors, try to upload
    if (empty($error_msg)) {
        // --- (Optional but Recommended) Delete old profile picture ---
        // $old_img_stmt = $conn->prepare("SELECT profile_image FROM users WHERE id = ?");
        // $old_img_stmt->bind_param("i", $user_id);
        // $old_img_stmt->execute();
        // $old_img_result = $old_img_stmt->get_result()->fetch_assoc();
        // $old_img_path = $upload_dir . $old_img_result['profile_image'];
        // if (file_exists($old_img_path) && $old_img_result['profile_image'] != 'default.png') {
        //     unlink($old_img_path);
        // }
        // $old_img_stmt->close();
        
        // Move the uploaded file
        if (move_uploaded_file($_FILES['profile_image_file']['tmp_name'], $target_file)) {
            // File upload success, now update the database
            $sql = "UPDATE users SET profile_image = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            if ($stmt) {
                $stmt->bind_param("si", $new_file_name, $user_id);
                if ($stmt->execute()) {
                    $success_msg = "Your profile picture has been updated!";
                } else {
                    $error_msg = "Database could not be updated.";
                }
                $stmt->close();
            }
        } else {
            $error_msg = "Sorry, there was an error uploading your file.";
        }
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
?>

<!-- Main Content Area -->
<div class="dashboard-container" style="padding: 3rem 0;">
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
                            <a class="nav-link " href="edit_preferences.php">
                                <i class="fas fa-user-cog"></i> Partner Preferences
                            </a>
                        </li>
                        <li class="nav-item " >
                            <a class="nav-link active " href="manage_photos.php">
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
                    <h3 class="mb-4" style="color: var(--primary-color);">Manage Your Profile Photo</h3>
                    
                    <?php if ($success_msg): ?>
                        <div class="alert alert-success"><?php echo $success_msg; ?></div>
                    <?php endif; ?>
                    <?php if ($error_msg): ?>
                        <div class="alert alert-danger"><?php echo $error_msg; ?></div>
                    <?php endif; ?>

                    <div class="row">
                        <div class="col-md-6">
                            <h5>Current Photo</h5>
                            <p class="text-muted">This is the photo other members will see.</p>
                            <img src="<?php echo htmlspecialchars($image_path); ?>" 
                                 onerror="this.onerror=null; this.src='uploads/profiles/default.png';"
                                 alt="Current Profile Picture" class="img-fluid rounded mb-3" 
                                 style="max-height: 400px; box-shadow: var(--shadow);">
                        </div>
                        <div class="col-md-6">
                            <h5>Upload New Photo</h5>
                            <p class="text-muted">For best results, upload a clear, front-facing photo.</p>
                            <form action="manage_photos.php" method="POST" enctype="multipart/form-data">
                                <div class="mb-3">
                                    <label for="profile_image_file" class="form-label">Select image to upload (Max 5MB)</label>
                                    <input class="form-control" type="file" id="profile_image_file" name="profile_image_file" accept="image/png, image/jpeg, image/gif" required>
                                </div>
                                <button type="submit" class="btn btn-primary-custom">Upload Photo</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Finally, include the footer
require_once 'includes/footer.php';
?>
