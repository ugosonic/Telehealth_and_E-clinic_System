<?php
session_start();
ob_start();

// Include your database/init config (must define $conn)
require_once '../init.php';
require_once '../config.php';

// (Optional) Include your sidebar or navigation
require_once '../sidebar.php';

// Ensure user is logged in as 'Patient'
if (!isset($_SESSION['patient_id']) || $_SESSION['usergroup'] !== 'Patient') {
    header("Location: /my_clinic/unauthorised.php");
    exit();
}

// The current patient's ID from session
$patientUsername = $_SESSION['patient_id'];
$error_message   = '';

// ------------------------------------
// 1) Handle POST actions: Delete or Join
// ------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // A) Delete Action
    if (isset($_POST['action']) && $_POST['action'] === 'delete') {
        $meetingIDToDelete = $_POST['meeting_id'] ?? '';
        if (!empty($meetingIDToDelete)) {
            // Delete the meeting if it belongs to the current patient
            $delStmt = $conn->prepare("
                DELETE FROM meetings
                WHERE meeting_id = ?
                  AND patient_id = ?
            ");
            $delStmt->bind_param('ss', $meetingIDToDelete, $patientUsername);
            $delStmt->execute();
            $delStmt->close();

            // Refresh page so the table updates
            header("Location: " . $_SERVER['PHP_SELF']);
            exit();
        }
    }

    // B) Join Meeting Action
    if (isset($_POST['join_meeting'])) {
        if (!empty($_POST['meeting_id']) && !empty($_POST['password'])) {
            $meeting_id = trim($_POST['meeting_id']);
            $password   = trim($_POST['password']);

            // Fetch the meeting, confirm it belongs to this patient & not expired
            $stmt = $conn->prepare("
                SELECT *
                FROM meetings
                WHERE meeting_id = ?
                  AND patient_id = ?
                  AND expiration > NOW()
                LIMIT 1
            ");
            $stmt->bind_param("ss", $meeting_id, $patientUsername);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result && $result->num_rows === 1) {
                $meeting = $result->fetch_assoc();
                $stmt->close();

                // If your 'password' column is hashed:
                if (password_verify($password, $meeting['password'])) {
                    // Correct => redirect to video call
                    header("Location: ../video_call.php?meeting_id=" . urlencode($meeting_id));
                    exit();
                } else {
                    $error_message = "Incorrect password!";
                }

                // If your 'password' column is plain text, do:
                // if ($password === $meeting['password']) { ... }

            } else {
                $error_message = "Invalid or expired meeting, or not authorized!";
                if ($stmt) { $stmt->close(); }
            }
        } else {
            $error_message = "Please enter both the Meeting ID and Password.";
        }
    }
}

