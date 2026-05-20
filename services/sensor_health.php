<?php
// Run via cron: 0 * * * *  www-data  php /var/www/html/smart-parking/services/sensor_health.php
// Checks sensor health and logs issues

define('OFFLINE_THRESHOLD', 48);
define('OVERSTAY_THRESHOLD', 24);
define('LOG_FILE', __DIR__ . '/../logs/sensor_health.log');

include __DIR__ . '/../includes/db_connect.php';

$now = time();

$sql = "SELECT sensor_id, status, timestamp FROM sensor_data ORDER BY sensor_id";
$result = mysqli_query($conn, $sql);

while ($row = mysqli_fetch_assoc($result)) {
    $id = (int)$row['sensor_id'];
    $status = (int)$row['status'];
    $ts = strtotime($row['timestamp']);
    $hours_ago = ($now - $ts) / 3600;

    if ($hours_ago > OFFLINE_THRESHOLD) {
        $msg = sprintf("[%s] SENSOR #%d OFFLINE — No data for %.1f hours", date('Y-m-d H:i:s'), $id, $hours_ago);
        file_put_contents(LOG_FILE, $msg . "\n", FILE_APPEND);
        error_log($msg);
    } elseif ($status == 0 && $hours_ago > OVERSTAY_THRESHOLD) {
        $msg = sprintf("[%s] SENSOR #%d OVERSTAY — Occupied for %.1f hours", date('Y-m-d H:i:s'), $id, $hours_ago);
        file_put_contents(LOG_FILE, $msg . "\n", FILE_APPEND);
        error_log($msg);
    }
}

echo "Health check completed at " . date('Y-m-d H:i:s') . "\n";
