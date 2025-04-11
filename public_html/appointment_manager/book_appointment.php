<?php

session_start();

// Load DB connection and other configs
require_once '../init.php';
require_once '../config.php';

/**
 * If user isn't logged in, we can decide how to handle search requests:
 * - Return JSON error or an empty array. 
 */
function notLoggedInJson()
{
    // If you prefer an error message:
    // echo json_encode(["error" => "Not logged in"]);
    // or just return no results:
    echo json_encode([]);
    exit;
}

//////////////////////////////////////////
// 2) HANDLE AJAX PATIENT SEARCH EARLY
//////////////////////////////////////////
if (isset($_GET['action']) && $_GET['action'] === 'search_patient') {
    // e.g. ?action=search_patient&query=king
    header('Content-Type: application/json; charset=utf-8');

    // If not logged in, return empty array or an error instead of redirecting
    if (!isset($_SESSION['username'])) {
        notLoggedInJson();  // ends script
    }

    // Now do your LIKE search
    $query = $_GET['query'] ?? '';
    $query = $conn->real_escape_string($query);

    $sql = "SELECT patient_id, first_name, middle_name, surname
            FROM patient_db
            WHERE patient_id  LIKE '%$query%'
               OR first_name  LIKE '%$query%'
               OR middle_name LIKE '%$query%'
               OR surname     LIKE '%$query%'";

    $result   = $conn->query($sql);
    $patients = [];
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $patients[] = $row;
        }
    }
    echo json_encode($patients);
    exit; // Stop here; no HTML output
}

//////////////////////////////////////////
// 3) NORMAL PAGE FLOW (not a search call)
//////////////////////////////////////////

// If you have an access check that redirects or outputs HTML, do it here.
require_once '../access_control.php';

// Now check if user is logged in for normal page usage:
if (!isset($_SESSION['username'])) {
    // This redirect only happens during normal page load,
    // not for the search action above.
    header("Location: ../login/login.php");
    exit();
}

// Optionally load your sidebar or any other includes that produce HTML
require_once '../sidebar.php';

// Grab session info
$username  = $_SESSION['username'];
$usergroup = $_SESSION['usergroup'] ?? '';

// 4) If user is Patient => load their own patient_id. Otherwise admin/staff => ?id=123
$patientID = '';
$fullName  = '';

