# Smart Parking System

IoT-based parking monitoring system with real-time dashboard. Supports both **BLE gateway** and **USB serial** data paths.

---

## Quick Start (Fresh Ubuntu Install)

```bash
# 1. Install dependencies
sudo apt update
sudo apt install apache2 php php-mysqli mysql-server mysql-client pkg-config libdbus-1-dev libssl-dev

# 2. Start services
sudo systemctl enable apache2 mysql
sudo systemctl start apache2 mysql

# 3. Set up MySQL (use your own root password)
sudo mysql -e "ALTER USER 'root'@'localhost' IDENTIFIED BY 'your_root_password';"
sudo mysql -u root -p'your_root_password' -e "CREATE USER 'admin'@'localhost' IDENTIFIED BY 'admin123'; GRANT ALL ON *.* TO 'admin'@'localhost'; FLUSH PRIVILEGES;"

# 4. Clone & deploy
git clone https://github.com/imkhas/smart-parking.git
sudo mv smart-parking /var/www/html/
sudo chown -R www-data:www-data /var/www/html/smart-parking

# 5. Import database
mysql -u root -p'your_root_password' < /var/www/html/smart-parking/database/smart_parking.sql

# 6. Open dashboard
# http://localhost/smart-parking/dashboard/index.php
```

---

## System Architecture

```
IR Sensors → Arduino Uno → BLE (AT-09 module) → Linux Gateway (C) → HTTP POST → sensor_data.php → MySQL
                                                        └─ Dashboard (AJAX 2s poll) → API → MySQL
```

### Data Flow

| Layer | Path | Details |
|-------|------|---------|
| Embedded | Arduino | IR sensors, software UART for BLE |
| Gateway | Linux (C) | BLE scan → connect → notify → HTTP POST |
| Backend | PHP | Receives POST, debounces, updates DB |
| Database | MySQL | Slot status + parking metadata |
| Frontend | Dashboard | Real-time AJAX polling with filters |

---

## Folder Structure

```
smart-parking/
├── admin.php                # Admin panel (login protected, sensor health, override)
├── sensor_data.php          # POST endpoint: receives data from gateway
│
├── 11_gateway_POST/        # Linux BLE gateway (C application)
│   ├── compile.sh          # Build script (gcc + dbus-1)
│   ├── gateway_POST        # Compiled binary
│   ├── main.c              # Entry point: BLE scan → connect → POST loop
│   ├── ble.c / ble.h       # BlueZ D-Bus BLE communication
│   ├── sense.c / sense.h   # Ring buffer for sensor readings
│   ├── client.c / client.h # HTTP/1.1 socket client (POST/GET)
│   └── sys.c / sys.h       # System init, monotonic clock, fatal errors
│
├── carpark/                 # Arduino firmware (AVR C)
│   ├── carpark.ino          # Entry point (main)
│   ├── uart.c / uart.h      # Hardware UART0 ISR-driven
│   ├── swuart.c / swuart.h  # Software UART for BLE module
│   ├── gpio.c / gpio.h      # GPIO abstraction
│   ├── timer0.c / timer0.h  # Drives SWUART at ~38.4kHz
│   ├── timer2.c / timer2.h  # 1ms system tick (SYS_TICK)
│   ├── ble.c / ble.h        # BLE enable/state pin control
│   ├── adc.c / adc.h        # ADC init and read
│   ├── twi.c / twi.h        # TWI/I2C master
│   ├── delay.c / delay.h    # Blocking delay using SYS_TICK
│   └── err.c / err.h        # Error handler stub
│
├── api/                     # REST API endpoints
│   ├── get_slots.php        # GET: returns slot status (supports ?status[], ?type[], ?level[] filters)
│   ├── get_slot_details.php # GET: returns single slot details for modal (?slot_id=)
│   ├── get_sensor_health.php# GET: sensor health check (online/offline/overstay)
│   ├── update_slot.php      # GET: manual slot status update (?slot_id=&status=)
│   └── get_statistics.php   # (empty)
│
├── dashboard/               # Web dashboard frontend
│   ├── index.php            # Main dashboard (filter bar, slot grid, modal)
│   ├── assets/
│   │   ├── css/
│   │   │   └── style.css    # Main stylesheet + filter dropdowns + modal
│   │   └── js/
│   │       ├── ajax.js      # Fetches slots with filters, updates UI
│   │       └── dashboard.js # Clock, click handlers, modal display
│
├── database/
│   └── smart_parking.sql    # Schema + sample data
│
├── includes/
│   └── db_connect.php       # MySQL connection (edit credentials here)
│
├── services/
│   ├── serial_reader.php    # Alternative: USB serial daemon
│   └── sensor_health.php    # Background health check (run via cron)
│
├── logs/                    # System logs
└── README.md                # This file
```

