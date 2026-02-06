<?php
session_start();
include "../config/db.php";

/* =========================
   USER ONLY
========================= */
if (!isset($_SESSION['id']) || $_SESSION['role'] !== 'user') {
    header("Location: ../index.php");
    exit;
}

$user_id = $_SESSION['id'];

/* =========================
   SEARCH / FILTER
========================= */
$search = $_GET['search'] ?? '';
$filter_status = $_GET['status'] ?? '';

// Only show events that the user has registered for
$query = "
    SELECT e.*, r.status AS registration_status 
    FROM events e
    INNER JOIN registrations r ON r.event_id = e.id
    WHERE r.user_id = ?
";
$params = [$user_id];
$types = "i";

// Search by title or location
if ($search) {
    $query .= " AND (e.title LIKE ? OR e.location LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $types .= "ss";
}

// Registration status filter
if ($filter_status && in_array($filter_status, ['pending','approved','rejected'])) {
    $query .= " AND r.status = ?";
    $params[] = $filter_status;
    $types .= "s";
}

$query .= " ORDER BY e.event_date DESC";

$stmt = $conn->prepare($query);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$events = $stmt->get_result();

include "includes/user_header.php";
?>

<div class="section-header">
    <i class="fas fa-ticket-alt"></i>
    <h3>My Registered Events</h3>
    <div class="section-title-line"></div>
</div>

<!-- SEARCH / FILTER FORM -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body p-3">
        <form method="GET" class="row g-3">
            <div class="col-md-6">
                <div class="input-group">
                    <span class="input-group-text bg-light border-0"><i class="fas fa-search text-muted"></i></span>
                    <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" class="form-control bg-light border-0" placeholder="Search by title or location">
                </div>
            </div>
            <div class="col-md-4">
                <select name="status" class="form-select bg-light border-0">
                    <option value="">All Statuses</option>
                    <option value="pending" <?= $filter_status=='pending' ? 'selected' : '' ?>>Pending</option>
                    <option value="approved" <?= $filter_status=='approved' ? 'selected' : '' ?>>Approved</option>
                    <option value="rejected" <?= $filter_status=='rejected' ? 'selected' : '' ?>>Rejected</option>
                </select>
            </div>
            <div class="col-md-2">
                <button class="btn btn-primary w-100">Filter</button>
            </div>
        </form>
    </div>
</div>

<?php if ($events->num_rows === 0) { ?>
    <div class="card border-0 shadow-sm">
        <div class="card-body p-5 text-center">
            <i class="fas fa-calendar-times fa-3x text-muted mb-3 opacity-25"></i>
            <h4 class="text-muted">No registrations found.</h4>
            <p class="mb-0">You haven't registered for any events matching your search yet.</p>
        </div>
    </div>
<?php } else { ?>
    <div class="row g-4">
    <?php while ($e = $events->fetch_assoc()) { ?>
        <div class="col-md-6 col-lg-4">
            <div class="card h-100 shadow-sm border-0">
                <div class="card-body p-4 d-flex flex-column">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <h4 class="fw-bold mb-0"><?= htmlspecialchars($e['title']) ?></h4>
                        <?php if ($e['registration_status'] === 'approved') { ?>
                            <span class="badge bg-success"><i class="fas fa-check-circle"></i> Approved</span>
                        <?php } elseif ($e['registration_status'] === 'rejected') { ?>
                            <span class="badge bg-danger"><i class="fas fa-times-circle"></i> Rejected</span>
                        <?php } else { ?>
                            <span class="badge bg-warning text-dark"><i class="fas fa-clock"></i> Pending</span>
                        <?php } ?>
                    </div>
                    
                    <p class="text-muted small mb-4" style="height: 4.5em; overflow: hidden;"><?= htmlspecialchars($e['description']) ?></p>

                    <div class="mt-auto">
                        <div class="d-flex align-items-center mb-2 small text-muted">
                            <i class="fas fa-calendar-alt text-primary me-2"></i>
                            <span><?= date("M d, Y", strtotime($e['event_date'])) ?></span>
                        </div>
                        <div class="d-flex align-items-center mb-0 small text-muted">
                            <i class="fas fa-map-marker-alt text-danger me-2"></i>
                            <span><?= htmlspecialchars($e['location']) ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php } ?>
    </div>
<?php } ?>

<?php include "includes/user_footer.php"; ?>
