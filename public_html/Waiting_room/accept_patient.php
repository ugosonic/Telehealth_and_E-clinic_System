<?php
session_start();
require_once '../init.php';
require_once '../config.php';
require_once '../access_control.php';
if (!isset($_SESSION['usergroup']) || $_SESSION['usergroup'] !== 'Doctor') {
    header("Location: ../login/login.php");
    exit();
}

$wid = isset($_GET['id']) ? intval($_GET['id']) : 0;
$join = isset($_GET['join']) ? intval($_GET['join']) : 0;
// If "join=1", we will also redirect to the video call page.
$meeting_id = isset($_GET['m']) ? $_GET['m'] : '';

if ($wid <= 0) {
    echo "Invalid ID.";
    exit();
}

// 1) Find the patient
$stmt = $conn->prepare("SELECT patient_id FROM waiting_room WHERE waiting_id = ? LIMIT 1");
$stmt->bind_param('i', $wid);
$stmt->execute();
$result = $stmt->get_result();
$patient = $result->fetch_assoc();
$stmt->close();

if (!$patient) {
    echo "Error: No patient found.";
    exit();
}

// 2) Update waiting_room to Accepted
$patient_id = $patient['patient_id'];
$update = $conn->prepare("UPDATE waiting_room SET status = 'Accepted', check_out_time = NOW() WHERE waiting_id = ?");
$update->bind_param('i', $wid);
$update->execute();
$update->close();

// 3) Redirect logic
if ($join === 1 && !empty($meeting_id)) {
    // If join=1 and we have a meeting_id => go straight to video call
    header("Location: /my clinic/video_call.php?meeting_id=" . urlencode($meeting_id));
    exit();
} else {
    // Otherwise => go to patient's profile
    header("Location: ../patient/patient_record.php?id=" . $patient_id);
    exit();
}
?>