if ($usergroup === 'Patient') {
    // fetch from patient_db
    $stmt = $conn->prepare("SELECT patient_id, first_name, middle_name, surname 
                            FROM patient_db WHERE username=? LIMIT 1");
    $stmt->bind_param('s', $username);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res->num_rows > 0) {
        $p = $res->fetch_assoc();
        $patientID = $p['patient_id'];
        $fullName  = trim($p['first_name'].' '.$p['middle_name'].' '.$p['surname']);
    } else {
        die("Cannot find your patient record. Please contact admin.");
    }
    $stmt->close();
} else {
    // admin or staff
    if (isset($_GET['id']) && $_GET['id'] !== '') {
        $theID = $_GET['id'];
        $stmt = $conn->prepare("SELECT patient_id, first_name, middle_name, surname 
                                FROM patient_db WHERE patient_id=? LIMIT 1");
        $stmt->bind_param('i', $theID);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($res->num_rows > 0) {
            $p = $res->fetch_assoc();
            $patientID = $p['patient_id'];
            $fullName  = trim($p['first_name'].' '.$p['middle_name'].' '.$p['surname']);
        }
        $stmt->close();
    }
}

// 5) Helper functions to generate meeting ID/password
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

// 6) Handle Form Submission to Book Appointment
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'book') {
    $patient_id       = intval($_POST['patient_id'] ?? 0);
    $patient_name     = $_POST['patient_name'] ?? '';
    $appointment_date = $_POST['appointment_date'] ?? '';
    $appointment_time = $_POST['appointment_time'] ?? '';
    $type             = $_POST['type'] ?? '';
    $department       = $_POST['department'] ?? '';
    $reason           = $_POST['reason'] ?? '';

    if ($patient_id > 0 && $appointment_date && $appointment_time && $type && $department) {
        // Insert into appointments
        $sql = "INSERT INTO appointments 
                  (patient_id, patient_full_name, appointment_date, appointment_time, type, department, reason)
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param(
            "issssss", 
            $patient_id, 
            $patient_name, 
            $appointment_date, 
            $appointment_time, 
            $type, 
            $department, 
            $reason
        );
        if ($stmt->execute()) {
            $message = "Appointment booked successfully!";

            // If 'type' == 'Online', automatically create meeting
            if ($type === 'Online') {
                // Generate unique meeting_id + password
                $meetingId = generateMeetingID();
                $password  = generatePassword();
                $createdBy = $username;
                
                // Combine date+time for expiration
                $expirationDateTime = $appointment_date.' '.$appointment_time.':00';

                // Insert meeting row
                $sqlMeeting = "INSERT INTO meetings 
                  (meeting_id, password, created_by, patient_id, patient_name, reason, department, 
                   expiration, assigned_staff, status)
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'Scheduled')";
                $stmt2 = $conn->prepare($sqlMeeting);
                $assignedStaff = $department; // or a staff name
                $stmt2->bind_param(
                    "sssisssss",
                    $meetingId,
                    $password,
                    $createdBy,
                    $patient_id,
                    $patient_name,
                    $reason,
                    $department,
                    $expirationDateTime,
                    $assignedStaff
                );
                if ($stmt2->execute()) {
                    $message .= " A meeting (session) was also created automatically.";
                } else {
                    $message .= " However, failed to create session: " . $stmt2->error;
                }
                $stmt2->close();
            }
        } else {
            $message = "Error booking appointment: " . $stmt->error;
        }
        $stmt->close();
    } else {
        $message = "Please fill in all required fields.";
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Book Appointment</title>
    <!-- Bootstrap 5 CSS -->
    <link
      rel="stylesheet"
      href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css"
    />
    <style>
      body {
        background-color: #f2f2f2;
      }
      .search-results {
        background: #fff;
        border: 1px solid #ccc;
        position: absolute;
        z-index: 999;
        width: 100%;
      }
      .search-results div {
        padding: 5px;
        cursor: pointer;
      }
      .search-results div:hover {
        background: #eee;
      }
    </style>
</head>
<body>
<div class="container py-4">
  <h2 class="mb-4">Book Appointment</h2>

  <?php if(!empty($message)): ?>
    <div class="alert alert-info"><?= htmlspecialchars($message); ?></div>
  <?php endif; ?>

  <!-- Link to Availability (if user is admin/staff) -->
  <?php 
    $allowedGroups = ['Admin','Doctor','Nurse','Lab Scientist'];
    if (in_array($usergroup, $allowedGroups)): 
  ?>
    <div class="mb-3">
      <a href="availability_settings.php" class="btn btn-outline-primary">
        Set Availability
      </a>
    </div>
  <?php endif; ?>

  <form action="book_appointment.php<?php if(!empty($patientID)){ echo '?id='.$patientID;} ?>" method="post" class="bg-white p-4 rounded shadow-sm">
    <input type="hidden" name="action" value="book">

    <?php if ($usergroup !== 'Patient'): ?>
      <!-- Admin/Staff: can search for patient by name or ID -->
      <div class="mb-3 position-relative">
        <label for="search_patient" class="form-label">Search Patient</label>
        <input 
          type="text" 
          id="search_patient" 
          class="form-control" 
          placeholder="Enter patient name or ID..."
          autocomplete="off"
        >
        <div id="search_results" class="search-results" style="display:none;"></div>
      </div>
    <?php endif; ?>

    <div class="row">
      <div class="col-md-6 mb-3">
        <label for="patient_id" class="form-label">Patient ID</label>
        <input 
          type="text" 
          name="patient_id" 
          id="patient_id" 
          class="form-control" 
          value="<?= htmlspecialchars($patientID); ?>" 
          readonly 
          required
        >
      </div>
      <div class="col-md-6 mb-3">
        <label for="patient_name" class="form-label">Patient Name</label>
        <input 
          type="text" 
          name="patient_name" 
          id="patient_name" 
          class="form-control"
          value="<?= htmlspecialchars($fullName); ?>" 
          readonly 
          required
        >
      </div>
    </div>

    <div class="row">
      <div class="col-md-6 mb-3">
        <label for="department" class="form-label">Department</label>
        <select name="department" id="department" class="form-select" required>
            <option value="" disabled selected>Select department</option>
            <option value="Doctor">Doctor</option>
            <option value="Nurse">Nurse</option>
            <option value="Lab Scientist">Lab Scientist</option>
        </select>
      </div>
      <div class="col-md-6 mb-3">
        <label for="type" class="form-label">Appointment Type</label>
        <select name="type" id="type" class="form-select" required>
          <option value="" disabled selected>Select type</option>
          <option value="Online">Online</option>
          <option value="In-clinic">In-clinic</option>
        </select>
      </div>
    </div>

    <div class="row">
      <div class="col-md-6 mb-3">
        <label for="appointment_date" class="form-label">Date of Appointment</label>
        <input 
          type="date" 
          name="appointment_date" 
          id="appointment_date" 
          class="form-control"
          required
        >
      </div>
      <div class="col-md-6 mb-3">
        <label for="appointment_time" class="form-label">Time of Appointment</label>
        <select name="appointment_time" id="appointment_time" class="form-select" required>
          <option value="" disabled selected>Select time</option>
          <!-- Populated by JS (fetch_availability.php) -->
        </select>
      </div>
    </div>

    <div class="mb-3">
      <label for="reason" class="form-label">Reason for the Appointment</label>
      <textarea name="reason" id="reason" class="form-control" rows="3" required></textarea>
    </div>

    <div>
      <button type="submit" class="btn btn-primary">Book Appointment</button>
    </div>
  </form>
</div>

<!-- Bootstrap 5 JS -->
<script
  src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js">
</script>

<script>
  // 1) SEARCH PATIENT (for admin/staff)
  function searchPatient() {
    const query = document.getElementById('search_patient').value.trim();
    if (query.length < 2) {
      document.getElementById('search_results').style.display = 'none';
      return;
    }
    // We'll call this same page with ?action=search_patient&query=...
    fetch(`book_appointment.php?action=search_patient&query=${encodeURIComponent(query)}`)
      .then(res => res.json())
      .then(data => {
        const resultsDiv = document.getElementById('search_results');
        resultsDiv.innerHTML = '';
        if (data.length > 0) {
          data.forEach(item => {
            const div = document.createElement('div');
            div.textContent = 
              item.patient_id + ' - ' + item.first_name + ' ' + item.middle_name + ' ' + item.surname;
            div.onclick = () => {
              document.getElementById('patient_id').value   = item.patient_id;
              document.getElementById('patient_name').value = 
                item.first_name + ' ' + item.middle_name + ' ' + item.surname;
              resultsDiv.style.display = 'none';
            };
            resultsDiv.appendChild(div);
          });
          resultsDiv.style.display = 'block';
        } else {
          resultsDiv.style.display = 'none';
        }
      })
      .catch(err => {
        console.error('Error fetching patients:', err);
        document.getElementById('search_results').style.display = 'none';
      });
  }
  const searchField = document.getElementById('search_patient');
  if (searchField) {
    searchField.addEventListener('input', searchPatient);
  }

  // 2) DYNAMICALLY FETCH AVAILABLE TIMES FROM fetch_availability.php
  const deptEl = document.getElementById('department');
  const typeEl = document.getElementById('type');
  const dateEl = document.getElementById('appointment_date');
  const timeEl = document.getElementById('appointment_time');

  function updateTimes() {
    const dept = deptEl.value;
    const typ  = typeEl.value;
    const dat  = dateEl.value;
    if (!dept || !typ || !dat) return;

    fetch(`fetch_availability.php?department=${encodeURIComponent(dept)}&type=${encodeURIComponent(typ)}&date=${encodeURIComponent(dat)}`)
      .then(res => res.json())
      .then(data => {
        timeEl.innerHTML = '<option value="" disabled selected>Select time</option>';
        data.forEach(slot => {
          const opt = document.createElement('option');
          opt.value = slot.time;
          opt.textContent = slot.time;
          if (slot.disabled) {
            opt.disabled = true;
            opt.textContent += ' (Unavailable)';
          }
          timeEl.appendChild(opt);
        });
      })
      .catch(err => {
        console.error('Error fetching availability:', err);
      });
  }

  deptEl.addEventListener('change', updateTimes);
  typeEl.addEventListener('change', updateTimes);
  dateEl.addEventListener('change', updateTimes);
</script>
</body>
</html>
