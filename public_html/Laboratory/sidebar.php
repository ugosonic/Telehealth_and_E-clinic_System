<?php

require_once '../init.php';
require_once '../config.php';
// Function to check if user has required role
function hasRequiredRole($usergroup) {
    $allowedRoles = ['Lab Scientist', 'Admin', 'Doctor'];
    return in_array($usergroup, $allowedRoles);
}

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

        // Check if user has required role
        if (!hasRequiredRole($usergroup)) {
            // Redirect to unauthorized page if user doesn't have required role
            header("Location: ../unauthorized.php");
            exit();
        }
    } else {
        // Redirect to login if user not found in 'users' table
        header("Location: ../login/login.php");
        exit();
    }
    
    $stmt->close();
} else {
    // Redirect to login if not logged in
    header("Location: ../login/login.php");
    exit();
}

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

// Rest of your PHP code (HTML, CSS, JavaScript) goes here
?>

   
    <link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.nicescroll/3.6.8-fix/jquery.nicescroll.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script> <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.nicescroll/3.6.8-fix/jquery.nicescroll.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Include Bootstrap CSS -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">

<!-- Include jQuery -->


<!-- Include Bootstrap Bundle with Popper -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>

    
    <style>
     
        /* Ensure dropdown items are visible */
.dropdown-menu .dropdown-item {
    white-space: nowrap; /* Prevent text from wrapping */
}
        .sidebar-icon {
            width: 100px;
            background-color: #f8f9fa;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: start;
            border-right: 1px solid #ddd;
            position: fixed;
            top: 0;
            left: 0;
            overflow: visible;
            height: 100vh;
            overflow-y: auto; /* Add vertical scrollbar */
            scrollbar-width: thin; /* For Firefox */
            scrollbar-color: #ccc transparent; /* For Firefox */
        }
       
