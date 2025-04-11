<?php
session_start();
require_once '../init.php';
require_once '../config.php';
if (!isset($_SESSION['usergroup']) || $_SESSION['usergroup'] !== 'Doctor') {
    header("Location: ../login/login.php");
    exit();
}
require_once '../init.php';

if ($_SERVER['REQUEST_METHOD']==='POST') {
    $wid     = isset($_POST['waiting_id']) ? intval($_POST['waiting_id']) : 0;
    $notes   = $_POST['notes'] ?? '';
    $priority= isset($_POST['priority']) ? 1 : 0;

    if ($wid > 0) {
        $nEsc = $conn->real_escape_string($notes);
        $priority = $priority ? 1 : 0;
        $upd = "
          UPDATE waiting_room
          SET notes = '$nEsc', priority = $priority
          WHERE waiting_id = $wid
        ";
        $conn->query($upd);
        $_SESSION['message'] = 'Notes updated successfully.';
    }
}
    header("Location: doctor_waiting_room.php");
    exit();
    