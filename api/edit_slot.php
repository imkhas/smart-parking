<?php
header('Content-Type: application/json');
include '../includes/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'POST required']);
    exit;
}

$slot_id   = isset($_POST['slot_id'])   ? intval($_POST['slot_id'])   : 0;
$slot_name = trim($_POST['slot_name'] ?? '');
$location  = trim($_POST['location'] ?? '');
$level     = trim($_POST['level'] ?? '');
$zone      = trim($_POST['zone'] ?? '');
$slot_type = trim($_POST['slot_type'] ?? '');
$sensor_id_input = isset($_POST['sensor_id']) ? trim($_POST['sensor_id']) : '';

if ($slot_id < 1) {
    echo json_encode(['success' => false, 'error' => 'Invalid slot_id']);
    exit;
}

if (!$slot_name || !$location || !$level || !$zone || !$slot_type) {
    echo json_encode(['success' => false, 'error' => 'All metadata fields required']);
    exit;
}

// Determine sensor_id: empty string means unassign
$new_sensor_id = ($sensor_id_input === '' || $sensor_id_input === null) ? null : intval($sensor_id_input);

if ($new_sensor_id !== null && $new_sensor_id < 1) {
    echo json_encode(['success' => false, 'error' => 'Invalid sensor_id']);
    exit;
}

// Verify slot exists
$check = $conn->prepare("SELECT id, sensor_id FROM parking_slots WHERE id = ?");
$check->bind_param("i", $slot_id);
$check->execute();
$check->store_result();
if ($check->num_rows === 0) {
    echo json_encode(['success' => false, 'error' => 'Slot not found']);
    $check->close();
    $conn->close();
    exit;
}
$check->bind_result($existing_id, $old_sensor_id);
$check->fetch();
$check->close();

// If assigning a sensor, verify it exists in sensor_data and clear previous slot
if ($new_sensor_id !== null) {
    $exists = $conn->prepare("SELECT sensor_id FROM sensor_data WHERE sensor_id = ?");
    $exists->bind_param("i", $new_sensor_id);
    $exists->execute();
    $exists->store_result();
    if ($exists->num_rows === 0) {
        echo json_encode(['success' => false, 'error' => 'Sensor does not exist in sensor_data']);
        $exists->close();
        $conn->close();
        exit;
    }
    $exists->close();

    // Clear sensor from previous slot if different slot
    $clear = $conn->prepare("UPDATE parking_slots SET sensor_id = NULL WHERE sensor_id = ? AND id != ?");
    $clear->bind_param("ii", $new_sensor_id, $slot_id);
    $clear->execute();
    $clear->close();
}

$stmt = $conn->prepare("UPDATE parking_slots SET slot_name = ?, location = ?, level = ?, zone = ?, slot_type = ?, sensor_id = ? WHERE id = ?");
$stmt->bind_param("ssssssi", $slot_name, $location, $level, $zone, $slot_type, $new_sensor_id, $slot_id);

if ($stmt->execute()) {
    echo json_encode([
        'success' => true,
        'message' => "Slot #{$slot_id} updated" .
            ($new_sensor_id !== $old_sensor_id
                ? ($new_sensor_id ? " (sensor #{$new_sensor_id})" : " (sensor unassigned)")
                : "")
    ]);
} else {
    echo json_encode(['success' => false, 'error' => $stmt->error]);
}

$stmt->close();
$conn->close();
