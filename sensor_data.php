<?php
header('Content-Type: application/json');
include 'includes/db_connect.php';

// Log for debugging
file_put_contents('logs/post_raw.log', 
    date('Y-m-d H:i:s') . " - RAW: " . file_get_contents('php://input') . "\n", 
    FILE_APPEND
);
file_put_contents('logs/post_parsed.log', 
    date('Y-m-d H:i:s') . " - POST: " . print_r($_POST, true) . "\n", 
    FILE_APPEND
);

if (!isset($_POST['data'])) {
    echo json_encode(['code' => 400, 'message' => 'Missing data parameter']);
    exit;
}

$data = $_POST['data'];
$success = 0;
$errors = 0;

// Parse: ,200,1,1737000000,1.0;,201,2,1737000001,0.0;
$entries = explode(';', trim($data, ',;'));

foreach ($entries as $entry) {
    if (empty($entry)) continue;
    
    // Remove leading comma and split
    $parts = explode(',', trim($entry, ','));
    
    // Expected: [sid, mid, timestamp, value]
    if (count($parts) >= 4) {
        $sid = intval($parts[0]);           // 200-203
        $mid = intval($parts[1]);           // 1-4
        $timestamp = intval($parts[2]);     // unix timestamp
        $value = floatval($parts[3]);       // 0.0 or 1.0
        
        // Convert sid to slot_id: 200→1, 201→2, 202→3, 203→4
        $slot_id = $sid - 199;
        $status = intval($value);  // 0=occupied, 1=available
        
        // Update database
        $stmt = $conn->prepare("UPDATE sensor_data SET status = ?, timestamp = FROM_UNIXTIME(?) WHERE sensor_id = ?");
        $stmt->bind_param("iii", $status, $timestamp, $slot_id);
        
        if ($stmt->execute()) {
            $success++;
            file_put_contents('logs/updates.log', 
                date('Y-m-d H:i:s') . " - Slot $slot_id status=$status\n", 
                FILE_APPEND
            );
        } else {
            $errors++;
            file_put_contents('logs/errors.log', 
                date('Y-m-d H:i:s') . " - Error: " . $stmt->error . "\n", 
                FILE_APPEND
            );
        }
        $stmt->close();
    } else {
        file_put_contents('logs/parse_errors.log', 
            date('Y-m-d H:i:s') . " - Bad entry: $entry\n", 
            FILE_APPEND
        );
    }
}

echo json_encode([
    'code' => 200,
    'message' => 'Processed',
    'success' => $success,
    'errors' => $errors
]);
?>