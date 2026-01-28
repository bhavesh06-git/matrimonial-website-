<?php
/*
-- FILE: admin/manage_users.php
-- FIX: Moved all PHP action logic (Update/Redirect) to the top of the file
-- BEFORE any HTML is outputted to prevent "Headers already sent" error.
-- FIX: Corrected image paths to use ../uploads/profiles/
*/

// --- 1. PHP Logic First ---
// Correct path: up one level, then down to includes/
require_once '../includes/db.php'; 
require_once '../includes/functions.php'; 

// Security check: If admin is not logged in, redirect to login page
if (!isset($_SESSION['admin_user_id']) || !isset($_SESSION['admin_username'])) {
    // This redirect is SAFE because no HTML has been sent.
    redirect('index.php?error=Please login to access the admin panel.');
}

// --- 2. Page-Specific Action Logic ---
if (isset($_GET['action']) && isset($_GET['id'])) {
    $action = $_GET['action'];
    $user_id = intval($_GET['id']);
    $new_status = '';

    if ($action == 'approve') {
        $new_status = 'Active';
    } elseif ($action == 'suspend') {
        $new_status = 'Inactive';
    }

    if (!empty($new_status)) {
        $update_stmt = $conn->prepare("UPDATE users SET profile_status = ? WHERE id = ?");
        if ($update_stmt) {
            $update_stmt->bind_param("si", $new_status, $user_id);
            $update_stmt->execute();
            $update_stmt->close();
            // This redirect() call is also safe.
            redirect('manage_users.php?status=updated');
        } else {
            // Handle potential DB error
            echo "Error: " . $conn->error;
            exit;
        }
    }
}

// --- 3. Now, Include HTML Header ---
$page_title = 'Manage Users';
require_once 'includes/admin_header.php';

// --- 4. Page Content Logic ---
// Fetch All Users - FIXED to select profile_image
$sql = "SELECT id, first_name, last_name, email, gender, dob, registration_date, profile_status, profile_image 
        FROM users 
        ORDER BY registration_date DESC";
$all_users_result = $conn->query($sql);

if (!$all_users_result) {
    // Handle SQL query error
    echo "<div class='alert alert-danger'>Error fetching users: " . $conn->error . "</div>";
}
?>

<!-- 5. Page Content -->
<?php if(isset($_GET['status']) && $_GET['status'] == 'updated'): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        User status has been updated successfully.
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<div class="card" style="border: none; box-shadow: var(--shadow);">
    <div class="card-header" style="background-color: var(--white-color);">
        <h5 class="mb-0">All Registered Users (<?php echo $all_users_result ? $all_users_result->num_rows : 0; ?>)</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Photo</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Age</th>
                        <th>Registered</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if ($all_users_result && $all_users_result->num_rows > 0) {
                        while ($user = $all_users_result->fetch_assoc()) {
                            
                            $profile_image = !empty($user['profile_image']) ? $user['profile_image'] : 'default.png';
                            
                            // --- IMAGE PATH FIX ---
                            if (filter_var($profile_image, FILTER_VALIDATE_URL) === FALSE) {
                                // It's a local file. Prepend the correct path from /admin/
                                $image_path = '../uploads/profiles/' . $profile_image;
                            } else {
                                // It's a full URL. Use it directly.
                                $image_path = $profile_image; 
                            }
                            // --- END IMAGE PATH FIX ---
                    ?>
                        <tr>
                            <td>
                                <img src="<?php echo htmlspecialchars($image_path); ?>" alt="<?php echo htmlspecialchars($user['first_name']); ?>" 
                                     class="rounded-circle" width="40" height="40" style="object-fit: cover;"
                                     onerror="this.onerror=null; this.src='https://placehold.co/40x40/F7E7CE/A12C2F?text=Photo';">
                            </td>
                            <td><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></td>
                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                            <td><?php echo calculateAge($user['dob']); ?></td>
                            <td><?php echo date('d M Y', strtotime($user['registration_date'])); ?></td>
                            <td>
                                <?php if ($user['profile_status'] == 'Active'): ?>
                                    <span class="badge bg-success">Active</span>
                                <?php elseif ($user['profile_status'] == 'Inactive'): ?>
                                    <span class="badge bg-danger">Suspended</span>
                                <?php else: ?>
                                    <span class="badge bg-warning text-dark">Pending</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <!-- 
                                    This form is fine, because it submits the page again,
                                    which will trigger the logic at the top of the file.
                                -->
                                <form action="manage_users.php" method="GET" class="d-inline status-change-form" onsubmit="return confirm('Are you sure you want to change this user\'s status?');">
                                    <input type="hidden" name="id" value="<?php echo $user['id']; ?>">
                                    <div class="btn-group">
                                        <!-- FIX: Corrected path to profile page -->
                                        <a href="../profile.php?id=<?php echo $user['id']; ?>" target="_blank" class="btn btn-sm btn-outline-info" title="View Profile">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <?php if ($user['profile_status'] == 'Pending' || $user['profile_status'] == 'Inactive'): ?>
                                            <button type="submit" name="action" value="approve" class="btn btn-sm btn-outline-success" title="Approve">
                                                <i class="fas fa-check"></i>
                                            </button>
                                        <?php endif; ?>
                                        <?php if ($user['profile_status'] == 'Active'): ?>
                                            <button type="submit" name="action" value="suspend" class="btn btn-sm btn-outline-warning" title="Suspend">
                                                <i class="fas fa-ban"></i>
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </form>
                            </td>
                        </tr>
                    <?php
                        }
                    } else {
                        echo '<tr><td colspan="7" class="text-center">No users found.</td></tr>';
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php
// --- 6. Include HTML Footer ---
require_once 'includes/admin_footer.php';
?>