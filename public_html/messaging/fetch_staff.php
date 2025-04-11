<?php
include '../init.php';
include '../config.php';

if (isset($_GET['department'])) {
    $department = $_GET['department'];
    $stmt = $conn->prepare("SELECT registration_id, username FROM users WHERE usergroup = ?");
    if ($stmt) {
        $stmt->bind_param('s', $department);
        $stmt->execute();
        $result = $stmt->get_result();
        $selectedRecipients = isset($_POST['selectedRecipients']) ? $_POST['selectedRecipients'] : [];
        while ($row = $result->fetch_assoc()) {
            echo '<div>';
            echo '<input type="checkbox" name="recipients[]" value="' . $row['registration_id'] . '">';
            echo htmlspecialchars($row['username'], ENT_QUOTES);
            echo '</div>';
        }
        $stmt->close();
    } else {
        echo 'Error: ' . $conn->error;
    }
}
?>
