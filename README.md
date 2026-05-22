# Smart Parking System

IoT-based parking monitoring system with real-time dashboard, admin panel, BLE gateway, and dual Arduino firmware.

---

## Quick Start (Fresh Ubuntu Install)

```bash
# 1. Install dependencies
sudo apt update
sudo apt install apache2 php php-mysqli mysql-server mysql-client pkg-config libdbus-1-dev libssl-dev

# 2. Start services
sudo systemctl enable apache2 mysql
sudo systemctl start apache2 mysql

# 3. Create MySQL user
sudo mysql -e "CREATE USER 'admin'@'localhost' IDENTIFIED BY 'admin123'; GRANT ALL ON *.* TO 'admin'@'localhost'; FLUSH PRIVILEGES;"

# 4. Clone & deploy
git clone https://github.com/imkhas/smart-parking.git
sudo mv smart-parking /var/www/html/
sudo chown -R www-data:www-data /var/www/html/smart-parking

# 5. Import database
mysql -u admin -p'admin123' < /var/www/html/smart-parking/database/smart_parking.sql

# 6. Open dashboard
# http://localhost/smart-parking/dashboard/index.php
```

---

## System Architecture

```
IR Sensors → Arduino Uno → BLE (AT-09) → Linux Gateway (C, D-Bus) → HTTP POST → MySQL
                                                              └─ Dashboard (AJAX 2s) → API → MySQL
```

### Data Flow

| Layer     | Component          | Details                                                    |
|-----------|--------------------|------------------------------------------------------------|
| Embedded  | Arduino Uno        | Reads IR sensors, ADC for battery, sends 6-byte BLE frames |
| Wireless  | AT-09 / HM-10      | BLE peripheral, advertises as NAZHAN                       |
| Gateway   | Linux (C)          | BlueZ D-Bus scan → connect → notify → POST to PHP          |
| Backend   | PHP                | Parses POST data, debounces, UPDATEs sensor_data           |
| Database  | MySQL              | sensor_data + parking_slots with FK                        |
| Frontend  | Dashboard + Admin  | AJAX polling, slot cards, filter dropdowns, management     |

---

## Folder Structure

```
smart-parking/
├── login.php                  # Admin login page
├── admin.php                  # Admin panel — Sensor Health, Manual Override, Slot Management
├── sensor_data.php            # POST endpoint — receives BLE gateway readings, parses CSV, writes to DB
├── session.txt                # File-based PHP session storage
├── Info.md                    # Project notes / changelog
│
├── dashboard/                 # Web frontend
│   ├── index.php              # Dashboard — filter bar, dynamic slot grid, detail modal
│   └── assets/
│       ├── css/
│       │   ├── style.css      # Layout, slot cards, filters, responsive grid
│       │   ├── admin.css      # Admin tabs, health/override/mgmt table styles
│       │   ├── login.css      # Login page card styling
│       │   └── mobile.css     # Mobile-responsive overrides
│       └── js/
│           ├── ajax.js        # Core — fetches slots every 2s, builds cards, handles filters
│           ├── dashboard.js   # Clock, slot detail modal popup
│           └── admin.js       # Tab switching, health table, override, slot CRUD
│
├── api/                       # REST JSON endpoints
│   ├── get_slots.php          # GET — assigned slots with status (dashboard data source)
│   ├── get_slot_details.php   # GET — single slot details for modal popup
│   ├── get_sensor_health.php  # GET — online/offline/overstay health per sensor
│   ├── get_slot_mappings.php  # GET — slot↔sensor mapping table (admin)
│   ├── get_active_sensors.php # GET — unassigned + all sensor lists (dropdowns)
│   ├── add_slot.php           # POST — creates a new parking slot record
│   ├── assign_sensor.php      # POST — assigns sensor to slot (clears previous)
│   ├── edit_slot.php          # POST — edits slot metadata + reassign/unassign sensor
│   ├── delete_slot.php        # POST — deletes a parking slot by ID
│   └── update_slot.php        # GET — manual status override for maintenance
│
├── 11_gateway_POST/           # BLE gateway (C on Linux)
│   ├── main.c                 # Entry — BLE scan for NAZHAN, connect, notify, POST to PHP
│   ├── ble.c / ble.h          # BlueZ D-Bus — device discovery, GATT notification subscription
│   ├── sense.c / sense.h      # Ring buffer — queues readings (64 entries) before HTTP POST
│   ├── client.c / client.h    # HTTP/1.1 socket client — POST request, response parsing
│   ├── sys.c / sys.h          # System init, monotonic millisecond clock
│   ├── global.h               # Master header — includes all sub-modules
│   ├── compile.sh             # Build script — gcc + pkg-config dbus-1
│   ├── gateway_POST           # Compiled binary
│   └── POST.php               # Debug — print_r($_POST) to inspect received data
│
├── carpark(new)/              # Arduino firmware (AVR C, with battery monitoring)
│   ├── carpark.ino            # Main loop — reads 4 IR sensors + battery ADC, sends BLE frames
│   ├── battery.c / battery.h  # ADC-to-voltage conversion, percentage calculation
│   ├── adc.c / adc.h          # ADC read — AVcc reference, averaging buffer
│   ├── swuart.c / swuart.h    # Software UART — bit-banged serial for BLE module (ISR-driven)
│   ├── uart.c / uart.h        # Hardware UART0 — ISR-driven serial at 9600 baud (debug)
│   ├── ble.c / ble.h          # BLE control — GPIO enable pin, connection status, SWUART write
│   ├── gpio.c / gpio.h        # GPIO — direct DDR/Port register manipulation
│   ├── timer0.c / timer0.h    # Timer0 — drives SWUART at ~38.4kHz (CTC)
│   ├── timer2.c / timer2.h    # Timer2 — 1ms system tick (SYS_TICK)
│   ├── twi.c / twi.h          # TWI/I2C master — 100kHz bus
│   ├── delay.c / delay.h      # Busy-wait delay using SYS_TICK
│   ├── err.c / err.h          # Error handler stub
│   └── global.h               # Master header — all modules + CHECK_ERROR_FATAL macro
│
├── includes/                  # PHP backend
│   ├── db_connect.php         # MySQL connection (admin/admin123, smart_parking)
│   ├── health_config.php      # Shared thresholds — OFFLINE (1h), OVERSTAY (24h), log paths
│   └── auth.php               # Session-based admin authentication
│
├── services/                  # Background services
│   ├── serial_reader.php      # USB serial daemon — reads /dev/ttyACM0, parses CSV
│   └── sensor_health.php      # Cron job — logs OFFLINE/OVERSTAY alerts
│
├── database/
│   ├── smart_parking.sql      # Schema — sensor_data + parking_slots with FK
│   └── sample_data.sql        # Test data inserts
│
├── logs/                      # Runtime logs
│   ├── sensor_health.log      # Health check alerts
│   ├── post_raw.log           # Raw POST data dumps
│   ├── sensor_data_debug.log  # Battery voltage debug log
│   ├── updates.log            # Successful DB update records
│   ├── error.log              # Placeholder
│   ├── serial.log             # Placeholder
│   ├── system.log             # Placeholder
│   └── post_parsed.log        # Placeholder
│
├── .gitignore
├── Info.md
└── README.md                  # This file
```

