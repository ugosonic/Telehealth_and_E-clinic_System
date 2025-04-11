<?php
session_start();
include '../init.php';
include '../config.php';

if (!isset($_SESSION['registration_id'])) {
    header('Location: /My Clinic/login/login.php');
    exit();
}

$user_id = $_SESSION['registration_id'];
$first_name = $_POST['first_name'];
$middle_name = $_POST['middle_name'];
$surname = $_POST['surname'];
$email = $_POST['email'];
$phone_number = $_POST['phone_number'];

// Before updating, delete the old profile picture if it exists
$oldProfilePicPath = $_SERVER['DOCUMENT_ROOT'] . $user['profile_pic'];
if (file_exists($oldProfilePicPath)) {
    unlink($oldProfilePicPath);
}

// Handle the profile picture upload
$profile_pic = null;
if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] === UPLOAD_ERR_OK) {
    $fileName = basename($_FILES['profile_pic']['name']);
    // Define the upload directory relative to the web root
    $uploadDir = "/My Clinic/uploads/";
    $targetDir = $_SERVER['DOCUMENT_ROOT'] . $uploadDir;

    if (!file_exists($targetDir)) {
        mkdir($targetDir, 0777, true);
    }

    $uniqueFileName = uniqid() . "_" . $fileName;
    $filePath = $targetDir . $uniqueFileName;

    if (move_uploaded_file($_FILES['profile_pic']['tmp_name'], $filePath)) {
        // Save the web-accessible path in the database
        $profile_pic = $uploadDir . $uniqueFileName;
    }
}

// Prepare the SQL query for updating profile
if ($profile_pic) {
    $query = "UPDATE users SET first_name = ?, middle_name = ?, surname = ?, email = ?, phone_number = ?, profile_pic = ? WHERE registration_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('ssssssi', $first_name, $middle_name, $surname, $email, $phone_number, $profile_pic, $user_id);
} else {
    $query = "UPDATE users SET first_name = ?, middle_name = ?, surname = ?, email = ?, phone_number = ? WHERE registration_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('sssssi', $first_name, $middle_name, $surname, $email, $phone_number, $user_id);
}

if ($stmt->execute()) {
    $_SESSION['message'] = "Profile updated successfully.";
    $_SESSION['message_type'] = "success";
} else {
    $_SESSION['message'] = "Failed to update profile.";
    $_SESSION['message_type'] = "error";
}

$stmt->close();
header('Location: staff_account_settings.php');
exit();
