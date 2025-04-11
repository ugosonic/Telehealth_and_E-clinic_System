<?php
session_start();

// Database connection info
require_once '../init.php';
require_once '../config.php';

// Determine user
$action = isset($_GET['action']) ? $_GET['action'] : '';
$username   = '';
$email      = '';
$usergroup  = '';
if (isset($_SESSION['username'])) {
    $stmt = $conn->prepare('SELECT username, email, usergroup, registration_id FROM users WHERE username = ?');
    $stmt->bind_param('s', $_SESSION['username']);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        // Found in users
        $user_row  = $result->fetch_assoc();
        $username  = $user_row['username'];
        $email     = $user_row['email'];
        $usergroup = $user_row['usergroup'];
    } else {
        // Maybe it's a Patient
        $stmt = $conn->prepare('SELECT username, email FROM patient_db WHERE username = ?');
        $stmt->bind_param('s', $_SESSION['username']);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $user_row  = $result->fetch_assoc();
            $username  = $user_row['username'];
            $email     = $user_row['email'];
            $usergroup = 'Patient';
        } else {
            echo "<p>User not found. Please <a href='/my clinic/login/login.php'>login again</a>.</p>";
            exit();
        }
    }
    $stmt->close();
}

// Helpers for dashboard links
function getDashboardLink($usergroup) {
    switch ($usergroup) {
        case 'Admin':
            return "/My Clinic/Dashboard/admin_dashboard.php";
        case 'IT':
            return "/My Clinic/Dashboard/it_dashboard.php";
        case 'Doctor':
            return "/My Clinic/Dashboard/doctor_dashboard.php";
        case 'Nurse':
            return "/My Clinic/Dashboard/nurse_dashboard.php";
        case 'Lab Scientist':
            return "/My Clinic/Dashboard/lab_scientist_dashboard.php";
        case 'Pharmacist':
            return "/My Clinic/Dashboard/pharmacist_dashboard.php";
        case 'Patient':
            return "/My Clinic/Dashboard/patient_dashboard.php";
        default:
            return "/My Clinic/Dashboard/user_dashboard.php";
    }
}
function getvideoConsultation($usergroup) {
    switch ($usergroup) {
        case 'Admin':
        case 'Doctor':
        case 'Nurse':
        case 'Lab Scientist':
        case 'Pharmacist':
            return "/My Clinic/video_call/session.php";
        case 'Patient':
            return "/My Clinic/video_call/join_session.php";
        default:
            return "/My Clinic/Waiting_room/all_waiting_rooms.php";
    }
}
function getWaitingRoomLink($usergroup) {
    switch ($usergroup) {
        case 'Admin':
            return "/My Clinic/Waiting_room/all_waiting_rooms.php";
        case 'Doctor':
            return "/My Clinic/Waiting_room/doctor_waiting_room.php";
        case 'Nurse':
            return "/My Clinic/Waiting_room/nurse_waiting_room.php";
        case 'Lab Scientist':
            return "/My Clinic/Waiting_room/laboratory_waiting_room.php";
        case 'Pharmacist':
            return "/My Clinic/Waiting_room/pharmacy_waiting_room.php";
        case 'Patient':
            return "/My Clinic/Waiting_room/patient_waiting_room.php";
        default:
            return "/My Clinic/Waiting_room/all_waiting_rooms.php";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>

    <!-- CSS / Bootstrap -->
    <link rel="stylesheet" href="dashboard.css">
    <link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">

    <!-- JS / jQuery -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.nicescroll/3.6.8-fix/jquery.nicescroll.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        body {
            margin: 0;
            padding: 0;
            height: 100vh;
            background-color: #f4f4f4;
        }
        .container {
            display: flex; 
            height: 100vh;
        }
        .main-content {
            margin-left: -42px;
            padding: 0px;
            flex-grow: 1;
        }
        .sidebar-icon {
            width: 50px;
            background-color: #f8f9fa;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            border-right: 1px solid #ddd;
            position: fixed;
            top: 0;
            left: 0;
            height: 100%;
        }
        .sidebar-icon i {
            font-size: 1.5rem;
            margin: 1rem 0;
            cursor: pointer;
        }
        .offcanvas {
            width: 250px !important;
        }
        .offcanvas-body a {
            text-decoration: none;
            color: #333;
            display: flex;
            align-items: center;
            padding: 10px 0;
        }
        .offcanvas-body a:hover {
            background-color: #f0f0f0;
        }
        .offcanvas-body i {
            margin-right: 10px;
        }
        .top-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px;
        }
        .dashboard-title {
            font-size: 2rem;
            font-weight: bold;
            margin: 0;
        }
        .icon-group {
            display: flex;
            align-items: center;
            gap: 20px;
        }
        .icon-group i {
            font-size: 1.5rem;
            cursor: pointer;
            position: relative;
        }
        .notification-badge {
            position: absolute;
            top: -5px;
            right: -5px;
            background: red;
            color: white;
            border-radius: 50%;
            padding: 2px 6px;
            font-size: 0.7rem;
        }
        .dashboard-title {
            margin: 60px 0 20px 70px;
            font-size: 2rem;
            font-weight: bold;
        }
        .dashboard-container {
            margin-left: 40px;
            padding: 20px;
            width: calc(100% - 40px);
        }
        .small-containers, .large-containers, .bottom-containers {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
        }
        .card {
            background-color: white;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s, box-shadow 0.3s;
            font-weight: bold;
        }
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.15);
        }
        .small-containers .card {
            width: 30%;
            height: 150px;
            align-items: center;
        }
        .large-containers {
            display: flex;
            justify-content: space-between;
            gap: 20px;
        }
        .upcoming-appointments {
            flex: 7.5;
        }
        .waiting-room {
            flex: 2.5;
        }
        .bottom-containers .card {
            width: 48%;
            height: 345px;
            align-items: center;
        }
        .table-container {
            max-height: 300px; 
            overflow-y: auto;
            margin-top: 20px;
        }
        .sub-table {
            width: 100%;
            border-collapse: collapse;
            margin: 0;
            font-size: 0.9rem;
            color: #333;
        }
        .sub-table th, .sub-table td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        .sub-table th {
            background-color: #007bff;
            color: white;
            font-weight: bold;
        }
        .sub-table tr:hover {
            background-color: #f1f1f1;
        }
        .btn-view, .btn-reschedule, .btn-delete {
            text-decoration: none;
            color: white;
            padding: 5px 10px;
            border-radius: 5px;
            margin-right: 5px;
        }
        .btn-view { background-color: #28a745; }
        .btn-reschedule { background-color: #ffc107; }
        .btn-delete { background-color: #dc3545; }

        /* Modal base styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0; top: 0;
            width: 100%; 
            height: 100%; 
            overflow: auto; 
            background-color: rgba(0, 0, 0, 0.4); 
            padding-top: 60px;
            padding-left: 260px;
        }
        .modal-content {
            background-color: #fefefe;
            margin: 5% auto; 
            border: 1px solid #888;
            width: 70%;
            padding: 60px;
            border-radius: 5px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
            position: relative;
        }
        .close {
            color: #aaa;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
            position: absolute;
            top: 10px; 
            right: 15px;
            transition: color 0.3s;
        }
        .close:hover,
        .close:focus {
            color: #000;
            text-decoration: none;
            cursor: pointer;
        }
        .modal form {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }
        .modal form label {
            font-weight: bold;
        }
        .modal form input[type="date"],
        .modal form select,
        .modal form textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        .modal form button {
            background-color: #3498db;
            color: white;
            border: none;
            padding: 10px;
            border-radius: 4px;
            cursor: pointer;
        }
        .modal form button:hover {
            background-color: #2980b9;
        }
        .modal .btn-secondary {
            background-color: #95a5a6;
        }
        .modal .btn-danger {
            background-color: #e74c3c;
        }
        .modal .btn-danger:hover {
            background-color: #c0392b;
        }
        /* Staff Online Styles */
        #staff-list {
            max-height: 200px; /* scroll container */
            overflow-y: auto;
            margin-top: 10px;
        }
        .staff-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 5px;
            border-bottom: 1px solid #ddd;
        }
        .staff-name {
            font-size: 16px;
        }
        .status-dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
        }
        .pagination a {
            margin: 0 5px;
            text-decoration: none;
            color: #007BFF;
        }
        .pagination a:hover {
            text-decoration: underline;
        }
        .status-selector {
            margin-bottom: 20px;
        }
        .status-selector label {
            margin-right: 20px;
        }
        @media (max-width: 768px) {
            .modal-content {
                width: 90%;
                margin-top: 10%;
            }
        }
    </style>
