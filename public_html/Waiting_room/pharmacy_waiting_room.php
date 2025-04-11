<?php
session_start(); // Start the session to maintain user login status

// Check if the user is logged in and is a Pharmacist
if (!isset($_SESSION['usergroup']) || $_SESSION['usergroup'] != 'Pharmacist') {
    header("Location: ../Login/login.php");
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

// Fetch patients in the pharmacy waiting room
$sql = "SELECT wr.waiting_id, p.patient_id, p.first_name, p.surname, wr.check_in_time 
        FROM waiting_room wr 
        JOIN patient_db p ON wr.patient_id = p.patient_id 
        WHERE wr.waiting_room = 'Pharmacy' AND wr.status = 'Waiting' 
        ORDER BY wr.check_in_time ASC";
$result = $con->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pharmacy Waiting Room</title>
    <link rel="stylesheet" href="waiting_room.css">
</head>
<body>
    <h2>Pharmacy Waiting Room</h2>
    <div class="container">
        <table class="table">
            <tr>
                <th>Patient Name</th>
                <th>Check-In Time</th>
                <th>Actions</th>
            </tr>
            <?php
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo "<tr>
                        <td>{$row['first_name']} {$row['surname']}</td>
                        <td>{$row['check_in_time']}</td>
                        <td class='actions'>
                            <a href='accept_patient.php?id={$row['waiting_id']}&patient_id={$row['patient_id']}' class='accept'>Accept</a>
                            <a href='cancel_patient.php?id={$row['waiting_id']}' class='cancel'>Cancel</a>
                        </td>
                    </tr>";
                }
            } else {
                echo "<tr><td colspan='3'>No patients in the waiting room.</td></tr>";
            }
            ?>
        </table>
    </div>
</body>
</html>
