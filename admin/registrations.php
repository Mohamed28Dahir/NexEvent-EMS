<?php
session_start();
include "../config/db.php";

// Admin only
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit;
}

$message = "";

// UPDATE REGISTRATION STATUS
if (isset($_POST['update_status'])) {
    $reg_id = $_POST['reg_id'];
    $new_status = $_POST['status'];

    if (in_array($new_status, ['approved', 'rejected'])) {
        $stmt = $conn->prepare("UPDATE registrations SET status = ? WHERE id = ?");
        $stmt->bind_param("si", $new_status, $reg_id);
        if ($stmt->execute()) {
            $message = "Registration " . ucfirst($new_status) . " successfully!";
        }
    }
}

// SEARCH & FILTERS
$search = $_GET['search'] ?? '';
$filter_status = $_GET['status'] ?? '';
$filter_event = $_GET['event_id'] ?? '';

$query = "SELECT r.*, u.name as user_name, u.email as user_email, e.title as event_title 
          FROM registrations r 
          JOIN users u ON r.user_id = u.id 
          JOIN events e ON r.event_id = e.id 
          WHERE 1=1";
$params = [];
$types = "";

if ($search) {
    $query .= " AND (u.name LIKE ? OR e.title LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $types .= "ss";
}

if ($filter_status) {
    $query .= " AND r.status = ?";
    $params[] = $filter_status;
    $types .= "s";
}

if ($filter_event) {
    $query .= " AND r.event_id = ?";
    $params[] = $filter_event;
    $types .= "i";
}

$query .= " ORDER BY r.registered_at DESC";
$stmt = $conn->prepare($query);
if ($params) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$registrations = $stmt->get_result();

// Fetch events for filter dropdown
$events_list = $conn->query("SELECT id, title FROM events ORDER BY title ASC");

include "includes/admin_header.php";
?>

<div class="container-fluid py-4">
    <!-- SECTION: REGISTRATION DIRECTORY -->
    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
            <div class="section-header">
                <i class="fas fa-file-signature"></i>
                <h3>Registration Management</h3>
                <div class="section-title-line"></div>
            </div>

            <?php if ($message): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?= $message ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <!-- SEARCH & FILTER BAR -->
            <form method="GET" class="row g-3 mb-4 align-items-end">
                <div class="col-md-4">
                    <label class="form-label small fw-bold">Search User or Event</label>
                    <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" class="form-control" placeholder="Name or Title...">
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-bold">Event Filter</label>
                    <select name="event_id" class="form-select">
                        <option value="">All Events</option>
                        <?php while ($ev = $events_list->fetch_assoc()): ?>
                            <option value="<?= $ev['id'] ?>" <?= $filter_event == $ev['id'] ? 'selected' : '' ?>><?= htmlspecialchars($ev['title']) ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-bold">Status</label>
                    <select name="status" class="form-select">
                        <option value="">All</option>
                        <option value="pending" <?= $filter_status == 'pending' ? 'selected' : '' ?>>Pending</option>
                        <option value="approved" <?= $filter_status == 'approved' ? 'selected' : '' ?>>Approved</option>
                        <option value="rejected" <?= $filter_status == 'rejected' ? 'selected' : '' ?>>Rejected</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button class="btn btn-primary w-100">Filter</button>
                </div>
                <div class="col-md-1">
                    <a href="registrations.php" class="btn btn-outline-secondary w-100"><i class="fas fa-sync-alt"></i></a>
                </div>
            </form>

            <div class="table-responsive">
                <table class="directory-table">
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Event</th>
                            <th>Registered At</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($registrations->num_rows > 0): ?>
                            <?php while ($r = $registrations->fetch_assoc()): ?>
                                <tr>
                                    <td>
                                        <div class="fw-bold"><?= htmlspecialchars($r['user_name']) ?></div>
                                        <small class="text-muted"><?= htmlspecialchars($r['user_email']) ?></small>
                                    </td>
                                    <td>
                                        <div class="fw-semibold text-primary"><?= htmlspecialchars($r['event_title']) ?></div>
                                    </td>
                                    <td><?= date("d/m/Y H:i", strtotime($r['registered_at'])) ?></td>
                                    <td>
                                        <?php
                                        $badge_class = ($r['status'] == 'approved') ? 'bg-success' : (($r['status'] == 'rejected') ? 'bg-danger' : 'bg-warning');
                                        ?>
                                        <span class="badge <?= $badge_class ?>"><?= ucfirst($r['status']) ?></span>
                                    </td>
                                    <td>
                                        <?php if ($r['status'] == 'pending'): ?>
                                            <form method="POST" class="d-inline">
                                                <input type="hidden" name="reg_id" value="<?= $r['id'] ?>">
                                                <input type="hidden" name="status" value="approved">
                                                <button type="submit" name="update_status" class="btn btn-sm btn-success me-1">
                                                    <i class="fas fa-check"></i> Approve
                                                </button>
                                            </form>
                                            <form method="POST" class="d-inline">
                                                <input type="hidden" name="reg_id" value="<?= $r['id'] ?>">
                                                <input type="hidden" name="status" value="rejected">
                                                <button type="submit" name="update_status" class="btn btn-sm btn-danger">
                                                    <i class="fas fa-times"></i> Reject
                                                </button>
                                            </form>
                                        <?php else: ?>
                                            <span class="text-muted small">Processed</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center py-5">
                                    <i class="fas fa-inbox fa-3x text-muted mb-3 d-block"></i>
                                    No registrations found.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include "includes/admin_footer.php"; ?>
