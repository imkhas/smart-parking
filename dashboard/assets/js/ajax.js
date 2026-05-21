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
    const container = document.getElementById('slots-container');
    container.innerHTML = slots.map(slot => {
        const isOccupied = (slot.status == 0);
        const cls = "slot" + (isOccupied ? " occupied" : " available");

        const type = (slot.slot_type || '').toLowerCase();
        let badgeClass = 'slot-type-standard';
        let icon = 'fa-car-side';
        if (type.includes('disabled')) { badgeClass = 'slot-type-disabled'; icon = 'fa-wheelchair'; }
        else if (type.includes('ev') || type.includes('charging')) { badgeClass = 'slot-type-ev'; icon = 'fa-charging-station'; }
        else if (type.includes('women')) { badgeClass = 'slot-type-womens'; icon = 'fa-person-dress'; }
        else if (type.includes('compact')) { badgeClass = 'slot-type-compact'; icon = 'fa-car-side'; }

        const displayName = slot.slot_name !== '—' ? slot.slot_name : 'Slot ' + slot.sensor_id;
        const shortName = displayName.replace(/^Parking Slot - /i, '');
        const badgeText = slot.slot_type !== '—' ? slot.slot_type.replace(/ Parking$/, '') : '';

        return `
            <div class="${cls}" id="slot${slot.sensor_id}" data-sensor-id="${slot.sensor_id}" data-slot-type="${slot.slot_type}" data-level="${slot.level}">
                <h3>${shortName} ${badgeText ? '<span class="slot-type-badge ' + badgeClass + '">' + badgeText + '</span>' : ''}</h3>
                <i class="fa-solid ${icon}"></i>
                <button>${isOccupied ? "OCCUPIED" : "AVAILABLE"}</button>
            </div>
        `;
    }).join('');
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
    safeSet("total-slots", total);
    safeSet("occupancy-rate", rate + "%");

    safeSet("parking-status", available === 0 ? "FULL" : "OPEN");

    safeSet(
        "parking-message",
        available === 0
            ? "No available parking slots"
            : available + " slots available"
    );
}

function closeAllDropdowns() {
    document.querySelectorAll('.filter-dropdown.open').forEach(d => d.classList.remove('open'));
}

document.addEventListener("DOMContentLoaded", function () {
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

    document.querySelectorAll('.filter-dropdown input[type="checkbox"]').forEach(cb => {
        cb.addEventListener('change', function () {
            updateDropdownCounts();
            fetchSlots();
        });
    });

    document.addEventListener('click', function () {
        closeAllDropdowns();
    });

    console.log("Dashboard JS Loaded ✔");
    updateDropdownCounts();
    fetchSlots();
    setInterval(fetchSlots, UPDATE_INTERVAL);
});
