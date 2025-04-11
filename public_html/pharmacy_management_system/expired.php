<?php
include '../init.php';
include '../config.php';
include '../access_control.php';
// Fetch user details
$username = '';
$email = '';
if (isset($_SESSION['username'])) {
    $stmt = $con->prepare('SELECT username, email FROM users WHERE username = ?');
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

// Fetch expired drugs
$current_date = date('Y-m-d');
$expired_drugs = $con->query("SELECT * FROM inventory WHERE expiry_date < '$current_date'");

?>

<!DOCTYPE html>
<html>
<head>
    <title>Expired Drugs</title>
    <link rel="stylesheet" type="text/css" href="styles.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap">
    <script src="sidebar.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body id="body-pd">
    <header class="header" id="header">
        <div class="header_toggle"> <i class='bx bx-menu' id="header-toggle"></i> </div>
        <div class="header_img"> <img src="https://i.imgur.com/hczKIze.jpg" alt=""> </div>
    </header>
    <div class="l-navbar" id="nav-bar">
        <nav class="nav">
            <div> <a href="#" class="nav_logo"> <i class='bx bx-layer nav_logo-icon'></i> <span class="nav_logo-name">BBBootstrap</span> </a>
                <div class="nav_list">
                    <a href="dashboard.php" class="nav_link"> <i class='fas fa-tachometer-alt'></i> <span class="nav_name">Dashboard</span> </a>
                    <a href="inventory.php" class="nav_link"> <i class='fas fa-pills'></i> <span class="nav_name">Inventory</span> </a>
                    <a href="doctors_orders.php" class="nav_link"> <i class='fas fa-stethoscope'></i> <span class="nav_name">Doctor's Orders</span> </a>
                    <a href="expired.php" class="nav_link active"> <i class='fas fa-exclamation-triangle'></i> <span class="nav_name">Expired</span> </a>
                    <a href="out_of_orders.php" class="nav_link"> <i class='fas fa-ban'></i> <span class="nav_name">Out of Orders</span> </a>
                    <a href="sales_report.php" class="nav_link"> <i class='fas fa-chart-line'></i> <span class="nav_name">Sales Report</span> </a>
                </div>
            </div> 
            <a href="#" class="nav_link"> <i class='bx bx-log-out nav_icon'></i> <span class="nav_name">SignOut</span> </a>
        </nav>
    </div>
    <section class="certification">
    <div class="content">
        <h2>Expired Drugs</h2>
        <div class="table-container">
            <table id="expiredDrugsTable" class="table">
                <thead>
                    <tr>
                        <th>Drug ID</th>
                        <th>Name</th>
                        <th>Category</th>
                        <th>Groups</th>
                        <th>Form</th>
                        <th>Batch Number</th>
                        <th>Expiry Date</th>
                        <th>Price per Capsule (£)</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $expired_drugs->fetch_assoc()): ?>
                        <tr data-id="<?= $row['drug_id'] ?>">
                            <td><?= $row['drug_id'] ?></td>
                            <td><?= $row['name'] ?></td>
                            <td><?= $row['category'] ?></td>
                            <td><?= $row['groups'] ?></td>
                            <td><?= $row['form'] ?></td>
                            <td><?= $row['batch_number'] ?></td>
                            <td><?= $row['expiry_date'] ?></td>
                            <td><?= $row['price_per_capsule'] ?></td>
                            <td>
                                <button class="action-btn view" onclick="viewDrug(<?= $row['drug_id'] ?>)">View</button>
                                <button class="action-btn update" onclick="editDrug(<?= $row['drug_id'] ?>)">Update</button>
                                <button class="action-btn delete" onclick="deleteDrug(<?= $row['drug_id'] ?>)">Delete</button>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
        <div id="message" class="message hidden">
            <span class="close-btn" id="closeMessageBtn">&times;</span>
            <p id="messageText"></p>
        </div>
    </div>
                    </section>
    <!-- View Modal -->
    <div id="viewModal" class="modal hidden">
        <div class="modal-content">
            <span class="close-btn" id="closeViewModalBtn">&times;</span>
            <h2>View Drug</h2>
            <div id="viewDrugContent"></div>
        </div>
    </div>

    <!-- Update Modal -->
    <div id="updateModal" class="modal hidden">
        <div class="modal-content">
            <span class="close-btn" id="closeUpdateModalBtn">&times;</span>
            <h2>Update Drug</h2>
            <form id="updateDrugForm">
                <!-- Form fields for update -->
            </form>
        </div>
    </div>

