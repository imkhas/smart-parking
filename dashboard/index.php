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

<div class="container">

    <!-- HEADER -->
    <div class="header">
        <h1>Smart Parking Dashboard</h1>

        <div class="datetime">
            <i class="fa-solid fa-calendar-days"></i>
            <span id="datetime"></span>
        </div>
    </div>

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
                <h1><span id="available-slots">-</span>/4</h1>
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

        <div class="slots-grid" id="slots-container">

            <!-- SLOT 1 -->
            <div class="slot available" id="slot1" data-sensor-id="1" data-slot-type="Standard Parking" data-level="Level - 1">
                <h3>SLOT 1 <span class="slot-type-badge slot-type-standard">Standard</span></h3>
                <i class="fa-solid fa-car-side"></i>
                <button>AVAILABLE</button>
            </div>

            <!-- SLOT 2 -->
            <div class="slot available" id="slot2" data-sensor-id="2" data-slot-type="Disabled Parking" data-level="Level - 2">
                <h3>SLOT 2 <span class="slot-type-badge slot-type-disabled">Disabled</span></h3>
                <i class="fa-solid fa-wheelchair"></i>
                <button>AVAILABLE</button>
            </div>

            <!-- SLOT 3 -->
            <div class="slot available" id="slot3" data-sensor-id="3" data-slot-type="Womens Parking" data-level="Level - Ground">
                <h3>SLOT 3 <span class="slot-type-badge slot-type-womens">Women's</span></h3>
                <i class="fa-solid fa-person-dress"></i>
                <button>AVAILABLE</button>
            </div>

            <!-- SLOT 4 -->
            <div class="slot available" id="slot4" data-sensor-id="4" data-slot-type="EV Charging Parking" data-level="Level - Ground">
                <h3>SLOT 4 <span class="slot-type-badge slot-type-ev">EV</span></h3>
                <i class="fa-solid fa-charging-station"></i>
                <button>AVAILABLE</button>
            </div>

        </div>

    </div>

</div>

<!-- JavaScript Files -->
<script src="assets/js/dashboard.js"></script>
<script src="assets/js/ajax.js"></script>

</body>
</html>