<?php

include '../init.php';
include '../config.php';
include '../access_control.php';
include '../sidebar.php';
// Get the patient ID from the URL
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
} else {
    $profilePicPath = null;
}

$today = date('Y-m-d');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Patient Details</title>
    <link rel="stylesheet" href="../css/patient_record.css">
    <link rel="stylesheet" href="patient.css">
    <script type="text/javascript" src="country2.js"></script>
    <script type="text/javascript" src="profilecamera.js"></script>
    <script type="text/javascript" src="script.js"></script>
    <style>
        .notification {
            display: none;
position: fixed;
top: 20px;
left: 50%;
transform: translateX(-50%);
padding: 15px;
border-radius: 5px;
box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
z-index: 1000;
background-color: #d4edda;
color: #155724;
        }
    </style>
</head>
<body onload="generatePatientID()">
    <div class="dash-body">
    <div class="profile-header custom-profile">
    <div class="profile-details">      
    <div class="header-container">
    <h1 class="header-name">Edit Record</h1>
    <h1 class="header-title"><?php echo htmlspecialchars($row['first_name'] . ' ' . $row['surname']); ?></h1>
    <div class="profile-picture">
    <?php
include 'patient_profile_picture.php';

// Assuming you have fetched the patient data into $patientData

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
            <a href="../appointment_manager/book_appointment.php?id=<?= $patient_id ?>">Appointments</a>
        </div>
    </div>
   
</div>

        <?php
// Directory to store uploaded images (relative to the web root)
$uploadDir = '/My Clinic/uploads/profile_pictures/';
$targetDir = $_SERVER['DOCUMENT_ROOT'] . $uploadDir;

// Ensure the directory exists
if (!is_dir($targetDir)) {
    mkdir($targetDir, 0777, true);
}

