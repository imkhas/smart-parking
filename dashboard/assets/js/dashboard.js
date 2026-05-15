// ===============================
// SMART PARKING DASHBOARD JS
// ===============================

const API_URL = "/smart-parking/api/get_slots.php";

// -------------------------------
// DATE & TIME
// -------------------------------
function updateDateTime() {

    const now = new Date();

    const options = {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit'
    };

    const timeEl = document.getElementById('datetime');

    if (timeEl) {
        timeEl.innerHTML = now.toLocaleDateString('en-US', options);
    }
}

updateDateTime();
setInterval(updateDateTime, 1000);

// -------------------------------
// SAFE UI UPDATE
// -------------------------------
function safeSet(id, value) {
    const el = document.getElementById(id);
    if (el) el.innerText = value;
}

// -------------------------------
// LOAD SLOTS
// -------------------------------
function loadSlots() {

    fetch(API_URL)
        .then(res => {
            if (!res.ok) throw new Error("HTTP Error: " + res.status);
            return res.json();
        })
        .then(data => {

            let occupied = 0;
            let total = data.length;

            data.forEach(slot => {

                const slotDiv = document.getElementById("slot" + slot.slot_id);
                if (!slotDiv) return;

                const button = slotDiv.querySelector("button");

                // ✅ FIXED LOGIC
                if (slot.status == 0) {

                    // OCCUPIED
                    slotDiv.className = "slot occupied";
                    if (button) button.innerText = "OCCUPIED";
                    occupied++;

                } else {

                    // AVAILABLE
                    slotDiv.className = "slot available";
                    if (button) button.innerText = "AVAILABLE";
                }
            });

            let available = total - occupied;
            let rate = total > 0 ? Math.round((occupied / total) * 100) : 0;

            safeSet('occupied-count', occupied);
            safeSet('available-count', available);
            safeSet('available-slots', available);
            safeSet('occupancy-rate', rate + '%');

            safeSet('parking-status', available === 0 ? "FULL" : "OPEN");

            safeSet(
                'parking-message',
                available === 0
                    ? "No available parking slots"
                    : available + " slots available"
            );

            const loadingText = document.getElementById("loading");
            if (loadingText) loadingText.style.display = "none";

        })
        .catch(err => {
            console.error("FETCH ERROR:", err);
            safeSet('parking-status', "ERROR");
            safeSet('parking-message', "Failed to load parking data");
        });
}

// -------------------------------
// AUTO REFRESH
// -------------------------------
loadSlots();
setInterval(loadSlots, 2000);