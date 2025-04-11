<?php
session_start();
require_once '../init.php';
require_once '../config.php';
require_once '../access_control.php';
require_once '../sidebar.php';

// 1) Check login
if (!isset($_SESSION['username'])) {
    header("Location: ../login/login.php");
    exit();
}

$username  = $_SESSION['username'];
$usergroup = $_SESSION['usergroup'] ?? '';

// --- Helper functions ---
function generateMeetingID($length = 16) {
    return bin2hex(random_bytes($length / 2));
}
function generatePassword($length = 8) {
    $chars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $chars[rand(0, strlen($chars) - 1)];
    }
    return $randomString;
}

// Show small 5-second alerts:
$alert_script = <<<JS
    <script>
    setTimeout(() => {
      let alertBox = document.getElementById('alertMsg');
      if(alertBox){ alertBox.style.display = 'none'; }
    }, 5000);
    </script>
JS;

$success_message = '';
$error_message   = '';


// 2) Handle POST (create/edit/delete)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // -- CREATE
    if (isset($_POST['action']) && $_POST['action'] === 'create') {
        $meeting_id      = $_POST['meeting_id']      ?? '';
        $password        = $_POST['password']        ?? '';
        $patient_id      = $_POST['patient_id']      ?? '';
        $patient_name    = $_POST['patient_name']    ?? '';
        $patient_dob     = $_POST['patient_dob']     ?? '';
        $reason          = $_POST['reason']          ?? '';
        $department      = $_POST['department']      ?? ''; // primary department
        // e.g. "Doctor", "Nurse", "Lab Scientist"

        // Possibly multiple granted dept checkboxes (for create):
        $granted_dept_arr = isset($_POST['granted_departments']) 
            ? $_POST['granted_departments'] 
            : [];
        // Convert array to comma-separated string
        $granted_departments = implode(',', $granted_dept_arr);

        $assigned_staff  = $_POST['assigned_staff']  ?? '';
        $expiration_date = $_POST['expiration_date'] ?? '';
        $expiration_time = $_POST['expiration_time'] ?? '';

        // Combine date/time
        $expiration = '';
        if ($expiration_date && $expiration_time) {
            $expiration = $expiration_date . ' ' . $expiration_time . ':00';
        }

        if ($meeting_id && $password && $patient_id && $patient_name && $patient_dob 
            && $reason && $department && $expiration
        ) {
            $sql = "INSERT INTO meetings
              (meeting_id, password, created_by, patient_id, patient_name, patient_dob, 
               reason, department, granted_departments, assigned_staff, expiration, status)
              VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Scheduled')";
            $stmt = $conn->prepare($sql);
            if (!$stmt) {
                $error_message = "Prepare failed: " . $conn->error;
            } else {
                $stmt->bind_param(
                    'sssisssssss',
                    $meeting_id,
                    $password,
                    $username,
                    $patient_id,
                    $patient_name,
                    $patient_dob,
                    $reason,
                    $department,
                    $granted_departments,
                    $assigned_staff,
                    $expiration
                );
                if ($stmt->execute()) {
                    $success_message = "Session created successfully!";
                } else {
                    $error_message = "Failed to create meeting: " . $stmt->error;
                }
                $stmt->close();
            }
        } else {
            $error_message = "Please fill all required fields for CREATE.";
        }
    }

    // -- EDIT (Update)
    if (isset($_POST['action']) && $_POST['action'] === 'edit') {
        $meeting_id        = $_POST['edit_meeting_id']     ?? '';
        $password          = $_POST['edit_password']       ?? '';
        $patient_id        = $_POST['edit_patient_id']     ?? '';
        $patient_name      = $_POST['edit_patient_name']   ?? '';
        $patient_dob       = $_POST['edit_patient_dob']    ?? '';
        $reason            = $_POST['edit_reason']         ?? '';
        $department        = $_POST['edit_department']     ?? '';  // primary dept
        $assigned_staff    = $_POST['edit_assigned_staff'] ?? '';
        $expiration_date   = $_POST['edit_expiration_date']?? '';
        $expiration_time   = $_POST['edit_expiration_time']?? '';

        // "Grant other department" in edit form
        $edit_granted_arr  = isset($_POST['edit_granted_departments'])
            ? $_POST['edit_granted_departments']
            : [];
        $granted_departments = implode(',', $edit_granted_arr);

        // combine date/time
        $expiration = '';
        if ($expiration_date && $expiration_time) {
            $expiration = $expiration_date . ' ' . $expiration_time . ':00';
        }

        if ($meeting_id && $password && $patient_id && $patient_name && $patient_dob 
            && $reason && $department && $expiration
        ) {
            $sql = "UPDATE meetings
                    SET 
                      password=?,
                      patient_id=?,
                      patient_name=?,
                      patient_dob=?,
                      reason=?,
                      department=?,
                      granted_departments=?,
                      assigned_staff=?,
                      expiration=?
                    WHERE meeting_id=?";
            $stmt = $conn->prepare($sql);
            if (!$stmt) {
                $error_message = "Update prepare failed: " . $conn->error;
            } else {
                $stmt->bind_param(
                    'sissssssss',
                    $password,
                    $patient_id,
                    $patient_name,
                    $patient_dob,
                    $reason,
                    $department,
                    $granted_departments,
                    $assigned_staff,
                    $expiration,
                    $meeting_id
                );
                if ($stmt->execute()) {
                    $success_message = "Session updated successfully!";
                } else {
                    $error_message = "Failed to update session: " . $stmt->error;
                }
                $stmt->close();
            }
        } else {
            $error_message = "Please fill all required fields for EDIT.";
        }
    }

    // -- DELETE
    if (isset($_POST['action']) && $_POST['action'] === 'delete') {
        $del_meeting_id = $_POST['del_meeting_id'] ?? '';
        if ($del_meeting_id) {
            $delsql = "DELETE FROM meetings WHERE meeting_id=?";
            $delstmt = $conn->prepare($delsql);
            if ($delstmt) {
                $delstmt->bind_param('s', $del_meeting_id);
                if ($delstmt->execute()) {
                    $success_message = "Session deleted successfully!";
                } else {
                    $error_message = "Failed to delete session: " . $delstmt->error;
                }
                $delstmt->close();
            } else {
                $error_message = "Delete prepare failed: " . $conn->error;
            }
        } else {
            $error_message = "No meeting ID to delete.";
        }
    }
}

