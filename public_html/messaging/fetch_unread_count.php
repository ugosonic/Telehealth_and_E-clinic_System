<?php
session_start();
include '../init.php';
include '../config.php';

if (!isset($_SESSION['registration_id'])) {
    echo json_encode(['unread_count' => 0]);
    exit();
}

$user_id = $_SESSION['registration_id'];

$unreadQuery = "SELECT COUNT(*) AS unread_count FROM messages WHERE receiver_id = ? AND is_read = 0 AND parent_id IS NULL AND is_deleted_by_receiver = FALSE";
$stmt = $conn->prepare($unreadQuery);
if (!$stmt) {
    die('Prepare failed: ' . $conn->error);
}
$stmt->bind_param('i', $user_id);
$stmt->execute();
$result = $stmt->get_result();
$unreadCount = $result->fetch_assoc()['unread_count'] ?? 0;
$stmt->close();

echo json_encode(['unread_count' => $unreadCount]);
?>
