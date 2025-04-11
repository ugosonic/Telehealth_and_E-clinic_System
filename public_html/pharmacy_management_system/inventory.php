<?php
include '../init.php';
include '../config.php';
include '../access_control.php';





// Fetch search and filter criteria
$search_query = "";
$filter_query = "";

if ($_SERVER["REQUEST_METHOD"] == "GET") {
    if (isset($_GET['search']) && isset($_GET['search_type'])) {
        $search = $conn->real_escape_string($_GET['search']);
        $search_type = $conn->real_escape_string($_GET['search_type']);
        $search_query = " WHERE $search_type LIKE '%$search%'";
    }

    if (isset($_GET['filter_groups']) || isset($_GET['filter_forms']) || isset($_GET['filter_categories'])) {
        $filter_groups = isset($_GET['filter_groups']) ? $_GET['filter_groups'] : [];
        $filter_forms = isset($_GET['filter_forms']) ? $_GET['filter_forms'] : [];
        $filter_categories = isset($_GET['filter_categories']) ? $_GET['filter_categories'] : [];

        $filters = [];
        if (!empty($filter_groups)) {
            $group_filter = implode("', '", $filter_groups);
            $filters[] = "groups IN ('$group_filter')";
        }
        if (!empty($filter_forms)) {
            $form_filter = implode("', '", $filter_forms);
            $filters[] = "form IN ('$form_filter')";
        }
        if (!empty($filter_categories)) {
            $category_filter = implode("', '", $filter_categories);
            $filters[] = "category IN ('$category_filter')";
        }

        if (!empty($filters)) {
            $filter_query = " WHERE " . implode(" AND ", $filters);
        }
    }
}

// Fetch inventory data
//$inventory_query = "SELECT * FROM inventory" . $search_query . $filter_query;
//$inventory_data = $conn->query($inventory_query);

// Add these lines to handle pagination
$query_params = $_GET;
unset($query_params['page']);
$query_string = http_build_query($query_params);
$limit = 20; // Number of records per page
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? intval($_GET['page']) : 1;
$offset = ($page - 1) * $limit;

// Initialize an array to hold conditions
$conditions = [];

if (!empty($search_query)) {
    $conditions[] = substr($search_query, 6); // Remove ' WHERE' from $search_query
}

if (!empty($filter_query)) {
    $conditions[] = substr($filter_query, 6); // Remove ' WHERE' from $filter_query
}

$where_clause = '';
if (!empty($conditions)) {
    $where_clause = ' WHERE ' . implode(' AND ', $conditions);
}

// Use $where_clause in your queries
$inventory_query = "SELECT * FROM inventory" . $where_clause . " LIMIT $limit OFFSET $offset";
$inventory_data = $conn->query($inventory_query);

// For counting total rows
$count_query = "SELECT COUNT(*) as total FROM inventory" . $where_clause;
$count_result = $conn->query($count_query);
$total_rows = $count_result->fetch_assoc()['total'];
$total_pages = ceil($total_rows / $limit);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Medication Inventory Input</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Nunito', sans-serif;
        }
        .sidebar-icon {
    position: fixed;
    top: 0;
    left: 0;
    width: 100px;
    height: 100%;
    overflow-y: auto;
    background-color: #f8f9fa;
    z-index: 1; /* Lower z-index */
}

.navbar {
    position: fixed;
    top: 0;
    left: 100px; /* Start after the sidebar */
    width: calc(100% - 100px);
    z-index: 1000; /* Higher z-index */
}



        .navbar-brand {
            font-weight: bold;
        }

        .nav-link.active {
            font-weight: bold;
        }
        .main-content {
    margin-top: 56px; /* Height of the navbar */
    margin-left: 100px; /* Width of the sidebar */
    padding: 20px;
}

        .table-container {
            margin-top: 20px;
            overflow-x: auto;
        }
        .table th, .table td {
    word-wrap: break-word;
    max-width: 150px; /* Adjust as needed */
    overflow: hidden;
    text-overflow: ellipsis;
}

        .table th {
            background-color: #343a40;
            color: #fff;
        }

        .btn-primary {
            background-color: #007bff;
            border: none;
        }

        .btn-primary:hover {
            background-color: #0056b3;
        }

        .modal-header {
            background-color: #007bff;
            color: #fff;
        }

        .form-control:focus {
            box-shadow: none;
        }

        .search-filter-container {
            margin-top: 20px;
        }

        .filter-dropdown {
            display: none;
        }
       


        @media (max-width: 576px) {
            .search-filter-container {
                flex-direction: column;
            }

            .search-section,
            .filter-section {
                width: 100%;
            }
        }
    </style>
