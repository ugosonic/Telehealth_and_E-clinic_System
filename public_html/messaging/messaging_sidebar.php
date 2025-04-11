<?php
// messaging_sidebar.php

// Ensure the session is started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Ensure the user is authenticated
if (!isset($_SESSION['usergroup']) || $_SESSION['usergroup'] === 'Patient') {
    header('Location: /My Clinic/login/login.php');
    exit();
}


if (!isset($user_id)) {
    $user_id = $_SESSION['registration_id'];
}

// Fetch the number of unread messages
$unreadQuery = "SELECT COUNT(*) AS unread_count FROM messages WHERE receiver_id = ? AND is_read = 0 AND parent_id IS NULL AND is_deleted_by_receiver = FALSE";
$stmt = $conn->prepare($unreadQuery);
if (!$stmt) {
    die('Prepare failed: ' . $conn->error);
}
$stmt->bind_param('i', $user_id);
$stmt->execute();
$unreadResult = $stmt->get_result();
$unreadCount = $unreadResult->fetch_assoc()['unread_count'];
$stmt->close();
?>

<!-- Sidebar Links -->
<div class="message-sidebar">
    <a href="new_message.php">Compose Message</a>
    <a href="inbox.php">Inbox (<span class="unreadCount"><?= $unreadCount; ?></span>)</a>
    <a href="sent.php">Sent</a>
    <a href="draft.php">Draft</a>
    
    <?php if ($_SESSION['usergroup'] === 'Admin'): ?>
        <a href="reports.php">Reported Messages</a>
    <?php endif; ?>
</div>
