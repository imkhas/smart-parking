# Smart Parking System Documentation

## 1. Introduction

The Smart Parking System is an IoT-based solution designed to monitor and manage parking slot availability in real time. The system uses Arduino sensors for vehicle detection, a PHP backend for processing data, a MySQL database for storage, and a web dashboard for visualization.

---

## 2. System Overview

The system follows a layered architecture:

**Data Flow:**
Arduino Sensors → Serial Communication → PHP Serial Reader → MySQL Database → PHP API → Web Dashboard

### Key Components:

* Arduino: Detects vehicle presence using sensors
* PHP Backend: Processes serial data and exposes APIs
* MySQL Database: Stores parking status and history
* Web Dashboard: Displays real-time parking information

---

## 3. Project Architecture

### Folder Structure

```
smart-parking/
│
├── arduino/            # Arduino sensor code
├── config/             # Configuration files
├── database/           # SQL schema and migrations
├── dashboard/          # Frontend web interface
├── api/                # REST API endpoints
├── services/           # Core backend services
├── includes/           # Reusable PHP components
├── logs/               # System logs
└── test/               # Testing scripts
```

---

## 4. Hardware Requirements

* Arduino Uno / Mega
* IR sensors
* USB cable for serial communication

---

## 5. Software Requirements

* Ubuntu Linux (recommended)
* Apache2 Web Server
* PHP 8+
* MySQL / MariaDB
* phpMyAdmin (optional)

---

## 6. Installation Guide

### Step 1: Clone Repository

```bash
git clone https://github.com/imkhas/smart-parking.git
cd smart-parking
```

### Step 2: Database Setup

Import database schema:

```bash
mysql -u root -p < database/smart_parking.sql
```

### Step 3: Configure Environment

Edit configuration file in `config/app.php` or `.env`:

```
DB_HOST=localhost
DB_USER=root
DB_PASS=your_password
DB_NAME=smart_parking
```

### Step 4: Deploy to Apache

Move project to web directory:

```bash
sudo mv smart-parking /var/www/html/
```

Set permissions:

```bash
sudo chown -R www-data:www-data /var/www/html/smart-parking
```

### Step 5: Upload Arduino Code

Upload `arduino/carpark_serial.ino` to Arduino board using Arduino IDE.

Ensure correct serial port:

* /dev/ttyUSB0
* /dev/ttyACM0

---

## 7. System Modules

### 7.1 Arduino Module

* Reads sensor data
* Sends slot status via serial communication

### 7.2 Serial Reader Service

* Reads incoming serial data
* Parses parking slot status
* Updates database in real-time

### 7.3 API Layer

Provides REST-style endpoints:

* Get slot status
* Update slot manually
* Fetch statistics
* Retrieve history logs

### 7.4 Dashboard Module

* Displays real-time parking availability
* Auto-refresh using AJAX (2-second interval)
* Shows analytics and history

---

## 8. API Documentation

### GET /api/get_slots.php

Returns current parking slot status.

### POST /api/update_slot.php

Updates slot status manually or via system.

### GET /api/get_statistics.php

Returns occupancy statistics.

### GET /api/get_history.php

Returns historical parking records.

---

## 9. Database Design

### Tables:

#### parking_slots

* id
* slot_number
* status (0 = empty, 1 = occupied)
* updated_at

#### parking_history

* id
* slot_number
* status
* timestamp

---

## 10. Data Flow Explanation

1. Sensor detects vehicle presence
2. Arduino sends data via USB serial
3. PHP serial_reader.php captures data
4. Parser processes slot status
5. Database is updated
6. API serves updated data
7. Dashboard displays real-time status

---

## 11. Testing

Run test scripts:

```bash
php test/test_database.php
php test/test_serial.php
php test/test_api.php
```

---

## 12. Logs System

System logs are stored in:

* logs/serial.log
* logs/error.log
* logs/system.log

These logs are useful for debugging hardware and backend communication.

---

## 13. Automation (Cron Jobs)

Used for:

* Log cleanup
* System monitoring
* Backup tasks

Example:

```bash
bash cron/cleanup_logs.sh
```

---

## 14. Future Enhancements

* Upgrade to ESP32 (WiFi-based IoT)
* Real-time WebSocket updates (remove AJAX polling)
* Cloud database integration
* Mobile application dashboard
* AI-based parking prediction system
* License plate recognition (LPR)

---

##
