<?php
// schedule.php

require_once '../init.php';
require_once '../config.php';
require_once '../access_control.php';
require_once 'functions.php'; // New file for helper functions

// Ensure user is authenticated
if (!isset($_SESSION['username'])) {
    header('Location: ./login/login.php');
    exit;
}

$username = $_SESSION['username'];
$usergroup = $_SESSION['usergroup'] ?? '';

// Fetch user details
$userDetails = getUserDetails($conn, $username);
if (!$userDetails) {
    echo "<p>User not found. Please <a href='login.php'>login again</a>.</p>";
    exit;
}

// Pagination setup
$results_per_page = 10;  // Number of results per page
$current_page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$start_from = ($current_page - 1) * $results_per_page;

// Determine SQL condition based on user role
$patient_id = $_GET['patient_id'] ?? '';
$sqlCondition = getSqlCondition($usergroup, $username, $patient_id);

// Fetch appointments
$today = date('Y-m-d');
$appointments = getAppointments($conn, $sqlCondition, $today, $start_from, $results_per_page);

// Fetch total records for pagination
$total_records = getTotalAppointments($conn, $sqlCondition);
$total_pages = ceil($total_records / $results_per_page);

// Include header and sidebar

include '../sidebar.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Appointment Management</title>
    <!-- Include Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/css/bootstrap.min.css">
    <!-- Custom Styles -->
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container mt-5">
<h2 class="mb-1">Appointment Manager</h2>
    <div class="text-right mb-3">
        <a href="book_appointment.php" class="btn btn-primary">Book Appointment</a>
    </div>

    <!-- Tabs for Upcoming, Past, and Cancelled Appointments -->
    <ul class="nav nav-tabs" id="appointmentTabs" role="tablist">
        <li class="nav-item">
            <a class="nav-link active" id="upcoming-tab" data-toggle="tab" href="#upcoming" role="tab">Upcoming Appointments</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" id="past-tab" data-toggle="tab" href="#past" role="tab">Past Appointments</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" id="cancelled-tab" data-toggle="tab" href="#cancelled" role="tab">Cancelled Appointments</a>
        </li>
    </ul>

    <div class="tab-content" id="appointmentTabsContent">
        <!-- Upcoming Appointments Tab -->
        <div class="tab-pane fade show active" id="upcoming" role="tabpanel">
            <?php displayAppointmentsTable($appointments['upcoming'], 'upcoming'); ?>
        </div>
        <!-- Past Appointments Tab -->
        <div class="tab-pane fade" id="past" role="tabpanel">
            <?php displayAppointmentsTable($appointments['past'], 'past'); ?>
        </div>
        <!-- Cancelled Appointments Tab -->
        <div class="tab-pane fade" id="cancelled" role="tabpanel">
            <?php displayAppointmentsTable($appointments['cancelled'], 'cancelled'); ?>
        </div>
    </div>

    
<!-- Modals -->
<?php include 'modals.php'; ?>

<!-- Pagination -->
<?php displayPagination($current_page, $total_pages); ?>
</div>


<!-- Scripts -->
<!-- Include jQuery -->
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<!-- Include Bootstrap JS -->
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/js/bootstrap.min.js"></script>
<!-- Include custom script.js -->
<script src="script.js"></script>

</body>
</html>
