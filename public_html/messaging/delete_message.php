<?php
session_start();
include '../config.php';

if (!isset($_SESSION['registration_id'])) {
    header('Location: /login.php');
    exit();
}

$message_id = $_GET['id'];
$user_id = $_SESSION['registration_id'];
$usergroup = $_SESSION['usergroup']; // Check if the user is an Admin or a regular user

// Check where the deletion request is coming from
$source = isset($_GET['source']) ? $_GET['source'] : 'inbox'; // Default to inbox

// Soft delete for different user groups
if ($usergroup === 'Admin' && $source === 'reports') {
    // Soft delete by Admin (in reports.php)
    $updateQuery = "UPDATE messages SET is_deleted_by_admin = TRUE WHERE id = ?";
    $stmt = $conn->prepare($updateQuery);
    if ($stmt) {
        $stmt->bind_param('i', $message_id);
        $stmt->execute();
        $stmt->close();
        $_SESSION['success_message'] = "Message deleted successfully from reports.";
    } else {
        $_SESSION['error_message'] = "Failed to delete the message.";
    }
    // Redirect back to reports.php
    header('Location: reports.php');
} else {
    // Soft delete for the receiver only (inbox.php)
    $updateQuery = "UPDATE messages SET is_deleted_by_receiver = TRUE WHERE id = ? AND receiver_id = ?";
    $stmt = $conn->prepare($updateQuery);
    if ($stmt) {
        $stmt->bind_param('ii', $message_id, $user_id);
        $stmt->execute();
        $stmt->close();
        $_SESSION['success_message'] = "Message deleted successfully from inbox.";
    } else {
        $_SESSION['error_message'] = "Failed to delete the message.";
    }
    // Redirect back to inbox.php
    header('Location: inbox.php');
}
exit();
