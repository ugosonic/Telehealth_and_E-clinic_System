<?php
include '../init.php';
include '../config.php';

session_start();

if (isset($_POST['message_id']) && isset($_POST['status'])) {
    $message_id = $_POST['message_id'];
    $status = $_POST['status'];

    $stmt = $conn->prepare("UPDATE messages SET status = ? WHERE id = ?");
    $stmt->bind_param('si', $status, $message_id);
    $stmt->execute();
    $stmt->close();

    echo json_encode(['success' => true]);
}
?>
