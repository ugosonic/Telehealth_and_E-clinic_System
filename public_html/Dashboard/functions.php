<?php
// functions.php

/**
 * 1) Fetch user details, possibly from `users` or `patient_db`
 */
function getUserDetails($conn, $username) {
    $stmt = $conn->prepare('SELECT username, email, usergroup FROM users WHERE username = ?');
    $stmt->bind_param('s', $username);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($user = $result->fetch_assoc()) {
        return $user;
    }
    // If not in `users`, check `patient_db`
    $stmt = $conn->prepare('SELECT username FROM patient_db WHERE username = ?');
    $stmt->bind_param('s', $username);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($patient = $result->fetch_assoc()) {
        return array_merge($patient, ['usergroup' => 'Patient']);
    }
    return false; // not found
}

/**
 * 2) Return [ $conditionSQL, $paramTypes, $paramValuesArray ]
 *    for retrieving appointments from the `appointments` table
 */
function getSqlCondition($usergroup, $username, $patient_id) {
    if ($patient_id) {
        // E.g., "AND p.patient_id = ?"
        return ["AND p.patient_id = ?", 's', [$patient_id]];
    } elseif ($usergroup === 'Patient') {
        // If no numeric patient_id was passed, use username
        return ["AND p.username = ?", 's', [$username]];
    } else {
        // For staff or admin, no extra condition
        return ["", '', []];
    }
}

/**
 * 3) Retrieve upcoming/past/cancelled from `appointments`
 */
function getAppointments($conn, $sqlCondition, $today, $start_from, $results_per_page) {
    $appointments = [
        'upcoming'  => [],
        'past'      => [],
        'cancelled' => [],
    ];
    list($condition, $types, $params) = $sqlCondition;

    // 3.1) Upcoming
    {
        $baseTypes  = 's';  // For $today
        $limitTypes = 'ii'; // For $start_from, $results_per_page
        $allTypes   = $baseTypes . $types . $limitTypes;
        $baseParams = [$today];
        $limitParams= [$start_from, $results_per_page];
        $allParams  = array_merge($baseParams, $params, $limitParams);

        $sql = "SELECT a.appointment_id, a.appointment_date, a.appointment_time,
                       a.department, a.type,
                       p.patient_id, p.first_name, p.middle_name, p.surname
                FROM appointments a
                JOIN patient_db p ON a.patient_id = p.patient_id
                WHERE a.appointment_date >= ? $condition
                ORDER BY a.appointment_date ASC
                LIMIT ?, ?";
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            die('Prepare failed (Upcoming): ' . $conn->error);
        }
        $stmt->bind_param($allTypes, ...$allParams);
        $stmt->execute();
        $appointments['upcoming'] = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
    }

    // 3.2) Past
    {
        $baseTypes  = 's';
        $limitTypes = 'ii';
        $allTypes   = $baseTypes . $types . $limitTypes;
        $baseParams = [$today];
        $limitParams= [$start_from, $results_per_page];
        $allParams  = array_merge($baseParams, $params, $limitParams);

        $sql = "SELECT a.appointment_id, a.appointment_date, a.appointment_time,
                       a.department, a.type,
                       p.patient_id, p.first_name, p.middle_name, p.surname
                FROM appointments a
                JOIN patient_db p ON a.patient_id = p.patient_id
                WHERE a.appointment_date < ? $condition
                ORDER BY a.appointment_date DESC
                LIMIT ?, ?";
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            die('Prepare failed (Past): ' . $conn->error);
        }
        $stmt->bind_param($allTypes, ...$allParams);
        $stmt->execute();
        $appointments['past'] = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
    }

    // 3.3) Cancelled
    {
        $limitTypes = 'ii'; 
        $allTypes   = $types . $limitTypes;
        $limitParams= [$start_from, $results_per_page];
        $allParams  = array_merge($params, $limitParams);

        $sql = "SELECT c.appointment_id, c.cancellation_time, c.cancellation_reason,
                       a.appointment_date, a.appointment_time, 
                       a.department, a.type,
                       p.patient_id, p.first_name, p.middle_name, p.surname
                FROM canceled_appointments c
                JOIN appointments a ON c.appointment_id = a.appointment_id
                JOIN patient_db p ON a.patient_id = p.patient_id
                WHERE 1=1 $condition
                ORDER BY c.cancellation_time DESC
                LIMIT ?, ?";
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            die('Prepare failed (Cancelled): ' . $conn->error);
        }
        if (!empty($types)) {
            $stmt->bind_param($allTypes, ...$allParams);
        } else {
            $stmt->bind_param($limitTypes, ...$limitParams);
        }
        $stmt->execute();
        $appointments['cancelled'] = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
    }

    return $appointments;
}

