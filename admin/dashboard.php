<?php
session_start();
include "../config/db.php";

// Only admin access
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit;
}

$username = isset($_SESSION['name']) ? $_SESSION['name'] : 'Admin';

// =========================
// EVENTS COUNTS
// =========================
$events_counts = $conn->query("
    SELECT 
        SUM(status='approved') AS approved,
        SUM(status='pending') AS pending,
        SUM(status='rejected') AS rejected
    FROM events
")->fetch_assoc();

// =========================
// USERS COUNTS
// =========================
$users_counts = $conn->query("
    SELECT 
        SUM(role='admin') AS admin,
        SUM(role='user') AS user
    FROM users
")->fetch_assoc();
?>

<?php include "includes/admin_header.php"; ?>

<div class="container mt-5">

    <h2 class="mb-4">🛠️ Admin Dashboard</h2>

    <div class="row g-4">

        <!-- EVENT STATUS CARDS -->
        <div class="col-md-4">
            <div class="card text-center h-100 shadow-sm p-3">
                <h5>📅 Approved Events</h5>
                <h3 class="text-success"><?= $events_counts['approved'] ?? 0 ?></h3>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-center h-100 shadow-sm p-3">
                <h5>⏳ Pending Events</h5>
                <h3 class="text-warning"><?= $events_counts['pending'] ?? 0 ?></h3>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-center h-100 shadow-sm p-3">
                <h5>❌ Rejected Events</h5>
                <h3 class="text-danger"><?= $events_counts['rejected'] ?? 0 ?></h3>
            </div>
        </div>

    </div>

    <div class="row g-4 mt-4">

        <!-- USERS ROLE CARDS -->
        <div class="col-md-6">
            <div class="card text-center h-100 shadow-sm p-3">
                <h5>👑 Admins</h5>
                <h3 class="text-primary"><?= $users_counts['admin'] ?? 0 ?></h3>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card text-center h-100 shadow-sm p-3">
                <h5>👥 Users</h5>
                <h3 class="text-secondary"><?= $users_counts['user'] ?? 0 ?></h3>
            </div>
        </div>

    </div>

    <!-- OPTIONAL: CHARTS -->
    <div class="row g-4 mt-5">
        <div class="col-md-6">
            <div class="card p-3 shadow-sm">
                <h5 class="mb-3">Event Status Chart</h5>
                <canvas id="eventsChart"></canvas>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card p-3 shadow-sm">
                <h5 class="mb-3">User Roles Chart</h5>
                <canvas id="usersChart"></canvas>
            </div>
        </div>
    </div>

</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const ctxEvents = document.getElementById('eventsChart').getContext('2d');
const eventsChart = new Chart(ctxEvents, {
    type: 'bar',
    data: {
        labels: ['Approved', 'Pending', 'Rejected'],
        datasets: [{
            label: 'Total Events',
            data: [<?= $events_counts['approved'] ?? 0 ?>, <?= $events_counts['pending'] ?? 0 ?>, <?= $events_counts['rejected'] ?? 0 ?>],
            backgroundColor: ['#10b981', '#f59e0b', '#ef4444'],
            borderRadius: 8
        }]
    },
    options: { 
        responsive: true,
        plugins: {
            legend: { display: false }
        },
        scales: {
            y: { beginAtZero: true, ticks: { stepSize: 1 } }
        }
    }
});

const ctxUsers = document.getElementById('usersChart').getContext('2d');
const usersChart = new Chart(ctxUsers, {
    type: 'doughnut',
    data: {
        labels: ['Admins','Users'],
        datasets: [{
            data: [<?= $users_counts['admin'] ?>, <?= $users_counts['user'] ?>],
            backgroundColor: ['#007bff','#6c757d']
        }]
    },
    options: { responsive: true }
});
</script>

<?php include "includes/admin_footer.php"; ?>
