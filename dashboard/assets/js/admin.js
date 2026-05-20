const HEALTH_API = "/smart-parking/api/get_sensor_health.php";
const OVERRIDE_API = "/smart-parking/api/update_slot.php";

document.querySelectorAll('.tab-btn').forEach(btn => {
    btn.addEventListener('click', function () {
        document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
        document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
        this.classList.add('active');
        document.getElementById('tab-' + this.dataset.tab).classList.add('active');
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
        body.innerHTML = '<tr><td colspan="8" style="text-align:center; padding:40px; color:#888;">No sensors found</td></tr>';
        return;
    }
    body.innerHTML = sensors.map(s => {
        const statusText = s.status == 0 ? 'Occupied' : 'Available';
        return `
            <tr>
                <td><strong>#${s.sensor_id}</strong></td>
                <td>${s.slot_name}</td>
                <td>${s.slot_type}</td>
                <td>${s.level} / ${s.location}</td>
                <td>${statusText}</td>
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

fetchHealth();
setInterval(fetchHealth, 10000);