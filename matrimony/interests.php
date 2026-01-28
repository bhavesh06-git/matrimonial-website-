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

// --- Handle Accept/Decline Actions ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    
    $action = $_POST['action'];
    $sender_id = intval($_POST['sender_id']);
    $interest_id = intval($_POST['interest_id']);

    // Security check: ensure the current user is the receiver of this interest
    $check_stmt = $conn->prepare("SELECT * FROM interests WHERE interest_id = ? AND receiver_id = ?");
    $check_stmt->bind_param("ii", $interest_id, $user_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    $check_stmt->close();

    if ($check_result->num_rows == 0) {
        $error_msg = "Invalid action. You do not have permission to modify this interest.";
    } else {
        if ($action == 'accept') {
            // --- Create a Match! ---
            $conn->begin_transaction();
            try {
                // 1. Update the interest status to 'Accepted'
                $update_stmt = $conn->prepare("UPDATE interests SET status = 'Accepted' WHERE interest_id = ?");
                $update_stmt->bind_param("i", $interest_id);
                $update_stmt->execute();
                $update_stmt->close();

                // 2. Insert the new match record
                $user_1 = min($user_id, $sender_id);
                $user_2 = max($user_id, $sender_id);
                
                $insert_match_stmt = $conn->prepare("INSERT INTO matches (user1_id, user2_id) VALUES (?, ?)");
                $insert_match_stmt->bind_param("ii", $user_1, $user_2);
                $insert_match_stmt->execute();
                $insert_match_stmt->close();

                $conn->commit();
                $success_msg = "You have accepted the interest. You are now a match!";

            } catch (mysqli_sql_exception $exception) {
                $conn->rollback();
                if ($exception->getCode() == 1062) { // Duplicate key
                    $error_msg = "You are already matched with this user.";
                } else {
                    $error_msg = "A database error occurred: " . $exception->getMessage();
                }
            }

        } elseif ($action == 'decline') {
            // --- Decline the Interest ---
            $decline_stmt = $conn->prepare("UPDATE interests SET status = 'Declined' WHERE interest_id = ?");
            $decline_stmt->bind_param("i", $interest_id);
            $decline_stmt->execute();
            $decline_stmt->close();
            $success_msg = "You have declined the interest.";
        }
    }
}