---

## Hardware Requirements

* Arduino Uno R3 (or compatible)
* 4× IR obstacle-avoidance sensors
* AT-09 or HM-10 BLE module (9600 baud, configured as NAZHAN)
* 5V power supply (USB hub or wall adapter recommended to avoid flapping)
* 100µF capacitor across 5V/GND near IR sensor rail (optional, stabilizes readings)
* (Optional) Voltage divider for battery monitoring

### Pinout (carpark(new))

| Component      | Arduino Pin |
|----------------|-------------|
| IR Sensor 1    | D9          |
| IR Sensor 2    | D11         |
| IR Sensor 3    | D13         |
| IR Sensor 4    | D7          |
| LED 1          | D8          |
| LED 2          | D10         |
| LED 3          | D12         |
| LED 4          | D6          |
| Full LED       | D16 (A2)    |
| BLE EN         | D4          |
| BLE STATE      | D5          |
| ADC Battery    | A0          |

---

## Software Requirements

* Ubuntu Linux (recommended) or any Linux with BlueZ ≥5.50
* Apache2 + PHP 8+ with mysqli
* MySQL / MariaDB
* For BLE gateway: `libdbus-1-dev`, `libssl-dev`, BlueZ running

---

## Installation

### Step 1: Database Setup

```bash
mysql -u admin -p < database/smart_parking.sql
```

Creates two tables:
- **sensor_data** — real-time status and timestamps (sensor_id 1–4, auto-created)
- **parking_slots** — metadata (name, type, level, zone, location) with FK to sensor_data

### Step 2: Configure Database Credentials

Edit `includes/db_connect.php`:
```php
$conn = mysqli_connect("localhost", "admin", "admin123", "smart_parking");
```

### Step 3: Upload Arduino Firmware

1. Open `carpark(new)/carpark.ino` in Arduino IDE
2. Select board: **Arduino Uno**
3. Select port (e.g. `/dev/ttyACM0`)
4. Upload

The BLE module must be pre-configured via AT commands:
```
AT+NAMENAZHAN      # Set device name
AT+ROLE0           # Peripheral mode
AT+UUID0xFFE0      # Service UUID
AT+CHAR0xFFE1      # Characteristic UUID
AT+BAUD0           # 9600 baud (default)
```

