<?php 
session_start();
include "../config/db.php";

$error = "";

if ($_POST) {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Fetch user by email
    $result = $conn->query("SELECT * FROM users WHERE email='$email'");
    $user = $result->fetch_assoc();

    if ($user && password_verify($password, $user['password'])) {
        // Set session variables
        $_SESSION['id'] = $user['id'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['name'] = $user['name'];

        // Redirect based on role
        if ($user['role'] == 'admin') {
            header("Location: ../admin/dashboard.php");
        } else {
            header("Location: ../user/dashboard.php");
        }
        exit;
    } else {
        $error = "Invalid email or password";
    }
}

include "../includes/header.php"; 
?>

<div class="auth-split-wrapper">
    <!-- LEFT: FORM PANE -->
    <div class="auth-form-pane">
        <div class="auth-form-card">
            
            <div class="mb-5">
                <h2 class="fw-bold mb-1">Login please</h2>
                <div style="width: 40px; height: 3px; background: var(--primary);"></div>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-danger small border-0 py-2" style="background: #fef2f2; color: #991b1b;">
                    <i class="fas fa-exclamation-circle me-1"></i> <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <form method="POST">
                <div class="auth-input-group">
                    <i class="far fa-envelope"></i>
                    <input name="email" type="email" class="form-control" placeholder="Input your user ID or Email" required>
                </div>

                <div class="auth-input-group">
                    <i class="fas fa-key"></i>
                    <input name="password" type="password" class="form-control" placeholder="Input your password" required>
                </div>

                <div class="d-flex justify-content-between align-items-center mb-4 small">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="rememberMe">
                        <label class="form-check-label text-muted" for="rememberMe">
                            Remember me
                        </label>
                    </div>
                    <a href="#" class="text-decoration-none text-muted">Forgot Password?</a>
                </div>

                <button class="btn btn-fancy w-auto px-4" style="border-radius: 8px;">
                    <i class="fas fa-sign-in-alt"></i> LOG IN
                </button>
            </form>

            <div class="auth-divider">
                <span>OR</span>
            </div>

            <p class="text-center text-muted small">
                Don't have an account? <a href="register.php" class="text-primary fw-bold text-decoration-none">Sign up for free</a>
            </p>
        </div>
    </div>

    <!-- RIGHT: VISUAL PANE -->
    <div class="auth-visual-pane">
        <div class="auth-visual-content">
            <h1>WELCOME!</h1>
            <p>Enter your details and start journey with us</p>
            <div class="mt-5">
                <a href="register.php" class="btn btn-outline-light px-5 py-2 fw-bold" style="border-radius: 8px; border-width: 2px;">
                    SIGNUP
                </a>
            </div>
        </div>
    </div>
</div>

<?php include "../includes/footer.php"; ?>
