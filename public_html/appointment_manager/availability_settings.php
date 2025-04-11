<?php
session_start();
require_once '../init.php';
require_once '../config.php';
require_once '../access_control.php';
require_once '../sidebar.php'; // INCLUDE SIDEBAR

// 1) Check user group
if (!isset($_SESSION['username']) || !isset($_SESSION['usergroup'])) {
    header("Location: ../login/login.php");
    exit();
}
$usergroup = $_SESSION['usergroup'];
$username  = $_SESSION['username'];

// 2) Only Admin, Doctor, Nurse, Lab Scientist can view
$allowedGroups = ['Admin', 'Doctor', 'Nurse', 'Lab Scientist'];
if (!in_array($usergroup, $allowedGroups)) {
    echo "<p>You do not have permission to access availability settings.</p>";
    exit();
}

// 3) Department logic (only Admin can pick any dept; Doctor => "Doctor", Nurse => "Nurse", Lab => "Lab Scientist")
function getDepartmentForUserGroup($ug) {
    switch ($ug) {
        case 'Doctor':        return 'Doctor';
        case 'Nurse':         return 'Nurse';
        case 'Lab Scientist': return 'Lab Scientist';
        default:              return ''; // Admin can choose from a dropdown
    }
}

// 4) If the form is submitted
$successMsg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $department       = $_POST['department'] ?? '';
    $appointmentType  = $_POST['appointment_type'] ?? 'Online';
    $timeInterval     = intval($_POST['time_interval'] ?? 15);
    $availabilityDate = $_POST['availability_date'] ?? '';
    $timeSlots        = $_POST['time_slot'] ?? [];
    $slotNumbers      = $_POST['slot_number'] ?? [];
    $unavailables     = $_POST['is_unavailable'] ?? [];

    // Convert empty date => NULL
    $dateValue = (!empty($availabilityDate)) ? $availabilityDate : null;

    // For each timeSlot posted, we’ll delete old entry then insert new row
    foreach ($timeSlots as $index => $timeSlot) {
        $slotNo = isset($slotNumbers[$index]) ? intval($slotNumbers[$index]) : 1;
        $isUnav = isset($unavailables[$index]) ? 1 : 0;

        // 4A) Delete old
        $delSql = "DELETE FROM appointment_availability
                   WHERE department=?
                     AND appointment_type=?
                     AND time_slot=?
                     AND time_interval=?
                     AND (
                       (availability_date IS NULL AND ? IS NULL)
                       OR (availability_date = ?)
                     )";
        $delStmt = $conn->prepare($delSql);
        $delStmt->bind_param(
            'sssisi',
            $department,
            $appointmentType,
            $timeSlot,
            $timeInterval,
            $dateValue,
            $dateValue
        );
        $delStmt->execute();
        $delStmt->close();

        // 4B) Insert new
        $insSql = "INSERT INTO appointment_availability
          (department, appointment_type, availability_date, time_interval, time_slot, slot_number, is_unavailable)
          VALUES (?, ?, ?, ?, ?, ?, ?)";
        $insStmt = $conn->prepare($insSql);
        $insStmt->bind_param(
            'sssissi',
            $department,
            $appointmentType,
            $dateValue,
            $timeInterval,
            $timeSlot,
            $slotNo,
            $isUnav
        );
        $insStmt->execute();
        $insStmt->close();
    }

    $successMsg = "Availability settings saved successfully!";
}

// 5) Generate the time slots for the form 
function generateTimeSlots($intervalMinutes = 15, $start='07:00', $end='20:00') {
    $slots = [];
    $current = strtotime($start);
    $last    = strtotime($end);
    while ($current <= $last) {
        $slots[] = date('H:i', $current);
        $current = strtotime("+{$intervalMinutes} minutes", $current);
    }
    return $slots;
}

// Default interval for the form on first load
$defaultInterval = isset($_POST['time_interval']) ? intval($_POST['time_interval']) : 15;
$userDept = getDepartmentForUserGroup($usergroup);

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Availability Settings</title>
  <!-- Bootstrap -->
  <link
    href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css"
    rel="stylesheet"
  />
