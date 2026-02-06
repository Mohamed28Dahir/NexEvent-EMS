<?php
session_start();
include "../config/db.php";

// Only user access
if (!isset($_SESSION['id']) || $_SESSION['role'] !== 'user') {
    header("Location: ../index.php");
    exit;
}

$user_id = $_SESSION['id'];
$success = "";
$error = "";

// FETCH USER DATA
$stmt = $conn->prepare("SELECT name, email FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

// HANDLE PASSWORD CHANGE
if (isset($_POST['change_password'])) {
    $current_pass = $_POST['current_password'];
    $new_pass = $_POST['new_password'];
    $confirm_pass = $_POST['confirm_password'];

    // Verify current password
    $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $res = $stmt->get_result()->fetch_assoc();

    if (password_verify($current_pass, $res['password'])) {
        if ($new_pass === $confirm_pass) {
            $hashed_pass = password_hash($new_pass, PASSWORD_DEFAULT);
            $upd = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
            $upd->bind_param("si", $hashed_pass, $user_id);
            if ($upd->execute()) {
                $success = "Password changed successfully!";
            } else {
                $error = "Update failed. Try again.";
            }
        } else {
            $error = "New passwords do not match!";
        }
    } else {
        $error = "Current password is incorrect!";
    }
}

include "includes/user_header.php";
?>

<div class="row justify-content-center">
    <div class="col-md-8 col-lg-6">
        
        <!-- SECTION: PROFILE INFO -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body p-4 text-center">
                <div class="mb-3">
                    <i class="fas fa-user-circle fa-5x text-primary opacity-25"></i>
                </div>
                <h3 class="fw-bold mb-1"><?= htmlspecialchars($user['name']) ?></h3>
                <p class="text-muted mb-0"><?= htmlspecialchars($user['email']) ?></p>
                <span class="badge bg-indigo-subtle text-indigo mt-2 px-3 py-2 border border-indigo-subtle" style="background: #eef2ff; color: #4338ca;">Standard User Account</span>
            </div>
        </div>

        <!-- SECTION: SECURITY / PASSWORD -->
        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <div class="section-header">
                    <i class="fas fa-shield-alt"></i>
                    <h3>Account Security</h3>
                    <div class="section-title-line"></div>
                </div>

                <?php if ($success): ?>
                    <div class="alert alert-success mt-3"><?= $success ?></div>
                <?php endif; ?>

                <?php if ($error): ?>
                    <div class="alert alert-danger mt-3"><?= $error ?></div>
                <?php endif; ?>

                <form method="POST" class="mt-4">
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted">Current Password</label>
                        <input type="password" name="current_password" class="form-control" required>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6 mb-3">
                            <label class="form-label small fw-bold text-muted">New Password</label>
                            <input type="password" name="new_password" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label small fw-bold text-muted">Confirm New Password</label>
                            <input type="password" name="confirm_password" class="form-control" required>
                        </div>
                    </div>
                    <button type="submit" name="change_password" class="btn btn-fancy w-100" style="background: linear-gradient(135deg, #4f46e5, #4338ca);">
                        <i class="fas fa-key"></i> Update Password
                    </button>
                </form>
            </div>
        </div>

    </div>
</div>

<?php include "includes/user_footer.php"; ?>
