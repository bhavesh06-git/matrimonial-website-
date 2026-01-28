<?php
// We need the header to start the session and connect to the DB
require_once 'includes/header.php';

// If user is not logged in, redirect to login page
if (!isLoggedIn()) {
    redirect('login.php');
}

// Check for profile ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    redirect('dashboard.php');
}

$profile_id = intval($_GET['id']);
$user_id = $_SESSION['user_id'];
$is_own_profile = ($profile_id == $user_id);

// --- Fetch Profile Data ---
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ? AND profile_status = 'Active'");
$stmt->bind_param("i", $profile_id);
$stmt->execute();
$result = $stmt->get_result();
$profile = $result->fetch_assoc();
$stmt->close();

if (!$profile) {
    // If no profile found, redirect
    echo "<div class='container p-5 text-center'><h2>Profile not found or is not active.</h2><a href='search.php' class='btn btn-primary-custom'>Back to Search</a></div>";
    require_once 'includes/footer.php';
    exit();
}

// --- Check Interest & Match Status ---
$interest_status = null;
$match_status = null;

if (!$is_own_profile) {
    // 1. Check if an interest has been sent
    $int_stmt = $conn->prepare("SELECT status FROM interests WHERE sender_id = ? AND receiver_id = ?");
    $int_stmt->bind_param("ii", $user_id, $profile_id);
    $int_stmt->execute();
    $int_res = $int_stmt->get_result();
    if ($int_res->num_rows > 0) {
        $interest_status = $int_res->fetch_assoc()['status']; // 'pending' or 'accepted'
    }
    $int_stmt->close();

    // 2. Check if a match exists
    $user_1 = min($user_id, $profile_id);
    $user_2 = max($user_id, $profile_id);

    // --- THIS IS THE FIX ---
    // Corrected query: 'match_id', 'user1_id', 'user2_id'
    $match_stmt = $conn->prepare("SELECT match_id FROM matches WHERE user1_id = ? AND user2_id = ?");
    // --- END OF FIX ---

    $match_stmt->bind_param("ii", $user_1, $user_2); // This was line 53
    $match_stmt->execute();
    $match_res = $match_stmt->get_result();
    if ($match_res->num_rows > 0) {
        $match_status = 'matched';
    }
    $match_stmt->close();
}

// --- Calculate Age ---
$age = calculateAge($profile['dob']);

// --- Handle Image Path ---
$current_image = $profile['profile_image'];
if (filter_var($current_image, FILTER_VALIDATE_URL)) {
    $image_path = $current_image;
} else {
    // Check if the file exists, if not, use default
    if (file_exists('uploads/profiles/' . $current_image)) {
        $image_path = 'uploads/profiles/' . $current_image;
    } else {
        $image_path = 'uploads/profiles/default.png';
    }
}
// Handle error case for default.png as well
if ($current_image == 'default.png' && !file_exists($image_path)) {
     $image_path = 'https://placehold.co/600x400/F7E7CE/A12C2F?text=Photo';
}

?>

