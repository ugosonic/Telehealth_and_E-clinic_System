<?php
ob_start(); // Start output buffering
include '../init.php';
include '../config.php';
include '../access_control.php';
include '../sidebar.php';

// Fetch the prescription ID from the URL
$prescription_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Query to fetch the prescription details from the database
$sql = "SELECT p.*, pt.first_name, pt.surname, pt.dob, pt.patient_id
        FROM prescriptions p
        LEFT JOIN patient_db pt ON p.patient_id = pt.patient_id
        WHERE p.id = ?";
$stmt = $conn->prepare($sql);

if ($stmt === false) {
    die("Prepare failed: " . $conn->error);
}

$stmt->bind_param("i", $prescription_id);
$stmt->execute();
$result = $stmt->get_result();

// Check if the prescription exists
if ($result->num_rows === 0) {
    die("Prescription not found.");
}

$prescription = $result->fetch_assoc();
$stmt->close();

// Use explode() to parse the stored data
$medication_ids = explode(',', $prescription['medication_ids']);
$medication_names = explode(',', $prescription['medication_names']);
$medication_dosages = explode(',', $prescription['medication_dosages']);

// Ensure that all arrays have the same length
if (count($medication_ids) !== count($medication_names) || count($medication_names) !== count($medication_dosages)) {
    die("Medication data is corrupted.");
}

// Combine the medication data into a single array
$medications = [];
for ($i = 0; $i < count($medication_ids); $i++) {
    $medications[] = [
        'id' => $medication_ids[$i],
        'name' => $medication_names[$i],
        'dosage' => $medication_dosages[$i]
    ];
}

// Fetch existing medication quantities and statuses
$medication_quantities = [];
$medication_statuses = [];

if (!empty($prescription['medication_quantities'])) {
    $medication_quantities = explode(',', $prescription['medication_quantities']);
}
if (!empty($prescription['medication_statuses'])) {
    $medication_statuses = explode(',', $prescription['medication_statuses']);
}

// Ensure the arrays have the same length
$med_count = count($medications);

if (count($medication_quantities) !== $med_count) {
    $medication_quantities = array_fill(0, $med_count, '0');
}
if (count($medication_statuses) !== $med_count) {
    $medication_statuses = array_fill(0, $med_count, '');
}

// Update the medications array to include quantities and statuses
for ($i = 0; $i < $med_count; $i++) {
    $medications[$i]['quantity'] = $medication_quantities[$i];
    $medications[$i]['status'] = $medication_statuses[$i];
}

// Handle form submission to update prescription status
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $status = 'Pending';
    $all_checked = true;

    $medication_quantities = [];
    $medication_statuses = [];

    foreach ($medications as $medication) {
        $medication_id = $medication['id'];

        // Collect the prescribed status
        $prescribed_status = $_POST['status_' . $medication_id] ?? '';
        if ($prescribed_status === 'Prescribed' || $prescribed_status === 'Partially Prescribed') {
            $status = 'Completed';
        } elseif ($prescribed_status === 'Cancelled') {
            $status = 'Cancelled';
        } else {
            $all_checked = false;
        }

        // Collect the quantity prescribed
        $quantity_prescribed = $_POST['quantity_' . $medication_id] ?? '0';

        $medication_statuses[] = $prescribed_status;
        $medication_quantities[] = $quantity_prescribed;
    }

    if (!$all_checked && $status !== 'Cancelled') {
        $status = 'In Progress';
    }

    // Convert the arrays to comma-separated strings
    $medication_statuses_str = implode(',', $medication_statuses);
    $medication_quantities_str = implode(',', $medication_quantities);

    // Update prescription status and medications data in the database
    $update_sql = "UPDATE prescriptions SET status = ?, medication_statuses = ?, medication_quantities = ? WHERE id = ?";
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param("sssi", $status, $medication_statuses_str, $medication_quantities_str, $prescription_id);
    $update_stmt->execute();
    $update_stmt->close();

    header("Location: prescriptions.php");
    exit();
}