// 3) Generate defaults for create
$generated_meeting_id = generateMeetingID();
$generated_password   = generatePassword();

// 4) Fetch sessions
$sessions = [];
$sql2 = "SELECT 
   meeting_id,
   password,
   created_by,
   patient_id,
   patient_name,
   patient_dob,
   reason,
   department,
   granted_departments,
   assigned_staff,
   expiration,
   IFNULL(status, 'Scheduled') AS status
  FROM meetings
  ORDER BY expiration DESC";
$res2 = $conn->query($sql2);
if ($res2 && $res2->num_rows > 0) {
    while ($row = $res2->fetch_assoc()) {
        $sessions[] = $row;
    }
}

// For department-based staff checkboxes (example):
// You can store an array per department or do an AJAX call. 
// We'll do a minimal example for 'Doctor', 'Nurse', 'Lab Scientist'.
$staffOptions = [
    'Doctor'        => ['Dr. House', 'Dr. Who', 'Dr. Strange'],
    'Nurse'         => ['Nurse Joy', 'Nurse Ratched'],
    'Lab Scientist' => ['Dr. Banner', 'Dr. Oppenheimer'],
];

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Create Session</title>
  <link
    rel="stylesheet"
    href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css"
  />
  <style>
    body {
      background-color: #f8f9fa;
    }
    .expired {
      background-color: #ffecec;
    }
    .expired td {
      color: #999;
    }
  </style>
</head>
<body>

<?php 
// If there's a success or error message, show a small JS alert that disappears in 5s
if ($success_message) {
    echo "<div id='alertMsg' style='background:#d4edda;padding:8px;color:#155724;margin:10px;'>"
        . htmlspecialchars($success_message) . "</div>";
    echo $alert_script; 
}
if ($error_message) {
    echo "<div id='alertMsg' style='background:#f8d7da;padding:8px;color:#721c24;margin:10px;'>"
        . htmlspecialchars($error_message) . "</div>";
    echo $alert_script;
}
?>