</head>

<body>
<div class="container">
    <!-- Sidebar -->
    <div class="sidebar-icon">
        <a class="navbar-brand" href="#"><i class="fas fa-bars" data-bs-toggle="offcanvas" data-bs-target="#offcanvasSidebar"></i></a>
        <a href="<?= getDashboardLink($usergroup); ?>"><i class="fas fa-home"></i></a>
        <a href="<?= getvideoConsultation($usergroup); ?>"><i class="fas fa-video"></i></a>
        <a href="../appointment_manager/schedule.php"><i class="fas fa-calendar-alt"></i></a>
        <a href="/my clinic/patient/medical_records.php"><i class="fas fa-file-medical"></i></a>
        <a href="<?= getWaitingRoomLink($usergroup); ?>"><i class="fas fa-users"></i></a>
        <a href="/my clinic/laboratory.php"><i class="fa fa-flask"></i></a>
        <a href="../pharmacy_management_system/Dashboard.php"><i class="fa fa-medkit"></i></a>
        <a href="#" data-bs-toggle="dropdown"><i class="fa fa-envelope"></i></a>
        <a href="../staff_registration/registration.php"><i class="fa fa-user-plus"></i></a>
        <a href="/my clinic/settings.php"><i class="fa fa-cog"></i></a>
    </div>

    <!-- Offcanvas Sidebar -->
    <div class="offcanvas offcanvas-start" tabindex="-1" id="offcanvasSidebar" aria-labelledby="offcanvasSidebarLabel">
        <div class="offcanvas-header">
            <h5 class="offcanvas-title" id="offcanvasSidebarLabel">Menu</h5>
            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body">
            <div class="d-flex align-items-center mb-3">
                <img src="../img/user.png" alt="User Image" class="rounded-circle me-2" width="50">
                <div>
                    <p class="mb-0"><?= htmlspecialchars($username, ENT_QUOTES); ?></p>
                    <p class="small text-muted"><?= htmlspecialchars($email, ENT_QUOTES); ?></p>
                </div>
            </div>
            <a href="/my clinic/logout.php" class="btn btn-primary mb-3">Log out</a>
            <!-- Navigation links with icons -->
            <ul class="list-group">
                <li class="list-group-item">
                    <a href="<?= getDashboardLink($usergroup); ?>">
                        <i class="fas fa-home"></i> Dashboard
                    </a>
                </li>
                <li class="list-group-item">
                    <a href="<?= getvideoConsultation($usergroup); ?>">
                        <i class="fas fa-video"></i> Video Consultation
                    </a>
                </li>
                <li class="list-group-item">
                    <a href="../appointment_manager/schedule.php">
                        <i class="fas fa-calendar-alt"></i> Appointment
                    </a>
                </li>
                <li class="list-group-item">
                    <a href="/my clinic/patient/medical_records.php">
                        <i class="fas fa-file-medical"></i> Medical Records
                    </a>
                </li>
                <li class="list-group-item">
                    <a href="<?= getWaitingRoomLink($usergroup); ?>">
                        <i class="fas fa-users"></i> Waiting Room
                    </a>
                </li>
                <li class="list-group-item">
                    <a href="/my clinic/Laboratory/dashboard.php">
                        <i class="fa fa-flask"></i> Laboratory
                    </a>
                </li>
                <li class="list-group-item">
                    <a href="/my clinic/pharmacy_management_system/Dashboard.php">
                        <i class="fa fa-medkit"></i> Pharmacy
                    </a>
                </li>
                <li class="list-group-item dropdown">
                    <a href="#" data-bs-toggle="dropdown">
                        <i class="fa fa-envelope"></i> Messaging
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="/my clinic/messaging/inbox.php">Inbox</a></li>
                        <li><a class="dropdown-item" href="/my clinic/messaging/draft.php">Draft</a></li>
                        <li><a class="dropdown-item" href="/my clinic/messaging/sent.php">Sent</a></li>
                        <li><a class="dropdown-item" href="/my clinic/messaging/new_message.php">New Message</a></li>
                    </ul>
                </li>
                <li class="list-group-item">
                    <a href="/my clinic/staff_registration/registration.php">
                        <i class="fa fa-user-plus"></i> Staff Registration
                    </a>
                </li>
                <li class="list-group-item">
                    <a href="/my clinic/settings.php">
                        <i class="fa fa-cog"></i> Settings
                    </a>
                </li>
            </ul>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Top Bar -->
        <div class="top-bar">
            <div class="dashboard-title">Dashboard</div>
            <div class="icon-group">
                <div class="status-selector">
                    <h4>Select your status:</h4>
                    <label>
                        <input type="radio" name="status" value="Online" checked onchange="updateStatus(this.value)"> Online
                    </label>
                    <label>
                        <input type="radio" name="status" value="In a meeting" onchange="updateStatus(this.value)"> In a meeting
                    </label>
                    <label>
                        <input type="radio" name="status" value="Busy" onchange="updateStatus(this.value)"> Busy
                    </label>
                    <label>
                        <input type="radio" name="status" value="Attending to a patient" onchange="updateStatus(this.value)"> Attending to a patient
                    </label>
                </div>
                <i class="fas fa-search" onclick="window.location.href='/my clinic/search.php'"></i>
                <i class="fas fa-comments">
                    <span class="notification-badge">3</span>
                </i>
                <i class="fas fa-question-circle" onclick="window.location.href='/my clinic/help.php'"></i>
            </div>
        </div>

        <!-- Dashboard Container -->
        <div class="dashboard-container">
            <!-- Small Containers -->
            <div class="small-containers">
                <div class="card">Account Balance</div>
                <div class="card">
                    <p>Total Patients</p>
                    <h2 id="total-patients-count">0</h2>
                </div>
                <div class="card">
                    <p>No. of Patients in the Waiting Room</p>
                    <h2 id="waiting-room-count">0</h2>
                </div>
            </div>

            <!-- Large Containers -->
            <div class="large-containers">
                <div class="card upcoming-appointments">
                    <p class="heading-main12">Upcoming Appointments (Table)</p>
                    <div class="table-container">
                        <table class="sub-table">
                            <thead>
                                <tr>
                                    <th>Patient Name</th>
                                    <th>Patient ID</th>
                                    <th>Appointment ID</th>
                                    <th>Type</th>
                                    <th>Date</th>
                                    <th>Time</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="upcoming-appointments">
                                <!-- Populated via AJAX get_appointments.php -->
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card" style="flex: 2;">
                    <p>Waiting Time (Graph)</p>
                    <canvas id="waitingTimeChart" width="400" height="400"></canvas>
                </div>
            </div>

            <!-- Bottom Containers -->
            <div class="bottom-containers">
                <div class="card">
                    <p>List of Staff Online</p>
                    <div id="staff-list"></div>
                    <div id="pagination" class="pagination"></div>
                </div>
                <div class="card">List of Pending Prescriptions</div>
                <?php
