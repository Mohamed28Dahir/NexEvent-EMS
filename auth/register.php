<?php
session_start();
include "../config/db.php";

if ($_POST) {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $conn->query("INSERT INTO users(name,email,password) VALUES('$name','$email','$password')");
    header("Location: login.php");
    exit;
}

include "../includes/header.php";
?>

<div class="auth-split-wrapper">
    <!-- LEFT: FORM PANE -->
    <div class="auth-form-pane">
        <div class="auth-form-card">
            
            <div class="mb-5">
                <h2 class="fw-bold mb-1">Create Account</h2>
                <div style="width: 40px; height: 3px; background: var(--primary);"></div>
            </div>

            <form method="POST">
                <div class="auth-input-group">
                    <i class="far fa-user"></i>
                    <input name="name" type="text" class="form-control" placeholder="Full Name" required>
                </div>

                <div class="auth-input-group">
                    <i class="far fa-envelope"></i>
                    <input name="email" type="email" class="form-control" placeholder="Email Address" required>
                </div>

                <div class="auth-input-group">
                    <i class="fas fa-key"></i>
                    <input name="password" type="password" class="form-control" placeholder="Password" required>
                </div>

                <div class="mb-4">
                    <div class="form-check small">
                        <input class="form-check-input" type="checkbox" id="terms" required>
                        <label class="form-check-label text-muted" for="terms">
                            I agree to the <a href="#" class="text-primary text-decoration-none">Terms & Conditions</a>
                        </label>
                    </div>
                </div>

                <button class="btn btn-fancy w-100 mb-4" style="border-radius: 8px;">
                    <i class="fas fa-user-plus"></i> SIGN UP
                </button>
            </form>

            <div class="auth-divider">
                <span>OR</span>
            </div>

            <p class="text-center text-muted small">
                Already have an account? <a href="login.php" class="text-primary fw-bold text-decoration-none">Log in here</a>
            </p>
        </div>
    </div>

    <!-- RIGHT: VISUAL PANE -->
    <div class="auth-visual-pane">
        <div class="auth-visual-content">
            <h1>JOIN US!</h1>
            <p>Create your account and start managing events like a pro</p>
            <div class="mt-5">
                <a href="login.php" class="btn btn-outline-light px-5 py-2 fw-bold" style="border-radius: 8px; border-width: 2px;">
                    LOGIN
                </a>
            </div>
        </div>
    </div>
</div>

<?php include "../includes/footer.php"; ?>
