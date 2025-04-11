<?php
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

$drug_id = $_GET['id'];

// Retrieve drug details
$sql = "SELECT * FROM inventory WHERE drug_id = '$drug_id'";
$result = $conn->query($sql);
if ($result->num_rows > 0) {
    echo json_encode($result->fetch_assoc());
} else {
    echo json_encode([]);
}
$conn->close();
?>
