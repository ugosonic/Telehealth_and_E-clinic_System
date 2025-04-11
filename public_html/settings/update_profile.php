<?php
session_start();
include '../init.php';
include '../config.php';

if (!isset($_SESSION['patient_id'])) {
    header('Location: /My Clinic/login/login.php');
    exit();
}

$patient_id = $_SESSION['patient_id'];
$first_name = $_POST['first_name'];
$middle_name = $_POST['middle_name'];
$surname = $_POST['surname'];
$email = $_POST['email'];
$telephone = $_POST['telephone'];
$country = $_POST['country'];

// Fetch the current profile picture path
$query = "SELECT profile_pic FROM patient_db WHERE patient_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param('s', $patient_id);
$stmt->execute();
$result = $stmt->get_result();
$currentProfilePic = null;
if ($row = $result->fetch_assoc()) {
    $currentProfilePic = $row['profile_pic'];
}
$stmt->close();

// Handle the profile picture upload
$profile_pic = null;
if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] === UPLOAD_ERR_OK) {
    $fileName = basename($_FILES['profile_pic']['name']);
    // Define the upload directory relative to the web root
    $uploadDir = "/My Clinic/uploads/";
    $targetDir = $_SERVER['DOCUMENT_ROOT'] . $uploadDir;

    if (!file_exists($targetDir)) {
        mkdir($targetDir, 0777, true); // Create the uploads directory if it doesn't exist
    }

    $uniqueFileName = uniqid() . "_" . $fileName;
    $filePath = $targetDir . $uniqueFileName;

    // Validate file type (optional but recommended)
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
    $fileType = mime_content_type($_FILES['profile_pic']['tmp_name']);
    if (in_array($fileType, $allowedTypes)) {
        if (move_uploaded_file($_FILES['profile_pic']['tmp_name'], $filePath)) {
            $profile_pic = $uploadDir . $uniqueFileName; // Save the web-accessible path in the database

            // Delete the old profile picture if it exists
            if ($currentProfilePic) {
                $oldProfilePicPath = $_SERVER['DOCUMENT_ROOT'] . $currentProfilePic;
                if (file_exists($oldProfilePicPath)) {
                    unlink($oldProfilePicPath);
                }
            }
        } else {
            $_SESSION['message'] = "Failed to upload profile picture.";
            $_SESSION['message_type'] = "error";
            header('Location: patient_account_settings.php');
            exit();
        }
    } else {
        $_SESSION['message'] = "Invalid file type. Only JPG, PNG, and GIF are allowed.";
        $_SESSION['message_type'] = "error";
        header('Location: patient_account_settings.php');
        exit();
    }
}

// Prepare the SQL query for updating profile
if ($profile_pic) {
    $query = "UPDATE patient_db SET first_name = ?, middle_name = ?, surname = ?, email = ?, telephone = ?, country = ?, profile_pic = ? WHERE patient_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('ssssssss', $first_name, $middle_name, $surname, $email, $telephone, $country, $profile_pic, $patient_id);
} else {
    $query = "UPDATE patient_db SET first_name = ?, middle_name = ?, surname = ?, email = ?, telephone = ?, country = ? WHERE patient_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('sssssss', $first_name, $middle_name, $surname, $email, $telephone, $country, $patient_id);
}

if ($stmt->execute()) {
    $_SESSION['message'] = "Profile updated successfully.";
    $_SESSION['message_type'] = "success";
} else {
    $_SESSION['message'] = "Failed to update profile. Error: " . $stmt->error;
    $_SESSION['message_type'] = "error";
}

$stmt->close();
header('Location: patient_account_settings.php');
exit();
