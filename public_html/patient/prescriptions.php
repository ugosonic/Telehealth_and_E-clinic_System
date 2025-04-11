<?php
include '../init.php';
include '../config.php';
include '../access_control.php';
include '../sidebar.php';

// Get the patient ID from the URL
$patient_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($patient_id > 0) {
    // Prepare and execute the query to fetch patient details
    $sql = "SELECT * FROM patient_db WHERE patient_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $patient_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $profilePicPath = isset($row['profile_pic']) ? $row['profile_pic'] : null;
    } else {
        $patient_id = 0; // Reset patient ID if not found
    }
}

// Pagination setup
$records_per_page = 10;
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$start_from = ($page - 1) * $records_per_page;

// Prepare the SQL query
$sql = "SELECT p.*, pt.first_name, pt.surname, pt.dob
        FROM prescriptions p
        LEFT JOIN patient_db pt ON p.patient_id = pt.patient_id";

// If a specific patient's prescriptions are requested, add a WHERE clause
if ($patient_id > 0) {
    $sql .= " WHERE p.patient_id = ?";
}

$sql .= " ORDER BY p.date_prescribed DESC, p.time_prescribed DESC
          LIMIT ?, ?";

// Prepare and execute the statement
$stmt = $conn->prepare($sql);

if ($patient_id > 0) {
    $stmt->bind_param('iii', $patient_id, $start_from, $records_per_page); // Bind patient ID if specific patient
} else {
    $stmt->bind_param('ii', $start_from, $records_per_page); // Only bind pagination if fetching all
}

$stmt->execute();
$result = $stmt->get_result();

// Fetch total records for pagination
$sql_total = "SELECT COUNT(*) AS total FROM prescriptions";
if ($patient_id > 0) {
    $sql_total .= " WHERE patient_id = ?";
}

$total_stmt = $conn->prepare($sql_total);

if ($patient_id > 0) {
    $total_stmt->bind_param('i', $patient_id);
}

$total_stmt->execute();
$total_result = $total_stmt->get_result();
$total_records = $total_result->fetch_assoc()['total'];
$total_pages = ceil($total_records / $records_per_page);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
     <!-- Bootstrap CSS -->
     <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="../css/patient_record.css">
    <link rel="stylesheet" href="patient.css">
    <title>Prescriptions</title>
    <style>
        
.navbar {
    position: fixed;
    top: 0;
    left: 100px; /* Start after the sidebar */
    width: calc(100% - 100px);
    z-index: 1000; /* Higher z-index */
}



        .navbar-brand {
            font-weight: bold;
        }

        .nav-link.active {
            font-weight: bold;
        }
        .main-content {
    margin-top: 56px; /* Height of the navbar */
    margin-left: 100px; /* Width of the sidebar */
    padding: 20px;
}

        .prescription-container {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            justify-content: space-around;
            padding: 20px;
        }

        .prescription-card {
            background-color: #f5f5f5;
            border: 1px solid #ddd;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            padding: 15px;
            width: calc(33.333% - 30px);
            box-sizing: border-box;
            transition: transform 0.3s ease;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            cursor: pointer;
        }

        .prescription-card:hover {
            transform: scale(1.05);
        }

        .card-header {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 10px;
        }

        .card-content {
            flex-grow: 1;
        }

        .card-footer {
            margin-top: 10px;
        }

        .status-pending {
            background-color: #ffe6e6;
        }

        .status-in-progress {
            background-color: #fff5cc;
        }

        .status-completed {
            background-color: #d9f2d9;
        }

        .status-cancelled {
            background-color: #e0e0e0;
        }

        .prescription-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-top: 1px solid #ddd;
            padding-top: 10px;
            margin-top: 10px;
        }

        .view-link {
            color: #007bff;
            text-decoration: none;
            font-weight: bold;
        }

        .view-link:hover {
            text-decoration: underline;
        }

        .pagination {
            text-align: center;
            margin: 20px 0;
        }

        .pagination a {
            color: #007bff;
            margin: 0 5px;
            text-decoration: none;
        }

        .pagination a:hover {
            text-decoration: underline;
        }

    </style>
</head>
<body>

    <div class="dash-body">
        <?php if ($patient_id > 0): ?>
            <div class="profile-header custom-profile">
                <div class="profile-details">
                    
                    <div class="header-container">
                    <h1 class="header-name">Prescription</h1>
                        <h1 class="header-title"><?= htmlspecialchars($row['first_name'] . ' ' . $row['surname']); ?></h1>
                        <div class="profile-picture">
                        <?php
include 'patient_profile_picture.php';

// Assuming you have fetched the patient data into $patientData

displayPatientProfilePicture($row);
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
        <?php endif; ?>
        <h1 class="dashboard-header">Prescriptions</h1>
        <div class="prescription-container">
            <?php while ($prescription = $result->fetch_assoc()): ?>
                <?php
                // Determine the status class
                $statusClass = '';
                switch ($prescription['status']) {
                    case 'Pending':
                        $statusClass = 'status-pending';
                        break;
                    case 'In Progress':
                        $statusClass = 'status-in-progress';
                        break;
                    case 'Completed':
                        $statusClass = 'status-completed';
                        break;
                    case 'Cancelled':
                        $statusClass = 'status-cancelled';
                        break;
                }
                ?>
                <div class="prescription-card <?= $statusClass ?>">
                    <div class="card-header">
                        <?= htmlspecialchars($prescription['first_name'] . ' ' . $prescription['surname']) ?>
                    </div>
                    <div class="card-content">
                        <p><strong>Patient ID:</strong> <?= htmlspecialchars($prescription['patient_id']) ?></p>
                        <p><strong>Date of Birth:</strong> <?= htmlspecialchars($prescription['dob']) ?></p>
                        <p><strong>Prescribed by:</strong> <?= htmlspecialchars($prescription['doctor_name']) ?></p>
                        <p><strong>Department:</strong> <?= htmlspecialchars($prescription['department'] ?? " ") ?></p>
                        <p><strong>Date Prescribed:</strong> <?= htmlspecialchars($prescription['date_prescribed']) ?></p>
                        <p><strong>Time of Prescription:</strong> <?= htmlspecialchars($prescription['time_prescribed']) ?></p>
                        <p><strong>Status:</strong> <?= htmlspecialchars($prescription['status']) ?></p>
                    </div>
                    <div class="card-footer">
                        <a href="view_prescription.php?id=<?= $prescription['id'] ?>" class="view-link">View</a>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>

        <div class="pagination">
            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <a href="?<?= $patient_id ? 'id=' . $patient_id . '&' : '' ?>page=<?= $i ?>"><?= $i ?></a>
            <?php endfor; ?>
        </div>
    </div>
</body>
</html>

<?php
$conn->close();
?>
