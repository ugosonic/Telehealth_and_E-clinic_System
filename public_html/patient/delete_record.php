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

// Check if the user is logged in and is either Admin or IT
if (!isset($_SESSION['usergroup']) || ($_SESSION['usergroup'] != 'Admin' && $_SESSION['usergroup'] != 'IT')) {
    echo "ACTION DENIED: You are not authourised to take this action. <br><br><button><a class=.back_button href=javascript:history.back(1)>Back</a></button>";
    exit();
}

// Get the patient ID from the URL
$patient_id = isset($_GET['id']) ? $_GET['id'] : 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['confirm']) && $_POST['confirm'] === 'Yes') {
        // Prepare and execute the query to delete the patient record
        $sql = "DELETE FROM patient_db WHERE patient_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $patient_id);

        if ($stmt->execute()) {
            echo "Record deleted successfully.";
        } else {
            echo "Error deleting record: " . $conn->error;
        }

        $stmt->close();
        $conn->close();

        // Redirect back to the medical records page
        header('Location: medical_records.php');
        exit();
    } else {
        // If deletion is not confirmed, redirect back to the medical records page
        header('Location: medical_records.php');
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delete Patient Record</title>
</head>
<body>
    <h1>Delete Patient Record</h1>
    <form method="POST" action="delete_record.php?id=<?php echo $patient_id; ?>">
        <p>Are you sure you want to delete the patient file?</p>
        <button type="submit" name="confirm" value="Yes">Yes</button>
        <button type="submit" name="confirm" value="No">No</button>
    </form>
</body>
</html>
