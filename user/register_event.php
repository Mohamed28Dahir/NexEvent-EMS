<?php
session_start();
include "../config/db.php";

// Only user access
if (!isset($_SESSION['id']) || $_SESSION['role'] !== 'user') {
    header("Location: ../index.php");
    exit;
}

// Support both GET and POST
if (isset($_GET['event_id'])) {
    $event_id = $_GET['event_id'];
} elseif (isset($_POST['event_id'])) {
    $event_id = $_POST['event_id'];
} else {
    header("Location: dashboard.php");
    exit;
}

// Fetch event details
$event = $conn->query("SELECT * FROM events WHERE id=$event_id")->fetch_assoc();
if (!$event) {
    echo "<div class='container mt-5'><div class='alert alert-danger'>Event not found.</div></div>";
    exit;
}

// Handle registration confirmation
if (isset($_POST['confirm'])) {
    $user_id = $_SESSION['id'];

    // Check if user already registered
    $check = $conn->query("SELECT * FROM registrations WHERE user_id=$user_id AND event_id=$event_id");
    if ($check->num_rows == 0) {
        $conn->query("INSERT INTO registrations (user_id, event_id) VALUES ($user_id, $event_id)");
        // Redirect to view_events page after successful registration
        header("Location: view_events.php");
        exit;
    } else {
        // Already registered, redirect to view_events page
        header("Location: view_events.php");
        exit;
    }
}
?>

<?php include "../includes/header.php"; ?>

<div class="container mt-5">
    <div class="card shadow p-4">
        <h3><?= htmlspecialchars($event['title']); ?></h3>
        <p class="text-muted"><?= htmlspecialchars($event['description']); ?></p>
        <p>
            📅 <strong>Date:</strong> <?= htmlspecialchars($event['event_date']); ?><br>
            📍 <strong>Location:</strong> <?= htmlspecialchars($event['location']); ?>
        </p>

        <form method="POST">
            <input type="hidden" name="event_id" value="<?= $event_id; ?>">
            <button name="confirm" class="btn btn-success">Confirm Registration</button>
            <a href="dashboard.php" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
</div>

<?php include "../includes/footer.php"; ?>
