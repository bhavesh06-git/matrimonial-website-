<?php
// Start session and include DB
require_once 'includes/db.php';
require_once 'includes/functions.php';

// Check if user is logged in
if (!isLoggedIn()) {
    // Send a JSON error response
    header('Content-Type: application/json');
    echo json_encode(['status' => 'error', 'message' => 'You must be logged in to send an interest.']);
    exit();
}

// Get the logged-in user's ID
$sender_id = $_SESSION['user_id'];
// Get the ID of the user they are interested in from the POST request
$receiver_id = isset($_POST['receiver_id']) ? intval($_POST['receiver_id']) : 0;

header('Content-Type: application/json');

if ($receiver_id == 0) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid profile ID.']);
    exit();
}

if ($sender_id == $receiver_id) {
    echo json_encode(['status' => 'error', 'message' => 'You cannot send an interest to yourself.']);
    exit();
}

// --- Check 1: Has this user already sent an interest? ---
// FIX: Changed 'id' to 'interest_id'
$check_stmt = $conn->prepare("SELECT interest_id FROM interests WHERE sender_id = ? AND receiver_id = ?");
$check_stmt->bind_param("ii", $sender_id, $receiver_id);
$check_stmt->execute();
$check_result = $check_stmt->get_result();
$check_stmt->close();

if ($check_result->num_rows > 0) {
    echo json_encode(['status' => 'info', 'message' => 'You have already sent an interest to this person.']);
    exit();
}

// --- Check 2: Has the other person already sent an interest to this user? ---
// If so, we should create a "match" automatically!
// FIX: Changed 'id' to 'interest_id' and 'pending' to 'Sent'
$check_reverse_stmt = $conn->prepare("SELECT interest_id FROM interests WHERE sender_id = ? AND receiver_id = ? AND status = 'Sent'");
$check_reverse_stmt->bind_param("ii", $receiver_id, $sender_id);
$check_reverse_stmt->execute();
$check_reverse_result = $check_reverse_stmt->get_result();
$reverse_interest = $check_reverse_result->fetch_assoc();
$check_reverse_stmt->close();

if ($check_reverse_result->num_rows > 0) {
    // --- Create a Match! ---
    $conn->begin_transaction();
    try {
        // 1. Update their interest to 'Accepted'
        // FIX: Changed 'status' to 'Accepted' and 'id' to 'interest_id'
        $update_interest_stmt = $conn->prepare("UPDATE interests SET status = 'Accepted' WHERE interest_id = ?");
        $update_interest_stmt->bind_param("i", $reverse_interest['interest_id']);
        $update_interest_stmt->execute();
        $update_interest_stmt->close();
        
        // 2. Insert the new match
        // FIX: Changed 'user_id_1' to 'user1_id' and 'user_id_2' to 'user2_id'
        // Also, we sort the IDs to prevent duplicate (user1, user2) and (user2, user1)
        $user_1 = min($sender_id, $receiver_id);
        $user_2 = max($sender_id, $receiver_id);
        $insert_match_stmt = $conn->prepare("INSERT INTO matches (user1_id, user2_id) VALUES (?, ?)");
        $insert_match_stmt->bind_param("ii", $user_1, $user_2);
        $insert_match_stmt->execute();
        $insert_match_stmt->close();

        // 3. (Optional but good) Insert our own 'Accepted' interest for consistency
        // FIX: Changed 'status' to 'Accepted'
        $insert_own_interest_stmt = $conn->prepare("INSERT INTO interests (sender_id, receiver_id, status) VALUES (?, ?, 'Accepted')");
        $insert_own_interest_stmt->bind_param("ii", $sender_id, $receiver_id);
        $insert_own_interest_stmt->execute();
        $insert_own_interest_stmt->close();

        $conn->commit();
        echo json_encode(['status' => 'match', 'message' => 'It\'s a Match! This person was also interested in you.']);
        exit();

    } catch (mysqli_sql_exception $exception) {
        $conn->rollback();
        // Check for duplicate key error (match already exists)
        if ($exception->getCode() == 1062) {
             echo json_encode(['status' => 'info', 'message' => 'You are already matched with this person.']);
        } else {
             echo json_encode(['status' => 'error', 'message' => 'A database error occurred while creating a match.']);
        }
        exit();
    }

} else {
    // --- Send a New Interest (Normal flow) ---
    // FIX: Changed 'status' from 'pending' to 'Sent'
    $insert_stmt = $conn->prepare("INSERT INTO interests (sender_id, receiver_id, status) VALUES (?, ?, 'Sent')");
    $insert_stmt->bind_param("ii", $sender_id, $receiver_id);
    
    if ($insert_stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Interest Sent!']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Could not send interest. Please try again.']);
    }
    $insert_stmt->close();
}

$conn->close();
?>

