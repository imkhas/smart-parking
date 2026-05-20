const SLOT_DETAILS_URL = "/smart-parking/api/get_slot_details.php";

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
        timeEl.innerHTML = now.toLocaleString('en-US', options);
    }
}

updateDateTime();
setInterval(updateDateTime, 1000);

function showSlotDetails(slotId) {
    fetch(SLOT_DETAILS_URL + '?slot_id=' + slotId)
        .then(res => res.json())
        .then(response => {
            if (response.success) {
                displayModal(response.data);
            } else {
                alert(response.error || "Error loading slot");
            }
        })
        .catch(err => {
            console.error(err);
            alert("Failed to load slot details");
        });
}

function displayModal(data) {
    const statusText = data.status == 0 ? 'OCCUPIED' : 'AVAILABLE';
    const statusClass = data.status == 0 ? 'occupied' : 'available';

    let slotTypeClass = 'slot-type-standard';
    const type = (data.slot_type || '').toLowerCase();
    if (type.includes('disabled')) slotTypeClass = 'slot-type-disabled';
    else if (type.includes('ev')) slotTypeClass = 'slot-type-ev';
    else if (type.includes('women')) slotTypeClass = 'slot-type-womens';
    else if (type.includes('compact')) slotTypeClass = 'slot-type-compact';

    const modal = `
        <div class="modal-overlay" onclick="closeModal()">
            <div class="modal-content" onclick="event.stopPropagation()">
                <span class="close-btn" onclick="closeModal()">&times;</span>

                <h2>
                    ${data.slot_name || 'Slot ' + data.sensor_id}
                    <span class="slot-type-badge ${slotTypeClass}">
                        ${data.slot_type || 'Standard'}
                    </span>
                </h2>

                <span class="status-badge ${statusClass}">${statusText}</span>

                <div class="detail-section">
                    <h3><i class="fa-solid fa-location-dot"></i> Location</h3>
                    <p><strong>Level:</strong> ${data.level}</p>
                    <p><strong>Zone:</strong> ${data.zone}</p>
                    <p><strong>Location:</strong> ${data.location}</p>
                </div>

                <div class="detail-section">
                    <h3><i class="fa-solid fa-info-circle"></i> Slot Info</h3>
                    <p><strong>Type:</strong> ${data.slot_type}</p>
                </div>

                <div class="detail-section">
                    <h3><i class="fa-solid fa-clock"></i> Last Updated</h3>
                    <p>${data.timestamp || 'N/A'}</p>
                </div>
            </div>
        </div>
    `;

    document.body.insertAdjacentHTML('beforeend', modal);
}

function closeModal() {
    const modal = document.querySelector('.modal-overlay');
    if (modal) modal.remove();
}

function initializeSlotClickHandlers() {
    document.querySelectorAll('.slot').forEach(slot => {
        slot.addEventListener('click', function () {
            const slotId = this.id.replace('slot', '');
            showSlotDetails(slotId);
        });
    });
}

document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') closeModal();
});

document.addEventListener('DOMContentLoaded', function () {
    initializeSlotClickHandlers();
});
