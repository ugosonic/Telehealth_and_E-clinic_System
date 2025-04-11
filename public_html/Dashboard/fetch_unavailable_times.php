<?php
include '../init.php';
include '../config.php';

// Get the selected date from the request
$selectedDate = $_GET['date'];

// Prepare a query to fetch unavailable times
$stmt = $con->prepare('SELECT appointment_time FROM appointments WHERE appointment_date = ?');
$stmt->bind_param('s', $selectedDate);
$stmt->execute();
$result = $stmt->get_result();

$unavailableTimes = [];
while ($row = $result->fetch_assoc()) {
    $unavailableTimes[] = $row['appointment_time'];
}

$stmt->close();
$con->close();

// Return the unavailable times as JSON
header('Content-Type: application/json');
echo json_encode($unavailableTimes);
?>