### Step 4: Compile & Run BLE Gateway

```bash
cd /var/www/html/smart-parking/11_gateway_POST
bash compile.sh
sudo ./gateway_POST
```

The gateway scans for NAZHAN for 60 seconds, connects, receives 6-byte notifications, and POSTs to `/sensor_data.php`.

### Step 5: Set Up Cron for Health Monitoring

```bash
# /etc/cron.d/smart-parking
0 * * * *   www-data   php /var/www/html/smart-parking/services/sensor_health.php
```

### Alternative: USB Serial Path

If using USB serial instead of BLE (e.g. for testing):

```bash
php /var/www/html/smart-parking/services/serial_reader.php
```

---

## Dashboard

**URL:** `http://localhost/smart-parking/dashboard/index.php`

### Features

- Real-time slot status (auto-refreshes every 2 seconds)
- Dynamic slot cards (generated from API — no hardcoded HTML)
- Filter dropdowns with multi-select:
  - **Status** — Available, Occupied
  - **Type** — Standard, Women's, Disabled, EV Charging
  - **Level** — Ground, Level 1, Level 2
- Click any slot card → modal with full details
- Event delegation for click handling (works on dynamically created elements)
- Parking status banner: OPEN / FULL
- Responsive layout

---

## Admin Panel

**URL:** `http://localhost/smart-parking/admin.php`
**Credentials:** `admin` / `admin123`

### Tabs

| Tab              | Description                                                          |
|------------------|----------------------------------------------------------------------|
| **Sensor Health** | Live health status (online/offline/overstay) with hours-since-last-update |
| **Manual Override** | Force a slot's status (available/occupied) for maintenance         |
| **Slot Management** | Add new slots, assign/unassign sensors, view mapping, delete slots |

### Slot Management

- **Add Slot** — Form with 5 fields (name, location, level, zone, type). Inserts row into `parking_slots` without a sensor.
- **Assign Sensor** — Dropdown of unassigned sensors → dropdown of slots. Clears previous assignment automatically (sensor_id set NULL on old slot, then set on new slot).
- **Mapping Table** — Shows all parking_slots with sensor info. Delete button with confirmation dialog.
- The dashboard uses `INNER JOIN` so only **assigned** slots display. Unassigned slots are invisible to end users.

### Sensor Health Thresholds

| Condition                   | Threshold | Badge    |
|-----------------------------|-----------|----------|
| No data in 24+ hours        | 24h       | OFFLINE  |
| Occupied for 24+ hours      | 24h       | OVERSTAY |
| Normal operation            | —         | ONLINE   |

> **Note:** Actual thresholds are defined in `includes/health_config.php` and can be adjusted without touching other files.

---

## API

### GET /api/get_slots.php

Returns **assigned** slots only (INNER JOIN). Supports filters:

| Parameter  | Type    | Example                                    |
|------------|---------|--------------------------------------------|
| `status[]` | int[]   | `?status[]=1` (available only)             |
| `type[]`   | string[]| `?type[]=Womens%20Parking`                 |
| `level[]`  | string[]| `?level[]=Level%20-%20Ground`              |

Response:
```json
[
  {
    "sensor_id": 1,
    "status": 1,
    "slot_name": "Parking Slot - A1",
    "slot_type": "Standard Parking",
    "level": "Level - 1",
    "location": "Left Wing",
    "zone": "Zone A - Main Entrance Area",
    "timestamp": "2026-05-15 03:00:00"
  }
]
```

### GET /api/get_slot_details.php

Single slot details for the modal.

| Parameter | Type | Required |
|-----------|------|----------|
| `slot_id` | int  | Yes      |

Response: `{ "success": true, "data": { ... } }`

### GET /api/get_sensor_health.php

Health status for all sensors (LEFT JOIN includes unassigned). Used by admin.

Response:
```json
{
  "sensors": [
    {
      "sensor_id": 1,
      "status": 0,
      "health": "online",
      "alert": "",
      "hours_ago": 1.5,
      "slot_name": "...",
      "slot_type": "..."
    }
  ],
  "summary": { "online": 3, "offline": 0, "overstay": 1 }
}
```

### GET /api/get_slot_mappings.php

All parking_slots with sensor LEFT JOIN — used by admin mapping table.

### GET /api/get_active_sensors.php

```json
{
  "unassigned": [ { "sensor_id": 2, ... } ],
  "all": [ { "sensor_id": 1, ... }, ... ]
}
```

### POST /api/add_slot.php

Create a new parking slot (no sensor assigned yet). Fields: `slot_name`, `location`, `level`, `zone`, `slot_type`.

### POST /api/assign_sensor.php

