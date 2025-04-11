<?php
include '../init.php'; // Include your database connection

// Get the selected date, department, and type from the request
$selectedDate = $_GET['date'] ?? '';
$department = $_GET['department'] ?? '';
$type = $_GET['type'] ?? '';

if ($selectedDate && $department && $type) {
    // Prepare a query to fetch unavailable times
    $stmt = $conn->prepare('SELECT appointment_time FROM appointments WHERE appointment_date = ? AND department = ? AND type = ?');
    $stmt->bind_param('sss', $selectedDate, $department, $type);
    $stmt->execute();
    $result = $stmt->get_result();

    $unavailableTimes = [];
    while ($row = $result->fetch_assoc()) {
        $unavailableTimes[] = $row['appointment_time'];
    }

    $stmt->close();
    $conn->close();

    // Return the unavailable times as JSON
    header('Content-Type: application/json');
    echo json_encode($unavailableTimes);
}
?>
