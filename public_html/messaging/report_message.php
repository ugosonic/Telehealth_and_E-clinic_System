<?php
session_start();
include '../init.php';
include '../config.php';

if (!isset($_SESSION['usergroup']) || $_SESSION['usergroup'] === 'Patient') {
    header('Location: /My Clinic/login/login.php');
    exit();
}

$message_id = $_GET['id'];

// Report reason (if you want users to input a reason, otherwise, you can have a default reason)
$reason = "User reported this message.";

// Insert the report into the reports table
$stmt = $conn->prepare("INSERT INTO reports (message_id, reported_by, reason) VALUES (?, ?, ?)");
$stmt->bind_param('iis', $message_id, $_SESSION['registration_id'], $reason);
if ($stmt->execute()) {
    // Redirect back to inbox with success message
    $_SESSION['success_message'] = 'Message reported successfully.';
} else {
    // Redirect back to inbox with error message
    $_SESSION['error_message'] = 'Failed to report the message. Please try again.';
}
$stmt->close();

header('Location: inbox.php');
exit();
?>