</head>
<body>

    <!-- Navbar Start -->
    <div class="main-content">
        <div class="container-fluid">
        <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
            <a class="navbar-brand" href="#">My Clinic</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a href="dashboard.php" class="nav-link"><i class='fas fa-tachometer-alt'></i> Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a href="inventory.php" class="nav-link active"><i class='fas fa-pills'></i> Inventory</a>
                    </li>
                    <li class="nav-item">
                        <a href="../patient/prescriptions.php" class="nav-link"><i class='fas fa-stethoscope'></i> Doctor's Orders</a>
                    </li>
                    <li class="nav-item">
                        <a href="expired.php" class="nav-link"><i class='fas fa-exclamation-triangle'></i> Expired</a>
                    </li>
                   
                    </li>
                </ul>
            </div>
        </div>
    
    </nav>
    <!-- Navbar End -->

    <div class="container">
        <h1 class="mt-4">Medication Inventory</h1>

        <!-- Search and Filter Section -->
        <div class="search-filter-container row align-items-center">
            <div class="search-section col-md-6">
                <h3>Search Inventory</h3>
                <form class="search-form" method="GET" action="">
                    <div class="input-group mb-3">
                        <input type="text" name="search" class="form-control" placeholder="Search...">
                        <button class="btn btn-primary" type="submit">Search</button>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="search_type" id="searchDrugID" value="drug_id" checked>
                        <label class="form-check-label" for="searchDrugID">Drug ID</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="search_type" id="searchName" value="name">
                        <label class="form-check-label" for="searchName">Name of Drug</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="search_type" id="searchBatch" value="batch_number">
                        <label class="form-check-label" for="searchBatch">Batch Number</label>
                    </div>
                </form>
            </div>

            <div class="filter-section col-md-6">
                <h3>Filter Inventory</h3>
                <form class="filter-form" onsubmit="filterTable(event)">
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="filter_type" id="filterCategory" value="category" onclick="showDropdown('filter_category')">
                            <label class="form-check-label" for="filterCategory">Category of Drugs</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="filter_type" id="filterGroups" value="groups" onclick="showDropdown('filter_groups')">
                            <label class="form-check-label" for="filterGroups">Groups of Drugs</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="filter_type" id="filterForms" value="form" onclick="showDropdown('filter_forms')">
                            <label class="form-check-label" for="filterForms">Forms of Drugs</label>
                        </div>
                    </div>
                    <select id="filter_category" name="filter_category[]" multiple class="form-select filter-dropdown mb-3">
                        <option value="Depressants">Depressants</option>
                        <option value="Hallucinogens">Hallucinogens</option>
                        <option value="Stimulants">Stimulants</option>
                    </select>
                    <select id="filter_groups" name="filter_groups[]" multiple class="form-select filter-dropdown mb-3">
                    <option value="Analgesics">Analgesics</option>
                            <option value="Anesthetics">Anesthetics</option>
                            <option value="Antibiotics">Antibiotics</option>
                            <option value="Antidepressants">Antidepressants</option>
                            <option value="Antidiabetics">Antidiabetics</option>
                            <option value="Antihypertensives">Antihypertensives</option>
                            <option value="Antipsychotics">Antipsychotics</option>
                            <option value="Antivirals">Antivirals</option>
                            <option value="Benzodiazepines">Benzodiazepines</option>
                            <option value="Beta-blockers">Beta-blockers</option>
                            <option value="Calcium Channel Blockers">Calcium Channel Blockers</option>
                            <option value="Corticosteroids">Corticosteroids</option>
                            <option value="Diuretics">Diuretics</option>
                            <option value="Immunosuppressants">Immunosuppressants</option>
                            <option value="Lipid-lowering Agents">Lipid-lowering Agents</option>
                            <option value="NSAIDs">NSAIDs</option>
                            <option value="Opioids">Opioids</option>
                            <option value="Proton Pump Inhibitors">Proton Pump Inhibitors</option>
                            <option value="Statins">Statins</option>
                            <option value="Thyroid Hormones">Thyroid Hormones</option>
                        <!-- ... -->
                    </select>
                    <select id="filter_forms" name="filter_forms[]" multiple class="form-select filter-dropdown mb-3">
                        <option value="Tablets">Tablets</option>
                        <option value="Liquids">Liquids</option>
                        <option value="Inhalers">Inhalers</option>
                        <option value="Topicals">Topicals</option>
                        <option value="Suppositories">Suppositories</option>
                        <option value="Patches">Patches</option>
                        <option value="Injections">Injections</option>
                        <option value="Implants">Implants</option>
                    </select>
                    <button type="submit" class="btn btn-primary">Filter</button>
                </form>
            </div>
        </div>

        <section class="certification mt-4">
            <button id="addNewDrugBtn" class="btn btn-success mb-3">Add New Drug</button>
            <div class="table-container">
                <table id="medicationTable" class="table table-striped table-bordered">
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
                            <th>Capsules Remaining</th>
                            <th>Image</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $inventory_data->fetch_assoc()): ?>
                        <tr data-id="<?= $row['drug_id'] ?>">
                            <td><?= $row['drug_id'] ?></td>
                            <td><?= $row['name'] ?></td>
                            <td><?= $row['category'] ?></td>
                            <td><?= $row['groups'] ?></td>
                            <td><?= $row['form'] ?></td>
                            <td><?= $row['batch_number'] ?></td>
                            <td><?= $row['expiry_date'] ?></td>
                            <td><?= $row['price_per_capsule'] ?></td>
                            <td><?= $row['capsules_remaining'] ?></td>
                            <td><img src="<?= $row['image'] ?>" alt="Drug Image" width="100"></td>
                            <td>
                                <button class="btn btn-info btn-sm" onclick="viewDrug(<?= $row['drug_id'] ?>)"><i class="fas fa-eye"></i></button>
                                <button class="btn btn-warning btn-sm" onclick="editDrug(<?= $row['drug_id'] ?>)"><i class="fas fa-edit"></i></button>
                                <button class="btn btn-danger btn-sm" onclick="deleteDrug(<?= $row['drug_id'] ?>)"><i class="fas fa-trash"></i></button>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
            
        </section>
    </div>
    </div>
