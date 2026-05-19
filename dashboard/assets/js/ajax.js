const API_BASE_URL = "/smart-parking/api/";
const UPDATE_INTERVAL = 2000;

function safeSet(id, value) {
    const el = document.getElementById(id);
    if (el) el.innerText = value;
}

function getActiveFilters() {
    const filters = {};
    document.querySelectorAll('.filter-dropdown').forEach(dropdown => {
        const key = dropdown.dataset.filter;
        const checked = [];
        dropdown.querySelectorAll('input[type="checkbox"]:checked').forEach(cb => {
            checked.push(cb.value);
        });
        if (checked.length) {
            filters[key] = checked;
        }
    });
    return filters;
}

function buildFilterQuery() {
    const filters = getActiveFilters();
    const params = new URLSearchParams();
    Object.entries(filters).forEach(([k, vals]) => {
        vals.forEach(v => params.append(k + '[]', v));
    });
    const qs = params.toString();
    return qs ? '?' + qs : '';
}

function updateDropdownCounts() {
    document.querySelectorAll('.filter-dropdown').forEach(dropdown => {
        const count = dropdown.querySelectorAll('input[type="checkbox"]:checked').length;
        const badge = dropdown.querySelector('.dropdown-count');
        if (badge) {
            badge.textContent = count ? '(' + count + ')' : '';
        }
    });
}

function fetchSlots() {
    const qs = buildFilterQuery();

    fetch(API_BASE_URL + "get_slots.php" + qs)
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

function updateSlotsUI(slots) {
    document.querySelectorAll('.slot').forEach(el => el.classList.add('hidden'));

    slots.forEach(slot => {
        const el = document.getElementById("slot" + slot.sensor_id);
        if (!el) return;

        el.classList.remove('hidden');

        const btn = el.querySelector("button");
        const isOccupied = (slot.status == 0);

        el.className = "slot" + (isOccupied ? " occupied" : " available");

        if (btn) {
            btn.innerText = isOccupied ? "OCCUPIED" : "AVAILABLE";
        }
    });
}

function updateStatsFromSlots(slots) {
    let occupied = 0;
    let total = slots.length;

    slots.forEach(s => {
        if (s.status == 0) occupied++;
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

// Close all dropdowns
function closeAllDropdowns() {
    document.querySelectorAll('.filter-dropdown.open').forEach(d => d.classList.remove('open'));
}

// Toggle dropdown on click
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.dropdown-toggle').forEach(toggle => {
        toggle.addEventListener('click', function (e) {
            e.stopPropagation();
            const dropdown = this.closest('.filter-dropdown');
            const isOpen = dropdown.classList.contains('open');

            closeAllDropdowns();

            if (!isOpen) {
                dropdown.classList.add('open');
            }
        });
    });

    // Re-fetch on checkbox change
    document.querySelectorAll('.filter-dropdown input[type="checkbox"]').forEach(cb => {
        cb.addEventListener('change', function () {
            updateDropdownCounts();
            fetchSlots();
        });
    });

    // Close dropdowns on outside click
    document.addEventListener('click', function () {
        closeAllDropdowns();
    });

    console.log("Dashboard JS Loaded ✔");
    updateDropdownCounts();
    fetchSlots();
    setInterval(fetchSlots, UPDATE_INTERVAL);
});
