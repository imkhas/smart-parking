<?php
header('Content-Type: application/json');
include 'includes/db_connect.php';

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
        
        // Battery reading (sid=100)
        // Arduino sends Vin as float → convert to percentage
        if ($sid == 100) {
            $vin = $value;  // raw voltage (e.g. 4.3V)
            $pct = (($vin - 2.8) / 1.7) * 100;
            $battery = round($pct, 1);
            if ($battery >= 0 && $battery <= 100) {
                $stmt = $conn->prepare("UPDATE sensor_data SET battery = ?");
                $stmt->bind_param("d", $battery);
                if ($stmt->execute()) {
                    $success++;
                } else {
                    $errors++;
                }
                $stmt->close();
            }
            continue;
        }

        // Convert sid to slot_id: 200→1, 201→2, 202→3, 203→4
        $slot_id = $sid - 199;
        $status = intval($value);  // 0=occupied, 1=available
        
        // Debounce: skip if last change was < 3 seconds ago
        $check = $conn->prepare("SELECT UNIX_TIMESTAMP(timestamp) FROM sensor_data WHERE sensor_id = ?");
        $check->bind_param("i", $slot_id);
        $check->execute();
        $check->bind_result($cur_ts);
        $check->fetch();
        $check->close();
        
       // if ($cur_ts && ($timestamp - $cur_ts) < 3) {
       //     continue;
       // }
        
        // Update database
        $stmt = $conn->prepare("UPDATE sensor_data SET status = ?, timestamp = FROM_UNIXTIME(?) WHERE sensor_id = ?");
        $stmt->bind_param("iii", $status, $timestamp, $slot_id);
        
        if ($stmt->execute()) {
            $success++;
        } else {
            $errors++;
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