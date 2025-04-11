<?php
session_start(); // Start the session to maintain user login status

// Check if the user is logged in
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

// Database connection info
$DATABASE_HOST = 'localhost';
$DATABASE_USER = 'root';
$DATABASE_PASS = '';
$DATABASE_NAME = 'administrator';

// Establish connection to the database
$con = mysqli_connect($DATABASE_HOST, $DATABASE_USER, $DATABASE_PASS, $DATABASE_NAME);
if (mysqli_connect_errno()) {
    exit('Failed to connect to MySQL: ' . mysqli_connect_error());
}

if (isset($_GET['id'])) {
    $waiting_id = $_GET['id'];
    $staff_name = $_SESSION['username']; // Get the staff name from session

    // Update the waiting room entry status to 'Cancelled', set the check_out_time, and store the staff name
    $stmt = $con->prepare("UPDATE waiting_room SET status = 'Cancelled', check_out_time = NOW(), staff_name = ? WHERE waiting_id = ?");
    $stmt->bind_param("si", $staff_name, $waiting_id);

    if ($stmt->execute()) {
        echo "Patient has been removed from the waiting room.";
    } else {
        echo "Error canceling patient: " . $stmt->error;
    }

    $stmt->close();
}
?>