---

## Hardware Requirements

* Arduino Uno or Mega
* 4× IR sensors
* AT-09 or HM-10 BLE module
* 5V power supply (USB hub or wall adapter recommended to avoid sensor flapping)
* 100µF capacitor across 5V/GND near IR sensor rail (optional, stabilizes readings)

---

## Software Requirements

* Ubuntu Linux (recommended)
* Apache2 Web Server
* PHP 8+ with mysqli extension
* MySQL / MariaDB
* For BLE gateway: BlueZ (dbus-1), OpenSSL

---

## Installation

### Step 1: Clone Repository

```bash
git clone https://github.com/imkhas/smart-parking.git
cd smart-parking
```

### Step 2: Database Setup

```bash
mysql -u root -p < database/smart_parking.sql
```

This creates:
- **sensor_data** — stores real-time slot status (sensor_id, status, timestamp)
- **parking_slots** — stores slot metadata (name, type, level, zone, location)

### Step 3: Configure Database Credentials

Edit `includes/db_connect.php` with your database credentials.

### Step 4: Deploy to Apache

```bash
sudo mv smart-parking /var/www/html/
sudo chown -R www-data:www-data /var/www/html/smart-parking
```

### Step 5: Upload Arduino Firmware

Open `carpark/carpark.ino` in Arduino IDE, select your board and port (`/dev/ttyACM0` or `/dev/ttyUSB0`), then upload.

The BLE module must be pre-configured to advertise as **NAZHAN** and use UUID `0000ffe0-0000-1000-8000-00805f9b34fb`.

### Step 6: Compile & Run BLE Gateway

```bash
cd /var/www/html/smart-parking/11_gateway_POST
./compile.sh
sudo ./gateway_POST
```

The gateway scans for 60 seconds, connects to the BLE device, receives sensor notifications, and POSTs data to `sensor_data.php`.

### Alternative: USB Serial Path

If using USB serial instead of BLE:

```bash
php /var/www/html/smart-parking/services/serial_reader.php
```

---

## Dashboard

Access at: `http://localhost/smart-parking/dashboard/index.php`

### Features

- Real-time slot status (auto-refreshes every 2 seconds)
- Filter dropdowns with multi-select checkboxes:
  - **Status** — Available, Occupied
  - **Type** — Standard, Women's, Disabled, EV Charging
  - **Level** — Ground, Level 1, Level 2
- Click any slot for detailed info modal
- Responsive layout for mobile

---

## Admin Panel

Access at: `http://localhost/smart-parking/admin.php`

Default credentials: `admin` / `admin123`

### Features

| Feature | Description |
|---------|-------------|
| **Sensor Health** | Real-time status of all sensors (online/offline/overstay) |
| **Manual Override** | Force a slot status change for maintenance |
| **Offline Detection** | Alerts when a sensor hasn't reported in 48+ hours |
| **Overstay Detection** | Alerts when a slot is occupied for 24+ hours |

### Sensor Health Thresholds

| Condition | Threshold | Health Status |
|-----------|-----------|---------------|
| No data in 48+ hours | 48h | 🔴 `OFFLINE` |
| Occupied for 24+ hours | 24h | 🟡 `OVERSTAY` |
| Normal operation | — | 🟢 `ONLINE` |

