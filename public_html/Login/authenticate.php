<?php
session_start(); // Start session to maintain user login status

// Change this to your connection info.
$DATABASE_HOST = 'localhost';
$DATABASE_USER = 'u398331630_myclinic';
$DATABASE_PASS = 'kingsley55A';
$DATABASE_NAME = 'u398331630_administrator';

// Try and connect using the info above.
$con = mysqli_connect($DATABASE_HOST, $DATABASE_USER, $DATABASE_PASS, $DATABASE_NAME);
if (mysqli_connect_errno()) {
    // If there is an error with the connection, stop the script and display the error.
    exit('Failed to connect to MySQL: ' . mysqli_connect_error());
}

// Check if the user is logged in
if (isset($_SESSION['usergroup'])) {
    // Redirect users based on their user group
    switch ($_SESSION['usergroup']) {
        case 'Admin':
            header("Location: ../Dashboard/admin_dashboard.php"); // Redirect to admin dashboard
            break;
        case 'IT':
            header("Location: ../Dashboard/it_dashboard.php"); // Redirect to IT dashboard
            break;
        case 'Doctor':
            header("Location: ../Dashboard/doctor_dashboard.php"); // Redirect to doctor dashboard
            break;
        case 'Nurse':
            header("Location: ../Dashboard/nurse_dashboard.php"); // Redirect to nurse dashboard
            break;
        case 'Lab Scientist':
            header("Location: ../Dashboard/lab_scientist_dashboard.php"); // Redirect to lab scientist dashboard
            break;
        case 'Pharmacist':
            header("Location:  ../Dashboard/pharmacist_dashboard.php"); // Redirect to pharmacist dashboard
            break;
        default:
            header("Location: ../Dashboard/user_dashboard.php"); // Redirect to default user dashboard
            break;
    }
    exit();
} else {
    // If user is not logged in, redirect to login page
    header("Location: ./Login/login.php");
    exit();
}
?>
