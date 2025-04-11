<?php
session_start();

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "administrator";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Generate patient ID
function generatePatientID() {
    return rand(1000000, 9999999);
}

$patient_id = generatePatientID();

// Collect form data
$first_name = $_POST['first_name'];
$middle_name = $_POST['middle_name'];
$surname = $_POST['surname'];
$dob = date('Y-m-d', strtotime($_POST["dob"]));
$age = $_POST['age'];
$gender = $_POST['gender'];
$marital_status = $_POST['marital_status'];
$education_level = $_POST['education_level'];
$email = $_POST['email'];
$address = $_POST['address'];
$country = $_POST['country'];
$telephone = $_POST['telephone'];
$next_of_kin_name = $_POST['next_of_kin_name'];
$next_of_kin_relation = $_POST['next_of_kin_relation'];
$next_of_kin_telephone = $_POST['next_of_kin_telephone'];
$next_of_kin_city = $_POST['next_of_kin_city'];
$payer = $_POST['payer'];
$sponsor = $_POST['sponsor'];
$username = $_POST['username'];
$password = password_hash($_POST['password'], PASSWORD_BCRYPT); // Encrypt password
$previous_address_uk = $_POST['previous_address_uk'];
$previous_gp_practice = $_POST['previous_gp_practice'];
$address_previous_gp = $_POST['address_previous_gp'];
$enlisted_address = $_POST['enlisted_address'];
$enlistment_date = $_POST['enlistment_date'];
$discharge_date = $_POST['discharge_date'];
$allergy = $_POST['allergy'];
$disability = $_POST['disability'];
$id_type = $_POST['id_type'];
$declaration_confirm = isset($_POST['declaration_confirm']) ? 1 : 0;

// Create directories for profile pictures and ID uploads if they don't exist
$profile_pictures_dir = 'uploads/profile_pictures/';
$id_uploads_dir = 'uploads/id_uploads/';

if (!file_exists($profile_pictures_dir)) {
    mkdir($profile_pictures_dir, 0777, true);
}

if (!file_exists($id_uploads_dir)) {
    mkdir($id_uploads_dir, 0777, true);
}

// Handle file upload for profile picture
$profile_pic_path = null;
if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] == UPLOAD_ERR_OK) {
    $profile_pic_filename = $profile_pictures_dir . basename($_FILES['profile_pic']['name']);
    if (move_uploaded_file($_FILES['profile_pic']['tmp_name'], $profile_pic_filename)) {
        $profile_pic_path = $profile_pic_filename;
    } else {
        echo "Error uploading profile picture.";
    }
}

// Handle file upload for front ID image
$id_front_image_path = null;
if (isset($_FILES['id_front_image']) && $_FILES['id_front_image']['error'] == UPLOAD_ERR_OK) {
    $id_front_image_filename = $id_uploads_dir . basename($_FILES['id_front_image']['name']);
    if (move_uploaded_file($_FILES['id_front_image']['tmp_name'], $id_front_image_filename)) {
        $id_front_image_path = $id_front_image_filename;
    } else {
        echo "Error uploading front ID image.";
    }
}

// Handle file upload for back ID image
$id_back_image_path = null;
if (isset($_FILES['id_back_image']) && $_FILES['id_back_image']['error'] == UPLOAD_ERR_OK) {
    $id_back_image_filename = $id_uploads_dir . basename($_FILES['id_back_image']['name']);
    if (move_uploaded_file($_FILES['id_back_image']['tmp_name'], $id_back_image_filename)) {
        $id_back_image_path = $id_back_image_filename;
    } else {
        echo "Error uploading back ID image.";
    }
}

// Prepare and bind
$stmt = $conn->prepare("INSERT INTO patient_db (patient_id, first_name, middle_name, surname, dob, age, gender, marital_status, education_level, email, address, country, telephone, next_of_kin_name, next_of_kin_relation, next_of_kin_telephone, next_of_kin_city, payer, sponsor, username, password, previous_address_uk, previous_gp_practice, address_previous_gp, enlisted_address, enlistment_date, discharge_date, profile_pic, allergy, disability, id_type, declaration_confirm, id_front_image, id_back_image) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

$stmt->bind_param("ssssissssssssssssssssssssssssssssi", $patient_id, $first_name, $middle_name, $surname, $dob, $age, $gender, $marital_status, $education_level, $email, $address, $country, $telephone, $next_of_kin_name, $next_of_kin_relation, $next_of_kin_telephone, $next_of_kin_city, $payer, $sponsor, $username, $password, $previous_address_uk, $previous_gp_practice, $address_previous_gp, $enlisted_address, $enlistment_date, $discharge_date, $profile_pic_path, $allergy, $disability, $id_type, $declaration_confirm, $id_front_image_path, $id_back_image_path);

if ($stmt->execute()) {
    $_SESSION['message'] = "New patient record created successfully";
    $_SESSION['message_type'] = "success";
    header("Location: patient.php");
    exit();
} else {
    $_SESSION['message'] = "Error: " . $stmt->error;
    $_SESSION['message_type'] = "error";
    header("Location: patient.php");
    exit();
}

$stmt->close();
$conn->close();
?>
