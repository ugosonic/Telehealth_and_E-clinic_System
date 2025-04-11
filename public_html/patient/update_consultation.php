<?php
include '../init.php';
include '../config.php';
include '../access_control.php';
include '../sidebar.php';

// Determine the request method
$method = $_SERVER['REQUEST_METHOD'];

// Initialize variables
$patient_id = 0;
$consultation_id = 0;

// 1) Handle POST (updating consultation)
if ($method === 'POST') {
    $patient_id      = isset($_POST['patient_id'])       ? intval($_POST['patient_id'])       : 0;
    $consultation_id = isset($_POST['consultation_id'])  ? intval($_POST['consultation_id'])  : 0;

    if ($patient_id === 0 || $consultation_id === 0) {
        die("Patient ID or Consultation ID is missing.");
    }

    // Retrieve form data
    // Vital Signs
    $blood_pressure    = $_POST['blood_pressure']    ?? null;
    $heart_rate        = $_POST['heart_rate']        ?? null;
    $respiratory_rate  = $_POST['respiratory_rate']  ?? null;
    $temperature       = $_POST['temperature']       ?? null;
    $weight            = $_POST['weight']            ?? null;
    $height            = $_POST['height']            ?? null;
    $bmi               = $_POST['bmi']               ?? null;

    // Visit Details
    $reason_for_visit      = $_POST['reason_for_visit']      ?? null;
    $history_of_illness    = $_POST['history_of_illness']    ?? null;
    $past_medical_history  = $_POST['past_medical_history']  ?? null;
    $family_history        = $_POST['family_history']        ?? null;
    $social_history        = $_POST['social_history']        ?? null;
    $allergies             = $_POST['allergies']             ?? null;
    $initial_diagnosis     = $_POST['initial_diagnosis']     ?? null;
    $final_diagnosis       = $_POST['final_diagnosis']       ?? null;

    // Physical Examination
    $general_appearance    = $_POST['general_appearance']    ?? null;
    $heent                 = $_POST['heent']                 ?? null;
    $cardiovascular        = $_POST['cardiovascular']        ?? null;
    $respiratory           = $_POST['respiratory']           ?? null;
    $gastrointestinal      = $_POST['gastrointestinal']       ?? null;
    $musculoskeletal       = $_POST['musculoskeletal']       ?? null;
    $neurological          = $_POST['neurological']          ?? null;
    $skin                  = $_POST['skin']                  ?? null;

    // Laboratory Tests
    // (If you have a hidden input "lab_tests" with comma-separated IDs, you can handle it)
    $lab_tests = $_POST['lab_tests'] ?? null; // Expecting a comma-separated string
    
    // Treatment Plan
    $medication_ids       = $_POST['medication_ids']       ?? []; // arrays
    $medication_names     = $_POST['medication_names']     ?? [];
    $medication_dosages   = $_POST['medication_dosages']   ?? [];
    $other_prescriptions  = $_POST['other_prescriptions']  ?? null;

    // Convert arrays to comma-separated strings for storage
    $medication_ids_str     = implode(',', $medication_ids);
    $medication_names_str   = implode(',', $medication_names);
    $medication_dosages_str = implode(',', $medication_dosages);

    // Follow-Up Instructions
    $follow_up_date         = $_POST['follow_up_date']        ?? null;
    $symptoms_to_watch      = $_POST['symptoms_to_watch']      ?? null;
    $emergency_instructions = $_POST['emergency_instructions'] ?? null;
    $education_materials    = $_POST['education_materials']    ?? null;

    // Additional Notes
    $doctor_comments  = $_POST['doctor_comments']  ?? null;
    $patient_concerns = $_POST['patient_concerns'] ?? null;

    // Doctor info from session
    $doctor_name = $_SESSION['username']  ?? '';
    $department  = $_SESSION['usergroup'] ?? '';

    $conn->begin_transaction();

    try {
        // 2) Update the consultations table
        $sql = "UPDATE consultations SET
            vital_signs_blood_pressure = ?,
            vital_signs_heart_rate = ?,
            vital_signs_respiratory_rate = ?,
            vital_signs_temperature = ?,
            vital_signs_weight = ?,
            vital_signs_height = ?,
            vital_signs_bmi = ?,
            reason_for_visit = ?,
            history_of_illness = ?,
            past_medical_history = ?,
            family_history = ?,
            social_history = ?,
            allergies = ?,
            initial_diagnosis = ?,
            final_diagnosis = ?,
            physical_examination_general_appearance = ?,
            physical_examination_heent = ?,
            physical_examination_cardiovascular = ?,
            physical_examination_respiratory = ?,
            physical_examination_gastrointestinal = ?,
            physical_examination_musculoskeletal = ?,
            physical_examination_neurological = ?,
            physical_examination_skin = ?,
            lab_tests = ?,
            medication_ids = ?,
            medication_names = ?,
            medication_dosages = ?,
            other_prescriptions = ?,
            follow_up_date = ?,
            symptoms_to_watch = ?,
            emergency_instructions = ?,
            education_materials = ?,
            doctor_comments = ?,
            patient_concerns = ?,
            last_updated = NOW()
            WHERE consultation_id = ? AND patient_id = ?";

        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }

        // Bind
        $stmt->bind_param(
            'ssssssssssssssssssssssssssssssiiiiii',
            $blood_pressure,
            $heart_rate,
            $respiratory_rate,
            $temperature,
            $weight,
            $height,
            $bmi,
            $reason_for_visit,
            $history_of_illness,
            $past_medical_history,
            $family_history,
            $social_history,
            $allergies,
            $initial_diagnosis,
            $final_diagnosis,
            $general_appearance,
            $heent,
            $cardiovascular,
            $respiratory,
            $gastrointestinal,
            $musculoskeletal,
            $neurological,
            $skin,
            $lab_tests,  // comma-separated list
            $medication_ids_str,
            $medication_names_str,
            $medication_dosages_str,
            $other_prescriptions,
            $follow_up_date,
            $symptoms_to_watch,
            $emergency_instructions,
            $education_materials,
            $doctor_comments,
            $patient_concerns,
            $consultation_id,
            $patient_id
        );

        if (!$stmt->execute()) {
            throw new Exception("Consultation update failed: " . $stmt->error);
        }
        $stmt->close();
      
        // 3) Update or Insert prescription record
        $sql_prescription = "UPDATE prescriptions SET
            doctor_name = ?,
            department = ?,
            date_prescribed = ?,
            time_prescribed = ?,
            medication_ids = ?,
            medication_names = ?,
            medication_dosages = ?,
            status = ?
            WHERE consultation_id = ? AND patient_id = ?";

        $prescription_stmt = $conn->prepare($sql_prescription);
        if (!$prescription_stmt) {
            throw new Exception("Prepare failed for prescription: " . $conn->error);
        }

        $date_prescribed = date('Y-m-d');
        $time_prescribed = date('H:i:s');
        $status = 'Pending'; // or however you want

        $prescription_stmt->bind_param(
            'ssssssssii',
            $doctor_name,
            $department,
            $date_prescribed,
            $time_prescribed,
            $medication_ids_str,
            $medication_names_str,
            $medication_dosages_str,
            $status,
            $consultation_id,
            $patient_id
        );

        if (!$prescription_stmt->execute()) {
            throw new Exception("Prescription update failed: " . $prescription_stmt->error);
        }

        // If no rows updated, insert new
        if ($prescription_stmt->affected_rows === 0) {
            $sql_insert_prescription = "INSERT INTO prescriptions
                (patient_id, consultation_id, doctor_name, department, date_prescribed, time_prescribed,
                 medication_ids, medication_names, medication_dosages, status)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

            $insert_stmt = $conn->prepare($sql_insert_prescription);
            if (!$insert_stmt) {
                throw new Exception("Prepare failed for inserting prescription: " . $conn->error);
            }
            $insert_stmt->bind_param(
                'iissssssss',
                $patient_id,
                $consultation_id,
                $doctor_name,
                $department,
                $date_prescribed,
                $time_prescribed,
                $medication_ids_str,
                $medication_names_str,
                $medication_dosages_str,
                $status
            );
            if (!$insert_stmt->execute()) {
                throw new Exception("Prescription insert failed: " . $insert_stmt->error);
            }
            $insert_stmt->close();
        }
        $prescription_stmt->close();

        // 1) Check if the test already exists for this consultation & patient
// Handle lab requests
$lab_tests = $_POST['lab_tests'] ?? null; // e.g., "4,7,9"
if ($lab_tests) {
    $lab_test_ids = explode(',', $lab_tests);
    foreach ($lab_test_ids as $template_id) {
        // Check if already exists
        $check_sql = "SELECT id FROM lab_requests WHERE consultation_id = ? AND patient_id = ? AND template_id = ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("iii", $consultation_id, $patient_id, $template_id);
        $check_stmt->execute();
        $check_stmt->store_result();

        if ($check_stmt->num_rows === 0) {
            // Insert new lab request
            $insert_sql = "INSERT INTO lab_requests (consultation_id, patient_id, template_id, requested_by, date_requested, status)
                           VALUES (?, ?, ?, ?, NOW(), 'Pending')";
            $insert_stmt = $conn->prepare($insert_sql);
            $requested_by = $_SESSION['username'] ?? 'unknown';
            $insert_stmt->bind_param("iiis", $consultation_id, $patient_id, $template_id, $requested_by);
            $insert_stmt->execute();
            $insert_stmt->close();
        }
        $check_stmt->close();
    }
}


        // 4) Insert a record into consultation_updates
        $update_sql = "INSERT INTO consultation_updates (consultation_id, updated_at, updated_by, update_notes)
                       VALUES (?, NOW(), ?, ?)";
        $update_stmt = $conn->prepare($update_sql);
        if (!$update_stmt) {
            throw new Exception("Prepare failed for consultation_updates: " . $conn->error);
        }
        $updated_by   = $_SESSION['username'] ?? '';
        $update_notes = "Consultation details updated.";
        $update_stmt->bind_param("iss", $consultation_id, $updated_by, $update_notes);
        if (!$update_stmt->execute()) {
            throw new Exception("Failed to insert into consultation_updates: " . $update_stmt->error);
        }
        $update_stmt->close();

        // Commit transaction
        $conn->commit();

        $_SESSION['message']      = "Consultation and prescription updated successfully.";
        $_SESSION['message_type'] = "success";
    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['message']      = "An error occurred: " . $e->getMessage();
        $_SESSION['message_type'] = "error";
    }

