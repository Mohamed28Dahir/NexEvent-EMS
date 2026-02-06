<?php
session_start();
include "../config/db.php";

// Only user access
if (!isset($_SESSION['id']) || $_SESSION['role'] !== 'user') {
    header("Location: ../index.php");
    exit;
}

$user_id = $_SESSION['id'];

// --- STATS CALCULATION ---
$stats_query = "
    SELECT 
        SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved_count,
        SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_count,
        SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected_count
    FROM registrations 
    WHERE user_id = $user_id
";
$stats_res = $conn->query($stats_query)->fetch_assoc();
$approved = $stats_res['approved_count'] ?? 0;
$pending = $stats_res['pending_count'] ?? 0;
$rejected = $stats_res['rejected_count'] ?? 0;

// Fetch events user has NOT registered for yet
$events = $conn->query("
    SELECT * FROM events 
    WHERE id NOT IN (
        SELECT event_id FROM registrations WHERE user_id = $user_id
    )
    AND status = 'approved'
    ORDER BY event_date DESC
");

include "includes/user_header.php";
?>

<!-- DASHBOARD STATS -->
<div class="row g-4 mb-5">
    <!-- APPROVED -->
    <div class="col-md-4">
        <div class="card border-0 shadow-sm transition-hover" style="border-left: 5px solid #10b981 !important;">
            <div class="card-body p-4 d-flex align-items-center">
                <div class="bg-success-subtle p-3 rounded-circle me-3" style="background: #ecfdf5;">
                    <i class="fas fa-check-circle text-success fa-2x"></i>
                </div>
                <div>
                    <h2 class="fw-bold mb-0"><?= $approved ?></h2>
                    <p class="text-muted small mb-0">Approved Events</p>
                </div>
            </div>
        </div>
    </div>
    
    <!-- PENDING -->
    <div class="col-md-4">
        <div class="card border-0 shadow-sm transition-hover" style="border-left: 5px solid #f59e0b !important;">
            <div class="card-body p-4 d-flex align-items-center">
                <div class="bg-warning-subtle p-3 rounded-circle me-3" style="background: #fffbeb;">
                    <i class="fas fa-clock text-warning fa-2x"></i>
                </div>
                <div>
                    <h2 class="fw-bold mb-0"><?= $pending ?></h2>
                    <p class="text-muted small mb-0">Pending Approval</p>
                </div>
            </div>
        </div>
    </div>

    <!-- REJECTED -->
    <div class="col-md-4">
        <div class="card border-0 shadow-sm transition-hover" style="border-left: 5px solid #ef4444 !important;">
            <div class="card-body p-4 d-flex align-items-center">
                <div class="bg-danger-subtle p-3 rounded-circle me-3" style="background: #fef2f2;">
                    <i class="fas fa-times-circle text-danger fa-2x"></i>
                </div>
                <div>
                    <h2 class="fw-bold mb-0"><?= $rejected ?></h2>
                    <p class="text-muted small mb-0">Rejected Events</p>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="section-header">
    <i class="fas fa-calendar-star"></i>
    <h3>Available Events</h3>
    <div class="section-title-line"></div>
</div>

<?php if ($events->num_rows > 0) { ?>
    <div class="row g-4">
        <?php while ($e = $events->fetch_assoc()) { ?>
            <div class="col-md-6 col-lg-4">
                <div class="card h-100 shadow-sm border-0 transition-hover">
                    <div class="card-body p-4 d-flex flex-column">
                        <h4 class="fw-bold mb-3"><?= htmlspecialchars($e['title']); ?></h4>
                        <p class="text-muted small mb-4" style="display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden;">
                            <?= htmlspecialchars($e['description']); ?>
                        </p>
                        <div class="mt-auto">
                            <div class="d-flex align-items-center mb-2 small text-muted">
                                <i class="fas fa-calendar-alt text-primary me-2"></i>
                                <span><?= date("M d, Y", strtotime($e['event_date'])); ?></span>
                            </div>
                            <div class="d-flex align-items-center mb-4 small text-muted">
                                <i class="fas fa-map-marker-alt text-danger me-2"></i>
                                <span><?= htmlspecialchars($e['location']); ?></span>
                            </div>
                            <a href="register_event.php?event_id=<?= $e['id']; ?>" class="btn btn-fancy w-100" style="background: linear-gradient(135deg, #4f46e5, #4338ca);">
                                <i class="fas fa-plus-circle"></i> Register Now
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        <?php } ?>
    </div>
<?php } ?>

<?php include "includes/user_footer.php"; ?>
