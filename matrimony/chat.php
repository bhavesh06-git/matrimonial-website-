<?php
// --- 1. ALL PHP LOGIC GOES FIRST ---

// We need to initialize the app
require_once 'includes/db.php';
require_once 'includes/functions.php';

// If user is not logged in, redirect to login page
// This check now happens BEFORE any HTML is sent.
if (!isLoggedIn()) {
    redirect('login.php');
}

$user_id = $_SESSION['user_id'];
$chat_partner = null;
$messages = [];
$error_msg = '';

// --- Fetch Logged-in User Data (for sidebar) ---
$user = getLoggedInUser($conn);
if (!$user) {
    redirect('logout.php');
}

// --- Fetch All Matches (for Chat Inbox) ---
$matches_sql = "
    SELECT u.id, u.first_name, u.last_name, u.profile_image
    FROM matches m
    JOIN users u ON u.id = IF(m.user1_id = ?, m.user2_id, m.user1_id)
    WHERE (m.user1_id = ? OR m.user2_id = ?)
    ORDER BY m.matched_at DESC
";
$matches_stmt = $conn->prepare($matches_sql);
$matches_stmt->bind_param("iii", $user_id, $user_id, $user_id);
$matches_stmt->execute();
$matches = $matches_stmt->get_result();
$matches_stmt->close();


// --- Check if a Chat Partner is Selected (from URL) ---
if (isset($_GET['user_id']) && is_numeric($_GET['user_id'])) {
    $chat_partner_id = intval($_GET['user_id']);
    
    // --- Security Check: Verify this is a valid match ---
    if (!checkMatchStatus($conn, $user_id, $chat_partner_id)) {
        $error_msg = "You are not matched with this user.";
    } else {
        // --- Handle Sending a New Message (POST Request) ---
        // This logic also runs BEFORE any HTML is sent.
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['send_message'])) {
            $message_text = sanitize($_POST['message_text']);
            $receiver_id = intval($_POST['receiver_id']);

            if (!empty($message_text) && $receiver_id == $chat_partner_id) {
                $insert_msg_stmt = $conn->prepare("INSERT INTO chat_messages (sender_id, receiver_id, message_text) VALUES (?, ?, ?)");
                $insert_msg_stmt->bind_param("iis", $user_id, $receiver_id, $message_text);
                $insert_msg_stmt->execute();
                $insert_msg_stmt->close();
                
                // Redirect to the same page to show the new message and prevent resubmission
                // This redirect will now work correctly.
                redirect("chat.php?user_id=" . $receiver_id);
            }
        }
        
        // --- Fetch Chat Partner's Info ---
        $partner_stmt = $conn->prepare("SELECT id, first_name, last_name, profile_image FROM users WHERE id = ?");
        $partner_stmt->bind_param("i", $chat_partner_id);
        $partner_stmt->execute();
        $chat_partner = $partner_stmt->get_result()->fetch_assoc();
        $partner_stmt->close();

        // --- Fetch Conversation History ---
        $messages_sql = "
            SELECT * FROM chat_messages 
            WHERE (sender_id = ? AND receiver_id = ?) 
               OR (sender_id = ? AND receiver_id = ?)
            ORDER BY sent_at ASC
        ";
        $messages_stmt = $conn->prepare($messages_sql);
        $messages_stmt->bind_param("iiii", $user_id, $chat_partner_id, $chat_partner_id, $user_id);
        $messages_stmt->execute();
        $messages = $messages_stmt->get_result();
        
        // Mark messages as read
        $update_read_stmt = $conn->prepare("UPDATE chat_messages SET is_read = 1 WHERE sender_id = ? AND receiver_id = ? AND is_read = 0");
        $update_read_stmt->bind_param("ii", $chat_partner_id, $user_id);
        $update_read_stmt->execute();
        $update_read_stmt->close();
    }
}

// Handle Image Path for sidebar
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

// --- 2. NOW WE ARE DONE WITH LOGIC, SO WE INCLUDE THE HTML HEADER ---
// This file will now ONLY print HTML.
require_once 'includes/header.php';

?>

