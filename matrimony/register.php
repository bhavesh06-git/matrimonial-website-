<?php
include 'includes/db.php';
require_once 'includes/functions.php';

if (isLoggedIn()) {
    redirect('dashboard.php');
}

$errors = [];
$success_msg = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Sanitize all inputs
    $first_name = sanitize($_POST['first_name']);
    $last_name = sanitize($_POST['last_name']);
    $email = filter_var(sanitize($_POST['email']), FILTER_VALIDATE_EMAIL);
    $password = $_POST['password'];
    $gender = sanitize($_POST['gender']);
    $dob = sanitize($_POST['dob']);
    $marital_status = sanitize($_POST['marital_status']);
    
    // Basic Validation
    if (empty($first_name) || empty($last_name) || empty($email) || empty($password) || empty($gender) || empty($dob) || empty($marital_status)) {
        $errors[] = "All fields with * are required.";
    }
    if (!$email) {
        $errors[] = "Invalid email format.";
    }
    if (strlen($password) < 8) {
        $errors[] = "Password must be at least 8 characters long.";
    }

    // Check if email already exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
        $errors[] = "An account with this email already exists.";
    }
    $stmt->close();

    // If no errors, proceed to insert
    if (empty($errors)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        $stmt = $conn->prepare("INSERT INTO users (first_name, last_name, email, password, gender, dob, marital_status) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssss", $first_name, $last_name, $email, $hashed_password, $gender, $dob, $marital_status);
        
        if ($stmt->execute()) {
            $_SESSION['success_message'] = "Registration successful! You can now log in.";
            redirect('login.php');
        } else {
            $errors[] = "Registration failed. Please try again.";
        }
        $stmt->close();
    }
}

require_once 'includes/header.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-7" data-aos="fade-up">
            <div class="form-container">
                <h2 class="text-center mb-4">Create Your Profile</h2>
                <p class="text-center text-muted mb-4">Join us today and start your search for a life partner.</p>
                
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger">
                        <?php foreach ($errors as $error): ?>
                            <p class="mb-0"><?php echo $error; ?></p>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <form action="register.php" method="POST" class="needs-validation" novalidate>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="first_name" class="form-label">First Name *</label>
                            <input type="text" class="form-control" id="first_name" name="first_name" required>
                        </div>
                        <div class="col-md-6">
                            <label for="last_name" class="form-label">Last Name *</label>
                            <input type="text" class="form-control" id="last_name" name="last_name" required>
                        </div>
                        <div class="col-12">
                            <label for="email" class="form-label">Email Address *</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                        <div class="col-12">
                            <label for="password" class="form-label">Password *</label>
                            <input type="password" class="form-control" id="password" name="password" minlength="8" required>
                            <div class="form-text">Password must be at least 8 characters long.</div>
                        </div>
                        <div class="col-md-6">
                            <label for="gender" class="form-label">Gender *</label>
                            <select class="form-select" id="gender" name="gender" required>
                                <option value="">Choose...</option>
                                <option value="Male">Male</option>
                                <option value="Female">Female</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="dob" class="form-label">Date of Birth *</label>
                            <input type="date" class="form-control" id="dob" name="dob" required>
                        </div>
                        <div class="col-12">
                            <label for="marital_status" class="form-label">Marital Status *</label>
                             <select class="form-select" id="marital_status" name="marital_status" required>
                                <option value="">Choose...</option>
                                <option value="Never Married">Never Married</option>
                                <option value="Divorced">Divorced</option>
                                <option value="Widowed">Widowed</option>
                            </select>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary-custom w-100 mt-4">Register</button>
                    <p class="text-center mt-3">Already have an account? <a href="login.php">Login here</a></p>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>