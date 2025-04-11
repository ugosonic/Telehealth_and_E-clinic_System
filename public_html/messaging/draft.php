<?php
session_start();
include '../init.php';
include '../config.php';
include '../sidebar.php';

if (!isset($_SESSION['usergroup']) || $_SESSION['usergroup'] === 'Patient') {
    header('Location: /My Clinic/login/login.php');
    exit();
}

$user_id = $_SESSION['registration_id'];

// Query to fetch draft messages
$query = "SELECT id, subject, created_at, updated_at FROM messages WHERE sender_id = ? AND status = 'draft' ORDER BY updated_at DESC";

$stmt = $conn->prepare($query);

if (!$stmt) {
    die('SQL Error: ' . $conn->error);
}

$stmt->bind_param('i', $user_id);
$stmt->execute();
$result = $stmt->get_result();

// Fetch the number of unread messages
$unreadQuery = "SELECT COUNT(*) AS unread_count FROM messages WHERE receiver_id = ? AND status = 'unread' AND parent_id IS NULL";
$stmt = $conn->prepare($unreadQuery);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$unreadResult = $stmt->get_result();
$unreadCount = $unreadResult->fetch_assoc()['unread_count'];
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <title>Draft Messages</title>
</head>
<body>
<div class="inbox-container">
    <nav class="message-header">
        <h2>Draft Messages</h2>
        <a href="new_message.php">Compose New Message</a>
    </nav>

    <div class="container">
        <!-- Include the messaging sidebar -->
        <?php include 'messaging_sidebar.php'; ?>

        <div class="main-content">
            <!-- Display message if no drafts are available -->
            <?php if ($result->num_rows === 0): ?>
                <p>No draft messages available.</p>
            <?php else: ?>
                <table>
                    <tr>
                        <th>Subject</th>
                        <th>Created At</th>
                        <th>Last Updated At</th>
                        <th>Action</th>
                    </tr>
                    <!-- Loop through each draft message -->
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['subject'], ENT_QUOTES); ?></td>
                            <td><?= htmlspecialchars($row['created_at'], ENT_QUOTES); ?></td>
                            <td><?= htmlspecialchars($row['updated_at'], ENT_QUOTES); ?></td>
                            <td>
    <a href="new_message.php?draft_id=<?= $row['id']; ?>">Edit</a>
</td>

                        </tr>
                    <?php endwhile; ?>
                </table>
            <?php endif; ?>
        </div>
    </div>
</div>
</body>
</html>
