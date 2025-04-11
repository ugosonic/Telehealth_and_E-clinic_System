<?php

include '../init.php';
include '../config.php';
include '../access_control.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $date = $_POST["date"];
    $time = $_POST["time"];
    $type = $_POST["type"];
    $reason = $_POST["reason"];
    $userId = $_SESSION['user_id'];  // Assuming you store user_id in session after login
    $userGroup = $_SESSION['user_group'];  // Assuming you store user_group in session

    $appointmentDate = $date . ' ' . $time;

    // Set patient_id to user_id if the user is a patient (usergroup 'User'), otherwise set it to NULL
    $patientId = ($userGroup == 'User') ? $userId : 'NULL';

    // Check if the selected time slot is already taken
    $checkSql = "SELECT * FROM appointments WHERE appointment_date = '$appointmentDate' AND type = '$type'";
    $checkResult = mysqli_query($conn, $checkSql);

    if (mysqli_num_rows($checkResult) > 0) {
        echo "This time slot is already booked. Please choose another time.";
    } else {
        $sql = "INSERT INTO appointments (patient_id, appointment_date, reason, status, type, approved, created_by, created_by_usergroup) 
                VALUES ($patientId, '$appointmentDate', '$reason', 'Pending', '$type', 0, '$userId', '$userGroup')";

        if (mysqli_query($conn, $sql)) {
            echo "Appointment booked successfully. Please wait for admin approval.";
        } else {
            echo "Error: " . $sql . "<br>" . mysqli_error($conn);
        }
    }
}

mysqli_close($conn);
?>