// Check if form is submitted
$updateSuccess = false;
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // ... (process form data)

    $patient_id = intval($_POST['patient_id']);
    $first_name = htmlspecialchars(trim($_POST['first_name']));
    $middle_name = htmlspecialchars(trim($_POST['middle_name']));
    $surname = htmlspecialchars(trim($_POST['surname']));
    $dob = $_POST['dob'];
    $gender = htmlspecialchars($_POST['gender']);
    $marital_status = htmlspecialchars($_POST['marital_status']);
    $education_level = htmlspecialchars($_POST['education_level']);
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $telephone = htmlspecialchars(trim($_POST['telephone']));
    $next_of_kin_name = htmlspecialchars(trim($_POST['next_of_kin_name']));
    $next_of_kin_relation = htmlspecialchars($_POST['next_of_kin_relation']);
    $payer = htmlspecialchars($_POST['payer']);
    $sponsor = htmlspecialchars($_POST['sponsor']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT); // Hash the password
    $previous_address_uk = htmlspecialchars(trim($_POST['previous_address_uk']));
    $previous_gp_practice = htmlspecialchars(trim($_POST['previous_gp_practice']));
    $address_previous_gp = htmlspecialchars(trim($_POST['address_previous_gp']));
    $enlisted_address = htmlspecialchars(trim($_POST['enlisted_address']));
    $enlistment_date = $_POST['enlistment_date'];
    $discharge_date = $_POST['discharge_date'];
    $allergy = htmlspecialchars(trim($_POST['allergy']));
    $disability = htmlspecialchars($_POST['disability']);
    $disability_specify = htmlspecialchars(trim($_POST['disability_specify']));
    $id_type = htmlspecialchars($_POST['id_type']);

    // Validate Date of Birth
    if (strtotime($dob) > strtotime(date('Y-m-d'))) {
        die("Error: Date of Birth cannot be in the future.");
    }

    // Recalculate age
    $age = date_diff(date_create($dob), date_create('today'))->y;


    // Fetch existing image path before updating
    $profilePicPath = isset($row['profile_pic']) ? $row['profile_pic'] : null;

    // Handle file uploads
    if (isset($_FILES["profile_pic"]) && $_FILES["profile_pic"]["error"] == UPLOAD_ERR_OK) {
        $fileName = basename($_FILES["profile_pic"]["name"]);
        $fileTmpPath = $_FILES["profile_pic"]["tmp_name"];
        $fileSize = $_FILES["profile_pic"]["size"];
        $fileType = $_FILES["profile_pic"]["type"];

        // Check file type (optional, for security)
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        if (in_array($fileType, $allowedTypes)) {
            // Generate a unique name for the file to avoid overwriting
            $uniqueFileName = uniqid() . '-' . $fileName;
            $destFilePath = $targetDir . $uniqueFileName;

            // Move the uploaded file to the destination
            if (move_uploaded_file($fileTmpPath, $destFilePath)) {
                // Save the web-accessible path in the database
                $profilePicPath = $uploadDir . $uniqueFileName;

                // Delete the old profile picture if it exists
                if (!empty($row['profile_pic'])) {
                    $oldProfilePicPath = $_SERVER['DOCUMENT_ROOT'] . $row['profile_pic'];
                    if (file_exists($oldProfilePicPath)) {
                        unlink($oldProfilePicPath);
                    }
                }
            } else {
                echo "Error moving uploaded file.";
            }
        } else {
            echo "Invalid file type. Only JPEG, PNG, and GIF are allowed.";
        }
    }

    // Handle profile picture data from the camera (base64 encoded)
    if (!empty($_POST["profile_pic_data"])) {
        $base64Image = $_POST["profile_pic_data"];
        $imageData = explode(',', $base64Image);
        $decodedImage = base64_decode($imageData[1]);

        // Generate a unique name for the file
        $uniqueFileName = uniqid() . '.png';
        $destFilePath = $targetDir . $uniqueFileName;

        // Save the decoded image data to a file
        if (file_put_contents($destFilePath, $decodedImage)) {
            // Save the web-accessible path in the database
            $profilePicPath = $uploadDir . $uniqueFileName;

            // Delete the old profile picture if it exists
            if (!empty($row['profile_pic'])) {
                $oldProfilePicPath = $_SERVER['DOCUMENT_ROOT'] . $row['profile_pic'];
                if (file_exists($oldProfilePicPath)) {
                    unlink($oldProfilePicPath);
                }
            }
        } else {
            echo "Error saving snapshot.";
        }
    }

            if (isset($_FILES["id"]) && $_FILES["id"]["error"] == UPLOAD_ERR_OK) {
                $id_front_image_data = file_get_contents($_FILES["id"]["tmp_name"]);
            } else {
                $id_front_image_data = null; // Handle case where no file is uploaded
            }

            if (isset($_FILES["back_id"]) && $_FILES["back_id"]["error"] == UPLOAD_ERR_OK) {
                $back_id_image_data = file_get_contents($_FILES["back_id"]["tmp_name"]);
            } else {
                $back_id_image_data = null; // Handle case where no file is uploaded
            }

            // Prepare SQL update statement
            $sql = "UPDATE patient_db SET 
                    first_name = ?, 
                    middle_name = ?, 
                    surname = ?, 
                    dob = ?, 
                    age = ?,
                    gender = ?, 
                    marital_status = ?, 
                    education_level = ?, 
                    email = ?, 
                    telephone = ?, 
                    next_of_kin_name = ?, 
                    next_of_kin_relation = ?, 
                    payer = ?, 
                    sponsor = ?, 
                    password = ?, 
                    previous_address_uk = ?, 
                    previous_gp_practice = ?, 
                    address_previous_gp = ?, 
                    enlisted_address = ?, 
                    enlistment_date = ?, 
                    discharge_date = ?, 
                    allergy = ?, 
                    disability = ?, 
                    disability_specify = ?, 
                    id_type = ?, 
                    profile_pic = ?,  -- Use correct column name
                    id_front_image = ?, 
                    id_back_image = ?
                    WHERE patient_id = ?";

$stmt = $conn->prepare($sql);
if ($stmt === false) {
    die("Error preparing the statement: " . $conn->error);
}

$stmt->bind_param("ssssisssssssssssssssssssssssi", 
    $first_name, $middle_name, $surname, $dob, $age, $gender, $marital_status, 
    $education_level, $email, $telephone, $next_of_kin_name, $next_of_kin_relation, 
    $payer, $sponsor, $password, $previous_address_uk, $previous_gp_practice, 
    $address_previous_gp, $enlisted_address, $enlistment_date, $discharge_date, 
    $allergy, $disability, $disability_specify, $id_type, $profilePicPath,  // Pass the updated path
    $id_front_image_data, $back_id_image_data, $patient_id);

if ($stmt->execute()) {
    $updateSuccess = true;
} else {
    echo "Error updating record: " . $stmt->error;
}

