<?php
header('Content-Type: application/json');
include '../includes/db_connect.php';

// Changed from parking_slots to sensor_data
$sql = "SELECT sensor_id as slot_id, status FROM sensor_data ORDER BY sensor_id ASC";

$result = mysqli_query($conn, $sql);
$slots = [];

while($row = mysqli_fetch_assoc($result)) {
    $slots[] = [
        'slot_id' => $row['slot_id'],
        'status' => $row['status']
    ];
}

echo json_encode($slots);
?>