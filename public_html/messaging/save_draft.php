<?php 
session_start();
include '../init.php';
include '../config.php';

if (!isset($_SESSION['registration_id'])) {
    die('Unauthorized');
}

$user_id = $_SESSION['registration_id'];
$subject = isset($_POST['subject']) ? $_POST['subject'] : '';
$body = isset($_POST['body']) ? $_POST['body'] : '';
$draft_id = isset($_POST['draft_id']) ? intval($_POST['draft_id']) : null;

if ($subject || $body) {
    if ($draft_id) {
        // Update existing draft
        $query = "UPDATE messages SET subject = ?, body = ?, status = 'draft', updated_at = NOW() WHERE id = ? AND sender_id = ?";
        $stmt = $conn->prepare($query);
        if (!$stmt) {
            echo json_encode(['status' => 'error', 'message' => 'Prepare failed: ' . $conn->error]);
            exit;
        }
        $stmt->bind_param('ssii', $subject, $body, $draft_id, $user_id);
        if (!$stmt->execute()) {
            echo json_encode(['status' => 'error', 'message' => 'Execute failed: ' . $stmt->error]);
            exit;
        }
        $stmt->close();
    } else {
        // Insert new draft
        $query = "INSERT INTO messages (sender_id, subject, body, status, created_at) VALUES (?, ?, ?, 'draft', NOW())";
        $stmt = $conn->prepare($query);
        if (!$stmt) {
            echo json_encode(['status' => 'error', 'message' => 'Prepare failed: ' . $conn->error]);
            exit;
        }
        $stmt->bind_param('iss', $user_id, $subject, $body);
        if (!$stmt->execute()) {
            echo json_encode(['status' => 'error', 'message' => 'Execute failed: ' . $stmt->error]);
            exit;
        }
        $draft_id = $stmt->insert_id; // Get the new draft ID
        $stmt->close();
    }

    echo json_encode(['status' => 'success', 'draft_id' => $draft_id]);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Empty subject or body']);
}
?>