// ------------------------------------
// 2) Fetch All Meetings for This Patient
// ------------------------------------
$stmt = $conn->prepare("
    SELECT *
    FROM meetings
    WHERE patient_id = ?
    ORDER BY created_at DESC
");
$stmt->bind_param("s", $patientUsername);
$stmt->execute();
$result   = $stmt->get_result();
$meetings = [];

$current_time = new DateTime();

while ($row = $result->fetch_assoc()) {
    $expiration = new DateTime($row['expiration'] ?? '1970-01-01');

    // If now > expiration and status not 'Declined', mark as 'Expired'
    if (!empty($row['status']) && $row['status'] !== 'Declined' && $current_time > $expiration) {
        $row['status'] = 'Expired';
    }
    $meetings[] = $row;
}
$stmt->close();
ob_end_flush();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Join Session &amp; View Meetings</title>
  <!-- Bootstrap CSS -->
  <link
    rel="stylesheet"
    href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css"
  />
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <style>
    .toggle-password {
      cursor: pointer;
      position: absolute;
      right: 1.25rem;
      top: 2.9rem; /* adjust for your form layout */
      color: #007bff;
      font-size: 0.9rem;
    }
    .error-message {
      color: #d9534f;
      font-weight: bold;
      margin-top: 10px;
    }
    .table-responsive {
      margin-top: 1rem;
    }
  </style>
</head>
<body class="bg-light">
<div class="container py-4">
  <h1 class="mb-4">Join a Session</h1>

  <!-- Join Meeting Form in a Bootstrap card -->
  <div class="card mb-4">
    <div class="card-header bg-primary text-white">Meeting Access</div>
    <div class="card-body">
      <?php if (!empty($error_message)): ?>
        <div class="error-message"><?= htmlspecialchars($error_message) ?></div>
      <?php endif; ?>

      <form action="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>" method="post" class="row g-3">
        <div class="col-md-6 position-relative">
          <label for="meeting_id" class="form-label">Meeting ID</label>
          <input
            type="text"
            id="meeting_id"
            name="meeting_id"
            class="form-control"
            required
          />
        </div>
        <div class="col-md-6 position-relative">
          <label for="passwordField" class="form-label">Password</label>
          <input
            type="password"
            id="passwordField"
            name="password"
            class="form-control"
            required
          />
          <span
            id="togglePassword"
            class="toggle-password"
            onclick="togglePasswordVisibility()"
          >
            Show
          </span>
        </div>
        <div class="col-12 mt-2">
          <button type="submit" name="join_meeting" class="btn btn-success px-4">
            Join Meeting
          </button>
        </div>
      </form>
    </div>
  </div>

  <h2 class="mb-3">Your Sessions</h2>
  <div class="table-responsive">
    <table class="table table-bordered align-middle">
      <thead class="table-light">
        <tr>
          <th scope="col">Meeting ID</th>
          <th scope="col">Password</th>
          <th scope="col">Patient Name</th>
          <th scope="col">DOB</th>
          <th scope="col">Reason</th>
          <th scope="col">Staff</th>
          <th scope="col">Dept</th>
          <th scope="col">Created</th>
          <th scope="col">Expires</th>
          <th scope="col">Status</th>
          <th scope="col" style="min-width:120px;">Actions</th>
        </tr>
      </thead>
      <tbody>
      <?php if (count($meetings) === 0): ?>
        <tr>
          <td colspan="11" class="text-center">No sessions found.</td>
        </tr>
      <?php else: ?>
        <?php
        $now = new DateTime();
        foreach ($meetings as $mt):
          $m_id        = $mt['meeting_id'];
          $status      = $mt['status'] ?? '';
          $expiration  = new DateTime($mt['expiration'] ?? '1970-01-01');
          $isDeclined  = ($status === 'Declined');
          $isExpired   = ($status === 'Expired');

          // 1 hour before the expiration time
          $startAvailability = (clone $expiration)->modify('-1 hour');
          // End of same day
          $endOfDay = (clone $expiration)->setTime(23,59,59);

          // Decide if "Join" is shown
          $canJoin = false;
          if (!$isDeclined && !$isExpired) {
            if ($now >= $startAvailability && $now <= $endOfDay) {
              $canJoin = true;
            }
          }
        ?>
          <tr>
            <td><?= htmlspecialchars($m_id) ?></td>
            <td><?= str_repeat('*', 8) ?></td>
            <td><?= htmlspecialchars($mt['patient_name'] ?? '') ?></td>
            <td><?= htmlspecialchars($mt['patient_dob']  ?? '') ?></td>
            <td><?= htmlspecialchars($mt['reason']       ?? '') ?></td>
            <td><?= htmlspecialchars($mt['assigned_staff'] ?? '') ?></td>
            <td><?= htmlspecialchars($mt['department']     ?? '') ?></td>
            <td><?= htmlspecialchars($mt['created_at']     ?? '') ?></td>
            <td><?= htmlspecialchars($mt['expiration']     ?? '') ?></td>
            <td><?= htmlspecialchars($status) ?></td>
            <td>
              <div class="d-flex flex-wrap gap-1">
                <!-- Delete button -->
                <form
                  method="post"
                  action="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>"
                  onsubmit="return confirm('Are you sure you want to delete this meeting?');"
                >
                  <input type="hidden" name="meeting_id" value="<?= htmlspecialchars($m_id) ?>">
                  <button
                    type="submit"
                    class="btn btn-sm btn-outline-danger"
                    name="action"
                    value="delete"
                  >
                    Delete
                  </button>
                </form>

                <!-- Join button or Unavailable -->
                <?php if ($canJoin): ?>
                  <a
                    href="../video_call.php?meeting_id=<?= urlencode($m_id) ?>"
                    class="btn btn-sm btn-primary"
                  >
                    Join
                  </a>
                <?php else: ?>
                  <span class="text-muted">Unavailable</span>
                <?php endif; ?>
              </div>
            </td>
          </tr>
        <?php endforeach; ?>
      <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- Bootstrap JS -->
<script
  src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js">
</script>

<script>
// Show/Hide the password in the Join form
function togglePasswordVisibility() {
  const pwdField = document.getElementById('passwordField');
  const toggle   = document.getElementById('togglePassword');
  if (pwdField.type === 'password') {
    pwdField.type = 'text';
    toggle.textContent = 'Hide';
  } else {
    pwdField.type = 'password';
    toggle.textContent = 'Show';
  }
}
</script>
</body>
</html>