<div class="container py-4">
  
  <!-- TOP: CREATE FORM -->
  <div class="card mb-4">
    <div class="card-header">Create a New Session</div>
    <div class="card-body">
      <form method="post" action="create_session.php">
        <input type="hidden" name="action" value="create">

        <div class="row g-3">
          <div class="col-md-6">
            <label for="meeting_id" class="form-label">Meeting ID</label>
            <input 
              type="text"
              class="form-control"
              id="meeting_id"
              name="meeting_id"
              value="<?= htmlspecialchars($generated_meeting_id); ?>"
              readonly required
            >
          </div>
          <div class="col-md-6">
            <label for="password" class="form-label">Password</label>
            <input
              type="text"
              class="form-control"
              id="password"
              name="password"
              value="<?= htmlspecialchars($generated_password); ?>"
              readonly required
            >
          </div>
        </div><!-- row -->

        <div class="row g-3 mt-3">
          <div class="col-md-6">
            <label for="patient_name" class="form-label">Patient Name</label>
            <input 
              type="text"
              class="form-control"
              id="patient_name"
              name="patient_name"
              required
            >
          </div>
          <div class="col-md-6">
            <label for="patient_id" class="form-label">Patient ID</label>
            <input
              type="text"
              class="form-control"
              id="patient_id"
              name="patient_id"
              placeholder="Auto-filled..."
              readonly required
            >
          </div>
        </div><!-- row -->

        <div class="row g-3 mt-3">
          <div class="col-md-6">
            <label for="patient_dob" class="form-label">Patient DOB</label>
            <input
              type="text"
              class="form-control"
              id="patient_dob"
              name="patient_dob"
              placeholder="Auto-filled..."
              readonly required
            >
          </div>
          <div class="col-md-6">
            <label for="reason" class="form-label">Session Reason</label>
            <input
              type="text"
              class="form-control"
              id="reason"
              name="reason"
              required
            >
          </div>
        </div><!-- row -->

        <div class="row g-3 mt-3">
          <div class="col-md-6">
            <label for="department" class="form-label">Primary Department</label>
            <select
              id="department"
              name="department"
              class="form-select"
              required
            >
              <option value="Doctor">Doctor</option>
              <option value="Nurse">Nurse</option>
              <option value="Lab Scientist">Lab Scientist</option>
            </select>

            <!-- Staff assignment checkboxes for the chosen department -->
            <div id="staffCheckContainer" class="mt-2" style="display:none;">
              <label class="form-label">Assign Staff</label>
              <div id="staffCheckContent"></div>
              <!-- We'll store them in a hidden input "assigned_staff" as a comma separated list -->
              <input type="hidden" name="assigned_staff" id="assigned_staff" value="">
            </div>
          </div>
          <div class="col-md-6">
            <label class="form-label">Grant Access to Additional Departments</label><br>
            <div class="form-check">
              <input
                class="form-check-input"
                type="checkbox"
                name="granted_departments[]"
                value="Doctor"
                id="grantDoctor"
              >
              <label class="form-check-label" for="grantDoctor">Doctor</label>
            </div>
            <div class="form-check">
              <input
                class="form-check-input"
                type="checkbox"
                name="granted_departments[]"
                value="Nurse"
                id="grantNurse"
              >
              <label class="form-check-label" for="grantNurse">Nurse</label>
            </div>
            <div class="form-check">
              <input
                class="form-check-input"
                type="checkbox"
                name="granted_departments[]"
                value="Lab Scientist"
                id="grantLab"
              >
              <label class="form-check-label" for="grantLab">Lab Scientist</label>
            </div>
          </div>
        </div><!-- row -->

        <div class="row g-3 mt-3">
          <div class="col-md-6">
            <label for="expiration_date" class="form-label">Expiration Date</label>
            <input
              type="date"
              class="form-control"
              id="expiration_date"
              name="expiration_date"
              required
            >
          </div>
          <div class="col-md-6">
            <label for="expiration_time" class="form-label">Expiration Time</label>
            <input
              type="time"
              class="form-control"
              id="expiration_time"
              name="expiration_time"
              required
            >
          </div>
        </div><!-- row -->

        <div class="mt-3">
          <button type="submit" class="btn btn-primary">Create Session</button>
        </div>

      </form>
    </div>
  </div><!-- /card -->

  <!-- TABLE OF SESSIONS BELOW -->
  <h2>Existing Sessions</h2>
  <table class="table table-bordered table-striped table-hover">
    <thead class="table-dark">
      <tr>
        <th>Meeting ID</th>
        <th>Patient</th>
        <th>Department(s)</th>
        <th>Expiration</th>
        <th>Status</th>
        <th>Password</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php
      date_default_timezone_set('UTC'); 
      $now = new DateTime();

      foreach ($sessions as $sess):
        $expireDT = new DateTime($sess['expiration']);
        $expired  = ($now > $expireDT);
        $status   = $sess['status'];
        $rowClass = $expired ? 'expired' : '';

        // If expired and still 'Scheduled', mark as 'Expired'
        if ($expired && $status === 'Scheduled') {
            $upd = $conn->prepare("UPDATE meetings SET status='Expired' WHERE meeting_id=?");
            if ($upd) {
                $upd->bind_param('s', $sess['meeting_id']);
                $upd->execute();
                $upd->close();
            }
            $status = 'Expired';
        }

        // Combine the primary dept + granted deps
        $primaryDept = $sess['department'];
        $grantedDepts= $sess['granted_departments'] ?? '';
        $deptArr     = array_filter(array_map('trim', explode(',', $grantedDepts)));
        // final department string:
        $allDepts    = array_merge([$primaryDept], $deptArr);
        $allDeptsStr = implode(', ', $allDepts);

        // Decide if user can JOIN:
        // Admin can always join. Otherwise must be in allDepts array:
        $canJoin = false;
        if ($usergroup === 'Admin') {
            $canJoin = true;
        } else {
            // check if usergroup is in $allDepts
            if (in_array($usergroup, $allDepts)) {
                $canJoin = true;
            }
        }
      ?>
      <tr class="<?= $rowClass; ?>">
        <td><?= htmlspecialchars($sess['meeting_id']); ?></td>
        <td>
          <?= htmlspecialchars($sess['patient_name']); ?>
          <br><small>ID: <?= htmlspecialchars($sess['patient_id']); ?></small>
        </td>
        <td><?= htmlspecialchars($allDeptsStr); ?></td>
        <td><?= htmlspecialchars($sess['expiration']); ?></td>
        <td>
          <?php if ($expired): ?>
            <span class="text-danger fw-bold">Expired</span>
          <?php else: ?>
            <?= htmlspecialchars($status); ?>
          <?php endif; ?>
        </td>
        <td><?= htmlspecialchars($sess['password']); ?></td>
        <td>
          <!-- Only can JOIN if not expired and (admin or in department(s)) -->
          <?php if (!$expired && $canJoin): ?>
            <a href="/my clinic/video_call.php?meeting_id=<?= urlencode($sess['meeting_id']); ?>"
               class="btn btn-success btn-sm mb-1">
              Join
            </a>
          <?php else: ?>
            <button class="btn btn-secondary btn-sm mb-1" disabled>Join</button>
          <?php endif; ?>

          <!-- VIEW button => View modal -->
          <button
            class="btn btn-info btn-sm mb-1"
            data-bs-toggle="modal"
            data-bs-target="#viewSessionModal"
            data-meetingid="<?= htmlspecialchars($sess['meeting_id']); ?>"
            data-password="<?= htmlspecialchars($sess['password']); ?>"
            data-patientid="<?= htmlspecialchars($sess['patient_id']); ?>"
            data-patientname="<?= htmlspecialchars($sess['patient_name']); ?>"
            data-patientdob="<?= htmlspecialchars($sess['patient_dob']); ?>"
            data-reason="<?= htmlspecialchars($sess['reason']); ?>"
            data-alldepts="<?= htmlspecialchars($allDeptsStr); ?>"
            data-assignedstaff="<?= htmlspecialchars($sess['assigned_staff']); ?>"
            data-expiration="<?= htmlspecialchars($sess['expiration']); ?>"
            data-status="<?= htmlspecialchars($status); ?>"
          >
            View
          </button>

          <!-- EDIT button => only Admin or primary dept user can edit -->
          <?php
          $primaryUserCanEdit = ($usergroup === 'Admin' || $usergroup === $primaryDept);
          ?>
          <?php if (!$expired && $primaryUserCanEdit): ?>
            <button
              class="btn btn-warning btn-sm mb-1"
              data-bs-toggle="modal"
              data-bs-target="#editSessionModal"
              data-meetingid="<?= htmlspecialchars($sess['meeting_id']); ?>"
              data-password="<?= htmlspecialchars($sess['password']); ?>"
              data-patientid="<?= htmlspecialchars($sess['patient_id']); ?>"
              data-patientname="<?= htmlspecialchars($sess['patient_name']); ?>"
              data-patientdob="<?= htmlspecialchars($sess['patient_dob']); ?>"
              data-reason="<?= htmlspecialchars($sess['reason']); ?>"
              data-department="<?= htmlspecialchars($primaryDept); ?>"
              data-granted="<?= htmlspecialchars($grantedDepts); ?>"
              data-assignedstaff="<?= htmlspecialchars($sess['assigned_staff']); ?>"
              data-expiration="<?= htmlspecialchars($sess['expiration']); ?>"
            >
              Edit
            </button>
          <?php else: ?>
            <button class="btn btn-secondary btn-sm mb-1" disabled>Edit</button>
          <?php endif; ?>

          <!-- DELETE => only admin or primary dept user can delete -->
          <?php if ($primaryUserCanEdit): ?>
            <form method="post" action="create_session.php" style="display:inline;">
              <input type="hidden" name="action" value="delete">
              <input type="hidden" name="del_meeting_id" value="<?= htmlspecialchars($sess['meeting_id']); ?>">
              <button 
                type="submit"
                class="btn btn-danger btn-sm mb-1"
                onclick="return confirm('Are you sure you want to delete this session?');"
              >
                Delete
              </button>
            </form>
          <?php endif; ?>
        </td>
      </tr>
      <?php endforeach; ?>
      <?php if (empty($sessions)): ?>
      <tr>
        <td colspan="7" class="text-center">No sessions found.</td>
      </tr>
      <?php endif; ?>
    </tbody>
  </table>
