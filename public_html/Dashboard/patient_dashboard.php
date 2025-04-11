<?php
session_start();
ob_start();

// 1) DB + Config
require_once '../init.php';
require_once '../config.php';

// 2) Must be logged in as a Patient
if (!isset($_SESSION['username']) || ($_SESSION['usergroup'] ?? '') !== 'Patient') {
    header("Location: ../unauthorised.php");
    exit();
}
$username = $_SESSION['username'];

// 3) Fetch patient's first/last name
$stmt = $conn->prepare("SELECT first_name, surname, email FROM patient_db WHERE username=? LIMIT 1");
$stmt->bind_param("s", $username);
$stmt->execute();
$res = $stmt->get_result();
$patientFirstName = 'Unknown';
$patientLastName  = 'Patient';
$patientEmail     = '';
if ($res && $res->num_rows > 0) {
    $row = $res->fetch_assoc();
    $patientFirstName = $row['first_name'] ?? 'Unknown';
    $patientLastName  = $row['surname']    ?? 'Patient';
    $patientEmail     = $row['email']      ?? '';
}
$stmt->close();

// 4) Include function file
require_once 'functions.php';

// 5) Check POST for "Reschedule" or "Cancel"
$rescheduleSuccess = '';
$rescheduleError   = '';
$cancelSuccess     = '';
$cancelError       = '';

// 5A) If user submitted a reschedule
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'reschedule') {
        $appointmentId = intval($_POST['appointment_id'] ?? 0);
        $newDate       = $_POST['new_date'] ?? '';
        $newTime       = $_POST['new_time'] ?? '';
        if ($appointmentId > 0 && $newDate && $newTime) {
            if (rescheduleAppointment($conn, $appointmentId, $newDate, $newTime)) {
                $rescheduleSuccess = "Appointment #{$appointmentId} has been rescheduled to {$newDate} {$newTime}.";
            } else {
                $rescheduleError = "Failed to reschedule appointment #{$appointmentId}.";
            }
        } else {
            $rescheduleError = "Incomplete reschedule data.";
        }
    }
    // 5B) If user wants to cancel
    if (isset($_POST['action']) && $_POST['action'] === 'cancel') {
        $appointmentId = intval($_POST['appointment_id'] ?? 0);
        $reason        = $_POST['cancel_reason'] ?? '';
        if ($appointmentId > 0) {
            if (cancelAppointment($conn, $appointmentId, $reason)) {
                $cancelSuccess = "Appointment #{$appointmentId} has been cancelled.";
            } else {
                $cancelError = "Failed to cancel appointment #{$appointmentId}.";
            }
        } else {
            $cancelError = "No valid appointment ID to cancel.";
        }
    }
}

// 6) Build $sqlCondition for appointments
$sqlCondition     = getSqlCondition('Patient', $username, '');
$results_per_page = 10;
$current_page     = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$start_from       = ($current_page - 1) * $results_per_page;
$today            = date('Y-m-d');

// 7) Fetch from getAppointments
$appointments = getAppointments($conn, $sqlCondition, $today, $start_from, $results_per_page);
$total_records= getTotalAppointments($conn, $sqlCondition);
$total_pages  = ceil($total_records / $results_per_page);

// next appointment
$nextAppointment = null;
if (!empty($appointments['upcoming'])) {
    $nextAppointment = $appointments['upcoming'][0];
}

// 8) Also retrieve next VIDEO CONSULTATION from `meetings` table
//    Using the function we added in functions.php
$videoConsultInfo = getNextVideoConsultation($conn, $username); // returns a row or null
$videoMsg         = '';
$videoJoin        = false;
if ($videoConsultInfo) {
    // Check if today is the date portion of expiration
    $expDT   = new DateTime($videoConsultInfo['expiration']);
    $nowDT   = new DateTime();
    $todayDT = $nowDT->format('Y-m-d');
    $expDate = $expDT->format('Y-m-d');
    $expTime = $expDT->format('g:i A, F j Y');

    // If it's already expired -> skip
    if ($nowDT > $expDT) {
        // no upcoming video consult
        $videoConsultInfo = null;
    } else {
        // check if it's exactly today
        if ($expDate === $todayDT) {
            $videoMsg  = "You have a Video Consultation today at {$expTime}.";
            $videoJoin = true; // show button to join
        } else {
            $videoMsg  = "Your next Video Consultation is on {$expTime}.";
        }
    }
}