<!-- Main Content Area -->
<div class="profile-page-container page-section">
    <div class="container">
        <div class="row">
            <!-- Sidebar Summary Card -->
            <div class="col-lg-4" data-aos="fade-right">
                <div class="profile-summary-card">
                    <img src="<?php echo htmlspecialchars($image_path); ?>" 
                         onerror="this.onerror=null; this.src='https://placehold.co/400x400/F7E7CE/A12C2F?text=Photo';";
                         alt="Profile of <?php echo htmlspecialchars($profile['first_name']); ?>" 
                         class="profile-image-large">
                    
                    <h3 class="profile-name-large"><?php echo htmlspecialchars($profile['first_name'] . ' ' . $profile['last_name']); ?></h3>
                    
                    <p class="profile-meta-large">
                        <?php echo $age; ?> Years Old • <?php echo htmlspecialchars($profile['occupation'] ?? 'N/A'); ?><br>
                        <i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($profile['city'] . ', ' . $profile['country']); ?>
                    </p>

                    <!-- Action Buttons -->
                    <div class="d-grid gap-2">
                        <?php if ($is_own_profile): ?>
                            <a href="edit_profile.php" class="btn btn-primary-custom"><i class="fas fa-edit me-2"></i> Edit Your Profile</a>
                            <a href="manage_photos.php" class="btn btn-outline-primary-custom"><i class="fas fa-camera me-2"></i> Manage Photos</a>
                        <?php elseif ($match_status == 'matched'): ?>
                            <button class="btn btn-success-custom" disabled><i class="fas fa-check-circle me-2"></i> Matched!</button>
                            <a href="chat.php?user_id=<?php echo $profile_id; ?>" class="btn btn-primary-custom">
                                <i class="fas fa-comments me-2"></i> Start Chat
                            </a>
                        <?php elseif ($interest_status == 'Sent'): ?>
                            <button id="sendInterestBtn" class="btn btn-outline-primary-custom" disabled>
                                <i class="fas fa-clock me-2"></i> Interest Sent
                            </button>
                        <?php elseif ($interest_status == 'Accepted'): ?>
                             <button class="btn btn-success-custom" disabled><i class="fas fa-check-circle me-2"></i> Matched!</button>
                        <?php else: ?>
                            <button id="sendInterestBtn" class="btn btn-primary-custom" onclick="sendInterest(<?php echo $profile_id; ?>)">
                                <i class="fas fa-heart me-2"></i> Send Interest
                            </button>
                        <?php endif; ?>
                    </div>
                    <!-- This div is for showing messages from the button click -->
                    <div id="interestMessage" class="mt-3"></div>

                </div>
            </div>

            <!-- Main Profile Details -->
            <div class="col-lg-8" data-aos="fade-left" data-aos-delay="100">
                <div class="profile-details-main">
                    <!-- Nav Tabs -->
                    <ul class="nav nav-tabs profile-nav-tabs" id="profileTab" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="about-tab" data-bs-toggle="tab" data-bs-target="#about" type="button" role="tab" aria-controls="about" aria-selected="true">
                                <i class="fas fa-user-alt me-1"></i> About
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="details-tab" data-bs-toggle="tab" data-bs-target="#details" type="button" role="tab" aria-controls="details" aria-selected="false">
                                <i class="fas fa-list-ul me-1"></i> Full Details
                            </button>
                        </li>
                        <!-- You can add this tab later -->
                        <!-- <li class="nav-item" role="presentation">
                            <button class="nav-link" id="preferences-tab" data-bs-toggle="tab" data-bs-target="#preferences" type="button" role="tab" aria-controls="preferences" aria-selected="false">
                                <i class="fas fa-user-cog me-1"></i> Partner Preferences
                            </button>
                        </li> -->
                    </ul>

                    <!-- Tab Content -->
                    <div class="tab-content" id="profileTabContent">
                        <!-- About Me Tab -->
                        <div class="tab-pane fade show active" id="about" role="tabpanel" aria-labelledby="about-tab">
                            <div class="profile-section">
                                <h4>About <?php echo htmlspecialchars($profile['first_name']); ?></h4>
                                <p class="text-muted" style="line-height: 1.8;">
                                    <?php echo nl2br(htmlspecialchars($profile['about_me'] ?? 'No information provided.')); ?>
                                </p>
                            </div>
                        </div>

                        <!-- Full Details Tab -->
                        <div class="tab-pane fade" id="details" role="tabpanel" aria-labelledby="details-tab">
                            <!-- Basic Details -->
                            <div class="profile-section">
                                <h4>Basic Information</h4>
                                <div class="info-grid">
                                    <div class="info-item"><div class="info-icon"><i class="fas fa-birthday-cake"></i></div><div><span class="info-label">Age</span><span class="info-value"><?php echo $age; ?> Years</span></div></div>
                                    <div class="info-item"><div class="info-icon"><i class="fas fa-arrows-alt-v"></i></div><div><span class="info-label">Height</span><span class="info-value"><?php echo htmlspecialchars($profile['height_cm'] ?? 'N/A'); ?> cm</span></div></div>
                                    <div class="info-item"><div class="info-icon"><i class="fas fa-ring"></i></div><div><span class="info-label">Marital Status</span><span class="info-value"><?php echo htmlspecialchars($profile['marital_status']); ?></span></div></div>
                                    <div class="info-item"><div class="info-icon"><i class="fas fa-map-marker-alt"></i></div><div><span class="info-label">Location</span><span class="info-value"><?php echo htmlspecialchars($profile['city'] . ', ' . $profile['country']); ?></span></div></div>
                                    <div class="info-item"><div class="info-icon"><i class="fas fa-phone"></i></div><div><span class="info-label">Phone</span><span class="info-value"><?php echo htmlspecialchars($profile['phone_number'] ?? 'N/A'); ?></span></div></div>
                                </div>
                            </div>
                            
                            <!-- Religious Details -->
                            <div class="profile-section">
                                <h4>Religious & Social Background</h4>
                                <div class="info-grid">
                                    <div class="info-item"><div class="info-icon"><i class="fas fa-pray"></i></div><div><span class="info-label">Religion</span><span class="info-value"><?php echo htmlspecialchars($profile['religion'] ?? 'N/A'); ?></span></div></div>
                                    <div class="info-item"><div class="info-icon"><i class="fas fa-users"></i></div><div><span class="info-label">Caste / Community</span><span class="info-value"><?php echo htmlspecialchars($profile['caste'] ?? 'N/A'); ?></span></div></div>
                                </div>
                            </div>

                            <!-- Professional Details -->
                            <div class="profile-section">
                                <h4>Education & Career</h4>
                                <div class="info-grid">
                                    <div class="info-item"><div class="info-icon"><i class="fas fa-graduation-cap"></i></div><div><span class="info-label">Education</span><span class="info-value"><?php echo htmlspecialchars($profile['education'] ?? 'N/A'); ?></span></div></div>
                                    <div class="info-item"><div class="info-icon"><i class="fas fa-briefcase"></i></div><div><span class="info-label">Occupation</span><span class="info-value"><?php echo htmlspecialchars($profile['occupation'] ?? 'N/A'); ?></span></div></div>
                                    <div class="info-item"><div class="info-icon"><i class="fas fa-money-bill-wave"></i></div><div><span class="info-label">Annual Income</span><span class="info-value"><?php echo htmlspecialchars($profile['annual_income'] ?? 'N/A'); ?></span></div></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- JavaScript for Send Interest Button -->
