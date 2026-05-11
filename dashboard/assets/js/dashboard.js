function updateDateTime() {

    const now = new Date();

    const options = {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: 'numeric',
        minute: 'numeric'
    };

    document.getElementById('datetime').innerHTML =
        now.toLocaleDateString('en-US', options);
}

updateDateTime();

setInterval(updateDateTime, 1000);


/*
=====================================
DEMO SLOT UPDATE
Replace with AJAX + Database later
=====================================
*/

const slotData = {
    slot1: 1,
    slot2: 1,
    slot3: 1,
    slot4: 0
};

function updateDashboard() {

    let occupied = 0;

    Object.values(slotData).forEach(status => {
        if(status === 1) occupied++;
    });

    const available = 4 - occupied;

    document.getElementById('occupied-count').innerText = occupied;
    document.getElementById('available-count').innerText = available;
    document.getElementById('available-slots').innerText = available;

    const rate = Math.round((occupied / 4) * 100);

    document.getElementById('occupancy-rate').innerText = rate + '%';


    if(available === 0) {

        document.getElementById('parking-status').innerText = 'FULL';
        document.getElementById('parking-message').innerText =
            'No available parking slots';

    } else {

        document.getElementById('parking-status').innerText = 'OPEN';
        document.getElementById('parking-message').innerText =
            available + ' parking slots available';
    }
}

updateDashboard();