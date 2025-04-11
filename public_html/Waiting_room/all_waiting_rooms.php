<?php
include '../init.php';
include '../config.php';
include '../access_control.php';
include '../sidebar.php';

$username = '';
$email = '';
if (isset($_SESSION['username'])) {
    $stmt = $conn->prepare('SELECT username, email FROM users WHERE username = ?');
    $stmt->bind_param('s', $_SESSION['username']);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $username = $row['username'];
        $email = $row['email'];
    } else {
        echo "<p>User not found. Please <a href='/my clinic/login/login.php'>login again</a>.</p>";
    }
    $stmt->close();
}
// Fetch all patients in the waiting rooms
$sql = "SELECT wr.waiting_id, p.patient_id, p.first_name, p.surname, wr.check_in_time, wr.waiting_room, wr.status, wr.staff_name 
        FROM waiting_room wr 
        JOIN patient_db p ON wr.patient_id = p.patient_id 
        ORDER BY wr.check_in_time ASC";
$result = $conn->query($sql);


// Set the default date to today or get from user input
$selected_date = $_GET['date'] ?? date('Y-m-d');

// Prepare and execute the query
$stmt = $conn->prepare("SELECT wr.waiting_id, p.patient_id, p.first_name, p.surname, wr.check_in_time, wr.waiting_room, wr.status, wr.staff_name 
        FROM waiting_room wr 
        JOIN patient_db p ON wr.patient_id = p.patient_id
        WHERE DATE(wr.check_in_time) = ? 
        ORDER BY wr.status = 'Waiting' DESC, wr.check_in_time ASC");
$stmt->bind_param("s", $selected_date);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Waiting Rooms</title>
    <link rel="stylesheet" href="waiting_room.css">
   
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <script src="/my clinic/Dashboard/sidebar.js"></script>
</head>
<body>

    
    <h2>All Waiting Rooms</h2>
    <div class="dash-body">
    <p>Sort by Date:</p> <form action="" method="get">
            <input type="date" name="date" value="<?= htmlspecialchars($selected_date) ?>">
            <button type="submit">Show List</button>
        </form>
        <table class="table">
            <tr>
                <th>Patient Name</th>
                <th>Check-In Time</th>
                <th>Waiting Room</th>
                <th>Status</th>
                <th>Staff Name</th>
                <th>Actions</th>
            </tr>
            <?php
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $actions = '';
                    if ($row['status'] === 'Waiting') {
                        $actions = "<a href='accept_patient.php?id={$row['waiting_id']}&patient_id={$row['patient_id']}' class='accept'>Accept</a>
                                    <a href='cancel_patient.php?id={$row['waiting_id']}' class='cancel'>Cancel</a>";
                    }
                    echo "<tr>
                        <td>{$row['first_name']} {$row['surname']}</td>
                        <td>{$row['check_in_time']}</td>
                        <td>{$row['waiting_room']}</td>
                        <td>{$row['status']}</td>
                        <td>{$row['staff_name']}</td>
                        <td class='actions'>{$actions}</td>
                    </tr>";
                }
            } else {
                echo "<tr><td colspan='6'>No patients in the waiting rooms for the selected date.</td></tr>";
            }
            ?>
        </table>
    </div>
        </div>
</body>
</html>