// --- Fetch Logged-in User Data (for sidebar) ---
$user = getLoggedInUser($conn);
if (!$user) {
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

// --- SQL QUERY FIXES ---

// --- Fetch Interests Received (Pending) ---
// This query joins interests with the users table to get the sender's info
// FIX: Simplified query to only select columns that are guaranteed to exist.
$received_sql = "
    SELECT i.interest_id, i.sender_id, i.status, u.first_name, u.last_name, u.profile_image
    FROM interests i
    JOIN users u ON i.sender_id = u.id
    WHERE i.receiver_id = ? AND i.status = 'Sent'
    ORDER BY i.sent_at DESC
";
$received_stmt = $conn->prepare($received_sql); // This was the line failing
$received_stmt->bind_param("i", $user_id);
$received_stmt->execute();
$received_interests = $received_stmt->get_result();
$received_stmt->close();

// --- Fetch Interests Sent ---
// FIX: Simplified query
$sent_sql = "
    SELECT i.status, u.id, u.first_name, u.last_name, u.profile_image
    FROM interests i
    JOIN users u ON i.receiver_id = u.id
    WHERE i.sender_id = ?
    ORDER BY i.sent_at DESC
";
$sent_stmt = $conn->prepare($sent_sql);
$sent_stmt->bind_param("i", $user_id);
$sent_stmt->execute();
$sent_interests = $sent_stmt->get_result();
$sent_stmt->close();

// --- Fetch Accepted Matches (for chat) ---
// FIX: Simplified query
$matches_sql = "
    SELECT u.id, u.first_name, u.last_name, u.profile_image
    FROM matches m
    JOIN users u ON u.id = IF(m.user1_id = ?, m.user2_id, m.user1_id)
    WHERE (m.user1_id = ? OR m.user2_id = ?)
";
$matches_stmt = $conn->prepare($matches_sql);
$matches_stmt->bind_param("iii", $user_id, $user_id, $user_id);
$matches_stmt->execute();
$matches = $matches_stmt->get_result();
$matches_stmt->close();

// --- END OF SQL QUERY FIXES ---

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
                            <a class="nav-link" href="dashboard.php">
                                <i class="fas fa-tachometer-alt"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item ">
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
                        <li class="nav-item active">
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
                <div class="profile-details-main">
                    
                    <?php if ($success_msg): ?>
                        <div class="alert alert-success m-4"><?php echo $success_msg; ?></div>
                    <?php endif; ?>
                    <?php if ($error_msg): ?>
                        <div class="alert alert-danger m-4"><?php echo $error_msg; ?></div>
                    <?php endif; ?>

                    <!-- Nav Tabs -->
                    <ul class="nav nav-tabs profile-nav-tabs" id="interestsTab" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="received-tab" data-bs-toggle="tab" data-bs-target="#received" type="button" role="tab" aria-controls="received" aria-selected="true">
                                <i class="fas fa-inbox me-1"></i> Interests Received
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="sent-tab" data-bs-toggle="tab" data-bs-target="#sent" type="button" role="tab" aria-controls="sent" aria-selected="false">
                                <i class="fas fa-paper-plane me-1"></i> Interests Sent
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="matches-tab" data-bs-toggle="tab" data-bs-target="#matches" type="button" role="tab" aria-controls="matches" aria-selected="false">
                                <i class="fas fa-check-double me-1"></i> Your Matches
                            </button>
                        </li>
                    </ul>

                    <!-- Tab Content -->
                    <div class="tab-content" id="interestsTabContent">
                        
                        <!-- Interests Received Tab -->
                        <div class="tab-pane fade show active" id="received" role="tabpanel" aria-labelledby="received-tab">
                            <div class="profile-section">
                                <h4>New Interests Waiting for Your Response</h4>
                                <?php if ($received_interests->num_rows > 0): ?>
                                    <?php while ($interest = $received_interests->fetch_assoc()): ?>
                                        <?php
                                        // Handle Image Path for sender
                                        $sender_image = 'uploads/profiles/default.png'; // Default
                                        if (!empty($interest['profile_image'])) {
                                            if (filter_var($interest['profile_image'], FILTER_VALIDATE_URL)) {
                                                $sender_image = $interest['profile_image'];
                                            } else if (file_exists('uploads/profiles/' . $interest['profile_image'])) {
                                                $sender_image = 'uploads/profiles/' . $interest['profile_image'];
                                            }
                                        }
                                        if ($sender_image == 'uploads/profiles/default.png' && !file_exists($sender_image)) {
                                            $sender_image = 'https://placehold.co/100x100/F7E7CE/A12C2F?text=Photo';
                                        }
                                        ?>
                                        <div class="interest-card">
                                            <img src="<?php echo htmlspecialchars($sender_image); ?>" alt="Profile">
                                            <div class="interest-info">
                                                <a href="profile.php?id=<?php echo $interest['sender_id']; ?>" class="text-decoration-none">
                                                    <h5><?php echo htmlspecialchars($interest['first_name'] . ' ' . $interest['last_name']); ?></h5>
                                                </a>
                                                <!-- FIX: Removed occupation, city, country as they might not exist -->
                                                <p><?php echo htmlspecialchars($interest['first_name']); ?> has sent you an interest.</p>
                                            </div>
                                            <div class="interest-actions">
                                                <form action="interests.php" method="POST" class="d-inline">
                                                    <input type="hidden" name="interest_id" value="<?php echo $interest['interest_id']; ?>">
                                                    <input type="hidden" name="sender_id" value="<?php echo $interest['sender_id']; ?>">
                                                    <button type="submit" name="action" value="accept" class="btn btn-success btn-sm">
                                                        <i class="fas fa-check"></i> Accept
                                                    </button>
                                                    <button type="submit" name="action" value="decline" class="btn btn-danger btn-sm">
                                                        <i class="fas fa-times"></i> Decline
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <p class="text-muted p-3">You have no new pending interests.</p>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Interests Sent Tab -->
                        <div class="tab-pane fade" id="sent" role="tabpanel" aria-labelledby="sent-tab">
                             <div class="profile-section">
                                <h4>Interests You Have Sent</h4>
                                <?php if ($sent_interests->num_rows > 0): ?>
                                    <?php while ($interest = $sent_interests->fetch_assoc()): ?>
                                        <?php
                                        // Handle Image Path for receiver
                                        $receiver_image = 'uploads/profiles/default.png'; // Default
                                        if (!empty($interest['profile_image'])) {
                                            if (filter_var($interest['profile_image'], FILTER_VALIDATE_URL)) {
                                                $receiver_image = $interest['profile_image'];
                                            } else if (file_exists('uploads/profiles/' . $interest['profile_image'])) {
                                                $receiver_image = 'uploads/profiles/' . $interest['profile_image'];
                                            }
                                        }
                                        if ($receiver_image == 'uploads/profiles/default.png' && !file_exists($receiver_image)) {
                                            $receiver_image = 'https://placehold.co/100x100/F7E7CE/A12C2F?text=Photo';
                                        }
                                        ?>
                                        <div class="interest-card">
                                            <img src="<?php echo htmlspecialchars($receiver_image); ?>" alt="Profile">
                                            <div class="interest-info">
                                                <a href="profile.php?id=<?php echo $interest['id']; ?>" class="text-decoration-none">
                                                    <h5><?php echo htmlspecialchars($interest['first_name'] . ' ' . $interest['last_name']); ?></h5>
                                                </a>
                                                <!-- FIX: Removed occupation, city, country -->
                                                <p>You sent an interest.</p>
                                            </div>
                                            <div class="interest-actions">
                                                <?php
                                                    $status_class = 'text-muted';
                                                    $status_icon = 'fa-clock';
                                                    if ($interest['status'] == 'Accepted') {
                                                        $status_class = 'text-success';
                                                        $status_icon = 'fa-check-circle';
                                                    } elseif ($interest['status'] == 'Declined') {
                                                        $status_class = 'text-danger';
                                                        $status_icon = 'fa-times-circle';
                                                    }
                                                ?>
                                                <span class="interest-status <?php echo $status_class; ?>">
                                                    <i class="fas <?php echo $status_icon; ?> me-2"></i><?php echo $interest['status']; ?>
                                                </span>
                                            </div>
                                        </div>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <p class="text-muted p-3">You have not sent any interests yet.</p>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Your Matches Tab -->
                        <div class="tab-pane fade" id="matches" role="tabpanel" aria-labelledby="matches-tab">
                             <div class="profile-section">
                                <h4>Your Matches</h4>
                                <p class="text-muted">These are the people you have mutually accepted interests with. You can now start a chat!</p>
                                <?php if ($matches->num_rows > 0): ?>
                                    <?php while ($match = $matches->fetch_assoc()): ?>
                                        <?php
                                        // Handle Image Path for match
                                        $match_image = 'uploads/profiles/default.png'; // Default
                                        if (!empty($match['profile_image'])) {
                                            if (filter_var($match['profile_image'], FILTER_VALIDATE_URL)) {
                                                $match_image = $match['profile_image'];
                                            } else if (file_exists('uploads/profiles/' . $match['profile_image'])) {
                                                $match_image = 'uploads/profiles/' . $match['profile_image'];
                                            }
                                        }
                                        if ($match_image == 'uploads/profiles/default.png' && !file_exists($match_image)) {
                                            $match_image = 'https://placehold.co/100x100/F7E7CE/A12C2F?text=Photo';
                                        }
                                        ?>
                                        <div class="interest-card">
                                            <img src="<?php echo htmlspecialchars($match_image); ?>" alt="Profile">
                                            <div class="interest-info">
                                                <a href="profile.php?id=<?php echo $match['id']; ?>" class="text-decoration-none">
                                                    <h5><?php echo htmlspecialchars($match['first_name'] . ' ' . $match['last_name']); ?></h5>
                                                </a>
                                                <!-- FIX: Removed occupation -->
                                                <p>You are matched. Start a conversation!</p>
                                            </div>
                                            <div class="interest-actions">
                                                <a href="chat.php?user_id=<?php echo $match['id']; ?>" class="btn btn-primary-custom btn-sm">
                                                    <i class="fas fa-comments"></i> Start Chat
                                                </a>
                                            </div>
                                        </div>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <p class="text-muted p-3">You have no matches yet. Accept an interest to create a match.</p>
                                <?php endif; ?>
                            </div>
                        </div>

                    </div> <!-- .tab-content -->
                </div> <!-- .profile-details-main -->
            </div> <!-- .col-lg-9 -->
        </div> <!-- .row -->
    </div> <!-- .container -->
</div> <!-- .page-section -->

<?php
// Finally, include the footer
require_once 'includes/footer.php';
?>

