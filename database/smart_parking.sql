-- =====================================================
-- SMART PARKING SYSTEM DATABASE
-- =====================================================

CREATE DATABASE IF NOT EXISTS smart_parking;

USE smart_parking;

-- =====================================================
-- DROP TABLE IF EXISTS
-- =====================================================

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

    sensor_id INT NOT NULL,

    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    status TINYINT(1) NOT NULL
    COMMENT '0 = occupied, 1 = available'

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================
-- INSERT SAMPLE DATA
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
-- SHOW DATA
-- =====================================================

SELECT
    id,
    sensor_id,
    timestamp,
    status
FROM sensor_data;