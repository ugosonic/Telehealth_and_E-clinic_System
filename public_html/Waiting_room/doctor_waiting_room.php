<?php
session_start();
require_once '../init.php';
require_once '../config.php';
require_once '../access_control.php';
require_once '../sidebar.php';

if (!isset($_SESSION['usergroup']) || $_SESSION['usergroup'] !== 'Doctor') {
    header("Location: ../login/login.php");
    exit();
}

// Force reload after 10 mins (600000 ms)
echo "<script>
  setTimeout(() => { window.location.reload(); }, 600000);
</script>";

$today = date('Y-m-d');
$nowTime = date('H:i:s');

/**
 * Helper function for bind_param references
 */
function refValues($arr) {
    $refs = [];
    foreach ($arr as $key => $value) {
        $refs[$key] = &$arr[$key];
    }
    return $refs;
}

/*
  ----------------------------------------------------------------------------
  1) Insert today's appointments (<= now) into waiting_room if not already done
  ----------------------------------------------------------------------------
*/
$appSql = "
    SELECT a.appointment_id, a.patient_id, a.type
    FROM appointments a
    WHERE a.department = 'Doctor'
      AND a.appointment_date = ?
      AND a.appointment_time <= ?
      AND NOT EXISTS (
        SELECT 1 FROM waiting_room wr
        WHERE wr.patient_id = a.patient_id
          AND wr.waiting_room = 'Doctor'
          AND wr.status = 'Waiting'
          AND DATE(wr.check_in_time) = ?
      )
";
$appStmt = $conn->prepare($appSql);
if (!$appStmt) {
    die('Error preparing appointment check: ' . $conn->error);
}
$appStmt->bind_param('sss', $today, $nowTime, $today);
$appStmt->execute();
$appRes = $appStmt->get_result();
while ($a = $appRes->fetch_assoc()) {
    $pID      = $a['patient_id'];
    $apptType = $a['type']; // 'Online' or 'In-clinic'
    $ins = $conn->prepare("
        INSERT INTO waiting_room
            (patient_id, waiting_room, check_in_time, status, staff_name, from_source, appointment_type)
        VALUES (?, 'Doctor', NOW(), 'Waiting', 'From Appointment', 'Appointment', ?)
    ");
    if (!$ins) {
        die('Error preparing insert: ' . $conn->error);
    }
    $ins->bind_param('is', $pID, $apptType);
    $ins->execute();
    $ins->close();
}
$appStmt->close();

/*
  ----------------------------------------------------------------------------
  2) Handle filters: date, search, status, appt_type
  ----------------------------------------------------------------------------
*/
$filter_date     = isset($_GET['filter_date']) ? $_GET['filter_date'] : $today;
$search          = isset($_GET['search']) ? trim($_GET['search']) : '';
$filter_status   = isset($_GET['status']) ? $_GET['status'] : '';
$filter_appttype = isset($_GET['appt_type']) ? $_GET['appt_type'] : '';

// Don’t allow future date
if ($filter_date && $filter_date > date('Y-m-d')) {
    $filter_date = date('Y-m-d'); 
}

// Build base WHERE
$where  = "wr.waiting_room='Doctor'";
$params = [];
$types  = "";

// Filter by date (default today if no filter_date given)
$where .= " AND DATE(wr.check_in_time)=?";
$params[] = $filter_date;
$types   .= 's';

// If we have a search => match name/ID/DOB
if ($search !== '') {
    $where .= " AND (
                   p.first_name LIKE CONCAT('%',?,'%')
                OR p.surname LIKE CONCAT('%',?,'%')
                OR p.patient_id LIKE CONCAT('%',?,'%')
                OR p.dob LIKE CONCAT('%',?,'%')
               )";
    $params[] = $search;
    $params[] = $search;
    $params[] = $search;
    $params[] = $search;
    $types   .= 'ssss';
}

// If filter_status => 'Waiting','Accepted','Cancelled'
if ($filter_status !== '') {
    $where .= " AND wr.status=?";
    $params[] = $filter_status;
    $types   .= 's';
}

// If filter_appttype => 'Online','In-clinic'
if ($filter_appttype !== '') {
    $where .= " AND wr.appointment_type=?";
    $params[] = $filter_appttype;
    $types   .= 's';
}

