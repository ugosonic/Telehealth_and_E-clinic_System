<?php
session_start();
include '../init.php';
include '../config.php';

if (!isset($_SESSION['usergroup']) || $_SESSION['usergroup'] === 'Patient') {
    header('Location: /My Clinic/login/login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $parent_id = $_POST['parent_id'];
    $reply_body = $_POST['reply_body'];
    $sender_id = $_SESSION['registration_id'];

    // Fetch the original message to get the receiver's ID
    $stmt = $conn->prepare("SELECT sender_id FROM messages WHERE id = ?");
    $stmt->bind_param('i', $parent_id);
    $stmt->execute();
    $original_message = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    // Set the receiver ID for the reply
    $receiver_id = $original_message['sender_id'];

    // Handle file attachment
    $filePath = null;
    if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] === UPLOAD_ERR_OK) {
        $fileName = basename($_FILES['attachment']['name']);
        $targetDir = "../messaging/uploads/";
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0777, true); // Ensure the upload directory exists
        }
        $filePath = $targetDir . uniqid() . "_" . $fileName;
        move_uploaded_file($_FILES['attachment']['tmp_name'], $filePath);
    }

    // Insert reply into the database
    $stmt = $conn->prepare("INSERT INTO messages (parent_id, sender_id, receiver_id, subject, body, status, attachment) VALUES (?, ?, ?, '', ?, 'sent', ?)");
    if (!$stmt) {
        die('SQL Error: ' . $conn->error);
    }

    $stmt->bind_param('iiiss', $parent_id, $sender_id, $receiver_id, $reply_body, $filePath);
    if ($stmt->execute()) {
        // Redirect back to view_message.php with the message ID
        header('Location: view_message.php?id=' . $parent_id);
        exit();
    } else {
        echo "Error: " . $stmt->error;
    }
    $stmt->close();
}
?>
