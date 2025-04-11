<?php
include '../init.php';
include '../config.php';
include '../access_control.php';
include '../sidebar.php';

// Handle actions (cancel, delete)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['action']) && isset($_POST['meeting_id'])) {
        $meeting_id = mysqli_real_escape_string($conn, $_POST['meeting_id']);
        if ($_POST['action'] == 'cancel') {
            $sql = "UPDATE meetings SET status = 'Declined' WHERE meeting_id = '$meeting_id'";
        } elseif ($_POST['action'] == 'delete') {
            $sql = "DELETE FROM meetings WHERE meeting_id = '$meeting_id'";
        }
        mysqli_query($conn, $sql);
    }
}

// Fetch meetings
$sql = "SELECT * FROM meetings";
$result = mysqli_query($conn, $sql);
$meetings = [];
while ($row = mysqli_fetch_assoc($result)) {
    // Initialize the status if not set
    if (!isset($row['status'])) {
        $row['status'] = 'Pending';
    }

    // Update status based on expiration date
    $current_time = new DateTime();
    $expiration = new DateTime($row['expiration']);
    if ($row['status'] != 'Declined' && $current_time > $expiration) {
        $row['status'] = 'Expired';
    }
    
    $meetings[] = $row;
}

mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>View Meetings</title>
    <link rel="stylesheet" href="../css/patient_record.css">
    <link rel="stylesheet" href="../patient/patient.css">
    <link rel="stylesheet" href="../css/medical_records.css">
</head>
<body>
<div class="dash-body">
    <div class="profile-header custom-profile">
        <div class="profile-details">
            
        <div class="header-container">
        <h1 class="header-name">Session</h1>
        


        </div>
        <div class="nav-links">
    <?php if (isset($_SESSION['usergroup'])): ?>
        <?php if (in_array($_SESSION['usergroup'], ['Admin', 'Nurse', 'Pharmacist', 'Lab Scientist'])): ?>
            <a href="create_session.php">Create Session</a>
            <a href="session.php">View Session</a>
        <?php endif; ?>

        <?php if ($_SESSION['usergroup'] == 'Patient'): ?>
            <a href="join_session.php">Join Session</a>
        <?php endif; ?>
    <?php endif; ?>


                
            </div>
        </div>
    
    </div>

      
        <section class="certification">
        <table>
            <thead>
                <tr>
                    <th>Meeting ID</th>
                    <th>Password</th>
                    <th>Patient Name</th>
                    <th>Date of Birth</th>
                    <th>Reason</th>
                    <th>Staff to Attend</th>
                    <th>Department</th>
                    <th>Created Date</th>
                    <th>Expiring Date</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($meetings as $meeting): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($meeting['meeting_id']); ?></td>
                        <td><?php echo htmlspecialchars($meeting['password']); ?></td>
                        <td><?php echo htmlspecialchars($meeting['patient_name']); ?></td>
                        <td><?php echo htmlspecialchars($meeting['patient_dob']); ?></td>
                        <td><?php echo htmlspecialchars($meeting['reason']); ?></td>
                        <td><?php echo htmlspecialchars($meeting['assigned_staff']); ?></td>
                        <td><?php echo htmlspecialchars($meeting['department']); ?></td>
                        <td><?php echo htmlspecialchars($meeting['created_at']); ?></td>
                        <td><?php echo htmlspecialchars($meeting['expiration']); ?></td>
                        <td><?php echo htmlspecialchars($meeting['status']); ?></td>
                        <td>
                            <form method="post" action="">
                                <input type="hidden" name="meeting_id" value="<?php echo $meeting['meeting_id']; ?>">
                                <button type="submit" name="action" value="cancel">Cancel</button>
                                <button type="submit" name="action" value="delete">Delete</button>
                                <a href="../video_call.php?meeting_id=<?php echo $meeting['meeting_id']; ?>">Join</a>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
                </section>
    </div>
</body>
</html>