<!-- Pagination Controls with Query Parameters -->
<nav aria-label="Page navigation">
    <ul class="pagination justify-content-center">
        <?php if($page > 1): ?>
            <li class="page-item">
                <a class="page-link" href="?<?= $query_string ?>&page=<?= $page - 1 ?>" aria-label="Previous">
                    <span aria-hidden="true">&laquo; Previous</span>
                </a>
            </li>
        <?php endif; ?>

        <?php for($i = 1; $i <= $total_pages; $i++): ?>
            <li class="page-item <?= ($i == $page) ? 'active' : '' ?>">
                <a class="page-link" href="?<?= $query_string ?>&page=<?= $i ?>"><?= $i ?></a>
            </li>
        <?php endfor; ?>

        <?php if($page < $total_pages): ?>
            <li class="page-item">
                <a class="page-link" href="?<?= $query_string ?>&page=<?= $page + 1 ?>" aria-label="Next">
                    <span aria-hidden="true">Next &raquo;</span>
                </a>
            </li>
        <?php endif; ?>
    </ul>
</nav>

        <!-- Add New Drug Modal -->
    <div class="modal fade" id="addDrugModal" tabindex="-1" aria-labelledby="addDrugModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form id="newDrugForm" action="process_inventory.php" method="POST" enctype="multipart/form-data">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addDrugModalLabel">Add New Drug</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <!-- Form fields -->
                        <div class="mb-3">
                            <label for="drug_id" class="form-label">Drug ID:</label>
                            <input type="text" class="form-control" id="drug_id" name="drug_id" value="<?php echo rand(100000, 999999); ?>" readonly>
                        </div>
                        <div class="modal-body">
    <div class="mb-3">
                        <label for="name">Name of Medication:</label>
                    <input type="text" id="name" name="name" required><br>
                    <label for="category">Category of Drugs:</label>
                    <select id="category" name="category[]" multiple required>
                        <option value="Depressants">Depressants</option>
                        <option value="Hallucinogens">Hallucinogens</option>
                        <option value="Stimulants">Stimulants</option>
                    </select><br>
                    <label for="groups">Groups of Drugs:</label>
                    <select id="groups" name="groups[]" multiple required>
                        <option value="Analgesics">Analgesics</option>
                        <option value="Anesthetics">Anesthetics</option>
                        <option value="Antibiotics">Antibiotics</option>
                        <option value="Antidepressants">Antidepressants</option>
                        <option value="Antidiabetics">Antidiabetics</option>
                        <option value="Antihypertensives">Antihypertensives</option>
                        <option value="Antipsychotics">Antipsychotics</option>
                        <option value="Antivirals">Antivirals</option>
                        <option value="Benzodiazepines">Benzodiazepines</option>
                        <option value="Beta-blockers">Beta-blockers</option>
                        <option value="Calcium Channel Blockers">Calcium Channel Blockers</option>
                        <option value="Corticosteroids">Corticosteroids</option>
                        <option value="Diuretics">Diuretics</option>
                        <option value="Immunosuppressants">Immunosuppressants</option>
                        <option value="Lipid-lowering Agents">Lipid-lowering Agents</option>
                        <option value="NSAIDs">Nonsteroidal Anti-Inflammatory Drugs (NSAIDs)</option>
                        <option value="Opioids">Opioids</option>
                        <option value="Proton Pump Inhibitors">Proton Pump Inhibitors</option>
                        <option value="Statins">Statins</option>
                        <option value="Thyroid Hormones">Thyroid Hormones</option>
                    </select><br>
                    <label for="num_capsules">Number of Capsules:</label>
                    <input type="number" id="num_capsules" name="num_capsules" required><br>
                    <label for="num_sachets">Number of Sachets:</label>
                    <input type="number" id="num_sachets" name="num_sachets" required><br>
                    <label for="num_packets">Number of Packets:</label>
                    <input type="number" id="num_packets" name="num_packets" required><br>
                    <label for="form">Forms of Drugs:</label>
                    <select id="form" name="form" required>
                        <option value="Tablets">Tablets</option>
                        <option value="Liquids">Liquids</option>
                        <option value="Inhalers">Inhalers</option>
                        <option value="Topicals">Topicals</option>
                        <option value="Suppositories">Suppositories</option>
                        <option value="Patches">Patches</option>
                        <option value="Injections">Injections</option>
                        <option value="Implants">Implants</option>
                    </select><br>
                    <label for="batch_number">Batch Number:</label>
                    <input type="text" id="batch_number" name="batch_number" required><br>
                    <label for="mode_of_admin">Mode of Administration:</label>
                    <select id="mode_of_admin" name="mode_of_admin" onchange="showInjectionOptions(this)" required>
                        <option value="Oral">Oral</option>
                        <option value="Inhalation">Inhalation</option>
                        <option value="Topical">Topical</option>
                        <option value="Injection">Injection</option>
                        <option value="Intrathecal">Intrathecal</option>
                        <option value="Rectal">Rectal</option>
                        <option value="Sublingual">Sublingual</option>
                        <option value="Buccal">Buccal</option>
                        <option value="Transdermal">Transdermal</option>
                    </select><br>
                    <div id="injection_methods" class="hidden">
                        <label>Injection Methods:</label><br>
                        <input type="checkbox" name="injection_methods[]" value="IV"> Intravenous (IV)<br>
                        <input type="checkbox" name="injection_methods[]" value="IM"> Intramuscular (IM)<br>
                        <input type="checkbox" name="injection_methods[]" value="SC"> Subcutaneous (SC)<br>
                    </div>
                    <label for="expiry_date">Expiry Date:</label>
                    <input type="date" id="expiry_date" name="expiry_date" required><br>
                    <label for="price_per_capsule">Price per Capsule (£):</label>
                    <input type="number" step="0.01" id="price_per_capsule" name="price_per_capsule" required><br>
                    <label for="special_note">Special Note:</label>
                    <textarea id="special_note" name="special_note"></textarea><br>
                    <label for="prescription_note">Prescription Note:</label>
                    <textarea id="prescription_note" name="prescription_note"></textarea><br>
                    <label for="capsules_remaining">Capsules Remaining:</label>
                    <input type="number" id="capsules_remaining" name="capsules_remaining" required><br>
                    <label for="image">Image:</label>
                    <input type="file" id="image" name="image" accept="image/*"><br>
                    <input type="submit" value="Submit">
                </form>
                        </div>
                    </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-success">Submit</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- View Drug Modal -->
    <div class="modal fade" id="viewDrugModal" tabindex="-1" aria-labelledby="viewDrugModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 id="viewDrugModalLabel" class="modal-title">View Drug</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="viewDrugContent">
                    <!-- Content populated via JavaScript -->
                </div>
            </div>
        </div>
    </div>

    <!-- Update Drug Modal -->
    <div class="modal fade" id="updateDrugModal" tabindex="-1" aria-labelledby="updateDrugModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form id="updateDrugForm" action="update_drug.php" method="POST" enctype="multipart/form-data">
                    <div class="modal-header">
                        <h5 class="modal-title" id="updateDrugModalLabel">Update Drug</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                  <div class="modal-body">
    <div class="mb-3">
                    <label for="capsules_remaining">Capsules Remaining:</label>
                    <input type="number" id="update_capsules_remaining" name="capsules_remaining" required><br>
                    <label for="image">Image:</label>
                    <input type="file" id="update_image" name="image" accept="image/*"><br>
                        <!-- Content populated via JavaScript -->
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-warning">Update</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    

    <!-- Bootstrap JS and Popper.js -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Custom JS -->
    <script>
        function showInjectionOptions(select) {
            var injectionMethods = document.getElementById("injection_methods");
            if (select.value === "Injection") {
                injectionMethods.style.display = 'block';
            } else {
                injectionMethods.style.display = 'none';
            }
        }

        document.getElementById('addNewDrugBtn').addEventListener('click', function() {
            document.getElementById('newDrugForm').reset();
            document.getElementById('drug_id').value = Math.floor(Math.random() * 900000) + 100000;
            var addDrugModal = new bootstrap.Modal(document.getElementById('addDrugModal'));
            addDrugModal.show();
        });

        // View Drug Function
        function viewDrug(drugId) {
            fetch(`get_drug.php?id=${drugId}`)
                .then(response => response.json())
                .then(data => {
                    let content = `
                        <p><strong>Drug ID:</strong> ${data.drug_id}</p>
                        <p><strong>Name:</strong> ${data.name}</p>
                        <p><strong>Category:</strong> ${data.category}</p>
                    <p><strong>Groups:</strong> ${data.groups}</p>
                    <p><strong>Form:</strong> ${data.form}</p>
                    <p><strong>Batch Number:</strong> ${data.batch_number}</p>
                    <p><strong>Expiry Date:</strong> ${data.expiry_date}</p>
                    <p><strong>Price per Capsule (£):</strong> ${data.price_per_capsule}</p>
                    <p><strong>Capsules Remaining:</strong> ${data.capsules_remaining}</p>
                    <p><strong>Image:</strong><br><img src="${data.image}" alt="Drug Image" width="100"></p>
                    <p><strong>Special Note:</strong> ${data.special_note}</p>
                    <p><strong>Prescription Note:</strong> ${data.prescription_note}</p>
                        <!-- ... -->
                    `;
                    document.getElementById('viewDrugContent').innerHTML = content;
                    var viewModal = new bootstrap.Modal(document.getElementById('viewDrugModal'));
                    viewModal.show();
                });
        }

        // Edit Drug Function
        function editDrug(drugId) {
            fetch(`get_drug.php?id=${drugId}`)
                .then(response => response.json())
                .then(data => {
                    let form = document.getElementById('updateDrugForm');
                    form.innerHTML = `
                        <input type="hidden" name="drug_id" value="${data.drug_id}">
                       <label for="name">Name of Medication:</label>
                    <input type="text" id="update_name" name="name" value="${data.name}" required><br>
                    <label for="category">Category of Drugs:</label>
                    <select id="update_category" name="category[]" multiple required>
                        <option value="Depressants" ${data.category.includes('Depressants') ? 'selected' : ''}>Depressants</option>
                        <option value="Hallucinogens" ${data.category.includes('Hallucinogens') ? 'selected' : ''}>Hallucinogens</option>
                        <option value="Stimulants" ${data.category.includes('Stimulants') ? 'selected' : ''}>Stimulants</option>
                    </select><br>
                    <label for="groups">Groups of Drugs:</label>
                    <select id="update_groups" name="groups[]" multiple required>
                        <option value="Analgesics" ${data.groups.includes('Analgesics') ? 'selected' : ''}>Analgesics</option>
                        <option value="Anesthetics" ${data.groups.includes('Anesthetics') ? 'selected' : ''}>Anesthetics</option>
                        <option value="Antibiotics" ${data.groups.includes('Antibiotics') ? 'selected' : ''}>Antibiotics</option>
                        <option value="Antidepressants" ${data.groups.includes('Antidepressants') ? 'selected' : ''}>Antidepressants</option>
                        <option value="Antidiabetics" ${data.groups.includes('Antidiabetics') ? 'selected' : ''}>Antidiabetics</option>
                        <option value="Antihypertensives" ${data.groups.includes('Antihypertensives') ? 'selected' : ''}>Antihypertensives</option>
                        <option value="Antipsychotics" ${data.groups.includes('Antipsychotics') ? 'selected' : ''}>Antipsychotics</option>
                        <option value="Antivirals" ${data.groups.includes('Antivirals') ? 'selected' : ''}>Antivirals</option>
                        <option value="Benzodiazepines" ${data.groups.includes('Benzodiazepines') ? 'selected' : ''}>Benzodiazepines</option>
                        <option value="Beta-blockers" ${data.groups.includes('Beta-blockers') ? 'selected' : ''}>Beta-blockers</option>
                        <option value="Calcium Channel Blockers" ${data.groups.includes('Calcium Channel Blockers') ? 'selected' : ''}>Calcium Channel Blockers</option>
                        <option value="Corticosteroids" ${data.groups.includes('Corticosteroids') ? 'selected' : ''}>Corticosteroids</option>
                        <option value="Diuretics" ${data.groups.includes('Diuretics') ? 'selected' : ''}>Diuretics</option>
                        <option value="Immunosuppressants" ${data.groups.includes('Immunosuppressants') ? 'selected' : ''}>Immunosuppressants</option>
                        <option value="Lipid-lowering Agents" ${data.groups.includes('Lipid-lowering Agents') ? 'selected' : ''}>Lipid-lowering Agents</option>
                        <option value="NSAIDs" ${data.groups.includes('NSAIDs') ? 'selected' : ''}>Nonsteroidal Anti-Inflammatory Drugs (NSAIDs)</option>
                        <option value="Opioids" ${data.groups.includes('Opioids') ? 'selected' : ''}>Opioids</option>
                        <option value="Proton Pump Inhibitors" ${data.groups.includes('Proton Pump Inhibitors') ? 'selected' : ''}>Proton Pump Inhibitors</option>
                        <option value="Statins" ${data.groups.includes('Statins') ? 'selected' : ''}>Statins</option>
                        <option value="Thyroid Hormones" ${data.groups.includes('Thyroid Hormones') ? 'selected' : ''}>Thyroid Hormones</option>
                    </select><br>
                    <label for="num_capsules">Number of Capsules:</label>
                    <input type="number" id="update_num_capsules" name="num_capsules" value="${data.num_capsules}" required><br>
                    <label for="num_sachets">Number of Sachets:</label>
                    <input type="number" id="update_num_sachets" name="num_sachets" value="${data.num_sachets}" required><br>
                    <label for="num_packets">Number of Packets:</label>
                    <input type="number" id="update_num_packets" name="num_packets" value="${data.num_packets}" required><br>
                    <label for="form">Forms of Drugs:</label>
                    <select id="update_form" name="form" required>
                        <option value="Tablets" ${data.form === 'Tablets' ? 'selected' : ''}>Tablets</option>
                        <option value="Liquids" ${data.form === 'Liquids' ? 'selected' : ''}>Liquids</option>
                        <option value="Inhalers" ${data.form === 'Inhalers' ? 'selected' : ''}>Inhalers</option>
                        <option value="Topicals" ${data.form === 'Topicals' ? 'selected' : ''}>Topicals</option>
                        <option value="Suppositories" ${data.form === 'Suppositories' ? 'selected' : ''}>Suppositories</option>
                        <option value="Patches" ${data.form === 'Patches' ? 'selected' : ''}>Patches</option>
                        <option value="Injections" ${data.form === 'Injections' ? 'selected' : ''}>Injections</option>
                        <option value="Implants" ${data.form === 'Implants' ? 'selected' : ''}>Implants</option>
                    </select><br>
                    <label for="batch_number">Batch Number:</label>
                    <input type="text" id="update_batch_number" name="batch_number" value="${data.batch_number}" required><br>
                    <label for="mode_of_admin">Mode of Administration:</label>
                    <select id="update_mode_of_admin" name="mode_of_admin" onchange="showInjectionOptions(this)" required>
                        <option value="Oral" ${data.mode_of_admin === 'Oral' ? 'selected' : ''}>Oral</option>
                        <option value="Inhalation" ${data.mode_of_admin === 'Inhalation' ? 'selected' : ''}>Inhalation</option>
                        <option value="Topical" ${data.mode_of_admin === 'Topical' ? 'selected' : ''}>Topical</option>
                        <option value="Injection" ${data.mode_of_admin === 'Injection' ? 'selected' : ''}>Injection</option>
                        <option value="Intrathecal" ${data.mode_of_admin === 'Intrathecal' ? 'selected' : ''}>Intrathecal</option>
                        <option value="Rectal" ${data.mode_of_admin === 'Rectal' ? 'selected' : ''}>Rectal</option>
                        <option value="Sublingual" ${data.mode_of_admin === 'Sublingual' ? 'selected' : ''}>Sublingual</option>
                        <option value="Buccal" ${data.mode_of_admin === 'Buccal' ? 'selected' : ''}>Buccal</option>
                        <option value="Transdermal" ${data.mode_of_admin === 'Transdermal' ? 'selected' : ''}>Transdermal</option>
                    </select><br>
                    <div id="update_injection_methods" class="${data.mode_of_admin === 'Injection' ? '' : 'hidden'}">
                        <label>Injection Methods:</label><br>
                        <input type="checkbox" name="injection_methods[]" value="IV" ${data.injection_methods.includes('IV') ? 'checked' : ''}> Intravenous (IV)<br>
                        <input type="checkbox" name="injection_methods[]" value="IM" ${data.injection_methods.includes('IM') ? 'checked' : ''}> Intramuscular (IM)<br>
                        <input type="checkbox" name="injection_methods[]" value="SC" ${data.injection_methods.includes('SC') ? 'checked' : ''}> Subcutaneous (SC)<br>
                    </div>
                    <label for="expiry_date">Expiry Date:</label>
                    <input type="date" id="update_expiry_date" name="expiry_date" value="${data.expiry_date}" required><br>
                    <label for="price_per_capsule">Price per Capsule (£):</label>
                    <input type="number" step="0.01" id="update_price_per_capsule" name="price_per_capsule" value="${data.price_per_capsule}" required><br>
                    <label for="special_note">Special Note:</label>
                    <textarea id="update_special_note" name="special_note">${data.special_note}</textarea><br>
                    <label for="prescription_note">Prescription Note:</label>
                    <textarea id="update_prescription_note" name="prescription_note">${data.prescription_note}</textarea><br>
                    <label for="capsules_remaining">Capsules Remaining:</label>
                    <input type="number" id="update_capsules_remaining" name="capsules_remaining" value="${data.capsules_remaining}" required><br>
                    <label for="image">Image:</label>
                    <input type="file" id="update_image" name="image" accept="image/*"><br>
                    <input type="submit" value="Update">
                ;
                    `;
                    var updateModal = new bootstrap.Modal(document.getElementById('updateDrugModal'));
                    updateModal.show();
                });
        }

        // Delete Drug Function
        function deleteDrug(drugId) {
            if (confirm("Are you sure you want to delete this drug?")) {
                fetch(`delete_drug.php?id=${drugId}`)
                    .then(response => response.text())
                    .then(data => {
                        if (data.includes('successfully')) {
                            document.querySelector(`tr[data-id="${drugId}"]`).remove();
                        }
                        alert(data);
                    });
            }
        }

        // Filter Function
       function showDropdown(dropdownId) {
    // Hide all dropdowns
    document.querySelectorAll('.filter-dropdown').forEach(function(dropdown) {
        dropdown.style.display = 'none';
    });
    // Show the selected dropdown
    document.getElementById(dropdownId).style.display = 'block';
}

        function filterTable(event) {
            event.preventDefault();
            const filterType = document.querySelector('input[name="filter_type"]:checked').value;
            const filterValues = Array.from(document.querySelectorAll(`#filter_${filterType} option:checked`)).map(el => el.value);

            const rows = document.querySelectorAll('#medicationTable tbody tr');
            rows.forEach(row => {
                const cellValue = row.querySelector(`td:nth-child(${getColumnIndex(filterType)})`).textContent;
                row.style.display = filterValues.includes(cellValue) ? '' : 'none';
            });
        }

        function getColumnIndex(filterType) {
            switch (filterType) {
                case 'category':
                    return 3;
                case 'groups':
                    return 4;
                case 'form':
                    return 5;
                default:
                    return 0;
            }
        }
    </script>
</body>
</html>