ob_end_flush();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Patient Dashboard</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <!-- Bootstrap 5 CSS -->
  <link
    rel="stylesheet"
    href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css"
  />
  <link
    rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css"
    crossorigin="anonymous"
  />
  <style>
    body {
      background-color: #f8f9fa;
    }
    .navbar-brand {
      font-weight: 600;
      font-size: 1.2rem;
    }
    .dashboard-card {
      background: #fff;
      border: none;
      border-radius: 0.5rem;
      box-shadow: 0 2px 4px rgba(0,0,0,0.1);
      height: 100%;
      display: flex;
      flex-direction: column;
      padding: 1.25rem;
      margin-bottom: 1.25rem;
    }
    .dashboard-card h5 {
      margin-bottom: 1rem;
      font-weight: 600;
    }
    .dashboard-card i {
      font-size: 2rem;
      margin-bottom: 0.75rem;
      color: #0d6efd;
    }
    .small-card-body {
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
    }
  </style>
</head>
<body>

<!-- Top Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
  <div class="container-fluid">
    <!-- Brand -->
    <a class="navbar-brand" href="#">
      <i class="fas fa-stethoscope me-2"></i> My Clinic
    </a>
    <!-- Toggler -->
    <button
      class="navbar-toggler"
      type="button"
      data-bs-toggle="collapse"
      data-bs-target="#topNav"
      aria-controls="topNav"
      aria-expanded="false"
      aria-label="Toggle navigation"
    >
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="topNav">
      <ul class="navbar-nav ms-auto">
        <!-- Patient’s name -->
        <li class="nav-item dropdown">
          <a
            class="nav-link dropdown-toggle"
            href="#"
            id="userDropdown"
            role="button"
            data-bs-toggle="dropdown"
            aria-expanded="false"
          >
            <i class="fas fa-user-circle"></i> 
            <?= htmlspecialchars($patientFirstName . ' ' . $patientLastName); ?>
          </a>
          <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
            <li>
              <a class="dropdown-item" href="../appointment_manager/schedule.php">
                <i class="fas fa-calendar-alt me-2"></i>Appointments
              </a>
            </li>
            <li>
              <a class="dropdown-item" href="../Video_call/join_session.php">
                <i class="fas fa-video me-2"></i>Video Consultation
              </a>
            </li>
            <li>
              <a class="dropdown-item" href="../laboratory.php">
                <i class="fas fa-flask me-2"></i>Lab
              </a>
            </li>
            <li>
              <a class="dropdown-item" href="../pharmacy_management_system/Dashboard.php">
                <i class="fas fa-medkit me-2"></i>Pharmacy
              </a>
            </li>
            <li>
              <a class="dropdown-item" href="../messaging/inbox.php">
                <i class="fas fa-envelope me-2"></i>Messaging
              </a>
            </li>
            <li><hr class="dropdown-divider" /></li>
            <li>
              <a class="dropdown-item" href="../settings/patient_account_settings.php">
                <i class="fas fa-cog me-2"></i>Settings
              </a>
            </li>
            <li>
              <a class="dropdown-item" href="../logout.php">
                <i class="fas fa-sign-out-alt me-2"></i>Logout
              </a>
            </li>
          </ul>
        </li>
      </ul>
    </div>
  </div>
</nav>

