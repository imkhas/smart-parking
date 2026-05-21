<?php
header('Content-Type: application/json');
include '../includes/health_config.php';
include '../includes/db_connect.php';

$now = time();

$stmt = $conn->prepare("
    SELECT sd.sensor_id, sd.status, sd.timestamp, sd.battery,
           ps.slot_name, ps.slot_type, ps.level, ps.location, ps.zone
    FROM sensor_data sd
    LEFT JOIN parking_slots ps ON sd.sensor_id = ps.sensor_id
    ORDER BY sd.sensor_id
");
$stmt->execute();
$result = $stmt->get_result();

$sensors = [];
while ($row = $result->fetch_assoc()) {
    $id = (int)$row['sensor_id'];
    $status = (int)$row['status'];
    $ts = strtotime($row['timestamp']);
    $hours_ago = ($now - $ts) / 3600;

    $health = 'online';
    $alert = '';

    if ($hours_ago > OFFLINE_THRESHOLD) {
        $health = 'offline';
        $alert = "No data for " . round($hours_ago, 1) . " hours";
    } elseif ($status == 0 && $hours_ago > OVERSTAY_THRESHOLD) {
        $health = 'overstay';
        $alert = "Occupied for " . round($hours_ago, 1) . " hours";
    }

    $sensors[] = [
        'sensor_id' => $id,
        'status'    => $status,
        'timestamp' => $row['timestamp'],
        'hours_ago' => round($hours_ago, 1),
        'health'    => $health,
        'alert'     => $alert,
        'battery'   => $row['battery'] !== null ? round((float)$row['battery'], 1) : null,
        'slot_name' => $row['slot_name'] ?? '—',
        'slot_type' => $row['slot_type'] ?? '—',
        'level'     => $row['level'] ?? '—',
        'location'  => $row['location'] ?? '—',
        'zone'      => $row['zone'] ?? '—'
    ];
}

$counts = ['online' => 0, 'offline' => 0, 'overstay' => 0];
foreach ($sensors as $s) {
    if ($s['health'] !== 'online') {
        $counts[$s['health']]++;
    } else {
        $counts['online']++;
    }
}

echo json_encode([
    'sensors' => $sensors,
    'summary' => $counts
]);
