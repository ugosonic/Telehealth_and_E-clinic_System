<?php

include '../init.php';
include '../config.php';
include '../access_control.php';
include 'patient_profile_picture.php';
/// Get the patient ID from the URL
$patient_id = isset($_GET['id']) ? $_GET['id'] : 0;

// Prepare and execute the query to fetch patient details
$sql = "SELECT * FROM patient_db WHERE patient_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $patient_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    // Fetch the data
    $row = $result->fetch_assoc();
    $profilePicPath = isset($row['profile_pic']) ? $row['profile_pic'] : null;

    // Fetch today's waiting room request
    $today_date = date('Y-m-d');
    $sql = "SELECT wr.*, u.username AS staff_name 
            FROM waiting_room wr 
            LEFT JOIN users u ON wr.staff_name = u.username
            WHERE wr.patient_id = ? AND DATE(wr.check_in_time) = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $patient_id, $today_date);
    $stmt->execute();
    $waiting_room_result = $stmt->get_result();
    $waiting_rooms = [];
    while ($wr = $waiting_room_result->fetch_assoc()) {
        $waiting_rooms[] = $wr;
    }

    // Fetch today's consultation details
    $today_date = date('Y-m-d');
    $sql = "SELECT * FROM consultations WHERE patient_id = ? AND consultation_date = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $patient_id, $today_date);
    $stmt->execute();
    $consultation_result = $stmt->get_result();
    $consultation = $consultation_result->fetch_assoc();

    // Define content based on action
    $action = isset($_GET['action']) ? $_GET['action'] : '';

    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="stylesheet" href="../css/patient_record.css">
        <link rel="stylesheet" href="patient.css">
        
        <title>Patient Details</title>
    
    </head>
    <body>
    <?php
    include '../sidebar.php';
    ?>
    <div class="dash-body">
    <div class="profile-header custom-profile">
        <div class="profile-details">
            
        <div class="header-container">
        <h1 class="header-name">Profile</h1>
        <h1 class="header-title"><?php echo htmlspecialchars($row['first_name'] . ' ' . $row['surname']); ?></h1>
        <div class="profile-picture">
        <div class="profile-picture">
        <?php
                        // Use the function to display the profile picture
                        displayPatientProfilePicture($row);
                        ?>
    </div>


        </div>
            <div class="nav-links">
            <a href="patient_record.php?id=<?= $patient_id ?>">Profile</a>
            <a href="edit_record.php?id=<?= $patient_id ?>">Edit Record</a>
                <a href="consultation.php?id=<?= $patient_id ?>">Consultation</a>
                <a href="consultation_history.php?id=<?= $patient_id ?>">Consultation History</a>
                <a href="prescriptions.php?id=<?= $patient_id ?>">Prescriptions</a>
                <a href="../appointment_manager/schedule.php?id=<?= $patient_id ?>">Appointments</a>
            </div>
        </div>
    
    </div>

            <div class="section">
                <h2>Send to Waiting Room</h2>
                <?php if (isset($_SESSION['message'])): ?>
                    <div class="popup-message <?= $_SESSION['message_type']; ?>" id="message-popup">
                        <?= $_SESSION['message']; ?>
                        <button class="close-btn" onclick="document.getElementById('message-popup').style.display='none'">&times;</button>
                    </div>
                <?php endif; ?>
                <form action="send_to_waiting_room.php" method="post">
                    <input type="hidden" name="patient_id" value="<?php echo $row['patient_id']; ?>">
                    <label for="waiting_room">Select Waiting Room:</label>
                    <select name="waiting_room" id="waiting_room" required>
                        <option value="Nurse">Nurse</option>
                        <option value="Doctor">Doctor</option>
                        <option value="Laboratory">Laboratory</option>
                        <option value="Pharmacy">Pharmacy</option>
                    </select>
                    <input type="submit" value="Send to Waiting Room">
                </form>
            
                <?php if (!empty($waiting_rooms)): ?>
                    <?php foreach ($waiting_rooms as $waiting_room): ?>
                        <p><b><?= htmlspecialchars($row['first_name'] . ' ' . $row['surname']) ?> was sent to <?= htmlspecialchars($waiting_room['waiting_room']) ?> waiting room at <?= htmlspecialchars($waiting_room['check_in_time']) ?>.</b></p>
                        <p>Status: <?= htmlspecialchars($waiting_room['status']) ?><?php if ($waiting_room['status'] != 'Waiting') echo ' by ' . htmlspecialchars($waiting_room['staff_name']); ?></p>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>No waiting room request for today.</p>
                <?php endif; ?>
            </div>
        
            <div class="section">
    <h2>Ongoing Consultation</h2>
    <?php if ($consultation): ?>
        <div><strong>Consultation ID:</strong> <?= htmlspecialchars($consultation['consultation_id']); ?></div>
        <div><strong>Initial Diagnosis:</strong> <?= htmlspecialchars($consultation['initial_diagnosis']) ?></div>
        <div><strong>Final Diagnosis:</strong> <?= htmlspecialchars($consultation['final_diagnosis']) ?></div>
        <div><strong>Last Updated:</strong> <?= htmlspecialchars($consultation['last_updated'] ?? $consultation['consultation_time']) ?></div>
        <div><strong>Doctor's Signature:</strong> <?= htmlspecialchars($consultation['doctor_signature']) ?>        </div>
        <form action="update_consultation.php" method="get">
    <input type="hidden" name="id" value="<?= htmlspecialchars($patient_id); ?>">
    <input type="hidden" name="consultation_id" value="<?= htmlspecialchars($consultation['consultation_id']); ?>">
    <button type="submit" class="btn btn-primary">Update</button>
