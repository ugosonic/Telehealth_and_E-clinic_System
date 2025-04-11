<?php
session_start();

// Database connection info
require_once '../init.php';
require_once '../config.php';

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $appointmentId = $_POST['appointment_id'];
    $newDate = $_POST['new_date'];
    $newTime = $_POST['new_time'];

    // Check for conflicting appointments
    $stmt = $con->prepare('SELECT * FROM appointments WHERE appointment_date = ? AND appointment_time = ? AND appointment_id != ?');
    $stmt->bind_param('ssi', $newDate, $newTime, $appointmentId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo json_encode(['status' => 'error', 'message' => 'Appointment slot is already booked. Please choose a different time.']);
    } else {
        // Update the appointment details
        $stmt = $con->prepare('UPDATE appointments SET appointment_date = ?, appointment_time = ? WHERE appointment_id = ?');
        $stmt->bind_param('ssi', $newDate, $newTime, $appointmentId);

        if ($stmt->execute()) {
            echo json_encode(['status' => 'success', 'message' => 'Appointment rescheduled successfully.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Error rescheduling appointment.']);
        }
    }

    $stmt->close();
    exit();
}

$con->close();
?>
