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

// ** Fetch Unread Messages Count **
$unreadQuery = "SELECT COUNT(*) AS unread_count FROM messages WHERE receiver_id = ? AND status = 'unread' AND parent_id IS NULL";
$stmt = $conn->prepare($unreadQuery);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$unreadResult = $stmt->get_result();
$unreadCount = $unreadResult->fetch_assoc()['unread_count'];
$stmt->close();

// ** Pagination Logic **
$limit = 10; // Number of messages per page
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1; // Current page
$offset = ($page - 1) * $limit; // Offset for SQL query

// Count total number of sent messages for pagination
$countQuery = "SELECT COUNT(*) AS total FROM messages WHERE sender_id = ?";
$stmt = $conn->prepare($countQuery);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$countResult = $stmt->get_result();
$totalMessages = $countResult->fetch_assoc()['total'];
$stmt->close();

// Calculate total pages
$totalPages = ceil($totalMessages / $limit);

// Fetch sent messages with pagination
$query = "
    SELECT messages.*, CONCAT(users.first_name, ' ', users.middle_name, ' ', users.surname) AS receiver_name 
    FROM messages 
    JOIN users ON messages.receiver_id = users.registration_id 
    WHERE messages.sender_id = ? 
    ORDER BY messages.created_at DESC
    LIMIT ? OFFSET ?"; // Add LIMIT and OFFSET for pagination

$stmt = $conn->prepare($query);

if (!$stmt) {
    die('SQL Error: ' . $conn->error);
}

$stmt->bind_param('iii', $user_id, $limit, $offset);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <title>Sent Messages</title>
</head>
<body>
    <!-- Nav Bar -->
    <nav class="message-header">
        <h2>Sent</h2>
        <a href="inbox.php">Back to Inbox</a>
    </nav>
    <!-- Main Container -->
    <div class="container">
       <!-- Include the messaging sidebar -->
       <?php include 'messaging_sidebar.php'; ?>

        <div class="main-content">
            <table>
                <tr>
                    <th>Receiver</th>
                    <th>Subject</th>
                    <th>Sent At</th>
                    <th>Action</th>
                </tr>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['receiver_name'], ENT_QUOTES); ?></td>
                        <td><?= htmlspecialchars($row['subject'], ENT_QUOTES); ?></td>
                        <td><?= htmlspecialchars($row['created_at'], ENT_QUOTES); ?></td>
                        <td>
                            <a href="view_message.php?id=<?= $row['id']; ?>">View</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </table>

            <!-- Pagination -->
            <div class="pagination">
                <?php if ($page > 1): ?>
                    <a href="?page=<?= $page - 1; ?>">&laquo; Previous</a>
                <?php endif; ?>

                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <a href="?page=<?= $i; ?>" <?= ($i === $page) ? 'class="active"' : ''; ?>><?= $i; ?></a>
                <?php endfor; ?>

                <?php if ($page < $totalPages): ?>
                    <a href="?page=<?= $page + 1; ?>">Next &raquo;</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
