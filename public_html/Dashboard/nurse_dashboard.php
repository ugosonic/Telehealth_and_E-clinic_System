<?php
session_start();

// Database connection info
require_once '../init.php';
require_once '../config.php';
// Fetch user details
$username = '';
$email = '';
if (isset($_SESSION['username'])) {
    $stmt = $con->prepare('SELECT username, email FROM users WHERE username = ?');
    $stmt->bind_param('s', $_SESSION['username']);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $username = $row['username'];
        $email = $row['email'];
    } else {
        echo "<p>User not found. Please <a href='/my clinic/login/login.php'>login again</a>.</p>";
    }
    $stmt->close();
}

// Define content based on action
$action = isset($_GET['action']) ? $_GET['action'] : '';

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/animations.css">  
    <link rel="stylesheet" href="../css/main.css">  
    <link rel="stylesheet" href="../css/admin.css">
    <link rel="stylesheet" href="../css/sidebar.css">
    <link rel="stylesheet" href="admin_dashboard.css">
    <script src="sidebar.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <title>Nurse Dashboard</title>
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
                                                <a href="/my clinic/logout.php"><input type="button" value="Log out" class="logout-btn btn-primary-soft btn"></a>
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                            <!-- Navigation links -->
                            <tr class="menu-row"><td class="menu-btn menu-icon-dashbord menu-active"><a href="patient_dashboard.php" class="non-style-link-menu-active"><div><p class="menu-text">Dashboard</p></div></a></td></tr>
                            <tr class="menu-row"><td class="menu-btn menu-icon-doctor"><a href="doctors.php" class="non-style-link-menu"><div><p class="menu-text">Doctors</p></div></a></td></tr>
                            <tr class="menu-row"><td class="menu-btn menu-icon-schedule"><a href="schedule.php" class="non-style-link-menu"><div><p class="menu-text">Schedule</p></div></a></td></tr>
                            <tr class="menu-row"><td class="menu-btn menu-icon-appoinment"><a href="appointment.php" class="non-style-link-menu"><div><p class="menu-text">Appointment</p></div></a></td></tr>
                            <tr class="menu-row"><td class="menu-btn menu-icon-patient"><a href="patient.php" class="non-style-link-menu"><div><p class="menu-text">Patients</p></div></a></td></tr>
                        </table>
                    </div>
                </nav>
            </div>
             <!-- Dark Overlay element -->
             <div class="overlay"></div>
        </div>
        <div class="dash-body">
            <!-- Content area -->
            <p class="dashboard-header">Nurse Dashboard</p>
            <div class="icon-row">
                <div class="icon-box">
                <a href="/my clinic/patient/medical_records.php">
                    <i class="fa fa-users fa-3x" style="color: blue;"></i>
                    <p>Medical Records</p>
                </div>
              
                <div class="icon-box">
                    <a href="/my clinic/waiting_room/nurse_waiting_room.php">
                        <i class="fa fa-clock-o fa-3x" style="color: orange;"></i>
                        <p>Waiting Room</p>
                    </a>
                </div>
            
                <div class="icon-box">
                    <a href="/my clinic/reports.php">
                        <i class="fa fa-bar-chart fa-3x" style="color: purple;"></i>
                        <p>Reports and Analysis</p>
                    </a>
                </div>
                <div class="icon-box">
                    <a href="/my clinic/messaging.php">
                        <i class="fa fa-envelope fa-3x" style="color: red;"></i>
                        <p>Messaging</p>
                    </a>
                </div>
                <div class="icon-box">
                    <a href="/my clinic/profile.php">
                        <i class="fa fa-user fa-3x" style="color: cyan;"></i>
                        <p>Profile</p>
                    </a>
                </div>
                <div class="icon-box">
                    <a href="/my clinic/appointment_manager/scheduler.php">
                        <i class="fa fa-calendar fa-3x" style="color: magenta;"></i>
                        <p>Appointment Manager</p>
                    </a>
                </div>
                <div class="icon-box">
                    <a href="/my clinic/settings.php">
                        <i class="fa fa-cog fa-3x" style="color: gray;"></i>
                        <p>Settings</p>
                    </a>
                </div>
            </div>
                
            <div class="thick-line"></div>

            <div class="container">
                <div class="section">
                    <h2>Section 1</h2>
                    <p>Content for section 1.</p>
                </div>
                <div class="section">
                    <h2>Section 2</h2>
                    <p>Content for section 2.</p>
                </div>
                <div class="section">
                    <h2>Section 3</h2>
                    <p>Content for section 3.</p>
                </div>
            </div>
        </div>
    </div>

</body>
</html>
