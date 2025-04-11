<?php
include '../init.php';
include '../config.php';

// Fetch upcoming appointments
$today = date('Y-m-d');

$sqlUpcoming = "SELECT a.appointment_id, a.appointment_date, a.appointment_time, a.department, a.type, 
                p.patient_id, p.first_name, p.middle_name, p.surname 
                FROM appointments a 
                JOIN patient_db p ON a.patient_id = p.patient_id 
                WHERE a.appointment_date >= '$today' 
                ORDER BY a.appointment_date ASC 
                LIMIT 10"; // Fetch 10 records

$resultUpcoming = $conn->query($sqlUpcoming);

if ($resultUpcoming->num_rows > 0) {
    while ($row = $resultUpcoming->fetch_assoc()) {
        echo "<tr>
                <td>{$row['first_name']} {$row['middle_name']} {$row['surname']}</td>
                <td>{$row['patient_id']}</td>
                <td>{$row['appointment_id']}</td>
                <td>{$row['type']}</td>
                <td>{$row['appointment_date']}</td>
                <td>{$row['appointment_time']}</td>
                <td data-label='Actions'>
                    <a href='#' class='btn-view' onclick=\"openModal('view', '{$row['appointment_id']}')\">View</a>
                    <a href='#' class='btn-reschedule' onclick=\"openModal('reschedule', '{$row['appointment_id']}')\">Reschedule</a>
                    <a href='#' class='btn-delete' onclick=\"openModal('cancel', '{$row['appointment_id']}')\">Cancel</a>
                </td>
            </tr>";
    }
} else {
    echo "<tr><td colspan='7'><center>No upcoming appointments found.</center></td></tr>";
}
$conn->close();
?>
