<?php
/*
-- FILE: admin/logout.php
-- Destroys the admin session and redirects to the login page.
*/

// --- Must include DB file first to start the session ---
require_once '../includes/db.php'; 

// Unset all admin session variables
unset($_SESSION['admin_user_id']);
unset($_SESSION['admin_username']);

// You can also use session_destroy() if you want to kill the whole session,
// but unset is safer if the user is also logged into the main site.
// session_destroy(); 

// Redirect to the admin login page
header('Location: index.php?status=loggedout');
exit();
?>