// After processing, we have $patient_id and $consultation_id
} elseif ($method === 'GET') {
    $patient_id      = isset($_GET['id'])               ? intval($_GET['id'])               : 0;
    $consultation_id = isset($_GET['consultation_id'])  ? intval($_GET['consultation_id'])  : 0;
    if ($patient_id === 0 || $consultation_id === 0) {
        die("Patient ID or Consultation ID is missing.");
    }
} else {
    die("Invalid request method.");
}

// ----------------------------------
// Common code to fetch data
// ----------------------------------
// 1) Fetch patient
$sql = "SELECT * FROM patient_db WHERE patient_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $patient_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    $patient = $result->fetch_assoc();
} else {
    die("No patient found with ID: " . htmlspecialchars($patient_id));
}
$stmt->close();

// 2) Fetch consultation
$sql = "SELECT * FROM consultations WHERE consultation_id = ? AND patient_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $consultation_id, $patient_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    $consultation = $result->fetch_assoc();
} else {
    die("No consultation found with ID: " . htmlspecialchars($consultation_id));
}
$stmt->close();

// 3) Fetch user details
$username   = '';
$email      = '';
$department = $_SESSION['usergroup'] ?? '';
if (isset($_SESSION['username'])) {
    $stmt = $conn->prepare('SELECT username, email FROM users WHERE username = ?');
    if ($stmt === false) {
        die("Prepare failed: " . $conn->error);
    }
    $stmt->bind_param('s', $_SESSION['username']);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result === false) {
        die("Get result failed: " . $stmt->error);
    }
    if ($result->num_rows > 0) {
        $user_row = $result->fetch_assoc();
        $username = $user_row['username'];
        $email    = $user_row['email'];
    }
    $stmt->close();
}

