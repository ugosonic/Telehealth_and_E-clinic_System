<?php
include 'init.php';
$servername = "localhost";
$username = "u398331630_myclinic"; // Replace this with your actual username
$password = "kingsley55A"; // Replace this with your MySQL password
$dbname = "u398331630_administrator";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}


?>