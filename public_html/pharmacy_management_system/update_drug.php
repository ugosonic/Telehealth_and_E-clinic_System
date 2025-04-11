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

// Retrieve form data
$drug_id = $_POST['drug_id'];
$name = $_POST['name'];
$category = implode(", ", $_POST['category']);
$groups = implode(", ", $_POST['groups']);
$num_capsules = $_POST['num_capsules'];
$num_sachets = $_POST['num_sachets'];
$num_packets = $_POST['num_packets'];
$form = $_POST['form'];
$batch_number = $_POST['batch_number'];
$mode_of_admin = $_POST['mode_of_admin'];
$injection_methods = isset($_POST['injection_methods']) ? implode(", ", $_POST['injection_methods']) : '';
$expiry_date = $_POST['expiry_date'];
$price_per_capsule = $_POST['price_per_capsule'];
$special_note = $_POST['special_note'];
$prescription_note = $_POST['prescription_note'];
$capsules_remaining = $_POST['capsules_remaining'];

// Fetch existing image path from database if not updated
$imagePath = '';
if (!empty($_FILES['image']['name'])) {
    $targetDir = "uploads/";
    $imagePath = $targetDir . basename($_FILES["image"]["name"]);
    if (!move_uploaded_file($_FILES["image"]["tmp_name"], $imagePath)) {
        die("Error: Failed to upload image.");
    }
} else {
    $stmt = $conn->prepare('SELECT image FROM inventory WHERE drug_id = ?');
    $stmt->bind_param('s', $drug_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $imagePath = $row['image'];
    }
    $stmt->close();
}

// Update data in database
$stmt = $conn->prepare("UPDATE inventory SET name=?, category=?, groups=?, num_capsules=?, num_sachets=?, num_packets=?, form=?, batch_number=?, mode_of_admin=?, injection_methods=?, expiry_date=?, price_per_capsule=?, special_note=?, prescription_note=?, capsules_remaining=?, image=? WHERE drug_id=?");
$stmt->bind_param('ssssssssssssssiss', $name, $category, $groups, $num_capsules, $num_sachets, $num_packets, $form, $batch_number, $mode_of_admin, $injection_methods, $expiry_date, $price_per_capsule, $special_note, $prescription_note, $capsules_remaining, $imagePath, $drug_id);

if ($stmt->execute()) {
    echo "Record updated successfully";
} else {
    echo "Error updating record: " . $stmt->error;
}
$stmt->close();
$conn->close();
?>
