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
                        <th>Last Update</th>
                        <th>Health</th>
                        <th>Alert</th>
                    </tr>
                </thead>
                <tbody id="health-body">
                    <tr><td colspan="8" style="text-align:center; padding:40px; color:#888;">Loading...</td></tr>
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

</div>

<footer class="footer">
    <p>© 2026 LEGA Company. Smart Parking System. All rights reserved.</p>
</footer>


<script src="dashboard/assets/js/admin.js"></script>

</body>
</html>
