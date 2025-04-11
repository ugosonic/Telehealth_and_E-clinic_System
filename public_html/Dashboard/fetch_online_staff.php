<?php
// fetch_online_staff.php
require_once '../init.php';
require_once '../config.php';

$limit = 10; // Number of records per page
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$start = ($page - 1) * $limit;

$sql = "SELECT username, usergroup, status FROM users WHERE usergroup != 'Patient' AND online_status = 1 LIMIT ?, ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $start, $limit);
$stmt->execute();
$result = $stmt->get_result();

$onlineStaff = [];

while ($row = $result->fetch_assoc()) {
    $onlineStaff[] = $row;
}

$totalResult = $conn->query("SELECT COUNT(*) AS total FROM users WHERE usergroup != 'Patient' AND online_status = 1")->fetch_assoc()['total'];
$totalPages = ceil($totalResult / $limit);

$response = [
    'staff' => $onlineStaff,
    'totalPages' => $totalPages
];

echo json_encode($response);

$conn->close();
?>
