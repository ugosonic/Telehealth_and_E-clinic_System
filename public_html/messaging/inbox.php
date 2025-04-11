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
$items_per_page = 10; // Number of messages per page
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $items_per_page;

// Handle search and filter functionality
$search_query = isset($_GET['search']) ? $_GET['search'] : '';
$filter_unread = isset($_GET['filter']) && $_GET['filter'] == 'unread';



// Query to fetch messages based on search, unread filter, and pagination
$query = "
    SELECT messages.*, 
           CONCAT(users.first_name, ' ', users.middle_name, ' ', users.surname) AS sender_name, 
           users.usergroup AS sender_department
    FROM messages
    JOIN users ON messages.sender_id = users.registration_id
    WHERE messages.receiver_id = ? 
    AND messages.parent_id IS NULL 
    AND is_deleted_by_receiver = FALSE 
";

if ($filter_unread) {
    $query .= "AND messages.is_read = 0 ";
}

if (!empty($search_query)) {
    $query .= "AND (messages.subject LIKE ? OR users.first_name LIKE ? OR users.surname LIKE ?) ";
}

$query .= "ORDER BY messages.created_at DESC LIMIT ?, ?";


// Prepare and execute query
$stmt = $conn->prepare($query);

if (!empty($search_query)) {
    $search_term = '%' . $search_query . '%';
    $stmt->bind_param('issssi', $user_id, $search_term, $search_term, $search_term, $offset, $items_per_page);
} else {
    $stmt->bind_param('iii', $user_id, $offset, $items_per_page);
}

$stmt->execute();
$result = $stmt->get_result();

// ** Pagination Logic **
$totalMessagesQuery = "SELECT COUNT(*) AS total FROM messages WHERE receiver_id = ? AND parent_id IS NULL AND is_deleted_by_receiver = FALSE";
$stmt = $conn->prepare($totalMessagesQuery);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$totalMessagesResult = $stmt->get_result();
$totalMessages = $totalMessagesResult->fetch_assoc()['total'];
$totalPages = ceil($totalMessages / $items_per_page);
$stmt->close();

// Function to format time (e.g., "xx minutes ago", "yesterday")
function formatTimeAgo($datetime) {
    date_default_timezone_set('Europe/London');
    $now = new DateTime();
    $receivedAt = new DateTime($datetime);
    $interval = $now->diff($receivedAt);

    if ($interval->y > 0) return $interval->y . ' year(s) ago';
    if ($interval->m > 0) return $interval->m . ' month(s) ago';
    if ($interval->d > 1) return $interval->d . ' days ago';
    if ($interval->d == 1) return 'yesterday';
    if ($interval->h > 0) return $interval->h . ' hour(s) ago';
    if ($interval->i > 0) return $interval->i . ' minute(s) ago';
    return 'just now';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <title>Inbox</title>
   
</head>
<body>
<div class="inbox-container">
    
    <!-- Nav Bar -->
    <nav class="message-header">
        <h2>Inbox</h2>
        <a href="new_message.php">Compose Message</a>
    </nav>
    <!-- Search and Filter -->
    <form method="get" action="inbox.php">
            <input type="text" name="search" placeholder="Search messages..." value="<?= htmlspecialchars($search_query); ?>">
            <label><input type="checkbox" name="filter" value="unread" <?= $filter_unread ? 'checked' : ''; ?>> Unread</label>
            <button type="submit">Search</button>
        </form>

    <!-- Display success or error messages -->
    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="alert alert-success">
            <?= $_SESSION['success_message']; ?>
        </div>
        <?php unset($_SESSION['success_message']); ?>
    <?php elseif (isset($_SESSION['error_message'])): ?>
        <div class="alert alert-danger">
            <?= $_SESSION['error_message']; ?>
        </div>
        <?php unset($_SESSION['error_message']); ?>
    <?php endif; ?>
    
    <div class="container">
  <!-- Include the messaging sidebar -->
  <?php include 'messaging_sidebar.php'; ?>

    <div class="main-content">
    <table>
    <thead>
        <tr>
            <th>Sender</th>
            <th>Subject</th>
            <th>Received At</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        <?php while ($row = $result->fetch_assoc()): ?>
            <?php
            // Determine if the message should be bold
            $messageClass = $row['is_read'] == 0 ? 'unread' : 'read';
            ?>
            <tr class="<?= $messageClass; ?>">
                <td>
                    <?= htmlspecialchars($row['sender_name'], ENT_QUOTES); ?>
                    <br>
                    <small>(<?= htmlspecialchars($row['sender_department'], ENT_QUOTES); ?>)</small>
                </td>
                <td>
                    <?= htmlspecialchars($row['subject'], ENT_QUOTES); ?>
                </td>
                <td>
                    <?= formatTimeAgo($row['created_at']); ?>
                </td>
                <td class="action-buttons">
                    <a href="view_message.php?id=<?= $row['id']; ?>">View</a>
                    <button onclick="deleteMessage(<?= $row['id']; ?>)">Delete</button>
                    <a href="report_message.php?id=<?= $row['id']; ?>">Report</a>
                </td>
            </tr>
        <?php endwhile; ?>
    </tbody>
</table>

    
     <!-- Pagination -->
<div class="pagination">
    <?php if ($page > 1): ?>
        <a href="?<?= http_build_query(array_merge($_GET, ['page' => $page - 1])); ?>">&laquo; Previous</a>
    <?php endif; ?>

    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
        <a href="?<?= http_build_query(array_merge($_GET, ['page' => $i])); ?>" <?= ($i === $page) ? 'class="active"' : ''; ?>><?= $i; ?></a>
    <?php endfor; ?>

    <?php if ($page < $totalPages): ?>
        <a href="?<?= http_build_query(array_merge($_GET, ['page' => $page + 1])); ?>">Next &raquo;</a>
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

function updateUnreadCount() {
    fetch('fetch_unread_count.php')
        .then(response => response.json())
        .then(data => {
            const unreadCountElements = document.querySelectorAll('.unreadCount');
            unreadCountElements.forEach(element => {
                element.textContent = data.unread_count;
            });
        })
        .catch(error => console.error('Error fetching unread count:', error));
}

// Call the function initially
updateUnreadCount();

// Set up an interval to update the count every 5 seconds
setInterval(updateUnreadCount, 5000);



function updateInboxMessages() {
    let params = getQueryParams();
    let url = 'fetch_messages.php';
    if (Object.keys(params).length > 0) {
        url += '?' + new URLSearchParams(params).toString();
    }
    fetch(url)
        .then(response => response.text())
        .then(html => {
            document.querySelector('table tbody').innerHTML = html;
        })
        .catch(error => console.error('Error fetching messages:', error));
}



function getQueryParams() {
    let params = {};
    let search = window.location.search.substring(1);
    if (search) {
        search.split('&').forEach(function(part) {
            let [key, value] = part.split('=');
            params[decodeURIComponent(key)] = decodeURIComponent(value || '');
        });
    }
    return params;
}


// Real-time update for unread message count and inbox messages every 5 seconds
document.addEventListener('DOMContentLoaded', function() {
    updateUnreadCount();
    setInterval(function() {
        updateUnreadCount();
        updateInboxMessages();
    }, 5000);
});

</script>

</body>
</html>