</head>
<body>
<div class="container py-4">
  <h1 class="mb-4">Set Availability</h1>
  <?php if (!empty($successMsg)): ?>
    <div class="alert alert-success"><?= htmlspecialchars($successMsg); ?></div>
  <?php endif; ?>

  <form method="POST" class="bg-white p-4 rounded shadow-sm">
    <!-- Department -->
    <div class="mb-3">
      <label for="department" class="form-label">Department</label>
      <?php if ($usergroup === 'Admin'): ?>
        <select name="department" id="department" class="form-select" required>
          <option value="" disabled selected>Select Department</option>
          <option value="Doctor"        <?= (isset($_POST['department']) && $_POST['department']=='Doctor'       ? 'selected' : ''); ?>>Doctor</option>
          <option value="Nurse"         <?= (isset($_POST['department']) && $_POST['department']=='Nurse'        ? 'selected' : ''); ?>>Nurse</option>
          <option value="Lab Scientist" <?= (isset($_POST['department']) && $_POST['department']=='Lab Scientist'? 'selected' : ''); ?>>Lab Scientist</option>
        </select>
      <?php else: ?>
        <!-- For Doctor/Nurse/LabScientist, read-only input -->
        <input type="text" name="department" id="department" class="form-control" 
               value="<?= htmlspecialchars($userDept); ?>" readonly />
      <?php endif; ?>
    </div>

    <!-- Appointment Type -->
    <div class="mb-3">
      <label for="appointment_type" class="form-label">Appointment Type</label>
      <select name="appointment_type" id="appointment_type" class="form-select" required>
        <option value="Online"    <?= (isset($_POST['appointment_type']) && $_POST['appointment_type']=='Online'   ? 'selected' : ''); ?>>Online</option>
        <option value="In-clinic" <?= (isset($_POST['appointment_type']) && $_POST['appointment_type']=='In-clinic'? 'selected' : ''); ?>>In-clinic</option>
      </select>
    </div>

    <!-- Time Interval -->
    <div class="mb-3">
      <label for="time_interval" class="form-label">Time Interval (minutes)</label>
      <select name="time_interval" id="time_interval" class="form-select" required>
        <option value="15" <?= ($defaultInterval==15 ? 'selected' : ''); ?>>15</option>
        <option value="30" <?= ($defaultInterval==30 ? 'selected' : ''); ?>>30</option>
        <option value="45" <?= ($defaultInterval==45 ? 'selected' : ''); ?>>45</option>
        <option value="60" <?= ($defaultInterval==60 ? 'selected' : ''); ?>>60</option>
      </select>
    </div>

    <!-- Optional Date -->
    <div class="mb-3">
      <label for="availability_date" class="form-label">
        Date for These Settings (blank => applies to ALL dates)
      </label>
      <input type="date" name="availability_date" id="availability_date"
             class="form-control"
             value="<?= isset($_POST['availability_date']) ? $_POST['availability_date'] : ''; ?>">
    </div>

    <!-- Generate Time Slots & slot_number & is_unavailable -->
    <?php 
      $timeSlots = generateTimeSlots($defaultInterval, '07:00', '20:00'); 
      // On postback, preserve user inputs
      $postedSlotNumbers = $_POST['slot_number'] ?? [];
      $postedUnavailable = $_POST['is_unavailable'] ?? [];
    ?>
    <table class="table table-bordered mb-4">
      <thead>
        <tr>
          <th>Time Slot</th>
          <th>Slot # (Capacity)</th>
          <th>Mark Unavailable?</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($timeSlots as $index => $slot):
          $thisSlotNumber = $postedSlotNumbers[$index] ?? 1;
          $thisUnav       = isset($postedUnavailable[$index]) ? true : false;
        ?>
        <tr>
          <td>
            <?= htmlspecialchars($slot); ?>
            <input type="hidden" name="time_slot[]" value="<?= $slot; ?>">
          </td>
          <td style="width: 150px;">
            <input type="number" min="1" max="50" name="slot_number[]" 
                   class="form-control"
                   value="<?= htmlspecialchars($thisSlotNumber); ?>">
          </td>
          <td style="width: 150px; text-align: center;">
            <input type="checkbox" name="is_unavailable[<?= $index; ?>]" 
                   value="1" <?= $thisUnav ? 'checked' : ''; ?>>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>

    <button type="submit" class="btn btn-primary">Save Settings</button>
  </form>
</div>

<!-- Bootstrap JS -->
<script
  src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js">
</script>
</body>
</html>
