<?php
header('Content-Type: application/json');
include '../includes/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'POST required']);
    exit;
}

$slot_id   = isset($_POST['slot_id'])   ? intval($_POST['slot_id'])   : 0;
$sensor_id = isset($_POST['sensor_id']) ? intval($_POST['sensor_id']) : 0;

if ($slot_id < 1 || $sensor_id < 1) {
    echo json_encode(['success' => false, 'error' => 'Invalid slot_id or sensor_id']);
    exit;
}

// Check sensor exists in sensor_data
$check = $conn->prepare("SELECT sensor_id FROM sensor_data WHERE sensor_id = ?");
$check->bind_param("i", $sensor_id);
$check->execute();
$check->store_result();
if ($check->num_rows === 0) {
    echo json_encode(['success' => false, 'error' => 'Sensor does not exist']);
    $check->close();
    $conn->close();
    exit;
}
$check->close();

// Check slot exists
$check2 = $conn->prepare("SELECT id FROM parking_slots WHERE id = ?");
$check2->bind_param("i", $slot_id);
$check2->execute();
$check2->store_result();
if ($check2->num_rows === 0) {
    echo json_encode(['success' => false, 'error' => 'Slot not found']);
    $check2->close();
    $conn->close();
    exit;
}
$check2->close();

// Clear sensor from any previous slot (allows reassignment)
$clear = $conn->prepare("UPDATE parking_slots SET sensor_id = NULL WHERE sensor_id = ?");
$clear->bind_param("i", $sensor_id);
$clear->execute();
$clear->close();

// Assign sensor to target slot
$stmt = $conn->prepare("UPDATE parking_slots SET sensor_id = ? WHERE id = ?");
$stmt->bind_param("ii", $sensor_id, $slot_id);

if ($stmt->execute()) {
    echo json_encode([
        'success'   => true,
        'message'   => "Sensor #{$sensor_id} assigned to slot #{$slot_id}",
        'slot_id'   => $slot_id,
        'sensor_id' => $sensor_id
    ]);
} else {
    echo json_encode(['success' => false, 'error' => $stmt->error]);
}

$stmt->close();
$conn->close();
