<?php
require_once '../init.php';
require_once '../config.php';

if (isset($_GET['type']) && isset($_GET['term'])) {
    $type = $_GET['type'];
    $term = $con->real_escape_string($_GET['term']);
    
    if ($type == 'staff') {
        $sql = "SELECT username FROM users WHERE username LIKE '%$term%'";
    } elseif ($type == 'patient') {
        $sql = "SELECT patient_id, first_name, middle_name, surname FROM patient_db WHERE CONCAT(first_name, ' ', middle_name, ' ', surname) LIKE '%$term%'";
    }
    
    $result = $con->query($sql);
    $data = array();
    while ($row = $result->fetch_assoc()) {
        $data[] = $type == 'staff' ? $row['username'] : $row['first_name'] . ' ' . $row['middle_name'] . ' ' . $row['surname'];
    }
    echo json_encode($data);
}

if (isset($_GET['patient_name'])) {
    $patient_name = $con->real_escape_string($_GET['patient_name']);
    $sql = "SELECT patient_id, dob FROM patient_db WHERE CONCAT(first_name, ' ', middle_name, ' ', surname) = '$patient_name'";
    $result = $con->query($sql);
    if ($result->num_rows > 0) {
        echo json_encode($result->fetch_assoc());
    } else {
        echo json_encode(array('patient_id' => '', 'dob' => ''));
    }
}
?>
