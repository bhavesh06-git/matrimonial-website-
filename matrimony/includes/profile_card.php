<?php
/*
-- FILE: includes/profile_card.php
-- This is the reusable card for displaying a user profile summary.
-- It expects a $profile variable (array) to be available.
*/

if (!isset($profile)) {
    echo "<p>Error: Profile data not found.</p>";
    return;
}

// Calculate age
$age = calculateAge($profile['dob']);
?>
<div class="col-lg-4 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="100">
    <div class="profile-card-revamped">
        <div class="profile-img-container">
            <img src="<?php echo htmlspecialchars($profile['profile_image']); ?>" alt="Profile of <?php echo htmlspecialchars($profile['first_name']); ?>"
                 onerror="this.onerror=null; this.src='https://placehold.co/600x400/F7E7CE/A12C2F?text=Photo';">
        </div>
        <div class="profile-card-content">
            <h5 class="profile-name"><?php echo htmlspecialchars($profile['first_name']) . ' ' . htmlspecialchars($profile['last_name']); ?></h5>
            <p class="profile-meta"><?php echo $age; ?> Yrs, <?php echo htmlspecialchars($profile['height_cm'] ?? 'N/A'); ?> cm</p>
            <div class="profile-details">
                <span><i class="fa-solid fa-briefcase"></i> <?php echo htmlspecialchars($profile['occupation'] ?? 'N/A'); ?></span>
                <span><i class="fa-solid fa-location-dot"></i> <?php echo htmlspecialchars($profile['city'] ?? 'N/A'); ?>, <?php echo htmlspecialchars($profile['country'] ?? 'N/A'); ?></span>
                <span><i class="fa-solid fa-heart"></i> <?php echo htmlspecialchars($profile['religion'] ?? 'N/A'); ?></span>
            </div>
            <a href="profile.php?id=<?php echo $profile['id']; ?>" class="btn btn-primary-custom w-100 mt-3">View Full Profile</a>
        </div>
    </div>
</div>

