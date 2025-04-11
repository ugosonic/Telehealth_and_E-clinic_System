<?php

require_once '../config.php';
// lab_requests.php
include '../init.php';     






// Assuming $usergroup is set in the session or fetched from the database
$usergroup = $_SESSION['usergroup'] ?? 'Guest';
$message = "";

// 1) Connect to DB
try {
    $conn = new mysqli($servername, $username, $password, $dbname);
    if ($conn->connect_errno) {
        throw new Exception("Failed to connect to MySQL: " . $conn->connect_error);
    }
} catch (Exception $e) {
    die("Error: " . $e->getMessage());
}

// ----------------------------------------------------
// 2) Handle Save (Edit) + Status updates
// ----------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_result'])) {
    $requestId = (int)($_POST['request_id'] ?? 0);
    $newStatus = $_POST['status'] ?? 'Pending';
    $allowedStatuses = ['Pending','In Progress','Completed'];
    if (!in_array($newStatus, $allowedStatuses)) {
        $newStatus = 'Pending';
    }

    // The edited content from TinyMCE
    $resultContent = $_POST['result_content'] ?? '';

    // Update lab_requests
    $stmt = $conn->prepare("
        UPDATE lab_requests
        SET status = ?, result_content = ?
        WHERE id = ?
    ");
    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }
    $stmt->bind_param("ssi", $newStatus, $resultContent, $requestId);
    if (!$stmt->execute()) {
        die("Execute failed: " . $stmt->error);
    }
    $stmt->close();

    $message = "Lab request #$requestId updated; status = $newStatus.";
}

// ----------------------------------------------------
// 3) Filters & Pagination
// ----------------------------------------------------
$statusFilter = $_GET['status'] ?? 'Open';
$allowedFilters = ['All','Pending','In Progress','Completed','Open'];
if (!in_array($statusFilter, $allowedFilters)) {
    $statusFilter = 'Open';
}

$dateSort = $_GET['sort'] ?? 'DESC';
if (!in_array($dateSort, ['ASC','DESC'])) {
    $dateSort = 'DESC';
}

$page = max(1, (int)($_GET['page'] ?? 1));
$limit = 5;
$offset = ($page - 1) * $limit;

// Where Clause
$whereClauses = [];
if ($statusFilter === 'Open') {
    $whereClauses[] = "lr.status IN ('Pending','In Progress')";
} elseif ($statusFilter !== 'All') {
    $whereClauses[] = "lr.status = '".$conn->real_escape_string($statusFilter)."'";
}
$whereSQL = "";
if ($whereClauses) {
    $whereSQL = "WHERE " . implode(" AND ", $whereClauses);
}

// Count total
$countSQL = "SELECT COUNT(*) AS total FROM lab_requests lr $whereSQL";
$resCount = $conn->query($countSQL);
$totalRows = $resCount ? (int)$resCount->fetch_assoc()['total'] : 0;
$totalPages = ceil($totalRows / $limit);

// ----------------------------------------------------
// 4) Fetch all lab requests (with filters + pagination)
// ----------------------------------------------------
$sql = "
    SELECT 
        lr.id AS request_id,
        lr.consultation_id,
        lr.patient_id,
        lr.template_id,
        lr.date_requested,
        lr.status,
        lr.result_content,
        p.first_name AS patient_fname,
        p.middle_name AS patient_mname,
        p.surname AS patient_sname,
        p.dob AS patient_dob,
        u.title AS staff_title,
        u.first_name AS staff_fname,
        u.middle_name AS staff_mname,
        u.surname AS staff_sname,
        ltt.name AS template_name,
        ltt.content AS template_content
    FROM lab_requests lr
    JOIN patient_db p ON lr.patient_id = p.patient_id
    JOIN users u ON lr.requested_by = u.username
    LEFT JOIN lab_test_templates ltt ON lr.template_id = ltt.id
    $whereSQL
    ORDER BY lr.date_requested $dateSort
    LIMIT $limit OFFSET $offset
";

$result = $conn->query($sql);
if (!$result) {
    die("Query failed: " . $conn->error);
}

$labRequests = [];
while ($row = $result->fetch_assoc()) {
    $labRequests[] = $row;
}
$result->free();
// Function to get dashboard link based on user role
function getDashboardLink($usergroup) {
    switch ($usergroup) {
        case 'Admin':
            return "../Dashboard/admin_dashboard.php";
        case 'Doctor':
            return "../Dashboard/doctor_dashboard.php";
        case 'Lab Scientist':
            return "../Dashboard/lab_scientist_dashboard.php";
        default:
            return "../Dashboard/user_dashboard.php";
    }
}

// Function to get video consultation link based on user role
function getVideoConsultation($usergroup) {
    switch ($usergroup) {
        case 'Admin':
        case 'Doctor':
        case 'Lab Scientist':
            return "../Video_call/session.php";
        default:
            return "../Waiting_room/all_waiting_rooms.php";
    }
}