<!-- Main Container -->
<div class="container mt-4">
  <!-- Display success/error messages for reschedule/cancel -->
  <?php if(!empty($rescheduleSuccess)): ?>
    <div class="alert alert-success"><?= htmlspecialchars($rescheduleSuccess); ?></div>
  <?php endif; ?>
  <?php if(!empty($rescheduleError)): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($rescheduleError); ?></div>
  <?php endif; ?>
  <?php if(!empty($cancelSuccess)): ?>
    <div class="alert alert-success"><?= htmlspecialchars($cancelSuccess); ?></div>
  <?php endif; ?>
  <?php if(!empty($cancelError)): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($cancelError); ?></div>
  <?php endif; ?>

  <!-- Greeting / Book Appointment -->
  <div class="row">
    <div class="col">
      <div class="p-3 bg-white rounded shadow-sm mb-4">
        <h4 class="fw-bold">Welcome, <?= htmlspecialchars($patientFirstName); ?>!</h4>
        <p class="text-muted mb-2">
          View your upcoming appointments, medical records, billing, and more.
        </p>
        <a href="/my clinic/appointment_manager/schedule.php" class="btn btn-primary btn-sm">
          <i class="fas fa-calendar-plus me-1"></i> Book Appointment
        </a>
      </div>
    </div>
  </div>

  <!-- Next Appointment -->
  <div class="row g-3 mb-3">
    <div class="col-md-4">
      <div class="dashboard-card small-card-body">
        <i class="far fa-calendar-check"></i>
        <h5>Next Appointment</h5>
        <?php if ($nextAppointment):
          $dt       = new DateTime($nextAppointment['appointment_date']);
          $apptType = $nextAppointment['type'] ?? 'Consultation'; 
          ?>
          <p class="text-muted text-center mb-0">
            <?= $dt->format('M j, Y \a\t g:i A'); ?><br/>
            <?= htmlspecialchars($apptType); ?>
          </p>
        <?php else: ?>
          <p class="text-muted text-center mb-0">No upcoming appointments.</p>
        <?php endif; ?>
      </div>
    </div>
    <!-- Video Consultation Card -->
    <div class="col-md-4">
      <div class="dashboard-card small-card-body">
        <i class="fas fa-video"></i>
        <h5>Video Consultation</h5>
        <?php if (!empty($videoConsultInfo) && empty($videoMsg) === false): ?>
          <p class="text-muted text-center mb-0"><?= htmlspecialchars($videoMsg); ?></p>
          <?php if ($videoJoin): ?>
            <!-- Show button to join if it's today -->
            <a href="../video_call.php?meeting_id=<?= urlencode($videoConsultInfo['meeting_id']); ?>"
               class="btn btn-success btn-sm mt-2"
            >
              Join Now
            </a>
          <?php endif; ?>
        <?php else: ?>
          <p class="text-muted text-center mb-0">No upcoming video consultation.</p>
        <?php endif; ?>
      </div>
    </div>
    <!-- Another small container (Medical Records, etc.) -->
    <div class="col-md-4">
      <div class="dashboard-card small-card-body">
        <i class="fas fa-file-medical-alt"></i>
        <h5>Medical Records</h5>
        <p class="text-muted text-center mb-0">
          Check your lab results and prescriptions.
        </p>
      </div>
    </div>
  </div>

  <!-- Detailed Upcoming Appointments -->
  <div class="row g-3 mb-3">
    <div class="col-md-6">
      <div class="dashboard-card">
        <h5><i class="far fa-calendar-check me-2"></i>Upcoming Appointments</h5>
        <?php if (!empty($appointments['upcoming'])): ?>
          <ul class="list-group">
            <?php foreach ($appointments['upcoming'] as $appt):
              $apptId   = $appt['appointment_id'];
              $apptDate = new DateTime($appt['appointment_date']);
              $apptTime = $appt['appointment_time'] ?? '';
              $apptType = $appt['type'] ?? 'Consultation';
            ?>
            <li class="list-group-item d-flex justify-content-between align-items-center">
              <span>
                <?= $apptDate->format('M j, Y'); ?> (<?= htmlspecialchars($apptTime); ?>)
                - <?= htmlspecialchars($apptType); ?>
              </span>
              <span>
                <!-- Reschedule button => open modal -->
                <button 
                  class="btn btn-sm btn-warning"
                  data-bs-toggle="modal"
                  data-bs-target="#rescheduleModal"
                  data-id="<?= $apptId; ?>"
                >
                  Reschedule
                </button>
                <!-- Cancel button => open cancel modal -->
                <button
                  class="btn btn-sm btn-danger"
                  data-bs-toggle="modal"
                  data-bs-target="#cancelModal"
                  data-id="<?= $apptId; ?>"
                >
                  Cancel
                </button>
              </span>
            </li>
            <?php endforeach; ?>
          </ul>
        <?php else: ?>
          <div class="alert alert-secondary">
            No upcoming appointments found.
          </div>
        <?php endif; ?>
      </div>
    </div>

    <div class="col-md-6">
      <div class="dashboard-card">
        <h5><i class="fas fa-notes-medical me-2"></i>Medical Records</h5>
        <p class="text-muted">Prescriptions, test results, and summaries.</p>
        <a href="#" class="btn btn-outline-primary btn-sm">View Records</a>
      </div>
    </div>
  </div>

  <!-- Another row if you want -->
  <div class="row g-3 mb-3">
    <div class="col-md-4">
      <div class="dashboard-card">
        <h5><i class="fas fa-file-invoice-dollar me-2"></i>Billing History</h5>
        <p class="text-muted">Past payments, pending bills, etc.</p>
      </div>
    </div>
    <div class="col-md-4">
      <div class="dashboard-card">
        <h5><i class="fas fa-bell me-2"></i>Notifications</h5>
        <p class="text-muted">Alerts for new appointments or test results.</p>
      </div>
    </div>
    <div class="col-md-4">
      <div class="dashboard-card">
        <h5><i class="fas fa-comments me-2"></i>Messaging</h5>
        <p class="text-muted">Communicate with your healthcare providers.</p>
        <a href="/my clinic/messaging/new_message.php" class="btn btn-primary btn-sm">
          <i class="fas fa-envelope me-1"></i> New Message
        </a>
      </div>
    </div>
  </div>

  

