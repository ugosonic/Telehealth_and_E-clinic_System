<?php
include 'init.php';

// Fetch user details
$username = '';
$email = '';
$usergroup = '';

if (isset($_SESSION['username'])) {
    $stmt = $conn->prepare('SELECT username, email, usergroup FROM users WHERE username = ?');
    $stmt->bind_param('s', $_SESSION['username']);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        // User is found in the 'users' table
        $user_row = $result->fetch_assoc();
        $username = $user_row['username'];
        $email = $user_row['email'];
        $usergroup = $user_row['usergroup'];
    } else {
        // If user is not found in the 'users' table, check the 'patient_db' table
        $stmt = $conn->prepare('SELECT username, email FROM patient_db WHERE username = ?');
        $stmt->bind_param('s', $_SESSION['username']);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            // User is found in the 'patient_db' table
            $user_row = $result->fetch_assoc();
            $username = $user_row['username'];
            $email = $user_row['email'];
            $usergroup = 'Patient';  // Set the usergroup as 'Patient' for patients
        } else {
            echo "<p>User not found. Please <a href='/my clinic/login/login.php'>login again</a>.</p>";
        }
    }

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
                return "/My Clinic/video_call/session.php";
            case 'Doctor':
                return "/My Clinic/video_call/session.php";
            case 'Nurse':
                return "/My Clinic/video_call/session.php";
            case 'Lab Scientist':
                return "/My Clinic/video_call/session.php";
            case 'Pharmacist':
                return "/My Clinic/video_call/session.php";
            case 'Patient':
                return "/My Clinic/video_call/join_session.php";  // Add a specific waiting room for patients if necessary
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
                return "/My Clinic/Waiting_room/patient_waiting_room.php";  // Add a specific waiting room for patients if necessary
            default:
                return "/My Clinic/Waiting_room/all_waiting_rooms.php";
        }
    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/animations.css"> 
    <link rel="stylesheet" href="../css/main.css">  
    <link rel="stylesheet" href="../css/admin.css">
    <link rel="stylesheet" href="../css/sidebar.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <script src="/my clinic/Dashboard/sidebar.js"></script>
</head>
<body>
<div class="container">
    <div class="menu">
        <div class="wrapper">
            <span class="hamburger" id="sidebarCollapse">&#9776;</span>
            <!-- Sidebar -->
            <nav id="sidebar">
                <div id="dismiss">
                    <i class="fa fa-times" aria-hidden="true"></i>
                </div>
                <div class="sidebar-header">
                    <table class="menu-container" border="0">
                        <tr>
                            <td style="padding:10px" colspan="2">
                                <table border="0" class="profile-container">
                                    <tr>
                                        <td width="30%" style="padding-left:20px">
                                            <img src="../img/user.png" alt="" width="100%" style="border-radius:50%">
                                        </td>
                                        <td>
                                            <p class="profile-title"><?= htmlspecialchars($username, ENT_QUOTES); ?></p>
                                            <p class="profile-subtitle"><?= htmlspecialchars($email, ENT_QUOTES); ?></p>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="2">
                                            <a href="../logout.php"><input type="button" value="Log out" class="logout-btn btn-primary-soft btn"></a>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                        <!-- Navigation links -->
                        <tr class="menu-row">
                            <td class="menu-btn menu-icon-dashbord menu-active">
                                <a href="<?= getDashboardLink($usergroup); ?>" class="non-style-link-menu-active">
                                    <div><p class="menu-text">Dashboard</p></div>
                                </a>
                            </td>
                        </tr>
                        <tr class="menu-row">
                            <td class="menu-btn menu-icon-doctor">
                            <a href="<?= getvideoConsultation($usergroup); ?>" class="non-style-link-menu">
                                    <div><p class="menu-text">Video Consultation</p></div>
                                </a>
                            </td>
                        </tr>
                        <tr class="menu-row">
                            <td class="menu-btn menu-icon-schedule">
                                <a href="../appointment_manager/schedule.php" class="non-style-link-menu">
                                    <div><p class="menu-text">Appointment</p></div>
                                </a>
                            </td>
                        </tr>
                        <tr class="menu-row">
                            <td class="menu-btn menu-icon-appoinment">
                                <a href="../patient/medical_records.php" class="non-style-link-menu">
                                    <div><p class="menu-text">Medical Records</p></div>
                                </a>
                            </td>
                        </tr>
                        <tr class="menu-row">
                            <td class="menu-btn menu-icon-patient">
                                <a href="<?= getWaitingRoomLink($usergroup); ?>" class="non-style-link-menu">
                                    <div><p class="menu-text">Waiting Room</p></div>
                                </a>
                            </td>
                        </tr>
                    </table>
                </div>
            </nav>
        </div>
        <!-- Dark Overlay element -->
        <div class="overlay"></div>
    </div>
</body>
</html>