ob_end_flush(); // End output buffering
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Prescription Details</title>
    <link rel="stylesheet" href="../css/prescription.css">
    <style>
        .prescription-details {
            max-width: 800px;
            margin: 20px auto;
            padding: 20px;
            background-color: #f8f9fa;
            border: 1px solid #ddd;
            border-radius: 5px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .prescription-details h2 {
            margin-top: 0;
        }

        .medication-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .medication-table th, .medication-table td {
            border: 1px solid #ddd;
            padding: 8px;
        }

        .medication-table th {
            background-color: #f2f2f2;
            text-align: left;
        }

        .medication-table input[type="number"] {
            width: 60px;
        }

        .status-label {
            display: block;
            margin-bottom: 5px;
        }

        .status-options {
            display: flex;
            flex-direction: column;
        }

        .status-options input[type="radio"] {
            margin-right: 5px;
        }

        .submit-button {
            display: block;
            text-align: center;
        }

        .container {
            height: 200vh;
        }
    </style>
</head>
<body>
    <div class="prescription-details">
        <h2>Prescription Details</h2>
        <p><strong>Patient Name:</strong> <?= htmlspecialchars($prescription['first_name'] . ' ' . $prescription['surname']) ?></p>
        <p><strong>Patient ID:</strong> <?= htmlspecialchars($prescription['patient_id']) ?></p>
        <p><strong>Date of Birth:</strong> <?= htmlspecialchars($prescription['dob']) ?></p>
        <p><strong>Prescribed by:</strong> <?= htmlspecialchars($prescription['doctor_name']) ?></p>
        <p><strong>Department:</strong> <?= htmlspecialchars($prescription['department']) ?></p>
        <p><strong>Date Prescribed:</strong> <?= htmlspecialchars($prescription['date_prescribed']) ?></p>
        <p><strong>Time of Prescription:</strong> <?= htmlspecialchars($prescription['time_prescribed']) ?></p>
        <p><strong>Status:</strong> <?= htmlspecialchars($prescription['status']) ?></p>
        
        <h3>Medications</h3>
        <form method="POST">
            <table class="medication-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Dosage</th>
                        <th>Status</th>
                        <th>Quantity Prescribed</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($medications as $medication): ?>
                        <tr>
                            <td><?= htmlspecialchars($medication['name']) ?></td>
                            <td><?= htmlspecialchars($medication['dosage']) ?></td>
                            <td>
                                <?php if ($prescription['status'] !== 'Completed' && $prescription['status'] !== 'Cancelled'): ?>
                                    <div class="status-options">
                                        <label><input type="radio" name="status_<?= $medication['id'] ?>" value="Prescribed" <?= ($medication['status'] === 'Prescribed') ? 'checked' : '' ?>> Prescribed</label>
                                        <label><input type="radio" name="status_<?= $medication['id'] ?>" value="Out of Stock" <?= ($medication['status'] === 'Out of Stock') ? 'checked' : '' ?>> Out of Stock</label>
                                        <label><input type="radio" name="status_<?= $medication['id'] ?>" value="Partially Prescribed" <?= ($medication['status'] === 'Partially Prescribed') ? 'checked' : '' ?>> Partially Prescribed</label>
                                        <label><input type="radio" name="status_<?= $medication['id'] ?>" value="Cancelled" <?= ($medication['status'] === 'Cancelled') ? 'checked' : '' ?>> Cancelled</label>
                                    </div>
                                <?php else: ?>
                                    <?= htmlspecialchars($medication['status']) ?>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($prescription['status'] !== 'Completed' && $prescription['status'] !== 'Cancelled'): ?>
                                    <input type="number" name="quantity_<?= $medication['id'] ?>" min="0" value="<?= htmlspecialchars($medication['quantity']) ?>">
                                <?php else: ?>
                                    <?= htmlspecialchars($medication['quantity']) ?>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php
            // Determine whether to show the submit button
            $show_submit_button = false;
            if ($prescription['status'] === 'Partially Prescribed' || $prescription['status'] === 'In Progress' || $prescription['status'] === 'Pending') {
                $show_submit_button = true;
            }
            ?>
            <?php if ($show_submit_button): ?>
                <div class="submit-button">
                    <button type="submit">Submit</button>
                </div>
            <?php endif; ?>
        </form>
    </div>
</body>
</html>

<?php
$conn->close();
?>
