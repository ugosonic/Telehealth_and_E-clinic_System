<?php
include 'init.php';

// Check if the user is logged in
if (!isset($_SESSION['usergroup'])) {
    header("Location: ../unauthorised.php");
    exit();
}

// Check access based on usergroup
switch ($_SESSION['usergroup']) {
    case 'Admin':
    case 'IT':
    case 'Nurse':
    case 'Pharmacist':
    case 'Doctor':
    case 'Lab Scientist':
    case 'Patient':  // Patient group now has access
        // Allow access
        break;
    default:
        // If usergroup does not match any allowed groups, deny access
        header("Location: ../unauthorised.php");
        exit();
}
?>
