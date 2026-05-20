<?php
// Shared sensor health monitoring configuration

// Set timezone so strtotime() interprets DB timestamps correctly
date_default_timezone_set('Asia/Kuala_Lumpur');

// Hours without data before a sensor is considered offline
define('OFFLINE_THRESHOLD', 24);

// Hours continuously occupied before flagged as overstay
define('OVERSTAY_THRESHOLD', 0.01);

// Absolute log path — works from any including script
define('LOG_FILE', __DIR__ . '/../logs/sensor_health.log');
