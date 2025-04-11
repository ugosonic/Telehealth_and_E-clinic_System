<?php
include 'init.php';
include 'config.php';
include 'access_control.php';
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
    $patient = $result->fetch_assoc();
    $profilePicPath = isset($patient['profile_pic']) ? $patient['profile_pic'] : null;
} else {
    // Handle the case where no patient is found
    die("No patient found with ID: " . htmlspecialchars($patient_id));
}

// Fetch user details
$username = '';
$email = '';
$department = $_SESSION['usergroup'];
if (isset($_SESSION['username'])) {
    $stmt = $conn->prepare('SELECT title, first_name, middle_name, surname, email, username FROM users WHERE username = ?');
    if ($stmt === false) {
        die("Prepare failed: " . $conn->error);
    }
    $stmt->bind_param('s', $_SESSION['username']);
    if (!$stmt->execute()) {
        die("Execute failed: " . $stmt->error);
    }
    $result = $stmt->get_result();
    if ($result === false) {
        die("Get result failed: " . $stmt->error);
    }
    if ($result->num_rows > 0) {
        $user_row = $result->fetch_assoc();
        $username = $user_row['username'];
        $staff_name = htmlspecialchars($user_row['title'] . ' ' . $user_row['first_name'] . ' ' . $user_row['middle_name'] . ' ' . $user_row['surname']);
        $email = $user_row['email'];
    } else {
        echo "<p>User not found. Please <a href='/my clinic/login/login.php'>login again</a>.</p>";
    }
    $stmt->close();
}

// Fetch available laboratory tests
$laboratory_tests = [];
$sql = "SELECT id, name AS test_name
        FROM lab_test_templates
        ORDER BY name ASC";

$result = $conn->query($sql);
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $laboratory_tests[] = $row;
    }
}

// Fetch available medications
$medications = [];
$sql = "SELECT id, name FROM inventory";
$result = $conn->query($sql);
if ($result === false) {
    die("Query failed: " . $conn->error);
}
while ($row = $result->fetch_assoc()) {
    $medications[] = $row;
}

$current_date = date('Y-m-d');
$current_time = date('H:i');

// Close the connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/patient_record.css">
    <link rel="stylesheet" href="patient.css">
    <title>Consultation</title>
</head>
<body>
    <div class="dash-body">
        <div class="profile-header custom-profile">
            <div class="profile-details">
                <div class="header-container">
                <h1 class="header-name">Consultation</h1>
                    <h1 class="header-title"><?php echo htmlspecialchars($patient['first_name'] . ' ' . $patient['surname']); ?></h1>
                    <div class="profile-picture">
                    <?php
include 'patient_profile_picture.php';

// Assuming you have fetched the patient data into $patientData

