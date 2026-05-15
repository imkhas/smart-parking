// ================================================
// SMART PARKING REAL-TIME DASHBOARD
// ================================================

const API_BASE_URL = "/smart-parking/api/";
const UPDATE_INTERVAL = 2000;

// -------------------------------
// SAFE DOM UPDATE
// -------------------------------
function safeSet(id, value) {
    const el = document.getElementById(id);
    if (el) el.innerText = value;
}

// -------------------------------
// FETCH SLOTS
// -------------------------------
function fetchSlots() {

    fetch(API_BASE_URL + "get_slots.php")
        .then(res => {
            if (!res.ok) throw new Error("HTTP Error: " + res.status);
            return res.json();
        })
        .then(slots => {

            updateSlotsUI(slots);
            updateStatsFromSlots(slots);

        })
        .catch(err => {
            console.error("Error fetching slots:", err);
        });
}

// -------------------------------
// UPDATE SLOT UI
// -------------------------------
function updateSlotsUI(slots) {

    slots.forEach(slot => {

        const el = document.getElementById("slot" + slot.slot_id);
        if (!el) return;

        const btn = el.querySelector("button");

        // 0 = occupied, 1 = available
        const isOccupied = (slot.status == 0);

        el.className = isOccupied ? "slot occupied" : "slot available";

        if (btn) {
            btn.innerText = isOccupied ? "OCCUPIED" : "AVAILABLE";
        }
    });
}

// -------------------------------
// STATS CALCULATION
// -------------------------------
function updateStatsFromSlots(slots) {

    let occupied = 0;
    let total = slots.length;

    slots.forEach(s => {
        if (s.status == 0) {
            occupied++; // 0 = occupied
        }
    });

    let available = total - occupied;
    let rate = total > 0 ? Math.round((occupied / total) * 100) : 0;

    safeSet("occupied-count", occupied);
    safeSet("available-count", available);
    safeSet("available-slots", available);
    safeSet("occupancy-rate", rate + "%");

    safeSet("parking-status", available === 0 ? "FULL" : "OPEN");

    safeSet(
        "parking-message",
        available === 0
            ? "No available parking slots"
            : available + " slots available"
    );
}

// -------------------------------
// START
// -------------------------------
document.addEventListener("DOMContentLoaded", function () {

    console.log("Dashboard JS Loaded ✔");

    fetchSlots();
    setInterval(fetchSlots, UPDATE_INTERVAL);
});