/*
  ----------------------------------------------------------------------------
  3) Sorting
  ----------------------------------------------------------------------------
*/
$allowedSortCols = [
    'wr.check_in_time',
    'wr.status',
    'wr.appointment_type',
    'p.surname',
    'wr.priority',
    'wr.check_out_time'
];
$sort_col = isset($_GET['sort_col']) ? $_GET['sort_col'] : 'wr.priority';
if (!in_array($sort_col, $allowedSortCols)) {
    $sort_col = 'wr.priority'; // default
}
$sort_dir = (isset($_GET['sort_dir']) && strtoupper($_GET['sort_dir']) === 'DESC') ? 'DESC' : 'ASC';

/*
  ----------------------------------------------------------------------------
  4) Pagination
  ----------------------------------------------------------------------------
*/
$per_page = 10;
$page     = isset($_GET['page']) ? intval($_GET['page']) : 1;
if ($page < 1) $page = 1;
$start    = ($page - 1) * $per_page;

// We won’t calculate total pages in this example, but you can do so similarly:
$totalPages = 1; // Placeholder if you plan to implement actual pagination

/*
  ----------------------------------------------------------------------------
  5) Main Query
  ----------------------------------------------------------------------------
*/
$sql = "
  SELECT DISTINCT
    wr.waiting_id,
    wr.patient_id,
    wr.check_in_time,
    wr.check_out_time,
    wr.status,
    wr.staff_name,
    wr.from_source,
    wr.appointment_type,
    wr.notes,
    wr.priority,
    p.first_name,
    p.surname,
    meetings.meeting_id
  FROM waiting_room wr
  JOIN patient_db p ON wr.patient_id = p.patient_id
  LEFT JOIN meetings ON meetings.patient_id = wr.patient_id
  WHERE $where
  ORDER BY wr.priority DESC, $sort_col $sort_dir
  LIMIT ?, ?
";

$finalTypes = $types . 'ii';
$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("Error preparing main query: " . $conn->error . "\nSQL: " . $sql);
}
$allParams = array_merge($params, [$start, $per_page]);
$stmt->bind_param(...refValues([$finalTypes, ...$allParams]));
$stmt->execute();
$res = $stmt->get_result();

// Calculate average waiting times
$avgOnline = 0;
$avgInclinic = 0;

// Calculate Average Waiting Times
$onlineTimes = [];
$inClinicTimes = [];
$waitingTimeSql = "
    SELECT appointment_type, TIMESTAMPDIFF(MINUTE, check_in_time, NOW()) AS waiting_time
    FROM waiting_room
    WHERE status = 'Waiting' AND waiting_room = 'Doctor' AND DATE(check_in_time) = ?
";
$waitingStmt = $conn->prepare($waitingTimeSql);
$waitingStmt->bind_param('s', $filter_date);
$waitingStmt->execute();
$waitingRes = $waitingStmt->get_result();

while ($row = $waitingRes->fetch_assoc()) {
    if ($row['appointment_type'] === 'Online') {
        $onlineTimes[] = $row['waiting_time'];
    } else {
        $inClinicTimes[] = $row['waiting_time'];
    }
}
$avgOnline = count($onlineTimes) > 0 ? round(array_sum($onlineTimes) / count($onlineTimes)) : 0;
$avgInclinic = count($inClinicTimes) > 0 ? round(array_sum($inClinicTimes) / count($inClinicTimes)) : 0;
$waitingStmt->close();

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Doctor's Waiting Room</title>
  <link 
    rel="stylesheet"
    href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css"
  />
  <style>
    .main-container {
      margin-left: 100px; /* enough for the sidebar */
      padding: 20px;
    }
    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 1rem;
    }
    th, td {
      padding: 0.75rem;
      border: 1px solid #ccc;
    }
    th {
      background: #f8f9fa;
    }
    .row-priority {
        background-color: #ffcccc; /* Light red for Priority rows */
    }
    .row-online {
        background-color: #e0ffe0; /* Light green for Online appointments */
    }
    .row-inclinic {
        background-color: #e0f0ff; /* Light blue for In-clinic appointments */
    }
    .btn-sm {
        padding: 5px 10px;
        font-size: 0.8rem;
        margin: 2px;
    }
    .btn-success {
        background-color: #28a745;
        color: #fff;
    }
    .btn-primary {
        background-color: #007bff;
        color: #fff;
    }
    .btn-danger {
        background-color: #dc3545;
        color: #fff;
    }
  </style>
</head>
<body>

