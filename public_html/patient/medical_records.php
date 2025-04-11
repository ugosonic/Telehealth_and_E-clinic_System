<?php

include '../init.php';
include '../config.php';
include '../access_control.php';
include '../sidebar.php';

// Function for error handling
function handle_error($message) {
    echo "<div class='error'>Error: $message</div>";
    // Log the error
    error_log($message);
    exit();
}

// Number of records to display per page
$records_per_page = 20;

// Define content based on action
$action = isset($_GET['action']) ? $_GET['action'] : '';

// Handle search query
$search_keyword = '';
$search_type = '';
$result = false; // Initialize result to false

if (isset($_GET['search'])) {
    $search_keyword = $_GET['search'];
    $search_type = $_GET['search_type'];

    switch ($search_type) {
        case 'name':
            $sql = "SELECT patient_id, first_name, dob, age, gender FROM patient_db WHERE first_name LIKE ? OR surname LIKE ?";
            $search_param = "%$search_keyword%";
            $stmt = $conn->prepare($sql);
            if (!$stmt) {
                handle_error("Prepare failed: (" . $conn->errno . ") " . $conn->error);
            }
            $stmt->bind_param('ss', $search_param, $search_param);
            break;
        case 'dob':
            $sql = "SELECT patient_id, first_name, dob, age, gender FROM patient_db WHERE dob = ?";
            $stmt = $conn->prepare($sql);
            if (!$stmt) {
                handle_error("Prepare failed: (" . $conn->errno . ") " . $conn->error);
            }
            $stmt->bind_param('s', $search_keyword);
            break;
        case 'phone':
            $sql = "SELECT patient_id, first_name, dob, age, gender FROM patient_db WHERE telephone LIKE ?";
            $search_param = "%$search_keyword%";
            $stmt = $conn->prepare($sql);
            if (!$stmt) {
                handle_error("Prepare failed: (" . $conn->errno . ") " . $conn->error);
            }
            $stmt->bind_param('s', $search_param);
            break;
        case 'patient_id':
            $sql = "SELECT patient_id, first_name, dob, age, gender FROM patient_db WHERE patient_id = ?";
            $stmt = $conn->prepare($sql);
            if (!$stmt) {
                handle_error("Prepare failed: (" . $conn->errno . ") " . $conn->error);
            }
            $stmt->bind_param('s', $search_keyword);
            break;
        default:
            handle_error("Invalid search type.");
            break;
    }
    $stmt->execute();
    $result = $stmt->get_result();

    if (!$result) {
        handle_error("Search query failed: (" . $conn->errno . ") " . $conn->error);
    }
} else {
    // Pagination
    $page = isset($_GET['page']) ? $_GET['page'] : 1;
    $start_from = ($page - 1) * $records_per_page;

    // Query to fetch medical records
    $sql = "SELECT patient_id, first_name, dob, age, gender FROM patient_db LIMIT ?, ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        handle_error("Prepare failed: (" . $conn->errno . ") " . $conn->error);
    }
    $stmt->bind_param('ii', $start_from, $records_per_page);
    $stmt->execute();
    $result = $stmt->get_result();

    if (!$result) {
        handle_error("Query failed: (" . $conn->errno . ") " . $conn->error);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Medical Records</title>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<style> 
        /* medical_records.css */

/* Global Styles */
body {
    background-color: #f8f9fa;
    font-family: 'Helvetica Neue', Arial, sans-serif;
    color: #343a40;
}

h1.dashboard-header {
    font-size: 2.5rem;
    font-weight: 700;
    margin-top: 40px;
    margin-bottom: 40px;
    text-align: center;
    color: #007bff;
}

/* Search Form Styles */
.search-form {
    margin-bottom: 30px;
}

.search-form .form-control {
    border-radius: 0;
}

.search-form .form-select {
    border-radius: 0;
}

.search-form button.btn {
    border-radius: 0;
    background-color: #007bff;
    color: #fff;
}

.search-form button.btn:hover {
    background-color: #0056b3;
    color: #fff;
}

/* Table Styles */
.table-responsive {
    margin-bottom: 30px;
}

.table {
    background-color: #fff;
    border-collapse: separate;
    border-spacing: 0;
    width: 100%;
}

.table th,
.table td {
    vertical-align: middle !important;
    text-align: center;
}

.table thead th {
    background-color: #343a40;
    color: #fff;
    border-bottom: none;
}

.table tbody tr:hover {
    background-color: #f1f1f1;
}

.table-striped tbody tr:nth-of-type(odd) {
    background-color: #e9ecef;
}

.table-hover tbody tr:hover {
    background-color: #dee2e6;
}

/* Button Styles */
.btn {
    border-radius: 50px;
    font-weight: 500;
}

.btn-info {
    background-color: #17a2b8;
    border-color: #17a2b8;
}

.btn-info:hover {
    background-color: #138496;
    border-color: #117a8b;
}

.btn-warning {
    background-color: #ffc107;
    border-color: #ffc107;
    color: #212529;
}

.btn-warning:hover {
    background-color: #e0a800;
    border-color: #d39e00;
    color: #212529;
}

.btn-danger {
    background-color: #dc3545;
    border-color: #dc3545;
}

.btn-danger:hover {
    background-color: #c82333;
    border-color: #bd2130;
}

/* Pagination Styles */
.pagination {
    justify-content: center;
}

.pagination .page-item.active .page-link {
    background-color: #007bff;
    border-color: #007bff;
}

.pagination .page-link {
    color: #007bff;
}

.pagination .page-link:hover {
    color: #0056b3;
}

/* Alert Styles */
.alert {
    border-radius: 0;
}

/* Media Queries */
@media (max-width: 768px) {
    h1.dashboard-header {
        font-size: 2rem;
    }

    .btn {
        font-size: 0.9rem;
    }
}

    </style>
   
    <script>
         function confirmDeletion(patientId) {
            if (confirm("Are you sure you want to delete this patient file?")) {
                window.location.href = 'delete_record.php?id=' + encodeURIComponent(patientId);
            }
        }
    </script>
</head>
<body>

<div class="dash-body container-fluid">
            <!-- Content area -->
            <h1 class="dashboard-header">Medical Records</h1>
            <div class="d-flex justify-content-end mb-3">
    <a href="./patient.php" class="btn btn-success">Register Patient</a>
</div>



            <section class="search-section">
                <h2>Search Section</h2>
                <form class="row g-3 align-items-center search-form" method="GET" action="">
    <div class="col-md-6">
        <input type="text" name="search" class="form-control" placeholder="Search...">
    </div>
    <div class="col-md-4">
        <select class="form-select" name="search_type">
            <option value="name" <?php if ($search_type == 'name') echo 'selected'; ?>>Name</option>
            <option value="dob" <?php if ($search_type == 'dob') echo 'selected'; ?>>Date of Birth</option>
            <option value="phone" <?php if ($search_type == 'phone') echo 'selected'; ?>>Phone Number</option>
            <option value="patient_id" <?php if ($search_type == 'patient_id') echo 'selected'; ?>>Patient ID</option>
        </select>
    </div>
    <div class="col-md-2">
        <button type="submit" class="btn btn-primary w-100">Search</button>
    </div>
</form>

            </section>

            <section class="certification">
            <?php
                if ($result && $result->num_rows > 0) {
                    echo '<div class="table-responsive">';
                    echo '<table class="table table-striped table-hover">';
                    echo '<thead class="table-dark">';
                    echo '<tr>';
                    echo '<th>Patient ID</th>';
                    echo '<th>Name</th>';
                    echo '<th>Date of Birth</th>';
                    echo '<th>Age</th>';
                    echo '<th>Gender</th>';
                    echo '<th>Actions</th>';
                    echo '</tr>';
                    echo '</thead>';
                    echo '<tbody>';

                    while ($row = $result->fetch_assoc()) {
                        echo '<tr>';
                        echo '<td>' . htmlspecialchars($row["patient_id"] ?? '') . '</td>';
                        echo '<td>' . htmlspecialchars($row["first_name"] ?? '') . '</td>';
                        echo '<td>' . htmlspecialchars($row["dob"] ?? '') . '</td>';
                        echo '<td>' . htmlspecialchars($row["age"] ?? '') . '</td>';
                        echo '<td>' . htmlspecialchars($row["gender"] ?? '') . '</td>';
                        echo '<td>';
                        echo '<a href="patient_record.php?id=' . urlencode($row["patient_id"] ?? '') . '" class="btn btn-info btn-sm"><i class="fas fa-eye"></i> View</a> ';
                        echo '<a href="edit_record.php?id=' . urlencode($row["patient_id"] ?? '') . '" class="btn btn-warning btn-sm"><i class="fas fa-edit"></i> Edit</a> ';
                        echo '<button onclick="confirmDeletion(\'' . htmlspecialchars($row["patient_id"] ?? '') . '\')" class="btn btn-danger btn-sm"><i class="fas fa-trash-alt"></i> Delete</button>';
                        echo '</td>';
                        echo '</tr>';
                    }                    

                    echo '</tbody>';
                    echo '</table>';
                    echo '</div>';

                    // Pagination logic as before
                } else {
                    echo '<div class="alert alert-warning text-center" role="alert">';
                    echo 'No records found.';
                    echo '</div>';
                }

                $conn->close();
            ?>
        </section>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