// Example snippet to display ONLY unread messages (maximum 10 shown).
// Requires an active session + a DB connection ($conn).
// Adjust table/column references as needed.

if (!isset($_SESSION['registration_id'])) {
    echo "<p>Please <a href='/my clinic/login/login.php'>log in</a> to see your inbox.</p>";
    return;
}

$user_id = $_SESSION['registration_id'];
?>

<div class="card" style="margin-top: 0px;">
    <h5>Inbox (Unread Messages)</h5>
    <div style="max-height: 450px; overflow-y: auto; padding: 5px;">
        <?php
        // Fetch unread messages
        $sql = "
            SELECT m.id,
                   m.subject,
                   m.created_at,
                   CONCAT(u.first_name, ' ', u.surname) AS sender_name
            FROM messages m
            JOIN users u ON m.sender_id = u.registration_id
            WHERE m.receiver_id = ?
              AND m.is_read = 0
              AND m.parent_id IS NULL
              AND m.is_deleted_by_receiver = FALSE
            ORDER BY m.created_at DESC
            LIMIT 10
        ";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $user_id);
        $stmt->execute();
        $res = $stmt->get_result();

        if ($res->num_rows === 0):
            echo "<p>No unread messages.</p>";
        else:
        ?>
            <table style="width: 100%; border-collapse: collapse;">
                <thead style="background-color: #007bff; color: white;">
                    <tr>
                        <th style="padding: 8px;">Sender</th>
                        <th style="padding: 8px;">Subject</th>
                        <th style="padding: 8px;">Received</th>
                        <th style="padding: 8px;">Action</th>
                    </tr>
                </thead>
                <tbody>
                <?php while ($row = $res->fetch_assoc()): ?>
                    <tr style="border-bottom: 1px solid #ddd;">
                        <td style="padding: 8px; font-weight: bold;">
                            <?= htmlspecialchars($row['sender_name']); ?>
                        </td>
                        <td style="padding: 8px; font-weight: bold;">
                            <?= htmlspecialchars($row['subject']); ?>
                        </td>
                        <td style="padding: 8px;">
                            <?php 
                            // Simple "time ago" logic
                            $createdTime = strtotime($row['created_at']);
                            $timeAgo = time() - $createdTime;
                            if ($timeAgo < 3600) {
                                echo floor($timeAgo / 60) . ' min(s) ago';
                            } elseif ($timeAgo < 86400) {
                                echo floor($timeAgo / 3600) . ' hour(s) ago';
                            } else {
                                echo date('Y-m-d H:i', $createdTime);
                            }
                            ?>
                        </td>
                        <td style="padding: 8px;">
                            <a href="/my clinic/messaging/view_message.php?id=<?= $row['id']; ?>">View</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
        <?php
        endif;
        $stmt->close();
        ?>
    </div>
    <div style="margin-top: 18px;">
        <a href="/my clinic/messaging/inbox.php?filter=unread">See all unread messages</a> |
        <a href="/my clinic/messaging/inbox.php">Go to Inbox</a>
    </div>