<div class="main-container">

  <!-- Possibly show success/error messages -->
  <?php if (isset($_SESSION['message'])): ?>
    <div class="alert alert-<?= $_SESSION['message_type'] ?? 'info'; ?> alert-dismissible fade show" role="alert">
      <?= htmlspecialchars($_SESSION['message']); ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php unset($_SESSION['message'], $_SESSION['message_type']); ?>
  <?php endif; ?>

  <h2>Doctor's Waiting Room</h2>

  <div class="mb-3">
    <strong>Average Waiting (Online):</strong> <?= $avgOnline; ?> mins |
    <strong>Average Waiting (In-clinic):</strong> <?= $avgInclinic; ?> mins
  </div>

  <!-- Filters + Sorting Form -->
  <form method="GET" class="row gx-3 gy-2 align-items-center mb-3">
    <div class="col-auto">
      <label for="filter_date" class="visually-hidden">Filter Date</label>
      <input type="date" class="form-control" id="filter_date" name="filter_date"
             max="<?= date('Y-m-d'); ?>"
             value="<?= htmlspecialchars($filter_date); ?>">
    </div>
    <div class="col-auto">
      <label for="search" class="visually-hidden">Search</label>
      <input type="text" class="form-control" id="search" name="search" 
             placeholder="Name / ID / DOB" 
             value="<?= htmlspecialchars($search); ?>">
    </div>
    <div class="col-auto">
      <label for="status" class="visually-hidden">Status</label>
      <select class="form-select" name="status" id="status">
        <option value=""   <?= ($filter_status===''?'selected':''); ?>>All Status</option>
        <option value="Waiting"   <?= ($filter_status==='Waiting'?'selected':''); ?>>Waiting</option>
        <option value="Accepted"  <?= ($filter_status==='Accepted'?'selected':''); ?>>Accepted</option>
        <option value="Cancelled" <?= ($filter_status==='Cancelled'?'selected':''); ?>>Cancelled</option>
      </select>
    </div>
    <div class="col-auto">
      <label for="appt_type" class="visually-hidden">Appointment Type</label>
      <select class="form-select" name="appt_type" id="appt_type">
        <option value=""          <?= ($filter_appttype===''?'selected':''); ?>>All Types</option>
        <option value="Online"    <?= ($filter_appttype==='Online'?'selected':''); ?>>Online</option>
        <option value="In-clinic" <?= ($filter_appttype==='In-clinic'?'selected':''); ?>>In-clinic</option>
      </select>
    </div>
    <div class="col-auto">
      <label for="sort_col" class="visually-hidden">Sort Column</label>
      <select class="form-select" name="sort_col" id="sort_col">
        <option value="wr.priority"         <?= ($sort_col==='wr.priority'?'selected':''); ?>>Priority</option>
        <option value="wr.check_in_time"    <?= ($sort_col==='wr.check_in_time'?'selected':''); ?>>Check-In Time</option>
        <option value="wr.status"           <?= ($sort_col==='wr.status'?'selected':''); ?>>Status</option>
        <option value="wr.appointment_type" <?= ($sort_col==='wr.appointment_type'?'selected':''); ?>>Type</option>
        <option value="p.surname"           <?= ($sort_col==='p.surname'?'selected':''); ?>>Surname</option>
        <option value="wr.check_out_time"   <?= ($sort_col==='wr.check_out_time'?'selected':''); ?>>Check-Out Time</option>
      </select>
    </div>
    <div class="col-auto">
      <label for="sort_dir" class="visually-hidden">Sort Direction</label>
      <select class="form-select" name="sort_dir" id="sort_dir">
        <option value="ASC"  <?= ($sort_dir==='ASC'?'selected':''); ?>>ASC</option>
        <option value="DESC" <?= ($sort_dir==='DESC'?'selected':''); ?>>DESC</option>
      </select>
    </div>
    <div class="col-auto">
      <button type="submit" class="btn btn-primary">Filter/Sort</button>
    </div>
  </form>

  <!-- Table -->
  <table class="table table-bordered">
    <thead class="table-dark">
        <tr>
            <th>Patient Name</th>
            <th>Check-In</th>
            <th>Check-Out</th>
            <th>Status</th>
            <th>Notes</th>
            <th>Appointment Type</th>
            <th>Priority</th>
            <th>Notes</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
    <?php while ($row = $res->fetch_assoc()): ?>
            <?php
            $rowClass = '';
            if ($row['priority']) {
                $rowClass = 'row-priority';
            } elseif ($row['appointment_type'] === 'Online') {
                $rowClass = 'row-online';
            } else {
                $rowClass = 'row-inclinic';
            }
            ?>
            <tr class="<?= $rowClass; ?>">
                <td><?= htmlspecialchars($row['first_name'] . ' ' . $row['surname']); ?></td>
                <td><?= htmlspecialchars($row['check_in_time']); ?></td>
                <td><?= $row['check_out_time'] ? htmlspecialchars($row['check_out_time']) : '--'; ?></td>
                <td><?= htmlspecialchars($row['status']); ?></td>
                 <td><?= htmlspecialchars($row['notes']?? ''); ?></td>
                <td><?= htmlspecialchars($row['appointment_type']?? ''); ?></td>
                <td><?= $row['priority'] ? 'Yes' : 'No'; ?></td>
                <td>
                    <!-- Button to open "Notes" modal -->
                    <button 
                        type="button" 
                        class="btn btn-warning btn-sm" 
                        data-bs-toggle="modal" 
                        data-bs-target="#notesModal"
                        data-wid="<?= $row['waiting_id']; ?>"
                        data-notes="<?= htmlspecialchars($row['notes']?? ''); ?>"
                    >
                        Add Notes
                    </button>
                </td>
                <td>
                    <?php if ($row['status'] === 'Waiting'): ?>
                        <?php if ($row['appointment_type'] === 'Online'): ?>
                            <!-- ONLINE => Only "Join" (which also Accepts & removes from list) -->
                            <a href="accept_patient.php?id=<?= $row['waiting_id']; ?>&m=<?= urlencode($row['meeting_id']); ?>&join=1"
                               class="btn btn-primary btn-sm">
                                Join
                            </a>
                        <?php else: ?>
                            <!-- IN-CLINIC => Only "Accept", leads to patient's profile -->
                            <a href="accept_patient.php?id=<?= $row['waiting_id']; ?>"
                               class="btn btn-success btn-sm">
                               Accept
                            </a>
                        <?php endif; ?>
                        
                        <a href="cancel_patient.php?id=<?= $row['waiting_id']; ?>" class="btn btn-danger btn-sm">
                            Cancel
                        </a>
                    <?php else: ?>
                        --
                    <?php endif; ?>
                </td>
            </tr>
        <?php endwhile; ?>
    </tbody>
  </table>

  <!-- Pagination (Placeholder) -->
  <nav>
    <ul class="pagination">
      <?php for($i=1; $i<=$totalPages; $i++): ?>
        <?php
        $q = $_GET;
        $q['page'] = $i;
        $queryStr = http_build_query($q);
        ?>
        <li class="page-item <?= ($i===$page?'active':''); ?>">
          <a class="page-link" href="?<?= $queryStr; ?>"><?= $i; ?></a>
        </li>
      <?php endfor; ?>
    </ul>
  </nav>
