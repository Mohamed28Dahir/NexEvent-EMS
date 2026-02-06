<?php
session_start();
include "../config/db.php";

// Only admin access
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit;
}

$username = isset($_SESSION['name']) ? $_SESSION['name'] : 'Admin';

// Delete user
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $conn->query("DELETE FROM users WHERE id=$id");
    header("Location: users.php");
    exit;
}

// Search and filter
$search = $_GET['search'] ?? '';
$filter_role = $_GET['role'] ?? '';

$query = "SELECT * FROM users WHERE 1=1";
$params = [];
$types = "";

// Search by ID, name, or email
if ($search) {
    if (is_numeric($search)) {
        // If search is a number, include ID
        $query .= " AND (id = ? OR name LIKE ? OR email LIKE ?)";
        $params[] = $search;
        $params[] = "%$search%";
        $params[] = "%$search%";
        $types .= "iss";
    } else {
        $query .= " AND (name LIKE ? OR email LIKE ?)";
        $params[] = "%$search%";
        $params[] = "%$search%";
        $types .= "ss";
    }
}

// Filter by role
if ($filter_role && in_array($filter_role, ['admin', 'user'])) {
    $query .= " AND role = ?";
    $params[] = $filter_role;
    $types .= "s";
}

$query .= " ORDER BY id ASC";
$stmt = $conn->prepare($query);

if ($params) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$users = $stmt->get_result();
?>

<?php include "includes/admin_header.php"; ?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3>Manage Users</h3>
        <a href="add_user.php" class="btn btn-success">➕ Create User</a>
    </div>

    <?php if (isset($_GET['success'])) { ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            User created successfully!
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php } ?>

    <!-- SEARCH & FILTER FORM -->
    <form method="GET" class="row g-2 mb-3">
        <div class="col-md-6">
            <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" class="form-control" placeholder="Search by ID, name or email">
        </div>
        <div class="col-md-4">
            <select name="role" class="form-select">
                <option value="">All Roles</option>
                <option value="admin" <?= $filter_role=='admin' ? 'selected' : '' ?>>Admin</option>
                <option value="user" <?= $filter_role=='user' ? 'selected' : '' ?>>User</option>
            </select>
        </div>
        <div class="col-md-2">
            <button class="btn btn-primary w-100">Filter</button>
        </div>
    </form>

    <table class="table table-bordered table-hover">
        <thead class="table-light">
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Email</th>
                <th>Role</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
        <?php while ($u = $users->fetch_assoc()) { ?>
            <tr>
                <td><?= $u['id']; ?></td>
                <td><?= htmlspecialchars($u['name']); ?></td>
                <td><?= htmlspecialchars($u['email']); ?></td>
                <td><?= $u['role']; ?></td>
                <td>
                    <a href="edit_user.php?id=<?= $u['id']; ?>" class="btn btn-primary btn-sm">
                        Edit
                    </a>
                    <a href="?delete=<?= $u['id']; ?>" 
                       onclick="return confirm('Delete user?')" 
                       class="btn btn-danger btn-sm">
                       Delete
                    </a>
                </td>
            </tr>
        <?php } ?>
        </tbody>
    </table>
</div>

<?php include "includes/admin_footer.php"; ?>
