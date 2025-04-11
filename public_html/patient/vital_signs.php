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

// Check if the user is logged in and is either Admin, IT, Doctor, or Nurse
if (!isset($_SESSION['usergroup']) || !in_array($_SESSION['usergroup'], ['Admin', 'IT', 'Doctor', 'Nurse'])) {
    header("Location: ../login/login.php");
    exit();
}

// Get the staff details from session
if (!isset($_SESSION['registration_id'])) {
    die("Staff ID is not set in session.");
}

$staff_id = $_SESSION['registration_id'];
$staff_query = "SELECT first_name, surname FROM users WHERE registration_id = ?";
$stmt = $conn->prepare($staff_query);
if ($stmt === false) {
    die("Prepare failed: " . $conn->error);
}
$stmt->bind_param("i", $staff_id);
$stmt->execute();
$staff_result = $stmt->get_result();
if ($staff_result->num_rows == 0) {
    die("Staff record not found.");
}
$staff = $staff_result->fetch_assoc();
$staff_name = $staff['first_name'] . " " . $staff['surname'];
$stmt->close();

// Get patient ID from the URL
$patient_id = isset($_GET['id']) ? $_GET['id'] : 0;

// Prepare and execute the query to fetch patient details
$sql = "SELECT * FROM patient_db WHERE id = ?";
$stmt = $conn->prepare($sql);
if ($stmt === false) {
    die("Prepare failed: " . $conn->error);
}
$stmt->bind_param("i", $patient_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    // Fetch the data
    $patient = $result->fetch_assoc();
    $patient_name = $patient['first_name'] . " " . $patient['middle_name'] . " " . $patient['surname'];
    $patient_age = $patient['age'];
} else {
    die("No records found for Patient ID: " . $patient_id);
}

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Input Vital Signs</title>
    <style>
        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            border: 1px solid #ccc;
            border-radius: 10px;
            background-color: #f9f9f9;
        }
        h1 {
            text-align: center;
        }
        label {
            display: block;
            margin-top: 10px;
        }
        input[type="text"], input[type="number"], input[type="submit"] {
            width: 100%;
            padding: 10px;
            margin-top: 5px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        input[type="submit"] {
            background-color: #4CAF50;
            color: white;
            border: none;
        }
        p {
            font-size: 0.9em;
            color: #555;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Input Vital Signs</h1>
        <form action="process_vital_signs.php" method="post">
            <input type="hidden" name="patient_id" value="<?php echo htmlspecialchars($patient['id']); ?>">
            <input type="hidden" name="patient_name" value="<?php echo htmlspecialchars($patient_name); ?>">
            <input type="hidden" name="patient_age" value="<?php echo htmlspecialchars($patient_age); ?>">
            <input type="hidden" name="staff_id" value="<?php echo htmlspecialchars($staff_id); ?>">
            <input type="hidden" name="staff_name" value="<?php echo htmlspecialchars($staff_name); ?>">

            <label for="blood_pressure_systolic">Blood Pressure (Systolic):</label>
            <input type="number" id="blood_pressure_systolic" name="blood_pressure_systolic" required>
            <label for="blood_pressure_diastolic">Blood Pressure (Diastolic):</label>
            <input type="number" id="blood_pressure_diastolic" name="blood_pressure_diastolic" required>
            <p>Normal range: 90/60 mmHg to 120/80 mmHg</p>

            <label for="breathing">Breathing (breaths per minute):</label>
            <input type="number" id="breathing" name="breathing" required>
            <p>Normal range: 12 to 18 breaths per minute</p>

            <label for="pulse_rate">Pulse Rate (beats per minute):</label>
            <input type="number" id="pulse_rate" name="pulse_rate" required>
            <p>Normal range: 60 to 100 beats per minute</p>

            <label for="temperature">Temperature (°F):</label>
            <input type="number" step="0.1" id="temperature" name="temperature" required>
            <p>Normal range: 97.8°F to 99.1°F (36.5°C to 37.3°C); average 98.6°F (37°C)</p>

            <input type="submit" value="Submit">
        </form>
    </div>
</body>
</html>
