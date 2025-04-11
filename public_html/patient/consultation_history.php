<?php
include 'init.php';
include 'config.php';
include 'access_control.php';
include '../sidebar.php';

// Fetch patient ID from URL
$patient_id = isset($_GET['id']) ? $_GET['id'] : 0;

// Prepare and execute the query to fetch patient details
$sql_patient = "SELECT patient_id, first_name, middle_name, surname, dob, age, gender, telephone FROM patient_db WHERE patient_id = ?";
$stmt_patient = $conn->prepare($sql_patient);
$stmt_patient->bind_param("s", $patient_id);
$stmt_patient->execute();
$result_patient = $stmt_patient->get_result();

if ($result_patient->num_rows > 0) {
    $patient = $result_patient->fetch_assoc();
} else {
    die("No patient found with ID: " . htmlspecialchars($patient_id));
}

// Assuming you have a function to get the test name from its ID
function getLabTestName($test_id, $conn) {
    $sql = "SELECT test_name FROM lab_tests WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $test_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        return $result->fetch_assoc()['test_name'];
    } else {
        return "Unknown Test";
    }
}

// Pagination setup
$limit = 10; // 10 records per page
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Sorting and date filter
$sort_option = isset($_GET['sort']) ? $_GET['sort'] : 'latest';
$date_filter = isset($_GET['date']) ? $_GET['date'] : null;

// Prepare query for consultation history with pagination
$sql = "SELECT consultation_id, final_diagnosis, created_at, department, doctor_name, doctor_comments, 
        reason_for_visit, history_of_illness, vital_signs_blood_pressure, vital_signs_heart_rate, 
        vital_signs_respiratory_rate, vital_signs_temperature, vital_signs_weight, vital_signs_height, 
        vital_signs_bmi, physical_examination_general_appearance, physical_examination_heent, 
        physical_examination_cardiovascular, physical_examination_respiratory, physical_examination_gastrointestinal, 
        physical_examination_musculoskeletal, physical_examination_neurological, physical_examination_skin, 
        lab_tests, medications, medication_names, medication_dosages, follow_up_date, emergency_instructions 
        FROM consultations 
        WHERE patient_id = ?";

// Apply date filter if provided
if ($date_filter) {
    $sql .= " AND DATE(created_at) = ?";
}

// Apply sorting based on selection
if ($sort_option === 'latest') {
    $sql .= " ORDER BY created_at DESC";
} elseif ($sort_option === 'oldest') {
    $sql .= " ORDER BY created_at ASC";
}

// Add the sanitized LIMIT and OFFSET values
$sql .= " LIMIT $limit OFFSET $offset";

// Prepare and execute the statement
if ($date_filter) {
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $patient_id, $date_filter);
} else {
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $patient_id);
}
$stmt->execute();
$result = $stmt->get_result();

// Fetch total records for pagination
$sql_count = "SELECT COUNT(*) as total FROM consultations WHERE patient_id = ?";

if ($date_filter) {
    $sql_count .= " AND DATE(created_at) = ?";
    $stmt_count = $conn->prepare($sql_count);
    $stmt_count->bind_param("ss", $patient_id, $date_filter);
} else {
    $stmt_count = $conn->prepare($sql_count);
    $stmt_count->bind_param("s", $patient_id);
}

