<?php
session_start();

include 'init.php';
include 'config.php';

// Get the patient ID and waiting room from the POST request
$patient_id = $_POST['patient_id'];
$waiting_room = $_POST['waiting_room'];

// Check if the patient has already been sent to the same waiting room today
$today_date = date('Y-m-d');
$sql_check = "SELECT * FROM waiting_room WHERE patient_id = ? AND waiting_room = ? AND DATE(check_in_time) = ?";
$stmt_check = $conn->prepare($sql_check);
$stmt_check->bind_param("iss", $patient_id, $waiting_room, $today_date);
$stmt_check->execute();
$result_check = $stmt_check->get_result();

if ($result_check->num_rows > 0) {
    // Patient has already been sent to this waiting room today
    $_SESSION['message'] = 'Patient already sent to this waiting room for today.';
    $_SESSION['message_type'] = 'error';
} else {
    // Insert the patient into the waiting_room table
    $sql = "INSERT INTO waiting_room (patient_id, waiting_room, check_in_time, status) VALUES (?, ?, NOW(), 'Waiting')";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("is", $patient_id, $waiting_room);

    if ($stmt->execute()) {
        $_SESSION['message'] = 'Patient sent to waiting room successfully.';
        $_SESSION['message_type'] = 'success';
    } else {
        $_SESSION['message'] = 'Error: ' . $stmt->error;
        $_SESSION['message_type'] = 'error';
    }

    $stmt->close();
}

$stmt_check->close();
$conn->close();

header("Location: patient_record.php?id=$patient_id");
exit();
?>