</div>

            </div>
        </div>
    </div>
</div>

<!-- View Modal -->
<div id="viewModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal()">&times;</span>
        <h2>View Appointment</h2>
        <div id="viewDetails">Loading...</div>
    </div>
</div>

<!-- Reschedule Appointment Modal -->
<div id="rescheduleModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal()">&times;</span>
        <h2>Reschedule Appointment</h2>

        <!-- 
          We need to pass department & type to fetch_availability.php
          We'll store them here in hidden fields. 
        -->
        <form id="rescheduleForm" action="/my clinic/appointment_manager/reschedule_appointment.php" method="post">
            <input type="hidden" name="appointment_id" id="rescheduleAppointmentId">
            <input type="hidden" name="department" id="reschedule_department">
            <input type="hidden" name="type" id="reschedule_type">

            <label for="new_date">New Date:</label>
            <input type="date" name="new_date" id="new_date" required>

            <label for="appointment_time">New Time:</label>
            <select name="appointment_time" id="appointment_time" class="form-select" required>
                <option value="" disabled selected>Select time</option>
                <!-- populated by fetch_availability.php -->
            </select>

            <button type="submit" class="btn-primary">Reschedule</button>
        </form>
    </div>
</div>

<!-- Cancel Confirmation Modal -->
<div id="cancelModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal()">&times;</span>
        <h2>Cancel Appointment</h2>
        <p>Are you sure you want to cancel this appointment?</p>
        <form id="cancelForm" action="/my clinic/appointment_manager/cancel_appointment.php" method="post">
            <input type="hidden" name="appointment_id" id="cancelAppointmentId">
            <label for="cancellation_reason">Reason for cancellation:</label>
            <textarea name="cancellation_reason" id="cancellation_reason" required></textarea>
            <button type="submit" class="btn-danger">Cancel Appointment</button>
        </form>
    </div>
