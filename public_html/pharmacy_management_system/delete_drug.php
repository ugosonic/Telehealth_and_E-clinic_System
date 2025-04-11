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

// Delete drug
$sql = "DELETE FROM inventory WHERE drug_id = '$drug_id'";

if ($conn->query($sql) === TRUE) {
    echo "Record deleted successfully";
} else {
    echo "Error deleting record: " . $conn->error;
}

$conn->close();
?>