</div><!-- /container -->

<!-- VIEW MODAL -->
<div class="modal fade" id="viewSessionModal" tabindex="-1" aria-labelledby="viewSessionModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="viewSessionModalLabel">Session Details</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <!-- Read-Only Data here -->
        <div class="mb-2"><strong>Meeting ID:</strong> <span id="view_meeting_id"></span></div>
        <div class="mb-2"><strong>Password:</strong> <span id="view_password"></span></div>
        <div class="mb-2"><strong>Patient ID:</strong> <span id="view_patient_id"></span></div>
        <div class="mb-2"><strong>Patient Name:</strong> <span id="view_patient_name"></span></div>
        <div class="mb-2"><strong>Patient DOB:</strong> <span id="view_patient_dob"></span></div>
        <div class="mb-2"><strong>Reason:</strong> <span id="view_reason"></span></div>
        <div class="mb-2"><strong>Department(s):</strong> <span id="view_alldepts"></span></div>
        <div class="mb-2"><strong>Assigned Staff:</strong> <span id="view_assigned_staff"></span></div>
        <div class="mb-2"><strong>Expiration:</strong> <span id="view_expiration"></span></div>
        <div class="mb-2"><strong>Status:</strong> <span id="view_status"></span></div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<!-- EDIT MODAL -->