// Function to get waiting room link based on user role
function getWaitingRoomLink($usergroup) {
    switch ($usergroup) {
        case 'Admin':
            return "../Waiting_room/all_waiting_rooms.php";
        case 'Doctor':
            return "../Waiting_room/doctor_waiting_room.php";
        case 'Lab Scientist':
            return "../Waiting_room/laboratory_waiting_room.php";
        default:
            return "../Waiting_room/all_waiting_rooms.php";
    }
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <title>Lab Requests</title>

  <!-- Bootstrap 5 CSS -->
  <link 
    rel="stylesheet" 
    href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css"
  />
   <!-- icon stylesheet -->
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">  
  <!-- TinyMCE -->
  <script
    src="https://cdn.tiny.cloud/1/u1rkztuuis5q511n45h77nbkmb1vs8c8qadu6p3l1y3nnuln/tinymce/6/tinymce.min.js"
    referrerpolicy="origin"
  ></script>
</head>
<body class="bg-light">

<div class="container my-4">
    <h1 class="mb-4">Laboratory Requests</h1>

    <?php if (!empty($message)): ?>
      <div class="alert alert-info" id="popup-message">
          <?= htmlspecialchars($message); ?>
      </div>
      <script>
          setTimeout(() => {
              let popup = document.getElementById('popup-message');
              if (popup) popup.style.display = 'none';
          }, 5000);
      </script>
    <?php endif; ?>
    <!-- navbar-->
    <nav class="navbar navbar-expand-lg navbar-light">
    <div class="card mb-4" style="background-color:rgb(247, 227, 222);">
      
        <button
            class="navbar-toggler"
            type="button"
            data-bs-toggle="collapse"
            data-bs-target="#navbarNav"
            aria-controls="navbarNav"
            aria-expanded="false"
            aria-label="Toggle navigation"
        >
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
    <ul class="navbar-nav">
        <li class="nav-item">
            <a class="nav-link" href="<?= getDashboardLink($usergroup); ?>">
                <i class="fas fa-home me-2"></i> Dashboard
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="<?= getWaitingRoomLink($usergroup); ?>">
                <i class="fas fa-users me-2"></i> Waiting Room
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="<?= getVideoConsultation($usergroup); ?>">
                <i class="fas fa-video me-2"></i> Video Consultation
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="../Laboratory/create_lab_test.php">
                <i class="fas fa-plus-circle me-2"></i> Create Lab Template
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="../Laboratory/lab_requests.php">
                <i class="fas fa-file-alt me-2"></i> View Requested Laboratory
            </a>
        </li>
    </ul>
</div>

    </div>
</nav>

    <!-- Filter + Sort Panel -->
    <form method="get" class="row g-3 mb-3">
      <div class="col-auto">
        <label for="status" class="form-label">Status Filter:</label>
        <select name="status" class="form-select" id="status">
          <option value="Open" <?= $statusFilter==='Open' ? 'selected' : '' ?>>
            Pending & In Progress
          </option>
          <option value="Pending" <?= $statusFilter==='Pending' ? 'selected' : '' ?>>Pending</option>
          <option value="In Progress" <?= $statusFilter==='In Progress' ? 'selected' : '' ?>>In Progress</option>
          <option value="Completed" <?= $statusFilter==='Completed' ? 'selected' : '' ?>>Completed</option>
          <option value="All" <?= $statusFilter==='All' ? 'selected' : '' ?>>All</option>
        </select>
      </div>
      <div class="col-auto">
        <label for="sort" class="form-label">Date Sort:</label>
        <select name="sort" class="form-select" id="sort">
          <option value="DESC" <?= $dateSort==='DESC' ? 'selected' : '' ?>>Newest First</option>
          <option value="ASC" <?= $dateSort==='ASC' ? 'selected' : '' ?>>Oldest First</option>
        </select>
      </div>
      <div class="col-auto" style="margin-top:32px;">
        <button type="submit" class="btn btn-primary">Apply</button>
      </div>
    </form>

    <?php if ($totalRows === 0): ?>
      <div class="alert alert-warning">
        No lab requests found.
      </div>
    <?php else: ?>
      <div class="table-responsive mb-3">
        <table class="table table-bordered table-hover align-middle">
          <thead class="table-light">
            <tr>
              <th>Request ID</th>
              <th>Patient Name</th>
              <th>Patient ID</th>
              <th>Consultation ID</th>
              <th>Date of Birth</th>
              <th>Staff Requested</th>
              <th>Date Requested</th>
              <th>Test (Template)</th>
              <th>Status</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody>
          <?php foreach ($labRequests as $req): ?>
            <?php
              $patientFullName = trim(
                $req['patient_fname'] . ' ' .
                $req['patient_mname'] . ' ' .
                $req['patient_sname']
              );
              $staffFullName = trim(
                $req['staff_title'] . ' ' .
                $req['staff_fname'] . ' ' .
                $req['staff_mname'] . ' ' .
                $req['staff_sname']
              );

              $testName = (!empty($req['template_name']))
                          ? $req['template_name']
                          : 'Lab Test ID #'.$req['template_id'];

              $requestId = (int)$req['request_id'];
              $currentStatus = $req['status'] ?? 'Pending';

              // The full HTML is either the "result_content" or, if empty, the original "template_content".
              // We'll Base64-encode it for safe storage in data-attribute:
              $fullContent = $req['result_content'] ?: $req['template_content'];
              $encodedContent = base64_encode($fullContent);

              // We can put the status in a data attribute, or just pass "Pending"/"In Progress"/"Completed".
            ?>
            <tr>
              <td><?= htmlspecialchars($requestId); ?></td>
              <td><?= htmlspecialchars($patientFullName); ?></td>
              <td><?= htmlspecialchars($req['patient_id']); ?></td>
              <td><?= htmlspecialchars($req['consultation_id']); ?></td>
              <td><?= htmlspecialchars($req['patient_dob']); ?></td>
              <td><?= htmlspecialchars($staffFullName); ?></td>
              <td><?= htmlspecialchars($req['date_requested']); ?></td>
              <td><?= htmlspecialchars($testName); ?></td>
              <td><?= htmlspecialchars($currentStatus); ?></td>
              <td>
                <!-- "Edit" button with data attributes -->
                <button 
                  type="button" 
                  class="btn btn-sm btn-success"
                  data-bs-toggle="modal"
                  data-bs-target="#editModal"
                  data-request-id="<?= $requestId; ?>"
                  data-status="<?= htmlspecialchars($currentStatus, ENT_QUOTES); ?>"
                  data-content="<?= $encodedContent; ?>"
                  onclick="openEditModalFromButton(this)"
                >
                  Edit
                </button>
              </td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <?php if ($totalPages > 1): ?>
        <nav>
          <ul class="pagination">
            <?php for ($i=1; $i <= $totalPages; $i++): ?>
              <li class="page-item <?= ($i === $page) ? 'active' : '' ?>">
                <a 
                  class="page-link" 
                  href="?status=<?= urlencode($statusFilter) ?>&sort=<?= urlencode($dateSort) ?>&page=<?= $i ?>"
                >
                  <?= $i ?>
                </a>
              </li>
            <?php endfor; ?>
          </ul>
        </nav>
      <?php endif; ?>
    <?php endif; ?>
</div>

<!-- ============== -->
<!-- Edit Modal -->
<!-- ============== -->
<div 
  class="modal fade" 
  id="editModal" 
  tabindex="-1" 
  aria-labelledby="editModalLabel" 
  aria-hidden="true"
>
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <form action="" method="post" onsubmit="tinymce.triggerSave();">
        <div class="modal-header">
          <h5 class="modal-title" id="editModalLabel">Edit Lab Test Result</h5>
          <button 
            type="button" 
            class="btn-close" 
            data-bs-dismiss="modal" 
            aria-label="Close"
          ></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="request_id" id="editRequestId" />
          
          <div class="mb-3">
            <label for="editResultContent" class="form-label">Result Content:</label>
            <textarea 
              class="form-control" 
              name="result_content" 
              id="editResultContent"
              rows="10"
            ></textarea>
          </div>
          <div class="mb-3">
            <label for="editStatus" class="form-label">Status:</label>
            <select name="status" id="editStatus" class="form-select">
              <option value="Pending">Pending</option>
              <option value="In Progress">In Progress</option>
              <option value="Completed">Completed</option>
            </select>
          </div>
        </div>
        <div class="modal-footer">
          <button 
            type="button" 
            class="btn btn-secondary" 
            data-bs-dismiss="modal"
          >
            Cancel
          </button>
          <button 
            type="submit" 
            name="save_result" 
            class="btn btn-primary"
          >
            Save
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Bootstrap 5 JS -->
<script 
  src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js">
</script>

<script>
// Initialize TinyMCE
tinymce.init({
  selector: '#editResultContent',
  height: 400,
  menubar: false,
  plugins: 'advlist autolink lists link image preview anchor ' +
           'searchreplace visualblocks code fullscreen ' +
           'insertdatetime media table paste code help wordcount',
  toolbar: 'undo redo | formatselect | bold italic backcolor | ' +
           'alignleft aligncenter alignright alignjustify | ' +
           'bullist numlist outdent indent | removeformat | help',
});

// Grab data from button and open modal with content
function openEditModalFromButton(btn) {
  // Get attributes
  var requestId = btn.getAttribute('data-request-id');
  var status = btn.getAttribute('data-status') || 'Pending';
  var base64Content = btn.getAttribute('data-content') || '';

  // Put them into the form
  document.getElementById('editRequestId').value = requestId;
  document.getElementById('editStatus').value = status;

  // Decode base64
  var decoded = atob(base64Content);
  // Then load into TinyMCE
  tinymce.get('editResultContent').setContent(decoded);
}
</script>

</body>
</html>
