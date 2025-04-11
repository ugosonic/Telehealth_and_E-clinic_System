<?php
session_start();
include '../init.php';
include '../config.php';
include '../sidebar.php';

// Ensure only staff (non-patient) can access this page
if ($_SESSION['usergroup'] === 'Patient') {
    header('Location: /My Clinic/login/login.php');
    exit();
}

$user_id = $_SESSION['registration_id'];

// Fetch user data for the logged-in staff
$query = "SELECT * FROM users WHERE registration_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Account Settings</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .settings-container {
            display: flex;
        }
        .settings-sidebar {
            width: 25%;
            background-color: #f8f8f8;
            padding: 20px;
        }
        .settings-sidebar a {
            display: block;
            margin-bottom: 10px;
            color: #333;
            text-decoration: none;
        }
        .settings-sidebar a:hover {
            text-decoration: underline;
        }
        .settings-content {
            width: 75%;
            padding: 20px;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
        }
        .form-group input, .form-group select {
            width: 100%;
            padding: 8px;
            box-sizing: border-box;
        }
        .popup-message {
            display: none;
            position: fixed;
            top: 20px;
            left: 50%;
            transform: translateX(-50%);
            padding: 15px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            z-index: 1000;
        }
        .popup-message.success {
            background-color: #d4edda;
            color: #155724;
        }
        .popup-message.error {
            background-color: #f8d7da;
            color: #721c24;
        }
        .popup-message .close-btn {
            background: none;
            border: none;
            font-size: 16px;
            margin-left: 10px;
            cursor: pointer;
        }
    </style>
</head>
<body>

<?php if (isset($_SESSION['message'])): ?>
    <div class="popup-message <?= $_SESSION['message_type']; ?>" id="message-popup">
        <?= $_SESSION['message']; ?>
        <button class="close-btn" onclick="document.getElementById('message-popup').style.display='none'">&times;</button>
    </div>
    <script>
        document.getElementById('message-popup').style.display = 'block';
        setTimeout(function() {
            document.getElementById('message-popup').style.display = 'none';
        }, 12000);
    </script>
    <?php unset($_SESSION['message'], $_SESSION['message_type']); ?>
<?php endif; ?>

<div class="settings-container">
    <!-- Sidebar -->
    <div class="settings-sidebar">
        <h3>Account Settings</h3>
        <a href="staff_account_settings.php">Profile Settings</a>
        <a href="staff_password_change.php">Change Password</a>
        <a href="staff_notification_settings.php">Notification Preferences</a>
        <a href="staff_privacy_settings.php">Privacy Settings</a>
    </div>

    <!-- Main Content -->
    <div class="settings-content">
        <h2>Profile Settings</h2>
        <form action="update_staff_profile.php" method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label for="first_name">First Name:</label>
                <input type="text" id="first_name" name="first_name" value="<?= htmlspecialchars($user['first_name']); ?>" required>
            </div>
            <div class="form-group">
                <label for="middle_name">Middle Name:</label>
                <input type="text" id="middle_name" name="middle_name" value="<?= htmlspecialchars($user['middle_name']); ?>">
            </div>
            <div class="form-group">
                <label for="surname">Surname:</label>
                <input type="text" id="surname" name="surname" value="<?= htmlspecialchars($user['surname']); ?>" required>
            </div>
            <div class="form-group">
                <label for="email">Email Address:</label>
                <input type="email" id="email" name="email" value="<?= htmlspecialchars($user['email']); ?>" required>
            </div>
            <div class="form-group">
                <label for="phone_number">Phone Number:</label>
                <input type="text" id="phone_number" name="phone_number" value="<?= htmlspecialchars($user['phone_number']); ?>">
            </div>
            <div class="form-group">
                <label for="profile_pic">Profile Picture:</label>
                <input type="file" id="profile_pic" name="profile_pic" accept="image/*">
            </div>
            <div class="form-group">
                <label for="usergroup">User Group:</label>
                <input type="text" id="usergroup" name="usergroup" value="<?= htmlspecialchars($user['usergroup']); ?>" readonly>
            </div>
            <input type="submit" value="Save Changes">
        </form>
    </div>
</div>

</body>
</html>
