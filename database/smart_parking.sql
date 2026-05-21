-- =====================================================
-- SMART PARKING SYSTEM DATABASE
-- SIMPLE VERSION
-- =====================================================

CREATE DATABASE IF NOT EXISTS smart_parking;

USE smart_parking;

-- =====================================================
-- DROP TABLES
-- =====================================================

DROP TABLE IF EXISTS parking_logs;
DROP TABLE IF EXISTS parking_slots;
DROP TABLE IF EXISTS sensor_data;

-- =====================================================
-- TABLE: sensor_data
-- =====================================================
-- status:
-- 0 = occupied
-- 1 = available
-- =====================================================

CREATE TABLE sensor_data (

    id INT AUTO_INCREMENT PRIMARY KEY,

    sensor_id INT NOT NULL UNIQUE,

    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    status TINYINT(1) NOT NULL
    COMMENT '0 = occupied, 1 = available',

    battery FLOAT DEFAULT NULL
    COMMENT 'Battery level 0.0–100.0 (%)'

);

-- =====================================================
-- TABLE: parking_slots
-- =====================================================
-- id:       auto-increment primary key
-- sensor_id: nullable, unique — one sensor per slot max
-- FK SET NULL so slots survive sensor removal
-- =====================================================

CREATE TABLE parking_slots (

    id INT AUTO_INCREMENT PRIMARY KEY,

    sensor_id INT UNIQUE,

    slot_name VARCHAR(50) NOT NULL,

    location VARCHAR(100) NOT NULL,

    level VARCHAR(50) NOT NULL,

    zone VARCHAR(100) NOT NULL,

    slot_type VARCHAR(50) NOT NULL,

    FOREIGN KEY (sensor_id)
    REFERENCES sensor_data(sensor_id)
    ON DELETE SET NULL
    ON UPDATE CASCADE

);

-- =====================================================
-- INSERT SENSOR DATA
-- =====================================================

INSERT INTO sensor_data (
    sensor_id,
    status
)
VALUES
(1, 1),
(2, 1),
(3, 1),
(4, 1);

-- =====================================================
-- INSERT PARKING SLOT DETAILS
-- =====================================================

INSERT INTO parking_slots (
    sensor_id,
    slot_name,
    location,
    level,
    zone,
    slot_type
)
VALUES

(
    1,
    'Parking Slot - A1',
    'Left Wing',
    'Level - 1',
    'Zone A - Main Entrance Area',
    'Standard Parking'
),

(
    2,
    'Parking Slot - A2',
    'Left Wing',
    'Level - 2',
    'Zone A - Main Entrance Area',
    'Disabled Parking'
),

(
    3,
    'Parking Slot - B1',
    'Right Wing',
    'Level - Ground',
    'Zone B - East Section',
    'Womens Parking'
),

(
    4,
    'Parking Slot - B2',
    'Right Wing',
    'Level - Ground',
    'Zone B - East Section',
    'EV Charging Parking'
);

-- =====================================================
-- VIEW DATA
-- =====================================================

SELECT

    sd.sensor_id,

    CASE
        WHEN sd.status = 0 THEN 'Occupied'
        WHEN sd.status = 1 THEN 'Available'
    END AS parking_status,

    ps.slot_name,

    ps.location,

    ps.level,

    ps.zone,

    ps.slot_type,

    sd.timestamp

FROM sensor_data sd

LEFT JOIN parking_slots ps
ON sd.sensor_id = ps.sensor_id

ORDER BY sd.sensor_id;