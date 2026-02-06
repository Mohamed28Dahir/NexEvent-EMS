<?php
session_start();
include "../config/db.php";

/* =========================
   ADMIN ONLY
========================= */
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit;
}

$username = $_SESSION['name'] ?? 'Admin';

/* =========================
   EXPORT LOGIC
========================= */
if (isset($_GET['export'])) {
    $format = $_GET['export'];
    $eventsData = [];

    $res = $conn->query("SELECT * FROM events ORDER BY event_date DESC");
    while ($e = $res->fetch_assoc()) {
        $event_id = $e['id'];
        $regs = $conn->query("
            SELECT u.name, r.status AS reg_status
            FROM registrations r
            JOIN users u ON r.user_id = u.id
            WHERE r.event_id = $event_id
        ");

        $users = [];
        while ($u = $regs->fetch_assoc()) {
            $users[] = $u['name'] . " (" . ucfirst($u['reg_status']) . ")";
        }

        $e['users'] = $users ? implode(", ", $users) : "No registrations";
        $eventsData[] = $e;
    }

    // CSV
    if ($format === 'csv') {
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="events.csv"');
        $out = fopen('php://output', 'w');
        fputcsv($out, ['ID','Title','Date','Location','Status','Registered Users']);
        foreach ($eventsData as $row) {
            fputcsv($out, [$row['id'],$row['title'],$row['event_date'],$row['location'],$row['status'],$row['users']]);
        }
        fclose($out);
        exit;
    }

    // PDF
    if ($format === 'pdf') {
        require_once __DIR__ . '/../vendor/autoload.php';
        $pdf = new \TCPDF();
        $pdf->AddPage();
        $html = '<h2>All Events</h2><table border="1" cellpadding="4">';
        $html .= '<tr><th>ID</th><th>Title</th><th>Date</th><th>Location</th><th>Status</th><th>Users</th></tr>';
        foreach ($eventsData as $row) {
            $html .= "<tr>
                <td>{$row['id']}</td>
                <td>{$row['title']}</td>
                <td>{$row['event_date']}</td>
                <td>{$row['location']}</td>
                <td>{$row['status']}</td>
                <td>{$row['users']}</td>
            </tr>";
        }
        $html .= '</table>';
        $pdf->writeHTML($html);
        $pdf->Output('events.pdf', 'D');
        exit;
    }

    // Excel
    if ($format === 'excel') {
        require_once __DIR__ . '/../vendor/autoload.php';
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $headers = ['ID','Title','Date','Location','Status','Registered Users'];
        foreach ($headers as $i => $h) {
            $sheet->setCellValue(chr(65+$i).'1', $h);
        }

        $row = 2;
        foreach ($eventsData as $e) {
            $sheet->fromArray(
                [$e['id'],$e['title'],$e['event_date'],$e['location'],$e['status'],$e['users']],
                null,
                'A'.$row++
            );
        }

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="events.xlsx"');
        \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet,'Xlsx')->save('php://output');
        exit;
    }
}

/* =========================
   ADD / UPDATE / DELETE
========================= */
if (isset($_POST['add'])) {
    $status = 'pending';
    $stmt = $conn->prepare(
        "INSERT INTO events (title, description, event_date, location, status)
         VALUES (?, ?, ?, ?, ?)"
    );
    $stmt->bind_param(
        "sssss",
        $_POST['title'],
        $_POST['description'],
        $_POST['event_date'],
        $_POST['location'],
        $status
    );
    $stmt->execute();
    header("Location: events.php");
    exit;
}

if (isset($_POST['update'])) {
    $stmt = $conn->prepare(
        "UPDATE events
         SET title=?, description=?, event_date=?, location=?, status=?
         WHERE id=?"
    );
    $stmt->bind_param(
        "sssssi",
        $_POST['title'],
        $_POST['description'],
        $_POST['event_date'],
        $_POST['location'],
        $_POST['status'],
        $_POST['id']
    );
    $stmt->execute();
    header("Location: events.php");
    exit;
}

if (isset($_GET['delete'])) {
    $stmt = $conn->prepare("DELETE FROM events WHERE id=?");
    $stmt->bind_param("i", $_GET['delete']);
    $stmt->execute();
    header("Location: events.php");
    exit;
}