### Background Health Check (Cron)

Set up an hourly cron job to log sensor issues:

```bash
# /etc/cron.d/smart-parking
0 * * * *   www-data   php /var/www/html/smart-parking/services/sensor_health.php
```

Logs are written to `logs/sensor_health.log`.

---

## API

### GET /api/get_slots.php

Returns all slots with status and metadata. Supports optional filters:

| Parameter | Type | Example |
|-----------|------|---------|
| `status[]` | array | `?status[]=1` (available), `?status[]=0&status[]=1` |
| `type[]` | array | `?type[]=Womens+Parking` |
| `level[]` | array | `?level[]=Level+-+Ground` |

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

Returns details for a single slot.

| Parameter | Type | Required |
|-----------|------|----------|
| `slot_id` | int | Yes |

### GET /api/get_sensor_health.php

Returns health status for all sensors (used by admin panel).

Response:
```json
{
  "sensors": [
    {
      "sensor_id": 1,
      "status": 0,
      "health": "online",
      "alert": "",
      "hours_ago": 1.5
    }
  ],
  "summary": {
    "online": 3,
    "offline": 0,
    "overstay": 1
  }
}
```

### POST /sensor_data.php

Endpoint for gateway to push sensor readings.

| Parameter | Type | Format |
|-----------|------|--------|
| `data` | string | `,200,1,1778813047,1.0;` |

Format per entry: `,sid, mid, timestamp, value;`
- `sid`: 200–203 (converted to slot_id = sid − 199)
- `value`: 0.0 (occupied) or 1.0 (available)

### GET /api/update_slot.php

Manual slot status update via GET parameters.

| Parameter | Type | Required |
|-----------|------|----------|
| `slot_id` | int | Yes |
| `status` | int | Yes |

---

## Database

### sensor_data

| Column | Type | Notes |
|--------|------|-------|
| id | INT | Auto increment PK |
| sensor_id | INT | 1–4 (unique) |
| status | TINYINT(1) | 0 = occupied, 1 = available |
| timestamp | TIMESTAMP | Auto-updates on change |

### parking_slots

| Column | Type | Notes |
|--------|------|-------|
| sensor_id | INT | FK to sensor_data |
| slot_name | VARCHAR(50) | e.g. "Parking Slot - A1" |
| location | VARCHAR(100) | e.g. "Left Wing" |
| level | VARCHAR(50) | e.g. "Level - Ground" |
| zone | VARCHAR(100) | e.g. "Zone A - Main Entrance Area" |
| slot_type | VARCHAR(50) | e.g. "Womens Parking" |

---

## Status Mapping

| DB Value | Meaning | Dashboard |
|----------|---------|-----------|
| 0 | Occupied | Red card, OCCUPIED label |
| 1 | Available | Green card, AVAILABLE label |

---

## Sensor Debouncing

`sensor_data.php` enforces a 3-second minimum interval between updates to the same sensor. This prevents dashboard flicker when sensors produce noisy/flapping readings due to voltage instability at the detection threshold.

---

## Troubleshooting

**Dashboard shows "FULL" when slots are available** — Hard refresh (Ctrl+Shift+R) to clear cached JS.

**Gateway can't find BLE device** — Check `bluetoothctl scan on` in terminal. Verify the module is powered and advertising as `NAZHAN`. Remove cached devices: `bluetoothctl remove <MAC>`.

**Sensor readings flip rapidly** — Low voltage on Arduino. Use a powered USB hub or 5V wall adapter. Add a 100µF capacitor across 5V/GND near the IR sensor power rail.

**POST returns 404** — The gateway's target URL must match your web path (default: `/smart-parking/sensor_data.php`). See `11_gateway_POST/main.c`.

---

## Future Enhancements

- ESP32 WiFi-based IoT (replace BLE gateway)
- WebSocket real-time updates (replace AJAX polling)
- Camera-based parking detection
- Mobile application
- AI-based occupancy prediction
