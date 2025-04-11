<?php
session_start();
include 'config.php'; // Adjust the path based on your directory structure

if (!isset($_SESSION['username'])) {
    echo json_encode(['unread_count' => 0]);
    exit();
}

// Fetch user ID based on username
$username = $_SESSION['username'];
$stmt = $conn->prepare("SELECT registration_id FROM users WHERE username = ?");
$stmt->bind_param('s', $username);
$stmt->execute();
$result = $stmt->get_result();
if ($row = $result->fetch_assoc()) {
    $user_id = $row['registration_id'];
} else {
    // User not found
    echo json_encode(['unread_count' => 0]);
    exit();
}
$stmt->close();

// Fetch the number of unread messages
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
