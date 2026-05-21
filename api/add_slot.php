<?php
header('Content-Type: application/json');
include '../includes/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'POST required']);
    exit;
}

$slot_name = trim($_POST['slot_name'] ?? '');
$location  = trim($_POST['location'] ?? '');
$level     = trim($_POST['level'] ?? '');
$zone      = trim($_POST['zone'] ?? '');
$slot_type = trim($_POST['slot_type'] ?? '');

if (!$slot_name || !$location || !$level || !$zone || !$slot_type) {
    echo json_encode(['success' => false, 'error' => 'All fields required']);
    exit;
}

$stmt = $conn->prepare("
    INSERT INTO parking_slots (slot_name, location, level, zone, slot_type)
    VALUES (?, ?, ?, ?, ?)
");
$stmt->bind_param("sssss", $slot_name, $location, $level, $zone, $slot_type);

if ($stmt->execute()) {
    echo json_encode([
        'success' => true,
        'slot_id' => $stmt->insert_id,
        'message' => "Slot '$slot_name' created"
    ]);
} else {
    echo json_encode(['success' => false, 'error' => $stmt->error]);
}

$stmt->close();
$conn->close();