<div class="modal fade" id="editSessionModal" tabindex="-1" aria-labelledby="editSessionModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <form method="post" action="create_session.php">
        <input type="hidden" name="action" value="edit">
        <input type="hidden" id="edit_meeting_id" name="edit_meeting_id">
        
        <div class="modal-header">
          <h5 class="modal-title" id="editSessionModalLabel">Edit Session</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        
        <div class="modal-body">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label">Password</label>
              <input type="text" class="form-control" id="edit_password" name="edit_password" required>
            </div>
            <div class="col-md-6">
              <label class="form-label">Patient ID</label>
              <input type="text" class="form-control" id="edit_patient_id" name="edit_patient_id" readonly required>
            </div>
          </div><!-- row -->

          <div class="row g-3 mt-3">
            <div class="col-md-6">
              <label class="form-label">Patient Name</label>
              <input type="text" class="form-control" id="edit_patient_name" name="edit_patient_name" required>
            </div>
            <div class="col-md-6">
              <label class="form-label">Patient DOB</label>
              <input type="text" class="form-control" id="edit_patient_dob" name="edit_patient_dob" readonly required>
            </div>
          </div><!-- row -->

          <div class="row g-3 mt-3">
            <div class="col-md-6">
              <label class="form-label">Reason</label>
              <input type="text" class="form-control" id="edit_reason" name="edit_reason" required>
            </div>
            <div class="col-md-6">
              <label class="form-label">Primary Department</label>
              <select class="form-select" id="edit_department" name="edit_department" required>
                <option value="Doctor">Doctor</option>
                <option value="Nurse">Nurse</option>
                <option value="Lab Scientist">Lab Scientist</option>
              </select>
            </div>
          </div><!-- row -->

          <!-- Grant other departments in edit -->
          <div class="row g-3 mt-3">
            <div class="col-md-6">
              <label class="form-label">Grant Other Departments</label>
              <div class="form-check">
                <input class="form-check-input" type="checkbox" id="edit_grantDoctor" name="edit_granted_departments[]" value="Doctor">
                <label class="form-check-label" for="edit_grantDoctor">Doctor</label>
              </div>
              <div class="form-check">
                <input class="form-check-input" type="checkbox" id="edit_grantNurse" name="edit_granted_departments[]" value="Nurse">
                <label class="form-check-label" for="edit_grantNurse">Nurse</label>
              </div>
              <div class="form-check">
                <input class="form-check-input" type="checkbox" id="edit_grantLab" name="edit_granted_departments[]" value="Lab Scientist">
                <label class="form-check-label" for="edit_grantLab">Lab Scientist</label>
              </div>
            </div>

            <div class="col-md-6">
              <label class="form-label">Assigned Staff</label>
              <input type="text" class="form-control" id="edit_assigned_staff" name="edit_assigned_staff" required>
            </div>
          </div><!-- row -->

          <div class="row g-3 mt-3">
            <div class="col-md-6">
              <label class="form-label">Expiration Date</label>
              <input type="date" class="form-control" id="edit_expiration_date" name="edit_expiration_date" required>
            </div>
            <div class="col-md-6">
              <label class="form-label">Expiration Time</label>
              <input type="time" class="form-control" id="edit_expiration_time" name="edit_expiration_time" required>
            </div>
          </div><!-- row -->

        </div><!-- modal-body -->
        
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
          <button type="submit" class="btn btn-primary">Save Changes</button>
        </div>
      
      </form>
    </div>
  </div>