<!-- Reschedule Modal -->
<div 
  class="modal fade" 
  id="rescheduleModal" 
  tabindex="-1" 
  aria-labelledby="rescheduleModalLabel" 
  aria-hidden="true"
>
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="post" action="">
        <div class="modal-header">
          <h5 class="modal-title" id="rescheduleModalLabel">Reschedule Appointment</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="appointment_id" id="reschedule_appt_id" />
          <div class="mb-3">
            <label for="new_date" class="form-label">New Date</label>
            <input type="date" name="new_date" id="new_date" class="form-control" required />
          </div>
          <div class="mb-3">
            <label for="new_time" class="form-label">New Time</label>
            <input type="time" name="new_time" id="new_time" class="form-control" required />
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
          <button 
            type="submit" 
            name="action" 
            value="reschedule" 
            class="btn btn-primary"
          >
            Save Changes
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Cancel Modal -->
<div 
  class="modal fade" 
  id="cancelModal" 
  tabindex="-1" 
  aria-labelledby="cancelModalLabel" 
  aria-hidden="true"
>
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="post" action="">
        <div class="modal-header">
          <h5 class="modal-title" id="cancelModalLabel">Cancel Appointment</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="appointment_id" id="cancel_appt_id" />
          <div class="mb-3">
            <label for="cancel_reason" class="form-label">Reason (optional)</label>
            <textarea 
              name="cancel_reason" 
              id="cancel_reason" 
              class="form-control"
              placeholder="Why are you cancelling?"
            ></textarea>
          </div>
          <p>Are you sure you want to cancel this appointment?</p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">No</button>
          <button 
            type="submit" 
            name="action" 
            value="cancel" 
            class="btn btn-danger"
          >
            Yes, Cancel
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<script
  src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js">
</script>
<script>
  // For Reschedule
  const rescheduleModal = document.getElementById('rescheduleModal');
  if (rescheduleModal) {
    rescheduleModal.addEventListener('show.bs.modal', event => {
      const button = event.relatedTarget; 
      const apptId = button.getAttribute('data-id'); 
      const inputField = rescheduleModal.querySelector('#reschedule_appt_id');
      inputField.value = apptId;
    });
  }

  // For Cancel
  const cancelModal = document.getElementById('cancelModal');
  if (cancelModal) {
    cancelModal.addEventListener('show.bs.modal', event => {
      const button  = event.relatedTarget;
      const apptId  = button.getAttribute('data-id');
      const inputId = cancelModal.querySelector('#cancel_appt_id');
      inputId.value = apptId;
    });
  }
</script>
</body>
</html>
