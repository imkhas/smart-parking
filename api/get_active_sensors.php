<?php
header('Content-Type: application/json');
include '../includes/db_connect.php';

// Sensors that exist in sensor_data but are NOT currently assigned
$sql = "
    SELECT sd.sensor_id, sd.status, sd.timestamp
    FROM sensor_data sd
    LEFT JOIN parking_slots ps ON sd.sensor_id = ps.sensor_id
    WHERE ps.sensor_id IS NULL
    ORDER BY sd.sensor_id
";
$result = $conn->query($sql);

$unassigned = [];
while ($row = $result->fetch_assoc()) {
    $unassigned[] = [
        'sensor_id' => (int)$row['sensor_id'],
        'status'    => (int)$row['status'],
        'timestamp' => $row['timestamp']
    ];
}

// All sensors (for the mapping display — includes assigned ones)
$all = $conn->query("SELECT sensor_id, status, timestamp FROM sensor_data ORDER BY sensor_id");
$allSensors = [];
while ($row = $all->fetch_assoc()) {
    $allSensors[] = [
        'sensor_id' => (int)$row['sensor_id'],
        'status'    => (int)$row['status'],
        'timestamp' => $row['timestamp']
    ];
}

echo json_encode([
    'unassigned' => $unassigned,
    'all'        => $allSensors
]);
