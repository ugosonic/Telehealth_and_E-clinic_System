<?php
include '../init.php';
include '../config.php';

// Set the default date to today
$selected_date = date('Y-m-d');

// Prepare and execute the query to fetch waiting times for the current day
$sql = "SELECT waiting_room, 
               AVG(TIMESTAMPDIFF(MINUTE, check_in_time, NOW())) AS average_waiting_time 
        FROM waiting_room 
        WHERE DATE(check_in_time) = ? AND status = 'Waiting'
        GROUP BY waiting_room";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $selected_date);
$stmt->execute();
$result = $stmt->get_result();

// Initialize arrays to store data
$waitingRooms = [];
$waitingTimes = [];

while ($row = $result->fetch_assoc()) {
    $waitingRooms[] = $row['waiting_room'];
    $waitingTimes[] = round($row['average_waiting_time']); // Round to nearest minute
}

$stmt->close();
$conn->close();

// Return the data in JSON format
echo json_encode([
    'waitingRooms' => $waitingRooms,
    'waitingTimes' => $waitingTimes
]);
?>
