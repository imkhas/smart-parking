<?php

header('Content-Type: application/json');

include '../includes/db_connect.php';

$sql = "SELECT * FROM parking_slots ORDER BY slot_id ASC";

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