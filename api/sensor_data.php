<?php
header('Content-Type: application/json');
include 'includes/db_connect.php';

// Log the raw POST data for debugging
file_put_contents('logs/post_debug.log', date('Y-m-d H:i:s') . " - RAW POST: " . file_get_contents('php://input') . "\n", FILE_APPEND);
file_put_contents('logs/post_debug.log', date('Y-m-d H:i:s') . " - POST Array: " . print_r($_POST, true) . "\n", FILE_APPEND);

// Check if data parameter exists
if (!isset($_POST['data'])) {
    echo json_encode(['code' => 400, 'message' => 'Missing data parameter']);
    exit;
}

$data = $_POST['data'];
$success_count = 0;
$error_count = 0;

// Parse the data string
// Format: ,sid,mid,timestamp,value;,sid,mid,timestamp,value;
$entries = explode(';', trim($data, ',;'));

foreach ($entries as $entry) {
    if (empty($entry)) continue;
    
    $parts = explode(',', trim($entry, ','));
    
    // Expected format: sid, mid, timestamp, value
    if (count($parts) >= 4) {
        $sid = intval($parts[0]);        // sensor slot id (200, 201, 202, 203)
        $mid = intval($parts[1]);        // measurement id (1, 2, 3, 4)
        $timestamp = intval($parts[2]);  // unix timestamp
        $value = floatval($parts[3]);    // sensor value (0 or 1)
        
        // Convert sensor ID (200-203) to slot ID (1-4)
        $slot_id = $sid - 199;  // 200->1, 201->2, 202->3, 203->4
        
        // Convert value to status (0=occupied, 1=available)
        $status = intval($value);
        
        // Update database
        $stmt = $conn->prepare("UPDATE sensor_data SET status = ?, timestamp = FROM_UNIXTIME(?) WHERE sensor_id = ?");
        $stmt->bind_param("iii", $status, $timestamp, $slot_id);
        
        if ($stmt->execute()) {
            $success_count++;
            
            // Also log to file for debugging
            file_put_contents('logs/sensor_updates.log', 
                date('Y-m-d H:i:s') . " - Slot $slot_id: status=$status\n", 
                FILE_APPEND
            );
        } else {
            $error_count++;
            file_put_contents('logs/sensor_errors.log', 
                date('Y-m-d H:i:s') . " - Error updating slot $slot_id: " . $stmt->error . "\n", 
                FILE_APPEND
            );
        }
        
        $stmt->close();
    }
}

echo json_encode([
    'code' => 200,
    'message' => 'Data processed',
    'success' => $success_count,
    'errors' => $error_count
]);
?>