</div>

<script>
// --------------------------------------------------------------------------
// 1) PATIENT COUNTS
// --------------------------------------------------------------------------
function fetchPatientCounts() {
    var xhr = new XMLHttpRequest();
    xhr.open('GET', 'get_patient_counts.php', true);
    xhr.onreadystatechange = function () {
        if (xhr.readyState == 4 && xhr.status == 200) {
            var response = JSON.parse(xhr.responseText);
            if (response.error) {
                console.error(response.error);
            } else {
                document.getElementById('total-patients-count').innerText = response.total_patients;
                document.getElementById('waiting-room-count').innerText = response.total_waiting_room;
            }
        }
    };
    xhr.send();
}
fetchPatientCounts();
setInterval(fetchPatientCounts, 1000); // every 1s

// --------------------------------------------------------------------------
// 2) UPCOMING APPOINTMENTS
// --------------------------------------------------------------------------
function fetchAppointments() {
    var xhr = new XMLHttpRequest();
    xhr.open('GET', 'get_appointments.php', true);
    xhr.onreadystatechange = function () {
        if (xhr.readyState == 4 && xhr.status == 200) {
            document.getElementById('upcoming-appointments').innerHTML = xhr.responseText;
        }
    };
    xhr.send();
}
fetchAppointments();
setInterval(fetchAppointments, 10000); // refresh every 10s