<script>
function sendInterest(receiverId) {
    const button = document.getElementById('sendInterestBtn');
    const messageDiv = document.getElementById('interestMessage');

    // Disable button to prevent multiple clicks
    button.disabled = true;
    button.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> Sending...';

    // Prepare data to send
    const formData = new FormData();
    formData.append('receiver_id', receiverId);

    // Send POST request to the backend script
    fetch('process_interest.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            // Success
            button.innerHTML = '<i class="fas fa-clock me-2"></i> Interest Sent';
            messageDiv.innerHTML = '<div class="alert alert-success mt-3 small">Interest Sent!</div>';
        } else if (data.status === 'match') {
            // It's a match!
            button.innerHTML = '<i class="fas fa-check-circle me-2"></i> Matched!';
            button.classList.remove('btn-primary-custom');
            button.classList.add('btn-success-custom');
            messageDiv.innerHTML = '<div class="alert alert-success mt-3 small"><b>It\'s a Match!</b> This person was also interested in you. You can now start a chat.</div>';
            
            // Create and append the chat button
            const chatButton = document.createElement('a');
            chatButton.href = 'chat.php?user_id=' + receiverId;
            chatButton.className = 'btn btn-primary-custom d-block mt-2';
            chatButton.innerHTML = '<i class="fas fa-comments me-2"></i> Start Chat';
            button.after(chatButton);

        } else if (data.status === 'info') {
             // Info (like already sent)
            button.innerHTML = '<i class="fas fa-clock me-2"></i> Interest Sent';
            messageDiv.innerHTML = `<div class="alert alert-info mt-3 small">${data.message}</div>`;
        
        } else {
            // Error
            button.disabled = false;
            button.innerHTML = '<i class="fas fa-heart me-2"></i> Send Interest';
            messageDiv.innerHTML = `<div class="alert alert-danger mt-3 small">${data.message}</div>`;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        button.disabled = false;
        button.innerHTML = '<i class="fas fa-heart me-2"></i> Send Interest';
        messageDiv.innerHTML = '<div class="alert alert-danger mt-3 small">A network error occurred. Please try again.</div>';
    });
}
</script>

<?php
// Finally, include the footer
require_once 'includes/footer.php';
?>