displayPatientProfilePicture($patient);
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

        <?php if (isset($_SESSION['message'])): ?>
            <div class="popup-message <?= $_SESSION['message_type']; ?>" id="message-popup">
                <?= $_SESSION['message']; ?>
                <button class="close-btn" onclick="document.getElementById('message-popup').style.display='none'">&times;</button>
            </div>
            <?php unset($_SESSION['message'], $_SESSION['message_type']); ?>
        <?php endif; ?> 

        <form action="submit_consultation.php" method="post">
            <input type="hidden" name="patient_id" value="<?= htmlspecialchars($patient_id); ?>">

            <div class="section">
                <h2>Patient Information</h2>
                <div class="plain-text"><strong>Patient ID:</strong> <?= htmlspecialchars($patient['patient_id']); ?></div>
                <div class="plain-text"><strong>Patient Full Name:</strong> <?= htmlspecialchars($patient['first_name'] . ' ' . $patient['middle_name'] . ' ' . $patient['surname']); ?></div>
                <div class="plain-text"><strong>Date of Birth:</strong> <?= htmlspecialchars($patient['dob']); ?></div>
                <div class="plain-text"><strong>Age:</strong> <?= htmlspecialchars($patient['age']); ?></div>
                <div class="plain-text"><strong>Gender:</strong> <?= htmlspecialchars($patient['gender']); ?></div>
                <div class="plain-text"><strong>Contact Information:</strong> <?= htmlspecialchars($patient['telephone']); ?></div>
            </div>
            
            <div class="section">
                <h2>Consultation Details</h2>
                <div class="plain-text"><strong>Date of Consultation:</strong> <?= $current_date ?></div>
                <div class="plain-text"><strong>Time of Consultation:</strong> <?= $current_time ?></div>
                <div class="plain-text"><strong>Staff Name:</strong> <?= $staff_name ?></div>
                <div class="plain-text"><strong>Department:</strong> <?= $department ?></div>
                <input type="hidden" name="consultation_date" value="<?= $current_date ?>">
                <input type="hidden" name="consultation_time" value="<?= $current_time ?>">
            </div>

            <?php
            function canEditVitalSigns($userGroup) {
                return $userGroup === 'Doctor' || $userGroup === 'Nurse';
            }
            $isEditable = canEditVitalSigns($_SESSION['usergroup']);
            ?>

            <div class="section">
                <h2>Vital Signs</h2>
                <div class="form-group">
                    <span>
                        <label for="blood_pressure">Blood Pressure (mmHg):</label>
                        <input type="text" id="blood_pressure" name="blood_pressure" oninput="validateVitalSigns()" <?= !$isEditable ? 'readonly' : ''; ?>>
                    </span>
                    <span>
                        <label for="heart_rate">Heart Rate (bpm):</label>
                        <input type="text" id="heart_rate" name="heart_rate" oninput="validateVitalSigns()" <?= !$isEditable ? 'readonly' : ''; ?>>
                    </span>
                </div>
                <div class="form-group">
                    <span>
                        <label for="respiratory_rate">Respiratory Rate (breaths/min):</label>
                        <input type="text" id="respiratory_rate" name="respiratory_rate" oninput="validateVitalSigns()" <?= !$isEditable ? 'readonly' : ''; ?>>
                    </span>
                    <span>
                        <label for="temperature">Temperature (°C):</label>
                        <input type="text" id="temperature" name="temperature" oninput="validateVitalSigns()" <?= !$isEditable ? 'readonly' : ''; ?>>
                    </span>
                </div>
                <div class="form-group">
                    <span>
                        <label for="weight">Weight (kg):</label>
                        <input type="text" id="weight" name="weight" oninput="calculateBMI()" <?= !$isEditable ? 'readonly' : ''; ?>>
                    </span>
                    <span>
                        <label for="height">Height (cm):</label>
                        <input type="text" id="height" name="height" oninput="calculateBMI()" <?= !$isEditable ? 'readonly' : ''; ?>>
                    </span>
                </div>
                <div class="form-group">
                    <label for="bmi">BMI:</label>
                    <input type="text" id="bmi" name="bmi" readonly>
                </div>
                <a href="#bmi-ranges-modal" class="btn" id="open-modal">View BMI Ranges</a>
            </div>
            
            <?php
            function isDoctor($userGroup) {
                return $userGroup === 'Doctor' || $userGroup === 'Nurse';
            }
            $isEditable = isDoctor($_SESSION['usergroup']);
            ?>

            <div class="section">
                <h2>Visit Details</h2>
                <div class="form-group">
                    <label for="reason_for_visit">Reason for Visit / Chief Complaint:</label>
                    <textarea id="reason_for_visit" name="reason_for_visit" <?= !$isEditable ? 'readonly' : ''; ?>></textarea>
                </div>
                <div class="form-group">
                    <label for="history_of_illness">History of Present Illness:</label>
                    <textarea id="history_of_illness" name="history_of_illness" <?= !$isEditable ? 'readonly' : ''; ?>></textarea>
                </div>
                <div class="form-group">
                    <label for="past_medical_history">Past Medical History:</label>
                    <textarea id="past_medical_history" name="past_medical_history" <?= !$isEditable ? 'readonly' : ''; ?>></textarea>
                </div>
                <div class="form-group">
                    <label for="family_history">Family History:</label>
                    <textarea id="family_history" name="family_history" <?= !$isEditable ? 'readonly' : ''; ?>></textarea>
                </div>
                <div class="form-group">
                    <label for="social_history">Social History:</label>
                    <textarea id="social_history" name="social_history" <?= !$isEditable ? 'readonly' : ''; ?>></textarea>
                </div>
                <div class="form-group">
                    <label for="allergies">Allergies:</label>
                    <textarea id="allergies" name="allergies" <?= !$isEditable ? 'readonly' : ''; ?>></textarea>
                </div>
                <div class="form-group">
                    <label for="initial_diagnosis">Initial Diagnosis:</label>
                    <textarea id="initial_diagnosis" name="initial_diagnosis" <?= !$isEditable ? 'readonly' : ''; ?>></textarea>
                </div>
                <div class="form-group">
                    <label for="final_diagnosis">Final Diagnosis:</label>
                    <textarea id="final_diagnosis" name="final_diagnosis" <?= !$isEditable ? 'readonly' : ''; ?>></textarea>
                </div>
            </div>

            <div class="section">
                <h2>Physical Examination</h2>
                <div class="form-group">
                    <label for="general_appearance">General Appearance:</label>
                    <textarea id="general_appearance" name="general_appearance"></textarea>
                </div>
                <div class="form-group">
                    <label for="heent">HEENT:</label>
                    <textarea id="heent" name="heent"></textarea>
                </div>
                <div class="form-group">
                    <label for="cardiovascular">Cardiovascular:</label>
                    <textarea id="cardiovascular" name="cardiovascular"></textarea>
                </div>
                <div class="form-group">
                    <label for="respiratory">Respiratory:</label>
                    <textarea id="respiratory" name="respiratory"></textarea>
                </div>
                <div class="form-group">
                    <label for="gastrointestinal">Gastrointestinal:</label>
                    <textarea id="gastrointestinal" name="gastrointestinal"></textarea>
                </div>
                <div class="form-group">
                    <label for="musculoskeletal">Musculoskeletal:</label>
                    <textarea id="musculoskeletal" name="musculoskeletal"></textarea>
                </div>
                <div class="form-group">
                    <label for="neurological">Neurological:</label>
                    <textarea id="neurological" name="neurological"></textarea>
                </div>
                <div class="form-group">
                    <label for="skin">Skin:</label>
                    <textarea id="skin" name="skin"></textarea>
                </div>
            </div>
            
            <div class="section">
                <h2>Laboratory Tests and Results</h2>
                <div class="form-group">
                    <label for="lab_test_search">Search and Add Lab Tests:</label>
                    <input type="text" id="lab_test_search" placeholder="Search for a lab test...">
                    <select id="lab_test_dropdown">
                        <option value="">Select a lab test</option>
                        <?php foreach ($laboratory_tests as $test): ?>
                            <option value="<?= $test['id'] ?>"><?= $test['test_name'] ?></option>
                        <?php endforeach; ?>
                    </select>
                    <button type="button" class="add-button" onclick="addLabTest()">Add</button>
                    <div class="selected-items" id="selected_lab_tests"></div>
                    <input type="hidden" id="lab_tests" name="lab_tests">
                </div>
            </div>
            
            <div class="section">
                <h2>Treatment Plan</h2>
                <div class="form-group">
                    <label for="medication_search">Search and Add Medications:</label>
                    <input type="text" id="medication_search" placeholder="Search for a medication...">
                    <select id="medication_dropdown">
                        <option value="">Select a medication</option>
                        <?php foreach ($medications as $medication): ?>
                            <option value="<?= $medication['id'] ?>"><?= $medication['name'] ?></option>
                        <?php endforeach; ?>
                    </select>
                    <input type="text" id="dosage" placeholder="Enter Dosage">
                    <button type="button" class="add-button" onclick="addMedication()">Add</button>
                    <div class="selected-items" id="selected_medications"></div>
                    <!-- The hidden input for medications will be created dynamically -->
                </div>
                <div class="form-group">
                    <label for="other_prescriptions">Other Prescriptions and Notes:</label>
                    <textarea id="other_prescriptions" name="other_prescriptions"></textarea>
                </div>
            </div>

            <div class="section">
                <h2>Follow-Up Instructions</h2>
                <div class="form-group">
                    <label for="follow_up_date">Follow-Up Appointment Date:</label>
                    <input type="date" id="follow_up_date" name="follow_up_date">
                </div>
                <div class="form-group">
                    <label for="symptoms_to_watch">Symptoms to Watch For:</label>
                    <textarea id="symptoms_to_watch" name="symptoms_to_watch"></textarea>
                </div>
                <div class="form-group">
                    <label for="emergency_instructions">Emergency Instructions:</label>
                    <textarea id="emergency_instructions" name="emergency_instructions"></textarea>
                </div>
                <div class="form-group">
                    <label for="education_materials">Patient Education Materials Provided:</label>
                    <textarea id="education_materials" name="education_materials"></textarea>
                </div>
            </div>
            
            <div class="section">
                <h2>Additional Notes</h2>
                <div class="form-group">
                    <label for="doctor_comments">Doctor's Additional Comments:</label>
                    <textarea id="doctor_comments" name="doctor_comments"></textarea>
                </div>
                <div class="form-group">
                    <label for="patient_concerns">Patient's Questions and Concerns:</label>
                    <textarea id="patient_concerns" name="patient_concerns"></textarea>
                </div>
            </div>
            
            <div class="section">
                <h2>Signature</h2>
                <div class="plain-text"><strong>Signature:</strong> <?= $staff_name ?></div>
                <div class="plain-text"><strong>Date:</strong> <?= $current_date ?></div>
            </div>
            
            <button type="submit" class="btns btn-primary">Submit Consultation</button>
        </form>
    </div>

    <div id="bmi-ranges-modal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2>BMI Ranges</h2>
            <table>
                <tr>
                    <th>Category</th>
                    <th>BMI Range</th>
                    <th>Color</th>
                </tr>
                <tr>
                    <td>Underweight</td>
                    <td>&lt; 18.5</td>
                    <td style="background-color: lightblue;">Light Blue</td>
                </tr>
                <tr>
                    <td>Normal weight</td>
                    <td>18.5 - 24.9</td>
                    <td style="background-color: lightgreen;">Light Green</td>
                </tr>
                <tr>
                    <td>Overweight</td>
                    <td>25 - 29.9</td>
                    <td style="background-color: yellow;">Yellow</td>
                </tr>
                <tr>
                    <td>Obesity</td>
                    <td>&ge; 30</td>
                    <td style="background-color: red;">Red</td>
                </tr>
            </table>
        </div>
    </div>
    <script>
        const labTests = [];
        const medications = [];

        function addLabTest() {
            const labTestDropdown = document.getElementById('lab_test_dropdown');
            const selectedLabTests = document.getElementById('selected_lab_tests');
            const selectedValue = labTestDropdown.value;
            const selectedText = labTestDropdown.options[labTestDropdown.selectedIndex].text;

            if (selectedValue && !labTests.includes(selectedValue)) {
                labTests.push(selectedValue);
                const div = document.createElement('div');
                div.innerHTML = `${selectedText} <button type="button" onclick="removeLabTest('${selectedValue}', this)">Remove</button>`;
                selectedLabTests.appendChild(div);
                document.getElementById('lab_tests').value = labTests.join(',');
            }
        }

        function removeLabTest(value, button) {
            const index = labTests.indexOf(value);
            if (index > -1) {
                labTests.splice(index, 1);
                button.parentElement.remove();
                document.getElementById('lab_tests').value = labTests.join(',');
            }
        }

        const selectedMedications = [];

        function addMedication() {
            const medicationDropdown = document.getElementById('medication_dropdown');
            const dosageInput = document.getElementById('dosage');
            const selectedMedicationsDiv = document.getElementById('selected_medications');

            const selectedValue = medicationDropdown.value;
            const selectedText = medicationDropdown.options[medicationDropdown.selectedIndex].text;
            const dosage = dosageInput.value.trim();

            if (selectedValue && dosage) {
                // Add medication to the selected list
                const medicationEntry = {
                    id: parseInt(selectedValue),
                    name: selectedText,
                    dosage: dosage
                };

                selectedMedications.push(medicationEntry);

                // Create a new div element to display the selected medication
                const medicationDiv = document.createElement('div');
                medicationDiv.classList.add('medication-entry');

                // Add hidden input for medication ID
                const medicationIdInput = document.createElement('input');
                medicationIdInput.type = 'hidden';
                medicationIdInput.name = 'medication_ids[]';
                medicationIdInput.value = medicationEntry.id;

                // Add text input for medication name (non-editable)
                const medicationNameInput = document.createElement('input');
                medicationNameInput.type = 'text';
                medicationNameInput.name = 'medication_names[]';
                medicationNameInput.value = medicationEntry.name;
                medicationNameInput.readOnly = true;

                // Add text input for dosage (non-editable)
                const medicationDosageInput = document.createElement('input');
                medicationDosageInput.type = 'text';
                medicationDosageInput.name = 'medication_dosages[]';
                medicationDosageInput.value = medicationEntry.dosage;
                medicationDosageInput.readOnly = true;

                // Create a remove button
                const removeButton = document.createElement('button');
                removeButton.type = 'button';
                removeButton.innerText = 'Remove';
                removeButton.onclick = function() {
                    removeMedication(medicationEntry.id, medicationDiv);
                };

                // Append elements to the medication div
                medicationDiv.appendChild(medicationIdInput);
                medicationDiv.appendChild(medicationNameInput);
                medicationDiv.appendChild(medicationDosageInput);
                medicationDiv.appendChild(removeButton);

                // Append the medication div to the selected medications container
                selectedMedicationsDiv.appendChild(medicationDiv);

                // Clear the input fields
                medicationDropdown.selectedIndex = 0;
                dosageInput.value = '';
            }
        }

        function removeMedication(medicationId, medicationDiv) {
            // Remove medication from the selected list
            const index = selectedMedications.findIndex(med => med.id === medicationId);
            if (index > -1) {
                selectedMedications.splice(index, 1);
            }

            // Remove the medication div from the DOM
            medicationDiv.remove();
        }

        // Autocomplete functionality
        const labTestSearch = document.getElementById('lab_test_search');
        const medicationSearch = document.getElementById('medication_search');

        labTestSearch.addEventListener('input', function() {
            autocomplete(this, 'lab_tests');
        });

        medicationSearch.addEventListener('input', function() {
            autocomplete(this, 'medications');
        });

        function autocomplete(input, type) {
            const list = type === 'lab_tests' ? <?= json_encode($laboratory_tests) ?> : <?= json_encode($medications) ?>;
            const value = input.value.toLowerCase();
            let suggestions = list.filter(item => item.test_name.toLowerCase().includes(value) || item.name.toLowerCase().includes(value));
            
            if (suggestions.length > 0) {
                input.setAttribute('list', type + '_suggestions');
                let dataList = document.getElementById(type + '_suggestions');
                if (!dataList) {
                    dataList = document.createElement('datalist');
                    dataList.id = type + '_suggestions';
                    input.appendChild(dataList);
                }
                dataList.innerHTML = suggestions.map(item => `<option value="${item.test_name || item.name}">`).join('');
            } else {
                input.removeAttribute('list');
            }
        }

        function validateVitalSigns() {
            const bloodPressure = document.getElementById('blood_pressure').value;
            const heartRate = parseFloat(document.getElementById('heart_rate').value);
            const respiratoryRate = parseFloat(document.getElementById('respiratory_rate').value);
            const temperature = parseFloat(document.getElementById('temperature').value);

            // Validate Blood Pressure
            if (/^\d+\/\d+$/.test(bloodPressure)) {
                const [systolic, diastolic] = bloodPressure.split('/').map(Number);
                if (systolic < 120 && diastolic < 80) {
                    document.getElementById('blood_pressure').style.backgroundColor = 'lightgreen';
                } else if ((systolic >= 120 && systolic < 130) && diastolic < 80) {
                    document.getElementById('blood_pressure').style.backgroundColor = 'yellow';
                } else if ((systolic >= 130 && systolic < 140) || (diastolic >= 80 && diastolic < 90)) {
                    document.getElementById('blood_pressure').style.backgroundColor = 'orange';
                } else if (systolic >= 140 || diastolic >= 90) {
                    document.getElementById('blood_pressure').style.backgroundColor = 'red';
                } else {
                    document.getElementById('blood_pressure').style.backgroundColor = '';
                }
            } else {
                document.getElementById('blood_pressure').style.backgroundColor = '';
            }

            // Validate Heart Rate
            if (!isNaN(heartRate)) {
                if (heartRate >= 60 && heartRate <= 100) {
                    document.getElementById('heart_rate').style.backgroundColor = 'lightgreen';
                } else {
                    document.getElementById('heart_rate').style.backgroundColor = 'red';
                }
            } else {
                document.getElementById('heart_rate').style.backgroundColor = '';
            }

            // Validate Respiratory Rate
            if (!isNaN(respiratoryRate)) {
                if (respiratoryRate >= 12 && respiratoryRate <= 20) {
                    document.getElementById('respiratory_rate').style.backgroundColor = 'lightgreen';
                } else {
                    document.getElementById('respiratory_rate').style.backgroundColor = 'red';
                }
            } else {
                document.getElementById('respiratory_rate').style.backgroundColor = '';
            }

            // Validate Temperature
            if (!isNaN(temperature)) {
                if (temperature >= 36.1 && temperature <= 37.2) {
                    document.getElementById('temperature').style.backgroundColor = 'lightgreen';
                } else {
                    document.getElementById('temperature').style.backgroundColor = 'red';
                }
            } else {
                document.getElementById('temperature').style.backgroundColor = '';
            }
        }

        // Modal functionality
        var modal = document.getElementById("bmi-ranges-modal");
        var btn = document.getElementById("open-modal");
        var span = document.getElementsByClassName("close")[0];

        btn.onclick = function() {
            modal.style.display = "block";
        }

        span.onclick = function() {
            modal.style.display = "none";
        }

        window.onclick = function(event) {
            if (event.target == modal) {
                modal.style.display = "none";
            }
        }

        function calculateBMI() {
            const weight = parseFloat(document.getElementById('weight').value);
            const heightCm = parseFloat(document.getElementById('height').value);
            const bmiInput = document.getElementById('bmi');
            
            if (!isNaN(weight) && !isNaN(heightCm) && heightCm > 0) {
                const heightM = heightCm / 100;
                const bmi = weight / (heightM * heightM);
                bmiInput.value = bmi.toFixed(2);
                updateBMIColor(bmi);
            } else {
                bmiInput.value = '';
                bmiInput.style.backgroundColor = '';
            }
        }

        function updateBMIColor(bmi) {
            const bmiInput = document.getElementById('bmi');
            
            if (bmi < 18.5) {
                bmiInput.style.backgroundColor = 'lightblue';
            } else if (bmi >= 18.5 && bmi <= 24.9) {
                bmiInput.style.backgroundColor = 'lightgreen';
            } else if (bmi >= 25 && bmi <= 29.9) {
                bmiInput.style.backgroundColor = 'yellow';
            } else if (bmi >= 30) {
                bmiInput.style.backgroundColor = 'red';
            } else {
                bmiInput.style.backgroundColor = '';
            }
        }

        //message timeout
        const messagePopup = document.getElementById('message-popup');
        if (messagePopup) {
            messagePopup.style.display = 'block';
            setTimeout(function() {
                messagePopup.style.display = 'none';
            }, 12000);
        }
    </script>
</body>
</html>