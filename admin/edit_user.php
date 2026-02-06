<?php
session_start();
include "../config/db.php";

// Only admin access
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit;
}

// Get user ID
if (!isset($_GET['id'])) {
    header("Location: users.php");
    exit;
}

$id = $_GET['id'];

// Fetch user data
$result = $conn->query("SELECT * FROM users WHERE id=$id");
$user = $result->fetch_assoc();

// Update user
if (isset($_POST['update'])) {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $role = $_POST['role'];

    $conn->query("UPDATE users SET name='$name', email='$email', role='$role' WHERE id=$id");
    header("Location: users.php");
    exit;
}
?>

<?php include "includes/admin_header.php"; ?>

<div class="container mt-4">
    <h3>Edit User</h3>

    <form method="POST">
        <div class="mb-3">
            <label>Name</label>
            <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($user['name']); ?>" required>
        </div>
        <div class="mb-3">
            <label>Email</label>
            <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($user['email']); ?>" required>
        </div>
        <div class="mb-3">
            <label>Role</label>
            <select name="role" class="form-control" required>
                <option value="admin" <?= $user['role'] === 'admin' ? 'selected' : ''; ?>>Admin</option>
                <option value="user" <?= $user['role'] === 'user' ? 'selected' : ''; ?>>User</option>
            </select>
        </div>
        <button type="submit" name="update" class="btn btn-success">Update</button>
        <a href="users.php" class="btn btn-secondary">Cancel</a>
    </form>
</div>

<?php include "includes/admin_footer.php"; ?>
