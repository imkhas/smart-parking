<?php
header('Content-Type: application/json');
include '../includes/db_connect.php';

$slot_id = isset($_GET['slot_id']) ? intval($_GET['slot_id']) : 0;

if ($slot_id < 1) {
    echo json_encode(['success' => false, 'error' => 'Invalid slot_id']);
    exit;
}

$stmt = $conn->prepare("
    SELECT sd.sensor_id, sd.status, sd.timestamp,
           ps.slot_name, ps.slot_type, ps.level, ps.location, ps.zone
    FROM sensor_data sd
    LEFT JOIN parking_slots ps ON sd.sensor_id = ps.sensor_id
    WHERE sd.sensor_id = ?
");
$stmt->bind_param("i", $slot_id);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    echo json_encode([
        'success' => true,
        'data' => [
            'sensor_id' => (int)$row['sensor_id'],
            'status'    => (int)$row['status'],
            'slot_name' => $row['slot_name'] ?? '—',
            'slot_type' => $row['slot_type'] ?? '—',
            'level'     => $row['level'] ?? '—',
            'location'  => $row['location'] ?? '—',
            'zone'      => $row['zone'] ?? '—',
            'timestamp' => $row['timestamp']
        ]
    ]);
} else {
    echo json_encode(['success' => false, 'error' => 'Slot not found']);
}
