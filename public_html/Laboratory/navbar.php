<?php
// navbar.php
include '../init.php';
include '../config.php';

// Function to get dashboard link based on user role
function getDashboardLink($usergroup) {
    switch ($usergroup) {
        case 'Admin':
            return "../Dashboard/admin_dashboard.php";
        case 'Doctor':
            return "../Dashboard/doctor_dashboard.php";
        case 'Lab Scientist':
            return "../Dashboard/lab_scientist_dashboard.php";
        default:
            return "../Dashboard/user_dashboard.php";
    }
}

// Function to get video consultation link based on user role
function getVideoConsultation($usergroup) {
    switch ($usergroup) {
        case 'Admin':
        case 'Doctor':
        case 'Lab Scientist':
            return "../video_call/session.php";
        default:
            return "../Waiting_room/all_waiting_rooms.php";
    }
}

// Function to get waiting room link based on user role
function getWaitingRoomLink($usergroup) {
    switch ($usergroup) {
        case 'Admin':
            return "../Waiting_room/all_waiting_rooms.php";
        case 'Doctor':
            return "../Waiting_room/doctor_waiting_room.php";
        case 'Lab Scientist':
            return "../Waiting_room/laboratory_waiting_room.php";
        default:
            return "../Waiting_room/all_waiting_rooms.php";
    }
}

// Assuming $usergroup is set in the session or fetched from the database
$usergroup = $_SESSION['usergroup'] ?? 'Guest';
?>

<nav class="navbar navbar-expand-lg navbar-light bg-light">
    <div class="container-fluid">
        <a class="navbar-brand" href="#">My Clinic</a>
        <button
            class="navbar-toggler"
            type="button"
            data-bs-toggle="collapse"
            data-bs-target="#navbarNav"
            aria-controls="navbarNav"
            aria-expanded="false"
            aria-label="Toggle navigation"
        >
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link" href="<?= getDashboardLink($usergroup); ?>">Dashboard</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?= getWaitingRoomLink($usergroup); ?>">Waiting Room</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?= getVideoConsultation($usergroup); ?>">Video Consultation</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="../Laboratory/create_lab_test.php">Create Lab Template</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="../Laboratory/lab_requests.php">View Requested Laboratory</a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<!-- Bootstrap CSS and JS -->
<link
    href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css"
    rel="stylesheet"
>
<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"
></script>
