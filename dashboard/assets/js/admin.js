const HEALTH_API      = "/smart-parking/api/get_sensor_health.php";
const OVERRIDE_API    = "/smart-parking/api/update_slot.php";
const MAPPINGS_API    = "/smart-parking/api/get_slot_mappings.php";
const ACTIVE_API      = "/smart-parking/api/get_active_sensors.php";
const ADD_SLOT_API    = "/smart-parking/api/add_slot.php";
const ASSIGN_API      = "/smart-parking/api/assign_sensor.php";
const DELETE_API      = "/smart-parking/api/delete_slot.php";
const EDIT_API        = "/smart-parking/api/edit_slot.php";

document.querySelectorAll('.tab-btn').forEach(btn => {
    btn.addEventListener('click', function () {
        document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
        document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
        this.classList.add('active');
        document.getElementById('tab-' + this.dataset.tab).classList.add('active');

        if (this.dataset.tab === 'slots') {
            loadSlotMgmt();
        }
    });
});

function fetchHealth() {
    fetch(HEALTH_API)
        .then(res => res.json())
        .then(data => {
            document.getElementById('count-online').textContent = data.summary.online;
            document.getElementById('count-offline').textContent = data.summary.offline;
            document.getElementById('count-overstay').textContent = data.summary.overstay;
            renderHealthTable(data.sensors);
            renderOverrideTable(data.sensors);
        })
        .catch(err => console.error('Health check failed:', err));
}

function renderHealthTable(sensors) {
    const body = document.getElementById('health-body');
    if (!sensors.length) {
        body.innerHTML = '<tr><td colspan="9" style="text-align:center; padding:40px; color:#888;">No sensors found</td></tr>';
        return;
    }
    body.innerHTML = sensors.map(s => {
        const statusText = s.status == 0 ? 'Occupied' : 'Available';
        const battDisplay = s.battery !== null
            ? `<span style="${s.battery < 20 ? 'color:#ef4444;' : s.battery < 50 ? 'color:#f59e0b;' : 'color:#16a34a;'}">${s.battery}%</span>`
            : '<span style="color:#999;">—</span>';
        return `
            <tr>
                <td><strong>#${s.sensor_id}</strong></td>
                <td>${s.slot_name}</td>
                <td>${s.slot_type}</td>
                <td>${s.level} / ${s.location}</td>
                <td>${statusText}</td>
                <td>${battDisplay}</td>
                <td>${s.timestamp}<br><small style="color:#888">(${s.hours_ago}h ago)</small></td>
                <td><span class="health-badge ${s.health}">${s.health.toUpperCase()}</span></td>
                <td>${s.alert ? '<span class="alert-msg">⚠ ' + s.alert + '</span>' : '—'}</td>
            </tr>
        `;
    }).join('');
}

function renderOverrideTable(sensors) {
    const body = document.getElementById('override-body');
    body.innerHTML = sensors.map(s => {
        const current = s.status == 0 ? 'Occupied' : 'Available';
        return `
            <tr>
                <td><strong>#${s.sensor_id}</strong></td>
                <td>${s.slot_name}</td>
                <td>${current}</td>
                <td>
                    <select class="override-input" id="override-${s.sensor_id}">
                        <option value="0">Occupied</option>
                        <option value="1">Available</option>
                    </select>
                </td>
                <td>
                    <button class="override-btn" onclick="overrideSlot(${s.sensor_id})">
                        Apply
                    </button>
                </td>
            </tr>
        `;
    }).join('');
}

function overrideSlot(sensorId) {
    const val = document.getElementById('override-' + sensorId).value;
    fetch(OVERRIDE_API + '?slot_id=' + sensorId + '&status=' + val)
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                alert('Slot ' + sensorId + ' set to ' + (val == 0 ? 'Occupied' : 'Available'));
                fetchHealth();
            } else {
                alert('Error: ' + (data.error || 'Failed'));
            }
        })
        .catch(err => alert('Request failed'));
}

function loadSlotMgmt() {
    loadMappings();
    loadAssignDropdowns();
}

function loadMappings() {
    fetch(MAPPINGS_API)
        .then(res => res.json())
        .then(data => {
            const body = document.getElementById('mapping-body');
            if (!data.length) {
                body.innerHTML = '<tr><td colspan="9" style="text-align:center; padding:40px; color:#888;">No slots found</td></tr>';
                return;
            }
            body.innerHTML = data.map(s => {
                const sensorLabel = s.sensor_id
                    ? `<span class="health-badge online">#${s.sensor_id}</span>`
                    : '<span style="color:#999;">—</span>';
                const statusLabel = s.status !== null
                    ? (s.status == 0 ? '<span style="color:#ef4444;">Occupied</span>' : '<span style="color:#16a34a;">Available</span>')
                    : '<span style="color:#999;">—</span>';
                return `
                    <tr>
                        <td>${s.id}</td>
                        <td><strong>${s.slot_name}</strong></td>
                        <td>${s.slot_type}</td>
                        <td>${s.level}</td>
                        <td>${s.location}</td>
                        <td>${s.zone}</td>
                        <td>${sensorLabel}</td>
                        <td>${statusLabel}</td>
                        <td>
                            <button class="edit-btn" onclick="editSlot(${s.id})" style="margin-right:4px;"><i class="fa-solid fa-pen"></i></button>
                            <button class="delete-btn" onclick="deleteSlot(${s.id}, '${s.slot_name}')"><i class="fa-solid fa-trash-can"></i></button>
                        </td>
                    </tr>
                `;
            }).join('');
        })
        .catch(err => console.error('Failed to load mappings:', err));
}

