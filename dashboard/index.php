<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Smart Parking Dashboard</title>

    <link rel="stylesheet" href="assets/css/style.css">

    <!-- Font Awesome -->
    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body>

<nav class="navbar">
    <div class="nav-left">
        <h1><i class="fa-solid fa-square-parking"></i> LEGA Smart-Parking</h1>
    </div>
    <div class="nav-right">
        <div class="datetime">
            <i class="fa-solid fa-calendar-days"></i>
            <span id="datetime"></span>
        </div>
        <a href="../admin.php" class="admin-link" title="Admin Panel">
            <i class="fa-solid fa-gear"></i>
        </a>
    </div>
</nav>

<div class="container">

    <!-- TOP STATUS CARDS -->
    <div class="top-cards">

        <div class="status-card red-card">
            <div class="icon-circle">
                <i class="fa-solid fa-car-side"></i>
            </div>

            <div>
                <h3>PARKING STATUS</h3>
                <h1 id="parking-status">LOADING...</h1>
                <p id="parking-message">Checking availability...</p>
            </div>
        </div>

        <div class="status-card blue-card">
            <div class="icon-circle">
                <i class="fa-solid fa-square-parking"></i>
            </div>

            <div>
                <h3>AVAILABLE SLOTS</h3>
                <h1><span id="available-slots">-</span>/<span id="total-slots">-</span></h1>
                <p>Click any slot for details</p>
            </div>
        </div>

    </div>

    <!-- FILTER BAR -->
    <div class="filter-bar">

        <div class="filter-dropdown" data-filter="status">
            <button class="dropdown-toggle">
                <i class="fa-solid fa-filter"></i> Status <span class="dropdown-count"></span>
                <i class="fa-solid fa-chevron-down"></i>
            </button>
            <div class="dropdown-menu">
                <label class="dropdown-item">
                    <input type="checkbox" value="1"> Available
                </label>
                <label class="dropdown-item">
                    <input type="checkbox" value="0"> Occupied
                </label>
            </div>
        </div>

        <div class="filter-dropdown" data-filter="type">
            <button class="dropdown-toggle">
                <i class="fa-solid fa-tag"></i> Type <span class="dropdown-count"></span>
                <i class="fa-solid fa-chevron-down"></i>
            </button>
            <div class="dropdown-menu">
                <label class="dropdown-item">
                    <input type="checkbox" value="Standard Parking"> Standard
                </label>
                <label class="dropdown-item">
                    <input type="checkbox" value="Womens Parking"> Women's
                </label>
                <label class="dropdown-item">
                    <input type="checkbox" value="Disabled Parking"> Disabled
                </label>
                <label class="dropdown-item">
                    <input type="checkbox" value="EV Charging Parking"> EV Charging
                </label>
                <label class="dropdown-item">
                    <input type="checkbox" value="Compact Parking"> Compact
                </label>
            </div>
        </div>

        <div class="filter-dropdown" data-filter="level">
            <button class="dropdown-toggle">
                <i class="fa-solid fa-layer-group"></i> Level <span class="dropdown-count"></span>
                <i class="fa-solid fa-chevron-down"></i>
            </button>
            <div class="dropdown-menu">
                <label class="dropdown-item">
                    <input type="checkbox" value="Level - Ground"> Ground
                </label>
                <label class="dropdown-item">
                    <input type="checkbox" value="Level - 1"> Level 1
                </label>
                <label class="dropdown-item">
                    <input type="checkbox" value="Level - 2"> Level 2
                </label>
            </div>
        </div>

    </div>

    <!-- SLOT OVERVIEW -->
    <div class="section-card">

        <h2>PARKING SLOTS OVERVIEW <span id="filter-label"></span></h2>

        <div class="slots-grid" id="slots-container"></div>

    </div>

</div>

<footer class="footer">
    <p>© 2026 LEGA Company. Smart Parking System. All rights reserved.</p>
</footer>

<!-- JavaScript Files -->
<script src="assets/js/dashboard.js?v=2"></script>
<script src="assets/js/ajax.js?v=2"></script>

</body>
</html>
