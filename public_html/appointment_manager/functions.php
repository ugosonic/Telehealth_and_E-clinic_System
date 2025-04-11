<?php
// functions.php

function getUserDetails($conn, $username) {
    $stmt = $conn->prepare('SELECT username, email, usergroup FROM users WHERE username = ?');
    $stmt->bind_param('s', $username);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($user = $result->fetch_assoc()) {
        return $user;
    }
    // Check in patient_db
    $stmt = $conn->prepare('SELECT username FROM patient_db WHERE username = ?');
    $stmt->bind_param('s', $username);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($patient = $result->fetch_assoc()) {
        return array_merge($patient, ['usergroup' => 'Patient']);
    }
    // User not found
    return false;
}

function getSqlCondition($usergroup, $username, $patient_id) {
    if ($patient_id) {
        return ["AND p.patient_id = ?", 's', [$patient_id]];
    } elseif ($usergroup === 'Patient') {
        return ["AND p.username = ?", 's', [$username]];
    } else {
        // For Admins and other user groups, no additional condition
        return ["", '', []];
    }
}

function getAppointments($conn, $sqlCondition, $today, $start_from, $results_per_page) {
    $appointments = ['upcoming' => [], 'past' => [], 'cancelled' => []];
    list($condition, $types, $params) = $sqlCondition;

    // Upcoming Appointments
    {
        // Build types and params
        $baseTypes = 's'; // For $today
        $limitTypes = 'ii'; // For $start_from and $results_per_page
        $allTypes = $baseTypes . $types . $limitTypes;
        $baseParams = [$today];
        $limitParams = [$start_from, $results_per_page];
        $allParams = array_merge($baseParams, $params, $limitParams);

        $sql = "SELECT a.appointment_id, a.appointment_date, a.appointment_time, a.department, a.type, 
                p.patient_id, p.first_name, p.middle_name, p.surname 
                FROM appointments a 
                JOIN patient_db p ON a.patient_id = p.patient_id 
                WHERE a.appointment_date >= ? $condition 
                ORDER BY a.appointment_date ASC 
                LIMIT ?, ?";
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            die('Prepare failed: ' . $conn->error);
        }
        $stmt->bind_param($allTypes, ...$allParams);
        $stmt->execute();
        $appointments['upcoming'] = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
    }

    // Past Appointments
    {
        // Build types and params
        $baseTypes = 's';
        $limitTypes = 'ii';
        $allTypes = $baseTypes . $types . $limitTypes;
        $baseParams = [$today];
        $limitParams = [$start_from, $results_per_page];
        $allParams = array_merge($baseParams, $params, $limitParams);

        $sql = "SELECT a.appointment_id, a.appointment_date, a.appointment_time, a.department, a.type, 
                p.patient_id, p.first_name, p.middle_name, p.surname 
                FROM appointments a 
                JOIN patient_db p ON a.patient_id = p.patient_id 
                WHERE a.appointment_date < ? $condition 
                ORDER BY a.appointment_date DESC 
                LIMIT ?, ?";
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            die('Prepare failed: ' . $conn->error);
        }
        $stmt->bind_param($allTypes, ...$allParams);
        $stmt->execute();
        $appointments['past'] = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
    }

    // Cancelled Appointments
    {
        // Build types and params
        $limitTypes = 'ii'; // For $start_from and $results_per_page
        $allTypes = $types . $limitTypes;
        $limitParams = [$start_from, $results_per_page];
        $allParams = array_merge($params, $limitParams);

        $sql = "SELECT c.appointment_id, c.cancellation_time, c.cancellation_reason, 
                a.appointment_date, a.appointment_time, a.department, a.type,
                p.patient_id, p.first_name, p.middle_name, p.surname 
                FROM canceled_appointments c
                JOIN appointments a ON c.appointment_id = a.appointment_id
                JOIN patient_db p ON a.patient_id = p.patient_id 
                WHERE 1=1 $condition
                ORDER BY c.cancellation_time DESC 
                LIMIT ?, ?";
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            die('Prepare failed: ' . $conn->error);
        }
        if (!empty($types)) {
            $stmt->bind_param($allTypes, ...$allParams);
        } else {
            // Only limit parameters to bind
            $stmt->bind_param($limitTypes, ...$limitParams);
        }
        $stmt->execute();
        $appointments['cancelled'] = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
    }

    return $appointments;
}

function getTotalAppointments($conn, $sqlCondition) {
    list($condition, $types, $params) = $sqlCondition;
    $sql = "SELECT COUNT(*) as total FROM appointments a 
            JOIN patient_db p ON a.patient_id = p.patient_id 
            WHERE 1=1 $condition";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        die('Prepare failed: ' . $conn->error);
    }
    if (!empty($types)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    return $result['total'];
}

function displayAppointmentsTable($appointments, $type) {
    if (empty($appointments)) {
        echo "<p class='mt-3'>No appointments found.</p>";
        return;
    }
    echo "<table class='table table-bordered mt-3'>
            <thead class='thead-dark'>
                <tr>
                    <th>Patient Name</th>
                    <th>Patient ID</th>
                    <th>Appointment ID</th>
                    <th>Type</th>
                    <th>Date</th>
                    <th>Time</th>";
    if ($type !== 'cancelled') {
        echo "<th>Actions</th>";
    } else {
        echo "<th>Cancelled On</th>
              <th>Reason</th>";
    }
    echo    "</tr>
            </thead>
            <tbody>";
    foreach ($appointments as $row) {
        echo "<tr>
                <td>{$row['first_name']} {$row['middle_name']} {$row['surname']}</td>
                <td>{$row['patient_id']}</td>
                <td>{$row['appointment_id']}</td>
                <td>{$row['type']}</td>
                <td>{$row['appointment_date']}</td>
                <td>{$row['appointment_time']}</td>";
        if ($type !== 'cancelled') {
            echo "<td>
                    <button class='btn btn-info btn-sm btn-view' data-id='{$row['appointment_id']}'>View</button>
                    <button class='btn btn-warning btn-sm btn-reschedule' data-id='{$row['appointment_id']}'>Reschedule</button>
                    <button class='btn btn-danger btn-sm btn-cancel' data-id='{$row['appointment_id']}'>Cancel</button>
                  </td>";
        } else {
            echo "<td>{$row['cancellation_time']}</td>
                  <td>{$row['cancellation_reason']}</td>";
        }
        echo "</tr>";
    }
    echo    "</tbody>
          </table>";
}

function displayPagination($current_page, $total_pages) {
    if ($total_pages <= 1) {
        return;
    }
    echo "<nav aria-label='Page navigation'>
            <ul class='pagination justify-content-center'>";
    for ($i = 1; $i <= $total_pages; $i++) {
        $active = ($i === $current_page) ? 'active' : '';
        echo "<li class='page-item $active'>
                <a class='page-link' href='?page=$i'>$i</a>
              </li>";
    }
    echo    "</ul>
          </nav>";
}
?>
