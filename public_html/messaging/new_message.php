<?php
ob_start(); // Start output buffering
session_start();
include '../init.php';
include '../config.php';
include '../sidebar.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!isset($_SESSION['usergroup']) || $_SESSION['usergroup'] === 'Patient') {
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

// Fetch departments (usergroups) except 'Patient'
$departments = [];
$result = $conn->query("SELECT DISTINCT usergroup FROM users WHERE usergroup != 'Patient'");
while ($row = $result->fetch_assoc()) {
    $departments[] = $row['usergroup'];
}

// Initialize variables
$subject = '';
$body = '';
$draft_id = isset($_GET['draft_id']) ? intval($_GET['draft_id']) : null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $subject = $_POST['subject'];
    $body = $_POST['body'];
    $recipients = isset($_POST['recipients']) ? $_POST['recipients'] : [];
    $draft_id = isset($_POST['draft_id']) ? intval($_POST['draft_id']) : null;

    if (empty($recipients)) {
        $_SESSION['message'] = "Please select at least one recipient.";
        $_SESSION['message_type'] = "error";
        // Do not redirect; allow the script to continue
    } else {
        // Handle file attachment
        $targetDir = __DIR__ . "/uploads/";
        if (!file_exists($targetDir)) {
            mkdir($targetDir, 0777, true);
        }

        $filePath = null;
        if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] === UPLOAD_ERR_OK) {
            $fileName = basename($_FILES['attachment']['name']);
            $filePath = $targetDir . uniqid() . "_" . $fileName;
            if (!move_uploaded_file($_FILES['attachment']['tmp_name'], $filePath)) {
                die('Error uploading file: ' . $_FILES['attachment']['error']);
            }
        }

        foreach ($recipients as $receiver_id) {
            $stmt = $conn->prepare("INSERT INTO messages (sender_id, receiver_id, subject, body, status, attachment) VALUES (?, ?, ?, ?, 'sent', ?)");
            if ($stmt) {
                $stmt->bind_param('iisss', $user_id, $receiver_id, $subject, $body, $filePath);
                $stmt->execute();
                $stmt->close();
            } else {
                die('SQL Error: ' . $conn->error);
            }
        }

        $_SESSION['message'] = "Message sent successfully.";
        $_SESSION['message_type'] = "success";

        // Stop autosaving after message is sent
        echo '<script>clearInterval(autosaveInterval);</script>';

        // After successfully sending the message, delete the draft if it exists
        if ($draft_id) {
            $stmt = $conn->prepare("DELETE FROM messages WHERE id = ? AND sender_id = ?");
            $stmt->bind_param('ii', $draft_id, $user_id);
            $stmt->execute();
            $stmt->close();
        }

        header('Location: new_message.php');
        exit();
    }
} else if ($draft_id) {
    // Load draft data
    $stmt = $conn->prepare("SELECT subject, body FROM messages WHERE id = ? AND sender_id = ? AND status = 'draft'");
    $stmt->bind_param('ii', $draft_id, $user_id);
    $stmt->execute();
    $draft = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    $subject = $draft['subject'];
    $body = $draft['body'];
}

ob_end_flush(); // End output buffering
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Message</title>
    <link rel="stylesheet" href="style.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
</head>
<body>
<div class="inbox-container">
    <nav class="message-header">
        <h2>Compose New Message</h2>
        <a href="inbox.php">Back to Inbox</a>
    </nav>

<div class="container">
<!-- Include the messaging sidebar -->
<?php include 'messaging_sidebar.php'; ?>

<div class="main-content">
<?php if (isset($_SESSION['message'])): ?>
    <div class="<?= $_SESSION['message_type']; ?>-message">
        <?= $_SESSION['message']; ?>
        <?php unset($_SESSION['message'], $_SESSION['message_type']); ?>
    </div>
<?php endif; ?>

<form id="newMessageForm" action="new_message.php" method="post" enctype="multipart/form-data">
<label for="department">Select Department:</label>
<select id="department" name="department" onchange="fetchStaff(this.value)">
    <option value="" disabled>Select department</option>
    <?php foreach ($departments as $department): ?>
        <option value="<?= htmlspecialchars($department, ENT_QUOTES); ?>" <?= (isset($_POST['department']) && $_POST['department'] == $department) ? 'selected' : ''; ?>><?= htmlspecialchars($department, ENT_QUOTES); ?></option>
    <?php endforeach; ?>
</select>
    
    <div id="staff-list">
        <!-- Recipients -->
    </div>
    <!-- Add hidden input to pass draft_id -->
    <input type="hidden" name="draft_id" value="<?= htmlspecialchars($draft_id, ENT_QUOTES); ?>">

    <!-- Existing form fields -->
    <label for="subject">Subject:</label>
    <input type="text" name="subject" id="subject" value="<?= isset($draft['subject']) ? htmlspecialchars($draft['subject'], ENT_QUOTES) : ''; ?>" required>
    
    <label for="body">Message:</label>
    <textarea name="body" id="body" required><?= isset($draft['body']) ? htmlspecialchars($draft['body'], ENT_QUOTES) : ''; ?></textarea>
    
    

    <label for="attachment">Attachment:</label>
    <input type="file" name="attachment" id="attachment">
    
    <!-- Add Save to Draft and Send buttons -->
    <input type="submit" value="Send">
    <button type="button" onclick="saveToDraft()">Save to Draft</button>
</form>

<!-- Autosave message -->
<p id="autosave-message"></p>
</div>
</div>
</div>

<script>
// JavaScript to handle autosaving of drafts
var autosaveInterval;
var draftId = '<?= $draft_id; ?>'; // Initialize with PHP draft ID (if editing an existing draft)

var draftId = '<?= $draft_id; ?>'; // Initialize with PHP draft ID (if editing an existing draft)

// Save draft function
function saveDraft() {
    var subject = $('#subject').val();
    var body = $('#body').val();

    if (subject || body) {
        $.ajax({
            url: 'save_draft.php',
            type: 'POST',
            data: {
                subject: subject,
                body: body,
                draft_id: draftId // Pass current draft_id if exists
            },
            success: function(response) {
                var data = JSON.parse(response);
                if (data.status === 'success') {
                    draftId = data.draft_id; // Store draft_id after the first save
                    $('#autosave-message').text('Draft saved at ' + new Date().toLocaleTimeString());
                    // Update the hidden input field
                    $('input[name="draft_id"]').val(draftId);
                } else {
                    console.error('Draft save failed:', data.message);
                }
            }
        });
    }
}

document.addEventListener('DOMContentLoaded', function() {
    var selectedDepartment = '<?= isset($_POST['department']) ? addslashes($_POST['department']) : ''; ?>';
    if (selectedDepartment) {
        document.getElementById('department').value = selectedDepartment;
        fetchStaff(selectedDepartment);
    }
});


// Save draft when Save to Draft button is clicked
function saveToDraft() {
    saveDraft();
}

// Trigger autosave every 30 seconds
$(document).ready(function() {
    autosaveInterval = setInterval(saveDraft, 30000);
    
    // Save draft when the user leaves the page
    $(window).on('beforeunload', saveDraft);
});

function fetchStaff(department) {
    if (department) {
        fetch('fetch_staff.php?department=' + department)
            .then(response => response.text())
            .then(data => document.getElementById('staff-list').innerHTML = data);
    }
}
</script>

</body>
</html>
