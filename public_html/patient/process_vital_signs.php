<?php
session_start();

// Database connection info
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "administrator";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $patient_id = $_POST['patient_id'];
    $patient_name = $_POST['patient_name'];
    $patient_age = $_POST['patient_age'];
    $staff_id = $_POST['staff_id'];
    $staff_name = $_POST['staff_name'];
    $blood_pressure_systolic = $_POST['blood_pressure_systolic'];
    $blood_pressure_diastolic = $_POST['blood_pressure_diastolic'];
    $breathing = $_POST['breathing'];
    $pulse_rate = $_POST['pulse_rate'];
    $temperature = $_POST['temperature'];

    // Get current date and time in Europe/London timezone
    date_default_timezone_set('Europe/London');
    $datetime = date('Y-m-d H:i:s');

    // Prepare and execute the insert query
    $sql = "INSERT INTO vital_signs (patient_id, patient_name, patient_age, staff_id, staff_name, blood_pressure_systolic, blood_pressure_diastolic, breathing, pulse_rate, temperature, datetime) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("isiiisiiids", $patient_id, $patient_name, $patient_age, $staff_id, $staff_name, $blood_pressure_systolic, $blood_pressure_diastolic, $breathing, $pulse_rate, $temperature, $datetime);

    if ($stmt->execute()) {
        echo "Vital signs recorded successfully.";
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
}

$conn->close();
?>
