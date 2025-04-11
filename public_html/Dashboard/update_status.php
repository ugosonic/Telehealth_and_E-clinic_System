<?php
// update_status.php

session_start();
if (!isset($_SESSION['username'])) {
    exit('User not logged in');
}

require_once '../init.php';
require_once '../config.php';

$username = $_SESSION['username'];
$status = $_POST['status'];  // Get the selected status from the AJAX request

$sql = "UPDATE users SET status = ? WHERE username = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $status, $username);
$stmt->execute();

$stmt->close();
$conn->close();
?>
