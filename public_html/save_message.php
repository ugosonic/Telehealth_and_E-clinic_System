<?php
include 'init.php';
include 'config.php';

// Set the content type for the response
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_status']) && $_POST['update_status'] === 'true') {
        // Update the status of messages
        $meeting_id = htmlspecialchars($_POST['meeting_id']);
        $username = htmlspecialchars($_POST['username']);
        
        // Update all messages from 'sent' to 'read' for the given user and meeting ID
        $stmt = $conn->prepare('UPDATE session_messages SET status = "read" WHERE meeting_id = ? AND username != ? AND status = "sent"');
        $stmt->bind_param('ss', $meeting_id, $username);
        if ($stmt->execute()) {
            echo json_encode(['status' => 'success', 'message' => 'Messages updated to read']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to update message status']);
        }
        $stmt->close();
    } else if (isset($_POST['meeting_id'], $_POST['username'], $_POST['message'])) {
        // Save new message to the database
        $meeting_id = htmlspecialchars($_POST['meeting_id']);
        $username = htmlspecialchars($_POST['username']);
        $message = htmlspecialchars($_POST['message']);
        $status = 'sent'; // Set the initial status to 'sent'

        $stmt = $conn->prepare('INSERT INTO session_messages (meeting_id, username, message, status, created_at) VALUES (?, ?, ?, ?, NOW())');
        $stmt->bind_param('ssss', $meeting_id, $username, $message, $status);
        if ($stmt->execute()) {
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to save message']);
        }
        $stmt->close();
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Missing required parameters']);
    }
    $conn->close();
    exit;
}

// If the request is not POST, return an error
echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
?>