<!-- 3. MAIN HTML CONTENT STARTS HERE -->
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
                            <a class="nav-link" href="edit_preferences.php">
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
                            <a class="nav-link active" href="chat.php">
                                <i class="fas fa-comments"></i> Chat
                            </a>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Main Chat Content -->
            <div class="col-lg-9">
                <div class="chat-container">
                    <!-- Chat Sidebar (Matches List) -->
                    <div class="chat-sidebar">
                        <div class="chat-sidebar-header">
                            <h4>Your Matches</h4>
                        </div>
                        <div class="chat-list">
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
                                    <a href="chat.php?user_id=<?php echo $match['id']; ?>" class="chat-list-item <?php echo ($chat_partner && $chat_partner['id'] == $match['id']) ? 'active' : ''; ?>">
                                        <img src="<?php echo htmlspecialchars($match_image); ?>" alt="<?php echo htmlspecialchars($match['first_name']); ?>">
                                        <div class="chat-info">
                                            <h6><?php echo htmlspecialchars($match['first_name'] . ' ' . $match['last_name']); ?></h6>
                                            <p>Chat with <?php echo htmlspecialchars($match['first_name']); ?></p>
                                        </div>
                                    </a>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <p class="text-center text-muted p-3">You have no matches yet. Go to your 'Interests' page to accept a request.</p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Main Chat Window -->
                    <div class="chat-main">
                        <?php if ($chat_partner): ?>
                            <!-- Chat Header -->
                            <?php
                            // Handle Image Path for chat partner
                            $partner_image = 'uploads/profiles/default.png'; // Default
                            if (!empty($chat_partner['profile_image'])) {
                                if (filter_var($chat_partner['profile_image'], FILTER_VALIDATE_URL)) {
                                    $partner_image = $chat_partner['profile_image'];
                                } else if (file_exists('uploads/profiles/' . $chat_partner['profile_image'])) {
                                    $partner_image = 'uploads/profiles/' . $chat_partner['profile_image'];
                                }
                            }
                            if ($partner_image == 'uploads/profiles/default.png' && !file_exists($partner_image)) {
                                $partner_image = 'https://placehold.co/100x100/F7E7CE/A12C2F?text=Photo';
                            }
                            ?>
                            <div class="chat-header">
                                <img src="<?php echo htmlspecialchars($partner_image); ?>" alt="<?php echo htmlspecialchars($chat_partner['first_name']); ?>">
                                <h5><?php echo htmlspecialchars($chat_partner['first_name'] . ' ' . $chat_partner['last_name']); ?></h5>
                            </div>

                            <!-- Chat Body -->
                            <div class="chat-body" id="chat-body-messages">
                                <?php if ($messages->num_rows > 0): ?>
                                    <?php while ($message = $messages->fetch_assoc()): ?>
                                        <?php if ($message['sender_id'] == $user_id): ?>
                                            <!-- Sent Message -->
                                            <div class="message sent">
                                                <div class="message-bubble">
                                                    <?php echo htmlspecialchars($message['message_text']); ?>
                                                    <div class="message-time"><?php echo (new DateTime($message['sent_at']))->format('h:i A'); ?></div>
                                                </div>
                                            </div>
                                        <?php else: ?>
                                            <!-- Received Message -->
                                            <div class="message received">
                                                <div class="message-bubble">
                                                    <?php echo htmlspecialchars($message['message_text']); ?>
                                                    <div class="message-time"><?php echo (new DateTime($message['sent_at']))->format('h:i A'); ?></div>
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <p class="text-center text-muted">This is the beginning of your conversation. Say hello!</p>
                                <?php endif; ?>
                            </div>

                            <!-- Chat Footer (Send Form) -->
                            <div class="chat-footer">
                                <form action="chat.php?user_id=<?php echo $chat_partner_id; ?>" method="POST">
                                    <input type="hidden" name="receiver_id" value="<?php echo $chat_partner_id; ?>">
                                    <input type="text" class="form-control" name="message_text" placeholder="Type your message..." required autocomplete="off">
                                    <button type="submit" name="send_message" class="btn btn-primary-custom ms-2" style="border-radius: 50px; padding: 0.5rem 1rem;">
                                        <i class="fas fa-paper-plane"></i> Send
                                    </button>
                                </form>
                            </div>

                        <?php else: ?>
                            <!-- Placeholder if no chat is selected -->
                            <div class="chat-placeholder">
                                <?php if ($error_msg): ?>
                                    <i class="fas fa-exclamation-triangle"></i>
                                    <h4>Error</h4>
                                    <p><?php echo $error_msg; ?></p>
                                <?php else: ?>
                                    <i class="fas fa-comments"></i>
                                    <h4>Select a Match</h4>
                                    <p>Please select a match from the list on the left to start chatting.</p>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Auto-scroll chat to bottom -->
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const chatBody = document.getElementById("chat-body-messages");
        if (chatBody) {
            chatBody.scrollTop = chatBody.scrollHeight;
        }
    });
</script>

<?php
// Finally, include the footer
require_once 'includes/footer.php';
?>