function deleteSlot(slotId, slotName) {
    if (!confirm(`Delete "${slotName}" (Slot #${slotId})? This cannot be undone.`)) return;
    const fd = new FormData();
    fd.append('slot_id', slotId);
    fetch(DELETE_API, { method: 'POST', body: fd })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
                loadSlotMgmt();
            } else {
                alert('Error: ' + (data.error || 'Failed'));
            }
        })
        .catch(err => alert('Request failed'));
}

document.getElementById('add-slot-form').addEventListener('submit', function (e) {
    e.preventDefault();
    const fd = new FormData(this);
    fetch(ADD_SLOT_API, { method: 'POST', body: fd })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
                this.reset();
                loadSlotMgmt();
            } else {
                alert('Error: ' + (data.error || 'Failed'));
            }
        })
        .catch(err => alert('Request failed'));
});

function editSlot(slotId) {
    // Fetch slot details + all sensors
    Promise.all([
        fetch(MAPPINGS_API).then(r => r.json()),
        fetch(ACTIVE_API).then(r => r.json())
    ]).then(([mappings, sensors]) => {
        const slot = mappings.find(s => s.id === slotId);
        if (!slot) { alert('Slot not found'); return; }

        document.getElementById('edit-slot-id').value = slot.id;
        document.getElementById('edit-slot-name').value = slot.slot_name;
        document.getElementById('edit-location').value = slot.location;
        document.getElementById('edit-level').value = slot.level;
        document.getElementById('edit-zone').value = slot.zone;
        document.getElementById('edit-slot-type').value = slot.slot_type;

        // Build sensor dropdown: all sensors + current assignment + none
        const sel = document.getElementById('edit-sensor-id');
        sel.innerHTML = '<option value="">— None (unassign) —</option>';

        const currentSensorId = slot.sensor_id;
        const allSensors = sensors.all || [];

        allSensors.forEach(s => {
            const opt = document.createElement('option');
            opt.value = s.sensor_id;
            // Show assigned slot name if sensor is assigned elsewhere
            const assignedSlot = mappings.find(m => m.sensor_id === s.sensor_id && m.id !== slotId);
            const label = assignedSlot
                ? `Sensor #${s.sensor_id} (currently in "${assignedSlot.slot_name}")`
                : `Sensor #${s.sensor_id}`;
            opt.textContent = label;
            if (s.sensor_id === currentSensorId) opt.selected = true;
            sel.appendChild(opt);
        });

        document.getElementById('edit-modal').style.display = 'flex';
    }).catch(err => console.error('Failed to load edit data:', err));
}

function closeEditModal() {
    document.getElementById('edit-modal').style.display = 'none';
}

document.getElementById('edit-slot-form').addEventListener('submit', function (e) {
    e.preventDefault();
    const fd = new FormData(this);
    fetch(EDIT_API, { method: 'POST', body: fd })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
                closeEditModal();
                loadSlotMgmt();
            } else {
                alert('Error: ' + (data.error || 'Failed'));
            }
        })
        .catch(err => alert('Request failed'));
});

function loadAssignDropdowns() {
    fetch(MAPPINGS_API)
        .then(res => res.json())
        .then(slots => {
            const sel = document.getElementById('assign-slot-select');
            sel.innerHTML = '<option value="">— Select Slot —</option>'
                + slots.map(s => `<option value="${s.id}">${s.slot_name} ${s.sensor_id ? '(#'+s.sensor_id+')' : '(unassigned)'}</option>`).join('');
        })
        .catch(err => console.error('Failed to load slots:', err));

    fetch(ACTIVE_API)
        .then(res => res.json())
        .then(data => {
            const sel = document.getElementById('assign-sensor-select');
            sel.innerHTML = '<option value="">— Select Sensor —</option>'
                + (data.unassigned.length
                    ? data.unassigned.map(s => `<option value="${s.sensor_id}">Sensor #${s.sensor_id}</option>`).join('')
                    : '<option value="" disabled>No unassigned sensors</option>');
        })
        .catch(err => console.error('Failed to load sensors:', err));
}

document.getElementById('assign-sensor-form').addEventListener('submit', function (e) {
    e.preventDefault();
    const fd = new FormData(this);
    fetch(ASSIGN_API, { method: 'POST', body: fd })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
                loadSlotMgmt();
            } else {
                alert('Error: ' + (data.error || 'Failed'));
            }
        })
        .catch(err => alert('Request failed'));
});

fetchHealth();
setInterval(fetchHealth, 10000);
