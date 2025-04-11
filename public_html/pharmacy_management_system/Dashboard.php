<?php
include '../init.php';
include '../config.php';
include '../access_control.php';
include '../sidebar.php';

?>
<!DOCTYPE html>
<html>
<head>
    <title>Pharmacy Management System</title>
    <link rel="stylesheet" type="text/css" href="styles.css">
    <link rel="stylesheet" href="../waiting_room/waiting_room.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
<div class="container">
  
    <!-- Secondary Sidebar -->
    <div class="topbar">
        <a class="active" href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
        <a href="inventory.php"><i class="fas fa-pills"></i> Inventory</a>
        <a href="doctors_orders.php"><i class="fas fa-stethoscope"></i> Doctor's Orders</a>
        <a href="expired.php"><i class="fas fa-exclamation-triangle"></i> Expired</a>
        <a href="out_of_orders.php"><i class="fas fa-ban"></i> Out of Orders</a>
        <a href="sales_report.php"><i class="fas fa-chart-line"></i> Sales Report</a>
    </div>

    <!-- Main Content -->
    <div class="content">
        <div class="section">
            <h2>Expire Date Notification</h2>
            <!-- Expire date notifications will be dynamically generated here -->
        </div>
        <div class="section">
            <h2>Out of Stock Notification</h2>
            <!-- Out of stock notifications will be dynamically generated here -->
        </div>
        <div class="section">
            <h2>Drug Quantity Chart</h2>
            <canvas id="drugQuantityChart"></canvas>
        </div>
        <div class="section">
            <h2>About to Get Finished Notification</h2>
            <!-- About to finish notifications will be dynamically generated here -->
        </div>
    </div>
</div>

<!-- Include JavaScript libraries -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // JavaScript to generate the chart
    var ctx = document.getElementById('drugQuantityChart').getContext('2d');
    var chart = new Chart(ctx, {
        // Chart configuration
        type: 'bar',
        data: {
            labels: ['Amoxillin', 'Citazin', 'Metformin', 'Panadol'],
            datasets: [{
                label: 'Quantity',
                data: [50, 30, 70, 60],
                backgroundColor: ['#007bff', '#28a745', '#ffc107', '#dc3545']
            }]
        },
        options: {
            responsive: true,
            scales: {
                x: {
                    beginAtZero: true
                }
            }
        }
    });
</script>
</body>
</html>