// modal openers
function openModal(action, appointmentId, dept, typ) {
    var modal;
    if (action === 'view') {
        modal = document.getElementById('viewModal');
        var modalContent = document.getElementById('viewDetails');
        loadContent(`/My Clinic/appointment_manager/view_appointment.php?id=${appointmentId}`, modalContent);
    } 
    else if (action === 'reschedule') {
        modal = document.getElementById('rescheduleModal');
        document.getElementById('rescheduleAppointmentId').value = appointmentId;
        // store dept & type in hidden fields
        document.getElementById('reschedule_department').value = dept;
        document.getElementById('reschedule_type').value = typ;
    }
    else if (action === 'cancel') {
        modal = document.getElementById('cancelModal');
        document.getElementById('cancelAppointmentId').value = appointmentId;
    }
    modal.style.display = "block";
}
function closeModal() {
    var modals = document.querySelectorAll('.modal');
    modals.forEach(function (modal) {
        modal.style.display = 'none';
    });
}
function loadContent(url, container) {
    var xhr = new XMLHttpRequest();
    xhr.open('GET', url, true);
    xhr.onreadystatechange = function () {
        if (xhr.readyState === 4 && xhr.status === 200) {
            container.innerHTML = xhr.responseText;
        }
    };
    xhr.send();
}
document.querySelectorAll('.close').forEach(function (element) {
    element.onclick = closeModal;
});

// --------------------------------------------------------------------------
// 3) WAITING TIME CHART
// --------------------------------------------------------------------------
let chart;
function fetchWaitingTimes() {
    fetch('get_waiting_times.php') // your endpoint
        .then(response => response.json())
        .then(data => {
            updateChart(data.waitingRooms, data.waitingTimes);
        })
        .catch(error => console.error('Error fetching waiting times:', error));
}
function updateChart(waitingRooms, waitingTimes) {
    if (chart) {
        chart.data.labels = waitingRooms;
        chart.data.datasets[0].data = waitingTimes;
        chart.update();
    } else {
        const ctx = document.getElementById('waitingTimeChart').getContext('2d');
        chart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: waitingRooms,
                datasets: [{
                    label: 'Waiting Time (Minutes)',
                    data: waitingTimes,
                    backgroundColor: [
                        'rgba(75, 192, 192, 0.2)',
                        'rgba(54, 162, 235, 0.2)',
                        'rgba(255, 206, 86, 0.2)',
                        'rgba(153, 102, 255, 0.2)'
                    ],
                    borderColor: [
                        'rgba(75, 192, 192, 1)',
                        'rgba(54, 162, 235, 1)',
                        'rgba(255, 206, 86, 1)',
                        'rgba(153, 102, 255, 1)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                scales: { y: { beginAtZero: true } }
            }
        });
    }
}
fetchWaitingTimes();
setInterval(fetchWaitingTimes, 6000); // update every 6s

