<?php
header('Content-Type: application/json');
include '../includes/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'POST required']);
    exit;
}

$slot_id = isset($_POST['slot_id']) ? intval($_POST['slot_id']) : 0;

if ($slot_id < 1) {
    echo json_encode(['success' => false, 'error' => 'Invalid slot_id']);
    exit;
}

$stmt = $conn->prepare("DELETE FROM parking_slots WHERE id = ?");
$stmt->bind_param("i", $slot_id);

if ($stmt->execute() && $stmt->affected_rows > 0) {
    echo json_encode(['success' => true, 'message' => "Slot #{$slot_id} deleted"]);
} else {
    echo json_encode(['success' => false, 'error' => 'Slot not found']);
}

$stmt->close();
$conn->close();
