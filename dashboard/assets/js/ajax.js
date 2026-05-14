// ================================================
// SMART PARKING REAL-TIME DASHBOARD (FIXED)
// ================================================

// Use ABSOLUTE PATH (IMPORTANT)
const API_BASE_URL = "/smart-parking/api/";
const UPDATE_INTERVAL = 2000;


// ===============================
// FETCH SLOTS (MAIN FUNCTION)
// ===============================
function fetchSlots() {

    fetch(API_BASE_URL + "get_slots.php")
        .then(res => {

            if (!res.ok) {
                throw new Error("HTTP Error: " + res.status);
            }

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


// ===============================
// UPDATE SLOT UI
// ===============================
function updateSlotsUI(slots) {

    slots.forEach(slot => {

        const el = document.getElementById("slot" + slot.slot_id);

        if (!el) return;

        const isOccupied = slot.status == 1;

        el.className = isOccupied ? "slot occupied" : "slot available";

        const btn = el.querySelector("button");

        if (btn) {
            btn.innerText = isOccupied ? "OCCUPIED" : "AVAILABLE";
        }
    });
}


// ===============================
// CALCULATE STATS FROM SLOTS
// (NO NEED EXTRA API)
// ===============================
function updateStatsFromSlots(slots) {

    let occupied = 0;
    let total = slots.length;

    slots.forEach(s => {
        if (s.status == 1) occupied++;
    });

    let available = total - occupied;
    let rate = total > 0 ? Math.round((occupied / total) * 100) : 0;

    safeSet("occupied-count", occupied);
    safeSet("available-count", available);
    safeSet("available-slots", available);
    safeSet("occupancy-rate", rate + "%");

    safeSet("parking-status", available === 0 ? "FULL" : "OPEN");

    safeSet("parking-message",
        available === 0
            ? "No available parking slots"
            : available + " slots available"
    );
}


// ===============================
// SAFE DOM UPDATE
// ===============================
function safeSet(id, value) {
    const el = document.getElementById(id);
    if (el) el.innerText = value;
}


// ===============================
// START SYSTEM
// ===============================
document.addEventListener("DOMContentLoaded", function () {

    console.log("Dashboard JS Loaded ✔");

    fetchSlots();

    setInterval(fetchSlots, UPDATE_INTERVAL);
});