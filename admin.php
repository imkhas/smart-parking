<?php
include 'includes/auth.php';

if (!isset($_SESSION['admin_auth'])) {
    header('Location: login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Smart Parking — Admin</title>
    <link rel="stylesheet" href="dashboard/assets/css/style.css">
    <link rel="stylesheet" href="dashboard/assets/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
</head>
<body>

<nav class="navbar">
    <div class="nav-left">
        <h1><i class="fa-solid fa-shield-halved"></i> LEGA Admin</h1>
    </div>
    <div class="nav-right">
        <a href="dashboard/index.php" class="admin-link" title="Back to Dashboard">
            <i class="fa-solid fa-gauge"></i>
        </a>
        <form method="POST" style="display:inline; margin:0;">
            <button type="submit" name="logout" class="logout-btn"><i class="fa-solid fa-right-from-bracket"></i> Logout</button>
        </form>
    </div>
</nav>

<div class="container">

    <div class="admin-header">
        <div>
            <h1><i class="fa-solid fa-gear"></i> Admin Panel</h1>
        </div>
    </div>

    <div class="top-cards">
        <div class="status-card blue-card">
            <div class="icon-circle"><i class="fa-solid fa-microchip"></i></div>
            <div>
                <h3>SENSORS ONLINE</h3>
                <h1 id="count-online">-</h1>
            </div>
        </div>
        <div class="status-card red-card">
            <div class="icon-circle"><i class="fa-solid fa-triangle-exclamation"></i></div>
            <div>
                <h3>SENSORS OFFLINE</h3>
                <h1 id="count-offline">-</h1>
            </div>
        </div>
        <div class="status-card" style="background: linear-gradient(to right, #f59e0b, #d97706);">
            <div class="icon-circle"><i class="fa-solid fa-clock"></i></div>
            <div>
                <h3>OVERSTAY ALERTS</h3>
                <h1 id="count-overstay">-</h1>
            </div>
        </div>
    </div>

    <div class="tab-nav">
        <button class="tab-btn active" data-tab="health"><i class="fa-solid fa-heart-pulse"></i> Sensor Health</button>
        <button class="tab-btn" data-tab="override"><i class="fa-solid fa-sliders"></i> Manual Override</button>
        <button class="tab-btn" data-tab="slots"><i class="fa-solid fa-layer-group"></i> Slot Management</button>
    </div>

    <div class="section-card tab-content active" id="tab-health">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:15px;">
            <h2 style="margin:0">Sensor Health Monitor</h2>
            <button class="refresh-btn" onclick="fetchHealth()"><i class="fa-solid fa-arrows-rotate"></i> Refresh</button>
        </div>
        <div style="overflow-x:auto;">
            <table class="health-table" id="health-table">
                <thead>
                    <tr>
                        <th>Sensor ID</th>
                        <th>Slot</th>
                        <th>Type</th>
                        <th>Location</th>
                        <th>Status</th>
                        <th>Battery</th>
                        <th>Last Update</th>
                        <th>Health</th>
                        <th>Alert</th>
                    </tr>
                </thead>
                <tbody id="health-body">
                    <tr><td colspan="9" style="text-align:center; padding:40px; color:#888;">Loading...</td></tr>
                </tbody>
            </table>
        </div>
    </div>

    <div class="section-card tab-content" id="tab-override">
        <h2>Manual Slot Override</h2>
        <p style="color:#888; margin-bottom:20px;">Force a slot status change. Useful for maintenance or sensor replacement.</p>
        <div style="overflow-x:auto;">
            <table class="health-table">
                <thead>
                    <tr>
                        <th>Sensor ID</th>
                        <th>Slot</th>
                        <th>Current Status</th>
                        <th>Set Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody id="override-body">
                    <tr><td colspan="5" style="text-align:center; padding:40px; color:#888;">Loading...</td></tr>
                </tbody>
            </table>
        </div>
    </div>

    <div class="section-card tab-content" id="tab-slots">
        <h2>Slot Management</h2>

        <div class="slot-mgmt-grid">
            <div class="mgmt-card">
                <h3><i class="fa-solid fa-plus-circle"></i> Add New Slot</h3>
                <form id="add-slot-form" class="mgmt-form">
                    <input type="text" name="slot_name" placeholder="Slot Name (e.g. Parking Slot - C1)" required>
                    <input type="text" name="location" placeholder="Location (e.g. Left Wing)" required>
                    <input type="text" name="level" placeholder="Level (e.g. Level - 1)" required>
                    <input type="text" name="zone" placeholder="Zone (e.g. Zone C)" required>
                    <select name="slot_type" required>
                        <option value="">— Select Type —</option>
                        <option value="Standard Parking">Standard</option>
                        <option value="Disabled Parking">Disabled</option>
                        <option value="Womens Parking">Women's</option>
                        <option value="EV Charging Parking">EV Charging</option>
                        <option value="Compact Parking">Compact</option>
                    </select>
                    <button type="submit" class="btn-primary"><i class="fa-solid fa-save"></i> Create Slot</button>
                </form>
            </div>

            <div class="mgmt-card">
                <h3><i class="fa-solid fa-plug"></i> Assign Sensor</h3>
                <form id="assign-sensor-form" class="mgmt-form">
                    <select name="slot_id" id="assign-slot-select" required>
                        <option value="">— Select Slot —</option>
                    </select>
                    <select name="sensor_id" id="assign-sensor-select" required>
                        <option value="">— Select Sensor —</option>
                    </select>
                    <button type="submit" class="btn-primary"><i class="fa-solid fa-link"></i> Assign</button>
                </form>
                <p class="hint" id="assign-hint">Only unassigned sensors are shown in the dropdown.</p>
            </div>

        </div>

        <h3 style="margin-top:30px;"><i class="fa-solid fa-table-cells"></i> Slot — Sensor Mapping</h3>
        <div style="overflow-x:auto;">
            <table class="health-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Slot Name</th>
                        <th>Type</th>
                        <th>Level</th>
                        <th>Location</th>
                        <th>Zone</th>
                        <th>Sensor</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody id="mapping-body">
                    <tr><td colspan="9" style="text-align:center; padding:40px; color:#888;">Loading...</td></tr>
                </tbody>
            </table>
        </div>
    </div>

</div>

<div class="modal-overlay" id="edit-modal" style="display:none;" onclick="closeEditModal()">
    <div class="modal-content" onclick="event.stopPropagation()" style="max-width:500px;">
        <span class="close-btn" onclick="closeEditModal()">&times;</span>
        <h2><i class="fa-solid fa-pen"></i> Edit Slot</h2>
        <form id="edit-slot-form" class="mgmt-form" style="margin-top:15px;">
            <input type="hidden" name="slot_id" id="edit-slot-id">
            <input type="text" name="slot_name" id="edit-slot-name" placeholder="Slot Name" required>
            <input type="text" name="location" id="edit-location" placeholder="Location" required>
            <input type="text" name="level" id="edit-level" placeholder="Level" required>
            <input type="text" name="zone" id="edit-zone" placeholder="Zone" required>
            <select name="slot_type" id="edit-slot-type" required>
                <option value="">— Select Type —</option>
                <option value="Standard Parking">Standard</option>
                <option value="Disabled Parking">Disabled</option>
                <option value="Womens Parking">Women's</option>
                <option value="EV Charging Parking">EV Charging</option>
                <option value="Compact Parking">Compact</option>
            </select>
            <select name="sensor_id" id="edit-sensor-id">
                <option value="">— None (unassign) —</option>
            </select>
            <button type="submit" class="btn-primary"><i class="fa-solid fa-save"></i> Save Changes</button>
        </form>
    </div>
</div>

<footer class="footer">
    <p>© 2026 LEGA Company. Smart Parking System. All rights reserved.</p>
</footer>


<script src="dashboard/assets/js/admin.js"></script>

</body>
</html>
