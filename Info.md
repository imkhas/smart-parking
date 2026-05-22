# Smart Parking System — File Reference

## Root (PHP Pages)

| File | Purpose |
|------|---------|
| `sensor_data.php` | POST endpoint — receives CSV-formatted sensor readings from the BLE gateway, debounces, writes to `sensor_data` table |
| `admin.php` | Admin panel — three tabs: Sensor Health, Manual Override, Slot Management (CRUD) |
| `login.php` | Admin login page — username/password form, redirects to `admin.php` |

## `/api/` — REST Endpoints (JSON)

| File | Purpose |
|------|---------|
| `get_slots.php` | GET — returns assigned parking slots with sensor status (dashboard data source) |
| `get_slot_details.php` | GET — single slot details for the detail modal popup |
| `get_sensor_health.php` | GET — all sensors with online/offline/overstay health status |
| `get_slot_mappings.php` | GET — slot-to-sensor mapping table for admin panel |
| `get_active_sensors.php` | GET — list of unassigned sensors and all sensors for dropdowns |
| `add_slot.php` | POST — creates a new parking slot record |
| `assign_sensor.php` | POST — assigns a sensor to a slot (auto-clears previous assignment) |
| `edit_slot.php` | POST — edits slot metadata and reassigns/unassigns sensor |
| `delete_slot.php` | POST — deletes a parking slot by ID |
| `update_slot.php` | GET — manual occupied/available override for maintenance |

## `/includes/` — Backend Config

| File | Purpose |
|------|---------|
| `db_connect.php` | MySQL connection (`admin`/`admin123`, database `smart_parking`) |
| `auth.php` | Session-based admin authentication |
| `health_config.php` | Shared thresholds: offline (1h), overstay (24h), log paths |

## `/database/` — Schema

| File | Purpose |
|------|---------|
| `smart_parking.sql` | Full schema — `sensor_data` table + `parking_slots` table with FK constraints |
| `sample_data.txt` | Test data inserts for 4 slots and sample logs |

## `/dashboard/` — Frontend

| File | Purpose |
|------|---------|
| `index.php` | Main public page — navbar, filter bar, slot grid, detail modal |
| `assets/css/style.css` | Dashboard layout, slot cards, filter bar, responsive grid |
| `assets/css/login.css` | Login page card styling |
| `assets/css/admin.css` | Admin tabs, health table, override table, slot management forms |
| `assets/css/mobile.css` | Mobile-responsive overrides |
| `assets/js/ajax.js` | Core logic — fetches slots every 2s, builds cards dynamically, handles filters |
| `assets/js/dashboard.js` | Live clock, slot detail modal popup |
| `assets/js/admin.js` | Tab switching, health table, manual override, slot CRUD |

## `/services/` — Background

| File | Purpose |
|------|---------|
| `serial_reader.php` | USB serial daemon — reads `/dev/ttyACM0`, parses CSV, updates DB directly |
| `sensor_health.php` | Cron job — logs OFFLINE (>1h) and OVERSTAY (>24h) alerts |

## `/11_gateway_POST/` — BLE Gateway (C on Linux)

| File | Purpose |
|------|---------|
| `main.c` | Entry point — BLE scan for `NAZHAN`, connect, subscribe to notifications, POST to PHP |
| `ble.c` | BlueZ D-Bus integration — device discovery, GATT notification subscription, data reception |
| `ble.h` | BLE constants (`NAZHAN`, UUIDs) and function prototypes |
| `sense.c` | Ring buffer — queues sensor readings (64 entries) before HTTP POST |
| `sense.h` | Sensor struct (`sid`, `mid`, `timestamp`, `value`) and buffer config |
| `client.c` | HTTP client — TCP socket connect, POST request building, response parsing |
| `client.h` | HTTP constants (`localhost:80`), error codes, function prototypes |
| `sys.c` | System init — starts BLE, SENSE, CLIENT modules; monotonic ms clock |
| `sys.h` | Macros: `SYS_TICK`, `SYS_Error_Check`, fatal error handler |
| `global.h` | Master header — includes all sub-modules |
| `compile.sh` | Build script — `gcc` + `pkg-config dbus-1` |
| `POST.php` | Debug script — `print_r($_POST)` to inspect received data |

## `/carpark(new)/` — Current Arduino Firmware (with Battery)

| File | Purpose |
|------|---------|
| `carpark.ino` | Main loop — reads 4 IR sensors, reads battery ADC, sends 6-byte BLE frames every 3s |
| `battery.c` | Battery math — `battery_calc()` converts ADC to voltage, `batt_percent()` to percentage |
| `battery.h` | Battery function declarations |
| `adc.c` | ADC read — configures AVcc reference, reads analog pin |
| `adc.h` | ADC config — 15 channels, `ADC_BUF_SIZE` (4) for averaging |
| `swuart.c` | Software UART — bit-banged serial (interrupt-driven) to communicate with BLE module |
| `swuart.h` | SW UART config — TX/RX buffers (128 bytes each), pin assignments |
| `uart.c` | Hardware UART — ISR-driven serial at 9600 baud for debugging |
| `uart.h` | HW UART config — 128-byte ring buffers |
| `ble.c` | BLE control — GPIO enable pin, connection status, write to module via SWUART |
| `ble.h` | BLE pin definitions and function prototypes |
| `gpio.c` | GPIO — direct DDR/Port register manipulation for Arduino pins |
| `gpio.h` | GPIO mode/read/write functions and error codes |
| `timer0.c` | Timer0 — drives SWUART at ~38.4kHz (CTC mode) |
| `timer0.h` | Timer0 config |
| `timer2.c` | Timer2 — 1ms system tick (`SYS_TICK`) |
| `timer2.h` | Timer2 config |
| `twi.c` | TWI/I2C master — 100kHz bus, start/stop, data send/receive |
| `twi.h` | I2C function declarations |
| `delay.c` | Busy-wait delay using `SYS_TICK` |
| `delay.h` | Delay declaration |
| `err.c` | Error handler stub |
| `err.h` | Error handler header |
| `global.h` | Master header — includes all modules, `CHECK_ERROR_FATAL` macro |

## `/logs/` — Runtime

| File | Purpose |
|------|---------|
| `sensor_health.log` | Health check alerts (offline/overstay timestamps) |
| `post_raw.log` | Raw POST data dumps from sensor readings |
| `updates.log` | Successful database update records |
| `sensor_data_debug.log` | Debug log of incoming battery voltage readings |
