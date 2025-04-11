<?php
session_start();

// Check if the user is already logged in, if yes, redirect to the appropriate dashboard
if (isset($_SESSION['usergroup'])) {
    header("Location: authenticate.php");
    exit();
}

$error_message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!empty($_POST['username']) && !empty($_POST['password'])) {
        $DATABASE_HOST = 'localhost';
        $DATABASE_USER = 'u398331630_myclinic';
        $DATABASE_PASS = 'kingsley55A';
        $DATABASE_NAME = 'u398331630_administrator';

        $conn = mysqli_connect($DATABASE_HOST, $DATABASE_USER, $DATABASE_PASS, $DATABASE_NAME);
        if (mysqli_connect_errno()) {
            exit('Failed to connect to MySQL: ' . mysqli_connect_error());
        }

        $username = mysqli_real_escape_string($conn, $_POST['username']);
        $password = mysqli_real_escape_string($conn, $_POST['password']);

        // First check in the users table
        $sql = "SELECT registration_id, password, usergroup FROM users WHERE username = '$username'";
        $result = mysqli_query($conn, $sql);
        if (mysqli_num_rows($result) == 1) {
            $row = mysqli_fetch_assoc($result);
            if (password_verify($password, $row['password'])) {
                $_SESSION['username'] = $username;
                $_SESSION['usergroup'] = $row['usergroup'];
                $_SESSION['registration_id'] = $row['registration_id'];

                // Update online status
    $update_status = "UPDATE users SET online_status = 1 WHERE username = '$username'";
    mysqli_query($conn, $update_status);

    // Close connection and redirect
                mysqli_close($conn);
                header("Location: authenticate.php");
                exit();
            }
        }

        // If not found, check in the patient_db table
        $sql = "SELECT patient_id, password FROM patient_db WHERE username = '$username'";
        $result = mysqli_query($conn, $sql);
        if (mysqli_num_rows($result) == 1) {
            $row = mysqli_fetch_assoc($result);
            if (password_verify($password, $row['password'])) {
                $_SESSION['username'] = $username;
                $_SESSION['usergroup'] = 'Patient';  // Set usergroup as 'Patient'
                $_SESSION['patient_id'] = $row['patient_id'];
                mysqli_close($conn);
                header("Location: ../Dashboard/patient_dashboard.php"); // Direct to patient dashboard
                exit();
            }
        }

        mysqli_close($conn);
        $error_message = "Invalid username or password!";
    } else {
        $error_message = "Please enter both username and password!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" type="text/css" href="login.css">
</head>
<body>
    <div class="login-container">
        <h2>Login</h2>
        <?php if (!empty($error_message)) { echo '<p class="error-message">' . $error_message . '</p>'; } ?>
        <form action="login.php" method="post">
            <label for="username">Username:</label><br>
            <input type="text" id="username" name="username" required><br>
            <label for="password">Password:</label><br>
            <input type="password" id="password" name="password" required><br><br>
            <input type="submit" value="Login">
        </form>
    </div>
</body>
</html>
