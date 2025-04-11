<?php
session_start(); // Start session to access session variables
include '../init.php'; 
include '../config.php';
// Include the sidebar only if the user is logged in
if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
    include '../sidebar.php';
}
    ?>
    <!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Patient Registration</title>
    <link rel="stylesheet" href="patient.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script type="text/javascript" src="country2.js"></script>
    <script type="text/javascript" src="profilecamera.js"></script>
    <script type="text/javascript" src="script.js"></script>
    <style>
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
    <script>
        function generatePatientID() {
            var patientID = Math.floor(Math.random() * 9000000) + 1000000; // Generates a 7-digit random number
            document.getElementById("patient_id").value = patientID;
            document.getElementById("hidden_patient_id").value = patientID;
        }
    </script>
</head>
<body onload="generatePatientID()">
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
    <div class="container">
    <h1 class="dashboard-header">Patient Registration</h1>
        <form action="submit_patient.php" method="POST" enctype="multipart/form-data">
        
            <h2>Personal Details</h2>
            <div class="form-group">
            <label for="patient_id">Patient ID:</label>
                <input type="text" id="patient_id" name="patient_id" readonly> <!-- Patient ID field -->
                <input type="hidden" name="hidden_patient_id" id="hidden_patient_id" value=""> <!-- Hidden field to store the patient ID -->
            </div>
            <div class="form-group">
                <label for="first_name">First Name:</label>
                <input type="text" id="first_name" name="first_name" required>
                <label for="middle_name">Middle Name:</label>
                <input type="text" id="middle_name" name="middle_name">
                <label for="surname">Surname:</label>
                <input type="text" id="surname" name="surname" required>
            </div>
            <div class="form-group">
    <label for="dob">Date of Birth:</label>
    <input type="date" id="dob" name="dob" required onchange="calculateAge()">
    <label for="age">Age:</label>
    <input type="text" id="age" name="age" readonly>
    <label for="gender">Gender:</label>
    <select id="gender" name="gender" required>
        <option value="">Select</option>
        <option value="Male">Male</option>
        <option value="Female">Female</option>
    </select>

            </div>
            <div class="form-group">
                <label for="marital_status">Marital Status:</label>
                <select id="marital_status" name="marital_status" required>
                    <option value="">Select</option>
                    <option value="Single">Single</option>
                    <option value="Married">Married</option>
                    <option value="Divorced">Divorced</option>
                    <option value="Widowed">Widowed</option>
                </select>
                <label for="education_level">Education Level:</label>
                <select id="education_level" name="education_level" required>
                    <option value="">Select</option>
                    <option value="None">None</option>
                    <option value="Primary">Primary</option>
                    <option value="Secondary">Secondary</option>
                    <option value="Tertiary">Tertiary</option>
                </select>
                <label for="email">Email Id:</label>
                <input type="email" id="email" name="email" required>
            </div>
            <div class="form-group">
                <label for="country">Citizen:</label>
                <select id="country" name="country" onchange="print_state('state', this.selectedIndex);" required>
                    <option value="">Choose a country</option>

                </select>
            </div>
            <div class="form-group">
                <label for="state">Select State/Province:</label>
                <select id="state" name="state" required>
                    <option value="">Choose a state/province</option>
                </select>
                <script>
                    // Populate country dropdown on page load
                    print_country("country");
                </script>
            </div>
            <h2>Home Address</h2>
            <div class="form-group">
                <label for="address">Address:</label>
                <input type="text" id="address" name="address" required>
                <label for="country">Country:</label>
                <select id="country" name="country" required>
                    <!-- country names and country code -->
    <option value="">Select Country</option>
    <option value="AF">Afghanistan</option>
    <option value="AX">Åland Islands</option>
    <option value="AL">Albania</option>
    <option value="DZ">Algeria</option>
    <option value="AS">American Samoa</option>
    <option value="AD">Andorra</option>
    <option value="AO">Angola</option>
    <option value="AI">Anguilla</option>
    <option value="AQ">Antarctica</option>
    <option value="AG">Antigua and Barbuda</option>
    <option value="AR">Argentina</option>
    <option value="AM">Armenia</option>
    <option value="AW">Aruba</option>
    <option value="AU">Australia</option>
    <option value="AT">Austria</option>
    <option value="AZ">Azerbaijan</option>
    <option value="BS">Bahamas</option>
    <option value="BH">Bahrain</option>
    <option value="BD">Bangladesh</option>
    <option value="BB">Barbados</option>
    <option value="BY">Belarus</option>
    <option value="BE">Belgium</option>
    <option value="BZ">Belize</option>
    <option value="BJ">Benin</option>
    <option value="BM">Bermuda</option>
    <option value="BT">Bhutan</option>
    <option value="BO">Bolivia (Plurinational State of)</option>
    <option value="BA">Bosnia and Herzegovina</option>
    <option value="BW">Botswana</option>
    <option value="BV">Bouvet Island</option>
    <option value="BR">Brazil</option>
    <option value="IO">British Indian Ocean Territory</option>
    <option value="BN">Brunei Darussalam</option>
    <option value="BG">Bulgaria</option>
    <option value="BF">Burkina Faso</option>
    <option value="BI">Burundi</option>
    <option value="CV">Cabo Verde</option>
    <option value="KH">Cambodia</option>
    <option value="CM">Cameroon</option>
    <option value="CA">Canada</option>
    <option value="BQ">Caribbean Netherlands</option>
    <option value="KY">Cayman Islands</option>
    <option value="CF">Central African Republic</option>
    <option value="TD">Chad</option>
    <option value="CL">Chile</option>
    <option value="CN">China</option>
    <option value="CX">Christmas Island</option>
    <option value="CC">Cocos (Keeling) Islands</option>
    <option value="CO">Colombia</option>
    <option value="KM">Comoros</option>
    <option value="CG">Congo</option>
    <option value="CD">Congo, Democratic Republic of the</option>
    <option value="CK">Cook Islands</option>
    <option value="CR">Costa Rica</option>
    <option value="HR">Croatia</option>
    <option value="CU">Cuba</option>
    <option value="CW">Curaçao</option>
    <option value="CY">Cyprus</option>
    <option value="CZ">Czech Republic</option>
    <option value="CI">Côte d'Ivoire</option>
    <option value="DK">Denmark</option>
    <option value="DJ">Djibouti</option>
    <option value="DM">Dominica</option>
    <option value="DO">Dominican Republic</option>
    <option value="EC">Ecuador</option>
    <option value="EG">Egypt</option>
    <option value="SV">El Salvador</option>
    <option value="GQ">Equatorial Guinea</option>
    <option value="ER">Eritrea</option>
    <option value="EE">Estonia</option>
    <option value="SZ">Eswatini (Swaziland)</option>
    <option value="ET">Ethiopia</option>
    <option value="FK">Falkland Islands (Malvinas)</option>
    <option value="FO">Faroe Islands</option>
    <option value="FJ">Fiji</option>
    <option value="FI">Finland</option>
    <option value="FR">France</option>
    <option value="GF">French Guiana</option>
    <option value="PF">French Polynesia</option>
    <option value="TF">French Southern Territories</option>
    <option value="GA">Gabon</option>
    <option value="GM">Gambia</option>
    <option value="GE">Georgia</option>
    <option value="DE">Germany</option>
    <option value="GH">Ghana</option>
    <option value="GI">Gibraltar</option>
    <option value="GR">Greece</option>
    <option value="GL">Greenland</option>
    <option value="GD">Grenada</option>
    <option value="GP">Guadeloupe</option>
    <option value="GU">Guam</option>
    <option value="GT">Guatemala</option>
    <option value="GG">Guernsey</option>
    <option value="GN">Guinea</option>
    <option value="GW">Guinea-Bissau</option>
    <option value="GY">Guyana</option>
    <option value="HT">Haiti</option>
    <option value="HM">Heard Island and Mcdonald Islands</option>
    <option value="HN">Honduras</option>
    <option value="HK">Hong Kong</option>
    <option value="HU">Hungary</option>
    <option value="IS">Iceland</option>
    <option value="IN">India</option>
    <option value="ID">Indonesia</option>
    <option value="IR">Iran</option>
    <option value="IQ">Iraq</option>
    <option value="IE">Ireland</option>
    <option value="IM">Isle of Man</option>
    <option value="IL">Israel</option>
    <option value="IT">Italy</option>
    <option value="JM">Jamaica</option>
    <option value="JP">Japan</option>
    <option value="JE">Jersey</option>
    <option value="JO">Jordan</option>
    <option value="KZ">Kazakhstan</option>
    <option value="KE">Kenya</option>
    <option value="KI">Kiribati</option>
    <option value="KP">Korea, North</option>
    <option value="KR">Korea, South</option>
    <option value="XK">Kosovo</option>
    <option value="KW">Kuwait</option>
    <option value="KG">Kyrgyzstan</option>
    <option value="LA">Lao People's Democratic Republic</option>
    <option value="LV">Latvia</option>
    <option value="LB">Lebanon</option>
    <option value="LS">Lesotho</option>
    <option value="LR">Liberia</option>
    <option value="LY">Libya</option>
    <option value="LI">Liechtenstein</option>
    <option value="LT">Lithuania</option>
    <option value="LU">Luxembourg</option>
    <option value="MO">Macao</option>
    <option value="MK">Macedonia North</option>
    <option value="MG">Madagascar</option>
    <option value="MW">Malawi</option>
    <option value="MY">Malaysia</option>
    <option value="MV">Maldives</option>
    <option value="ML">Mali</option>
    <option value="MT">Malta</option>
    <option value="MH">Marshall Islands</option>
    <option value="MQ">Martinique</option>
    <option value="MR">Mauritania</option>
    <option value="MU">Mauritius</option>
    <option value="YT">Mayotte</option>
    <option value="MX">Mexico</option>
    <option value="FM">Micronesia</option>
    <option value="MD">Moldova</option>
    <option value="MC">Monaco</option>
    <option value="MN">Mongolia</option>
    <option value="ME">Montenegro</option>
    <option value="MS">Montserrat</option>
    <option value="MA">Morocco</option>
    <option value="MZ">Mozambique</option>
    <option value="MM">Myanmar (Burma)</option>
    <option value="NA">Namibia</option>
    <option value="NR">Nauru</option>
    <option value="NP">Nepal</option>
    <option value="NL">Netherlands</option>
    <option value="AN">Netherlands Antilles</option>
    <option value="NC">New Caledonia</option>
    <option value="NZ">New Zealand</option>
    <option value="NI">Nicaragua</option>
    <option value="NE">Niger</option>
    <option value="NG">Nigeria</option>
    <option value="NU">Niue</option>
    <option value="NF">Norfolk Island</option>
    <option value="MP">Northern Mariana Islands</option>
    <option value="NO">Norway</option>
    <option value="OM">Oman</option>
    <option value="PK">Pakistan</option>
    <option value="PW">Palau</option>
    <option value="PS">Palestine</option>
    <option value="PA">Panama</option>
    <option value="PG">Papua New Guinea</option>
    <option value="PY">Paraguay</option>
    <option value="PE">Peru</option>
    <option value="PH">Philippines</option>
    <option value="PN">Pitcairn Islands</option>
    <option value="PL">Poland</option>
    <option value="PT">Portugal</option>
    <option value="PR">Puerto Rico</option>
    <option value="QA">Qatar</option>
    <option value="RE">Reunion</option>
    <option value="RO">Romania</option>
    <option value="RU">Russian Federation</option>
    <option value="RW">Rwanda</option>
    <option value="BL">Saint Barthelemy</option>
    <option value="SH">Saint Helena</option>
    <option value="KN">Saint Kitts and Nevis</option>
    <option value="LC">Saint Lucia</option>
    <option value="MF">Saint Martin</option>
    <option value="PM">Saint Pierre and Miquelon</option>
    <option value="VC">Saint Vincent and the Grenadines</option>
    <option value="WS">Samoa</option>
    <option value="SM">San Marino</option>
    <option value="ST">Sao Tome and Principe</option>
    <option value="SA">Saudi Arabia</option>
    <option value="SN">Senegal</option>
    <option value="RS">Serbia</option>
    <option value="CS">Serbia and Montenegro</option>
    <option value="SC">Seychelles</option>
    <option value="SL">Sierra Leone</option>
    <option value="SG">Singapore</option>
    <option value="SX">Sint Maarten</option>
    <option value="SK">Slovakia</option>
    <option value="SI">Slovenia</option>
    <option value="SB">Solomon Islands</option>
    <option value="SO">Somalia</option>
    <option value="ZA">South Africa</option>
    <option value="GS">South Georgia and the South Sandwich Islands</option>
    <option value="SS">South Sudan</option>
    <option value="ES">Spain</option>
    <option value="LK">Sri Lanka</option>
    <option value="SD">Sudan</option>
    <option value="SR">Suriname</option>
    <option value="SJ">Svalbard and Jan Mayen</option>
    <option value="SE">Sweden</option>
    <option value="CH">Switzerland</option>
    <option value="SY">Syria</option>
    <option value="TW">Taiwan</option>
    <option value="TJ">Tajikistan</option>
    <option value="TZ">Tanzania</option>
    <option value="TH">Thailand</option>
    <option value="TL">Timor-Leste</option>
    <option value="TG">Togo</option>
    <option value="TK">Tokelau</option>
    <option value="TO">Tonga</option>
    <option value="TT">Trinidad and Tobago</option>
    <option value="TN">Tunisia</option>
    <option value="TR">Turkey (Türkiye)</option>
    <option value="TM">Turkmenistan</option>
    <option value="TC">Turks and Caicos Islands</option>
    <option value="TV">Tuvalu</option>
    <option value="UM">U.S. Outlying Islands</option>
    <option value="UG">Uganda</option>
    <option value="UA">Ukraine</option>
    <option value="AE">United Arab Emirates</option>
    <option value="GB">United Kingdom</option>
    <option value="US">United States</option>
    <option value="UY">Uruguay</option>
    <option value="UZ">Uzbekistan</option>
    <option value="VU">Vanuatu</option>
    <option value="VA">Vatican City Holy See</option>
    <option value="VE">Venezuela</option>
    <option value="VN">Vietnam</option>
    <option value="VG">Virgin Islands, British</option>
    <option value="VI">Virgin Islands, U.S</option>
    <option value="WF">Wallis and Futuna</option>
    <option value="EH">Western Sahara</option>
    <option value="YE">Yemen</option>
    <option value="ZM">Zambia</option>
    <option value="ZW">Zimbabwe</option>
