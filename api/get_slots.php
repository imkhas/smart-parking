<?php
header('Content-Type: application/json');
include '../includes/db_connect.php';

$where = [];
$params = [];
$types = '';

if (isset($_GET['status'])) {
    $vals = is_array($_GET['status']) ? $_GET['status'] : [$_GET['status']];
    $vals = array_values(array_filter(array_map('intval', $vals), function ($v) { return $v === 0 || $v === 1; }));
    if ($vals) {
        $placeholders = implode(',', array_fill(0, count($vals), '?'));
        $where[] = "sd.status IN ($placeholders)";
        $params = array_merge($params, $vals);
        $types .= str_repeat('i', count($vals));
    }
}

if (isset($_GET['type'])) {
    $vals = is_array($_GET['type']) ? $_GET['type'] : [$_GET['type']];
    $vals = array_values(array_filter($vals, 'strlen'));
    if ($vals) {
        $placeholders = implode(',', array_fill(0, count($vals), '?'));
        $where[] = "ps.slot_type IN ($placeholders)";
        $params = array_merge($params, $vals);
        $types .= str_repeat('s', count($vals));
    }
}

if (isset($_GET['level'])) {
    $vals = is_array($_GET['level']) ? $_GET['level'] : [$_GET['level']];
    $vals = array_values(array_filter($vals, 'strlen'));
    if ($vals) {
        $placeholders = implode(',', array_fill(0, count($vals), '?'));
        $where[] = "ps.level IN ($placeholders)";
        $params = array_merge($params, $vals);
        $types .= str_repeat('s', count($vals));
    }
}

$sql = "SELECT sd.sensor_id, sd.status, sd.timestamp,
               ps.slot_name, ps.slot_type, ps.level, ps.location, ps.zone
        FROM sensor_data sd
        INNER JOIN parking_slots ps ON sd.sensor_id = ps.sensor_id";

if ($where) {
    $sql .= ' WHERE ' . implode(' AND ', $where);
}

$sql .= ' ORDER BY sd.sensor_id ASC';

$stmt = $conn->prepare($sql);
if ($params) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

$slots = [];
while ($row = $result->fetch_assoc()) {
    $slots[] = [
        'sensor_id' => (int)$row['sensor_id'],
        'status'    => (int)$row['status'],
        'slot_name' => $row['slot_name'] ?? '—',
        'slot_type' => $row['slot_type'] ?? '—',
        'level'     => $row['level'] ?? '—',
        'location'  => $row['location'] ?? '—',
        'zone'      => $row['zone'] ?? '—',
        'timestamp' => $row['timestamp']
    ];
}

echo json_encode($slots);