</div><!-- /editSessionModal -->


<!-- Bootstrap + jQuery -->
<script
  src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js">
</script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
// =============== VIEW MODAL ===============
const viewModal = document.getElementById('viewSessionModal');
if (viewModal) {
  viewModal.addEventListener('show.bs.modal', event => {
    const button = event.relatedTarget;
    // get data from data-* attributes
    document.getElementById('view_meeting_id').textContent     = button.getAttribute('data-meetingid');
    document.getElementById('view_password').textContent       = button.getAttribute('data-password');
    document.getElementById('view_patient_id').textContent     = button.getAttribute('data-patientid');
    document.getElementById('view_patient_name').textContent   = button.getAttribute('data-patientname');
    document.getElementById('view_patient_dob').textContent    = button.getAttribute('data-patientdob');
    document.getElementById('view_reason').textContent         = button.getAttribute('data-reason');
    document.getElementById('view_alldepts').textContent       = button.getAttribute('data-alldepts');
    document.getElementById('view_assigned_staff').textContent = button.getAttribute('data-assignedstaff');
    document.getElementById('view_expiration').textContent     = button.getAttribute('data-expiration');
    document.getElementById('view_status').textContent         = button.getAttribute('data-status');
  });
}

// =============== EDIT MODAL ===============
const editModal = document.getElementById('editSessionModal');
if (editModal) {
  editModal.addEventListener('show.bs.modal', event => {
    const button = event.relatedTarget;
    // parse data- attributes
    const meetingId    = button.getAttribute('data-meetingid');
    const password     = button.getAttribute('data-password');
    const patientId    = button.getAttribute('data-patientid');
    const patientName  = button.getAttribute('data-patientname');
    const patientDob   = button.getAttribute('data-patientdob');
    const reason       = button.getAttribute('data-reason');
    const department   = button.getAttribute('data-department');
    const granted      = button.getAttribute('data-granted'); // comma-separated
    const assignedStaff= button.getAttribute('data-assignedstaff');
    const expiration   = button.getAttribute('data-expiration');

    // fill fields
    document.getElementById('edit_meeting_id').value   = meetingId;
    document.getElementById('edit_password').value     = password;
    document.getElementById('edit_patient_id').value   = patientId;
    document.getElementById('edit_patient_name').value = patientName;
    document.getElementById('edit_patient_dob').value  = patientDob;
    document.getElementById('edit_reason').value       = reason;
    document.getElementById('edit_department').value   = department;
    document.getElementById('edit_assigned_staff').value = assignedStaff;

    // parse expiration "YYYY-MM-DD HH:MM:SS"
    if (expiration) {
      let parts = expiration.split(' ');
      if (parts.length === 2) {
        document.getElementById('edit_expiration_date').value = parts[0];
        document.getElementById('edit_expiration_time').value = parts[1].substring(0,5);
      }
    }

    // handle granted checkboxes
    // e.g. "Nurse,Lab Scientist"
    const grantedArr = granted.split(',');
    // clear them first:
    document.getElementById('edit_grantDoctor').checked = false;
    document.getElementById('edit_grantNurse').checked  = false;
    document.getElementById('edit_grantLab').checked    = false;

    grantedArr.forEach(d => {
      let trimmed = d.trim();
      if (trimmed === 'Doctor') {
        document.getElementById('edit_grantDoctor').checked = true;
      } else if (trimmed === 'Nurse') {
        document.getElementById('edit_grantNurse').checked  = true;
      } else if (trimmed === 'Lab Scientist') {
        document.getElementById('edit_grantLab').checked    = true;
      }
    });
  });
}