$stmt_count->execute();
$result_count = $stmt_count->get_result();
$total_records = $result_count->fetch_assoc()['total'];
$total_pages = ceil($total_records / $limit);

 
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Consultation History - <?= htmlspecialchars($patient['first_name'] . ' ' . $patient['surname']); ?></title>
    <link rel="stylesheet" href="../css/patient_record.css">
    <link rel="stylesheet" href="patient.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .dash-body {
            width: 100vw;
            padding: 20px;
        }

        .profile-header.custom-profile, .header-container {
            width: 100%;
           
        }

        .details-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            font-size: 14px;
        }

        .details-table th, .details-table td {
            border: 1px solid #dee2e6;
            padding: 12px;
            text-align: left;
        }

        .details-table th {
            background-color: #f8f9fa;
            font-weight: bold;
        }

        .details-table tr:hover {
            background-color: #f1f1f1;
        }

        .btn-view {
            padding: 5px 10px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        .sort-filter {
            margin-bottom: 20px;
        }

        .pagination {
            margin-top: 20px;
        }

        .pagination a {
            margin: 0 5px;
            padding: 8px 12px;
            background-color: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 4px;
        }

        .pagination a:hover {
            background-color: #0056b3;
        }

        .pagination .active {
            background-color: #0056b3;
        }

        .modal-body {
            font-size: 14px;
        }
    </style>
</head>
<body>
<div class="dash-body">
<div class="profile-header custom-profile">
            <div class="profile-details">
                <div class="header-container">
                <h1 class="header-name">Consultation History</h1>
                    <h1 class="header-title"><?php echo htmlspecialchars($patient['first_name'] . ' ' . $patient['surname']); ?></h1>
                    <div class="profile-picture">
                    <?php
include 'patient_profile_picture.php';

// Assuming you have fetched the patient data into $patientData

displayPatientProfilePicture($patient);
?>
                    </div>
                </div>
                <div class="nav-links">
                    <a href="patient_record.php?id=<?= $patient_id ?>">Profile</a>
                    <a href="edit_record.php?id=<?= $patient_id ?>">Edit Record</a>
                    <a href="consultation.php?id=<?= $patient_id ?>">Consultation</a>
                    <a href="consultation_history.php?id=<?= $patient_id ?>">Consultation History</a>
                    <a href="prescriptions.php?id=<?= $patient_id ?>">Prescriptions</a>
                    <a href="../appointment_manager/book_appointment.php?id=<?= $patient_id ?>">Appointments</a>
                </div>
            </div>
        </div>

        <div class="sort-filter">
            <form method="GET" action="consultation_history.php">
                <input type="hidden" name="id" value="<?= htmlspecialchars($patient_id); ?>">
                <select name="sort" onchange="this.form.submit()">
                    <option value="latest" <?= $sort_option === 'latest' ? 'selected' : ''; ?>>Latest</option>
                    <option value="oldest" <?= $sort_option === 'oldest' ? 'selected' : ''; ?>>Oldest</option>
                </select>
                <input type="date" name="date" value="<?= isset($date_filter) ? $date_filter : ''; ?>" max="<?= date('Y-m-d'); ?>" onchange="this.form.submit()">
            </form>
        </div>

        <?php if ($result->num_rows > 0): ?>
            <table class="details-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Final Diagnosis</th>
                        <th>Staff Attended</th>
                        <th>Department</th>
                        <th>Doctor's Comments</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['created_at']); ?></td>
                            <td><?= htmlspecialchars($row['final_diagnosis']); ?></td>
                            <td><?= htmlspecialchars($row['doctor_name']); ?></td>
                            <td><?= htmlspecialchars($row['department']); ?></td>
                            <td><?= htmlspecialchars($row['doctor_comments']); ?></td>
                            <td>
                                <button class="btn-view btn btn-primary" data-bs-toggle="modal" data-bs-target="#consultationModal-<?= $row['consultation_id']; ?>">View</button>
                            </td>
                        </tr>
                       

                        <!-- Modal -->
                        <div class="modal fade" id="consultationModal-<?= $row['consultation_id']; ?>" tabindex="-1" aria-labelledby="consultationModalLabel" aria-hidden="true">
                            <div class="modal-dialog modal-lg">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="consultationModalLabel">Consultation Details</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <!-- Patient Details -->
                <h6><strong>Patient Details:</strong></h6>
                <ul>
                    <li><strong>Patient ID:</strong> <?= htmlspecialchars($patient['patient_id']); ?></li>
                    <li><strong>Full Name:</strong> <?= htmlspecialchars($patient['first_name'] . ' ' . $patient['middle_name'] . ' ' . $patient['surname']); ?></li>
                    <li><strong>Date of Birth:</strong> <?= htmlspecialchars($patient['dob']); ?></li>
                    <li><strong>Age:</strong> <?= htmlspecialchars($patient['age']); ?></li>
                    <li><strong>Gender:</strong> <?= htmlspecialchars($patient['gender']); ?></li>
                    <li><strong>Contact Information:</strong> <?= htmlspecialchars($patient['telephone']); ?></li>
                
                </ul>
                                        <h6><strong>Date:</strong> <?= htmlspecialchars($row['created_at']); ?></h6>
                                        <h6><strong>Reason for Visit:</strong> <?= htmlspecialchars($row['reason_for_visit']); ?></h6>
                                        <h6><strong>History of Illness:</strong> <?= htmlspecialchars($row['history_of_illness']); ?></h6>
                                        <h6><strong>Final Diagnosis:</strong> <?= htmlspecialchars($row['final_diagnosis']); ?></h6>
                                        <h6><strong>Vital Signs:</strong></h6>
                                        <ul>
                                            <li>Blood Pressure: <?= htmlspecialchars($row['vital_signs_blood_pressure']); ?></li>
                                            <li>Heart Rate: <?= htmlspecialchars($row['vital_signs_heart_rate']); ?></li>
                                            <li>Respiratory Rate: <?= htmlspecialchars($row['vital_signs_respiratory_rate']); ?></li>
                                            <li>Temperature: <?= htmlspecialchars($row['vital_signs_temperature']); ?></li>
                                            <li>Weight: <?= htmlspecialchars($row['vital_signs_weight']); ?></li>
                                            <li>Height: <?= htmlspecialchars($row['vital_signs_height']); ?></li>
                                            <li>BMI: <?= htmlspecialchars($row['vital_signs_bmi']); ?></li>
                                        </ul>
                                        <h6><strong>Physical Examination:</strong></h6>
                                        <ul>
                                            <li>General Appearance: <?= htmlspecialchars($row['physical_examination_general_appearance']); ?></li>
                                            <li>HEENT: <?= htmlspecialchars($row['physical_examination_heent']); ?></li>
                                            <li>Cardiovascular: <?= htmlspecialchars($row['physical_examination_cardiovascular']); ?></li>
                                            <li>Respiratory: <?= htmlspecialchars($row['physical_examination_respiratory']); ?></li>
                                            <li>Gastrointestinal: <?= htmlspecialchars($row['physical_examination_gastrointestinal']); ?></li>
                                            <li>Musculoskeletal: <?= htmlspecialchars($row['physical_examination_musculoskeletal']); ?></li>
                                            <li>Neurological: <?= htmlspecialchars($row['physical_examination_neurological']); ?></li>
                                            <li>Skin: <?= htmlspecialchars($row['physical_examination_skin']); ?></li>
                                        </ul>
                                        <h6><strong>Lab Tests Ordered:</strong></h6>
<ul>
    <?php
    if (!empty($row['lab_tests'])) {
        $lab_test_ids = explode(',', $row['lab_tests']);
        // If you have a mapping of test IDs to names, you can fetch the names here
        foreach ($lab_test_ids as $test_id):
            // Fetch the test name based on $test_id, or just display the ID
            $test_name = htmlspecialchars($test_id); // Replace with actual test name if available
    ?>
        <li><?= $test_name; ?></li>
    <?php
        endforeach;
    } else {
        echo "<li>No lab tests ordered.</li>";
    }
    ?>
</ul>

<h6><strong>Medications Prescribed:</strong></h6>
<ul>
    <?php
    if (!empty($row['medication_names']) && !empty($row['medication_dosages'])) {
        $medication_names = explode(',', $row['medication_names']);
        $medication_dosages = explode(',', $row['medication_dosages']);
        $med_count = count($medication_names);

        // Ensure that the arrays have the same length
        if ($med_count === count($medication_dosages)) {
            for ($i = 0; $i < $med_count; $i++):
    ?>
                <li><?= htmlspecialchars($medication_names[$i]) . " - " . htmlspecialchars($medication_dosages[$i]); ?></li>
    <?php
            endfor;
        } else {
            echo "<li>Medication data is corrupted.</li>";
        }
    } else {
        echo "<li>No medications prescribed.</li>";
    }
    ?>
</ul>
<!-- Fetch Update History for this Consultation -->
<?php
                    $consultation_id = $row['consultation_id'];
                    $history_sql = "SELECT updated_at, updated_by, update_notes FROM consultation_updates WHERE consultation_id = ? ORDER BY updated_at DESC";
                    $history_stmt = $conn->prepare($history_sql);
                    $history_stmt->bind_param("i", $consultation_id);
                    $history_stmt->execute();
                    $history_result = $history_stmt->get_result();
                    ?>

                    <!-- Update History Section -->
                    <h6><strong>Update History:</strong></h6>
<?php
$updates = array();
while ($history_row = $history_result->fetch_assoc()) {
    $updates[] = $history_row;
}
$history_result->close();
$history_stmt->close();

if (count($updates) > 0):
    // Collect unique updated_by values
    $updated_bys = array_unique(array_column($updates, 'updated_by'));
    if (count($updated_bys) === 1):
?>
    <p><strong>Updated by:</strong> <?= htmlspecialchars($updated_bys[0]); ?></p>
<?php endif; ?>
    <ul style="list-style-type: disc; padding-left: 20px;">
        <?php foreach ($updates as $update): ?>
            <li>
                <p><strong>Date:</strong> <?= htmlspecialchars($update['updated_at']); ?></p>
                <p><?= nl2br(htmlspecialchars($update['update_notes'])); ?></p>
                <?php if (count($updated_bys) > 1): ?>
                    <p><em>Updated by <?= htmlspecialchars($update['updated_by']); ?></em></p>
                <?php endif; ?>
            </li>
        <?php endforeach; ?>
    </ul>
<?php else: ?>
    <p>No updates have been made to this consultation.</p>
<?php endif; ?>


                    <!-- Doctor's Comments -->
                    <h6><strong>Doctor's Comments:</strong></h6>
                    <p><?= htmlspecialchars($row['doctor_comments']); ?></p>
                    <h6><strong>Follow-Up Date:</strong> <?= htmlspecialchars($row['follow_up_date']); ?></h6>
                    <h6><strong>Emergency Instructions:</strong> <?= htmlspecialchars($row['emergency_instructions']); ?></h6>
                </div>

                <!-- Modal Footer -->
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
<?php endwhile; ?>
            <!-- Pagination Links -->
            <div class="pagination">
                <?php if ($page > 1): ?>
                    <a href="?id=<?= $patient_id ?>&page=<?= $page - 1 ?>&sort=<?= $sort_option ?>&date=<?= $date_filter ?>">Previous</a>
                <?php endif; ?>

                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <a href="?id=<?= $patient_id ?>&page=<?= $i ?>&sort=<?= $sort_option ?>&date=<?= $date_filter ?>" class="<?= ($i == $page) ? 'active' : ''; ?>"><?= $i ?></a>
                <?php endfor; ?>

                <?php if ($page < $total_pages): ?>
                    <a href="?id=<?= $patient_id ?>&page=<?= $page + 1 ?>&sort=<?= $sort_option ?>&date=<?= $date_filter ?>">Next</a>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <p>No consultation records found for this patient.</p>
        <?php endif; ?>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

</body>
</html>

<?php
$stmt->close();
$conn->close();
?>