</select>
                    <!-- Add all countries here -->
                </select>
</div>
            <div class="form-group">
                <label for="telephone">Telephone:</label>
                <input type="text" id="telephone" name="telephone">
            </div>
            <h2>Next of Kin</h2>
            <div class="form-group">
                <label for="same_address_as_patient">Same address as patient:</label>
                <input type="checkbox" id="same_address_as_patient" name="same_address_as_patient">
                <label for="next_of_kin_name">Next of Kin Name:</label>
                <input type="text" id="next_of_kin_name" name="next_of_kin_name">
                <label for="next_of_kin_relation">Relation:</label>
                <select id="next_of_kin_relation" name="next_of_kin_relation" required>
                    <option value="">Select</option>
                    <option value="Parent">Parent</option>
                    <option value="Sibling">Sibling</option>
                    <option value="Spouse">Spouse</option>
                    <option value="Friend">Friend</option>
                </select>
                <label for="next_of_kin_telephone">Telephone:</label>
                <input type="text" id="next_of_kin_telephone" name="next_of_kin_telephone">
                <label for="next_of_kin_city">City:</label>
                <input type="text" id="next_of_kin_city" name="next_of_kin_city">
            </div>
            <h2>Payer Information</h2>
            <div class="form-group">
                <label for="payer">Payer:</label>
                <select id="payer" name="payer" required>
                    <option value="">Select</option>
                    <option value="Private Cash">Private Cash</option>
                    <option value="Insurance">Insurance</option>
                    <option value="Employer">Employer</option>
                </select>
                <label for="sponsor">Sponsor:</label>
                <select id="sponsor" name="sponsor" required>
                    <option value="">Select</option>
                    <option value="Private Cash">Private Cash</option>
                    <option value="Insurance">Insurance</option>
                    <option value="Employer">Employer</option>
                </select>
            </div>
            <h2>Login Details</h2>
            <div class="form-group">
                <label for="username">Username:</label>
                <input type="text" id="username" name="username" required>
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required>
            </div>
            <h2>Previous GP</h2>
            <div class="form-group">
                <label for="previous_address_uk">Your previous address in UK:</label>
                <input type="text" id="previous_address_uk" name="previous_address_uk">
                <label for="previous_gp_practice">Name of previous GP practice:</label>
                <input type="text" id="previous_gp_practice" name="previous_gp_practice">
                <label for="address_previous_gp">Address of previous GP practice:</label>
                <input type="text" id="address_previous_gp" name="address_previous_gp">
            </div>
            <h2>Armed Forces Information</h2>
            <div class="form-group">
                <label for="enlisted_address">Address before enlisting:</label>
                <input type="text" id="enlisted_address" name="enlisted_address">
                <label for="enlistment_date">Enlistment date:</label>
                <input type="date" id="enlistment_date" name="enlistment_date">
                <label for="discharge_date">Discharge date:</label>
                <input type="date" id="discharge_date" name="discharge_date">
            </div>
            <h2>Profile Picture</h2>
    <div class="form-group">
        <label for="profile_pic">Profile Picture:</label>
        <input type="file" id="profile_pic" name="profile_pic" accept="image/*">
    
                <input type="file" id="profile_pic" name="profile_pic" accept="image/*" style="display: none;">
                <div id="cameraOptions" style="display: none;">
                    <video id="video" width="300" height="300" autoplay></video>
                    <button type="button" onclick="takeSnapshot()">Take Snapshot</button>
                    <button type="button" onclick="cancelSnapshot()">Cancel</button>
                </div>
                <canvas id="canvas" width="300" height="300" style="display: none;"></canvas>
                <input type="hidden" id="profile_pic_data" name="profile_pic_data">
                <div id="snapshotTaken" style="display: none;">
                    <p>Snapshot taken.</p>
                    <button type="button" onclick="discardSnapshot()">Discard</button>
                </div>
            </div>
            <h2>Allergy</h2>
            <div class="form-group">
                <label for="allergy">Allergy:</label>
                <input type="text" id="allergy" name="allergy" style="border: 1px solid red;">
            </div>
            <h2>Disability</h2>
            <div class="form-group">
                <label for="disability">Disability:</label>
                <select id="disability" name="disability">
                    <option value="">Select</option>
                    <option value="Yes">Yes</option>
                    <option value="No">No</option>
                </select>
                <input type="text" id="disability_specify" name="disability_specify" placeholder="Specify Disability" style="display: none;">
            </div>
            <h2>ID Verification</h2>
            <div class="form-group">
                <label for="id_type">Type of Identification ID:</label>
                <select id="id_type" name="id_type">
                    <option value="">Select</option>
                    <option value="Driving Licence">Driving Licence</option>
                    <option value="International Passport">International Passport</option>
                    <option value="Residence Card or Permit">Residence Card or Permit</option>
                </select>
            </div>
            <div class="form-group" id="id_upload" style="display: none;">
                <label for="id">Upload ID:</label>
                <input type="file" id="id" name="id" accept="image/*" onchange="validateFileSize(this, 25)">
            </div>
            <div class="form-group" id="back_id_upload" style="display: none;">
                <label for="back_id">Upload Back ID (if applicable):</label>
                <input type="file" id="back_id" name="back_id" accept="image/*" onchange="validateFileSize(this, 25)">
            </div>
            <h2>Patient Declaration</h2>
            <div class="form-group">
                <label for="declaration_confirm" class="checkbox-container">I declare that the information I give on this form is correct and complete. I understand that if it is not correct, appropriate action may be taken against me.
                    <input type="checkbox" id="declaration_confirm" name="declaration_confirm" required>
                    <span class="checkmark"></span>
                </label>
            </div>
            <input type="submit" value="Register">
        </form>
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
