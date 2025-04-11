<?php
require_once '../init.php';
require_once '../config.php';

$query = isset($_GET['query']) ? $_GET['query'] : '';
$query = $con->real_escape_string($query);

$sql = "SELECT patient_id, first_name, middle_name, surname FROM patient_db WHERE 
        patient_id LIKE '%$query%' OR 
        first_name LIKE '%$query%' OR 
        middle_name LIKE '%$query%' OR 
        surname LIKE '%$query%'";

$result = $con->query($sql);
$patients = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $patients[] = $row;
    }
}
echo json_encode($patients);
?>
