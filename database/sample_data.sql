-- ================================================
-- Sample Data for Testing
-- ================================================

USE smart_parking;

-- Update slots to simulate real scenario
UPDATE parking_slots SET status = 1 WHERE slot_id = 1;
UPDATE parking_slots SET status = 1 WHERE slot_id = 2;
UPDATE parking_slots SET status = 1 WHERE slot_id = 3;
UPDATE parking_slots SET status = 0 WHERE slot_id = 4;

-- Insert historical logs for testing
INSERT INTO parking_logs (slot_id, status, timestamp) VALUES
(1, 1, '2024-01-15 08:30:00'),
(2, 1, '2024-01-15 08:45:00'),
(3, 1, '2024-01-15 09:00:00'),
(1, 0, '2024-01-15 10:30:00'),
(1, 1, '2024-01-15 11:00:00'),
(4, 1, '2024-01-15 12:00:00'),
(4, 0, '2024-01-15 14:00:00');

-- Verify data
SELECT * FROM parking_slots;
SELECT * FROM parking_logs ORDER BY timestamp DESC LIMIT 10;