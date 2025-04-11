<?php
include '../init.php'; // Include your database connection and any initialization logic
include '../config.php';
include '../access_control.php'; // Ensure access control is implemented for authorized users only

// Enable error reporting
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

// Check if consultation_id is passed via GET request
if (isset($_GET['consultation_id']) && !empty($_GET['consultation_id'])) {
    $consultation_id = intval($_GET['consultation_id']);

    // Fetch the consultation details based on consultation_id
    $sql = "SELECT 
                c.patient_id, 
                c.reason_for_visit, 
                c.history_of_illness, 
                c.final_diagnosis, 
                c.vital_signs_blood_pressure, 
                c.vital_signs_heart_rate, 
                c.vital_signs_respiratory_rate, 
                c.vital_signs_temperature, 
                c.vital_signs_weight, 
                c.vital_signs_height, 
                c.vital_signs_bmi, 
                c.physical_examination_general_appearance, 
                c.physical_examination_heent, 
                c.physical_examination_cardiovascular, 
                c.physical_examination_respiratory, 
                c.physical_examination_gastrointestinal, 
                c.physical_examination_musculoskeletal, 
                c.physical_examination_neurological, 
                c.physical_examination_skin, 
                c.lab_tests, 
                c.medications, 
                c.medication_ids, 
                c.medication_names, 
                c.medication_dosages, 
                c.other_prescriptions, 
                c.follow_up_date, 
                c.symptoms_to_watch, 
                c.emergency_instructions, 
                c.education_materials, 
                c.doctor_comments, 
                c.patient_concerns, 
                c.doctor_name, 
                c.department, 
                c.consultation_date, 
                c.consultation_time 
            FROM 
                consultations c 
            WHERE 
                c.consultation_id = ?";

    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("i", $consultation_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            // Fetch all the details for this consultation
            $consultation = $result->fetch_assoc();
            
            // Displaying the data in a modal (this is for your fetch_consultation_details.php)
            echo json_encode($consultation); // Return the consultation data as JSON
        } else {
            // Handle no consultation found case
            echo json_encode(['error' => 'No consultation found for this ID']);
        }
        $stmt->close();
    } else {
        // Handle SQL prepare error
        echo json_encode(['error' => 'Error preparing query: ' . $conn->error]);
    }
} else {
    // Handle missing consultation_id
    echo json_encode(['error' => 'Missing consultation_id']);
}

$conn->close();
?>