// --------------------------------------------------------------------------
// 4) RESCHEDULE FORM
// --------------------------------------------------------------------------
document.addEventListener('DOMContentLoaded', function () {
    setDatePickerMinDate();
    document.getElementById('rescheduleForm').addEventListener('submit', function (event) {
        event.preventDefault();
        var formData = new FormData(this);
        fetch('/my clinic/appointment_manager/reschedule_appointment.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            alert(data.message);
            if (data.status === 'success') {
                closeModal();
                fetchAppointments();
            }
        })
        .catch(error => console.error('Error:', error));
    });
    // Whenever new_date changes, call updateTimes() to populate the times
    document.getElementById('new_date').addEventListener('change', updateTimes);
});
function setDatePickerMinDate() {
    const datePicker = document.getElementById('new_date');
    const today = new Date().toISOString().split('T')[0];
    datePicker.setAttribute('min', today);
}

// Use fetch_availability.php to get time slots
function updateTimes() {
    const dept = document.getElementById('reschedule_department').value;
    const typ  = document.getElementById('reschedule_type').value;
    const dat  = document.getElementById('new_date').value;
    if (!dept || !typ || !dat) return;

    fetch(`fetch_availability.php?department=${encodeURIComponent(dept)}&type=${encodeURIComponent(typ)}&date=${encodeURIComponent(dat)}`)
    .then(res => res.json())
    .then(data => {
        const timeEl = document.getElementById('appointment_time');
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
    });
}

// --------------------------------------------------------------------------
// 5) STAFF ONLINE
// --------------------------------------------------------------------------
let currentPage = 1;
function updateStatus(status) {
    const xhr = new XMLHttpRequest();
    xhr.open('POST', 'update_status.php', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.send(`status=${status}`);
}
function fetchOnlineStaff(page = 1) {
    const xhr = new XMLHttpRequest();
    xhr.open('GET', `fetch_online_staff.php?page=${page}`, true);
    xhr.onload = function() {
        if (xhr.status === 200) {
            const response = JSON.parse(xhr.responseText);
            const staffContainer = document.getElementById('staff-list');
            staffContainer.innerHTML = '';
            if (response.staff.length === 0) {
                staffContainer.innerHTML = '<p>No online staff available.</p>';
            } else {
                response.staff.forEach(staff => {
                    let color = 'green'; 
                    switch (staff.status) {
                        case 'In a meeting': color = 'yellow'; break;
                        case 'Busy': color = 'red'; break;
                        case 'Attending to a patient': color = 'blue'; break;
                        case 'Online': default: color = 'green'; break;
                    }
                    staffContainer.innerHTML += `
                        <div class="staff-item">
                            <span class="staff-name">${staff.username} (${staff.usergroup})</span>
                            <span class="status-dot" style="background-color: ${color};"></span>
                        </div>
                    `;
                });
            }
            // pagination
            const pagination = document.getElementById('pagination');
            pagination.innerHTML = '';
            for (let i = 1; i <= response.totalPages; i++) {
                pagination.innerHTML += `<a href="#" onclick="fetchOnlineStaff(${i})">${i}</a> `;
            }
        }
    }
    xhr.send();
}
fetchOnlineStaff(currentPage);
setInterval(() => fetchOnlineStaff(currentPage), 5000); // refresh every 5s
</script>

<!-- popper + bootstrap JS -->
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