</div>

<!-- NOTES MODAL -->
<div 
  class="modal fade" 
  id="notesModal" 
  tabindex="-1" 
  aria-labelledby="notesModalLabel" 
  aria-hidden="true"
>
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="post" action="update_notes.php">
        <div class="modal-header">
          <h5 class="modal-title" id="notesModalLabel">Add / Update Notes</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="waiting_id" id="modal_waiting_id">
          <div class="mb-3">
            <label for="modal_notes" class="form-label">Notes</label>
            <textarea class="form-control" name="notes" id="modal_notes" rows="4"></textarea>
          </div>
          <div class="form-check">
            <input class="form-check-input" type="checkbox" name="priority" id="modal_priority" value="1">
            <label class="form-check-label" for="modal_priority">
              Mark as Priority (move to top)
            </label>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
          <button type="submit" class="btn btn-primary">Save Note</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script 
  src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js">
</script>
<script>
// Populate the notes modal
var notesModal = document.getElementById('notesModal');
notesModal.addEventListener('show.bs.modal', function (event) {
  var button = event.relatedTarget; 
  var waitingId = button.getAttribute('data-wid');
  var existingNotes = button.getAttribute('data-notes');
  
  // fill inputs
  document.getElementById('modal_waiting_id').value = waitingId;
  document.getElementById('modal_notes').value       = existingNotes || '';
  document.getElementById('modal_priority').checked  = false; // default
});
</script>
</body>
</html>
