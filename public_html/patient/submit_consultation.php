<?php
include '../init.php';
include '../config.php';
include '../access_control.php';

// Enable error reporting
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$message = "";
$message_type = "success"; // Default message type

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Debugging statement to check form data
    echo "Form data received:<br>";
    echo "<pre>";
    print_r($_POST);
    echo "</pre>";

    // Check if patient_id is received correctly
    if (!isset($_POST['patient_id']) || empty($_POST['patient_id'])) {
        die("Patient ID is missing or empty!");
    }

    $patient_id = intval($_POST['patient_id']);
    echo "Debug: Patient ID received is: " . $patient_id;

    // Retrieve and sanitize all form input fields
    $reason_for_visit = $conn->real_escape_string($_POST['reason_for_visit'] ?? '');
    $history_of_illness = $conn->real_escape_string($_POST['history_of_illness'] ?? '');
    $past_medical_history = $conn->real_escape_string($_POST['past_medical_history'] ?? '');
    $family_history = $conn->real_escape_string($_POST['family_history'] ?? '');
    $social_history = $conn->real_escape_string($_POST['social_history'] ?? '');
    $allergies = $conn->real_escape_string($_POST['allergies'] ?? '');
    $initial_diagnosis = $conn->real_escape_string($_POST['initial_diagnosis'] ?? '');
    $final_diagnosis = $conn->real_escape_string($_POST['final_diagnosis'] ?? '');
    $blood_pressure = $conn->real_escape_string($_POST['blood_pressure'] ?? '');
    $heart_rate = $conn->real_escape_string($_POST['heart_rate'] ?? '');
    $respiratory_rate = $conn->real_escape_string($_POST['respiratory_rate'] ?? '');
    $temperature = $conn->real_escape_string($_POST['temperature'] ?? '');
    $weight = $conn->real_escape_string($_POST['weight'] ?? '');
    $height = $conn->real_escape_string($_POST['height'] ?? '');
    $bmi = $conn->real_escape_string($_POST['bmi'] ?? '');
    $general_appearance = $conn->real_escape_string($_POST['general_appearance'] ?? '');
    $heent = $conn->real_escape_string($_POST['heent'] ?? '');
    $cardiovascular = $conn->real_escape_string($_POST['cardiovascular'] ?? '');
    $respiratory = $conn->real_escape_string($_POST['respiratory'] ?? '');
    $gastrointestinal = $conn->real_escape_string($_POST['gastrointestinal'] ?? '');
    $musculoskeletal = $conn->real_escape_string($_POST['musculoskeletal'] ?? '');
    $neurological = $conn->real_escape_string($_POST['neurological'] ?? '');
    $skin = $conn->real_escape_string($_POST['skin'] ?? '');

    // ---------------------------
    // Fix: Convert lab_tests into array
    // ---------------------------
    $lab_test_ids = [];
    if (isset($_POST['lab_tests']) && !empty($_POST['lab_tests'])) {
        // If it’s already an array (e.g. lab_tests[] in form), just cast to int
        if (is_array($_POST['lab_tests'])) {
            $lab_test_ids = array_map('intval', $_POST['lab_tests']);
        }
        // Else if it’s a comma-separated string "3,7,9"
        else {
            $lab_test_ids = array_map('intval', explode(',', $_POST['lab_tests']));
        }
    }
    // Now $lab_test_ids is a proper array of integers, e.g. [3,7,9]
    
    // JSON for storing in consultations table
    $lab_tests_json = json_encode($lab_test_ids);

    // Gather medications data
    $medication_ids = $_POST['medication_ids'] ?? [];
    $medication_names = $_POST['medication_names'] ?? [];
    $medication_dosages = $_POST['medication_dosages'] ?? [];

    // Convert arrays to JSON for storing
    $medication_ids_json = json_encode($medication_ids);
    $medication_names_json = json_encode($medication_names);
    $medication_dosages_json = json_encode($medication_dosages);

    $other_prescriptions = $conn->real_escape_string($_POST['other_prescriptions'] ?? '');
    $follow_up_date = $conn->real_escape_string($_POST['follow_up_date'] ?? '');
    $symptoms_to_watch = $conn->real_escape_string($_POST['symptoms_to_watch'] ?? '');
    $emergency_instructions = $conn->real_escape_string($_POST['emergency_instructions'] ?? '');
    $education_materials = $conn->real_escape_string($_POST['education_materials'] ?? '');
    $doctor_comments = $conn->real_escape_string($_POST['doctor_comments'] ?? '');
    $patient_concerns = $conn->real_escape_string($_POST['patient_concerns'] ?? '');
    $department = $_SESSION['usergroup'] ?? '';
    $consultation_date = date('Y-m-d');
    $consultation_time = date('H:i:s'); // Use seconds for more precise timestamps

    $staff_name = '';
    if (isset($_SESSION['username'])) {
        $stmt = $conn->prepare('SELECT title, first_name, middle_name, surname FROM users WHERE username = ?');
        $stmt->bind_param('s', $_SESSION['username']);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $user_row = $result->fetch_assoc();
            $staff_name = htmlspecialchars($user_row['title'] . ' ' . $user_row['first_name'] . ' ' . $user_row['middle_name'] . ' ' . $user_row['surname']);
        }
        $stmt->close();
    }

    try {
        // Insert the consultation details into the consultations table
        $sql = "INSERT INTO consultations 
            (patient_id, reason_for_visit, history_of_illness, past_medical_history, family_history, 
             social_history, allergies, initial_diagnosis, final_diagnosis, vital_signs_blood_pressure, 
             vital_signs_heart_rate, vital_signs_respiratory_rate, vital_signs_temperature, 
             vital_signs_weight, vital_signs_height, vital_signs_bmi, physical_examination_general_appearance, 
             physical_examination_heent, physical_examination_cardiovascular, physical_examination_respiratory, 
             physical_examination_gastrointestinal, physical_examination_musculoskeletal, 
             physical_examination_neurological, physical_examination_skin, 
             lab_tests, medications, medication_ids, medication_names, medication_dosages,
             other_prescriptions, follow_up_date, symptoms_to_watch, emergency_instructions, 
             education_materials, doctor_comments, patient_concerns, consultation_date, 
             consultation_time, doctor_name, department) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $conn->prepare($sql);
        if ($stmt === false) {
            throw new Exception("Prepare failed: " . $conn->error);
        }

        // The giant bind_param with types + variables
        $stmt->bind_param(
            "isssssssssssssssssssssssssssssssssssssss",
            $patient_id,
            $reason_for_visit,
            $history_of_illness,
            $past_medical_history,
            $family_history,
            $social_history,
            $allergies,
            $initial_diagnosis,
            $final_diagnosis,
            $blood_pressure,
            $heart_rate,
            $respiratory_rate,
            $temperature,
            $weight,
            $height,
            $bmi,
            $general_appearance,
            $heent,
            $cardiovascular,
            $respiratory,
            $gastrointestinal,
            $musculoskeletal,
            $neurological,
            $skin,
            // store JSON array of lab test IDs
            $lab_tests_json,
            // "medications" is unused in your snippet, but we won't touch it
            $medications,
            $medication_ids_json,
            $medication_names_json,
            $medication_dosages_json,
            $other_prescriptions,
            $follow_up_date,
            $symptoms_to_watch,
            $emergency_instructions,
            $education_materials,
            $doctor_comments,
            $patient_concerns,
            $consultation_date,
            $consultation_time,
            $staff_name,
            $department
        );

        // Execute the statement
        if ($stmt->execute()) {
            $consultation_id = $stmt->insert_id;
            $message = "Consultation details saved successfully!";
            $message_type = "success";

            // Check if there are medications to save
            if (!empty($medication_ids) || !empty($medication_names) || !empty($medication_dosages)) {
                $prescription_stmt = $conn->prepare(
                    "INSERT INTO prescriptions 
                     (patient_id, doctor_name, department, date_prescribed, time_prescribed, 
                      medications, medication_ids, medication_names, medication_dosages, status) 
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
                );
                $status = 'Pending';

                $prescription_stmt->bind_param(
                    "isssssssss",
                    $patient_id,
                    $staff_name,
                    $department,
                    $consultation_date,
                    $consultation_time,
                    $medications,
                    $medication_ids_json,
                    $medication_names_json,
                    $medication_dosages_json,
                    $status
                );

                if ($prescription_stmt->execute()) {
                    $message .= " Prescription saved successfully.";
                } else {
                    $message .= " Prescription saving failed: " . $prescription_stmt->error;
                }
                $prescription_stmt->close();
            }

            // ----------------------------
            // Insert lab tests into lab_requests table
            // ----------------------------
            if (!empty($lab_test_ids)) {
                foreach ($lab_test_ids as $lab_test_id) {
                    if ($lab_test_id > 0) {
                        $lab_stmt = $conn->prepare(
                            "INSERT INTO lab_requests 
                             (consultation_id, patient_id, template_id, requested_by, date_requested, status) 
                             VALUES (?, ?, ?, ?, NOW(), ?)"
                        );

                        if ($lab_stmt === false) {
                            throw new Exception("Prepare failed for lab_requests: " . $conn->error);
                        }

                        $status = 'Pending';
                        $lab_stmt->bind_param(
                            "iisss",
                            $consultation_id,
                            $patient_id,
                            $lab_test_id,
                            $_SESSION['username'],
                            $status
                        );

                        if (!$lab_stmt->execute()) {
                            throw new Exception("Execute failed for lab_requests: " . $lab_stmt->error);
                        }

                        $lab_stmt->close();
                    }
                }
                $message .= " Lab requests saved successfully.";
            }
        } else {
            $message_type = "error";
            $message = "Error saving consultation: " . $stmt->error;
        }

        $stmt->close();
    } catch (Exception $e) {
        $message_type = "error";
        $message = "An error occurred: " . $e->getMessage();
    }
}

$_SESSION['message'] = $message;
$_SESSION['message_type'] = $message_type;
header("Location: consultation.php?id=" . $patient_id);
exit();
