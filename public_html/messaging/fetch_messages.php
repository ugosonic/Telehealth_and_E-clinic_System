<?php
session_start();
include '../config.php';

if (!isset($_SESSION['registration_id'])) {
    echo ''; // If user is not logged in, return empty
    exit();
}

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

$user_id = $_SESSION['registration_id'];
$items_per_page = 10;

// Get current page number from query parameters
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
if ($page < 1) $page = 1;
$offset = ($page - 1) * $items_per_page;

// Get search and filter parameters
$search_query = isset($_GET['search']) ? $_GET['search'] : '';
$filter_unread = isset($_GET['filter']) && $_GET['filter'] == 'unread';

// Build the base query
$query = "
    SELECT messages.*, 
           CONCAT(users.first_name, ' ', users.middle_name, ' ', users.surname) AS sender_name, 
           users.usergroup AS sender_department, 
           (SELECT COUNT(*) FROM messages AS replies WHERE replies.parent_id = messages.id) AS reply_count
    FROM messages
    JOIN users ON messages.sender_id = users.registration_id
    WHERE messages.receiver_id = ? 
    AND messages.parent_id IS NULL 
    AND is_deleted_by_receiver = FALSE 
";

// Build query conditions
$params = [$user_id];
$types = 'i';

if ($filter_unread) {
    $query .= "AND messages.is_read = 0 ";
}

if (!empty($search_query)) {
    $query .= "AND (messages.subject LIKE ? OR users.first_name LIKE ? OR users.surname LIKE ?) ";
    $search_term = '%' . $search_query . '%';
    array_push($params, $search_term, $search_term, $search_term);
    $types .= 'sss';
}

$query .= "
    ORDER BY messages.created_at DESC
    LIMIT ?, ?
";

array_push($params, $offset, $items_per_page);
$types .= 'ii';

// Prepare and execute the statement
$stmt = $conn->prepare($query);
if (!$stmt) {
    die('Prepare failed: ' . $conn->error);
}

// Bind parameters
$stmt->bind_param($types, ...$params);

$stmt->execute();
$result = $stmt->get_result();

// Return only the table rows
while ($row = $result->fetch_assoc()):
    $isUnread = $row['is_read'] == 0;
    $messageClass = $isUnread ? 'unread' : 'read';
?>
    <tr class="<?= $messageClass; ?>">
        <td>
            <?= htmlspecialchars($row['sender_name'], ENT_QUOTES); ?>
            <br>
            <small>(<?= htmlspecialchars($row['sender_department'], ENT_QUOTES); ?>)</small>
        </td>
        <td>
            <?= htmlspecialchars($row['subject'], ENT_QUOTES); ?>
            <?php if ($row['reply_count'] > 1): ?>
                (<?= $row['reply_count']; ?>)
            <?php endif; ?>
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
