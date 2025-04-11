<?php
$conn = new mysqli('localhost', 'root', '', 'test_database');
if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
}
echo 'Connection successful';
?>
