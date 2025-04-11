<?php
session_start();
include '../init.php';
include '../config.php';
include '../sidebar.php';

// Ensure only admins can access this page
if (!isset($_SESSION['usergroup']) || $_SESSION['usergroup'] !== 'Admin') {
    header('Location: /My Clinic/login/login.php');
    exit();
}

$user_id = $_SESSION['registration_id'];

// Fetch the number of unread messages
$unreadQuery = "SELECT COUNT(*) AS unread_count FROM messages WHERE receiver_id = ? AND status = 'unread' AND parent_id IS NULL";
$stmt = $conn->prepare($unreadQuery);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$unreadResult = $stmt->get_result();
$unreadCount = $unreadResult->fetch_assoc()['unread_count'];
$stmt->close();

$items_per_page = 10; // Number of reports per page
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $items_per_page;

// Fetch total number of reports for pagination
$totalReportsQuery = "SELECT COUNT(*) AS total_reports FROM reports";
$result = $conn->query($totalReportsQuery);
if (!$result) {
    die('SQL Error: ' . $conn->error);
}
$totalReports = $result->fetch_assoc()['total_reports'];

// Fetch reported messages with pagination, exclude soft-deleted messages
$query = "
    SELECT reports.*, messages.subject, messages.body, 
           CONCAT(reporter.first_name, ' ', reporter.middle_name, ' ', reporter.surname) AS reporter_name,
           CONCAT(sender.first_name, ' ', sender.middle_name, ' ', sender.surname) AS sender_name
    FROM reports
    JOIN messages ON reports.message_id = messages.id
    JOIN users AS reporter ON reports.reported_by = reporter.registration_id
    JOIN users AS sender ON messages.sender_id = sender.registration_id
    WHERE messages.is_deleted_by_admin = 0
    ORDER BY reports.reported_at DESC
    LIMIT ?, ?";

// Prepare the SQL statement
$stmt = $conn->prepare($query);

// Check for errors in query preparation
if (!$stmt) {
    die('SQL Error in Prepare: ' . $conn->error);
}

// Bind the limit parameters (offset and number of items per page)
$stmt->bind_param('ii', $offset, $items_per_page);

// Execute the query
$stmt->execute();
$reports = $stmt->get_result();

// Calculate total pages for pagination
$totalPages = ceil($totalReports / $items_per_page);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <title>Reported Messages</title>
</head>
<body>
    <div class="inbox-container">
        <nav class="message-header">
            <h2>Reported Messages</h2>
            <a href="inbox.php">Back to Inbox</a>
        </nav>
        <div class="container">
        <!-- Include the messaging sidebar -->
        <?php include 'messaging_sidebar.php'; ?>

        <!-- Main content -->
        <div class="main-content">
            <table>
                <thead>
                    <tr>
                        <th>Message Subject</th>
                        <th>Sender</th>
                        <th>Reported By</th>
                        <th>Report Reason</th>
                        <th>Reported At</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($report = $reports->fetch_assoc()): ?>
                        <tr>
                            <td><a href="view_message.php?id=<?= $report['message_id']; ?>"><?= htmlspecialchars($report['subject'], ENT_QUOTES); ?></a></td>
                            <td><?= htmlspecialchars($report['sender_name'], ENT_QUOTES); ?></td>
                            <td><?= htmlspecialchars($report['reporter_name'], ENT_QUOTES); ?></td>
                            <td><?= htmlspecialchars($report['reason'], ENT_QUOTES); ?></td>
                            <td><?= htmlspecialchars($report['reported_at'], ENT_QUOTES); ?></td>
                            <td>
                                <a href="new_message.php?recipient=<?= urlencode($report['sender_name']); ?>">Reply to Sender</a> | 
                                <a href="new_message.php?recipient=<?= urlencode($report['reporter_name']); ?>">Reply to Reporter</a> |
                                <button onclick="deleteMessage(<?= $report['message_id']; ?>, 'reports')">Delete</button>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
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
    <script>
        function deleteMessage(messageId) {
            if (confirm('Are you sure you want to delete this message?')) {
                window.location.href = 'delete_message.php?id=' + messageId;
            }
        }
    </script>
</body>
</html>