/**
 * 4) Count total from `appointments`
 */
function getTotalAppointments($conn, $sqlCondition) {
    list($condition, $types, $params) = $sqlCondition;
    $sql = "SELECT COUNT(*) as total
            FROM appointments a
            JOIN patient_db p ON a.patient_id = p.patient_id
            WHERE 1=1 $condition";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        die('Prepare failed (Total): ' . $conn->error);
    }
    if (!empty($types)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    return $row['total'] ?? 0;
}

/**
 * 5) Reschedule an appointment
 */
function rescheduleAppointment($conn, $appointmentId, $newDate, $newTime) {
    $sql = "UPDATE appointments
            SET appointment_date = ?, appointment_time = ?
            WHERE appointment_id = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        die("Prepare failed (reschedule): " . $conn->error);
    }
    $stmt->bind_param('ssi', $newDate, $newTime, $appointmentId);
    return $stmt->execute();
}

/**
 * 6) Cancel an appointment
 */
function cancelAppointment($conn, $appointmentId, $reason = '') {
    // Insert into canceled_appointments
    $stmt = $conn->prepare("INSERT INTO canceled_appointments
        (appointment_id, cancellation_time, cancellation_reason)
        VALUES (?, NOW(), ?)");
    if (!$stmt) {
        die("Prepare failed (cancel - insert): " . $conn->error);
    }
    $stmt->bind_param('is', $appointmentId, $reason);
    $ok = $stmt->execute();
    if (!$ok) return false;
    
    // Remove from 'appointments'
    $stmt2 = $conn->prepare("DELETE FROM appointments WHERE appointment_id = ?");
    if (!$stmt2) {
        die("Prepare failed (cancel - delete): " . $conn->error);
    }
    $stmt2->bind_param('i', $appointmentId);
    return $stmt2->execute();
}

/**
 * 7) Retrieve the next upcoming video consultation from `meetings`.
 *    NOTE: We must match the numeric patient_id from patient_db, not username.
 */
function getNextVideoConsultation($conn, $username) {
    // First, get the numeric patient_id for this username
    $stmt = $conn->prepare("SELECT patient_id FROM patient_db WHERE username = ? LIMIT 1");
    if (!$stmt) {
        die("Prepare failed (Get numeric patient_id): " . $conn->error);
    }
    $stmt->bind_param('s', $username);
    $stmt->execute();
    $patientRow = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$patientRow) {
        // If we can't find the patient in `patient_db`, no upcoming meeting
        return null;
    }

    $numericPatientId = $patientRow['patient_id'];

    // Now retrieve from meetings
    $sql = "SELECT meeting_id, expiration
            FROM meetings
            WHERE patient_id = ?
              AND expiration > NOW()
            ORDER BY expiration ASC
            LIMIT 1";
    
    $stmt2 = $conn->prepare($sql);
    if (!$stmt2) {
        die("Prepare failed (Telehealth): " . $conn->error);
    }
    // patient_id is presumably stored as a string or integer in DB:
    $stmt2->bind_param('s', $numericPatientId);
    $stmt2->execute();
    $row = $stmt2->get_result()->fetch_assoc();
    $stmt2->close();

    return $row ?: null;
}
