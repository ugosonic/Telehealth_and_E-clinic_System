<?php
include '../init.php';
include '../config.php';
include '../access_control.php';
include '../sidebar.php';
// Initialize variables for storing success and error messages
$success_message = '';
$error_message = '';

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $first_name = $_POST['first_name'];
    $middle_name = $_POST['middle_name'];
    $surname = $_POST['surname'];
    $date_of_birth = $_POST['date_of_birth'];
    $sex = $_POST['sex'];
    $username = $_POST['username'];
    $password = $_POST['password'];
    $email = $_POST['email'];
    $phone_number = $_POST['phone_number'];
    $usergroup = $_POST['usergroup'];
    $profile_pic = $_POST['profile_pic'];

    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = 'Invalid email address!';
    } else {
        // Check for duplicate username or email
        $query = "SELECT * FROM users WHERE username = '$username' OR email = '$email'";
        $result = mysqli_query($conn, $query);

        if (mysqli_num_rows($result) > 0) {
            $error_message = 'Duplicate username or email found!';
        } else {
            // Hash the password before storing it
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // Insert new staff data into the database
            $sql = "INSERT INTO users (first_name, middle_name, surname, date_of_birth, sex, username, password, email, phone_number, usergroup, profile_pic) VALUES ('$first_name', '$middle_name', '$surname', '$date_of_birth', '$sex', '$username', '$hashed_password', '$email', '$phone_number', '$usergroup', '$profile_pic')";

            if (mysqli_query($conn, $sql)) {
                $success_message = 'Staff registered successfully!';
            } else {
                $error_message = 'Error: ' . mysqli_error($conn);
            }
        }
    }

    // Close the database connection
    mysqli_close($conn);
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Registration</title>
    <link rel="stylesheet" type="text/css" href="registration.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <script src="/my clinic/Dashboard/sidebar.js"></script>
    
    
</head>
<body>
<div class="dash-body">
    <div class="registration-container">
        <h2>Staff Registration</h2>
        <?php
        if (!empty($success_message)) {
            echo '<p class="success-message">' . $success_message . '</p>';
        }
        if (!empty($error_message)) {
            echo '<p class="error-message">' . $error_message . '</p>';
        }
        ?>
        <form action="registration.php" method="post">
            <label for="first_name">First Name:</label><br>
            <input type="text" id="first_name" name="first_name" required><br>
            <label for="middle_name">Middle Name:</label><br>
            <input type="text" id="middle_name" name="middle_name"><br>
            <label for="surname">Surname:</label><br>
            <input type="text" id="surname" name="surname" required><br>
            <label for="date_of_birth">Date of Birth:</label><br>
            <input type="date" id="date_of_birth" name="date_of_birth" required><br>
            <label for="sex">Sex:</label><br>
            <select id="sex" name="sex" required>
                <option value="Male">Male</option>
                <option value="Female">Female</option>
                <option value="Other">Other</option>
            </select><br>
            <label for="username">Username:</label><br>
            <input type="text" id="username" name="username" required><br>
            <label for="password">Password:</label><br>
            <input type="password" id="password" name="password" required><br>
            <label for="email">Email:</label><br>
            <input type="email" id="email" name="email" required><br>
            <label for="phone_number">Phone Number:</label><br>
            <input type="text" id="phone_number" name="phone_number"><br>
            <label for="usergroup">User Group:</label><br>
            <select id="usergroup" name="usergroup" required>
                <option value="Admin">Admin</option>
                <option value="IT">IT</option>
                <option value="Doctor">Doctor</option>
                <option value="Nurse">Nurse</option>
                <option value="Lab Scientist">Lab Scientist</option>
                <option value="Pharmacist">Pharmacist</option>
                <option value="User">User</option>
            </select><br>
            <label for="profile_pic">Profile Picture URL:</label><br>
            <input type="text" id="profile_pic" name="profile_pic"><br><br>
            <input type="submit" value="Register">
        </form>
    </div>
    </div>
</body>
</html>