/* =========================
   SEARCH / FILTER
========================= */
$search = $_GET['search'] ?? '';
$filter_status = $_GET['status'] ?? '';

$query = "SELECT * FROM events WHERE 1=1";
$params = [];
$types = "";

if ($search) {
    if (is_numeric($search)) {
        $query .= " AND (id = ? OR title LIKE ? OR location LIKE ?)";
        $params[] = $search;
        $params[] = "%$search%";
        $params[] = "%$search%";
        $types .= "iss";
    } else {
        $query .= " AND (title LIKE ? OR location LIKE ?)";
        $params[] = "%$search%";
        $params[] = "%$search%";
        $types .= "ss";
    }
}

if ($filter_status && in_array($filter_status, ['pending','approved','rejected'])) {
    $query .= " AND status = ?";
    $params[] = $filter_status;
    $types .= "s";
}

$query .= " ORDER BY event_date DESC";
$stmt = $conn->prepare($query);
if ($params) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$events = $stmt->get_result();
?>

<?php include "includes/admin_header.php"; ?>

<style>
.registered-users {
    max-width: 220px;
    white-space: nowrap;
    overflow-x: auto;
    font-size: 0.9rem;
}
.actions {
    width: 140px;
    white-space: nowrap;
}
</style>

<div class="container-fluid py-4">
    <!-- SECTION: EVENT REGISTRATION -->
    <div class="card mb-5 border-0 shadow-sm">
        <div class="card-body p-4">
            <div class="section-header">
                <i class="fas fa-calendar-plus"></i>
                <h3>Event Registration</h3>
                <div class="section-title-line"></div>
            </div>

            <form method="POST">
                <input type="hidden" name="id" id="event-id">
                <div class="form-grid">
                    <div class="form-group">
                        <label for="title">Event Title</label>
                        <input type="text" class="form-control" name="title" id="title" placeholder="Enter event title" required>
                    </div>
                    <div class="form-group">
                        <label for="event_date">Event Date</label>
                        <input type="date" class="form-control" name="event_date" id="event_date" required>
                    </div>
                    <div class="form-group">
                        <label for="location">Location</label>
                        <input type="text" class="form-control" name="location" id="location" placeholder="City, Venue" required>
                    </div>
                    <div class="form-group" style="grid-column: span 2;">
                        <label for="description">Event Description</label>
                        <textarea class="form-control" name="description" id="description" rows="1" placeholder="Describe the event..." required></textarea>
                    </div>
                    <div class="form-group d-none" id="status-div">
                        <label for="status">Event Status</label>
                        <select class="form-select" name="status" id="status">
                            <option value="pending">Pending</option>
                            <option value="approved">Approved</option>
                            <option value="rejected">Rejected</option>
                        </select>
                    </div>
                </div>

                <div class="d-flex gap-3 mt-4">
                    <button type="submit" class="btn btn-fancy" id="form-btn" name="add">
                        <i class="fas fa-check-circle"></i> <span>Register Event</span>
                    </button>
                    <button type="reset" class="btn btn-reset" onclick="window.location.reload()">
                        <i class="fas fa-sync-alt"></i> Reset
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- SECTION: EVENT DIRECTORY -->
    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
            <div class="section-header">
                <i class="fas fa-list-ul"></i>
                <h3>Event Directory</h3>
                <div class="section-title-line"></div>
            </div>

            <!-- SEARCH & FILTER BAR -->
            <div class="row g-3 mb-4 align-items-end">
                <div class="col-md-5">
                    <form method="GET" class="d-flex gap-2">
                        <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" class="form-control py-2" placeholder="Search by ID, title, or location">
                </div>
                <div class="col-md-3">
                        <select name="status" class="form-select py-2">
                            <option value="">All Statuses</option>
                            <option value="pending" <?= $filter_status=='pending'?'selected':'' ?>>Pending</option>
                            <option value="approved" <?= $filter_status=='approved'?'selected':'' ?>>Approved</option>
                            <option value="rejected" <?= $filter_status=='rejected'?'selected':'' ?>>Rejected</option>
                        </select>
                </div>
                <div class="col-md-1">
                        <button class="btn btn-primary px-4 py-2">Filter</button>
                    </form>
                </div>
                <div class="col-md-3 text-end">
                    <div class="btn-group">
                        <a href="?export=csv" class="btn btn-outline-success btn-sm"><i class="fas fa-file-csv"></i> CSV</a>
                        <a href="?export=pdf" class="btn btn-outline-danger btn-sm"><i class="fas fa-file-pdf"></i> PDF</a>
                        <a href="?export=excel" class="btn btn-outline-primary btn-sm"><i class="fas fa-file-excel"></i> Excel</a>
                    </div>
                </div>
            </div>

            <div class="table-responsive">
                <table class="directory-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Title</th>
                            <th>Date</th>
                            <th>Location</th>
                            <th>Status</th>
                            <th>Users</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($e = $events->fetch_assoc()):
                            $event_id = $e['id'];
                            $regs = $conn->query("SELECT u.name,r.status FROM registrations r JOIN users u ON r.user_id=u.id WHERE r.event_id=$event_id");
                            $users=[];
                            while($u=$regs->fetch_assoc()){
                                $users[]=$u['name']." (".ucfirst($u['status']).")";
                            }
                        ?>
                        <tr>
                            <td class="fw-bold text-muted"><?= $e['id'] ?></td>
                            <td class="fw-semibold"><?= htmlspecialchars($e['title']) ?></td>
                            <td><?= date("d/m/Y", strtotime($e['event_date'])) ?></td>
                            <td><i class="fas fa-map-marker-alt text-primary me-2"></i><?= htmlspecialchars($e['location']) ?></td>
                            <td>
                                <?php
                                $badge_class = ($e['status'] == 'approved') ? 'bg-success' : (($e['status'] == 'rejected') ? 'bg-danger' : 'bg-warning');
                                ?>
                                <span class="badge <?= $badge_class ?>"><?= ucfirst($e['status']) ?></span>
                            </td>
                            <td class="registered-users text-muted"><?= $users ? implode(', ', $users) : 'No registrations' ?></td>
                            <td>
                                <div class="d-flex gap-2">
                                    <a href="registrations.php?event_id=<?= $e['id'] ?>" 
                                       class="btn btn-sm btn-outline-info" 
                                       title="View Registrations">
                                        <i class="fas fa-users"></i>
                                    </a>
                                    <button class="btn btn-sm btn-outline-primary edit-btn"
                                            data-id="<?= $e['id'] ?>"
                                            data-title="<?= htmlspecialchars($e['title']) ?>"
                                            data-description="<?= htmlspecialchars($e['description']) ?>"
                                            data-date="<?= $e['event_date'] ?>"
                                            data-location="<?= htmlspecialchars($e['location']) ?>"
                                            data-status="<?= $e['status'] ?>">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <a href="?delete=<?= $e['id'] ?>"
                                       class="btn btn-sm btn-outline-danger"
                                       onclick="return confirm('Delete this event?')">
                                        <i class="fas fa-trash-alt"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
const editBtns = document.querySelectorAll('.edit-btn');
const idInput = document.getElementById('event-id');
const titleInput = document.getElementById('title');
const descInput = document.getElementById('description');
const dateInput = document.getElementById('event_date');
const locationInput = document.getElementById('location');
const statusDiv = document.getElementById('status-div');
const statusSelect = document.getElementById('status');
const formBtn = document.getElementById('form-btn');

editBtns.forEach(btn => {
    btn.addEventListener('click', () => {
        idInput.value = btn.dataset.id;
        titleInput.value = btn.dataset.title;
        descInput.value = btn.dataset.description;
        dateInput.value = btn.dataset.date;
        locationInput.value = btn.dataset.location;
        statusSelect.value = btn.dataset.status;

        statusDiv.classList.remove('d-none');
        formBtn.name = 'update';
        formBtn.textContent = 'Update Event';
        formBtn.classList.remove('btn-success');
        formBtn.classList.add('btn-primary');
    });
});
</script>

<?php include "includes/admin_footer.php"; ?>