// 4) Fetch lab tests from (say) your `lab_test_templates` or `lab_tests`
$laboratory_tests = [];
$sql = "SELECT id, name AS test_name FROM lab_test_templates ORDER BY name ASC";
$res = $conn->query($sql);
if ($res) {
    while ($row = $res->fetch_assoc()) {
        $laboratory_tests[] = $row;
    }
}
$res->free();

// 5) If the consultation has `lab_tests = '4,7,9'`, parse them
$selectedLabTests = [];
if (!empty($consultation['lab_tests'])) {
    $lab_test_ids = explode(',', $consultation['lab_tests']);
    // Map the IDs to names
    $labTestMap = [];
    foreach ($laboratory_tests as $test) {
        // $test['id'], $test['test_name']
        $labTestMap[$test['id']] = $test['test_name'];
    }
    foreach ($lab_test_ids as $id) {
        if (isset($labTestMap[$id])) {
            $selectedLabTests[] = [
                'id'        => $id,
                'test_name' => $labTestMap[$id]
            ];
        }
    }
}

// 6) Fetch available medications (for the UI)
$availableMedications = [];
$sql = "SELECT id, name FROM inventory ORDER BY name ASC";
$res = $conn->query($sql);
if ($res) {
    while ($row = $res->fetch_assoc()) {
        $availableMedications[] = $row;
    }
}
$res->free();

// 7) Build selected medications from the consultation
$selectedMedications = [];
if (!empty($consultation['medication_ids'])) {
    $med_ids   = explode(',', $consultation['medication_ids']);
    $med_names = explode(',', $consultation['medication_names']);
    $med_doses = explode(',', $consultation['medication_dosages']);
    foreach ($med_ids as $index => $m_id) {
        $selectedMedications[] = [
            'id'     => $m_id,
            'name'   => $med_names[$index] ?? '',
            'dosage' => $med_doses[$index] ?? ''
        ];
    }
}

// 8) Also fetch **lab_requests** for this consultation/patient
//    so we can show status of each requested test + completed result (if any).
$labRequests = [];
$sql = "
    SELECT lr.id AS request_id,
           lr.template_id,
           lr.status,
           lr.result_content,
           ltt.name AS template_name,
           ltt.name AS template_name
           FROM lab_requests lr
           LEFT JOIN lab_test_templates ltt ON lr.template_id = ltt.id
    WHERE lr.consultation_id = ? AND lr.patient_id = ?
    ORDER BY lr.id ASC
";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $consultation_id, $patient_id);
$stmt->execute();
$res = $stmt->get_result();
while ($row = $res->fetch_assoc()) {
    $labRequests[] = $row;
}
$stmt->close();

$current_date = date('Y-m-d');
$current_time = date('H:i');

// Decide if staff can edit vital signs
function canEditVitalSigns($userGroup) {
    return ($userGroup === 'Doctor' || $userGroup === 'Nurse');
}
$isEditable = canEditVitalSigns($department);

function isDoctor($userGroup) {
    return ($userGroup === 'Doctor' || $userGroup === 'Nurse');
}
$isEditable2 = isDoctor($department);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Update Consultation</title>
    <link rel="stylesheet" href="../css/patient_record.css">
    <link rel="stylesheet" href="patient.css">

    <!-- Bootstrap 5 (for the read-only modal) -->
    <link 
      rel="stylesheet" 
      href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css"
    />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</head>
