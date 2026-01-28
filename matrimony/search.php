<?php
// We need the header to start the session and connect to the DB
require_once 'includes/header.php';

// If user is not logged in, redirect to login page
if (!isLoggedIn()) {
    redirect('login.php');
}

$user_id = $_SESSION['user_id'];
$search_results = [];
$searched = false;

// --- Handle Form Submission ---
if ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['perform_search'])) {
    $searched = true;

    // --- Build the Dynamic Search Query ---
    $sql = "SELECT * FROM users WHERE 
                id != ? 
                AND profile_status = 'Active'
            ";
    
    $params = ["i", $user_id];

    // --- Add filters from GET request ---
    // Gender
    if (!empty($_GET['gender'])) {
        $sql .= " AND gender = ?";
        $params[0] .= "s";
        $params[] = $_GET['gender'];
    }
    
    // Age Range (calculating DOB)
    if (!empty($_GET['min_age'])) {
        $max_age_date = (new DateTime("today -{$_GET['min_age']} years"))->format('Y-m-d');
        $sql .= " AND dob <= ?";
        $params[0] .= "s";
        $params[] = $max_age_date;
    }
    if (!empty($_GET['max_age'])) {
        $min_age_date = (new DateTime("today -{$_GET['max_age']} years"))->format('Y-m-d');
        $sql .= " AND dob >= ?";
        $params[0] .= "s";
        $params[] = $min_age_date;
    }
    
    // Marital Status
    if (!empty($_GET['marital_status'])) {
        $sql .= " AND marital_status = ?";
        $params[0] .= "s";
        $params[] = $_GET['marital_status'];
    }
    
    // Religion (using LIKE for partial match)
    if (!empty($_GET['religion'])) {
        $sql .= " AND religion LIKE ?";
        $params[0] .= "s";
        $params[] = '%' . $_GET['religion'] . '%';
    }
    
    // Country
    if (!empty($_GET['country'])) {
        $sql .= " AND country LIKE ?";
        $params[0] .= "s";
        $params[] = '%' . $_GET['country'] . '%';
    }
    
    // --- Sorting ---
    $sort_by = isset($_GET['sort_by']) ? $_GET['sort_by'] : 'registration_date';
    $sort_order = ($sort_by == 'dob') ? 'DESC' : 'DESC'; // Newest users or youngest users
    
    $sql .= " ORDER BY $sort_by $sort_order";

    // --- Execute the Query ---
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param(...$params);
        $stmt->execute();
        $result = $stmt->get_result();
        $search_results = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
    }
}
?>

