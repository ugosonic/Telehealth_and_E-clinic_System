<?php
session_start();

// Database connection info
require_once '../init.php';
require_once '../config.php';


// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $appointmentId = $_POST['appointment_id'];
    $cancellationReason = $_POST['cancellation_reason'];

    // Check if the appointment exists
    $stmt = $con->prepare('SELECT * FROM appointments WHERE appointment_id = ?');
    $stmt->bind_param('i', $appointmentId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Cancel the appointment
        $stmt = $con->prepare('INSERT INTO canceled_appointments (appointment_id, cancellation_time, cancellation_reason) VALUES (?, NOW(), ?)');
        $stmt->bind_param('is', $appointmentId, $cancellationReason);

        if ($stmt->execute()) {
            $_SESSION['message'] = "Appointment canceled successfully.";
            $_SESSION['message_type'] = "success";
        } else {
            $_SESSION['message'] = "Error canceling appointment.";
            $_SESSION['message_type'] = "error";
        }

        // Optionally, remove the appointment from the main table
        // $stmt = $con->prepare('DELETE FROM appointments WHERE appointment_id = ?');
        // $stmt->bind_param('i', $appointmentId);
        // $stmt->execute();

    } else {
        $_SESSION['message'] = "Appointment not found.";
        $_SESSION['message_type'] = "error";
    }

    $stmt->close();
    header('Location: schedule.php'); // Redirect to the appointment manager page
    exit();
}

$con->close();
?>
