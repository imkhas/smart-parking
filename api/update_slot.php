<?php
header('Content-Type: application/json');
include '../includes/db_connect.php';

$slot_id = isset($_GET['slot_id']) ? intval($_GET['slot_id']) : null;
$status = isset($_GET['status']) ? intval($_GET['status']) : null;

if ($slot_id !== null && $status !== null) {

    // Update latest sensor status
    $stmt = $conn->prepare("
        UPDATE sensor_data 
        SET status = ? 
        WHERE sensor_id = ?
    ");

    $stmt->bind_param("ii", $status, $slot_id);

    if ($stmt->execute()) {

        echo json_encode([
            'success' => true,
            'slot_id' => $slot_id,
            'status' => $status
        ]);

    } else {

        echo json_encode([
            'success' => false,
            'error' => $stmt->error
        ]);

    }

    $stmt->close();

} else {

    echo json_encode([
        'success' => false,
        'error' => 'Missing parameters'
    ]);

}

$conn->close();
?>