// =============== AUTOCOMPLETE EXAMPLES ===============
$('#patient_name').autocomplete({
  source: function(request, response) {
    $.ajax({
      url: 'autocomplete.php',
      dataType: 'json',
      data: {
        term: request.term,
        type: 'patient'
      },
      success: function(data) {
        response(data);
      }
    });
  },
  select: function(event, ui) {
    $('#patient_name').val(ui.item.value);
    $.ajax({
      url: 'autocomplete.php',
      dataType: 'json',
      data: {
        patient_name: ui.item.value
      },
      success: function(data) {
        $('#patient_id').val(data.patient_id);
        $('#patient_dob').val(data.dob);
      }
    });
    return false;
  }
});

// Example staff assignment: show checkboxes for that department
const staffData = {
  'Doctor':        ['Dr. House', 'Dr. Who', 'Dr. Strange'],
  'Nurse':         ['Nurse Joy', 'Nurse Ratched'],
  'Lab Scientist': ['Dr. Banner', 'Dr. Oppenheimer']
};

$('#department').on('change', function() {
  let dept = $(this).val();
  let container = $('#staffCheckContainer');
  let content   = $('#staffCheckContent');
  let hidden    = $('#assigned_staff');
  
  if (!dept) {
    container.hide();
    content.empty();
    hidden.val('');
    return;
  }
  
  // Show container
  container.show();
  content.empty();
  // create checkboxes
  let staffArr = staffData[dept] || [];
  staffArr.forEach(person => {
    let id = 'staffCheck_'+person.replace(/\s+/g, '_');
    content.append(`
      <div class="form-check">
        <input 
          class="form-check-input staff-assign" 
          type="checkbox" 
          id="${id}" 
          value="${person}"
        >
        <label class="form-check-label" for="${id}">${person}</label>
      </div>
    `);
  });

  // every time user checks/unchecks => update hidden input
  $('.staff-assign').on('change', function() {
    let selected = [];
    $('.staff-assign:checked').each(function(){
      selected.push($(this).val());
    });
    hidden.val(selected.join(', '));
  });
});

</script>
</body>
</html>