$stmt->close();
        }

        // Check if ID is set in URL
        if (isset($_GET['id'])) {
            $patient_id = intval($_GET['id']);

            // Prepare and bind
            $stmt = $conn->prepare("SELECT * FROM patient_db WHERE patient_id = ?");
            if ($stmt === false) {
                die("Error preparing the statement: " . $conn->error);
            }

            $stmt->bind_param("i", $patient_id);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                // Display form with pre-filled data
                $row = $result->fetch_assoc();
        ?>

        <?php if ($updateSuccess): ?>
        <div class="notification" id="notification">
            Patient record has been updated.
        </div>
        <script>
            document.getElementById('notification').style.display = 'block';
            setTimeout(function() {
                document.getElementById('notification').style.display = 'none';
            }, 12000);
        </script>
        <?php endif; ?>

        <form action="" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="patient_id" value="<?php echo $row['patient_id']; ?>">

            <!-- Personal Details -->
             <div class="section">
            <h2>Personal Details</h2>
            <div class="form-group">
                <label for="first_name">First Name:</label>
                <input type="text" id="first_name" name="first_name" value="<?php echo htmlspecialchars($row['first_name']); ?>" required>
            </div>
            <div class="form-group">
                <label for="middle_name">Middle Name:</label>
                <input type="text" id="middle_name" name="middle_name" value="<?php echo htmlspecialchars($row['middle_name']); ?>">
            </div>
            <div class="form-group">
                <label for="surname">Surname:</label>
                <input type="text" id="surname" name="surname" value="<?php echo htmlspecialchars($row['surname']); ?>" required>
            </div>
            <div class="form-group">
                <label for="dob">Date of Birth:</label>
                <input type="date" id="dob" name="dob" 
       value="<?php echo htmlspecialchars($row['dob']); ?>" 
       required onchange="calculateAge()" 
       max="<?php echo $today; ?>">
                <label for="age">Age:</label>
                <input type="text" id="age" name="age" value="<?php echo htmlspecialchars($row['age']); ?>" readonly>
            </div>
            <div class="form-group">
                <label for="gender">Gender:</label>
                <select id="gender" name="gender" required>
                    <option value="">Select</option>
                    <option value="Male" <?php if ($row['gender'] == 'Male') echo 'selected'; ?>>Male</option>
                    <option value="Female" <?php if ($row['gender'] == 'Female') echo 'selected'; ?>>Female</option>
                </select>
            </div>
            <div class="form-group">
                <label for="marital_status">Marital Status:</label>
                <select id="marital_status" name="marital_status" required>
                    <option value="">Select</option>
                    <option value="Single" <?php if ($row['marital_status'] == 'Single') echo 'selected'; ?>>Single</option>
                    <option value="Married" <?php if ($row['marital_status'] == 'Married') echo 'selected'; ?>>Married</option>
                    <option value="Divorced" <?php if ($row['marital_status'] == 'Divorced') echo 'selected'; ?>>Divorced</option>
                    <option value="Widowed" <?php if ($row['marital_status'] == 'Widowed') echo 'selected'; ?>>Widowed</option>
                </select>
            </div>
            <div class="form-group">
                <label for="education_level">Education Level:</label>
                <select id="education_level" name="education_level">
                    <option value="">Select</option>
                    <option value="None" <?php if ($row['education_level'] == 'None') echo 'selected'; ?>>None</option>
                    <option value="Primary" <?php if ($row['education_level'] == 'Primary') echo 'selected'; ?>>Primary</option>
                    <option value="Secondary" <?php if ($row['education_level'] == 'Secondary') echo 'selected'; ?>>Secondary</option>
                    <option value="Tertiary" <?php if ($row['education_level'] == 'Tertiary') echo 'selected'; ?>>Tertiary</option>
                </select>
            </div>
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($row['email']); ?>" required>
            </div>
            <div class="form-group">
                <label for="telephone">Telephone:</label>
                <input type="text" id="telephone" name="telephone" value="<?php echo htmlspecialchars($row['telephone']); ?>">
            </div>
        </div>
        <div class="section">
            <!-- Next of Kin -->
            <h2>Next of Kin</h2>
            <div class="form-group">
                <label for="next_of_kin_name">Next of Kin Name:</label>
                <input type="text" id="next_of_kin_name" name="next_of_kin_name" value="<?php echo htmlspecialchars($row['next_of_kin_name']); ?>">
            </div>
            <div class="form-group">
                <label for="next_of_kin_relation">Relation:</label>
                <select id="next_of_kin_relation" name="next_of_kin_relation" required>
                    <option value="">Select</option>
                    <option value="Parent" <?php if ($row['next_of_kin_relation'] == 'Parent') echo 'selected'; ?>>Parent</option>
                    <option value="Sibling" <?php if ($row['next_of_kin_relation'] == 'Sibling') echo 'selected'; ?>>Sibling</option>
                    <option value="Spouse" <?php if ($row['next_of_kin_relation'] == 'Spouse') echo 'selected'; ?>>Spouse</option>
                    <option value="Friend" <?php if ($row['next_of_kin_relation'] == 'Friend') echo 'selected'; ?>>Friend</option>
                </select>
            </div>
        </div>
        <div class="section">
            <!-- Payer Information -->
            <h2>Payer Information</h2>
            <div class="form-group">
                <label for="payer">Payer:</label>
                <select id="payer" name="payer" required>
                    <option value="">Select</option>
                    <option value="Private Cash" <?php if ($row['payer'] == 'Private Cash') echo 'selected'; ?>>Private Cash</option>
                    <option value="Insurance" <?php if ($row['payer'] == 'Insurance') echo 'selected'; ?>>Insurance</option>
                    <option value="Employer" <?php if ($row['payer'] == 'Employer') echo 'selected'; ?>>Employer</option>
                </select>
            </div>
            <div class="form-group">
                <label for="sponsor">Sponsor:</label>
                <select id="sponsor" name="sponsor" required>
                    <option value="">Select</option>
                    <option value="Private Cash" <?php if ($row['sponsor'] == 'Private Cash') echo 'selected'; ?>>Private Cash</option>
                    <option value="Insurance" <?php if ($row['sponsor'] == 'Insurance') echo 'selected'; ?>>Insurance</option>
                    <option value="Employer" <?php if ($row['sponsor'] == 'Employer') echo 'selected'; ?>>Employer</option>
                </select>
            </div>
        </div>
        <div class="section">
            <!-- Login Details -->
            <h2>Login Password</h2>
            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" value="<?php echo htmlspecialchars($row['password']); ?>">
            </div>
        </div>
        <div class="section">
            <!-- Previous GP -->
            <h2>Previous GP</h2>
            <div class="form-group">
                <label for="previous_address_uk">Your previous address in UK:</label>
                <input type="text" id="previous_address_uk" name="previous_address_uk" value="<?php echo htmlspecialchars($row['previous_address_uk']); ?>">
            </div>
            <div class="form-group">
                <label for="previous_gp_practice">Name of previous GP practice:</label>
                <input type="text" id="previous_gp_practice" name="previous_gp_practice" value="<?php echo htmlspecialchars($row['previous_gp_practice']); ?>">
            </div>
            <div class="form-group">
                <label for="address_previous_gp">Address of previous GP practice:</label>
                <input type="text" id="address_previous_gp" name="address_previous_gp" value="<?php echo htmlspecialchars($row['address_previous_gp']); ?>">
            </div>
        </div>
        <div class="section">
            <!-- Armed Forces Information -->
            <h2>Armed Forces Information</h2>
            <div class="form-group">
                <label for="enlisted_address">Address before enlisting:</label>
                <input type="text" id="enlisted_address" name="enlisted_address" value="<?php echo htmlspecialchars($row['enlisted_address']); ?>">
            </div>
            <div class="form-group">
                <label for="enlistment_date">Enlistment date:</label>
                <input type="date" id="enlistment_date" name="enlistment_date" value="<?php echo htmlspecialchars($row['enlistment_date']); ?>">
            </div>
            <div class="form-group">
                <label for="discharge_date">Discharge date:</label>
                <input type="date" id="discharge_date" name="discharge_date" value="<?php echo htmlspecialchars($row['discharge_date']); ?>">
            </div>
        </div>
        <div class="section">
            <!-- Profile Picture -->
            <h2>Profile Picture</h2>
            <div class="form-group">
                <label for="profile_source">Choose Profile Picture Source:</label>
                <select id="profile_source" name="profile_source" onchange="toggleProfileInput()">
                    <option value="select">Choose an Option</option>
                    <option value="device">Upload from Device</option>
                    <option value="camera">Take a Picture</option>
                </select>
                <input type="file" id="profile_pic" name="profile_pic" accept="image/*" style="display: none;">
                <div id="cameraOptions" style="display: none;">
                    <video id="video" width="320" height="240" autoplay></video>
                    <button type="button" onclick="takeSnapshot()">Take Snapshot</button>
                    <button type="button" onclick="cancelSnapshot()">Cancel</button>
                </div>
                <canvas id="canvas" width="320" height="240" style="display: none;"></canvas>
                <input type="hidden" id="profile_pic_data" name="profile_pic_data">
                <div id="snapshotTaken" style="display: none;">
                    <p>Snapshot taken. <button type="button" onclick="discardSnapshot()">Discard</button></p>
                </div>
            </div>
        </div>

        <div class="section">
          
            <!-- Allergy -->
            <h2>Allergy</h2>
            <div class="form-group">
                <label for="allergy">Allergy:</label>
                <input type="text" id="allergy" name="allergy" style="border: 1px solid red;" value="<?php echo htmlspecialchars($row['allergy']); ?>">
            </div>
        </div>
        <div class="section">
            <!-- Disability -->
            <h2>Disability</h2>
            <div class="form-group">
                <label for="disability">Disability:</label>
                <select id="disability" name="disability">
                    <option value="">Select</option>
                    <option value="Yes" <?php if ($row['disability'] == 'Yes') echo 'selected'; ?>>Yes</option>
                    <option value="No" <?php if ($row['disability'] == 'No') echo 'selected'; ?>>No</option>
                </select>
                <input type="text" id="disability_specify" name="disability_specify" placeholder="Specify Disability" style="display: <?php echo $row['disability'] == 'Yes' ? 'block' : 'none'; ?>;" value="<?php echo htmlspecialchars($row['disability_specify']); ?>">
             </div>
            
        </div>
        <div class="section">
            <!-- ID Verification -->
           
            <h2>ID Verification</h2>
            <div class="form-group">
                <label for="id_type">Type of Identification ID:</label>
                <select id="id_type" name="id_type" required>
                    <option value="">Select</option>
                    <option value="Driving Licence" <?php if ($row['id_type'] == 'Driving Licence') echo 'selected'; ?>>Driving Licence</option>
                    <option value="International Passport" <?php if ($row['id_type'] == 'International Passport') echo 'selected'; ?>>International Passport</option>
                    <option value="Residence Card or Permit" <?php if ($row['id_type'] == 'Residence Card or Permit') echo 'selected'; ?>>Residence Card or Permit</option>
                </select>
            </div>
            <div class="form-group" id="id_upload" style="display: none;">
                <label for="id">Upload ID:</label>
                <input type="file" id="id" name="id" accept="image/*" onchange="validateFileSize(this, 50)">
            </div>
            <div class="form-group" id="back_id_upload" style="display: none;">
                <label for="back_id">Upload Back ID (if applicable):</label>
                <input type="file" id="back_id" name="back_id" accept="image/*" onchange="validateFileSize(this, 50)">
            </div>
        </div>
            <input type="submit" value="Update">
        </form>

        <?php
            } else {
                echo "Patient not found.";
            }

            $stmt->close();
        } else {
            echo "No patient ID provided.";
        }

        $conn->close();
        ?>
    </div>
    <script>
        function toggleProfileInput() {
            var profileSource = document.getElementById("profile_source").value;
            var fileInput = document.getElementById("profile_pic");
            var cameraOptions = document.getElementById("cameraOptions");
            var canvas = document.getElementById("canvas");
            var profilePicData = document.getElementById("profile_pic_data");
            var snapshotTaken = document.getElementById("snapshotTaken");

            if (profileSource === "device") {
                fileInput.style.display = "block";
                cameraOptions.style.display = "none";
                canvas.style.display = "none";
                snapshotTaken.style.display = "none";
                profilePicData.value = "";
            } else if (profileSource === "camera") {
                fileInput.style.display = "none";
                cameraOptions.style.display = "block";
            } else {
                fileInput.style.display = "none";
                cameraOptions.style.display = "none";
                canvas.style.display = "none";
                snapshotTaken.style.display = "none";
                profilePicData.value = "";
            }
        }

        function takeSnapshot() {
            var video = document.getElementById("video");
            var canvas = document.getElementById("canvas");
            var context = canvas.getContext("2d");
            var profilePicData = document.getElementById("profile_pic_data");
            var snapshotTaken = document.getElementById("snapshotTaken");

            context.drawImage(video, 0, 0, canvas.width, canvas.height);
            var dataUrl = canvas.toDataURL("image/png");
            profilePicData.value = dataUrl;
            canvas.style.display = "block";
            snapshotTaken.style.display = "block";
        }

        function cancelSnapshot() {
            var canvas = document.getElementById("canvas");
            var profilePicData = document.getElementById("profile_pic_data");
            var snapshotTaken = document.getElementById("snapshotTaken");

            canvas.style.display = "none";
            snapshotTaken.style.display = "none";
            profilePicData.value = "";
        }

        function discardSnapshot() {
            var canvas = document.getElementById("canvas");
            var profilePicData = document.getElementById("profile_pic_data");
            var snapshotTaken = document.getElementById("snapshotTaken");

            canvas.style.display = "none";
            snapshotTaken.style.display = "none";
            profilePicData.value = "";
        }
    </script>
</body>
</html>