<!-- Main Content Area -->
<div class="search-page-container" style="padding: 3rem 0;">
    <div class="container">
        <div class="row">
            
            <!-- Filter Sidebar -->
            <div class="col-lg-4" data-aos="fade-right">
                <form action="search.php" method="GET" class="filter-sidebar">
                    <input type="hidden" name="perform_search" value="1">
                    <h4><i class="fas fa-filter me-2"></i>Refine Your Search</h4>
                    
                    <div class="mb-3">
                        <label for="gender" class="form-label">I'm looking for a</label>
                        <select id="gender" name="gender" class="form-select">
                            <option value="">Any Gender</option>
                            <option value="Male" <?php echo (isset($_GET['gender']) && $_GET['gender'] == 'Male') ? 'selected' : ''; ?>>Groom (Male)</option>
                            <option value="Female" <?php echo (isset($_GET['gender']) && $_GET['gender'] == 'Female') ? 'selected' : ''; ?>>Bride (Female)</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Age</label>
                        <div class="input-group">
                            <input type="number" class="form-control" name="min_age" value="<?php echo htmlspecialchars($_GET['min_age'] ?? '18'); ?>" placeholder="Min">
                            <span class="input-group-text">to</span>
                            <input type="number" class="form-control" name="max_age" value="<?php echo htmlspecialchars($_GET['max_age'] ?? '40'); ?>" placeholder="Max">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="marital_status" class="form-label">Marital Status</label>
                        <select id="marital_status" name="marital_status" class="form-select">
                            <option value="" <?php echo (isset($_GET['marital_status']) && $_GET['marital_status'] == '') ? 'selected' : ''; ?>>Any</option>
                            <option value="Never Married" <?php echo (isset($_GET['marital_status']) && $_GET['marital_status'] == 'Never Married') ? 'selected' : ''; ?>>Never Married</option>
                            <option value="Divorced" <?php echo (isset($_GET['marital_status']) && $_GET['marital_status'] == 'Divorced') ? 'selected' : ''; ?>>Divorced</option>
                            <option value="Widowed" <?php echo (isset($_GET['marital_status']) && $_GET['marital_status'] == 'Widowed') ? 'selected' : ''; ?>>Widowed</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="religion" class="form-label">Religion</label>
                        <input type="text" id="religion" name="religion" class="form-control" value="<?php echo htmlspecialchars($_GET['religion'] ?? ''); ?>" placeholder="e.g., Hindu, Muslim, Christian">
                    </div>

                    <div class="mb-3">
                        <label for="country" class="form-label">Country</label>
                        <input type="text" id="country" name="country" class="form-control" value="<?php echo htmlspecialchars($_GET['country'] ?? ''); ?>" placeholder="e.g., India, USA">
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary-custom btn-lg">
                            <i class="fas fa-search me-2"></i> Find Matches
                        </button>
                    </div>
                </form>
            </div>

            <!-- Search Results -->
            <div class="col-lg-8" data-aos="fade-left" data-aos-delay="100">
                <div class="results-header">
                    <h3>
                        <?php
                        if ($searched) {
                            echo count($search_results) . ' Matches Found';
                        } else {
                            echo 'Browse Profiles';
                        }
                        ?>
                    </h3>
                    <form action="search.php" method="GET" class="d-flex align-items-center">
                        <!-- Pass existing filters to sorting form -->
                        <input type="hidden" name="perform_search" value="1">
                        <input type="hidden" name="gender" value="<?php echo htmlspecialchars($_GET['gender'] ?? ''); ?>">
                        <input type="hidden" name="min_age" value="<?php echo htmlspecialchars($_GET['min_age'] ?? ''); ?>">
                        <input type="hidden" name="max_age" value="<?php echo htmlspecialchars($_GET['max_age'] ?? ''); ?>">
                        <input type="hidden" name="marital_status" value="<?php echo htmlspecialchars($_GET['marital_status'] ?? ''); ?>">
                        <input type="hidden" name="religion" value="<?php echo htmlspecialchars($_GET['religion'] ?? ''); ?>">
                        <input type="hidden" name="country" value="<?php echo htmlspecialchars($_GET['country'] ?? ''); ?>">

                        <label for="sort_by" class="form-label me-2 mb-0 small">Sort by:</label>
                        <select id="sort_by" name="sort_by" class="form-select form-select-sm" onchange="this.form.submit()">
                            <option value="registration_date" <?php echo (isset($_GET['sort_by']) && $_GET['sort_by'] == 'registration_date') ? 'selected' : ''; ?>>Newest First</option>
                            <option value="dob" <?php echo (isset($_GET['sort_by']) && $_GET['sort_by'] == 'dob') ? 'selected' : ''; ?>>Age (Youngest First)</option>
                        </select>
                    </form>
                </div>
                
                <div class->
                    <?php if (!$searched): ?>
                        <div class="no-results-card">
                            <i class="fas fa-filter"></i>
                            <h4>Start Your Search</h4>
                            <p>Use the filters on the left to find members who match your criteria.</p>
                        </div>
                    <?php elseif (empty($search_results)): ?>
                        <div class="no-results-card">
                            <i class="fas fa-search"></i>
                            <h4>No Matches Found</h4>
                            <p>We couldn't find any members matching your criteria. Try broadening your search.</p>
                        </div>
                    <?php else: ?>
                        <div class="row g-4">
                            <?php foreach ($search_results as $profile): ?>
                                <?php include 'includes/profile_card.php'; // Reuse the profile card template ?>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
        </div>
    </div>
</div>

<?php
// Finally, include the footer
require_once 'includes/footer.php';
?>