<script>
document.getElementById('closeMessageBtn').addEventListener('click', function() {
    document.getElementById('message').classList.add('hidden');
});

function viewDrug(drugId) {
    // Fetch drug data and display in modal
    fetch(`get_drug.php?id=${drugId}`)
        .then(response => response.json())
        .then(data => {
            document.getElementById('viewDrugContent').innerHTML = `
                <p><strong>Drug ID:</strong> ${data.drug_id}</p>
                <p><strong>Name:</strong> ${data.name}</p>
                <p><strong>Category:</strong> ${data.category}</p>
                <p><strong>Groups:</strong> ${data.groups}</p>
                <p><strong>Form:</strong> ${data.form}</p>
                <p><strong>Batch Number:</strong> ${data.batch_number}</p>
                <p><strong>Expiry Date:</strong> ${data.expiry_date}</p>
                <p><strong>Price per Capsule (£):</strong> ${data.price_per_capsule}</p>
                <p><strong>Special Note:</strong> ${data.special_note}</p>
                <p><strong>Prescription Note:</strong> ${data.prescription_note}</p>
            `;
            document.getElementById('viewModal').classList.remove('hidden');
        });
}

function editDrug(drugId) {
    // Fetch drug data and display in update form
    fetch(`get_drug.php?id=${drugId}`)
        .then(response => response.json())
        .then(data => {
            document.getElementById('updateDrugForm').innerHTML = `
                <input type="hidden" name="drug_id" value="${data.drug_id}">
                <label for="name">Name of Medication:</label>
                <input type="text" id="name" name="name" value="${data.name}" required><br>
                <label for="category">Category of Drugs:</label>
                <select id="category" name="category[]" multiple required>
                    <option value="Depressants" ${data.category.includes('Depressants') ? 'selected' : ''}>Depressants</option>
                    <option value="Hallucinogens" ${data.category.includes('Hallucinogens') ? 'selected' : ''}>Hallucinogens</option>
                    <option value="Stimulants" ${data.category.includes('Stimulants') ? 'selected' : ''}>Stimulants</option>
                </select><br>
                <!-- Add other form fields similarly -->
                <input type="submit" value="Update">
            `;
            document.getElementById('updateModal').classList.remove('hidden');
        });
}

function deleteDrug(drugId) {
    if (confirm('Are you sure you want to delete this drug?')) {
        fetch(`delete_drug.php?id=${drugId}`, { method: 'DELETE' })
            .then(response => response.text())
            .then(data => {
                if (data.includes('successfully')) {
                    document.querySelector(`tr[data-id='${drugId}']`).remove();
                    showMessage('Drug deleted successfully');
                } else {
                    showMessage('Error deleting drug');
                }
            });
    }
}

function showMessage(message) {
    document.getElementById('messageText').innerText = message;
    document.getElementById('message').classList.remove('hidden');
    setTimeout(() => { document.getElementById('message').classList.add('hidden'); }, 3000);
}

document.getElementById('closeViewModalBtn').addEventListener('click', function() {
    document.getElementById('viewModal').classList.add('hidden');
});

document.getElementById('closeUpdateModalBtn').addEventListener('click', function() {
    document.getElementById('updateModal').classList.add('hidden');
});

document.getElementById('updateDrugForm').addEventListener('submit', function(e) {
    e.preventDefault();

    const formData = new FormData(this);
    fetch('update_drug.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.text())
    .then(data => {
        if (data.includes('successfully')) {
            showMessage('Drug updated successfully');
            document.getElementById('updateModal').classList.add('hidden');
            // Optionally, update the table row with new data
        } else {
            showMessage('Error updating drug');
        }
    });
});
</script>
</body>
</html>