Assign a sensor to a slot. Clears any previous assignment automatically. Fields: `slot_id`, `sensor_id`.

### POST /api/delete_slot.php

Delete a slot by id. Field: `slot_id`.

### POST /sensor_data.php

Gateway endpoint. Parameter `data` contains comma-separated readings.

| Format                                  | Example                                       |
|-----------------------------------------|-----------------------------------------------|
| `data=<sid>,<mid>,<timestamp>,<value>;` | `data=200,0,1747785600,0.000000;201,0,...;`   |

- `sid`: 200–203 (converted to `slot_id = sid − 199`)
- `mid`: 0 (measurement index, unused by PHP)
- `timestamp`: Unix epoch from gateway
- `value`: 0.0 (occupied) or 1.0 (free)
- `sid=100`: battery percentage (ignored — falls outside 200–203 range)

Debounce: minimum 3 seconds between updates per sensor.

### GET /api/update_slot.php

Manual override.

| Parameter | Type | Required |
|-----------|------|----------|
| `slot_id` | int  | Yes      |
| `status`  | int  | Yes      |

---

## Database

### sensor_data

| Column     | Type          | Notes                        |
|------------|---------------|------------------------------|
| id         | INT           | Auto-increment PK            |
| sensor_id  | INT           | 1–4 (UNIQUE)                 |
| status     | TINYINT(1)    | 0 = occupied, 1 = available  |
| timestamp  | TIMESTAMP     | Auto-updates on change       |

### parking_slots

| Column     | Type          | Notes                              |
|------------|---------------|------------------------------------|
| id         | INT           | Auto-increment PK                  |
| sensor_id  | INT           | UNIQUE, NULLABLE, FK→sensor_data   |
| slot_name  | VARCHAR(50)   | e.g. "Parking Slot - A1"           |
| location   | VARCHAR(100)  | e.g. "Left Wing"                   |
| level      | VARCHAR(50)   | e.g. "Level - Ground"              |
| zone       | VARCHAR(100)  | e.g. "Zone A - Main Entrance Area" |
| slot_type  | VARCHAR(50)   | e.g. "Womens Parking"              |

FK: `ON DELETE SET NULL` — deleting a sensor_data row leaves the slot intact.

---

## BLE Gateway Protocol

### 6-Byte Frame (Arduino → Gateway)

| Offset | Field     | Description                       |
|--------|-----------|-----------------------------------|
| 0      | sid       | Sensor ID (200, 201, 202, 203)    |
| 1      | mid       | Measurement index (always 0)      |
| 2–5    | value     | IEEE 754 float (LSB first)        |

Gateway appends Unix timestamp via `time(NULL)` and POSTs to PHP.

### D-Bus Integration

The gateway uses BlueZ over D-Bus:
- **ObjectManager** `InterfacesAdded` signals for device discovery
- **Properties** `PropertiesChanged` for GATT notifications
- Adapter path hardcoded: `/org/bluez/hci1` (configurable in `ble.c`)

---

## Arduino Data Format (carpark(new))

6-byte BLE notification:

```
Byte 0: sid (200-203 for IR, 100 for battery)
Byte 1: mid (0 for IR, 1 for battery)
Byte 2-5: float32 value (0.0 = occupied, 1.0 = free)
```

The Arduino polls IR sensors every 3 seconds. On state change, it sends a 6-byte frame via BLE. Battery voltage is read via ADC every 12 seconds and sent as sid=100.

---

## Status Mapping

| DB Value | Meaning   | Dashboard Card          |
|----------|-----------|-------------------------|
| 0        | Occupied  | Red, OCCUPIED button    |
| 1        | Available | Green, AVAILABLE button |

---

## Troubleshooting

**Dashboard shows "FULL" when slots are available** — Hard refresh (Ctrl+Shift+R) to clear cached JS.

**No slots appear on dashboard** — Slots must have a sensor assigned. Go to Admin → Slot Management → Assign Sensor. Dashboard uses INNER JOIN and only shows assigned slots.

**Gateway can't find BLE device** — Run `bluetoothctl scan on` to check NAZHAN is advertising. Verify module is powered. Remove cached devices: `bluetoothctl remove C4:BE:84:2B:BF:ED`.

**Sensor readings flip rapidly** — Low voltage: use powered USB hub or 5V adapter. Add 100µF capacitor across 5V/GND near IR sensors.

**POST returns 404** — Gateway's target URL is hardcoded as `/sensor_data.php` in `client.h` (CLIENT_SERVER_NAME_DEFAULT = "localhost", port 80). Deploy to Apache root or change in `main.c`.

**Admin login not working** — Credentials are in `includes/auth.php`. Default: `admin` / `admin123`. Sessions are file-based in `session.txt`.
