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
        <h1>Dashboard</h1>

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
                <h1 id="parking-status">FULL</h1>
                <p id="parking-message">No available parking slots</p>
            </div>
        </div>


        <div class="status-card blue-card">
            <div class="icon-circle">
                <i class="fa-solid fa-square-parking"></i>
            </div>

            <div>
                <h3>PARKING SLOT</h3>
                <h1><span id="available-slots">1</span>/4</h1>
                <p>Slot Available</p>
            </div>
        </div>

    </div>


    <!-- SLOT OVERVIEW -->
    <div class="section-card">

        <h2>PARKING SLOTS OVERVIEW</h2>

        <div class="slots-grid">

            <!-- SLOT 1 -->
            <div class="slot occupied" id="slot1">
                <h3>SLOT 1</h3>
                <i class="fa-solid fa-car-side"></i>
                <button>OCCUPIED</button>
            </div>


            <!-- SLOT 2 -->
            <div class="slot occupied" id="slot2">
                <h3>SLOT 2</h3>
                <i class="fa-solid fa-car-side"></i>
                <button>OCCUPIED</button>
            </div>

            <!-- SLOT 3 -->
            <div class="slot occupied" id="slot3">
                <h3>SLOT 3</h3>
                <i class="fa-solid fa-car-side"></i>
                <button>OCCUPIED</button>
            </div>

            <!-- SLOT 4 -->
            <div class="slot available" id="slot4">
                <h3>SLOT 4</h3>
                <i class="fa-solid fa-car-side"></i>
                <button>AVAILABLE</button>
            </div>

        </div>

    </div>


<!-- JavaScript Files -->
<script src="assets/js/dashboard.js"></script>
<script src="assets/js/ajax.js"></script>

</body>
</html>