<body>
<div class="dash-body">
    <div class="profile-header custom-profile">
        <div class="profile-details">
            <div class="header-container">
                <h1 class="header-name">Update Consultation</h1>
                <h1 class="header-title">
                    <?= htmlspecialchars($patient['first_name'] . ' ' . $patient['surname']); ?>
                </h1>
                <div class="profile-picture">
                    <?php
                    include 'patient_profile_picture.php';
                    displayPatientProfilePicture($patient);
                    ?>
                </div>
            </div>
            <div class="nav-links">
                <a href="patient_record.php?id=<?= $patient_id ?>">Profile</a>
                <a href="edit_record.php?id=<?= $patient_id ?>">Edit Record</a>
                <a href="consultation.php?id=<?= $patient_id ?>&consultation_id=<?= $consultation_id ?>">
                  Consultation
                </a>
                <a href="consultation_history.php?id=<?= $patient_id ?>">
                  Consultation History
                </a>
                <a href="prescriptions.php?id=<?= $patient_id ?>">
                  Prescriptions
                </a>
                <a href="../appointment_manager/book_appointment.php?id=<?= $patient_id ?>">
                  Appointments
                </a>
            </div>
        </div>
    </div>

    <?php if (isset($_SESSION['message'])): ?>
        <div class="popup-message <?= $_SESSION['message_type']; ?>" id="message-popup">
            <?= $_SESSION['message']; ?>
            <button class="close-btn" 
                    onclick="document.getElementById('message-popup').style.display='none'">
              &times;
            </button>
        </div>
        <?php unset($_SESSION['message'], $_SESSION['message_type']); ?>
    <?php endif; ?> 

    <!-- The main form that updates the consultation -->
    <form action="update_consultation.php" method="post">
        <input type="hidden" name="consultation_id" value="<?= htmlspecialchars($consultation_id); ?>">
        <input type="hidden" name="patient_id" value="<?= htmlspecialchars($patient_id); ?>">

        <!-- ... [Patient Information section remains the same] -->
        <div class="section">
            <h2>Patient Information</h2>
            <div class="plain-text">
                <strong>Patient ID:</strong> 
                <?= htmlspecialchars($patient['patient_id']); ?>
            </div>
            <div class="plain-text">
                <strong>Patient Full Name:</strong> 
                <?= htmlspecialchars($patient['first_name'] . ' ' . $patient['middle_name'] . ' ' . $patient['surname']); ?>
            </div>
            <div class="plain-text">
                <strong>Date of Birth:</strong> 
                <?= htmlspecialchars($patient['dob']); ?>
            </div>
            <div class="plain-text">
                <strong>Age:</strong> 
                <?= htmlspecialchars($patient['age'] ?? ''); ?>
            </div>
            <div class="plain-text">
                <strong>Gender:</strong> 
                <?= htmlspecialchars($patient['gender']); ?>
            </div>
            <div class="plain-text">
                <strong>Contact Information:</strong> 
                <?= htmlspecialchars($patient['telephone']); ?>
            </div>
        </div>
            
            <div class="section">
                <h2>Consultation Details</h2>
                <div class="plain-text"><strong>Date of Consultation:</strong> <?= htmlspecialchars($consultation['consultation_date']) ?></div>
                <div class="plain-text"><strong>Time of Consultation:</strong> <?= htmlspecialchars($consultation['consultation_time']) ?></div>
                <div class="plain-text"><strong>Doctor's Name:</strong> <?= htmlspecialchars($consultation['doctor_name']) ?></div>
                <div class="plain-text"><strong>Department:</strong> <?= htmlspecialchars($consultation['department']) ?></div>
                <input type="hidden" name="consultation_date" value="<?= htmlspecialchars($consultation['consultation_date']); ?>">
                <input type="hidden" name="consultation_time" value="<?= htmlspecialchars($consultation['consultation_time']); ?>">
            </div>

           

            <div class="section">
                <h2>Vital Signs</h2>
                <div class="form-group">
                    <span>
                        <label for="blood_pressure">Blood Pressure (mmHg):</label>
                        <input type="text" id="blood_pressure" name="blood_pressure" value="<?= htmlspecialchars($consultation['vital_signs_blood_pressure']) ?>" oninput="validateVitalSigns()" <?= !$isEditable ? 'readonly' : ''; ?>>
                    </span>
                    <span>
                        <label for="heart_rate">Heart Rate (bpm):</label>
                        <input type="text" id="heart_rate" name="heart_rate" value="<?= htmlspecialchars($consultation['vital_signs_heart_rate']) ?>" oninput="validateVitalSigns()" <?= !$isEditable ? 'readonly' : ''; ?>>
                    </span>
                </div>
                <div class="form-group">
                    <span>
                        <label for="respiratory_rate">Respiratory Rate (breaths/min):</label>
                        <input type="text" id="respiratory_rate" name="respiratory_rate" value="<?= htmlspecialchars($consultation['vital_signs_respiratory_rate']) ?>" oninput="validateVitalSigns()" <?= !$isEditable ? 'readonly' : ''; ?>>
                    </span>
                    <span>
                        <label for="temperature">Temperature (°C):</label>
                        <input type="text" id="temperature" name="temperature" value="<?= htmlspecialchars($consultation['vital_signs_temperature']) ?>" oninput="validateVitalSigns()" <?= !$isEditable ? 'readonly' : ''; ?>>
                    </span>
                </div>
                <div class="form-group">
                    <span>
                        <label for="weight">Weight (kg):</label>
                        <input type="text" id="weight" name="weight" value="<?= htmlspecialchars($consultation['vital_signs_weight']) ?>" oninput="calculateBMI()" <?= !$isEditable ? 'readonly' : ''; ?>>
                    </span>
                    <span>
                        <label for="height">Height (cm):</label>
                        <input type="text" id="height" name="height" value="<?= htmlspecialchars($consultation['vital_signs_height']) ?>" oninput="calculateBMI()" <?= !$isEditable ? 'readonly' : ''; ?>>
                    </span>
                </div>
                <div class="form-group">
                    <label for="bmi">BMI:</label>
                    <input type="text" id="bmi" name="bmi" value="<?= htmlspecialchars($consultation['vital_signs_bmi']) ?>" readonly>
                </div>
                <a href="#bmi-ranges-modal" class="btn" id="open-modal">View BMI Ranges</a>
            </div>
            
           
            <div class="section">
                <h2>Visit Details</h2>
                <div class="form-group">
                    <label for="reason_for_visit">Reason for Visit / Chief Complaint:</label>
                    <textarea id="reason_for_visit" name="reason_for_visit" <?= !$isEditable ? 'readonly' : ''; ?>><?= htmlspecialchars($consultation['reason_for_visit']) ?></textarea>
                </div>
                <div class="form-group">
                    <label for="history_of_illness">History of Present Illness:</label>
                    <textarea id="history_of_illness" name="history_of_illness" <?= !$isEditable ? 'readonly' : ''; ?>><?= htmlspecialchars($consultation['history_of_illness']) ?></textarea>
                </div>
                <div class="form-group">
                    <label for="past_medical_history">Past Medical History:</label>
                    <textarea id="past_medical_history" name="past_medical_history" <?= !$isEditable ? 'readonly' : ''; ?>><?= htmlspecialchars($consultation['past_medical_history']) ?></textarea>
                </div>
                <div class="form-group">
                    <label for="family_history">Family History:</label>
                    <textarea id="family_history" name="family_history" <?= !$isEditable ? 'readonly' : ''; ?>><?= htmlspecialchars($consultation['family_history']) ?></textarea>
                </div>
                <div class="form-group">
                    <label for="social_history">Social History:</label>
                    <textarea id="social_history" name="social_history" <?= !$isEditable ? 'readonly' : ''; ?>><?= htmlspecialchars($consultation['social_history']) ?></textarea>
                </div>
                <div class="form-group">
                    <label for="allergies">Allergies:</label>
                    <textarea id="allergies" name="allergies" <?= !$isEditable ? 'readonly' : ''; ?>><?= htmlspecialchars($consultation['allergies']) ?></textarea>
                </div>
                <div class="form-group">
                    <label for="initial_diagnosis">Initial Diagnosis:</label>
                    <textarea id="initial_diagnosis" name="initial_diagnosis" <?= !$isEditable ? 'readonly' : ''; ?>><?= htmlspecialchars($consultation['initial_diagnosis']) ?></textarea>
                </div>
                <div class="form-group">
                    <label for="final_diagnosis">Final Diagnosis:</label>
                    <textarea id="final_diagnosis" name="final_diagnosis" <?= !$isEditable ? 'readonly' : ''; ?>><?= htmlspecialchars($consultation['final_diagnosis']) ?></textarea>
                </div>
            </div>

            <div class="section">
                <h2>Physical Examination</h2>
                <div class="form-group">
                    <label for="general_appearance">General Appearance:</label>
                    <textarea id="general_appearance" name="general_appearance"><?= htmlspecialchars($consultation['physical_examination_general_appearance']) ?></textarea>
                </div>
                <div class="form-group">
                    <label for="heent">HEENT:</label>
                    <textarea id="heent" name="heent"><?= htmlspecialchars($consultation['physical_examination_heent']) ?></textarea>
                </div>
                <div class="form-group">
                    <label for="cardiovascular">Cardiovascular:</label>
                    <textarea id="cardiovascular" name="cardiovascular"><?= htmlspecialchars($consultation['physical_examination_cardiovascular']) ?></textarea>
                </div>
                <div class="form-group">
                    <label for="respiratory">Respiratory:</label>
                    <textarea id="respiratory" name="respiratory"><?= htmlspecialchars($consultation['physical_examination_respiratory']) ?></textarea>
                </div>
                <div class="form-group">
                    <label for="gastrointestinal">Gastrointestinal:</label>
                    <textarea id="gastrointestinal" name="gastrointestinal"><?= htmlspecialchars($consultation['physical_examination_gastrointestinal']) ?></textarea>
                </div>
                <div class="form-group">
                    <label for="musculoskeletal">Musculoskeletal:</label>
                    <textarea id="musculoskeletal" name="musculoskeletal"><?= htmlspecialchars($consultation['physical_examination_musculoskeletal']) ?></textarea>
                </div>
                <div class="form-group">
                    <label for="neurological">Neurological:</label>
                    <textarea id="neurological" name="neurological"><?= htmlspecialchars($consultation['physical_examination_neurological']) ?></textarea>
                </div>
                <div class="form-group">
                    <label for="skin">Skin:</label>
                    <textarea id="skin" name="skin"><?= htmlspecialchars($consultation['physical_examination_skin']) ?></textarea>
                </div>
            </div>
            
         <!-- Laboratory Tests (the "Search and Add" part) -->
         <div class="section">
            <h2>Laboratory Tests and Results</h2>
            <div class="form-group">
                <label for="lab_test_search">Search and Add Lab Tests:</label>
                <input type="text" id="lab_test_search" placeholder="Search for a lab test...">
                <select id="lab_test_dropdown">
                    <option value="">Select a lab test</option>
                    <?php foreach ($laboratory_tests as $test): ?>
                        <option value="<?= $test['id'] ?>">
                            <?= htmlspecialchars($test['test_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <button type="button" class="add-button" onclick="addLabTest()">Add</button>
                <div class="selected-items" id="selected_lab_tests">
                  <!-- Show currently selected lab tests -->
                  <?php if (!empty($selectedLabTests)): ?>
                    <?php foreach ($selectedLabTests as $slab): ?>
                      <div>
                        <?= htmlspecialchars($slab['test_name']); ?>
                      </div>
                    <?php endforeach; ?>
                  <?php endif; ?>
                </div>
                <!-- We'll store final IDs in this hidden input: -->
                <input type="hidden" id="lab_tests" name="lab_tests" 
                       value="<?= htmlspecialchars($consultation['lab_tests']); ?>">
            </div>
        </div>
        <div class="section" style="margin-top:30px;">
      <h2>Requested Laboratory Tests (Status)</h2>
      <?php if (count($labRequests) === 0): ?>
        <p>No lab requests found for this consultation.</p>
      <?php else: ?>
        <div class="table-responsive">
          <table class="table table-bordered table-hover align-middle">
            <thead>
              <tr>
                <th>Lab Request ID</th>
                <th>Template Name</th>
                <th>Status</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($labRequests as $r): ?>
                <?php
                  $reqId   = (int)$r['request_id'];
                  $tName   = $r['template_name'] ?: 'Untitled Template #'.$r['template_id'];
                  $status  = $r['status'];
                  $content = $r['result_content'] ?: ''; // If completed
                  // We'll Base64-encode to keep it safe in a data-attribute:
                  $encoded = base64_encode($content);
                ?>
                <tr>
                  <td><?= $reqId; ?></td>
                  <td><?= htmlspecialchars($tName); ?></td>
                  <td><?= htmlspecialchars($status); ?></td>
                  <td>
                    <?php if ($status === 'Completed' && !empty($content)): ?>
                      <!-- Show "View" button with data attributes -->
                      <button type="button" 
        class="btn btn-sm btn-info"
        data-bs-toggle="modal"
        data-bs-target="#viewModal"
        data-lab-content="<?= $encoded; ?>">
  View
</button>


                    <?php else: ?>
                      <!-- If not completed, or no content, show disabled or blank -->
                      <button type="button" class="btn btn-sm btn-secondary" disabled>
                        Not Available
                      </button>
                    <?php endif; ?>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
    </div>
            <!-- Treatment Plan -->
            <div class="section">
                <h2>Treatment Plan</h2>
                <div class="form-group">
                    <label for="medication_search">Search and Add Medications:</label>
                    <input type="text" id="medication_search" placeholder="Search for a medication...">
                    <select id="medication_dropdown">
                        <option value="">Select a medication</option>
                        <?php foreach ($availableMedications as $medication): ?>
                            <option value="<?= $medication['id'] ?>"><?= $medication['name'] ?></option>
                        <?php endforeach; ?>
                    </select>
                    <input type="text" id="dosage" placeholder="Enter Dosage">
                    <button type="button" class="add-button" onclick="addMedication()">Add</button>
                    <div class="selected-items" id="selected_medications"></div>
                    <!-- Hidden inputs will be added dynamically -->
                </div>
                <div class="form-group">
                    <label for="other_prescriptions">Other Prescriptions and Notes:</label>
                    <textarea id="other_prescriptions" name="other_prescriptions"><?= htmlspecialchars($consultation['other_prescriptions']) ?></textarea>
                </div>
            </div>

            <div class="section">
                <h2>Follow-Up Instructions</h2>
                <div class="form-group">
                    <label for="follow_up_date">Follow-Up Appointment Date:</label>
                    <input type="date" id="follow_up_date" name="follow_up_date" value="<?= htmlspecialchars($consultation['follow_up_date']) ?>">
                </div>
                <div class="form-group">
                    <label for="symptoms_to_watch">Symptoms to Watch For:</label>
                    <textarea id="symptoms_to_watch" name="symptoms_to_watch"><?= htmlspecialchars($consultation['symptoms_to_watch']) ?></textarea>
                </div>
                <div class="form-group">
                    <label for="emergency_instructions">Emergency Instructions:</label>
                    <textarea id="emergency_instructions" name="emergency_instructions"><?= htmlspecialchars($consultation['emergency_instructions']) ?></textarea>
                </div>
                <div class="form-group">
                    <label for="education_materials">Patient Education Materials Provided:</label>
                    <textarea id="education_materials" name="education_materials"><?= htmlspecialchars($consultation['education_materials']) ?></textarea>
                </div>
            </div>
            
            <div class="section">
                <h2>Additional Notes</h2>
                <div class="form-group">
                    <label for="doctor_comments">Doctor's Additional Comments:</label>
                    <textarea id="doctor_comments" name="doctor_comments"><?= htmlspecialchars($consultation['doctor_comments']) ?></textarea>
                </div>
                <div class="form-group">
                    <label for="patient_concerns">Patient's Questions and Concerns:</label>
                    <textarea id="patient_concerns" name="patient_concerns"><?= htmlspecialchars($consultation['patient_concerns']) ?></textarea>
                </div>
            </div>
            
            <div class="section">
                <h2>Signature</h2>
                <div class="plain-text"><strong>Doctor's Signature:</strong> <?= htmlspecialchars($consultation['doctor_name']) ?></div>
                <div class="plain-text"><strong>Date:</strong> <?= htmlspecialchars($consultation['consultation_date']) ?></div>
            </div>
            
            <button type="submit" class="btns btn-primary">Update Consultation</button>
        </form>
    </div>

    <div id="bmi-ranges-modal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2>BMI Ranges</h2>
            <table>
                <tr>
                    <th>Category</th>
                    <th>BMI Range</th>
                    <th>Color</th>
                </tr>
                <tr>
                    <td>Underweight</td>
                    <td>&lt; 18.5</td>
                    <td style="background-color: lightblue;">Light Blue</td>
                </tr>
                <tr>
                    <td>Normal weight</td>
                    <td>18.5 - 24.9</td>
                    <td style="background-color: lightgreen;">Light Green</td>
                </tr>
                <tr>
                    <td>Overweight</td>
                    <td>25 - 29.9</td>
                    <td style="background-color: yellow;">Yellow</td>
                </tr>
                <tr>
                    <td>Obesity</td>
                    <td>&ge; 30</td>
                    <td style="background-color: red;">Red</td>
                </tr>
            </table>
        </div>
    </div>
    <!-- Modal for Viewing Lab Test Results -->
<div class="modal fade" id="labResultModal" tabindex="-1" aria-labelledby="labResultModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="labResultModalLabel">Lab Test Result</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="modal-content-area">
                <!-- Content will be dynamically inserted -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

    <script>

    
       // Medications
       const availableMedications = <?= json_encode($availableMedications) ?>;
        const selectedMedications = <?= json_encode($selectedMedications) ?> || [];

        function initializeSelectedMedications() {
            const selectedMedicationsDiv = document.getElementById('selected_medications');

            selectedMedications.forEach(function(medicationEntry) {
                const medicationDiv = document.createElement('div');
                medicationDiv.classList.add('medication-entry');

                const medicationIdInput = document.createElement('input');
                medicationIdInput.type = 'hidden';
                medicationIdInput.name = 'medication_ids[]';
                medicationIdInput.value = medicationEntry.id;

                const medicationNameInput = document.createElement('input');
                medicationNameInput.type = 'text';
                medicationNameInput.name = 'medication_names[]';
                medicationNameInput.value = medicationEntry.name;
                medicationNameInput.readOnly = true;

                const medicationDosageInput = document.createElement('input');
                medicationDosageInput.type = 'text';
                medicationDosageInput.name = 'medication_dosages[]';
                medicationDosageInput.value = medicationEntry.dosage;
                medicationDosageInput.readOnly = true;

                const removeButton = document.createElement('button');
                removeButton.type = 'button';
                removeButton.innerText = 'Remove';
                removeButton.onclick = function() {
                    removeMedication(medicationEntry.id, medicationDiv);
                };

                medicationDiv.appendChild(medicationIdInput);
                medicationDiv.appendChild(medicationNameInput);
                medicationDiv.appendChild(medicationDosageInput);
                medicationDiv.appendChild(removeButton);

                selectedMedicationsDiv.appendChild(medicationDiv);
            });
        }

        function addMedication() {
            const medicationDropdown = document.getElementById('medication_dropdown');
            const dosageInput = document.getElementById('dosage');
            const selectedMedicationsDiv = document.getElementById('selected_medications');

            const selectedValue = medicationDropdown.value;
            const selectedText = medicationDropdown.options[medicationDropdown.selectedIndex].text;
            const dosage = dosageInput.value.trim();

            if (selectedValue && dosage && !selectedMedications.some(med => med.id === parseInt(selectedValue))) {
                const medicationEntry = {
                    id: parseInt(selectedValue),
                    name: selectedText,
                    dosage: dosage
                };

                selectedMedications.push(medicationEntry);

                const medicationDiv = document.createElement('div');
                medicationDiv.classList.add('medication-entry');

                const medicationIdInput = document.createElement('input');
                medicationIdInput.type = 'hidden';
                medicationIdInput.name = 'medication_ids[]';
                medicationIdInput.value = medicationEntry.id;

                const medicationNameInput = document.createElement('input');
                medicationNameInput.type = 'text';
                medicationNameInput.name = 'medication_names[]';
                medicationNameInput.value = medicationEntry.name;
                medicationNameInput.readOnly = true;

                const medicationDosageInput = document.createElement('input');
                medicationDosageInput.type = 'text';
                medicationDosageInput.name = 'medication_dosages[]';
                medicationDosageInput.value = medicationEntry.dosage;
                medicationDosageInput.readOnly = true;

                const removeButton = document.createElement('button');
                removeButton.type = 'button';
                removeButton.innerText = 'Remove';
                removeButton.onclick = function() {
                    removeMedication(medicationEntry.id, medicationDiv);
                };

                medicationDiv.appendChild(medicationIdInput);
                medicationDiv.appendChild(medicationNameInput);
                medicationDiv.appendChild(medicationDosageInput);
                medicationDiv.appendChild(removeButton);

                selectedMedicationsDiv.appendChild(medicationDiv);

                medicationDropdown.selectedIndex = 0;
                dosageInput.value = '';
            }
        }

        function removeMedication(medicationId, medicationDiv) {
            const index = selectedMedications.findIndex(med => med.id === medicationId);
            if (index > -1) {
                selectedMedications.splice(index, 1);
            }

            medicationDiv.remove();
        }

        // Lab Tests
        const selectedLabTests = <?= json_encode($selectedLabTests) ?> || [];

        function initializeSelectedLabTests() {
            const selectedLabTestsDiv = document.getElementById('selected_lab_tests');
            selectedLabTests.forEach(function(test) {
                const div = document.createElement('div');
                div.innerHTML = `${test.test_name} <button type="button" onclick="removeLabTest('${test.id}', this)">Remove</button>`;
                selectedLabTestsDiv.appendChild(div);
            });
            // Set the hidden input value
            const labTestIds = selectedLabTests.map(test => test.id);
            document.getElementById('lab_tests').value = labTestIds.join(',');
        }

        function addLabTest() {
            const labTestDropdown = document.getElementById('lab_test_dropdown');
            const selectedLabTestsDiv = document.getElementById('selected_lab_tests');
            const selectedValue = labTestDropdown.value;
            const selectedText = labTestDropdown.options[labTestDropdown.selectedIndex].text;

            if (selectedValue && !selectedLabTests.some(test => test.id === selectedValue)) {
                selectedLabTests.push({ id: selectedValue, test_name: selectedText });
                const div = document.createElement('div');
                div.innerHTML = `${selectedText} <button type="button" onclick="removeLabTest('${selectedValue}', this)">Remove</button>`;
                selectedLabTestsDiv.appendChild(div);
                const labTestIds = selectedLabTests.map(test => test.id);
                document.getElementById('lab_tests').value = labTestIds.join(',');
            }
        }
        // Event delegation to bind click event to dynamically generated buttons
document.addEventListener('click', function (event) {
    if (event.target.matches('button[data-bs-target="#labResultModal"]')) {
        const content = atob(event.target.getAttribute('data-lab-content')); // Decode Base64
        document.getElementById('modal-content-area').textContent = content;
    }
});
console.log(bootstrap);



        function removeLabTest(value, button) {
            const index = selectedLabTests.findIndex(test => test.id === value);
            if (index > -1) {
                selectedLabTests.splice(index, 1);
                button.parentElement.remove();
                const labTestIds = selectedLabTests.map(test => test.id);
                document.getElementById('lab_tests').value = labTestIds.join(',');
            }
        }
        // Autocomplete functionality
        window.onload = function() {
    initializeSelectedMedications();
    initializeSelectedLabTests(); // If you have this function

    // Autocomplete event listeners
    const labTestSearch = document.getElementById('lab_test_search');
    const medicationSearch = document.getElementById('medication_search');

    if (labTestSearch) {
        labTestSearch.addEventListener('input', function() {
            autocomplete(this, 'lab_tests');
        });
    }

    if (medicationSearch) {
        medicationSearch.addEventListener('input', function() {
            autocomplete(this, 'medications');
        });
    }
};


        function autocomplete(input, type) {
    let list;
    if (type === 'lab_tests') {
        list = <?= json_encode($laboratory_tests) ?>;
    } else if (type === 'medications') {
        list = availableMedications;
    } else {
        list = [];
    }

    const value = input.value.toLowerCase();
    let suggestions = list.filter(item => {
        const itemName = item.test_name || item.name;
        return itemName.toLowerCase().includes(value);
    });
    
    if (suggestions.length > 0) {
        input.setAttribute('list', type + '_suggestions');
        let dataList = document.getElementById(type + '_suggestions');
        if (!dataList) {
            dataList = document.createElement('datalist');
            dataList.id = type + '_suggestions';
            document.body.appendChild(dataList);
        }
        dataList.innerHTML = suggestions.map(item => {
            const itemName = item.test_name || item.name;
            return `<option value="${itemName}">`;
        }).join('');
    } else {
        input.removeAttribute('list');
    }
}


        function validateVitalSigns() {
            const bloodPressure = document.getElementById('blood_pressure').value;
            const heartRate = parseFloat(document.getElementById('heart_rate').value);
            const respiratoryRate = parseFloat(document.getElementById('respiratory_rate').value);
            const temperature = parseFloat(document.getElementById('temperature').value);

            // Validate Blood Pressure
            if (/^\d+\/\d+$/.test(bloodPressure)) {
                const [systolic, diastolic] = bloodPressure.split('/').map(Number);
                if (systolic < 120 && diastolic < 80) {
                    document.getElementById('blood_pressure').style.backgroundColor = 'lightgreen';
                } else if ((systolic >= 120 && systolic < 130) && diastolic < 80) {
                    document.getElementById('blood_pressure').style.backgroundColor = 'yellow';
                } else if ((systolic >= 130 && systolic < 140) || (diastolic >= 80 && diastolic < 90)) {
                    document.getElementById('blood_pressure').style.backgroundColor = 'orange';
                } else if (systolic >= 140 || diastolic >= 90) {
                    document.getElementById('blood_pressure').style.backgroundColor = 'red';
                } else {
                    document.getElementById('blood_pressure').style.backgroundColor = '';
                }
            } else {
                document.getElementById('blood_pressure').style.backgroundColor = '';
            }

            // Validate Heart Rate
            if (!isNaN(heartRate)) {
                if (heartRate >= 60 && heartRate <= 100) {
                    document.getElementById('heart_rate').style.backgroundColor = 'lightgreen';
                } else {
                    document.getElementById('heart_rate').style.backgroundColor = 'red';
                }
            } else {
                document.getElementById('heart_rate').style.backgroundColor = '';
            }

            // Validate Respiratory Rate
            if (!isNaN(respiratoryRate)) {
                if (respiratoryRate >= 12 && respiratoryRate <= 20) {
                    document.getElementById('respiratory_rate').style.backgroundColor = 'lightgreen';
                } else {
                    document.getElementById('respiratory_rate').style.backgroundColor = 'red';
                }
            } else {
                document.getElementById('respiratory_rate').style.backgroundColor = '';
            }

            // Validate Temperature
            if (!isNaN(temperature)) {
                if (temperature >= 36.1 && temperature <= 37.2) {
                    document.getElementById('temperature').style.backgroundColor = 'lightgreen';
                } else {
                    document.getElementById('temperature').style.backgroundColor = 'red';
                }
            } else {
                document.getElementById('temperature').style.backgroundColor = '';
            }
        }

        // Modal functionality
        var modal = document.getElementById("bmi-ranges-modal");
        var btn = document.getElementById("open-modal");
        var span = document.getElementsByClassName("close")[0];

        btn.onclick = function() {
            modal.style.display = "block";
        }

        span.onclick = function() {
            modal.style.display = "none";
        }

        window.onclick = function(event) {
            if (event.target == modal) {
                modal.style.display = "none";
            }
        }

        function calculateBMI() {
            const weight = parseFloat(document.getElementById('weight').value);
            const heightCm = parseFloat(document.getElementById('height').value);
            const bmiInput = document.getElementById('bmi');
            
            if (!isNaN(weight) && !isNaN(heightCm) && heightCm > 0) {
                const heightM = heightCm / 100;
                const bmi = weight / (heightM * heightM);
                bmiInput.value = bmi.toFixed(2);
                updateBMIColor(bmi);
            } else {
                bmiInput.value = '';
                bmiInput.style.backgroundColor = '';
            }
        }

        function updateBMIColor(bmi) {
            const bmiInput = document.getElementById('bmi');
            
            if (bmi < 18.5) {
                bmiInput.style.backgroundColor = 'lightblue';
            } else if (bmi >= 18.5 && bmi <= 24.9) {
                bmiInput.style.backgroundColor = 'lightgreen';
            } else if (bmi >= 25 && bmi <= 29.9) {
                bmiInput.style.backgroundColor = 'yellow';
            } else if (bmi >= 30) {
                bmiInput.style.backgroundColor = 'red';
            } else {
                bmiInput.style.backgroundColor = '';
            }
        }
       

        //message timeout
        const messagePopup = document.getElementById('message-popup');
        if (messagePopup) {
            messagePopup.style.display = 'block';
            setTimeout(function() {
                messagePopup.style.display = 'none';
            }, 12000);
        }
    </script>
</body>
</html>
