<?php
// fetch_availability.php
require_once '../init.php';
require_once '../config.php';

// 1) Read query params
$department = $_GET['department'] ?? '';
$type       = $_GET['type'] ?? '';
$date       = $_GET['date'] ?? '';

// 2) Try to fetch date-specific availability
$sqlDate = "SELECT time_interval, time_slot, slot_number, is_unavailable
            FROM appointment_availability
            WHERE department=?
              AND appointment_type=?
              AND availability_date = ?";
$stmt = $conn->prepare($sqlDate);
$stmt->bind_param('sss', $department, $type, $date);
$stmt->execute();
$res = $stmt->get_result();

$interval = 15; // default
$timeData = [];
if ($res->num_rows > 0) {
    // Take the first row’s interval
    $res->data_seek(0);
    $first = $res->fetch_assoc();
    $interval = $first['time_interval'];
    $res->data_seek(0);

    while ($row = $res->fetch_assoc()) {
        $timeData[ substr($row['time_slot'],0,5) ] = [
            'slot_number'    => $row['slot_number'],
            'is_unavailable' => $row['is_unavailable']
        ];
    }
}
$stmt->close();

// 3) If we found nothing date-specific, fallback to all-dates
if (empty($timeData)) {
    $sqlAll = "SELECT time_interval, time_slot, slot_number, is_unavailable
               FROM appointment_availability
               WHERE department=?
                 AND appointment_type=?
                 AND availability_date IS NULL";
    $stmt2 = $conn->prepare($sqlAll);
    $stmt2->bind_param('ss', $department, $type);
    $stmt2->execute();
    $res2 = $stmt2->get_result();
    if ($res2->num_rows > 0) {
        $res2->data_seek(0);
        $first2 = $res2->fetch_assoc();
        $interval = $first2['time_interval'];
        $res2->data_seek(0);

        while ($r2 = $res2->fetch_assoc()) {
            $timeData[ substr($r2['time_slot'],0,5) ] = [
                'slot_number'    => $r2['slot_number'],
                'is_unavailable' => $r2['is_unavailable']
            ];
        }
    }
    $stmt2->close();
}

// 4) Generate time slots according to $interval
function generateSlots($interval, $start='07:00', $end='20:00') {
    $slots = [];
    $current = strtotime($start);
    $last    = strtotime($end);
    while ($current <= $last) {
        $slots[] = date('H:i', $current);
        $current = strtotime("+{$interval} minutes", $current);
    }
    return $slots;
}
$allSlots = generateSlots($interval);

// 5) For each slot, check (a) is_unavailable=1 or (b) booked >= slot_number => disabled
$output = [];
foreach ($allSlots as $slot) {
    $disabled = false;
    $slotNumber    = 1;
    $isUnavailable = 0;

    if (isset($timeData[$slot])) {
        $slotNumber    = $timeData[$slot]['slot_number'];
        $isUnavailable = $timeData[$slot]['is_unavailable'];
    }

    if ($isUnavailable == 1) {
        $disabled = true;
    } else {
        // Check how many booked
        $countSql = "SELECT COUNT(*) AS cnt
                     FROM appointments
                     WHERE department=?
                       AND type=?
                       AND appointment_date=?
                       AND appointment_time=?";
        $cstmt = $conn->prepare($countSql);
        $cstmt->bind_param('ssss', $department, $type, $date, $slot);
        $cstmt->execute();
        $cr = $cstmt->get_result()->fetch_assoc();
        $countBooked = $cr['cnt'] ?? 0;
        $cstmt->close();

        if ($countBooked >= $slotNumber) {
            $disabled = true;
        }
    }

    $output[] = [
        'time'     => $slot,
        'disabled' => $disabled
    ];
}

// 6) Return JSON
header('Content-Type: application/json');
echo json_encode($output);
