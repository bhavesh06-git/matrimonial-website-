<?php
/*
-- FILE: includes/functions.php
-- This file contains reusable functions for the whole site.
*/

// Note: db.php is NOT included here. It should be included by the file
// that also includes this one (like header.php).

/**
 * Checks if a user is currently logged in.
 * @return bool True if logged in, false otherwise.
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

/**
 * Redirects to a specified URL and stops script execution.
 * @param string $url The URL to redirect to.
 */
function redirect($url) {
    header("Location: $url");
    exit();
}

/**
 * Sanitizes user input to prevent XSS.
 * @param string $data The input data.
 * @return string The sanitized data.
 */
function sanitize($data) {
    return htmlspecialchars(stripslashes(trim($data)));
}

/**
 * Calculates age from a date of birth.
 * @param string $dob The date of birth in 'YYYY-MM-DD' format.
 * @return int The age in years.
 */
function calculateAge($dob) {
    if(empty($dob)) return 0;
    $birthDate = new DateTime($dob);
    $today = new DateTime('today');
    if ($birthDate > $today) {
        return 0;
    }
    return $birthDate->diff($today)->y;
}

/**
 * Fetches the current user's complete data from the database.
 * @param mysqli $conn The database connection object.
 * @param int $user_id The ID of the user to fetch.
 * @return array|null The user's data as an associative array, or null if not found.
 */
function getLoggedInUser($conn) {
    if (!isLoggedIn()) {
        return null;
    }
    
    $user_id = $_SESSION['user_id'];
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();
    
    return $user;
}

/**
 * Checks the interest status between two users.
 * @param mysqli $conn The database connection object.
 * @param int $sender_id The ID of the logged-in user.
 * @param int $receiver_id The ID of the profile user.
 * @return string|null "Sent", "Accepted", "Declined", or null if no interest.
 */
function checkInterestStatus($conn, $sender_id, $receiver_id) {
    $stmt = $conn->prepare("SELECT status FROM interests WHERE (sender_id = ? AND receiver_id = ?) OR (sender_id = ? AND receiver_id = ?)");
    $stmt->bind_param("iiii", $sender_id, $receiver_id, $receiver_id, $sender_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        return $row['status'];
    }
    $stmt->close();
    return null;
}

/**
 * Checks if two users are matched.
 * @param mysqli $conn The database connection object.
 * @param int $user1_id The ID of the first user.
 * @param int $user2_id The ID of the second user.
 * @return bool True if a match exists, false otherwise.
 */
function checkMatchStatus($conn, $user1_id, $user2_id) {
    $stmt = $conn->prepare("SELECT match_id FROM matches WHERE (user1_id = ? AND user2_id = ?) OR (user1_id = ? AND user2_id = ?)");
    $stmt->bind_param("iiii", $user1_id, $user2_id, $user2_id, $user1_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $is_matched = $result->num_rows > 0;
    $stmt->close();
    return $is_matched;
}
?>

