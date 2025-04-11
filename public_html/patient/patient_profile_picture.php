<?php
// patient_profile_picture.php

// Ensure this file isn't accessed directly
if (basename($_SERVER['PHP_SELF']) == basename(__FILE__)) {
    exit('No direct script access allowed');
}

// Function to generate default avatar
function generateDefaultAvatars($firstName, $lastName) {
    $initials = '';
    if (!empty($firstName)) {
        $initials .= strtoupper($firstName[0]);
    }
    if (!empty($lastName)) {
        $initials .= strtoupper($lastName[0]);
    }
    if (empty($initials)) {
        $initials = 'U'; // Default initial if none found
    }
    $backgroundColor = getRandomColors(); // Generate a random color
    echo '<div class="rounded-circle me-2" style="
        width: 100px; 
        height: 100px; 
        background-color: ' . $backgroundColor . '; 
        display: flex; 
        align-items: center; 
        justify-content: center; 
        color: white; 
        font-weight: bold; 
        font-size: 40px;">
        ' . htmlspecialchars($initials, ENT_QUOTES) . '
    </div>';
}

// Function to generate random color
function getRandomColors() {
    $colors = ['#007bff', '#28a745', '#ffc107', '#6610f2', '#e83e8c', '#fd7e14', '#17a2b8'];
    return $colors[array_rand($colors)];
}

// Function to display the profile picture or default avatar
function displayPatientProfilePicture($patient) {
    // Ensure $patientData contains necessary fields
    $firstName = $patient['first_name'] ?? 'Unknown';
    $lastName = $patient['surname'] ?? 'User';
    $profilePicPath = $patient['profile_pic'] ?? null;

    if ($profilePicPath) {
        $absolutePath = $_SERVER['DOCUMENT_ROOT'] . $profilePicPath;
        if (file_exists($absolutePath)) {
            // Use the web-accessible path in the img src attribute
            echo '<img src="' . htmlspecialchars($profilePicPath, ENT_QUOTES) . '" alt="Profile Picture" class="rounded-circle" width="150" height="150">';
        } else {
            // File doesn't exist; display default avatar
            generateDefaultAvatars($firstName, $lastName);
        }
    } else {
        // No profile picture; display default avatar
        generateDefaultAvatars($firstName, $lastName);
    }
}
?>
