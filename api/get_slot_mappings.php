<?php
header('Content-Type: application/json');
include '../includes/db_connect.php';

$sql = "
    SELECT ps.id, ps.slot_name, ps.location, ps.level, ps.zone, ps.slot_type,
           ps.sensor_id, sd.status, sd.timestamp
    FROM parking_slots ps
    LEFT JOIN sensor_data sd ON ps.sensor_id = sd.sensor_id
    ORDER BY ps.id
";
$result = $conn->query($sql);

$slots = [];
while ($row = $result->fetch_assoc()) {
    $slots[] = [
        'id'        => (int)$row['id'],
        'slot_name' => $row['slot_name'],
        'location'  => $row['location'],
        'level'     => $row['level'],
        'zone'      => $row['zone'],
        'slot_type' => $row['slot_type'],
        'sensor_id' => $row['sensor_id'] ? (int)$row['sensor_id'] : null,
        'status'    => $row['status'] !== null ? (int)$row['status'] : null,
        'timestamp' => $row['timestamp'] ?? null
    ];
}

echo json_encode($slots);
