<?php

include '../init.php';
include '../config.php';
include '../access_control.php';

// Check if appointment ID is set
if (isset($_GET['id'])) {
    $appointmentId = $_GET['id'];

    // Prepare and execute the query
    $stmt = $conn->prepare('SELECT a.appointment_id, a.appointment_date, a.appointment_time, a.department, a.type, a.reason, 
                            p.patient_id, p.first_name, p.middle_name, p.surname, p.email 
                            FROM appointments a 
                            JOIN patient_db p ON a.patient_id = p.patient_id 
                            WHERE a.appointment_id = ?');
    $stmt->bind_param('i', $appointmentId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Fetch appointment details
        $row = $result->fetch_assoc();
        echo "<h3>Appointment Details</h3>";
        echo "<p><strong>Patient Name:</strong> {$row['first_name']} {$row['middle_name']} {$row['surname']}</p>";
        echo "<p><strong>Patient ID:</strong> {$row['patient_id']}</p>";
        echo "<p><strong>Email:</strong> {$row['email']}</p>";
        echo "<p><strong>Appointment ID:</strong> {$row['appointment_id']}</p>";
        echo "<p><strong>Type:</strong> {$row['type']}</p>";
        echo "<p><strong>Date:</strong> {$row['appointment_date']}</p>";
        echo "<p><strong>Time:</strong> {$row['appointment_time']}</p>";
        echo "<p><strong>Department:</strong> {$row['department']}</p>";
        echo "<p><strong>Reason:</strong> {$row['reason']}</p>";
    } else {
        echo "<p>Appointment not found.</p>";
    }

    $stmt->close();
} else {
    echo "<p>No appointment ID provided.</p>";
}

$conn->close();
?>
