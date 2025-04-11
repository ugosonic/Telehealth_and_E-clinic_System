<?php
include '../init.php';
include '../config.php';

// Function for error handling
function handle_error($message) {
    echo json_encode(['error' => $message]);
    error_log($message);
    exit();
}

// Set the current date
$current_date = date('Y-m-d');

// Query to count the total number of patients in all waiting rooms for today
$sql_waiting_room = "SELECT COUNT(*) AS total_patients_in_waiting_rooms 
                     FROM waiting_room 
                     WHERE status = 'Waiting' AND DATE(check_in_time) = ?";
$stmt_waiting_room = $conn->prepare($sql_waiting_room);
if (!$stmt_waiting_room) {
    handle_error("Prepare failed: (" . $conn->errno . ") " . $conn->error);
}
$stmt_waiting_room->bind_param('s', $current_date);
$stmt_waiting_room->execute();
$result_waiting_room = $stmt_waiting_room->get_result();

if (!$result_waiting_room) {
    handle_error("Count query failed: (" . $conn->errno . ") " . $conn->error);
}

$row_waiting_room = $result_waiting_room->fetch_assoc();
$total_patients_in_waiting_rooms = $row_waiting_room["total_patients_in_waiting_rooms"];

// Query to count the total number of registered patients
$sql_total_patients = "SELECT COUNT(*) AS total_patients FROM patient_db";
$result_total_patients = $conn->query($sql_total_patients);

if (!$result_total_patients) {
    handle_error("Count query failed: (" . $conn->errno . ") " . $conn->error);
}

$row_total_patients = $result_total_patients->fetch_assoc();
$total_patients = $row_total_patients["total_patients"];

// Return both counts as JSON
echo json_encode([
    'total_waiting_room' => $total_patients_in_waiting_rooms,
    'total_patients' => $total_patients
]);

// Close the database connections
$stmt_waiting_room->close();
$conn->close();
?>
