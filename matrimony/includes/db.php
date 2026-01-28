<?php
// includes/db.php

// Start the session on any page that includes this file.
// This ensures that session_start() is called only once.
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// --- Display errors for debugging --- 
// (Comment these two lines out on a live website)
ini_set('display_errors', 1);
error_reporting(E_ALL);

// --- Database Credentials ---
$db_host = 'localhost';
$db_user = 'root'; // IMPORTANT: Change to your database username
$db_pass = '';     // IMPORTANT: Change to your database password
$db_name = 'matrimony_db'; // IMPORTANT: Change to your database name

// --- Establish Connection using mysqli ---
$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

// --- Check Connection ---
// If the connection fails, the script will stop and show an error.
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