.sidebar-icon .dropdown-menu::before {
    content: '';
    position: absolute;
    top: -10px; /* Adjust based on the dropdown position */
    left: 10px; /* Adjust to point at the dropdown button */
    border-width: 5px;
    border-style: solid;
    border-color: transparent transparent #f8f9fa transparent; /* Arrow pointing downwards */
    z-index: 2001; /* Same z-index to ensure visibility */
}
        /* For Chrome, Edge, and Safari */
        .sidebar-icon::-webkit-scrollbar {
            width: 6px;
        }
        .sidebar-icon::-webkit-scrollbar-thumb {
            background-color: #ccc;
            border-radius: 10px;
        }
        .sidebar-icon a {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin: 15px 0;
            text-decoration: none;
            color: inherit;
        }
        .sidebar-icon i {
            font-size: 1.5rem;
        }
        .sidebar-icon .menu-item__text {
            font-size: 0.8rem;
            text-align: center;
        }
        .sidebar-icon .fa-home { color: #007bff; }
        .sidebar-icon .fa-video { color: #28a745; }
        .sidebar-icon .fa-calendar-alt { color: #ffc107; }
        .sidebar-icon .fa-file-medical { color: #17a2b8; }
        .sidebar-icon .fa-users { color: #6c757d; }
        .sidebar-icon .fa-flask { color: #6610f2; }
        .sidebar-icon .fa-medkit { color: #e83e8c; }
        .sidebar-icon .fa-user-plus { color: #fd7e14; }
        .sidebar-icon .fa-envelope { color: #007bff; }
        .sidebar-icon .fa-cog { color: #6c757d; }

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
        .dropdown-menu .message-icon {
    position: fixed; /* Position dropdown outside of the scrolling sidebar */
    z-index: 1050; /* Set a high z-index value to ensure it appears on top */
    top: auto; /* You can use auto or set a specific top position */
    left: auto; /* Adjust as necessary based on your layout */
    transform: none !important; /* Disable any automatic transform positioning */
    min-width: 200px; /* Set a minimum width for better visibility */
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
       


        .message-icon {
    position: relative;
}

.notification-badge {
    position: absolute;
    top: -5px;
    right: -10px;
    background: red;
    color: white;
    border-radius: 50%;
    padding: 2px 6px;
    font-size: 0.7rem;
    display: none; /* Hidden by default */
}

.notification-badge.dot {
    width: 10px;
    height: 10px;
    padding: 0;
    font-size: 0; /* Hide the text inside the dot */
}

    </style>

<div class="container">
<!-- Sidebar -->
<div class="sidebar-icon">
<a class="navbar-brand" href="#"><i class="fas fa-bars" data-bs-toggle="offcanvas" data-bs-target="#offcanvasSidebar"></i></a>
        <a href="<?= getDashboardLink($usergroup); ?>">
            <i class="fas fa-home"></i>
            <div class="menu-item__text">Dashboard</div>
        </a>
        <a href="<?= getvideoConsultation($usergroup); ?>">
            <i class="fas fa-video"></i>
            <div class="menu-item__text">Video Consultation</div>
        </a>
        <a href="/my clinic/appointment_manager/schedule.php">
            <i class="fas fa-calendar-alt"></i>
            <div class="menu-item__text">Appointments</div>
        </a>
        <a href="/my clinic/Laboratory/create_lab_test.php">
            <i class="fa fa-flask"></i>
            <div class="menu-item__text">Laboratory</div>
        </a>
        <a href="/my clinic/pharmacy_management_system/Dashboard.php">
            <i class="fa fa-medkit"></i>
            <div class="menu-item__text">Pharmacy</div>
        </a>
        <?php if ($usergroup !== 'Patient'): ?>
            <a href="/my clinic/patient/medical_records.php">
                <i class="fas fa-file-medical"></i>
                <div class="menu-item__text">Medical Records</div>
            </a>
            <a href="<?= getWaitingRoomLink($usergroup); ?>">
                <i class="fas fa-users"></i>
                <div class="menu-item__text">Waiting Room</div>
            </a>
            <a href="../staff_registration/registration.php">
                <i class="fa fa-user-plus"></i>
                <div class="menu-item__text">Staff Registration</div>
            </a>
        <?php endif; ?>
        <a href="#" data-bs-toggle="dropdown" class="message-icon">
            <i class="fa fa-envelope" style="color: #007bff;"></i>
            <span class="notification-badge" id="unreadCountBadge"></span>
        </a>
        <ul class="dropdown-menu">
            <li><a class="dropdown-item" href="../messaging/new_message.php">New Message</a></li>
            <li><a class="dropdown-item" href="../messaging/inbox.php">Inbox</a></li>
            <li><a class="dropdown-item" href="../messaging/draft.php">Draft</a></li>
            <li><a class="dropdown-item" href="../messaging/sent.php">Sent</a></li>
        </ul>
            </li>
            <div class="menu-item__text">Messaging</div>
        <a href="<?= ($usergroup === 'Patient') ? '/my clinic/settings/patient_account_settings.php' : '/my clinic/settings/staff_account_settings.php'; ?>">
            <i class="fa fa-cog"></i>
            <div class="menu-item__text">Settings</div>
        </a>
    </div>


<!-- Offcanvas Sidebar -->
<div class="offcanvas offcanvas-start" tabindex="-1" id="offcanvasSidebar" aria-labelledby="offcanvasSidebarLabel">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title" id="offcanvasSidebarLabel">Menu</h5>
        <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">
    <div class="d-flex align-items-center mb-3">
    <?php

    // Fetch additional details
    if ($usergroup === 'Patient') {
        $query = "SELECT first_name, surname, profile_pic FROM patient_db WHERE username = ?";
    } else {
        $query = "SELECT first_name, surname, profile_pic FROM users WHERE username = ?";
    }

    $stmt = $conn->prepare($query);
    $stmt->bind_param('s', $_SESSION['username']);
    $stmt->execute();
    $result = $stmt->get_result();
    $user_row = $result->fetch_assoc();

    if ($user_row) {
        $firstName = $user_row['first_name'] ?? 'Unknown';
        $lastName = $user_row['surname'] ?? 'User';
        $profilePicPath = $user_row['profile_pic'] ?? null;
    } else {
        $firstName = 'Unknown';
        $lastName = 'User';
        $profilePicPath = null;
    }

    $stmt->close();



// Display Profile Picture or Default Avatar
if ($profilePicPath) {
    $absolutePath = $_SERVER['DOCUMENT_ROOT'] . $profilePicPath;
    if (file_exists($absolutePath)) {
        // Use the web-accessible path in the img src attribute
        echo '<img src="' . htmlspecialchars($profilePicPath, ENT_QUOTES) . '" alt="Profile Picture" class="rounded-circle me-2" width="50">';
    } else {
        // File doesn't exist; display default avatar
        generateDefaultAvatar($firstName, $lastName);
    }
} else {
    // No profile picture; display default avatar
    generateDefaultAvatar($firstName, $lastName);
}

// Function to generate default avatar
function generateDefaultAvatar($firstName, $lastName) {
    $initials = '';
    if (!empty($firstName)) {
        $initials .= strtoupper($firstName[0]);
    }
    if (!empty($lastName)) {
        $initials .= strtoupper($lastName[0]);
    }
    if (empty($initials)) {
        $initials = 'U'; // Default initial if none found
    }
    $backgroundColor = getRandomColor(); // Generate a random color
    echo '<div class="rounded-circle me-2" style="
        width: 50px; 
        height: 50px; 
        background-color: ' . $backgroundColor . '; 
        display: flex; 
        align-items: center; 
        justify-content: center; 
        color: white; 
        font-weight: bold; 
        font-size: 18px;">
        ' . htmlspecialchars($initials, ENT_QUOTES) . '
    </div>';
}

// Function to generate random color
function getRandomColor() {
    $colors = ['#007bff', '#28a745', '#ffc107', '#6610f2', '#e83e8c', '#fd7e14', '#17a2b8'];
    return $colors[array_rand($colors)];
}
    ?>
    <div>
        <p class="mb-0"><?= htmlspecialchars($username, ENT_QUOTES); ?></p>
        <p class="small text-muted"><?= htmlspecialchars($email, ENT_QUOTES); ?></p>
    </div>
</div>


        <a href="/my clinic/logout.php" class="btn btn-primary mb-3">Log out</a>
        <!-- Navigation links with icons -->
        <ul class="list-group">
            <li class="list-group-item"><a href="<?= getDashboardLink($usergroup); ?>"><i class="fas fa-home"></i> Dashboard</a></li>
            
            <li class="list-group-item"><a href="<?= getvideoConsultation($usergroup); ?>"><i class="fas fa-video"></i> Video Consultation</a></li>
            <li class="list-group-item"><a href="../appointment_manager/schedule.php"><i class="fas fa-calendar-alt"></i> Appointment</a></li>
            <li class="list-group-item"><a href="/my clinic/Laboratory/dashboard.php"><i class="fa fa-flask"></i> Laboratory</a></li>
            <li class="list-group-item"><a href="/my clinic/pharmacy_management_system/Dashboard.php"><i class="fa fa-medkit"></i> Pharmacy</a></li>
            <?php if ($usergroup !== 'Patient'): ?>
            <li class="list-group-item"><a href="/my clinic/patient/medical_records.php"><i class="fas fa-file-medical"></i> Medical Records</a></li>
            <li class="list-group-item"><a href="<?= getWaitingRoomLink($usergroup); ?>"><i class="fas fa-users"></i> Waiting Room</a></li>
            <li class="list-group-item"><a href="/my clinic/staff_registration/registration.php"><i class="fa fa-user-plus"></i> Staff Registration</a></li>
            <?php endif; ?>
            <li class="list-group-item">
    <div class="dropdown">
        <a href="#" class="dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false"><i class="fa fa-envelope"></i> Messaging</a>
        <ul class="dropdown-menu">
            <li><a class="dropdown-item" href="/My Clinic/messaging/new_message.php">New Message</a></li>
            <li><a class="dropdown-item" href="/My Clinic/messaging/inbox.php">Inbox</a></li>
            <li><a class="dropdown-item" href="/My Clinic/messaging/draft.php">Draft</a></li>
            <li><a class="dropdown-item" href="/My Clinic/messaging/sent.php">Sent</a></li>
        </ul>
    </div>
</li>
            </div>

<!-- In the Offcanvas Sidebar -->
<li class="list-group-item">
    <a href="<?= ($usergroup === 'Patient') ? '/my clinic/settings/patient_account_settings.php' : '/my clinic/settings/staff_account_settings.php'; ?>">
        <i class="fa fa-cog"></i> Settings
    </a>
</li>
            
    
</div>

<script>


function updateUnreadCountBadge() {
    fetch('/my clinic/fetch_unread_message_count.php') // Adjust the path if necessary
        .then(response => response.json())
        .then(data => {
            const unreadCount = data.unread_count;
            const badge = document.getElementById('unreadCountBadge');

            if (unreadCount > 0) {
                if (unreadCount === 1) {
                    // Show dot
                    badge.textContent = '';
                    badge.classList.add('dot');
                } else {
                    // Show count
                    badge.textContent = unreadCount > 9 ? '9+' : unreadCount;
                    badge.classList.remove('dot');
                }
                badge.style.display = 'block';
            } else {
                // Hide badge
                badge.style.display = 'none';
            }
        })
        .catch(error => console.error('Error fetching unread count:', error));
}

// Call the function initially
updateUnreadCountBadge();

// Set up an interval to update the count every 5 seconds
setInterval(updateUnreadCountBadge, 5000);
</script>

