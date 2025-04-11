<?php 
session_start();
include '../init.php';
include '../config.php';
include '../sidebar.php';

if (!isset($_SESSION['registration_id'])) {
    header('Location: /My Clinic/login/login.php');
    exit();
}

$user_id = $_SESSION['registration_id'];
$message_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Update message to mark as read
$updateQuery = "UPDATE messages SET is_read = 1 WHERE id = ? AND receiver_id = ? AND is_read = 0";
$stmt = $conn->prepare($updateQuery);
if (!$stmt) {
    die('Prepare failed: ' . $conn->error);
}
$stmt->bind_param('ii', $message_id, $user_id);
if (!$stmt->execute()) {
    die('Execute failed: ' . $stmt->error);
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
$unreadResult = $stmt->get_result();
$unreadCount = $unreadResult->fetch_assoc()['unread_count'] ?? 0;  // Handle if count is NULL
$stmt->close();

// Fetch the original message
$query = "SELECT * FROM messages WHERE id = ?";
$stmt = $conn->prepare($query);
if (!$stmt) {
    die('SQL Error: ' . $conn->error);
}
$stmt->bind_param('i', $message_id);
$stmt->execute();
$message = $stmt->get_result()->fetch_assoc();
$stmt->close();


// Fetch sender details
$sender_id = $message['sender_id'];
$stmt = $conn->prepare("SELECT first_name, middle_name, surname, usergroup FROM users WHERE registration_id = ?");

if (!$stmt) {
    die('SQL Error: ' . $conn->error);
}

$stmt->bind_param('i', $sender_id);
$stmt->execute();
$sender = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Fetch all replies to this message
$replies_query = "SELECT m.*, CONCAT(u.first_name, ' ', u.middle_name, ' ', u.surname) AS full_name, u.usergroup 
                  FROM messages m
                  JOIN users u ON m.sender_id = u.registration_id 
                  WHERE m.parent_id = ? 
                  ORDER BY m.created_at ASC";
$stmt = $conn->prepare($replies_query);

if (!$stmt) {
    die('SQL Error: ' . $conn->error);
}

$stmt->bind_param('i', $message_id);
$stmt->execute();
$replies = $stmt->get_result();
$stmt->close();

// Function to calculate time ago
function timeAgo($datetime) {
    date_default_timezone_set('Europe/London');
    $interval = (new DateTime())->diff(new DateTime($datetime));
    if ($interval->y > 0) return $interval->y . ' year(s) ago';
    if ($interval->m > 0) return $interval->m . ' month(s) ago';
    if ($interval->d > 0) return $interval->d . ' day(s) ago';
    if ($interval->h > 0) return $interval->h . ' hour(s) ago';
    if ($interval->i > 0) return $interval->i . ' minute(s) ago';
    return 'Just now';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <title>View Message</title>
    <style>
        /* General Body Styling */
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f1f3f4;
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        /* Nav Bar Styling */
        nav.message-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            width: 100%;
            background-color: #007bff;
            color: #fff;
            padding: 15px 30px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            position: fixed;
            top: 0;
            z-index: 1000;
            max-width: 92vw;
        }

        nav.message-header h2 {
            font-size: 1.5rem;
            margin: 0;
            color: #fff;
        }

        nav.message-header a {
            color: #fff;
            text-decoration: none;
            font-weight: bold;
            background-color: #0062cc;
            padding: 10px 15px;
            border-radius: 5px;
            transition: background-color 0.3s ease;
        }

        nav.message-header a:hover {
            background-color: #0056b3;
            text-decoration: underline;
        }

        /* Main Container Styling */
        .container {
            display: flex;
            width: 100%;
            max-width: 1200px;
            margin: 20px auto;
            padding: 0 20px;
            box-sizing: border-box;
            flex-grow: 1;
            padding-top: 35px;
        }

        /* Sidebar Styling */
        .message-sidebar {
            width: 250px;
            background-color: #f8f9fa;
            padding: 20px;
            border-right: 1px solid #ddd;
            height: 100%;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .message-sidebar a {
            display: block;
            padding: 10px;
            color: #333;
            text-decoration: none;
            margin-bottom: 10px;
            border-radius: 5px;
            font-weight: bold;
            font-size: 14px;
        }

        .message-sidebar a:hover {
            background-color: #e8f0fe;
            color: #1a73e8;
        }

        /* Main Content Styling */
        .main-content {
            flex-grow: 1;
            padding: 20px;
            box-sizing: border-box;
            background-color: #ffffff;
            border-radius: 8px;
            margin-left: 20px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        /* Message Details */
        .message-details {
            padding: 15px;
            background-color: #f1f3f4;
            border-radius: 8px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
            color: #5f6368;
        }

        .message-details p {
            margin: 4px 0;
            font-size: 14px;
        }

        .message-details strong {
            font-weight: 500;
            color: #202124;
        }

        /* Main Message Body */
        .message-body {
            margin-bottom: 20px;
            line-height: 1.6;
            color: #3c4043;
            padding: 15px;
            background-color: #fff;
            border: 1px solid #ddd;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }

        .message-body p {
            font-size: 15px;
            margin: 0;
        }

        /* Attachment Styling */
        .attachment-container {
            margin-top: 20px;
            padding: 10px;
            background-color: #f1f3f4;
            border-radius: 8px;
            border: 1px solid #ddd;
            margin-bottom: 20px;
        }

        .attachment-preview {
            border: 1px solid #ddd;
            padding: 8px;
            margin-bottom: 10px;
            cursor: pointer;
            display: inline-block;
            max-width: 180px;
            overflow: hidden;
            white-space: nowrap;
            text-overflow: ellipsis;
            background-color: #f8f9fa;
            color: #202124;
            font-size: 14px;
            margin-right: 10px;
        }

        .attachment-preview:hover {
            background-color: #e8f0fe;
        }

        .attachment-container a {
            color: #1a73e8;
            text-decoration: none;
            margin-right: 10px;
            font-size: 14px;
        }

        .attachment-container a:hover {
            text-decoration: underline;
        }

        /* Reply Box */
        .reply-box {
            margin-top: 30px;
            padding: 15px;
            background-color: #fff;
            border-radius: 8px;
            border: 1px solid #ddd;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }

        .reply-box textarea {
            width: 100%;
            height: 120px;
            border-radius: 5px;
            border: 1px solid #ddd;
            padding: 10px;
            margin-bottom: 10px;
            font-size: 14px;
            resize: vertical;
        }

        .reply-box button {
            background-color: #1a73e8;
            color: #fff;
            padding: 8px 16px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            transition: background-color 0.3s;
        }

        .reply-box button:hover {
            background-color: #174ea6;
        }

        /* Replies Section */
        .replies-section {
            margin-top: 20px;
            padding-top: 15px;
        }

        .reply-item {
            background-color: #e8f0fe;
            border-radius: 8px;
            padding: 10px;
            margin-bottom: 10px;
            border: 1px solid #ddd;
            position: relative;
        }

        .reply-item::before {
            content: "";
            width: 5px;
            height: 100%;
            background-color: #1a73e8;
            position: absolute;
            left: 0;
            top: 0;
            border-radius: 8px 0 0 8px;
        }

        .reply-item p {
            margin: 5px 0;
            font-size: 14px;
            color: #3c4043;
        }

        .chat-timestamp {
            font-size: 12px;
            color: #5f6368;
            margin-top: 5px;
        }

        /* Responsive Design */
        @media screen and (max-width: 768px) {
            .container {
                flex-direction: column;
                width: 95%;
                margin: 20px;
            }

            .message-sidebar {
                width: 100%;
                border-right: none;
                border-bottom: 1px solid #ddd;
                padding: 15px;
            }

            .main-content {
                padding: 15px;
            }
        }
    </style>
</head>
<body>
    <!-- Nav Bar -->
    <nav class="message-header">
        <h2>View Message</h2>
        <a href="inbox.php">Back to Inbox</a>
    </nav>

    <!-- Main Container -->
    <div class="container">
        <!-- Include the messaging sidebar -->
        <?php include 'messaging_sidebar.php'; ?>

        <!-- Main Content -->
        <div class="main-content">
            <div class="message-details">
                <p><strong>From:</strong> <?= htmlspecialchars($sender['first_name'] . ' ' . $sender['middle_name'] . ' ' . $sender['surname'], ENT_QUOTES); ?></p>
                <p><strong>Department:</strong> <?= htmlspecialchars($sender['usergroup'], ENT_QUOTES); ?></p>
                <p><strong>Subject:</strong> <?= htmlspecialchars($message['subject'], ENT_QUOTES); ?></p>
                <p><strong>Time Sent:</strong> <?= htmlspecialchars($message['created_at'], ENT_QUOTES); ?> (<?= timeAgo($message['created_at']); ?>)</p>
            </div>

            <div class="message-body">
                <p><strong>Message:</strong></p>
                <p><?= nl2br(htmlspecialchars($message['body'], ENT_QUOTES)); ?></p>
            </div>
            
            <?php if ($message['attachment']): ?>
                <div class="attachment-container">
                    <p><strong>Attachment:</strong></p>
                    <a href="<?= htmlspecialchars($message['attachment'], ENT_QUOTES); ?>" target="_blank">
                        <div class="attachment-preview">
                            <?= basename($message['attachment']); ?>
                        </div>
                    </a>
                    <p><a href="<?= htmlspecialchars($message['attachment'], ENT_QUOTES); ?>" target="_blank">View Full</a> | 
                    <a href="<?= htmlspecialchars($message['attachment'], ENT_QUOTES); ?>" download>Download</a></p>
                </div>
            <?php endif; ?>

            <div class="replies-section">
                <h3>Replies</h3>
                <?php while ($reply = $replies->fetch_assoc()): ?>
                    <div class="reply-item">
                        <p><strong>From:</strong> <?= htmlspecialchars($reply['full_name'], ENT_QUOTES); ?> (<?= htmlspecialchars($reply['usergroup'], ENT_QUOTES); ?>)</p>
                        <p><strong>Time Sent:</strong> <?= htmlspecialchars($reply['created_at'], ENT_QUOTES); ?> (<?= timeAgo($reply['created_at']); ?>)</p>
                        <p><?= nl2br(htmlspecialchars($reply['body'], ENT_QUOTES)); ?></p>
                        <?php if ($reply['attachment']): ?>
                            <a href="<?= htmlspecialchars($reply['attachment'], ENT_QUOTES); ?>" target="_blank">View Full</a> | 
                            <a href="<?= htmlspecialchars($reply['attachment'], ENT_QUOTES); ?>" download>Download</a>
                        <?php endif; ?>
                    </div>
                <?php endwhile; ?>
            </div>

            <div class="reply-box">
                <h3>Reply</h3>
                <form action="reply_message.php" method="post" enctype="multipart/form-data">
                    <textarea name="reply_body" placeholder="Write your reply here..." required></textarea>
                    <input type="hidden" name="parent_id" value="<?= $message_id; ?>">
                    <input type="file" name="attachment" />
                    <button type="submit">Send Reply</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