</form>

    <?php else: ?>
        <p>No consultation found for today.</p>
    <?php endif; ?>
</div>

            <div class="section">
                <h2>Personal Details</h2>
                <div><strong>Patient ID:</strong> <?php echo $row['patient_id']; ?></div>
                <div><strong>First Name:</strong> <?php echo $row['first_name']; ?></div>
                <div><strong>Middle Name:</strong> <?php echo $row['middle_name']; ?></div>
                <div><strong>Surname:</strong> <?php echo $row['surname']; ?></div>
                <div><strong>Date of Birth:</strong> <?php echo $row['dob']; ?></div>
                <div><strong>Age:</strong> <?php echo $row['age']; ?></div>
                <div><strong>Gender:</strong> <?php echo $row['gender']; ?></div>
            </div>
            <div class="section">
                <h2>Contact Information</h2>
                <div><strong>Marital Status:</strong> <?php echo $row['marital_status']; ?></div>
                <div><strong>Education Level:</strong> <?php echo $row['education_level']; ?></div>
                <div><strong>Email:</strong> <?php echo $row['email']; ?></div>
            </div>
            <div class="section">
                <h2>Home Address</h2>
                <div><strong>Address:</strong> <?php echo $row['address']; ?></div>
                <div><strong>Country:</strong> <?php echo $row['country']; ?></div>
                <div><strong>Telephone:</strong> <?php echo $row['telephone']; ?></div>
            </div>
            <div class="section">
                <h2>Next of Kin</h2>
                <div><strong>Next of Kin Name:</strong> <?php echo $row['next_of_kin_name']; ?></div>
                <div><strong>Next of Kin Relation:</strong> <?php echo $row['next_of_kin_relation']; ?></div>
                <div><strong>Next of Kin Telephone:</strong> <?php echo $row['next_of_kin_telephone']; ?></div>
            </div>
            <div class="section">
                <h2>Payer Information</h2>
                <div><strong>Payer:</strong> <?php echo $row['payer']; ?></div>
                <div><strong>Sponsor:</strong> <?php echo $row['sponsor']; ?></div>
            </div>
            <div class="section">
                <h2>Login Details</h2>
                <div><strong>Username:</strong> <?php echo $row['username']; ?></div>
            </div>
            <div class="section">
                <h2>Previous GP</h2>
                <div><strong>Previous Address UK:</strong> <?php echo $row['previous_address_uk']; ?></div>
                <div><strong>Previous GP Practice:</strong> <?php echo $row['previous_gp_practice']; ?></div>
                <div><strong>Address of Previous GP:</strong> <?php echo $row['address_previous_gp']; ?></div>
            </div>
            <div class="section">
                <h2>Armed Forces Information</h2>
                <div><strong>Enlisted Address:</strong> <?php echo $row['enlisted_address']; ?></div>
                <div><strong>Enlistment Date:</strong> <?php echo $row['enlistment_date']; ?></div>
                <div><strong>Discharge Date:</strong> <?php echo $row['discharge_date']; ?></div>
            </div>
            <div class="section">
                <h2>Allergy</h2>
                <div><strong>Allergy:</strong> <?php echo $row['allergy']; ?></div>
            </div>
            <div class="section">
                <h2>Disability</h2>
                <div><strong>Disability:</strong> <?php echo $row['disability']; ?></div>
            </div>
            <div class="section">
                <h2>ID Verification</h2>
                <div><strong>ID Type:</strong> <?php echo $row['id_type']; ?></div>
                <div><strong>ID Upload:</strong> <?php echo $row['id_upload'] ? '<img src="data:image/jpeg;base64,' . base64_encode($row['id_upload']) . '" alt="ID Upload" />' : 'No Document Uploaded'; ?></div>
            </div>
        </div>
    </div> 
    </div>

    <script>
        document.getElementById('message-popup').style.display = 'block';
        setTimeout(function() {
            document.getElementById('message-popup').style.display = 'none';
        }, 12000);
    </script>

    <?php unset($_SESSION['message'], $_SESSION['message_type']); // Unset the session message variables here ?>

    </body>
    </html>
    <?php
} else {
    echo "No records found for Patient ID: " . $patient_id;
}

$conn->close();
