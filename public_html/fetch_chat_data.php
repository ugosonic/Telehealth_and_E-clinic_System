<?php
// fetch_chat_data.php
include 'init.php';
include 'config.php';

$meeting_id = $_GET['meeting_id'];

// Fetch chat history
$chatHistory = [];
$stmt = $conn->prepare('SELECT username, message, created_at, status FROM session_messages WHERE meeting_id = ? ORDER BY created_at ASC');
$stmt->bind_param('s', $meeting_id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $chatHistory[] = $row;
}
$stmt->close();

// Fetch users in the chat
$usersInChat = [];
$stmt = $conn->prepare('SELECT DISTINCT username FROM session_messages WHERE meeting_id = ?');
$stmt->bind_param('s', $meeting_id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $usersInChat[] = $row['username'];
}
$stmt->close();

echo json_encode([
    'chatHistory' => $chatHistory,
    'usersInChat' => $usersInChat
]);
?>
